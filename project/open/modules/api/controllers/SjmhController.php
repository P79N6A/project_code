<?php
/**
 *
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\sjmh\SjmhDockingApi;
use app\models\sjmh\SjmhRequest;
use app\modules\api\common\sjmh\SNotify;
use app\models\GatherResult;
use yii\helpers\ArrayHelper;
use app\common\Func;
use YII;

set_time_limit(0);

class SjmhController extends ApiController{

    protected $server_id = 105;
    #错误码105
    /*
     * * 错误码说明
       error_code [
            105001 => '请求参数不完整'，
            0 => '数据保存成功',
            105003 => '该用户正在认证中',
            105004 => '请求类型不存在',
            105005 => '数据保存失败'，
            105006 => 'Task_id  已经存在，任务已经创建'，
            105007 => '请求次数过多，请稍后请求'，
            105008 => '认证失败，并返回认证信息，
            105009 => '认证成功，并返回认证信息'，
        ]
   */
    private $osjmhapi;  //引入公共类

    public function init() {
        parent::init();
        $this->osjmhapi = new SjmhDockingApi();
    }

    /**
     * 获取用户的认证信息结果认证
     * @return array|string
     */
    public function actionIndex(){
//        $postData = $this->post();
        $postData = $this->reqData;
        Logger::dayLog('sjmh/Index','request','请求参数：',$postData);
        //默认 0   为获取该用户的所有认证信息  1学信 2社保 3公积金 4京东商城  5（树立）银行流水
        $type =  ArrayHelper::getValue($postData,'type','0');
        $aid =  ArrayHelper::getValue($postData,'appId','1');
        $user_id =  ArrayHelper::getValue($postData,'user_id','1');
        $sourceInfo =[
            '0'=>'init',    //默认 没用
            '1'=>'chsi',    //学信
            '2'=>'she_bao',     //社保
            '3'=>'gjj',         //公积金
            '4'=>'jd',          //京东
            '5'=>'slbank',      //树立  银行流水
            '6'=>'tb',      //淘宝商城
            '7'=>'wy',      //网银
        ];
        if(empty($aid) || empty($user_id)){
            Logger::dayLog('sjmh/Index','error','参数不全：',$postData);
            return $this->resp('105001','参数不全！');
        }
        $source =[];
        if($type == 0){
            //获取的source
            $source = [1,2,3,4,5,6,7];
        }else{
            $source = [$type];
        }
        $oG = new GatherResult();
        $res = [];
        foreach($source as $v){
            $resultData = $oG->getDataList($user_id,$aid,$v);
            $res[$sourceInfo[$v]] = json_encode($resultData);
        }
        Logger::dayLog('sjmh/Index','result','返回数据：',$res,'type',$type,'aid',$aid);
        return $this->resp(0,$res);
    }


    /*
     *  添加授权成功的用户信息
     *  post  接收数据
     *  $user_id    用户id
     *  $source    数据源类型
     *  $callback_url   回调通知地址
     *  $task_id    任务id
     *  $aid     应用id
     * */
    public function actionSave(){
        $value = $this->reqData;
        $postdata=array(
            'user_id'          =>   (int)ArrayHelper::getValue($value,'user_id'),
            'source'     =>   (int)ArrayHelper::getValue($value,'source'),
            'callback_url'     =>   ArrayHelper::getValue($value,'callback_url'),
            'task_id'     =>   ArrayHelper::getValue($value,'task_id'),
            'request_id'     =>   (int)ArrayHelper::getValue($value,'request_id'),
            //'aid'     =>   $this->appData['id'],
        );
        //判断source  值是否符合要求
        $is_type = $this->osjmhapi->is_source(ArrayHelper::getValue($postdata,'source'));
        if($is_type){
            return $this->resp(105004, array('reason'=>"source类型不存在:".ArrayHelper::getValue($postdata,'source')));
        }
        if(empty($postdata['user_id']) || empty($postdata['task_id']) || empty($postdata['source']) || empty($postdata['callback_url']) || empty($postdata['request_id'])){
            Logger::dayLog('sjmh/sjmhDocking','Save/error','数据不全：',$postdata);
            return $this->resp(105001, array('reason'=>'参数信息不完整。'));
        }
        Logger::dayLog('sjmh/sjmhDocking','Save/success','请求数据：',$postdata);

        $oRequest = new SjmhRequest();
        //判断task_id  是否存在
        $taskData = $oRequest->getOne(ArrayHelper::getValue($postdata,'task_id'),'task_id');
        if($taskData){
            Logger::dayLog('sjmh/sjmhDocking','Save/is_taskId',date('Y-m-d H:i:s').'任务信息已经存在：',$taskData);
            $request_status = ArrayHelper::getValue($taskData,'request_status','');
            if($request_status==4 || $request_status==3 || $request_status==1){
                //授权中和重试
                return $this->resp(105003, array('reason'=>'信息正在认证中。','data'=>$taskData->attributes));
            }
            if($request_status==11){
                //授权失败
                return $this->resp(105008, array('reason'=>'信息认证失败。','data'=>$taskData->attributes));
            }
            return $this->resp(105009, array('reason'=>'信息已经认证完成。','data'=>$taskData->attributes));
        }
        $oSjmhRequest = new SjmhRequest;
        $post = $oRequest->oneSave($oSjmhRequest->gStatus('STATUS_AUTHORIZE'),$postdata);
        if(!$post){
            Logger::dayLog('sjmh/sjmhDocking','Save/error','数据保存失败：',$postdata);
            return $this->resp(105005, array('reason'=>'数据添加失败。','data'=>$postdata));
        }
        $oneData = $oRequest->getOne($postdata['task_id'],'task_id');
        return json_encode(array('res_code'=>'0','res_data'=>array('reason'=>'数据添加成功。','data'=>$oneData->attributes)));
    }



    /*
     *      访问入口
     *      $user_id        用户id
     *      $source_type    数据类型
     *      $cb_url  回显地址   授权成功以后跳转的地址
     *      return  数据魔盒h5入口接口
     * */
    public function actionChoice(){
        $value = $this->reqData;
        Logger::dayLog('sjmh/sjmhDocking','Choice/Request','请求数据：',$value);
        $postdata=array(
            'user_id'          =>   (int)ArrayHelper::getValue($value,'user_id'),
            'source'     =>   (int)ArrayHelper::getValue($value,'source'),
            'cb_url'     =>   ArrayHelper::getValue($value,'cb_url'),
            'aid'     =>   $this->appData['id'],
        );

        //判断source  值是否符合要求
        $is_type = $this->osjmhapi->is_source(ArrayHelper::getValue($postdata,'source'));
        if($is_type){
            return $this->resp(105004, array('reason'=>"source类型不存在:".ArrayHelper::getValue($postdata,'source')));
        }

        if(empty($postdata['user_id'])  || empty($postdata['source']) || empty($postdata['cb_url']) ){
            Logger::dayLog('sjmh/sjmhDocking','Choice/error','数据不全：',$postdata);
            return $this->resp(105001, array('reason'=>'参数信息不完整。'));
        }
        Logger::dayLog('sjmh/sjmhDocking','Choice/success','请求数据：',$postdata);
        //获取抓取数据的列表
        $oSjmhRequest = new SjmhRequest;
        //获取当前时间  前5分钟内  请求的条数  如果大于10条让稍后请求
        $request_frequency = $oSjmhRequest->restriction(ArrayHelper::getValue($postdata,'user_id'));
        if($request_frequency>=10){
            Logger::dayLog('sjmh/sjmhDocking','Save/error','5分钟内请求的次数：',$request_frequency);
            return $this->resp(105007, array('reason'=>'请求次数过，多请稍后请求。'));
        }
        $re = $oSjmhRequest->isRepeatQuery($postdata['user_id'],$postdata['source']);
        if($re){
            Logger::dayLog('sjmh/sjmhDocking/isRepeatQuery','isRepeatQuery',date('Y-m-d H:i:s').'用户信息认证：',$re);
            $request_status = ArrayHelper::getValue($re,'request_status','');
            if($request_status==4 || $request_status==3 || $request_status==1){
                //授权中和重试
                return $this->resp(105003, array('reason'=>'信息正在认证中。','data'=>$re->attributes));
            }
            if($request_status==11){
                //授权失败
                return $this->resp(105008, array('reason'=>'信息认证失败。','data'=>$re->attributes));
            }
            return $this->resp(105009, array('reason'=>'信息已经认证完成。','data'=>$re->attributes));

        }
        $saveDate = $postdata;
        unset($saveDate['cb_url']);
        $oRequest = new SjmhRequest();
        $request_id = $oRequest->saveData($saveDate);
        if(!$request_id){
            Logger::dayLog('sjmh/sjmhDocking','Save/error','数据保存失败：',$saveDate);
            return $this->resp(105005, array('reason'=>'数据添加失败。','data'=>$saveDate));
        }
        $postdata['request_id'] = $request_id;
        unset($postdata['aid']);
        $url = $this->osjmhapi->getTypeUrl($postdata);
        return json_encode(array('res_code'=>'0','res_url'=>$url));
    }


    //测试文件
    public function actionCheck(){
        $data['get'] = \Yii::$app->request->get();
        $data['post'] = \Yii::$app->request->post();
        $path = '/ofiles/jxl/result.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, json_encode($data)."\n",FILE_APPEND);
        echo 'SUCCESS';
    }



}
    ?>
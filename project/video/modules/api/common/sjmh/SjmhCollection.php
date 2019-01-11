<?php
/**
 * 数据魔盒H5对接 信息采集
 *  collection
 */
namespace app\modules\api\common\sjmh;
use app\models\sjmh\SjmhNotify;
use app\models\sjmh\SjmhResult;
use app\modules\api\common\sjmh\SNotify;
use Yii;
use app\common\Logger;
use app\models\sjmh\SjmhRequest;
use yii\helpers\ArrayHelper;
use app\common\Func;

class SjmhCollection{
    private $oSjmhDockingApi;
    private $config;
    private $oSjmhRequest;
    #抓取返回错误码，处理方式为  稍后重试
    private $error_code = array(201,202,203,204,205,206,207);

    public function __construct()
    {
        $this->oSjmhDockingApi = new SjmhDockingApi();
        $this->oSjmhRequest = new SjmhRequest();
        $this->config = $this->oSjmhDockingApi->getConfig();
    }

    /**
     * 执行所有需要查询的数据
     */
    public  function runAll() {
        //1 获取需要通知的数据
        $dataList = $this->oSjmhRequest->getSjmhRequestList();
        return $this->runRequest($dataList);
    }
    /**
     * 暂时五分钟跑一批:
     */
    public function runRequest($dataList) {
        if(empty($dataList)){
            return false;
        }
        //锁定状态为抓取中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oSjmhRequest->lockStatus($ids);   //1 锁定为抓取中的请求
        #$ups = true;
        if (!$ups) {
            return false;
        }

        $num = 0;
        foreach($dataList as  $value){
            $re = $this->oneDepositDate($value);
            if($re){
                $num++ ;
            }else{
                Logger::dayLog('sjmh/SNotify', 'runRequest_error','处理失败', $value);
            }
        }

        logger::dayLog('sjmh/SjmhCollection','runRequest','抓取成功条数：'.$num.',数据：',$ids);
        // 返回结果
        var_dump($num);
        return $num;
    }

    //处理单条数据，保存数据，通知
    public function oneDepositDate($value){
        $task_id = ArrayHelper::getValue($value,'task_id');
        $source = ArrayHelper::getValue($value,'source');
        $user_id = ArrayHelper::getValue($value,'user_id');
        $request_id = ArrayHelper::getValue($value,'id');
        $aid = ArrayHelper::getValue($value,'aid');

        $data['source'] = $source;
        $data['task_id'] = $task_id;
        $data['request_id'] = $request_id;
        $data['user_id'] = $user_id;
        $data['aid'] = $aid;

        $dataJson = $this->getJsonData($task_id,$source);
        $dataArray = json_decode($dataJson,true);
        $code = Arrayhelper::getValue($dataArray,'code');
        $data['reason'] = json_encode(array('code'=>$code,'message'=>ArrayHelper::getValue($dataArray,'message')));

        //判断抓的数据状态码
        if($code != 0){
            $this->oSjmhRequest->oneSave($this->oSjmhRequest->gStatus('STATUS_FAILURE'),$data);
            $request_status = $this->oSjmhRequest->gStatus('STATUS_FAILURE');
            logger::dayLog('sjmh/SjmhCollection','oneDepositDate','采集查询失败，错误代码：'.$code.'数据：',$value);
            return  $this->oneToNotify($request_id,$request_status,$value);
        }elseif(in_array($code,$this->error_code)){
            logger::dayLog('sjmh/SjmhCollection','oneDepositDate','添加采集结果错误,请求ID'.$request_id.'原因：',$dataArray);
            //状态更改为0等待下次再次抓取
            $this->oSjmhRequest->oneSave($this->oSjmhRequest->gStatus('STATUS_RETRY'),$data);
            return false;
        }
        //获取用户的基本信息
        $userInfo = $this->getUserInfo($source,$dataArray);
        if($userInfo){
            $data['name'] = ArrayHelper::getValue($userInfo,'name');;
            $data['mobile'] = ArrayHelper::getValue($userInfo,'mobile');;
            $data['idcard'] = ArrayHelper::getValue($userInfo,'idcard');;
        }
        //保持抓取结果获取路径地址
        $path = $this->saveJsonData($dataJson,$request_id);
        if(!$path){
            logger::dayLog('sjmh/SjmhCollection','oneDepositDate','添加采集结果错误,请求ID'.$request_id.'数据：',$dataJson);
            //状态更改为0等待下次再次抓取
            $this->oSjmhRequest->oneSave($this->oSjmhRequest->gStatus('STATUS_RETRY'),$data);
            return false;
        }
        $data['data_url'] = $path;
        //保存json文件
        $oSjmhResult = new SjmhResult();
        $result = $oSjmhResult->saveData($data);

        //修改请求状态
        if($result){
            $reStatus = $this->oSjmhRequest->oneSave($this->oSjmhRequest->gStatus('STATUS_SUCCESS'),$data);
            $request_status = $this->oSjmhRequest->gStatus('STATUS_SUCCESS');
            if($reStatus){
                return  $this->oneToNotify($request_id,$request_status,$value);
            }
        }
        //添加数据失败  状态更改成0等待下次抓取
        $this->oSjmhRequest->oneSave($this->oSjmhRequest->gStatus('STATUS_RETRY'),$data);
        return false;

    }


    /*
     *  从抓取的数据中 获取一些基本的用户信息
     *  $data 抓取到的 数据
     *  $type    she_bao  chsi   gjj  区分社保，学信，公积金
     * */
    public function getUserInfo($source,$data){
        if(!is_array($data)){
            return false;
        }
        $returnData = '';
        if($source == $this->oSjmhDockingApi->gStatus('SOURCE_CHSI')){
            $returnData['name'] = ArrayHelper::getValue($data,'data.real_name');
            $returnData['idcard'] = ArrayHelper::getValue($data,'data.identity_code');
        }elseif($source == $this->oSjmhDockingApi->gStatus('SOURCE_GJJ')){
            $returnData['name'] = ArrayHelper::getValue($data,'data.task_data.base_info.name');
            $returnData['mobile'] = ArrayHelper::getValue($data,'data.task_data.base_info.mobile');
            $returnData['idcard'] = ArrayHelper::getValue($data,'data.task_data.base_info.cert_no');
        }elseif($source == $this->oSjmhDockingApi->gStatus('SOURCE_SHE_BAO')){
            $returnData['name'] = ArrayHelper::getValue($data,'data.task_data.user_info.name');
            $returnData['mobile'] = ArrayHelper::getValue($data,'data.task_data.user_info.mobile');
            $returnData['idcard'] = ArrayHelper::getValue($data,'data.task_data.user_info.certificate_number');
        }else{
            return false;
        }
        return $returnData;
    }

    /*
     *  通知刚抓取完成的数据
     * $request_id   请求ID
     * $request_status  状态
     * $value  刚抓取的数据
     * */
    public function oneToNotify($request_id,$request_status,$value){
        //添加 通知数据
        $oSjmhNotify = new SjmhNotify();
        $res = $oSjmhNotify->saveData($request_id,$request_status);
        //开始通知本条数据
        if(!$res){
            logger::dayLog('sjmh/SjmhCollection','oneDepositDate','添加通知数据错误,数据：',$value);
            return false;
        }
        //根据请求id去查询 通知表 然后开始通知
        $resultOneDate = $oSjmhNotify->getOne($request_id,'request_id');
        $oSNotify = new SNotify();
        //----通知地址-----------------
        $notifyResult = $oSNotify->doNotify($resultOneDate);
        return $notifyResult;
    }

    //获取第三方的接口的数据
    public function getJsonData($taskId,$type){
        $config = $this->config;
        $data['partner_code'] = $config['partner_code'];
        $data['partner_key'] = $config['partner_key'];
        $url = $this->oSjmhDockingApi->getQueryUrl($type);
        $url = $this->oSjmhDockingApi->combinationUrl($url,$data);
        $postData['task_id'] = $taskId;
        if(empty($data['task_id'])){
            logger::dayLog('sjmh/collection','','task_id(任务id)不能为空');
        }
        $result = $this->Post($postData,$url);
        logger::dayLog('sjmh/collection','query_api','请求地址'.$url.'請求參數：',$postData);
        return $result;

    }

    //保存json数据文件
    public function saveJsonData($data,$request_id){
        //线上存储路径
        $path = '/../../openapi_ofiles/openapi/sjmh/' . date('Ym/d/') . $request_id . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        //访问路径
        $path = '/ofiles/openapi/sjmh/' . date('Ym/d/') . $request_id . '.json';
        return $path;
    }



    /**
     * @param $PostArry
     * @param $request_url
     * @return mixed
     */
    public function Post($PostArry,$request_url){
        $postData = $PostArry;
        $postDataString = http_build_query($postData);//格式化参数
        Logger::dayLog("ducredit/http", 'request',",请求地址:".$request_url."请求参数：",$postDataString);
        //die();
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $request_url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postDataString); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 60); // 设置超时限制防止死循环返回
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $tmpInfo = curl_error($curl);//捕抓异常
            Logger::dayLog("sjmh/http", 'abnormal',"异常："+$tmpInfo);
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

}
?>
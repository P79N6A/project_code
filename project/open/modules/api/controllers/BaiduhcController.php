<?php
/**
 *
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\baiduhc\BaiduhcApi;
use app\models\baiduhc\Baiduhc;
use yii\helpers\ArrayHelper;
use app\common\Func;
use YII;

set_time_limit(0);

class BaiduhcController extends ApiController{

    protected $server_id = 105;
    #错误码107
    /*
     * * 错误码说明
       error_code [
            105001 => '请求参数不完整'，

        ]
   */
    private $obaiduhcapi;  //引入公共类

    public function init() {
        parent::init();
        $this->obaiduhcapi = new BaiduhcApi();
    }
    /*
     *  查询信息信息
     *  @param   $postdata     接收数据
     *  @param   $user_id    用户id
     *  @param   sp_no
     *  @param   service_id
     *  @param   identity
     *  @param   phone
     *  @param   name
     * */
    public function actionRequest(){
        $value = $this->reqData;
        $postdata=array(

            'name'      =>   ArrayHelper::getValue($value,'name'),
            'identity'  =>   ArrayHelper::getValue($value,'identity'),//身份证
            'phone'     =>   ArrayHelper::getValue($value,'phone'),
            'datetime'  =>   time(),
            'reqid'     =>   'HC'.time(),
            'sign_type' =>    '1',//固定参数

        );
        $identity = ArrayHelper::getValue($value,'identity');//身份证
        $phone  = ArrayHelper::getValue($value,'phone');
        //判断  值是否符合要求
        if(empty($postdata['name']) || empty($postdata['identity']) || empty($postdata['phone'])){

            Logger::dayLog('baiduhc','Request/error','数据不全：',$postdata);
            return $this->resp(107001, array('reason'=>'参数信息不完整。'));
        }
        Logger::dayLog('baiduhc','Request/success','请求数据：',$postdata);
        $oRequest = new baiduhc();
        $rest =$oRequest->isRepeatQuery($identity,$phone);//根据身份证查询 此用户是否请求过
        if($rest!='true'){
            $rel = json_decode($rest,true);
            return $this->resp(0,$rel);
        }
        $obaiduhcapi =new BaiduhcApi();
        $data = $obaiduhcapi->sendFormat($value,$postdata);
        $req = $oRequest->saveData($data);//请求数据写入表
        $rst = $obaiduhcapi->getSign($postdata);//获取请求结果
        $result = $oRequest->oneSave($data,$rst);
        if($result){
            $rel = json_decode($rst,true);
            return $this->resp(0,$rel);

        }
        return false;

    }



}
    ?>
<?php
/**
 *      腾讯云反欺诈
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\antifraud\AntifraudApi;
use app\models\antifruad\AntiFruad;
use app\modules\api\common\fulin\FulinApi;
use app\models\fulin\Fulin;
use yii\helpers\ArrayHelper;
use app\common\Func;
use YII;

set_time_limit(0);

class AntifraudController extends ApiController{

    protected $server_id = 105;

//接口文档地址：https://cloud.tencent.com/document/product/668/14267
    public function init() {
        parent::init();
    }
    /*
     *  查询信息信息
     *  @param   $postdata     接收数据
     *
     * */
    public function actionRequest(){
        $value = $this->reqData;
        $postdata=array(
            'name' => ArrayHelper::getValue($value,'name'),     //姓名
            'mobile' => ArrayHelper::getValue($value,'mobile'),        //手机号
            'idCardno' => ArrayHelper::getValue($value,'idCardno'),         //身份证号
            'user_id' => ArrayHelper::getValue($value,'user_id','0'),         //身份证号
            'aid'=>$this->appData['id'],
        );
        if (empty($postdata['name']) || empty($postdata['idCardno']) || empty($postdata['mobile'])) {
            Logger::dayLog('fulin','Request/error','请求数据不全：',$postdata);
            return false;
        }
        $idCardno = ArrayHelper::getValue($value,'idCardno');
        $oAntiFruad = new AntiFruad();

        $rest =$oAntiFruad->isRepeatQuery($idCardno);//根据身份证查询 此用户是否请求过
        if($rest != 'true'){
            $resData = $this->resFormData($rest);
            return $this->resp(0,$resData);
        }

        $oAn = new AntifraudApi();
        $data = $oAn->sendFormat($postdata);
        $req = $oAntiFruad->saveData($data);//请求数据写入表
        Logger::dayLog("AntiFruad", "请求信息",$postdata);
        $res = $oAn->antiFraud($postdata);//请求第三方接口
        Logger::dayLog("AntiFruad", "请求结果",$res);
        $code = ArrayHelper::getValue($res,'code','');
        $codeDesc = ArrayHelper::getValue($res,'codeDesc','');
        if($code != 0 || $codeDesc != 'Success' ){
            Logger::dayLog("AntiFruad/error", "获取失败",$res);
            $oAntiFruad->oneErrorSave($req,$res);
            return $this->resp(105001,ArrayHelper::getValue($res,'message','获取失败！'));
        }

        $result = $oAntiFruad->oneSave($req,$res);
        $resData = $this->resFormDatas($res);
        return $this->resp(0,$resData);

    }

    /*
     *  返回的字段整理
     */
    public function resFormData($res){
        $data = [];
        $data['idFound'] = ArrayHelper::getValue($res,'id_found','');
        $data['found'] = ArrayHelper::getValue($res,'found','');
        $data['riskInfo'] = json_encode(ArrayHelper::getValue($res,'risk_info',''));
        $data['riskScore'] = ArrayHelper::getValue($res,'risk_score','');
        return $data;
    }

    /*
 *  返回的字段整理
 */
    public function resFormDatas($res){
        $data = [];
        $data['idFound'] = ArrayHelper::getValue($res,'idFound','');
        $data['found'] = ArrayHelper::getValue($res,'found','');
        $data['riskInfo'] = json_encode(ArrayHelper::getValue($res,'riskInfo',''));
        $data['riskScore'] = ArrayHelper::getValue($res,'riskScore','');
        return $data;
    }



}
    ?>
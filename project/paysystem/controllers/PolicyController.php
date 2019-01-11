<?php

/**
 * 众安保险保单推送
 */

namespace app\controllers;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\policy\PolicyBill;
use app\models\policy\PolicyPay;
use app\models\policy\ZhanPolicy;
use app\modules\api\common\policy\CPolicyBill;
use app\modules\api\common\policy\CPolicyApi;
use app\modules\api\common\policy\CPolicyPay;
use Yii;
use app\common\Crypt3Des;
class PolicyController extends BaseController {
    private $oApi;
    private $isSuccess = true;
    private $errorCode = 0;
    private $errorMsg  = '成功';
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $is_prod = SYSTEM_PROD;
        //$is_prod = true;
        $env = $is_prod ? 'prod' : 'dev';
        $this->oApi = new CPolicyApi($env);
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['notify','payback','paynotify','paynotify1'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * Undocumented function
     * 账单推送回调
     * @return void
     */
    public function actionNotify() {
        $postdata = $this->post();
        if(empty($postdata)) return false;
        $data = ArrayHelper::getValue($postdata,'data');
        $sign = ArrayHelper::getValue($postdata,'sign');
        $_data = $this->oApi->decodeAes($data);
        Logger::dayLog('policy/billpush', '解密数据', $_data);
        $_data = json_decode($_data,true);
        $policyList = ArrayHelper::getValue($_data,'policyList');
        $dataList = [];
        if(!empty($policyList)){
            foreach($policyList as $k=>$val){
                $channelOrderNo = ArrayHelper::getValue($val,'channelOrderNo');
                $policyNo = ArrayHelper::getValue($val,'policyNo');
                $dataType = ArrayHelper::getValue($val,'dataType');
                $model = new PolicyBill;
                $oPolicyBill = $model->getBillByOrderno($channelOrderNo,$dataType);
                if(empty($oPolicyBill)){
                    $result = $model->saveData($val);
                }else{
                    $result = $oPolicyBill->saveData($val);
                }              
                if(!$result){
                    Logger::dayLog('policy/billpush','保存保单记录失败',$val,$model->errinfo);
                }
                $oPolicy = (new ZhanPolicy)->getDataByClientId($channelOrderNo);
                $isSuccess = $this->isSuccess;
                $errorCode = $this->errorCode;
                $errorMsg  = $this->errorMsg;
                if(empty($oPolicy)){
                    $isSuccess = false;
                    $errorCode = "101";
                    $errorMsg = "查询不到保单记录";
                }
                if($dataType=="2" && $oPolicy->remit_status!=ZhanPolicy::STATUS_CANCEL){
                    //退保
                    $isSuccess = false;
                    $errorCode = "101";
                    $errorMsg = "退保记录匹配失败";
                }
                $data = [
                    'policyNo' => $policyNo,
                    'dataType'  => $dataType,
                    'isSuccess' => $isSuccess,
                    'errorCode' => $errorCode,
                    'errorMsg'  => $errorMsg,
                ];
                array_push($dataList,$data);
            }
        }
        $returnData = [
            'isSuccess' => $this->isSuccess,
            'errorCode' => $this->errorCode,
            'errorMsg'  => $this->errorMsg,
            'dataList'  => $dataList
        ];
        Logger::dayLog('policy/billpush', '响应返回数据',json_encode($returnData,JSON_UNESCAPED_UNICODE));
        echo  json_encode($returnData,JSON_UNESCAPED_UNICODE);exit;
    } 
    /**
     * Undocumented function
     * 支付成功跳转
     * @return void
     */
    public function actionPayback(){
        $getdata = $this->get();
        Logger::dayLog('policy/payback', '支付成功跳转',$getdata);
        $out_trade_no = ArrayHelper::getValue($getdata,'out_trade_no');//商户订单号
        $notify_info = ArrayHelper::getValue($getdata,'notify_info');//公用回传参数
        $notify_info = json_decode($notify_info,true);
        $client_id = ArrayHelper::getValue($notify_info,'client_id');//支付请求订单号
        $oPolicyPay = (new PolicyPay)->getDataByClientId($out_trade_no,$client_id);
        if(empty($oPolicyPay)){
            Logger::dayLog('policy/payback','查询不到支付订单',$client_id);
            return false;
        }
       $url = $oPolicyPay->clientBackurl();
       Header('Location:'.$url);exit;
    }
    /**
     * Undocumented function
     * 支付成功异步回调
     * @return void
     */
    public function actionPaynotify(){
        $postdata = $this->post();       
        Logger::dayLog('policy/payback', '支付异步回调数据',$postdata);
        if(empty($postdata)) return false;
        $out_trade_no = ArrayHelper::getValue($postdata,'out_trade_no');//商户订单号
        $amt = ArrayHelper::getValue($postdata,'amt',0);//支付金额
        $order_time = ArrayHelper::getValue($postdata,'order_time','');//下单时间
        $pay_time = ArrayHelper::getValue($postdata,'pay_time','');//支付时间
        $notify_time = ArrayHelper::getValue($postdata,'notify_time','');//通知时间
        $pay_trade_no = ArrayHelper::getValue($postdata,'pay_trade_no');//支付渠道订单号
        $za_order_no = ArrayHelper::getValue($postdata,'za_order_no');//众安保险订单号
        $pay_channel = ArrayHelper::getValue($postdata,'pay_channel');//支付渠道
        $pay_channel_user_no = ArrayHelper::getValue($postdata,'pay_channel_user_no');//支付渠道用户标识
        $pay_result = ArrayHelper::getValue($postdata,'pay_result');//支付结果
        $notify_info = ArrayHelper::getValue($postdata,'notify_info');//公用回传参数
        $notify_info = json_decode($notify_info,true);
        $client_id = ArrayHelper::getValue($notify_info,'client_id');//支付请求订单号
        //验签
        $result = $this->oApi->validationSign($postdata);
        if(!$result){
            Logger::dayLog('policy/payback','验签失败',$postdata);
            return false;
        }
        $oPolicyPay = (new PolicyPay)->getDataByClientId($out_trade_no,$client_id);
        if(empty($oPolicyPay)){
            Logger::dayLog('policy/payback','查询不到支付订单',$postdata);
            return false;
        }
        if($oPolicyPay->premium!=$amt){
            Logger::dayLog('policy/payback','支付订单金额不对',$postdata,'保费',$oPolicyPay->premium);
            return false;
        }
        if($pay_result == 'S'){
            $pay_status = PolicyPay::STATUS_SUCCESS;
        }else{
            $pay_status = PolicyPay::STATUS_DOING;
        }
        //判断是否是终态
        if($oPolicyPay->pay_status==PolicyPay::STATUS_SUCCESS ||$oPolicyPay->pay_status==PolicyPay::STATUS_FAILURE){
            Logger::dayLog('policy/payback','支付订单已是终态',$postdata);
            exit('success');
        }
        $data = [
            'amt'           => $amt,
            'order_time'    => $order_time,
            'pay_time'      => $pay_time,
            'notify_time'   => $notify_time,
            'pay_trade_no'  => $pay_trade_no,
            'za_order_no'   => $za_order_no,
            'pay_channel'   => $pay_channel,
            'pay_channel_user_no'   => $pay_channel_user_no,
            'pay_status'    => $pay_status,
            'pay_result'    => $pay_result,

        ];
        //更新支付表
        $result = $oPolicyPay->updateData($data);
        if(!$result){
            Logger::dayLog('policy/payback','保存订单回调结果失败',$postdata,$data,$oPolicyPay->errinfo);
            return false;
        }
        //更新保险表支付状态
        $result = (new ZhanPolicy)->upPolicy($out_trade_no,$za_order_no,$pay_status);
        if(!$result){
            Logger::dayLog('policy/payback','保存保险支付状态失败',$postdata);
            return false;
        }
        $result = (new CPolicyPay)->addNotify($oPolicyPay);
        if(!$result){
            Logger::dayLog('policy/payback','保存订单回调结果失败',$postdata,$data);
            return false;
        }
        exit('success');
    }
    /**
     * Undocumented function
     * 支付成功异步回调
     * @return void
     */
    public function actionPaynotify1(){
        $postdata = $this->post();       
        Logger::dayLog('policy/payback', '支付异步回调数据',$postdata);
        if(empty($postdata)) return false;
        $out_trade_no = ArrayHelper::getValue($postdata,'out_trade_no');//商户订单号
        $amt = ArrayHelper::getValue($postdata,'amt',0);//支付金额
        $order_time = ArrayHelper::getValue($postdata,'order_time','');//下单时间
        $pay_time = ArrayHelper::getValue($postdata,'pay_time','');//支付时间
        $notify_time = ArrayHelper::getValue($postdata,'notify_time','');//通知时间
        $pay_trade_no = ArrayHelper::getValue($postdata,'pay_trade_no');//支付渠道订单号
        $za_order_no = ArrayHelper::getValue($postdata,'za_order_no');//众安保险订单号
        $pay_channel = ArrayHelper::getValue($postdata,'pay_channel');//支付渠道
        $pay_channel_user_no = ArrayHelper::getValue($postdata,'pay_channel_user_no');//支付渠道用户标识
        $pay_result = ArrayHelper::getValue($postdata,'pay_result');//支付结果
        $notify_info = ArrayHelper::getValue($postdata,'notify_info');//公用回传参数
        $notify_info = json_decode($notify_info,true);
        $client_id = ArrayHelper::getValue($notify_info,'client_id');//支付请求订单号
        //验签
        $result = $this->oApi->validationSign($postdata);
        if(!$result){
            Logger::dayLog('policy/payback','验签失败',$postdata);
            //return false;
        }
        $oPolicyPay = (new PolicyPay)->getDataByClientId($out_trade_no,$client_id);
        if(empty($oPolicyPay)){
            Logger::dayLog('policy/payback','查询不到支付订单',$postdata);
            return false;
        }
        if($oPolicyPay->premium!=$amt){
            Logger::dayLog('policy/payback','支付订单金额不对',$postdata,'保费',$oPolicyPay->premium);
            return false;
        }
        if($pay_result == 'S'){
            $pay_status = PolicyPay::STATUS_SUCCESS;
        }else{
            $pay_status = PolicyPay::STATUS_DOING;
        }
        //判断是否是终态
        if($oPolicyPay->pay_status==PolicyPay::STATUS_SUCCESS ||$oPolicyPay->pay_status==PolicyPay::STATUS_FAILURE){
            Logger::dayLog('policy/payback','支付订单已是终态',$postdata);
            exit('success');
        }
        $data = [
            'amt'           => $amt,
            'order_time'    => $order_time,
            'pay_time'      => $pay_time,
            'notify_time'   => $notify_time,
            'pay_trade_no'  => $pay_trade_no,
            'za_order_no'   => $za_order_no,
            'pay_channel'   => $pay_channel,
            'pay_channel_user_no'   => $pay_channel_user_no,
            'pay_status'    => $pay_status,
            'pay_result'    => $pay_result,

        ];
        //更新支付表
        $result = $oPolicyPay->updateData($data);
        if(!$result){
            Logger::dayLog('policy/payback','保存订单回调结果失败',$postdata,$data,$oPolicyPay->errinfo);
            return false;
        }
        //更新保险表支付状态
        $result = (new ZhanPolicy)->upPolicy($out_trade_no,$za_order_no,$pay_status);
        if(!$result){
            Logger::dayLog('policy/payback','保存保险支付状态失败',$postdata);
            return false;
        }
        $result = (new CPolicyPay)->addNotify($oPolicyPay);
        if(!$result){
            Logger::dayLog('policy/payback','保存订单回调结果失败',$postdata,$data);
            return false;
        }
        exit('success');
    }
}

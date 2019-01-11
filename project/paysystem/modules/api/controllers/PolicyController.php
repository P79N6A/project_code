<?php

/**
 * 一亿元推送
 * 
 */
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\common\Logger;
use app\models\policy\ZhanPolicy;
use yii\helpers\ArrayHelper;
use app\modules\api\common\policy\CPolicy;
use app\modules\api\common\policy\CPolicyPay;
use app\modules\api\common\policy\CPolicyCancel;
use app\models\policy\PolicyPay;
class PolicyController extends ApiController {

    protected $server_id = 105;//服务号
    public function init() {
        parent::init();
    }

    public function actionReceive() {
        $postdata = $this->reqData; //解密的数据1
        $aid = $this->appData['id']; //aid
        Logger::dayLog('policy', '请求数据', $postdata);
        //字段判断是否为空
        $oPolicy = new ZhanPolicy;
        $check_result = $oPolicy->getVerifyEmptyData($postdata);
        if (!$check_result){
            Logger::dayLog('policy', 'errinfo',$oPolicy->errinfo);
            $this->resp(105001, $oPolicy->errinfo);
        }
        //保存入库
        $postdata['aid'] = $aid;
        $result = $oPolicy->saveData($postdata);
        if (!$result){
            Logger::dayLog('policy', 'saveData', '数据保存失败', '提交数据', $postdata, '错误原因', $oPolicy->errinfo);
            return $this->resp(105002, $oPolicy->errinfo);
        }
        //调用核保接口 同步返回结果
        $result = (new CPolicy)->doPolicy($oPolicy);
        $res_data = [
            'req_id'        => $oPolicy->req_id,
            'user_mobile'   => $oPolicy->user_mobile,
            'user_name'     => $oPolicy->user_name,
            'remit_status'  => $oPolicy->remit_status,
            'client_id'     => $oPolicy->client_id,
            'rsp_status'    => $oPolicy->rsp_status,
            'rsp_status_text'   => $oPolicy->rsp_status_text,
        ];
        if(!$result){
            Logger::dayLog('policy', 'doPolicy', '核保失败',$res_data);
            return $this->resp(105003,$res_data);
        }       
        return $this->resp(0, $res_data);
    }
    /**
     * Undocumented function
     * 众安保险收银台支付
     * @return void
     */
    public function actionPay() {
        $postdata = $this->reqData; //解密的数据1
        $aid = $this->appData['id']; //aid
        Logger::dayLog('policy', '请求数据', $postdata);
        if(empty($postdata['req_id'])){
            Logger::dayLog('policy', 'req_id参数缺失','提交数据', $postdata);
            return $this->resp(105004,'req_id参数缺失');
        }
        if(empty($postdata['client_id'])){
            Logger::dayLog('policy', 'client_id参数缺失','提交数据', $postdata);
            return $this->resp(105004,'client_id参数缺失');
        }
        // if(empty($postdata['return_url'])){
        //     Logger::dayLog('policy', 'return_url参数缺失','提交数据', $postdata);
        //     return $this->resp(105004,'return_url参数缺失');
        // }
        if(empty($postdata['callbackurl'])){
            Logger::dayLog('policy', 'callbackurl参数缺失','提交数据', $postdata);
            return $this->resp(105004,'callbackurl参数缺失');
        }
        //字段判断是否为空
        $oPolicy = (new ZhanPolicy)->getDataByReqid($aid,$postdata['req_id']);
        if(empty($oPolicy)){
            Logger::dayLog('policy','查询不到保险订单数据','提交数据',$postdata);
            return $this->resp(105005,'查询不到保险订单数据');
        }
        //判断该保险是否核保成功
        if($oPolicy->remit_status!=ZhanPolicy::STATUS_DOING){
            Logger::dayLog('policy','保险订单状态错误','提交数据',$postdata,$oPolicy->attributes);
            return $this->resp(105005,'保险订单状态错误');
        }
        //判断该保险是否已支付
        if($oPolicy->pay_status==ZhanPolicy::PAY_SUCCESS){
            Logger::dayLog('policy','保险订单支付状态错误','提交数据',$postdata,$oPolicy->attributes);
            return $this->resp(105005,'保险订单支付状态错误');
        }
        //保存到支付表
        $oPolicyPay = (new PolicyPay)->getDataByClientId($oPolicy['client_id'],$postdata['client_id']);
        if(empty($oPolicyPay)){
            $paydata = [
                'aid'       =>$aid,
                'req_id'    =>$oPolicy->req_id,//核保请求号
                'client_id' =>$postdata['client_id'],//支付请求订单号
                'order_id'  =>$oPolicy['client_id'],//核保订单号
                'premium'   =>$oPolicy->premium,
                'callbackurl'=>$postdata['callbackurl']
            ];
            $model = new PolicyPay;
            $oPolicyPay = $model->saveData($paydata);
            if (!$oPolicyPay){
                Logger::dayLog('policy', 'saveData', '支付表数据保存失败', '提交数据', $postdata, '错误原因', $model->errinfo);
                return $this->resp(105006, $model->errinfo);
            }
        }
        
        //调用核保接口 同步返回结果
        $result = (new CPolicyPay)->doPay($oPolicy,$oPolicyPay);  
        if(!$result){
            Logger::dayLog('policy','获取支付url失败','提交数据',$postdata);
            return $this->resp(105007,'获取支付url失败');
        }
        $res_data=[
            'url'=>$result
        ];
        return $this->resp(0, $res_data);
    }
    /**
     * Undocumented function
     * 众安保险退单接口
     * @return void
     */
    public function actionCancel() {
        $postdata = $this->reqData; //解密的数据1
        $aid = $this->appData['id']; //aid
        Logger::dayLog('policy', '请求数据', $postdata);
        if(empty($postdata['req_id'])){
            Logger::dayLog('policy', 'req_id参数缺失','提交数据', $postdata);
            return $this->resp(105004,'req_id参数缺失');
        }
        //字段判断是否为空
        $oPolicy = (new ZhanPolicy)->getDataByReqid($aid,$postdata['req_id']);
        if(empty($oPolicy)){
            Logger::dayLog('policy','查询不到保险订单数据','提交数据',$postdata);
            return $this->resp(105005,'查询不到保险订单数据');
        }
        //判断保单状态
        if($oPolicy->remit_status==ZhanPolicy::STATUS_CANCEL){
            Logger::dayLog('policy','保险订单已退保',$postdata);
            return $this->resp(105008,'保险订单已退保');
        }
        if($oPolicy->remit_status!=ZhanPolicy::STATUS_SUCCESS){
            Logger::dayLog('policy','保险订单状态错误，成功状态才能退保',$postdata);
            return $this->resp(105009,'保险订单状态错误，成功状态才能退保');
        }
        //调用退单接口
        $result = (new CPolicyCancel)->doCancel($oPolicy);  
        if(!$result){
            Logger::dayLog('policy','退单失败','提交数据',$postdata);
            return $this->resp(105010,'退单失败');
        }
        $res_data = [
            'req_id'        =>$oPolicy->req_id,
            'remit_status'  =>$oPolicy->remit_status,
            'premium'       =>$oPolicy->premium,
            'policyNo'      =>$oPolicy->policyNo
        ];
        return $this->resp(0, $res_data);
    }
}

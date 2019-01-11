<?php
/**
 * 计划任务处理:账单对账
 */
namespace app\modules\api\common\policy;
use app\common\Logger;
use app\models\policy\ZhanPolicy;
use app\models\policy\PolicyCheckbill;
use app\models\policy\PolicyCheckbilldetail;
use yii\helpers\ArrayHelper;
use app\models\baofoo\BfPayOrder;
use app\models\policy\PolicyBfBill;

set_time_limit(0);

class CPolicyBill {
    private $oPolicy;
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oPolicy = new ZhanPolicy;
    }

    
    /**
     * 对账
     */
    public function runBill($bill_date) {
        $dataList = (new ZhanPolicy)->getPolicyBill($bill_date);
        //var_dump($dataList);die;
        if(empty($dataList)) return false;
        $billStatus = PolicyCheckbill::STATUS_SUCCESS;
        foreach($dataList as $k=>$v){
            
            $postdata = $this->getBillData($v,$bill_date);
            //var_dump($postdata);die;
            $model = new PolicyCheckbilldetail;
            $clientId = ArrayHelper::getValue($postdata,'channelOrderNo');
            $oPolicyCheckbilldetail = $model->getData(['channelOrderNo'=>$clientId]);
            if($oPolicyCheckbilldetail){
                $res = $oPolicyCheckbilldetail->saveData($postdata);
            }else{
                $res = $model->saveData($postdata);
            }
            if(!$res){
                Logger::dayLog('policy/cpolicybill','保存对账单明细失败',$postdata,$model->errinfo);
            }
            $billDetailStatus = ArrayHelper::getValue($postdata,'billStatus',0);
            if($billDetailStatus==PolicyCheckbilldetail::STATUS_FAILURE){
                $billStatus = PolicyCheckbill::STATUS_FAILURE;
            }
        }
        $_data = [
            'billDate'=>$bill_date,
            'billStatus'=>$billStatus
        ];
        $_model = new PolicyCheckbill;
        $oPolicyCheckbill = $_model->getDataByBillDate($bill_date);
        if($oPolicyCheckbill){
            $res = $oPolicyCheckbill->saveData($_data);
        }else{
            $res = $_model->saveData($_data);
        }
        if(!$res){
            Logger::dayLog('policy/cpolicybill','保存对账单失败',$_data,$_model->errinfo);
        }
    }
    private function getBillData($v,$bill_date){
        $channelOrderNo = empty($v['client_id'])?'':$v['client_id'];
        $p_policyNo = empty($v['p_policyNo'])?'':$v['p_policyNo'];
        $policyNo = empty($v['policyNo'])?'':$v['policyNo'];
        $premium = empty($v['premium'])?'0':$v['premium'];
        $policy_premium = empty($v['policy_premium'])?'0':$v['policy_premium'];
        $aid = empty($v['aid'])?'0':$v['aid'];
        $user_name = empty($v['user_name'])?'':$v['user_name'];
        $user_mobile = empty($v['user_mobile'])?'':$v['user_mobile'];
        $fund = empty($v['fund'])?'0':$v['fund'];
        $orderId = empty($v['orderId'])?'':$v['orderId'];
        $remit_status = empty($v['remit_status'])?'0':$v['remit_status'];
        $pay_status = empty($v['pay_status'])?'0':$v['pay_status'];
        $rsp_status_text = empty($v['rsp_status_text'])?'':$v['rsp_status_text'];
        $billStatus = PolicyCheckbilldetail::STATUS_FAILURE;
        $remark = '';
        if(empty($policyNo)){          
            $remark = "众安推送账单缺失";
        }
        if(empty($p_policyNo)){
            $remark = $rsp_status_text;
        }
        if(!empty($policyNo) && !empty($p_policyNo) &&$policyNo==$p_policyNo){
            if($remit_status==ZhanPolicy::STATUS_SUCCESS){
                $billStatus = PolicyCheckbilldetail::STATUS_SUCCESS;
            }else{
                $remark = '保单状态错误';
            }
        }
        //查询宝付支付订单以及宝付对账单 确定支付状态
        $oPayOrder = (new BfPayOrder)->getByReqId($channelOrderNo);
        if(!$oPayOrder){
            $billStatus = PolicyCheckbilldetail::STATUS_FAILURE;
            $orderId = "";
            $remark = '宝付支付订单缺失';
        }
        $bfClient_id = $oPayOrder->client_id;
        $oBfBill = (new PolicyBfBill)->getBillByClientId($bfClient_id);
        //如果不存在支付账单
        if(!$oBfBill){
            $billStatus = PolicyCheckbilldetail::STATUS_FAILURE;
            $orderId = "";
            if($oPayOrder->remit_status==BfPayOrder::STATUS_SUCCESS){
                $remark = '宝付支付对账单缺失';
            }else if($oPayOrder->remit_status==BfPayOrder::STATUS_FAILURE){
                $remark = $oPayOrder->rsp_status_text;
            }else{
                $remark = "宝付支付订单失败";
            }
            
        }
        //如果存在支付账单
        if(($oPayOrder->remit_status != BfPayOrder::STATUS_SUCCESS) || ($pay_status!=ZhanPolicy::PAY_SUCCESS)) {
            $remark = "宝付支付订单状态错误";
            $billStatus = PolicyCheckbilldetail::STATUS_FAILURE;
            $orderId = "";
        }
        $postdata = [
            'channelOrderNo'    => $channelOrderNo,
            'policyNo'          => $p_policyNo==$policyNo?$policyNo:$p_policyNo,
            'premium'           => $premium,
            'policy_premium'    => $policy_premium,
            'billDate'          => $bill_date,
            'billStatus'        => $billStatus,
            'remark'            => $remark,
            'aid'               => $aid,
            'user_name'         => $user_name,
            'user_mobile'       => $user_mobile,
            'fund'              => $fund,
            'orderId'           => $orderId,
            'remit_status'      => $remit_status,
            'pay_status'        => $pay_status,
        ];
        return $postdata;
    }
    
}
<?php
namespace app\modules\api\common\cg;

use app\common\Logger;
use app\models\Payorder;
use app\models\cg\CgOrder;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @desc 存管
 * @author fei
 */
class CCg {

    /**
     * @desc 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod102
     */
    const SMS_CHANNEL = '000002';//交易渠道  网页
    const SMS_REQTYPE = '2';   //适用于 金运通通道充值
    const SMS_SRVTXCODE = 'directRechargeOnline'; //业务交易代码 短信充值
    private static $failCode = [
        '60006','60007','99996','60001','60002','60010'
    ];

    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        // $is_prod = true;
        $cfg = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }
    /**
     * @desc 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    public function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new CgApi($cfg);
        }
        return $map[$channel_id];
    }

    /**
     * @desc 创建支付订单
     * @param  obj $oPayorder
     * @return  [res_code,res_data]
     */
    public function createOrder($oPayorder,$postData) {
        //1. 数据检测
        if (empty($oPayorder)) {
            return ['res_code' => 147001, 'res_data' => '数据不完整'];
        }
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];
        $data['loan_id'] = $postData['loan_id'];
        $data['account_id'] = $postData['account_id'];
        $data['interest_fee'] = ArrayHelper::getValue($postData,'interest_fee','0');
        $data['coupon_repay_amount']  = ArrayHelper::getValue($postData,'coupon_repay_amount','0');

        //2. 字段检查是否正确
        $cgOrder = new CgOrder();
        $result = $cgOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('cg/createOrder', '提交数据', $data, '失败原因', $cgOrder->errors);
            return ['res_code' => 147002, 'res_data' => '订单保存失败'];
        }

        //3. 返回下一步处理流程
        $res_data = $cgOrder->getPayUrls();
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    /**
     * @desc 获取绑卡信息
     * @param  [] $data
     * @return [res_code,res_data]
     */
    private function getBindBank($data) {
        $oBind = (new BfBindbank)->getSameUserCard(
            $data['aid'],
            $data['channel_id'],
            $data['identityid'],
            $data['cardno']
        );
        if ($oBind) {
            return ['res_code' => 0, 'res_data' => $oBind];
        }

        return $this->bindCard($data);
    }

    /**
     * @desc 发送短信
     * @return  int $status 
     */
    public function cgSendSms($cgInfo){
        $data = [
            'channel' => self::SMS_CHANNEL,     
            'mobile' => $cgInfo->phone, 
            'from' => $this->getSource($cgInfo->aid), 
            'reqType' => self::SMS_REQTYPE,
            'srvTxCode' => self::SMS_SRVTXCODE,
            'cardNo' => $cgInfo->cardno,
            'acqRes' => ''
        ];
        
        $oSendsms = $this->getApi($cgInfo->channel_id)->sendSms($data);
        $oSendsms = json_decode($oSendsms, true);//转为数组
        $oSendInfo = json_decode(ArrayHelper::getValue($oSendsms,'data',''), true);//转为数组
        return $oSendInfo;
    }

    /**
     * 存管来源
     * 
     **/
    public function getSource($aid){
        if(empty($aid) || !isset($aid)){
            $aid = '9';
        }
        return $aid;
    }

    /**
     * @desc 支付结果
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function confirmPay($cgInfo,$validatecode) { 
        //1. 增加状态锁定
        $result = $cgInfo->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return -1;
        }
        $cgData = [
            'loanId' => $cgInfo->loan_id, //借款ID
            'source' => $this->getSource($cgInfo->aid), //来源
            'orderId' => $cgInfo->orderid,
            'accountId' => $cgInfo->account_id,
            'idNo' => $cgInfo->payorder->idcard,
            'name' => $cgInfo->payorder->name,
            'mobile' => $cgInfo->payorder->phone,
            'cardNo' => $cgInfo->payorder->cardno,
            'txAmount' => $cgInfo->payorder->amount / 100,// 单位元
            'smsCode' => $validatecode,
            'smsSeq' => $cgInfo->smsseq,
            'coupon_repay_amount' => $cgInfo->coupon_repay_amount
        ];
        $oCgpay = $this->getApi($cgInfo->channel_id)->confirmPay($cgData);
        //2. 保存结果信息
        if(is_array($oCgpay)){
            $rsp_code = ArrayHelper::getValue($oCgpay,'rsp_code','');
            $rsp_msg = ArrayHelper::getValue($oCgpay,'rsp_msg','');
           if($rsp_code == "0000" && $rsp_msg == 'SUCCESS'){
                //成功时处理
                $cgInfo->refresh();
                $result = $cgInfo->savePaySuccess($cgInfo->orderid);
            }

            if (in_array($rsp_code, self::$failCode)) {
                // 失败时处理
                $result = $cgInfo->savePayFail($rsp_code,$rsp_msg);
            }

        }
        //3. 返回当前状态
        return $cgInfo->status;
    }


}

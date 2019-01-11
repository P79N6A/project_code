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
class CCgnew {

    /**
     * @desc 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod102
     */
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
            return ['res_code' => 150001, 'res_data' => '数据不完整'];
        }
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];
        $data['loan_id'] = $postData['loan_id'];
        $data['interest_fee'] = ArrayHelper::getValue($postData,'interest_fee','0');
        $data['account_id'] = $postData['account_id'];
        $data['coupon_repay_amount']  = ArrayHelper::getValue($postData,'coupon_repay_amount','0');
        //2. 字段检查是否正确
        $cgOrder = new CgOrder();
        $result = $cgOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('cg/createOrder', '提交数据', $data, '失败原因', $cgOrder->errors);
            return ['res_code' => 150002, 'res_data' => '订单保存失败'];
        }

        //3. 请求存管获得支付form表单
        $data['forgotPwdUrl']  = ArrayHelper::getValue($postData,'forgotPwdUrl','');//数据不存库原路返回（存管修改密码）
        $res_data = $this->reqCgpay($data);
        $rsp_code = ArrayHelper::getValue($res_data,'rsp_code','150003');
        $rsp_msg = ArrayHelper::getValue($res_data,'rsp_msg','数据异常');
        if(!is_array($res_data) || $rsp_code != '0000'){
            return ['res_code' => $rsp_code, 'res_data' => $rsp_msg];
        }
            
        $returnData = [
            'url' => $rsp_msg,
            'pay_type' => $data['channel_id'],
            'status' => 1, //1,8
            'orderid' => $data['orderid'],
        ];

        Logger::dayLog('cg/pay_new', '体内reqCgpay返回', $returnData);

        return ['res_code' => 0, 'res_data' => $returnData];
    }

    /**
     * 请求债匹支付
     */
    public function reqCgpay($data){
        $channel_id = ArrayHelper::getValue($data,'channel_id','');
        $order_id = ArrayHelper::getValue($data,'orderid','');
        $cgData = [
            'loanId' => ArrayHelper::getValue($data,'loan_id',''), //借款ID
            'interest_fee' => ArrayHelper::getValue($data,'interest_fee','0'), //手续费
            'source' => ArrayHelper::getValue($data,'aid',''), //来源
            'orderId' => $order_id,
            'accountId' => ArrayHelper::getValue($data,'account_id',''),
            'idNo' => ArrayHelper::getValue($data,'idcard',''),
            'name' => ArrayHelper::getValue($data,'name',''),
            'mobile' => ArrayHelper::getValue($data,'phone',''),
            'cardNo' => ArrayHelper::getValue($data,'cardno',''),
            'txAmount' => ArrayHelper::getValue($data,'amount',0) / 100,// 单位元
            // 'notifyUrl' => ArrayHelper::getValue($data,'callbackurl',''),
            // 'retUrl' => ArrayHelper::getValue($data,'retUrl','')
            'coupon_repay_amount' => ArrayHelper::getValue($data,'coupon_repay_amount','0'), //手续费
            'forgotPwdUrl' => ArrayHelper::getValue($data,'forgotPwdUrl',''), //存管修改密码
            'period' => ArrayHelper::getValue($data,'period', 1)
        ];
        $oCgpay = $this->getApi($channel_id)->confirmPaynew($cgData);
        // var_dump($oCgpay);die;
        
        return $oCgpay;
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

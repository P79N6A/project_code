<?php
namespace app\modules\api\common\rongbaoxy;

use app\common\Logger;
use app\models\Payorder;
use app\models\rongbao\RbxyOrder;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\ChannelBank;

/**
 * @desc 融宝协议支付
 * @author xlj
 */
class CRbxy {

    //主动查询第三方的订单状态
    const RES_PAYOK = 'completed';    #成功
    const RES_PAYWAIT = 'wait';    #等待付款
    const RES_DOING = 'processing';    #处理中
    const RES_PAYFAIL = 'failed';    #失败
    const RES_PAYCLOSE = 'closed';   #订单关闭


    //可能为处理中的状态
    private static $handleCode = [
        '1121','3081',
    ];
    /**
     * @desc 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod160
     */
    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
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
            $map[$channel_id] = new RbxyApi($cfg);
        }
        return $map[$channel_id];
    }

    /**
     * @desc 创建支付订单
     * @param  obj $oPayorder  主订单对象
     *          array $postData     数组
     * @return  [res_code,res_data]
     */
    public function createOrder($oPayorder,$postData) {
        //1. 数据检测
        if (empty($oPayorder)) {
            return ['res_code' => 16015, 'res_data' => '数据不完整'];
        }
        $data = $oPayorder->attributes;
        $data['payorder_id'] = ArrayHelper::getValue($data,'id','0');
        $data['callbackurl'] = ArrayHelper::getValue($postData,'callbackurl','0');
        $data['loan_id'] = ArrayHelper::getValue($postData,'loan_id','0');
        $data['interest_fee'] = ArrayHelper::getValue($postData,'interest_fee','0');
        $data['account_id'] = ArrayHelper::getValue($postData,'account_id','0');
        $data['coupon_repay_amount']  = ArrayHelper::getValue($postData,'coupon_repay_amount','0');

        $bank_code = (new ChannelBank())->getBankCode($data['channel_id'],ArrayHelper::getValue($data,'bankname','0'));
        if(!$bank_code){
            Logger::dayLog('rbxy/createOrder', '提交数据', $data, '失败原因:银行编码找不到');
            return ['res_code' => 16016, 'res_data' => '不支持此银行！'];
        }
        $data['card_bank_code']  = $bank_code;
        //2. 字段检查是否正确
        $oJdOrder = new RbxyOrder();
        $result = $oJdOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('rbxy/createOrder', '提交数据', $data, '失败原因', $oJdOrder->errors);
            return ['res_code' => 16017, 'res_data' => '订单保存失败'];
        }

        //3. 同步主订单状态
        $result = $oPayorder->saveStatus($oJdOrder->status);
        //4. 返回下一步处理流程
        $res_data = $oJdOrder->getPayUrls();
        Logger::dayLog('rbxy', 'getPayUrls', $res_data);
        return ['res_code' => 0, 'res_data' => $res_data];

    }

    /**
     * 签约流程--获取短信验证码
     * @param $jdInfo  object
     *  return object
     */
    public function getSendSms($oRbxyInfo){
        $paramArr = array(
            'member_id' => $oRbxyInfo->identityid,  //用户id
            'order_no' => $oRbxyInfo->orderid, //商户订单号
            'phone' => $oRbxyInfo->phone,  //手机号
            'card_no' => $oRbxyInfo->cardno,    //银行卡号
            'owner' => $oRbxyInfo->name,      //持卡人姓名
            'cert_no'=> $oRbxyInfo->idcard,     //身份证号
        );
        $result = $this->getApi($oRbxyInfo->channel_id)->getSignSms($paramArr);
        #Logger::dayLog('rbxy/rbxySendSms', '签约返回结果', $result);
        //返回结果
        return $result;
    }


    /**
     * @desc 验证签约----签约完成
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function signCheck($oRbxyInfo,$validatecode) {
        #Logger::dayLog('rbxy/confirmPay', '支付请求提交数据', $oRbxyInfo, $validatecode);
        $paramArr = array(
            'order_no' => $oRbxyInfo->orderid, //商户订单号
            'check_code' =>$validatecode,  //短信验证码
        );
        $result = $this->getApi($oRbxyInfo->channel_id)->checkSigning($paramArr);
        #Logger::dayLog('rbxy/signCheck', '签约请求返回结果', $result);
        //返回结果
        return $result;
    }

    /*--------------------------------------------------------------------*/

    /**
     * @desc 支付流程
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function confirmPay($oRbxyInfo) {
        //异步通知地址
        $trade_notice =   Yii::$app->request->hostInfo.'/rbxy/backpay?code='.$oRbxyInfo['channel_id'];
        #Logger::dayLog('rbxy/confirmPay', '支付请求提交数据', $oRbxyInfo,$trade_notice);
        //1. 增加状态锁定
        $result = $oRbxyInfo->saveStatus(Payorder::STATUS_DOING,'','');
        if (!$result) {
            Logger::dayLog('rbxy/confirmPay', '修改状态失败', $result->errors);
           # return '';
        }
        $paramArr = array(
            'member_id' => $oRbxyInfo->identityid, //用户id
            'order_no' =>$oRbxyInfo->orderid,  //商户订单号
            'transtime' =>date("YmdHis"),          //交易时间
            'total_fee' =>$oRbxyInfo->amount,       //交易金额
            'sign_no' =>$oRbxyInfo->sign_no,        //签约协议号
            'title' =>$oRbxyInfo->productname,        //商品名称
            'body' =>$oRbxyInfo->productdesc,       //商品描述
            'notify_url' =>$trade_notice,           //商户后台系统的回调地址
            'time_expire' =>'20m',                     //订单关闭时间
        );

        $result = $this->getApi($oRbxyInfo->channel_id)->rbxyPay($paramArr);
        #Logger::dayLog('rbxy/confirmPay', '支付请求返回结果', $result);
        //返回结果
        return $result;
    }

    /**
     * @desc 主动查询结果
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function rbxyQuery($oRbxyInfo) {
        $paramArr = array(
            'order_no' =>$oRbxyInfo->orderid,  //商户订单号
        );
        $result = $this->getApi($oRbxyInfo->channel_id)->rbxyOrderQuery($paramArr);
        #Logger::dayLog('jd/JdQuery', '异常订单查询结果', $result);
        return $result;
    }

    /**
     * 异步通知结果处理
     * @param $channel_id
     * @param $data
     * @return mixed
     */
    public function receiveNotice($channel_id,$data){
        $data = json_encode($data);
        $result = $this->getApi($channel_id)->decryptData($data);
        return $result;
    }



    /**
     * $desc 处理时间内异常订单
     * @return int
     */
    public function runMinute($start_time, $end_time) {
        $model = new RbxyOrder();
        $dataList =$model->getAbnorList($start_time,$end_time);
        if(empty($dataList)){
            return 0;
        }
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $jdOrder) {
                $result = $this->orderQuery($jdOrder);
                if ($result){
                    $success++;
                }
            }
        }
        Logger::dayLog('rbxy/orderQuery', '查询成功条数：', $success);
        //5 返回结果
        return $success;
    }


    /*
    * @des 融宝协议支付订单查询
    * @param $baofooOrder
    * @return bool
    */
    public function     orderQuery($rbxyOrder){
        if(!is_object($rbxyOrder)){
            return false;
        }
        //去第三方查询
        $resPay = $this->rbxyQuery($rbxyOrder);
        if(empty($resPay)){
            Logger::dayLog('rbxy/rbxyQuery','orderQuery','查询超时', $resPay);
            return false;
        }


        $rseult_code = ArrayHelper::getValue($resPay,'result_code','');     //第三方返回的code
        $payStatus = ArrayHelper::getValue($resPay,'status','');    //订单状态
        $totalFee = ArrayHelper::getValue($resPay,'total_fee',''); //交易金额

        //如果没有订单状态已code为准
        if(empty($payStatus)){
            if(in_array($rseult_code,self::$handleCode)){
                Logger::dayLog('rbxy/rbxyQuery','orderQuery','处理中', $resPay);
                return false;
            }
            if($rseult_code != '0000'){
               return $this->updateStatus('fail',$rbxyOrder,$resPay);
            }
        }

        //订单完成    completed  交易完成
        if($payStatus == self::RES_PAYOK){
            //判断订单金额
            if($totalFee != $rbxyOrder->amount){
                Logger::dayLog('rbxy/rbxyQuery','orderQuery','金额不正确', $resPay);
                return false;
            }
            return $this->updateStatus('success',$rbxyOrder,$resPay);
        }

        //订单完成    failed  支付失败  closed：订单关闭状态
        if($payStatus == self::RES_PAYFAIL || $payStatus == self::RES_PAYCLOSE){
            return $this->updateStatus('fail',$rbxyOrder,$resPay);
        }

        //订单完成    processing:交易处理中  wait：等待付款
        if($payStatus == self::RES_DOING || $payStatus == self::RES_PAYWAIT ){
            #Logger::dayLog('rbxy/rbxyQuery', '订单当前状态', $resPay);
            return false;
        }

        return false;
    }

    /**
     *  保存订单最终态
     * @param $status  success  成功   fail  失败
     * @param $rbxyOrder    订单对象
     * @param array $resPay 错误参数
     * @return bool
     */
    public function updateStatus($status,$rbxyOrder,$resPay=[]){
        if(!is_object($rbxyOrder)){
            Logger::dayLog('rbxy/rbxyQuery', 'saveStatus/参数错误');
            return false;
        }
        if($status != 'success' && $status != 'fail' ){
            return false;
        }
        if($status == 'success'){
            $result=$rbxyOrder->savePaySuccess($rbxyOrder->orderid);
            if(!$result){
                Logger::dayLog('rbxy/rbxyQuery', 'saveStatus/success同步更新订单失败', $result);
                return false;
            }
        }
        if($status == 'fail'){
            $result = $rbxyOrder->savePayFail((string)ArrayHelper::getValue($resPay,'result_code',''),ArrayHelper::getValue($resPay,'result_msg',''));
            if(!$result){
                Logger::dayLog('rbxy/rbxyQuery', 'saveStatus/fail同步更新订单失败', $result);
                return false;
            }
        }
        if(in_array($rbxyOrder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            $resnotice = $rbxyOrder->payorder->clientNotify();
            if(!$resnotice){
                Logger::dayLog('rbxy/rbxyQuery', '通知失败', $rbxyOrder->orderid);
                return false;
            }
            return true;
        }
        return false;

    }

}

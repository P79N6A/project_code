<?php
namespace app\modules\api\common\ymdxy;

use app\common\Logger;
use app\models\Payorder;
use app\models\yimadai\YmdxyOrder;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\ChannelBank;

/**
 * @desc 一麻袋协议支付
 * 2018年9月18日15:08:56
 * @author xlj
 */
class CYmdxy {

    //主动查询第三方的订单状态
    const RES_PAYOK = '1';    #成功
    const RES_DOING = '3';    #处理中
    const RES_PAYFAIL = '0';    #失败



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
     * @return YmdApi
     */
    public function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new YmdxyApi($cfg);
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


        //2. 字段检查是否正确
        $oJdOrder = new YmdxyOrder();
        $result = $oJdOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('ymdxy/createOrder', '提交数据', $data, '失败原因', $oJdOrder->errors);
            return ['res_code' => 16017, 'res_data' => '订单保存失败'];
        }

        //3. 同步主订单状态
        $result = $oPayorder->saveStatus($oJdOrder->status);
        //4. 返回下一步处理流程
        $res_data = $oJdOrder->getPayUrls();
        Logger::dayLog('ymdxy', 'getPayUrls', $res_data);
        return ['res_code' => 0, 'res_data' => $res_data];

    }

    /**
     * 签约流程--获取短信验证码
     * @param $jdInfo  object
     *  return object
     */
    public function getSendSms($oYmdxyInfo){
        $paramArr = array(
            'member_id' => $oYmdxyInfo->identityid,  //用户id
            'requestNo' => $oYmdxyInfo->cli_orderid, //商户订单号
            'phone' => $oYmdxyInfo->phone,  //手机号
            'cardNo' => $oYmdxyInfo->cardno,    //银行卡号
            'accountName' => $oYmdxyInfo->name,      //持卡人姓名
            'identifNo'=> $oYmdxyInfo->idcard,     //身份证号
            'bankName'=> $oYmdxyInfo->bankname,     //银行名称
            'cardType' => 'debit',  //银行卡类型  debit为储蓄卡
        );
        $result = $this->getApi($oYmdxyInfo->channel_id)->getSignSms($paramArr);
        //返回结果
        return $result;
    }


    /**
     * @desc 验证签约----签约完成
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function signCheck($oYmdxyInfo,$validatecode) {
        #Logger::dayLog('ymdxy/confirmPay', '支付请求提交数据', $oYmdxyInfo, $validatecode);
        $paramArr = array(
            'requestNo' => $oYmdxyInfo->cli_orderid, //商户订单号
            'msgCode' =>$validatecode,  //短信验证码
        );
        $result = $this->getApi($oYmdxyInfo->channel_id)->checkSigning($paramArr);
        #Logger::dayLog('ymdxy/signCheck', '签约请求返回结果', $result);
        //返回结果
        return $result;
    }

    /*--------------------------------------------------------------------*/

    /**
     * @desc 支付流程
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function confirmPay($oYmdxyInfo) {
        //异步通知地址
        $trade_notice =   Yii::$app->request->hostInfo.'/ymdxy/backpay?code='.$oYmdxyInfo['channel_id'];
        //1. 增加状态锁定
        $result = $oYmdxyInfo->saveStatus(Payorder::STATUS_DOING,'','');
        if (!$result) {
            Logger::dayLog('ymdxy/confirmPay', '修改状态失败', $result->errors);
           # return '';
        }
        $paramArr = array(
            'merchantOrderNo' =>$oYmdxyInfo->cli_orderid,  //商户订单号
            'amount' =>($oYmdxyInfo->amount)/100,       //交易金额
            'token' =>$oYmdxyInfo->token_no,        //签约协议号
            'notifyUrl' =>$trade_notice,           //商户后台系统的回调地址
            'products' =>$oYmdxyInfo->productname,   //'测试',                     //
            'remark' =>$oYmdxyInfo->productdesc, //'测试',                     //
        );

        $result = $this->getApi($oYmdxyInfo->channel_id)->ymdxyPay($paramArr);
        //返回结果
        return $result;
    }

    /**
     * @desc 主动查询结果
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function ymdxyQuery($oYmdxyInfo) {
        $paramArr = array(
            'order_no' =>$oYmdxyInfo->cli_orderid,  //商户订单号
        );
        $result = $this->getApi($oYmdxyInfo->channel_id)->ymdxyOrderQuery($paramArr);
        return $result;
    }

    /**
     * 异步通知结果处理--验证签名
     * @param $channel_id
     * @param $data
     * @return mixed
     */
    public function receiveNotice($channel_id,$data){
        $result = $this->getApi($channel_id)->decryptData($data);
        return $result;
    }



    /**
     * $desc 处理时间内异常订单
     * @return int
     */
    public function runMinute($start_time, $end_time) {
        $model = new YmdxyOrder();
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
        Logger::dayLog('ymdxy/orderQuery', '查询成功条数：', $success);
        //5 返回结果
        return $success;
    }


    /*
    * @des 一麻袋协议支付订单查询
    * @param $baofooOrder
    * @return bool
    */
    public function orderQuery($ymdxyOrder){
        if(!is_object($ymdxyOrder)){
            return false;
        }
        //去第三方查询
        $resPay = $this->ymdxyQuery($ymdxyOrder);
        if(empty($resPay)){
            Logger::dayLog('ymdxy/ymdxyQuery','orderQuery','查询超时', $resPay);
            return false;
        }
        if(!$resPay){
            Logger::dayLog('ymdxy/ymdxyQuery','orderQuery','查询失败,返回信息解析错误', $resPay);
            return false;
        }
        $list = ArrayHelper::getValue($resPay,'list','');   //返回的订单信息
        if(empty($list)){
            Logger::dayLog('ymdxy/ymdxyQuery','orderQuery','查询,失败list不存在', $resPay);
            return false;
        }

        $payStatus = ArrayHelper::getValue($list,'orderStatus','');    //订单状态
        $totalFee = ArrayHelper::getValue($list,'orderAmount',''); //交易金额


        //订单完成    1  交易完成
        if($payStatus == self::RES_PAYOK){
            //判断订单金额
            if(($totalFee*100) != $ymdxyOrder->amount){
                Logger::dayLog('ymdxy/ymdxyQuery','orderQuery','金额不正确', $resPay);
                return false;
            }
            return $this->updateStatus('success',$ymdxyOrder,$resPay);
        }

        //订单完成    0  支付失败
        if($payStatus == self::RES_PAYFAIL){
            return $this->updateStatus('fail',$ymdxyOrder,$resPay);
        }
        //订单完成    3:交易处理中
        if($payStatus == self::RES_DOING  ){
            return false;
        }

        return false;
    }

    /**
     *  保存订单最终态
     * @param $status  success  成功   fail  失败
     * @param $ymdxyOrder    订单对象
     * @param array $resPay 错误参数
     * @return bool
     */
    public function updateStatus($status,$ymdxyOrder,$resPay=[]){
        if(!is_object($ymdxyOrder)){
            Logger::dayLog('ymdxy/ymdxyQuery', 'saveStatus/参数错误');
            return false;
        }
        if($status != 'success' && $status != 'fail' ){
            return false;
        }
        if($status == 'success'){
            //因为主动查询字段里  没有第三方的流水号，就先用自己的代替
            $result=$ymdxyOrder->savePaySuccess($ymdxyOrder->orderid);
            if(!$result){
                Logger::dayLog('ymdxy/ymdxyQuery', 'saveStatus/success同步更新订单失败', $result);
                return false;
            }
        }
        if($status == 'fail'){
            $result = $ymdxyOrder->savePayFail((string)ArrayHelper::getValue($resPay,'result_code',''),ArrayHelper::getValue($resPay,'result_msg',''));
            if(!$result){
                Logger::dayLog('ymdxy/ymdxyQuery', 'saveStatus/fail同步更新订单失败', $result);
                return false;
            }
        }
        if(in_array($ymdxyOrder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            $resnotice = $ymdxyOrder->payorder->clientNotify();
            if(!$resnotice){
                Logger::dayLog('ymdxy/ymdxyQuery', '通知失败', $ymdxyOrder->orderid);
                return false;
            }
            return true;
        }
        return false;

    }

}

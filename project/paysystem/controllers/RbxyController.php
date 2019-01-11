<?php
/**
 * 融宝协议支付
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\Payorder;
use app\models\rongbao\RbxyOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\rongbaoxy\CRbxy;
use Yii;
use yii\helpers\ArrayHelper;



    
class RbxyController extends ApiController {

   //外部错误码 支付可能为处理中
    private static $handleCode = [
        '1121','3081',
    ];
    # 3026 余额不足

    #'1020','3100', 银行卡受限  支付的
    #2049 认证次数过多       预签约的
    #2011  2009     四要素问题  预签约的
    # 2033 请求频次过高   银行卡签约
    //外部错误码 给用户提示建议更换银行卡支付
    private static $handleCodes = [
        '1020','3100','2049','2011','2009','2033'
    ];

     //第三方异步通知订单状态码
    const RES_PAYOK = 'TRADE_FINISHED';    #成功
    const RES_PAYFAIL = 'TRADE_FAILURE';    #失败
    public function init() {

    }

    public function beforeAction($action) {
        if (in_array($action->id, ['backpay'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * 显示融宝协议请求支付链接地址
     * get  xhhorderid
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(16500, "订单不合法或信息不完整", '');
        }
        //3  获取是否存在该订单
        $rbxyInfo = $this->getRbxyOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($rbxyInfo,'res_code','-1');
        if($resCode != '0000'){
            Logger::dayLog('rbxy/Controller', ArrayHelper::getValue($rbxyInfo,'res_data','该订单异常。'),$xhhorderid);
            return $this->showMessage($resCode, ArrayHelper::getValue($rbxyInfo,'res_data','该订单异常。'));
        }
        $oRbxyInfo = ArrayHelper::getValue($rbxyInfo,'res_data','');
        
        //5  获取主订单校验
        $oPayorder = $oRbxyInfo->payorder;
        if (!$oPayorder) {
            Logger::dayLog('rbxy/Controller', '主订单异常,请联系相关人员', $oRbxyInfo);
            return $this->showMessage(16502, "主订单异常,请联系相关人员");
        }

        //7 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $oRbxyInfo->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/rbxy/getsmscode",
        ]);


    }

    /**
     * 显示结果信息
     * @param $res_code 错误码0 正确  | >0错误
     * @param $res_data      结果   | 错误原因
     */
    protected function showMessage($res_code, $res_data, $type = 'json', $redirect = null) {
        switch ($type) {
        case 'json':
            return json_encode([
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        default:
            return $this->render('/pay/showmessage', [
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        }
    }
    /**
     * @des 根据id找到对应京东订单信息
     * @param int $id
     * @return obj 
     */
    private function getRbxyOrder($id) {
        if (empty($id)) {
            return ['res_code' => 16503, 'res_data' => '订单号不存在。'];
        }
        $oRbxyModel = new RbxyOrder();
        $rbxyInfo = $oRbxyModel->getRbxyById($id);
        if (!is_object($rbxyInfo)) {
            Logger::dayLog('rbxy/Controller', '未找到订单信息', $id);
            return ['res_code' => 16503, 'res_data' => '未找到订单信息。'];
        }
        if (in_array($rbxyInfo->status,[Payorder::STATUS_PAYOK,Payorder::STATUS_PAYFAIL])) {
            Logger::dayLog('rbxy/Controller', '订单状态异常！', $rbxyInfo);
            return ['res_code' => 16503, 'res_data' => '此订单已处理，不必重复提交。'];
        }
        if(!is_object($rbxyInfo)){
            Logger::dayLog('rbxy/Controller', '数据不是对象！', $rbxyInfo);
            return ['res_code' => 16503, 'res_data' => '此订单异常。'];
        }
        return ['res_code' => '0000', 'res_data' => $rbxyInfo];
    }


    /**
     * H5页面获取验证码--------验证一下状态
     * @return string
     */
    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(16505, "信息不完整");
        }
        //2 获取是否存在该订单
        $rbxyInfo = $this->getRbxyOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($rbxyInfo,'res_code','-1');
        $resData = ArrayHelper::getValue($rbxyInfo,'res_data','该订单异常。');
        if($resCode != '0000'){
            Logger::dayLog('rbxy/Controller',$resData,$xhhorderid);
            return $this->showMessage($resCode, $resData);
        }
        $oRbxyInfo = ArrayHelper::getValue($rbxyInfo,'res_data','');

        //1.发送短信
        $oRbxy = new CRbxy();
        $result = $oRbxy->getSendSms($oRbxyInfo);

        if(empty($result)){
            $oRbxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[ArrayHelper::getValue($result,'result_code','-1'),ArrayHelper::getValue($result,'result_msg','请求超时')]);
            Logger::dayLog('rbxy/rbxySendSms', '预签约请求超时', $oRbxyInfo);
            return $this->showMessage(16505, "请稍后再试！");
        }
        if(ArrayHelper::getValue($result,'result_code') != '0000'){
            $result_code = ArrayHelper::getValue($result,'result_code','-1');
            $result_msg = ArrayHelper::getValue($result,'result_msg','预签约失败，未知原因');
            $oRbxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[$result_code,$result_msg]);
            Logger::dayLog('rbxy/rbxySendSms', '预签约错误', $result);
            #return $this->showMessage($result_code, $result_msg);
            if(in_array($result_code,self::$handleCodes)){
                return $this->showMessage(16518, "获取短信失败,请确认您的银行卡信息是否有效,建议更换银行卡重试。");
            }
            return $this->showMessage(16520, "获取短信失败！请重新发起支付");
        }
        //STATUS_NOBIND  1  已经获取验证码
        $oRbxyInfo->saveStatus(Payorder::STATUS_NOBIND,'','');
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/rbxy/paycomfirm',
        ]);
    }



    /**
     * 输入验证码确定并支付
     * @param xhhorderid
     * @param validatecode
     *
     */
    public function actionPaycomfirm() {
        //1  参数验证
        $xhhorderid = $this->post('xhhorderid');
        $validatecode = $this->post('validatecode');
        $xhhorderid = Crypt3Des::decrypt($xhhorderid, Yii::$app->params['trideskey']);
        if (empty($xhhorderid)) {
            return $this->showMessage(16008, "xhhorderid未找到");
        }
        if (empty($validatecode)) {
            return $this->showMessage(16009, "验证码未找到");
        }
        //2  获取是否存在该订单
        $rbxyInfo = $this->getRbxyOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($rbxyInfo,'res_code','-1');
        if($resCode != '0000'){
            Logger::dayLog('rbxy/Controller', ArrayHelper::getValue($rbxyInfo,'res_data','该订单异常。'),$xhhorderid);
            return $this->showMessage($resCode, ArrayHelper::getValue($rbxyInfo,'res_data','该订单异常。'));
        }
        $oRbxyInfo = ArrayHelper::getValue($rbxyInfo,'res_data','');

        //赋值验证码
        $oRbxyInfo->smscode = $validatecode;
        //4  获取主订单
        $oPayorder = $oRbxyInfo->payorder;
        if (!$oPayorder) {
            Logger::dayLog('rbxy/Controller', '主订单异常,请联系相关人员',$oRbxyInfo);
            return $this->showMessage(16011, "主订单异常,请联系相关人员");
        }

        //验证短信验证码--签约完成
        $checkResult = $this->checkSign($oRbxyInfo,$validatecode);
        $resCode = ArrayHelper::getValue($checkResult,'res_code','-1');
        if($resCode != '0000'){
            return $this->showMessage($resCode, ArrayHelper::getValue($checkResult,'res_data','支付异常。'));
        }

        //正式支付流程
        $payresult = $this->doPay($oRbxyInfo,$xhhorderid);
        $resCodePay = ArrayHelper::getValue($payresult,'res_code','-1');
        if($resCodePay != '0000'){
            return $this->showMessage($resCodePay, ArrayHelper::getValue($payresult,'res_data','支付异常。'));
        }
        $url = $oPayorder->clientBackurl();
        return $this->showMessage(0, [
            'callbackurl' => $url,
        ]);
    }

    public function checkSign($oRbxyInfo,$validatecode){
        //5 调用签约接口
        $oRbxypay = new CRbxy();
        $re_result = $oRbxypay->signCheck($oRbxyInfo,$validatecode);
        $result_code = ArrayHelper::getValue($re_result,'result_code','-1');
        $result_msg = ArrayHelper::getValue($re_result,'result_msg','签约失败，未知错误');
        //6. 只有支付成功, 支付失败状态有效
        if(empty($result_code)){
            return ['res_code' => 16569, 'res_data' => '请求超时，请稍后重试。'];
        }
       //重试返回信息
        if($result_code == '3069'){
            Logger::dayLog('rbxy/rbxySendSms', '验证码错误！', $re_result,$oRbxyInfo->orderid);
            $oRbxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[$result_code,$result_msg]);
            return ['res_code' => 16569, 'res_data' => '验证码错误，请重试'];
        }
        if($result_code== '3074' || $result_code == '1184'){
            Logger::dayLog('rbxy/rbxySendSms', '验证码已过期！', $re_result,$oRbxyInfo->orderid);
            $oRbxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[$result_code,$result_msg]);
            return ['res_code' => 16518, 'res_data' => '验证码已过期，请重新发起支付！'];
        }

        //错误返回信息
        if($result_code != '0000'){
            //因为用户看到的是支付 所有提示支付失败
            Logger::dayLog('rbxy/rbxySendSms', '银行签约错误', $re_result,$oRbxyInfo->orderid);
            $oRbxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[$result_code,$result_msg]);
            //提示用户更换银行卡支付
            if(in_array($result_code,self::$handleCodes)){
                return ['res_code' => 16533, 'res_data' => '支付失败,银行卡可能受限,建议更换银行卡重试。'];
            }
            return ['res_code' => 16534, 'res_data' => '支付失败，请重新发起支付'];
        }
        //获取签约协议号
        $sign_no = ArrayHelper::getValue($re_result,'sign_no','');
        if(empty($sign_no)){
            Logger::dayLog('rbxy/rbxySendSms', '签约协议号不存在', $re_result,$oRbxyInfo->orderid);
            return ['res_code' => 16518, 'res_data' => '支付失败，请重新发起支付'];
        }
        //更新签约号   STATUS_BIND  8   签约成功
        $updateRe = $oRbxyInfo->saveStatus(Payorder::STATUS_BIND,'',$sign_no);
        if(!$updateRe){
            Logger::dayLog('rbxy/rbxySendSms', '更新签约号失败', $re_result,$oRbxyInfo->orderid);
            return ['res_code' => 16518, 'res_data' => '支付失败，请稍后重试'];
        }
        return ['res_code' => '0000', 'res_data' => '成功'];
    }


    public function doPay($oRbxyInfo,$xhhorderid){
        $oRbxyInfo->refresh();
        //5 调用签约接口
        $oRbxypay = new CRbxy();
        $re_result = $oRbxypay->confirmPay($oRbxyInfo);
        //6. 只有支付成功, 支付失败状态有效
        $result_code = ArrayHelper::getValue($re_result,'result_code','');
        if(empty($re_result)){
            return ['res_code' => 16531, 'res_data' => '请求超时，请稍后重试。'];
        }

        //支付状态不确定  不做任何操作等等 异步通知或者补单
        if(in_array($result_code,self::$handleCode)){
            return ['res_code' => '0000', 'res_data' =>'处理中'];
        }
        //错误返回信息
        if($result_code != '0000'){
            //因为用户看到的是支付 所有提示支付失败
            $oRbxyInfo->refresh();
            $oRbxyInfo->savePayFail($result_code,ArrayHelper::getValue($re_result,'result_msg',''));
            Logger::dayLog('rbxy/rbxyPayerror', '支付错误', $re_result,$oRbxyInfo->orderid);
            if(in_array($oRbxyInfo->status,[ Payorder::STATUS_PAYFAIL])){
                //需要重新获取对象
                $oRbxyModel = new RbxyOrder();
                $oNewRbxyInfo = $oRbxyModel->getRbxyById($xhhorderid);
                $oNewRbxyInfo->payorder->clientNotify();
                return ['res_code' => '0000', 'res_data' =>'处理中'];
            }
            return ['res_code' => 16532, 'res_data' => '支付失败，请稍后重试'];
        }
        return ['res_code' => '0000', 'res_data' =>'处理中'];
    }

    /**
     * 异步通知地址
     */
    public function actionBackpay(){
        // 1、接收数据
        $merchant = $this->get('code');
        $postData['data'] = $this->post('data');
        $postData['merchant_id'] = $this->post('merchant_id');
        $postData['encryptkey'] = $this->post('encryptkey');
        Logger::dayLog('rbxy/backPay', '异步通知结果', $postData,$merchant);
        if(empty($postData) || empty($merchant)){
            echo 'resp和code都不能为空';die;
        }
        $oCRbxy = new CRbxy();
        $resultData = $oCRbxy->receiveNotice($merchant,$postData);
        Logger::dayLog('rbxy/backPay', '异步通知结果--解密', $resultData);
        if(empty($resultData)){
            echo '参数错误';die;
        }
        $res_status = ArrayHelper::getValue($resultData,'status',0);
        $res_amount = ArrayHelper::getValue($resultData,'total_fee',0);
        $orderid = ArrayHelper::getValue($resultData,'order_no',0);


        $oRbxyModel = new RbxyOrder();
        $id = $oRbxyModel->getRbxyByOrderid($orderid);
        $rbxyInfo = $oRbxyModel->getRbxyById($id);
        if(!is_object($rbxyInfo)){
            Logger::dayLog('rbxy/backPay', '该订单不存在。', $resultData);
        }

        if(in_array($rbxyInfo->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            $rbxyInfo->payorder->clientNotify();
            Logger::dayLog('rbxy/backPay', '该订单已经处理。', $orderid);
            echo 'success';die;
        }

        //TRADE_FINISHED   支付成功
        if($res_status == self::RES_PAYOK){
            if($res_amount != $rbxyInfo['amount']){
                Logger::dayLog('rbxy/backPay', '交易金额不对。', $postData);
                echo '交易金额不对';die;
            }

            $result = $rbxyInfo->savePaySuccess($rbxyInfo->orderid);
            //成功时处理
            if(!$result){
                Logger::dayLog('rbxy/backPay', '同步更新订单失败', $result);
                echo '同步更新订单失败';die;
            }

        }
        // TRADE_FAILURE    支付失败
        if($res_status == self::RES_PAYFAIL){
            $res_code = ArrayHelper::getValue($resultData,'result_code',0);
            $res_msg =  ArrayHelper::getValue($resultData,'result_msg',0);
            $result = $rbxyInfo->savePayFail($res_code,$res_msg);
            if(!$result){
                Logger::dayLog('rbxy/orderQuery', '同步更新订单失败', $result);
                echo '同步更新订单失败';die;
            }
        }
        $resnotice = $rbxyInfo->payorder->clientNotify();
        if(!$resnotice){
            Logger::dayLog('rbxy/backPay', 'Backpay/通知失败', $orderid);
            echo '通知失败';die;
        }
        Logger::dayLog('rbxy/backPay', '该订单处理成功。订单号：', $orderid);
        echo 'success';die;
    }


}

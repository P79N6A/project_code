<?php
/**
 * 一麻袋协议支付
 * 2018年9月18日15:02:31
 * xlj
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\Payorder;
use app\models\yimadai\YmdxyOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\ymdxy\CYmdxy;
use Yii;
use yii\helpers\ArrayHelper;



    
class YmdxyController extends ApiController {

   //外部错误码 支付可能为处理中
    private static $handleCode = [
        'ERR1032',
    ];

     //第三方异步通知订单状态码
    const RES_PAYOK = '88';    #成功  其他都失败
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
     * 显示协一麻袋议请求支付链接地址
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
        $ymdxyInfo = $this->getOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($ymdxyInfo,'res_code','-1');
        if($resCode != '0000'){
            Logger::dayLog('ymdxy/Controller', ArrayHelper::getValue($ymdxyInfo,'res_data','该订单异常。'),$xhhorderid);
            return $this->showMessage($resCode, ArrayHelper::getValue($ymdxyInfo,'res_data','该订单异常。'));
        }
        $oYmdxyInfo = ArrayHelper::getValue($ymdxyInfo,'res_data','');
        
        //5  获取主订单校验
        $oPayorder = $oYmdxyInfo->payorder;
        if (!$oPayorder) {
            Logger::dayLog('ymdxy/Controller', '主订单异常,请联系相关人员', $oYmdxyInfo);
            return $this->showMessage(16502, "主订单异常,请联系相关人员");
        }

        //7 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $oYmdxyInfo->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/ymdxy/getsmscode",
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
     * @des 根据id找到对应订单信息
     * @param int $id
     * @return obj 
     */
    private function getOrder($id) {
        if (empty($id)) {
            return ['res_code' => 16503, 'res_data' => '订单号不存在。'];
        }
        $oYmdxyModel = new YmdxyOrder();
        $ymdxyInfo = $oYmdxyModel->getById($id);
        if (!is_object($ymdxyInfo)) {
            Logger::dayLog('ymdxy/Controller', '未找到订单信息', $id);
            return ['res_code' => 16503, 'res_data' => '未找到订单信息。'];
        }
        if (in_array($ymdxyInfo->status,[Payorder::STATUS_PAYOK,Payorder::STATUS_PAYFAIL])) {
            Logger::dayLog('ymdxy/Controller', '订单状态异常！', $ymdxyInfo);
            return ['res_code' => 16503, 'res_data' => '此订单已处理，不必重复提交。'];
        }
        if(!is_object($ymdxyInfo)){
            Logger::dayLog('ymdxy/Controller', '数据不是对象！', $ymdxyInfo);
            return ['res_code' => 16503, 'res_data' => '此订单异常。'];
        }
        return ['res_code' => '0000', 'res_data' => $ymdxyInfo];
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
        $ymdxyInfo = $this->getOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($ymdxyInfo,'res_code','-1');
        $resData = ArrayHelper::getValue($ymdxyInfo,'res_data','该订单异常。');
        if($resCode != '0000'){
            Logger::dayLog('ymdxy/Controller',$resData,$xhhorderid);
            return $this->showMessage($resCode, $resData);
        }
        $oYmdxyInfo = ArrayHelper::getValue($ymdxyInfo,'res_data','');

        //1.发送短信
        $oYmdxy = new CYmdxy();
        $result = $oYmdxy->getSendSms($oYmdxyInfo);
        $result = json_decode($result,true);
        if(empty($result)){
            $oYmdxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[ArrayHelper::getValue($result,'code','-1'),ArrayHelper::getValue($result,'message','请求超时')]);
            Logger::dayLog('ymdxy/ymdxySendSms', '预签约请求超时', $oYmdxyInfo);
            return $this->showMessage(16505, "请稍后再试！");
        }

        if(ArrayHelper::getValue($result,'code') != 'SUCCESS'){
            $result_code = ArrayHelper::getValue($result,'code','-1');
            $result_msg = ArrayHelper::getValue($result,'message','预签约失败，未知原因');
            $oYmdxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[$result_code,$result_msg]);
            Logger::dayLog('ymdxy/ymdxySendSms', '预签约错误', $result);
            return $this->showMessage(16520, "获取短信失败！请重新发起支付");
        }
        //STATUS_NOBIND  1  已经获取验证码
        $oYmdxyInfo->saveStatus(Payorder::STATUS_NOBIND,'','');
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/ymdxy/paycomfirm',
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
        $ymdxyInfo = $this->getOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($ymdxyInfo,'res_code','-1');
        if($resCode != '0000'){
            Logger::dayLog('ymdxy/Controller', ArrayHelper::getValue($ymdxyInfo,'res_data','该订单异常。'),$xhhorderid);
            return $this->showMessage($resCode, ArrayHelper::getValue($ymdxyInfo,'res_data','该订单异常。'));
        }
        $oYmdxyInfo = ArrayHelper::getValue($ymdxyInfo,'res_data','');

        //赋值验证码
        $oYmdxyInfo->smscode = $validatecode;
        //4  获取主订单
        $oPayorder = $oYmdxyInfo->payorder;
        if (!$oPayorder) {
            Logger::dayLog('ymdxy/Controller', '主订单异常,请联系相关人员',$oYmdxyInfo);
            return $this->showMessage(16011, "主订单异常,请联系相关人员");
        }

        //验证短信验证码--签约完成
        $checkResult = $this->checkSign($oYmdxyInfo,$validatecode);
        $resCode = ArrayHelper::getValue($checkResult,'res_code','-1');
        if($resCode != '0000'){
            return $this->showMessage($resCode, ArrayHelper::getValue($checkResult,'res_data','支付异常。'));
        }

        //正式支付流程
        $payresult = $this->doPay($oYmdxyInfo,$xhhorderid);
        $resCodePay = ArrayHelper::getValue($payresult,'res_code','-1');
        if($resCodePay != '0000'){
            return $this->showMessage($resCodePay, ArrayHelper::getValue($payresult,'res_data','支付异常。'));
        }
        $url = $oPayorder->clientBackurl();
        return $this->showMessage(0, [
            'callbackurl' => $url,
        ]);
    }

    public function checkSign($oYmdxyInfo,$validatecode){
        //5 调用签约接口
        $oYmdxypay = new CYmdxy();
        $re_result = $oYmdxypay->signCheck($oYmdxyInfo,$validatecode);
        $re_result = json_decode($re_result,true);
        $result_code = ArrayHelper::getValue($re_result,'code','-1');
        $result_msg = ArrayHelper::getValue($re_result,'message','签约失败，未知错误');
        //6. 只有支付成功, 支付失败状态有效
        if(empty($result_code)){
            Logger::dayLog('ymdxy/ymdxySendSms', '请求超时，请稍后重试',$oYmdxyInfo->orderid);
            return ['res_code' => 16569, 'res_data' => '请求超时，请稍后重试。'];
        }

        //错误返回信息
        if($result_code != 'SUCCESS'){
            //因为用户看到的是支付 所有提示支付失败
            Logger::dayLog('ymdxy/ymdxySendSms', '银行签约错误', $re_result,$oYmdxyInfo->orderid);
            $oYmdxyInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[$result_code,$result_msg]);
            //提示用户更换银行卡支付
//            if(in_array($result_code,self::$handleCodes)){
//                return ['res_code' => 16533, 'res_data' => '支付失败,银行卡可能受限,建议更换银行卡重试。'];
//            }
            return ['res_code' => 16534, 'res_data' => '支付失败，请重新发起支付'];
        }
        //获取签约协议号
        $token_no = ArrayHelper::getValue($re_result,'token','');
        if(empty($token_no)){
            Logger::dayLog('ymdxy/ymdxySendSms', '签约协议号不存在', $re_result,$oYmdxyInfo->orderid);
            return ['res_code' => 16518, 'res_data' => '支付失败，请重新发起支付'];
        }
        //更新签约号   STATUS_BIND  8   签约成功
        $updateRe = $oYmdxyInfo->saveStatus(Payorder::STATUS_BIND,'',$token_no);
        if(!$updateRe){
            Logger::dayLog('ymdxy/ymdxySendSms', '更新签约号失败', $re_result,$oYmdxyInfo->orderid);
            return ['res_code' => 16518, 'res_data' => '支付失败，请稍后重试'];
        }
        return ['res_code' => '0000', 'res_data' => '成功'];
    }


    public function doPay($oYmdxyInfo,$xhhorderid){
        $oYmdxyInfo->refresh();
        //5 调用签约接口
        $oYmdxypay = new CYmdxy();
        $re_result = $oYmdxypay->confirmPay($oYmdxyInfo);
        $re_result = json_decode($re_result,true);
        //6. 只有支付成功, 支付失败状态有效
        $result_code = ArrayHelper::getValue($re_result,'code','');
        if(empty($re_result) || $result_code =='ERR1002'){
            return ['res_code' => 16531, 'res_data' => '请求超时，请稍后重试。'];
        }

        //支付状态不确定  不做任何操作等等 异步通知或者补单
        if(in_array($result_code,self::$handleCode)){
            return ['res_code' => '0000', 'res_data' =>'处理中'];
        }
        //错误返回信息
        if($result_code != 'SUCCESS'){
            //因为用户看到的是支付 所有提示支付失败
            $oYmdxyInfo->refresh();
            $oYmdxyInfo->savePayFail($result_code,ArrayHelper::getValue($re_result,'message','未知错误'));
            Logger::dayLog('ymdxy/ymdxyPayerror', '支付错误', $re_result,$oYmdxyInfo->orderid);
            if(in_array($oYmdxyInfo->status,[ Payorder::STATUS_PAYFAIL])){
                //需要重新获取对象
                $oYmdxyModel = new YmdxyOrder();
                $oNewYmdxyInfo = $oYmdxyModel->getById($xhhorderid);
                $oNewYmdxyInfo->payorder->clientNotify();
                return ['res_code' => '0000', 'res_data' =>'处理中'];
            }
            return ['res_code' => 16532, 'res_data' => '支付失败，请稍后重试'];
        }

        if($result_code == 'SUCCESS'){
            $oYmdxyInfo->refresh();
            $oYmdxyInfo->savePaySuccess(ArrayHelper::getValue($re_result,'systemNo',''));
            if(in_array($oYmdxyInfo->status,[ Payorder::STATUS_PAYOK])){
                //需要重新获取对象
                $oYmdxyModel = new YmdxyOrder();
                $oNewYmdxyInfo = $oYmdxyModel->getById($xhhorderid);
                $oNewYmdxyInfo->payorder->clientNotify();
                return ['res_code' => '0000', 'res_data' =>'支付成功'];
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
        $postData =  $this->post();
        Logger::dayLog('ymdxy/backPay', '异步通知结果', $postData,$merchant);
        if(empty($postData) || empty($merchant)){
            echo 'postData和code都不能为空';die;
        }

        $Succeed = ArrayHelper::getValue($postData,'Succeed','');  //状态码   88为成功  其他失败
        $Result = ArrayHelper::getValue($postData,'Result',0); //状态详情
        $Amount = ArrayHelper::getValue($postData,'Amount',0);  //金额
        $OrderNo = ArrayHelper::getValue($postData,'OrderNo',0); //一麻袋流水号
        $SignInfo = ArrayHelper::getValue($postData,'SignInfo',0);   //签名
        $BillNo = ArrayHelper::getValue($postData,'BillNo','');  //订单号
        $MerNo = ArrayHelper::getValue($postData,'MerNo','');  //商户号

        if(empty($Succeed) || empty($BillNo) || empty($MerNo) || empty($SignInfo)){
            Logger::dayLog('ymdxy/backPay', '异步通知数据不完成', $postData);
        }
        $oCYmdxy = new CYmdxy();
        //验证签名
        $resultData = $oCYmdxy->receiveNotice($merchant,$postData);
        if(!$resultData){
            Logger::dayLog('ymdxy/backPay', '异步通知结果--验签未通过', $resultData);
            echo '参数错误';die;
        }

        $oYmdxyModel = new YmdxyOrder();
        $id = $oYmdxyModel->getByOrderid($BillNo);
        $ymdxyInfo = $oYmdxyModel->getById($id);
        if(!is_object($ymdxyInfo)){
            Logger::dayLog('ymdxy/backPay', '该订单不存在。', $postData);
        }

        if(in_array($ymdxyInfo->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            $ymdxyInfo->payorder->clientNotify();
            Logger::dayLog('ymdxy/backPay', '该订单已经处理。', $BillNo);
            echo 'ok';die;
        }

        //88  支付成功
        if($Succeed == self::RES_PAYOK){
            if(($Amount*100) != $ymdxyInfo['amount']){
                Logger::dayLog('ymdxy/backPay', '交易金额不对。', $postData);
                echo '交易金额不对';die;
            }

            $result = $ymdxyInfo->savePaySuccess($OrderNo);
            //成功时处理
            if(!$result){
                Logger::dayLog('ymdxy/backPay', '同步更新订单失败', $result);
                echo '同步更新订单失败';die;
            }

        }
        //88以外    支付失败
        if($Succeed != self::RES_PAYOK){
            $result = $ymdxyInfo->savePayFail($Succeed,$Result);
            if(!$result){
                Logger::dayLog('ymdxy/orderQuery', '同步更新订单失败', $result);
                echo '同步更新订单失败';die;
            }
        }
        $resnotice = $ymdxyInfo->payorder->clientNotify();
        if(!$resnotice){
            Logger::dayLog('ymdxy/backPay', 'Backpay/通知失败', $BillNo);
            echo '通知失败';die;
        }
        Logger::dayLog('ymdxy/backPay', '该订单处理成功。订单号：', $BillNo);
        echo 'ok';die;
    }


}

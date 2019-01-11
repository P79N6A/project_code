<?php
/**
 *  宝付快捷支付路由地址
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\App;
use app\models\Payorder;
use app\models\baofoo\BfAuthOrder;
use app\models\StdError;
use app\modules\api\common\ApiController;
use app\modules\api\common\baofoo\CBaofooAuth;
use app\modules\api\common\baofoo\CBack;
use Yii;

    
class BfauthController extends ApiController {

    private $cid = [113,114,123,124];

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
     * 显示宝付请求绑卡链接地址
     * xhhorderid
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return StdError::returnStdErrorJson("107","0001");
            //return $this->showMessage(10200, "订单不合法或信息不完整", '');
        }
        //3  获取是否存在该订单
        $bfauthDetail = $this->getBfauthOrder($xhhorderid);

        if (!is_object($bfauthDetail)) {
            return StdError::returnStdErrorJson("107","0003");
            //return $this->showMessage(10201, $this->errinfo, '');
        }
        //4 状态检测
        if(!in_array($bfauthDetail->status,[Payorder::STATUS_PREDO,Payorder::STATUS_BIND])){
            return StdError::returnStdErrorJson("107","0005");
            //return $this->showMessage(140203, "订单状态有误,请联系相关人员");
        }
        //5  获取主订单校验
        $oPayorder = $bfauthDetail->payorder;
        if (!$oPayorder) {
            return StdError::returnStdErrorJson("107","0006");
            //return $this->showMessage(140204, "主订单异常,请联系相关人员");
        }
        // //6 请求预支付接口
        // $oBfauth = new CBaofooAuth;
        // $status = $oBfauth->prepPay($bfauthDetail);

        //7 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $bfauthDetail->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/bfauth/getsmscode",
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
     * @des 根据id找到对应宝付订单信息
     * @param int $id
     * @return obj 
     */
    private function getBfauthOrder($id) {
        if (empty($id)) {
            return StdError::returnStdErrorJson("107","0002");
            //return $this->showMessage(140207, "订单号不存在");
        }
        $bfauthModel = new BfAuthOrder();
        $bfauthDetail = $bfauthModel->getByBfauthId($id);
        if (!is_object($bfauthDetail)) {
            return StdError::returnStdErrorJson("107","0003");
            //return $this->showMessage(140208, "未找到订单信息");
        }
        if (in_array($bfauthDetail->status,[Payorder::STATUS_PAYOK,Payorder::STATUS_PAYFAIL])) {
            return StdError::returnStdErrorJson("107","0004");
            //return $this->showMessage(140209, "此订单已处理，不必重复提交");
        }
        return $bfauthDetail;
    }


    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        if (!isset($xhhorderid) || !$xhhorderid) {
            return StdError::returnStdErrorJson("107","0001");
            //return $this->showMessage(140210, "信息不完整");
        }
        //2 获取是否存在该订单
        $bfauthDetail = $this->getBfauthOrder($xhhorderid);
        if (!is_object($bfauthDetail)) {
            return StdError::returnStdErrorJson("107","0003");
            //return $this->showMessage(140211, $this->errinfo);
        }

        if (!in_array($bfauthDetail->status,[Payorder::STATUS_PREDO,Payorder::STATUS_BIND])) {
            return StdError::returnStdErrorJson("107","0005");
            //return $this->showMessage(140212, "此订单状态错误!无法完成操作");
        }

        return $this->requestSms($bfauthDetail);
    }
 


    /**
     * 发送短信程序
     */
    private function requestSms($bfauthDetail) {
        //发送短信
        $result = $bfauthDetail->payorder->requestSms();
        if (!$result) {
            //return StdError::returnStdErrorJson("107","0010");
            return $this->showMessage(10702, $bfauthDetail->payorder->errinfo);
        }
        //6 请求预支付接口
        $oBfauth = new CBaofooAuth;
        $status = $oBfauth->prepPay($bfauthDetail);
        if($status != Payorder::STATUS_PREDO){
            $errorInfo = StdError::returnThirdStdError($bfauthDetail->channel_id,$bfauthDetail->error_code,$bfauthDetail->error_msg);
            return $this->showMessage($errorInfo['res_code'],$errorInfo['res_data']);
        }
        //返回结果
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/bfauth/paycomfirm',
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
            return StdError::returnStdErrorJson("107","0002");
            //return $this->showMessage(140210, "$xhhorderid未找到");
        }
        if (empty($validatecode)) {
            return StdError::returnStdErrorJson("107","0007");
            //return $this->showMessage(140211, "smscode未找到");
        }
        //2  获取是否存在该订单
        $bfauthDetail = $this->getBfauthOrder($xhhorderid);
        if (!is_object($bfauthDetail)) {
            return StdError::returnStdErrorJson("107","0003");
            //return $this->showMessage(140212, $this->errinfo);
        }
        //3 检测是不是未处理状态
        if ($bfauthDetail->status != Payorder::STATUS_PREDO) {
            return StdError::returnStdErrorJson("107","0005");
            //return $this->showMessage(140213, " 订单状态异常,请联系相关人员");
        }
        //4  获取主订单
        $oPayorder = $bfauthDetail->payorder;
        if (!$oPayorder) {
            return StdError::returnStdErrorJson("107","0006");
            //return $this->showMessage(140214, "主订单异常,请联系相关人员");
        }
        if ($validatecode != $oPayorder->smscode) {
            return StdError::returnStdErrorJson("107","0008");
            //return $this->showMessage(10207, "验证码错误");
        }
        //5 调用确定支付接口
        $oBfauth = new CBaofooAuth;
        $status = $oBfauth->confirmPay($bfauthDetail,$validatecode);

        //6. 只有支付成功, 支付失败状态有效
        if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])) {
            //return $this->showMessage(0, '操作成功');
            //7 异步通知客户端
            $oPayorder->refresh();
            $result = $oPayorder->clientNotify();

            $url = $oPayorder->clientBackurl();
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        }elseif($status == Payorder::STATUS_DOING){
            $url = $oPayorder->clientBackurl();
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        }else {
            return StdError::returnStdErrorJson("107","0009");
            //return $this->showMessage(140215, "订单处理失败");
        }
    }

    /**
     * 支付异步通知接口
     */
    public function actionBackpay($cid = '') {

        if(!isset($cid) || !$cid || !in_array($cid, $this->cid)){
            return StdError::returnStdErrorJson('100','0021');
        }
        //1.获取参数
        $dataContent = $this->getParam('data_content');
        //本地测试
        // if (!$dataContent && defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
        //     $dataContent = $this->testBackpay();
        // }
        //2 解析数据
        $resData = (new CBaofooAuth)->getApi($cid)->decryptData($dataContent);
        Logger::dayLog('bfauthback', "数据", $dataContent, $resData);
        if(!$resData){
            //return $this->showMessage(140413, '解密数据失败');
            return StdError::returnStdErrorJson($cid,"0020");
        }
        //参数校验
        if (!is_array($resData) || !isset($resData['trans_id'])) {
            //return $this->returnError(false, '参数不合法');
            return StdError::returnStdErrorJson($cid,"0021");
        }
        $trans_id = $resData['trans_id'];
        if (!$trans_id) {
            //return $this->returnError(false, '宝付订单号不能为空');
            return StdError::returnStdErrorJson($cid,"0002");
        }
        //2 获取订单
        $bfauthModel = new BfAuthOrder;
        $oBfOrder = $bfauthModel->getByCliOrderId($trans_id);
        if (!$oBfOrder) {
            return StdError::returnStdErrorJson($cid,"0003");
            //return $this->returnError(false, '未找到该订单');
        }
        $succ_amt = $resData['succ_amt'];
        if($succ_amt <= 0 && $succ_amt != $oBfOrder->amount){
            Logger::dayLog('bfauthback', "金额不一致", $succ_amt, $oBfOrder->amount);
            return StdError::returnStdErrorJson($cid,"0023");
            //return $this->returnError(false, '交易金额有误');
        }
        $is_finished = $oBfOrder->is_finished();
        if($is_finished){
            return 'OK';
            //return StdError::returnStdErrorJson("107","0004");
        }
        //3 保存状态
        $oCBack = new CBack;
        $result = $oCBack->backpay($oBfOrder,$resData);
        //4 输出结果
        if (!$result) {
            Logger::dayLog('bfauth', 'bfauth/backpay','异步回调保存状态失败');
            return StdError::returnStdErrorJson($cid,"0024");
        }
        echo 'OK';
        //5 异步通知客户端
        $result = $oCBack->clientNotify($oBfOrder);
        if (!$result) {
            Logger::dayLog('bfauth', 'bfauth/clientNotify','异步回调通知客户端失败');
        }
    }

    /**
     * 测试桩
     */
    private function testBackpay()
    {
    return '98d3cc198400137ecb8550ea770a738947c929c5e35c8945245f63db13b064e3874c51ddc3d5effbbd3c63e9e4413e584b718c411e1277759fc57350bb57d8d68d5b6cf5f6eee3ce44fcfa4deb4bab0e4b1fae1ef9361d9d9d485ff4c2d9d8aba33fffd2c4fb9a45f158bc260fd78cb30050d0f6f16798317eb7bf76bfe08be08921adaebd971edebaa66550bc32ac42ba737b35a14ad781ab06c1416aa317ca6c4b85a896d6ed4d9dfd56d7f68cbca1597dfdc776b5eafe5aec1b3292682b864f6633f282ca86c007ef2a671dbf387995f83ecaea0b9c308cf48385c5a0919ce3494d0205cc8a7746099900d55943da95e4be03176e9d6da8733cb65b09f7b488ca559be2f0e3116e678679ec3b7d338ff46a61deb8fd0e983ace86b1517416ad19728ed422c19e39364448957d1d6fccbe09f77b71b49afe7f46b1dc3d6b5e4763441beb5f2c9b3e9067a847e4eca939c16644b573cf900add2c97ebdf3f0e26875feeb2f1f543e5e194e542d87f3c98c984ebfc12f56d1ff3cca8c6dbc13c';      
    }

}

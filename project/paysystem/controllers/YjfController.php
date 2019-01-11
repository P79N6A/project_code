<?php
/**
 * @desc 易极付支付
 */
namespace app\controllers;
use app\modules\api\common\yjf\CYjf;
use app\modules\api\common\yjf\YjfApi;
use app\common\Logger;
use app\models\yjf\YjfOrder;
use app\models\StdError;
use app\models\Payorder;
use app\models\BindBank;
use app\models\yjf\YjfBindbank;
use Yii;

class YjfController extends BaseController {

    public $layout = false;
    private $oCYjf; 
    private $channel_id = 111;
    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $env = SYSTEM_PROD ? 'prod' : 'dev';
        $this->oCYjf = new CYjf($env);
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['backpay','returnurl'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * 支付链接地址
     * @return html
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid', '');
        $yjf_id = (new YjfOrder)->decryptId($cryid);
        if (!$yjf_id) {
             return StdError::returnStdErrorJson($this->channel_id,"0001");
        }
        //2  获取是否存在该订单
        $oYjfOrder = (new YjfOrder)->getById($yjf_id);
        if (!is_object($oYjfOrder)) {
            return StdError::returnStdErrorJson($this->channel_id,"0003");
        }
        $oPayOrder = $oYjfOrder->payorder;

        //3 输出
        $this->layout = false;
        return $this->render('payurl', [
            'oPayorder' => $oPayOrder,
            'xhhorderid' => $cryid,
            'smsurl' => "/yjf/getsmscode",
        ]);
 
    }
    /**
     * 判断 是绑定还是 支付
     * 绑定：自己发短信
     * 未绑定：请求绑定，易极付返回验证码
     */
    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid', '');
        $yjf_id = (new YjfOrder)->decryptId($cryid);
        if (!$yjf_id) {
            //订单不合法或信息不完整
            return StdError::returnStdErrorJson($this->channel_id,"0001");
        }

        //2  获取是否存在该订单
        $oYjfOrder = (new YjfOrder)->getById($yjf_id);
        if (!$oYjfOrder) {
             //订单不存在
            return StdError::returnStdErrorJson($this->channel_id,"0002");
        }
        //3 获取主订单
        $oPayorder = $oYjfOrder->payorder;
        if (!$oPayorder) {
            //主订单异常,请联系相关人员
            return StdError::returnStdErrorJson($this->channel_id,"0006");
        }
        //4 没有绑定的话 调用易极付四要素鉴权接口 易极付发送短信验证码
        if ($oYjfOrder->status != Payorder::STATUS_BIND) {
            $res = $this->oCYjf->verifyBankCard($oYjfOrder);
            if(empty($res)){
                //请从新获取验证码
                return StdError::returnStdErrorJson($this->channel_id,"0018");
            }
            if($res['res_code']!='0'){
                return $this->showMessage($res['res_code'], $res['res_data']);
            }
        }else{
            //系统发送短信
            $result = $oPayorder->requestSms();
            if (!$result) {
                return StdError::returnStdErrorJson($this->channel_id,"0010");
            }
        }
        //返回结果
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/yjf/paycomfirm',
        ]);
    }
    /**
     * 输入验证码确定并支付
     * @param xhhorderid
     * @param validatecode
     *
     * 成功回调客户端
     */
    public function actionPaycomfirm() {
        //1  参数验证
        $xhhorderid = $this->post('xhhorderid');
        $validatecode = $this->post('validatecode');
        $yjf_id = (new YjfOrder)->decryptId($xhhorderid);
        if (empty($yjf_id)) {
            return StdError::returnStdErrorJson($this->channel_id,"0002");
        }
        if (empty($validatecode)) {
            return StdError::returnStdErrorJson($this->channel_id,"0007");
        }
        //2  获取是否存在该订单
        $oYjfOrder = (new YjfOrder)->getById($yjf_id);
        if (!$oYjfOrder) {
             //订单不存在
            return StdError::returnStdErrorJson($this->channel_id,"0002");
        }
        //3 如果是支付中状态 或者支付成功支付失败
        if (in_array($oYjfOrder->status,array(Payorder::STATUS_DOING,Payorder::STATUS_PAYFAIL,Payorder::STATUS_PAYFAIL))) {
            return StdError::returnStdErrorJson($this->channel_id,"0004");
        }
        //4  获取主订单, 短信验证码检测
        $oPayorder = $oYjfOrder->payorder;
        if (!$oPayorder) {
            //主订单异常,请联系相关人员
            return StdError::returnStdErrorJson($this->channel_id,"0006");
        }
        if ($oYjfOrder->status == Payorder::STATUS_NOBIND) {

            //5 调用易极付接口确认验证码是否正确
            
            $res = $this->oCYjf->smsVerifyCodeCheck($validatecode,$oYjfOrder);
            if(empty($res)){
                return StdError::returnStdErrorJson($this->channel_id,"0016");
            }
            //6 处理绑卡结果
            $oBind = (new YjfBindbank)->saveRspStatus($oYjfOrder->bind_id,$res);
            if(!$oBind){
                  return StdError::returnStdErrorJson($this->channel_id,"0019");
            }
            if($oBind->status!=YjfBindbank::STATUS_BINDOK){
                 return $this->showMessage($oBind->error_code, $oBind->error_msg);
            }
            //7 更新订单绑定状态
            $result = $oYjfOrder->savePayBind();
            if(!$result){
                //更新订单信息失败
                return StdError::returnStdErrorJson($this->channel_id,"0013");
            }
            
        }else if($oYjfOrder->status == Payorder::STATUS_BIND){
            if ($validatecode != $oPayorder->smscode) {
                return StdError::returnStdErrorJson($this->channel_id,"0008");
            }
        }
        //9 调用支付接口
        $res = $this->oCYjf->pay($oYjfOrder);
        if($res['res_code']!='0'){
            $oYjfOrder->savePayFail($res['res_code'],$res['res_data']);
        }
        $url = $oPayorder->clientBackurl();
        return $this->showMessage($res['res_code'], [
            'callbackurl' => $url,
        ]);
    }

    /**
     * 支付异步通知接口
     */
    public function actionBackpay($cfg = 'dev') {
        //1 数据获取
        $postdata = Yii::$app->request->post();
        Logger::dayLog('yjf', 'jyf/backpay',$cfg, $postdata);
         // 无响应时不处理
        if (empty($postdata)) {
            exit;
        }
        $result = (new YjfApi($cfg))->verify($postdata);
        Logger::dayLog('yjf','yjf/backpay', '验签结果',$result);
        if(empty($result)){
            Logger::dayLog('yjf','yjf/backpay', '验签失败',$postdata);
           // exit;
        }
        $orderNo = isset($postdata['merchOrderNo'])?$postdata['merchOrderNo']:'';
        $other_orderid = isset($postdata['orderNo'])?$postdata['orderNo']:'';
        $serviceStatus = isset($postdata['serviceStatus'])?$postdata['serviceStatus']:'';
        $resultMessage = isset($postdata['resultMessage'])?$postdata['resultMessage']:'';
        $transAmount = isset($postdata['transAmount'])?$postdata['transAmount']:'';
        //4 根据易极付返回的订单号检查本数据库是否存在
        $oYjfOrder = (new YjfOrder)->getByCliOrderId($orderNo);
        Logger::dayLog('yjf','yjf/backpay', 'oYjfOrder',$oYjfOrder->attributes);
        if (empty($oYjfOrder)) {
            Logger::dayLog('yjf','yjf/backpay', 'orderid not found', $orderNo);
            exit;
        }
        if (($oYjfOrder->amount/100)!=$transAmount) {
            Logger::dayLog('yjf','yjf/backpay', '订单回执金额与订单金额不同', $transAmount);
            exit;
        }
        //var_dump($oYjfOrder->is_finished());die;
        //5  若状态已经更新成功了，则无需要再更新
        if ( !$oYjfOrder->is_finished() ) {
            // == 1表示支付成功
            if ($serviceStatus == 'WITHHOLD_SUCCESS') {
                $result = $oYjfOrder->savePaySuccess($other_orderid);
                Logger::dayLog('yjf','yjf/backpay', 'savePaySuccess/保存结果', $result);
                if (!$result) {
                    Logger::dayLog('yjf','yjf/backpay', 'savePaySuccess/保存失败', $orderNo);
                    exit;
                }
            }else if($serviceStatus == 'WITHHOLD_FAIL'){
                $result = $oYjfOrder->savePayFail($serviceStatus,$resultMessage);
                 Logger::dayLog('yjf','yjf/backpay', 'savePayFail/保存结果', $result);
                if (!$result) {
                    Logger::dayLog('yjf','yjf/backpay', 'Payfail/订单状态修改失败',$orderNo);
                    exit;
                }
            }
        }

        //6 回调客户端
        $result = $oYjfOrder->payorder->clientNotify();
        if ($result) {
            echo 'SUCCESS';
            exit;
        }
    }
}

<?php
/**
 * 内部错误码范围1050000-1050099
 * 1050000 代表成功 其他均为失败
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\Payorder;
use app\models\rongbao\RongbaoOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\rongbao\CRongbao;
use app\modules\api\common\rongbao\RongbaoApi;
use Yii;

class RongpayController extends ApiController {
    /**
     * 易宝投资通
     */
    private $yeepay;

    //支付处理中状态码
    private $rbHandingCode = [
          '3029','3081','3134','3136'
    ];

    public function init() {

    }

    public function beforeAction($action) {
        if (in_array($action->id, ['notify',])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
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
            return $this->render('showmessage', [
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        }
    }
    
    public function actionTest(){
        $str = Crypt3Des::encrypt((string) '113', Yii::$app->params['trideskey']);
        $id  = Crypt3Des::decrypt($str, Yii::$app->params['trideskey']);
        echo $id;die;
        
        
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(1050050, "订单不合法或信息不完整", '');
        }

        //3  获取是否存在该订单
        $oRongOrder = (new RongbaoOrder)->getByRongId($xhhorderid);
        if (!$oRongOrder) {
            return $this->showMessage(1050051, '此订单不存在');
        }
        
        
        $params['member_id'] = $oRongOrder -> identityid;
        $params['bind_id'] = '14439820';
        $params['order_no']= $oRongOrder -> cli_orderid; //商户生成的唯一订单号
        $model = new CRongbao();
        $oRongOrder = $model->certificate($oRongOrder);
        var_dump($oRongOrder);die;
    }

    
    /**
     * 显示融宝请求绑卡链接地址
     * xhhorderid
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);

        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(1050050, "订单不合法或信息不完整", '');
        }

        //3  获取是否存在该订单
        $oRongOrder = (new RongbaoOrder)->getByRongId($xhhorderid);
        if (!$oRongOrder) {
            return $this->showMessage(1050051, '此订单不存在');
        }
        //4 渲染输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $oRongOrder,
            'xhhorderid' => $cryid,
            'smsurl' => "/rongpay/getsmscode",
        ]);
    }
    
    
    /**
	 * 发送验证码
	 */
	public function actionGetsmscode(){
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);

        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(1050052, "信息不完整");
        }

        //2 获取是否存在该订单
        $getRongOrder = $this->getRongOrder($xhhorderid);
        if (!$getRongOrder) {
            return $this->showMessage(1050053, '订单信息错误');
        }
        //3获取主订单信息
        $oPayOrder = Payorder::findOne($getRongOrder -> payorder_id);
        if (!$oPayOrder) {
            return $this->showMessage(1050054, '订单信息错误');
        }
        $nexturl = Yii::$app->request->hostInfo . '/rongpay/paycomfirm';
        
        //重新获取验证码
        if($getRongOrder -> status == Payorder::STATUS_SENDSMS){
            $model = new CRongbao();
            $resSend = $model -> resendsms($getRongOrder);
            if($resSend['res_code'] > 0 ){
                return $this->showMessage($resSend['res_code'], $resSend['res_data']);
            }
            return $this->showMessage($resSend['res_code'], [
                'isbind' => false,
                'nexturl' => $nexturl,
            ]);
        }
        
        //新用户
        if($getRongOrder -> status == Payorder::STATUS_NOBIND){
            $model = new CRongbao();
            $res = $model -> newUserBindCard($getRongOrder);
            if($res['res_code'] > 0 ){
                return $this->showMessage($res['res_code'], $res['res_data']);
            }
            return $this->showMessage($res['res_code'], [
                'isbind' => false,
                'nexturl' => $nexturl,
            ]);
        }
        //老用户
        if($getRongOrder -> status == Payorder::STATUS_BIND){
            $model = new CRongbao();
            $oldBindRes = $model ->oldUserBindCard($getRongOrder);
            if($oldBindRes['res_code'] > 0 ){
                return $this->showMessage($oldBindRes['res_code'], $oldBindRes['res_data']);
            }
            //3 返回结果
            return $this->showMessage($oldBindRes['res_code'], [
                'isbind' => false,
                'nexturl' => $nexturl,
            ]);
        }
    }
    
    
    /*
     * 确认支付
     */
    public function actionPaycomfirm(){
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $validatecode = $this->post('validatecode');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);

        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(1050050, "订单不合法或信息不完整", '');
        }
        if (!isset($validatecode) || !$validatecode) {
            return $this->showMessage(1050051, "验证码不正确", '');
        }

        //3  获取是否存在该订单
        $oRongOrder = (new RongbaoOrder)->getByRongId($xhhorderid);
        if (!$oRongOrder) {
            return $this->showMessage(1050052, '此订单不存在');
        }
        
        //4 检测是不是是未绑定状态，否则不允许绑定
        if (!in_array($oRongOrder->status, [Payorder::STATUS_BIND , Payorder::STATUS_SENDSMS ,Payorder::STATUS_NOBIND])  ) {
            return $this->showMessage(1050054, "订单已在处理，请勿重复提交");
        }
        
        //5  获取主订单, 短信验证码检测
        $oPayorder = $oRongOrder->payorder;
        if (!$oPayorder) {
            return $this->showMessage(1050053, "主订单异常,请联系相关人员");
        }
        
        $model = new CRongbao();
        $res = $model -> paysave($oRongOrder , $validatecode);

        //支付处理中也返回callbackurl
        if(($res['res_code'] > 0) && (!in_array($res['res_code'],$this->rbHandingCode))){
             return $this->showMessage($res['res_code'], $res['res_data']);
        }
        $url = $oPayorder->clientBackurl();
        return $this->showMessage($res['res_code'], [
            'callbackurl' => $url,
        ]);
    }
    
    
    /**
     * 获取订单
     */
    private function getRongOrder($id) {
        if (empty($id)) {
            return $this->returnError(null, "订单号不存在");
        }
        $RongbaoOrder = (new RongbaoOrder)->getById($id);
        if (!$RongbaoOrder) {
            return $this->returnError(null, "未找到订单信息");
        }
        if ($RongbaoOrder->status == Payorder::STATUS_PAYOK) {
            return $this->returnError(null, "此订单已经完成，不必重复提交");
        }
        return $RongbaoOrder;
    }

   
    /**
     * 融宝快捷支付回调:
     * 只有支付成功易宝才会回调
     * post 融宝后台异步回调
     */
    public function actionNotify($cfg = '') {
        //1 数据获取
        $isPost = Yii::$app->request->isPost;
        $paramArr['merchant_id'] = $this->post('merchant_id');
        $paramArr['data']        = $this->post('data');
        $paramArr['encryptkey']  = $this->post('encryptkey');
        
        Logger::dayLog('rongpay', 'rongpay/notify', 'encryptdata', $cfg, $paramArr);
        $res                     = (new RongbaoApi($cfg))->notify($paramArr, 'status');
        $responseData = isset($res['data']) ? $res['data'] : [];
        $responseCode = isset($res['code']) ? $res['code'] : '';
        //$responseData['status'] == 'TRADE_FAILURE';
        Logger::dayLog('rongpay', 'rongpay/notify','decryptdata', $responseData);
        // 无响应时不处理
        if (empty($responseData)) {
            exit;
        }
        
        if ($responseCode=='10002') {
            Logger::dayLog('rongbao/quick_notify', '验签失败',$responseData);
            exit;
        }

        //4 根据融宝返回的订单号检查本数据库是否存在
        $oRongbaoOrder = (new RongbaoOrder)->getByCliOrderId($responseData['order_no']);
        if (empty($oRongbaoOrder)) {
            Logger::dayLog('rongbao/quick_notify', 'orderid not found', $responseData['order_no']);
            exit;
        }
        
        //5  若状态已经更新成功了，则无需要再更新
        if ( !$oRongbaoOrder->is_finished() ) {
            // == 1表示支付成功
            if ($responseData['status'] == 'TRADE_FINISHED') {
                $result = $oRongbaoOrder->savePaySuccess($responseData['trade_no']);
                if (!$result) {
                    Logger::dayLog('rongbao/quick_notify', 'savePaySuccess/保存失败', $responseData['order_no']);
                    exit;
                }
            }else if($responseData['status'] == 'TRADE_FAILURE'){
                $result = $oRongbaoOrder->savePayFail($responseCode,$res['msg']);
                if (!$result) {
                    Logger::dayLog('rongbao/quick_notify', 'Payfail/订单状态修改失败',$oRongbaoOrder);
                    exit;
                }
            }
        }

        //6 回调客户端
        if ($isPost) {
            $result = $oRongbaoOrder->payorder->clientNotify();
            if ($result) {
                echo 'SUCCESS';
                exit;
            }
        }
    }
}

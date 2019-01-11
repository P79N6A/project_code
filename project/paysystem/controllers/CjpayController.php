<?php
/**
 * 畅捷快捷支付
 */
namespace app\controllers;
use app\common\Logger;
use app\models\App;
use app\models\BindBank;
use app\models\cjt\CjQuickOrder;
use app\models\Payorder;
use app\modules\api\common\cjquick\CCjquick;
use app\modules\api\common\cjquick\CjquickApi;
use Yii;
use yii\helpers\ArrayHelper;
class CjpayController extends BaseController {

    public $layout = false;
    private $oCCj; 

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $this->oCCj = new CCjquick();
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['backbind', 'backpay','payurl'])) {
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
        $order_id = (new CjQuickOrder)->decryptId($cryid);
        if (!$order_id) {
            return $this->showMessage(140101, "订单不合法或信息不完整");
        }

        //2  获取是否存在该订单
        $oCjOrder = (new CjQuickOrder)->getById($order_id);
        if (!$oCjOrder) {
            return $this->showMessage(140102, '此订单不存在');
        }
        //3 按状态进行处理
        if (in_array($oCjOrder->status,[Payorder::STATUS_INIT,Payorder::STATUS_PREDO])) {

            #return $this->payview($oCjOrder);
            return $this->render('/pay/payurl', [
                'oPayorder' => $oCjOrder,
                'xhhorderid' => $cryid,
                'smsurl' => "/cjpay/getsmscode",
            ]);

        } elseif ($oCjOrder->status == Payorder::STATUS_PAYOK) {
            return $this->showMessage(140103, '此订单已经处理完毕, 并且支付成功');
        } elseif ($oCjOrder->status == Payorder::STATUS_PAYFAIL) {
            return $this->showMessage(140104, '此订单已经处理完毕, 并且支付失败');
        } else {
            return $this->showMessage(140105, '此订单状态不合法');
        }
    }

    /**
     * 显示支付页面
     * @param  object $oCjOrder
     * @return  html
     */
    private function payview($oCjOrder) {
        //1 绑定关系
        /*$oBind = BindBank::findOne($oCjOrder->bind_id);
        if (!$oBind) {
            return $this->showMessage(140106, '此订单银行卡绑定不正确');
        }*/

        //2 输出页面
        $cryid = $oCjOrder->encryptId($oCjOrder->id);
        return $this->render('/pay/payurl', [
            'oPayorder' => $oCjOrder,
            'xhhorderid' => $cryid,
            'smsurl' => "/cjpay/getsmscode",
        ]);
       /* return $this->render('payurl', [
            'oCjOrder' => $oCjOrder,
            'oBind' => $oBind,
            'xhhorderid' => $cryid,
        ]);*/
    }
    /**
     * 请求支付 调用畅捷快捷支付请求接口
     */
    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid', '');
        $order_id = (new CjQuickOrder)->decryptId($cryid);
        if (!$order_id) {
            return $this->showMessage(140201, "订单不合法或信息不完整");
        }
       /* $expiry_date = $this->post('expiry_date');
        $cvv2 = $this->post('cvv2');*/
        //2  获取是否存在该订单
        $oCjOrder = (new CjQuickOrder)->getById($order_id);
        if (!$oCjOrder) {
            return $this->showMessage(140202, '此订单不存在');
        }
        /*if($oCjOrder->bankcardtype==2){
            //信用卡
            if(empty($expiry_date)){
                 return $this->showMessage(140203, '请输入信用卡有效期');
            }
            if(empty($cvv2)){
                 return $this->showMessage(140203, '请输入信用卡CVV2 ');
            }
            $up_res = $oCjOrder->updateOrder(['cvv2'=>$cvv2,'expiry_date'=>$expiry_date]);
            if(!$up_res){
                 return $this->showMessage(140203, '更新订单信用卡信息失败');
            }
        }*/
        //3 获取主订单
        $oPayorder = (new Payorder)->getByOrder($oCjOrder->orderid, $oCjOrder->aid);
        if (!$oPayorder) {
            return $this->showMessage(140204, "主订单异常,请联系相关人员");
        }



        if(empty($oCjOrder->has_send)){
            //4 订单请求接口
            $has_send = $oCjOrder->has_send+1;
            $oCjOrder->updateOrder(['has_send'=>$has_send]);
            $status = $this->oCCj->pay($oCjOrder);
            if($status != Payorder::STATUS_PREDO){
                return $this->showMessage($oCjOrder->error_code,$oCjOrder->error_msg);
            }
        }else{
            //重发短信
            $has_send = $oCjOrder->has_send+1;
            $oCjOrder->updateOrder(['has_send'=>$has_send]);
            $msgresult = $this->oCCj->reSend($oCjOrder);
            if(!$msgresult){
                return $this->showMessage(140205,"该订单已失效，请重新发起请求！");
            }
        }
        
        //返回结果
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/cjpay/paycomfirm',
        ]);
    }
    

    /**
     * 确认支付
     */
    public function actionPaycomfirm() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid', '');
        $order_id = (new CjQuickOrder)->decryptId($cryid);
        if (!$order_id) {
            return $this->showMessage(140301, "订单不合法或信息不完整");
        }

        //2  获取是否存在该订单
        $oCjOrder = (new CjQuickOrder)->getById($order_id);
        if (!$oCjOrder) {
            return $this->showMessage(140302, '此订单不存在');
        }

        $validatecode = $this->post('validatecode');
        if (empty($validatecode)) {
            return $this->showMessage(140303, "smscode未找到");
        }

        //3  获取主订单, 短信验证码检测
        $oPayorder = $oCjOrder->payorder;
        if (!$oPayorder) {
            return $this->showMessage(140204, "主订单异常,请联系相关人员");
        }

        //5 调用支付接口
        $status = $this->oCCj->confirmPay($oCjOrder,$validatecode);
        //6. 只有支付中, 支付成功, 支付失败三种状态有效
        if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL, Payorder::STATUS_DOING])) {
            $url = $oPayorder->clientBackurl();
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        }else if(in_array($status, [Payorder::STATUS_PREDO])){
            return $this->showMessage($oCjOrder->error_code,$oCjOrder->error_msg);
        }else {
            return $this->showMessage(140308, "订单处理失败");
        }
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
            ],JSON_UNESCAPED_UNICODE);
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
     * 支付异步通知接口
     */
    public function actionBackpay($cfg = 'dev') {
         // 数据获取
        $postdata = Yii::$app->request->post();
        Logger::dayLog('cjquick/cjback', 'backpay/异步通知数据：',$cfg, $postdata);
         // 无响应时不处理
        if (empty($postdata)) {
            exit;
        }
        //验签
        $oCBack = new CjquickApi($cfg);
        $result = $oCBack->verify($postdata);
        if(!$result){
            Logger::dayLog('cjquick/cjback', "cjpay/verify","验签失败", $postdata, $result);
            return false;
        }
        //处理订单状态
        $outer_trade_no = ArrayHelper::getValue($postdata,'outer_trade_no','');//商户平台订单号
        $other_orderid  = ArrayHelper::getValue($postdata,'inner_trade_no','');//畅捷流水号
        $trade_status  = ArrayHelper::getValue($postdata,'trade_status','');//交易状态
        $trade_amount  = ArrayHelper::getValue($postdata,'trade_amount','');//交易金额
        if(empty($outer_trade_no)){
            Logger::dayLog('cjquick/cjback', "cjpay/backpay","商户平台订单号为空", $postdata, $outer_trade_no);
            return false;
        }
        $oCjOrder = (new CjQuickOrder)->getByCliOrderId($outer_trade_no);
        if(empty($oCjOrder)){
            Logger::dayLog('cjquick/cjback', "cjpay/backpay","查询不到订单", $postdata, $outer_trade_no);
            return false;
        }
        if($oCjOrder->amount!=$trade_amount*100){
            Logger::dayLog('cjquick/cjback', "cjpay/backpay","订单金额和交易金额不相符", $trade_amount*100, $oCjOrder->attributes);
            return false;
        }
        if($oCjOrder->is_finished()){
           echo 'success';exit;
        }
        if($trade_status=='TRADE_SUCCESS' || $trade_status=='TRADE_FINISHED'){
             //成功时处理
            $result = $oCjOrder->savePaySuccess($other_orderid);
            Logger::dayLog('cjquick/cjback', "cjpay/backpay","修改订单状态",$trade_status,$result);
            if(!$result){
                
               return false;
            }
            //更新绑卡表信息
            /*$bind_res = $this->oCCj->updateBindBank($oCjOrder);
            Logger::dayLog('cjquick/cjback', "cjpay/backpay","更新绑卡表信息",$bind_res);*/
        }
        //异步通知客户端
        $result = $oCjOrder->payorder->clientNotify();
        Logger::dayLog('cjquick/cjback', "cjpay/backpay","异步通知客户端",$result);
        if (!$result) {          
            return false;
        }

        //异步回调成功返回状态码
        echo 'success'; exit;
    }

}

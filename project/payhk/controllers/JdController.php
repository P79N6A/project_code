<?php
/**
 *  宝付快捷支付路由地址
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\Payorder;
use app\models\jd\JdOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\jd\CJdquick;
use Yii;
use yii\helpers\ArrayHelper;



    
class JdController extends ApiController {

    //外部错误码
    private static $handleCode = [
        '0001','EEB0058','EEB0060','EEB0061','EEB0063','EEE0002','EEE0003','EEN0015','EES0032',
    ];

    //第三方订单状态码
    const RES_PAYOK = 0;    #成功
    const RES_REFUND = 3;    #退款  不用
    const RES_DOING = 6;    #处理中
    const RES_PAYFAIL = 7;    #失败
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
     * 显示存管请求支付链接地址
     * xhhorderid
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(16000, "订单不合法或信息不完整", '');
        }
        //3  获取是否存在该订单
        $jdInfoRe = $this->getJdOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($jdInfoRe,'res_code','-1');
        if($resCode != '0000'){
            Logger::dayLog('rbxy/Controller', ArrayHelper::getValue($jdInfoRe,'res_data','该订单异常。'),$xhhorderid);
            return $this->showMessage($resCode, ArrayHelper::getValue($jdInfoRe,'res_data','该订单异常。'));
        }
        $jdInfo = ArrayHelper::getValue($jdInfoRe,'res_data','');
        
        //5  获取主订单校验
        $oPayorder = $jdInfo->payorder;
        if (!$oPayorder) {
            Logger::dayLog('jd/Controller', '主订单异常,请联系相关人员', $jdInfo);
            return $this->showMessage(16002, "主订单异常,请联系相关人员");
        }

        //7 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $jdInfo->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/jd/getsmscode",
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
    private function getJdOrder($id) {
        if (empty($id)) {
            #return $this->showMessage(16003, "订单号不存在");
            return ['res_code' => 16003, 'res_data' => '订单号不存在。'];
        }
        $jdModel = new JdOrder();
        $jdInfo = $jdModel->getByJdId($id);
        if (!is_object($jdInfo)) {
            Logger::dayLog('jd/Controller', '未找到订单信息', $id);
            #return $this->showMessage(16003, "未找到订单信息");
            return ['res_code' => 16003, 'res_data' => '未找到订单信息。'];
        }
        if (in_array($jdInfo->status,[Payorder::STATUS_PAYOK,Payorder::STATUS_PAYFAIL])) {
            Logger::dayLog('jd/Controller', '未找到订单信息', $jdInfo);
            #return $this->showMessage(16004, "此订单已处理，不必重复提交");
            return ['res_code' => 16004, 'res_data' => '此订单已处理，不必重复提交。'];
        }
        if(!is_object($jdInfo)){
            Logger::dayLog('rbxy/Controller', '数据不是对象！', $jdInfo);
            return ['res_code' => 16033, 'res_data' => '此订单异常。'];
        }
        return ['res_code' => 0000, 'res_data' => $jdInfo];
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
            return $this->showMessage(16005, "信息不完整");
        }
        //2 获取是否存在该订单
        $jdInfoRe = $this->getJdOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($jdInfoRe,'res_code','-1');
        if($resCode != '0000'){
            Logger::dayLog('rbxy/Controller', ArrayHelper::getValue($jdInfoRe,'res_data','该订单异常。'),$xhhorderid);
            return $this->showMessage($resCode, ArrayHelper::getValue($jdInfoRe,'res_data','该订单异常。'));
        }
        $jdInfo = ArrayHelper::getValue($jdInfoRe,'res_data','');
        //1.发送短信
        $oJd = new CJdquick();
        $result = $oJd->jdSendSms($jdInfo);
        if(empty($result)){
            $jdInfo->saveStatus(Payorder::STATUS_CANCEL,'','',['-1','请求超时']);
            return $this->showMessage(16007, '请求超时，请稍后再试。');
        }
        if((string)$result->RETURN->CODE == '0000'){
            $jdInfo->saveStatus(Payorder::STATUS_BIND,'');
            return $this->showMessage(0, [
                'isbind' => false,
                'nexturl' => Yii::$app->request->hostInfo . '/jd/paycomfirm',
            ]);
        }
        $jdInfo->saveStatus(Payorder::STATUS_CANCEL,'','',[(string)$result->RETURN->CODE,(string)$result->RETURN->DESC]);

        Logger::dayLog('jd/Controller', '验证码获取失败，请稍后再试',$jdInfo,(string)$result->RETURN->CODE,(string)$result->RETURN->DESC);
        return $this->showMessage(16007, '验证码获取失败，请稍后再试。');
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
        $jdInfoRe = $this->getJdOrder($xhhorderid);
        $resCode = ArrayHelper::getValue($jdInfoRe,'res_code','-1');
        if($resCode != '0000'){
            Logger::dayLog('rbxy/Controller', ArrayHelper::getValue($jdInfoRe,'res_data','该订单异常。'),$xhhorderid);
            return $this->showMessage($resCode, ArrayHelper::getValue($jdInfoRe,'res_data','该订单异常。'));
        }
        $jdInfo = ArrayHelper::getValue($jdInfoRe,'res_data','');
        //赋值验证码
        $jdInfo->smscode = $validatecode;
        //4  获取主订单
        $oPayorder = $jdInfo->payorder;
        if (!$oPayorder) {
            Logger::dayLog('jd/Controller', '主订单异常,请联系相关人员',$jdInfo);
            return $this->showMessage(16011, "主订单异常,请联系相关人员");
        }
        //5 调用支付接口
        $oJdpay = new CJdquick();
        $re_result = $oJdpay->confirmPay($jdInfo,$validatecode);
        //6. 只有支付成功, 支付失败状态有效
        if(empty($re_result)){
            #@todo
            return $this->showMessage(16014, '请求超时，请稍后重试。');
        }
        $rsp_code =(string) $re_result->RETURN->CODE;
        $rsp_msg = (string)$re_result->RETURN->DESC;
        $rsp_status = (string)$re_result->TRADE->STATUS;
        if($rsp_code == "0000" && $rsp_msg == '成功'){
            if($rsp_status == self::RES_PAYOK){
                //成功时处理
                $jdInfo->refresh();
                $jdInfo->savePaySuccess($jdInfo->orderid);
            }
            if($rsp_status == self::RES_PAYFAIL){
                //失败时处理
                $jdInfo->savePayFail($rsp_code,$rsp_msg);
            }
        }
        elseif($rsp_code == "EES0027" || $rsp_code == "EEN0017"){
            return $this->showMessage(16012, "短信验证码输入有误，请输入有效的6位数字验证码.");
        }
        elseif($rsp_code == "EES0035" || $rsp_code == "EEN0002"|| $rsp_code == "EEB0025"){
            return $this->showMessage(16013, "短信验证码已过期，请重新获取.");
        }
        elseif(in_array($rsp_code,self::$handleCode)){
            $url = $oPayorder->clientBackurl();
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        }else{
            Logger::dayLog('jd/Controller', '支付失败',$rsp_code,$rsp_msg,'支付状态：',$rsp_status);
            // 失败时处理
            $jdInfo->refresh();
            $jdInfo->savePayFail($rsp_code,$rsp_msg);
        }
        if(in_array($jdInfo->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            //需要重新获取对象
            $jdModel = new JdOrder();
            $new_jdInfo = $jdModel->getByJdId($xhhorderid);
            $new_jdInfo->payorder->clientNotify();
        }
        $url = $oPayorder->clientBackurl();
        return $this->showMessage(0, [
            'callbackurl' => $url,
        ]);
    }



    public function actionBackpay(){
        // 1、接收数据
        $merchant = $this->get('code');
        $postData = $this->post('resp');
        Logger::dayLog('jd/backPay', '异步通知结果', $postData,$merchant);
        if(empty($postData) || empty($merchant)){
            echo 'resp和code都不能为空';die;
        }
        //  2、解析数据 获取订单号
        $oCJdquick = new CJdquick();
        $postData = 'resp='.$postData;  #因为主动请求的结果有这玩意  所有就给补上去
        $res = $oCJdquick->getApi($merchant)->operate($postData);
        Logger::dayLog('jd/backPay', '异步通知结果解密', $res);
        $orderid = (string)$res->TRADE->ID;
        $oJdModel = new JdOrder();
        $jdOrder = $oJdModel->getByJdOrderId($orderid);
        //判断数据
        if(!is_object($jdOrder)){
            Logger::dayLog('jd/backPay', '没有查到改订单号。', $orderid);
            echo '订单号不能为空';die;
        }
        $rsp_code = (string)$res->RETURN->CODE;
        $rsp_msg = (string)$res->RETURN->DESC;
        $rsp_amount = (int)$res->TRADE->AMOUNT;

        if($rsp_amount != $jdOrder['amount']){
            Logger::dayLog('jd/backPay', '交易金额不对。', $postData);
            echo '交易金额不对';die;
        }
        if(in_array($jdOrder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
            #Logger::dayLog('jd/backPay', '该订单已经处理。', $rsp_code,$rsp_msg);
            $jdOrder->payorder->clientNotify();
            echo 'success';die;
        }

        //京东快捷支付  只异步通知成功的
        // 3、判断返回的结果
        if($rsp_code == "0000" && $rsp_msg == '成功'){
            $rsp_status = (string)$res->TRADE->STATUS;
            if($rsp_status == self::RES_PAYOK){
                //成功时处理
                $result = $jdOrder->savePaySuccess($jdOrder->orderid);
                //成功时处理
                if(!$result){
                    Logger::dayLog('jd/backPay', '同步更新订单失败', $result);
                    echo '同步更新订单失败';die;
                }
            }
            if($rsp_status == self::RES_PAYFAIL){
                //失败时处理
                $result = $jdOrder->savePayFail($rsp_code,$rsp_msg);
                if(!$result){
                    Logger::dayLog('jd/orderQuery', '同步更新订单失败', $result);
                    echo '同步更新订单失败';die;
                }
            }
        }
        $resnotice = $jdOrder->payorder->clientNotify();
        if(!$resnotice){
            Logger::dayLog('jd/backPay', '通知失败', $jdOrder->payorder);
            echo '通知失败';die;
        }
        Logger::dayLog('jd/backPay', '该订单处理成功。', $rsp_code,$rsp_msg);
        echo 'success';die;
    }


}

<?php
/**
 * 支付宝支付结果通知接口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/30
 * Time: 10:36
 *
 */
namespace app\controllers;
use app\common\Logger;
use app\models\Payorder;
use app\modules\api\common\ApiController;
//use app\modules\api\common\repayali\Repayali;
use app\modules\api\common\repayment\Cjpayment;

//use app\modules\api\common\repaywx\Config;
use yii\helpers\ArrayHelper;

class CjbackController extends ApiController
{
    private $__returninfo = '';
    public function init() {

    }
    public function actionNotify()
    {
        //1.接收参数
        $oCjpayment = new Cjpayment();
        $poststr = file_get_contents("php://input", 'r');
        //$poststr = 'notify_time=20180611162522&sign_type=RSA&notify_type=trade_status_sync&trade_status=TRADE_SUCCESS&gmt_payment=20180611162522&version=1.0&sign=c%2Bcwp%2BtC1%2BrA3Cni6V3TvDGNAcQCWRxfuYYRyRD5U36eQJPXhU7BUhKJ6NVnMzjujBUviIEgMXNwiZQIjMPUyCZItKMYEyKx%2FyybM%2FkIuQopFh4eiBfpDIe5IOUkYdaHXWC8MemIkxjLJbyAHQ1D4mNt9IodkZGjSx1v8uzWkjI%3D&extension=%7B%22RESPONSE_TO_INST_TYPE%22%3A%22RETURN_SERVER%22%2C%22RETURN_TO_INST_INFO%22%3A%22%26lt%3Bxml%26gt%3B%26lt%3Breturn_code%26gt%3B%26lt%3B%21%5BCDATA%5BSUCCESS%5D%5D%26gt%3B%26lt%3B%2Freturn_code%26gt%3B%26lt%3B%2Fxml%26gt%3B%22%2C%22apiResultMsg%22%3A%22%E4%BA%A4%E6%98%93%E6%88%90%E5%8A%9F%22%2C%22apiResultcode%22%3A%22S%22%2C%22channelTransTime%22%3A%222710772910308%22%2C%22instPayNo%22%3A%22SA194158306110003350718%22%2C%22settlementId%22%3A%2220180611162428PP90332723%22%2C%22unityResultMessage%22%3A%22%E4%BA%A4%E6%98%93%E6%88%90%E5%8A%9F%22%7D&gmt_create=20180611162522&_input_charset=UTF-8&outer_trade_no=CJ033011528705467&trade_amount=0.01&notify_id=2508696021a140ff938e0078e4d284d0&inner_trade_no=101152870546809975686';
        Logger::dayLog('cj/back', '接收参数：',$poststr);
        if (empty($poststr)){
            Logger::dayLog('cj/back', '错误：', '参数为空');
            return false;
        }
        //2.解析参数
        parse_str($poststr, $params_data);//字符串解析到变量中。
        Logger::dayLog('cj/back', '解析参数：', $params_data);
        if (empty($params_data)){
            Logger::dayLog('cj/back', '错误：', '参数为空');
            die('fail');
        }
        $outer_trade_no = ArrayHelper::getValue($params_data, 'outer_trade_no', '');//订单号
        $order = $oCjpayment->getOrder($outer_trade_no);//获取主订单表详细信息
        $oStatus = ArrayHelper::getValue($order, 'status');
        if( in_array( $oStatus, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL] ) ){
            die('success');
        }
        //3.对参数验证
        $trade_amount = ArrayHelper::getValue($params_data, 'trade_amount', '');//回调金额 单位元
        $amount = ArrayHelper::getValue($order, 'amount', '');//订单表金额
        $orderid = ArrayHelper::getValue($order, 'orderid', '');//订单表订单号
        if($outer_trade_no != $orderid){
            Logger::dayLog('cj/back', '订单号不同：', $params_data);
            die('fail');
        }
        if($trade_amount != $amount/100){
            Logger::dayLog('cj/back', '金额不同：', $params_data);
            die('fail');
        }
        //3.对参数验证
        //更新数据
        $ret = $oCjpayment->updateOrders($params_data);//更新子表
        if (!$ret){
            die('fail');
        }
        //记录通知表
        $result = $this->sendNotifys($params_data);

        if($result){
            die('success');
        }else{
            die('fail');
        }
    }
    private function sendNotifys($data_set)
    {
        $oPayorder = new Payorder();
        $payorder_info = $oPayorder->getOrderId(ArrayHelper::getValue($data_set, 'outer_trade_no'));
        if (empty($payorder_info)){
            return false;
        }
        
        $status = Payorder::STATUS_PAYOK;//状态为2成功
        $other_orderid = ArrayHelper::getValue($data_set, 'inner_trade_no', '');
        $res_code = ArrayHelper::getValue($data_set, 'trade_status', '');
        $res_msg = ArrayHelper::getValue($data_set, 'msg', '');
        $success = $payorder_info->saveStatus($status, $other_orderid, $res_code, $res_msg);//修改主表状态
        
        if(!$success){
           return false;
        }
        //通知
        $payorder_info->clientNotify();
        return true;
    }

    /**
     * 验证参数
     * @param $params_data
     * @param $data_set
     * @return bool
     */
    private function verifyParams($params_data, $data_set)
    {
        if (empty($params_data) || empty($data_set)){
            return false;
        }
        static $flag=true;
        foreach($params_data as $key=>$value) {
            if ($value){
                if (empty($data_set[$key])){
                    $flag = false;
                    return false;
                }
                if (is_array($value)){
                    $data = json_decode($data_set[$key], true);
                    if (!empty($data)){
                        $this->verifyParams($value, $data);
                    }

                }
            }
        }
        return $flag;
    }

    /**
     * 需要验证的字段
     * @return array
     */
    private function isParams()
    {
        $data = [
            'merchantOutOrderNo'      => 1, //商户订单号
            'merid'                 => 1, //商户号
            'msg'                     =>
                [
                    'payMoney'=>1,  // //交易金额
                ], //订单的详细信息
            //'msg_payMoney'            => 1, //交易金额
            'noncestr'                => 1, //随机参数
            'orderNo'                 => 1, //平台订单号
            'payResult'               => 1, //支付结果
            //'sign'                    => 1, //签名
        ];
        return $data;
    }

    private function sendNotify($data_set)
    {
        $oPayorder = new Payorder();
        $payorder_info = $oPayorder->getOrderId(ArrayHelper::getValue($data_set, 'merchantOutOrderNo'));
        if (empty($payorder_info)){
            return '0007';
        }
        //修改状态 
        $payResult = ArrayHelper::getValue($data_set, 'payResult', '');
        $status = $payResult ? 2 : 11;
        $other_orderid = ArrayHelper::getValue($data_set, 'orderNo', '');
        $res_code = ArrayHelper::getValue($data_set, 'payResult  ', '');
        $res_msg = ArrayHelper::getValue($data_set, 'msg', '');
        $payorder_info->saveStatus($status, $other_orderid, $res_code, $res_msg);
        //通知
        $ret = $payorder_info -> clientNotify();
        return $ret;
    }

    private function md5Sign($data, $key)
    {
        $signstr = 'merchantOutOrderNo='.$data['merchantOutOrderNo'].'&merid='.$data['merid'].'&msg='.$data['msg'].'&noncestr='.$data['noncestr'].'&orderNo='.$data['orderNo'].'&payResult='.$data['payResult'].'&key='.$key;
        return md5($signstr);
    }

}
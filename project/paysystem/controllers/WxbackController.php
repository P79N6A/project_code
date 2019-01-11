<?php
/**
 * 微信支付结果通知接口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/30
 * Time: 10:36
 *
 */
namespace app\controllers;
use app\common\Logger;
use app\models\Payorder;
use app\models\repayment\PayAlipayOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\repayment\RepayConfig;
use app\modules\api\common\repayment\Repayment;
use app\modules\api\common\repaywx\Config;
use app\modules\api\common\repaywx\Repaywx;
use yii\helpers\ArrayHelper;

class WxbackController extends ApiController
{
    private $__returninfo = '';
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $oRepayConfig = new Config();
        $this->__returninfo = $oRepayConfig->returnInfo();
    }
    public function actionNotify()
    {
        //1.接收参数
        $oRepayment = new Repaywx();
        $poststr = file_get_contents("php://input", 'r');
        //$poststr = 'merid=101100117&merchantOutOrderNo=R20171517907765&orderNo=10101100117180206170325264&payResult=true&msg=%7B%22tradeDate%22%3A%2220180206170331%22%2C%22payMoney%22%3A%220.01%22%2C%22buyerLogonId%22%3A%22o-hyhjr8htfeBXz-AfADmKcaNGGs%22%2C%22tradeNo%22%3A%224200000055201802068206470346%22%2C%22thirdNo%22%3A%22399590024234201802063171107433%22%2C%22rtCode%22%3A%2200%22%2C%22merid%22%3A%22101100117%22%7D&noncestr=5fe27f2ddb574d3e80cbc6c9c83198c6&sign=5f787f09f10ad1242b8cb2b0d3895e35';
        Logger::dayLog('repaywx/wxback', '接收参数：',$poststr);
        if (empty($poststr)){
            Logger::dayLog('repaywx/wxback', '错误：', '参数为空');
            return $oRepayment->returnMsg('0001', true);
        }
        //2.解析参数
        parse_str($poststr, $params_data);
        if (empty($params_data)){
            Logger::dayLog('repaywx/wxback', '错误：', '参数为空');
            return $oRepayment->returnMsg('0006', true);
        }
        //sign验证
        $sign = $params_data['sign'];
        $key = $oRepayment->getKey(ArrayHelper::getValue($params_data, 'merchantOutOrderNo', ''));
        $new_sign = $this->md5Sign($params_data, $key);
        if ($new_sign != $sign){
            Logger::dayLog('repaywx/wxback', '错误：', json_encode($params_data)."--sign验证不正确");
            //return $oRepayment->returnMsg('0010', true);
        }
        //3.对参数验证
        $isParams = $this->isParams();
        $verify_state = $this->verifyParams($isParams, $params_data);
        if (!$verify_state){
            Logger::dayLog('repaywx/wxback', '错误：', $verify_state);
            return $oRepayment->returnMsg('0001', true);
        }
        //更新数据
        $ret = $oRepayment -> updateOrder($params_data);
        //记录通知表
        $this->sendNotify($params_data);

        if (!$ret){
            return $oRepayment->returnMsg('0008', true);
        }
        return "success";
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
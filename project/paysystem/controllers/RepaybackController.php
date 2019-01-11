<?php
/**
 * 3.8  支付结果通知接口
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
use yii\helpers\ArrayHelper;

class RepaybackController extends ApiController
{
    private $__returninfo = '';
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $oRepayConfig = new RepayConfig();
        $this->__returninfo = $oRepayConfig->returnInfo();
    }
    public function actionNotify()
    {
        //1.接收参数
        $oRepayment = new Repayment();
        $poststr = file_get_contents("php://input", 'r');
        Logger::dayLog('repay/back', 'info',$poststr);
        if (empty($poststr)){
            Logger::dayLog('repay/back', 'error', '参数为空');
            return $oRepayment->returnMsg('0001', true);
        }
        //2.解析参数
        parse_str($poststr, $params_data);
        if (empty($params_data)){
            Logger::dayLog('repay/back', 'error', '参数为空');
            return $oRepayment->returnMsg('0006', true);
        }

        //sign验证
        $sign = $params_data['sign'];
        $key = $oRepayment->getMerid(ArrayHelper::getValue($params_data, 'merchantOutOrderNo', ''));
        $new_sign = $this->md5Sign($params_data, $key);
        if ($new_sign != $sign){
            Logger::dayLog('repay/back', 'error', json_encode($params_data)."--sign验证不正确");
            //return $oRepayment->returnMsg('0010', true);
        }
        //3.对参数验证
        $isParams = $this->isParams();
        $verify_state = $this->verifyParams($isParams, $params_data);
        if (!$verify_state){
            Logger::dayLog('repay/back', 'error', $verify_state);
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
        $status = $payResult == 1  ? 2 : 11;
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

    /**
     * 错误信息
     * @return array
     */
    private function errorMsg()
    {
        return [
            '0000'  => '成功',
            '1007'  => '商户信息有误',
            '1008'  => '字段格式不正确',
            '1010 ' => '订单金额不能低于 1 元',
            '1011'  => '订单金额不能超过 19900 元',
            '1012'  => '测试商户请发起 1 元 ',
            '1013'  => '请使用测试商户号',
            '1014'  => '订单金额超限',
            '1015'  => '不在支付宝环境',
            '1016'  => '不在微信环境',
            '1017'  => '权限错误',
            '2000'  => '验签失败',
            '2001'  => '订单号重复',
            '3000'  => '路由不存在',
            '3001'  => '通道不存在',
            '3002'  => '交易异常',
            '3003'  => '订单信息有误 ',
            '9999'  => '系统异常',
        ];
    }
}
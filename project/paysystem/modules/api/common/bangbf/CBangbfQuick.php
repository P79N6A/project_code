<?php
/**
 * @desc 邦宝付快捷支付API
 */
namespace app\modules\api\common\bangbf;
use app\common\Logger;
use app\models\bangbf\BangbfOrder;
use app\models\Payorder;
use Yii;

class CBangbfQuick {
    /**
     * 当前回调处理的订单
     */
    public $oOrder;
    /**
     * 定义出错
     */
    public $errinfo;
    private function returnError($result, $errinfo) {
        $this->errinfo = $errinfo;
        return $result;
    }
    public function __construct() {
        
    }

    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        //$is_prod = true;
        $cfg     = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }

    /**
     * @desc 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    public function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg              = $this->getCfg($channel_id);
            $map[$channel_id] = new BangbfApi($cfg);
        }
        return $map[$channel_id];
    }


    /**
     * @desc 创建邦宝付支付订单
     * @param  obj $oPayorder
     * @return  url
     */
    public function createOrder($oPayorder) {
        //1. 数据检测
        if (!is_object($oPayorder) || empty(get_object_vars($oPayorder))) {
            return ['res_code' => 100010, 'res_data' => '没有提交数据！'];
        }
        $bangbfOrder = new BangbfOrder();
        $res = $bangbfOrder->saveOrder($oPayorder);
        if (!$res) {
            Logger::dayLog('BangbfQuick/createOrder', '创建邦宝付支付订单', $oPayorder->attributes, '失败原因', $bangbfOrder->errors);
            return ['res_code' => 100011, 'res_data' => '订单保存失败'];
        }
        //返回下一步处理流程
        $res_data = $bangbfOrder->getPayUrls();
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    
    /**
     * @desc 快捷支付
     * @param  object $bangbfOrder
     * @return array [res_code, res_data]
     */
    public function pay($bangbfDetail) {
        if (!is_object($bangbfDetail) || empty(get_object_vars($bangbfDetail))) {
            return ['res_code' => 100010, 'res_data' => '没有提交数据！'];
        }
        $oPayorder = $bangbfDetail->payorder;
        //2. 同步请求宝付接口
        $resPay = $this->getApi($oPayorder->channel_id)->pay($oPayorder);
        $result = false;
        if(!empty($resPay)){
            if($resPay['returnCode'] == '000000') {
                //同步更新主订单 订单成功；
                $other_orderid = isset($resPay['tradeNo']) ? $resPay['tradeNo'] : '';
                $result = $bangbfDetail->savePaySuccess($other_orderid);
            }else if($resPay['returnCode'] == '000001'){
                //调用查询接口


            }else{
                $result = $bangbfDetail->savePayFail($resPay['returnCode'], $resPay['returnMessage']);
            }
        }else{
            //超时
            $result = $this->saveOrderStatus($bangbfDetail,Payorder::STATUS_DOING);
        }
        if(!$result) return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        return $oPayorder->clientData();
    }
    public function query($bangbfDetail){
        $oPayorder = $bangbfDetail->payorder;
        $resPay = $this->getApi($oPayorder->channel_id)->query($oPayorder);

    }
    /**
     * @desc  lianlian认证支付回调
     * @param  json $data
     * @return
     */
    public function backauthpay($data) {
        //1 参数校验
        if (!is_array($data) || !isset($data['orderId'])) {
            return $this->returnError(false, '参数不合法');
        }
        $cli_orderid = $data['orderId'];
        if (!$cli_orderid) {
            return $this->returnError(false, '此订单orderId为空');

        }
        $result_pay = strtoupper($data['status']);
        if ($result_pay !== 'SUCCESS') {
            return $this->returnError(false, '此订单返回状态码不正确');
        }
        //2 获取订单
        $this->oOrder = $oOrder = (new BangbfOrder())->getByCliOrderId($cli_orderid);
        if (!$oOrder) {
            return $this->returnError(false, '未找到该订单');
        }
        //验签
        $verify = $this->oCLian->verifyNotify($data);
        if (!$verify) {
            return $this->returnError(false, '签名错误');
        }
        //3 更新订单为成功
        $is_finished = $oOrder->is_finished();
        if (!$is_finished) {
            // 1.更新订单成功
            $result = $oOrder->savePaySuccess($data['tradeNo']);
            if (!$result) {
                return false;
            }


        }
        return true;
    }
    /**
     * POST 异步通知客户端
     * @param  object $oOrder
     * @return bool
     */
    public function clientNotify($oOrder) {
        if (!$oOrder) {
            return false;
        }
        $oPayorder = (new Payorder)->getByOrder($oOrder->orderid, $oOrder->aid);
        if (!$oPayorder) {
            return false;
        }
        $result = $oPayorder->clientNotify();
        return $result;
    }
}

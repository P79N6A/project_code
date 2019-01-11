<?php
/**
 * @desc 宝付快捷支付API 
 * @author lubaba
 */
namespace app\modules\api\common\baofoo;
use app\common\Logger;
use app\models\baofoo\BaofooOrder;
use app\models\baofoo\BaofooBank;
use app\models\Payorder;
use Yii;

class CBaofooQuick {

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
            $map[$channel_id] = new BaofooApi($cfg);
        }
        return $map[$channel_id];
    }


    /**
     * @desc 创建宝付支付订单
     * @param  obj $oPayorder
     * @return  url
     */
    public function createOrder($oPayorder) {
        //1. 数据检测
        if (!is_object($oPayorder) || empty(get_object_vars($oPayorder))) {
            return ['res_code' => 100010, 'res_data' => '没有提交数据！'];
        }
        $BaofooOrder = new BaofooOrder();
        $res = $BaofooOrder->saveOrder($oPayorder);
        if (!$res) {
            Logger::dayLog('BaofooQuick/createOrder', '创建宝付支付订单', $oPayorder->attributes, '失败原因', $BaofooOrder->errors);
            return ['res_code' => 100011, 'res_data' => '订单保存失败'];
        }
        //返回下一步处理流程
        $res_data = $BaofooOrder->getPayUrls();
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    
    /**
     * @desc 快捷支付
     * @param  object $baofooOrder
     * @return array [res_code, res_data]
     */
    public function pay($baofooDetail) {
        if (!is_object($baofooDetail) || empty(get_object_vars($baofooDetail))) {
            return ['res_code' => 100010, 'res_data' => '没有提交数据！'];
        }
        $oPayorder = $baofooDetail->payorder;
        $baofooBank = new BaofooBank();
        $baofooBankcode = $baofooBank->getBaofooBankcode($oPayorder->bankname);
        // $oPayorder->channel_id = 106;
        //2. 同步请求宝付接口
        $resPay = $this->getApi($oPayorder->channel_id)->pay($oPayorder,$baofooBankcode);
        $result = false;
        if(!empty($resPay)){
            if($resPay['resp_code'] == '0000'){
                //同步更新主订单 宝付订单成功；
                $other_orderid = isset($resPay['trans_no'])?$resPay['trans_no']:'';
                $result = $baofooDetail->savePaySuccess($other_orderid);
            }else{
                $result = $baofooDetail->savePayFail($resPay['resp_code'], $resPay['resp_msg']);
            }
        }else{
            //超时
            $result = $this->saveOrderStatus($model,Payorder::STATUS_DOING);
        }
        if(!$result) return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        return $oPayorder->clientData();
    }
}

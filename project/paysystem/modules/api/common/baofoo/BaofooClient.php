<?php
namespace app\modules\api\common\baofoo;
use app\models\baofoo\BaofooOrder;
use app\models\baofoo\BaofooBank;
use app\models\Payorder;
use app\models\StdError;
use Yii;

/**
 * @desc 宝付代扣支付类
 * @author lubaba
 */
class BaofooClient {


    public function __construct() {
        
    }

    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
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

    /*
     * @desc 宝付生成订单 
     * @param obj $oPayorder
     * @return array
     */

    public function createOrder($oPayorder) {
        //1.生成宝付款订单
        $model = new BaofooOrder();
        $res = $model->saveOrder($oPayorder);
        if (!$res) {
            return StdError::returnStdError($model->channel_id,"0012");
            //return ['res_code' => 1050006, 'res_data' => '订单保存失败'];
        }
        $baofooBank = new BaofooBank();
        $baofooBankcode = $baofooBank->getBaofooBankcode($oPayorder->bankname);
        //$model->channel_id = 106;
        //2. 同步请求宝付接口
        $resPay = $this->getApi($model->channel_id)->pay($oPayorder,$baofooBankcode);
        $result = false;
        if(!empty($resPay)){
            if($resPay['resp_code'] == '0000'){
                //同步更新主订单 宝付订单成功；
                $other_orderid = isset($resPay['trans_no'])?$resPay['trans_no']:'';
                $result = $model->savePaySuccess($other_orderid);
                $oPayorder->refresh();
                $res_data = [
                    'url' =>'',
                    'pay_type' => $oPayorder->channel_id,
                    'status' => $oPayorder->status, //1,8
                    'orderid' => $oPayorder->orderid,
                ];
                $resultInfo = ['res_code'=>0,'res_data'=>$res_data];
            }else{
                $result = $model->savePayFail($resPay['resp_code'], $resPay['resp_msg']);
                //$resultInfo = ['res_code'=>$resPay['resp_code'],'res_data'=>$resPay['resp_msg']];
                $resultInfo = StdError::returnThirdStdError($model->channel_id,$model->error_code,$model->error_msg);
            }
            //异步通知客户端
            $resnotice = $oPayorder->clientNotify();
        }else{
            //超时
            $result = $this->saveOrderStatus($model,Payorder::STATUS_DOING);
            //$resultInfo = ['res_code'=>1050055,'res_data'=>"请求超时，稍后重试"];
            $resultInfo =  StdError::returnStdError($oPayorder->channel_id,"0016");
        }
        if(!$result){
            return StdError::returnStdError($oPayorder->channel_id,"0013");
        }
        //if(!$result) return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        //标准化输出
        //return $model->payorder->clientData();
        return $resultInfo;
    }

    /**
     * $desc 处理时间内异常订单
     * @return int
     */
    public function runMinute($start_time, $end_time) {
        $model = new BaofooOrder();
        $dataList =$model->getAbnorList($start_time, $end_time);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $baofooOrder) {
            $result = $this->orderQuery($baofooOrder);
            if ($result['res_code'] == 0) 
                $success++;
            }
        }
        //5 返回结果
        return $success;
    }


     /* 
     * @des 宝付查询
     * @param $baofooOrder
     * @return array
     */
    public function orderQuery($baofooOrder){
        //1.条件判断
        if($baofooOrder->status != Payorder::STATUS_DOING){
            return StdError::returnStdError($baofooOrder->channel_id,"0005");
            //return ['res_code'=>1050052,'res_data'=>'订单状态有误'];
        }
        $queryData = [];
        $queryData['trans_serial_no'] = $baofooOrder->cli_orderid.rand(1000,9999);
        $queryData['orig_trans_id'] = $baofooOrder->cli_orderid;
        $queryData['orig_trade_date'] = date('YmdHis',strtotime($baofooOrder->create_time));
        $resPay = $this->getApi($baofooOrder->channel_id)->BaofooQuery($queryData);
        $result = false;
        if(!empty($resPay)){
            if($resPay['resp_code'] == '0000'){
                if($resPay['order_stat'] == 'S'){
                    $other_orderid = isset($resPay['trans_no'])?$resPay['trans_no']:'';
                    $result = $baofooOrder->savePaySuccess($other_orderid);
                }elseif($resPay['order_stat'] == 'F' || $resPay['order_stat'] == 'FF'){
                    $result = $baofooOrder->savePayFail($resPay['resp_code'], $resPay['resp_msg']);
                }
            }
            //异步通知客户端
            $resnotice = $baofooOrder->payorder->clientNotify();
        }
        if(!$result){
            return StdError::returnStdError($baofooOrder->channel_id,"0013");
            //return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        }
        //if(!$result) return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        //  //标准化输出
        //return $baofooOrder->payorder->clientData();
        return ['res_code'=>0,'res_data'=>'操作成功'];
    }
    /*
     * @des 修改订单状态
     * @param obj $baofooOrder
     * $param string $status 
     * $bool
     */
    private function saveOrderStatus($baofooOrder,$status){
        $result = $saveOrder = false;
        $baofooOrder ->status = $status;
        $saveOrder = $baofooOrder -> save();
        if($saveOrder){
            $oPayorder = Payorder::findOne($baofooOrder ->payorder_id);
            $result   = $oPayorder->saveStatus($status);
        }
        if($result && $saveOrder)
            return true;
        else
            return false;
    }
}
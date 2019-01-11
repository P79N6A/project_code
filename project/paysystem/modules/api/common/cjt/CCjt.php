<?php

namespace app\modules\api\common\cjt;

use app\models\BindBank;
use app\models\Payorder;
use app\models\cjt\CjtOrder;
use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 畅捷通支付类
 */
class CCjt {

    private $oApi;
    private $infoErrCode = ['1000','2004','2009'];
    private $bodyErrCode = ['2013','3999'];
    public function __construct() {
        
    }

    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg     = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }

    /**
     * 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    private function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg              = $this->getCfg($channel_id);
            $map[$channel_id] = new CjtApi($cfg);
        }
        return $map[$channel_id];
    }
    public function createOrder($oPayorder) {
        //1.生成付款订单
        $model = new CjtOrder();
        $res = $model->saveOrder($oPayorder);
        if (!$res) {
            return ['res_code'=>'-1','res_data'=>'订单保存失败'];
        }
        //2. 同步请求接口
        $resPay = $this->getApi($model->channel_id)->pay($oPayorder);
        //var_dump($resPay);
        if(empty($resPay)){
             //超时
            $result = $model->saveStatus(Payorder::STATUS_DOING,'','','');
            return ['res_code'=>-1,'res_data'=>"请求超时，稍后重试"];
        }
        $info_code  = isset($resPay['INFO']['RET_CODE'])?$resPay['INFO']['RET_CODE']:'';//报头信息
        $info_msg   = isset($resPay['INFO']['ERR_MSG'])?$resPay['INFO']['ERR_MSG']:'';
        $body_code  = isset($resPay['BODY']['RET_CODE'])?$resPay['BODY']['RET_CODE']:'';//报体信息
        $body_msg   = isset($resPay['BODY']['RET_MSG'])?$resPay['BODY']['RET_MSG']:'';
        $req_sn     = isset($resPay['INFO']['REQ_SN'])?$resPay['INFO']['REQ_SN']:'';//请求号
        //报头请求错误
        if(in_array($info_code,$this->infoErrCode)){
            //订单失败
            $result = $model->savePayFail($info_code, $info_msg,$req_sn);
            //异步通知客户端
            $oPayorder->clientNotify();
            return ['res_code'=>$info_code,'res_data'=>$info_msg];
        }
        //报体业务处理错误
        if(in_array($body_code,$this->bodyErrCode)){
            //订单失败
            $result = $model->savePayFail($body_code, $body_msg,$req_sn);
            $resultInfo =  ['res_code'=>$body_code,'res_data'=>$body_msg];
        }else if($body_code=='0000'){
            //处理成功
            $result = $model->savePaySuccess($req_sn);
            $resultInfo =  ['res_code'=>0,'res_data'=>'操作成功'];
        }else{
            //处理中
            $result = $model->saveStatus(Payorder::STATUS_DOING,$body_code,$body_msg,$req_sn);
            $resultInfo =  ['res_code'=>$body_code,'res_data'=>$body_msg];
        }
        //异步通知客户端
        $oPayorder->clientNotify();
        //var_dump($resultInfo);
        return $resultInfo;
    }
    /**
     * 查询通道余额
     */
    public function getBalance($channelId){
        if(!$channelId){
            return ['res_code'=>-1,'res_data'=>"通道ID不能为空"];
        }
        $resBalance = $this->getApi($channelId)->cjBalance();
        if(empty($resBalance)){
            return ['res_code'=>'200_error','res_data'=>'访问超时'];
        }
        $info_code  = isset($resBalance['INFO']['RET_CODE'])?$resBalance['INFO']['RET_CODE']:'';//报头信息
        $info_msg   = isset($resBalance['INFO']['ERR_MSG'])?$resBalance['INFO']['ERR_MSG']:'';
        $balance     = isset($resBalance['BODY']['USABLE_BALANCE'])?$resBalance['BODY']['USABLE_BALANCE']:'';//余额
        if($info_code=='0000'){
            return ['res_code'=>0,'res_data'=>($balance/100)];
        }else{
            return ['res_code'=>$info_code,'res_data'=>$info_msg];
        }
    }

    /**
     * 对账文件下载
     */
    public function getStatement($channelId,$serchTime){
        if(!$channelId){
            return ['res_code'=>-1,'res_data'=>"通道ID不能为空"];
        }
        if(!$serchTime){
            return ['res_code'=>-1,'res_data'=>"账单日期不能为空"];
        }
        $resBill = $this->getApi($channelId)->statement($serchTime);
        var_dump($resBill);exit;
        if(empty($resBill)){
            return ['res_code'=>'200_error','res_data'=>'访问超时'];
        }
        $info_code  = isset($resBalance['INFO']['RET_CODE'])?$resBalance['INFO']['RET_CODE']:'';//报头信息
        $info_msg   = isset($resBalance['INFO']['ERR_MSG'])?$resBalance['INFO']['ERR_MSG']:'';
        $balance     = isset($resBalance['BODY']['USABLE_BALANCE'])?$resBalance['BODY']['USABLE_BALANCE']:'';//余额
        if($info_code=='0000'){
            return ['res_code'=>0,'res_data'=>($balance/100)];
        }else{
            return ['res_code'=>$info_code,'res_data'=>$info_msg];
        }
    }

    /**
     * $desc 处理时间内异常订单
     * @return int
     */
    public function runQuery($start_time, $end_time) {
        $model = new CjtOrder();
        $dataList =$model->getAbnorList($start_time, $end_time);
        //var_dump($dataList);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $cjtOrder) {
                $result = $this->orderQuery($cjtOrder);
                if ($result['res_code'] == 0) 
                    $success++;
            }
        }
        //5 返回结果
        return $success;
    }
    /**
     * Undocumented function
     *
     * @param [type] $cjtOrder
     * @return void
     */
    private function  orderQuery($cjtOrder){
        //1.判断订单状态
        if($cjtOrder->status!=Payorder::STATUS_DOING) return false;
        //2. 同步请求接口
        $resPay = $this->getApi($cjtOrder->channel_id)->orderQuery($cjtOrder);
        //var_dump($resPay);die;
        if(empty($resPay)){
             //超时
            return ['res_code'=>-1,'res_data'=>"请求超时，稍后重试"];
        }
        $info_code  = isset($resPay['INFO']['RET_CODE'])?$resPay['INFO']['RET_CODE']:'';//报头信息
        $info_msg   = isset($resPay['INFO']['ERR_MSG'])?$resPay['INFO']['ERR_MSG']:'';
        $body_code  = isset($resPay['BODY']['RET_CODE'])?$resPay['BODY']['RET_CODE']:'';//报体信息
        $body_msg   = isset($resPay['BODY']['RET_MSG'])?$resPay['BODY']['RET_MSG']:'';
        //报头请求错误
        if(in_array($info_code,$this->infoErrCode)){
            return ['res_code'=>$info_code,'res_data'=>$info_msg];
        }
        $resultInfo = [];
        //报体业务处理
        if($body_code=='0000'){
            //处理成功
            $result = $cjtOrder->savePaySuccess('');
            $resultInfo =  ['res_code'=>0,'res_data'=>'操作成功'];
        }else if(in_array($body_code,$this->bodyErrCode)){
            //订单失败
            $result = $cjtOrder->savePayFail($body_code, $body_msg);
            $resultInfo =  ['res_code'=>$body_code,'res_data'=>$body_msg];
        }
        //异步通知客户端
        $cjtOrder->payorder->clientNotify();
        //var_dump($resultInfo);
        return $resultInfo;
        
    }
}

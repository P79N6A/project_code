<?php
/**
 * 畅捷快捷支付
 */
namespace app\modules\api\common\cjquick;

use app\common\Logger;
use app\models\cjt\CjQuickOrder;
use app\modules\api\common\cjquick\CjquickApi;
use app\modules\api\common\jd\CJdquick;
use Yii;
use app\models\BindBank;
use app\models\Payorder;
use yii\helpers\ArrayHelper;
class CCjquick {

    //短信可以重新获取的错误码 分两类
    private static $smsSendCode = ['QT100025','QT100024','QT700001'];
    private static $smsSendCodes = ['FIELD_TYPE_ERROR'];
    public function init() {
        parent::init();
    }
    /**
     * 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod102
     */
    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg = $is_prod ? "prod{$channel_id}" : 'dev';
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
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new CjquickApi($cfg);
        }
        return $map[$channel_id];
    }
    /**
     * 获取请求链接地址
     * 错误码 2000-2020
     */
    public function createOrder($oPayorder) {
        //1  基本参数检验
        if (!isset($oPayorder['orderid']) || empty($oPayorder['orderid'])) {
            return ['res_code' => 2001, 'res_data' => '订单号不可为空'];
        }
        $identityid = $oPayorder['identityid'];
        if (!$identityid) {
            return ['res_code' => 2002, 'res_data' => 'identityid不可为空'];
        }

        //2 保存到一键支付数据表
        $postData = $oPayorder->attributes;
        $postData['payorder_id'] = $postData['id'];
        $oQuickOrder = $this->saveBindOrder($postData);
        if (!$oQuickOrder) {
            return ['res_code' => 2003, 'res_data' => '订单保存失败'];
        }
        //3. 判断状态是否正确
        /*if (!in_array($oQuickOrder->status, [Payorder::STATUS_NOBIND, Payorder::STATUS_BIND])) {
            return ['res_code' => 2004, 'res_data' => '订单状态不合法'];
        }*/

        //4. 同步主订单状态
        $oPayorder->saveStatus($oQuickOrder->status);
        //如果是信用卡支付，使用另一接口通道
       /* if($oPayorder->card_type==2){
            return $this->creditPay($oQuickOrder);
        }*/
        $res = $oQuickOrder->getPayUrls();
        Logger::dayLog('cjquick', 'getPayUrls', $res);
        return ['res_code' => 0, 'res_data' => $res];
    }
    /**
     * Undocumented function
     * 畅捷信用卡支付
     * @param [type] $oPayorder
     * @return void
     */
    public function creditPay($oCjOrder){
        if(empty($oCjOrder)) return false;
        if($oCjOrder->bankcardtype!=2) return false;
        $result = $oCjOrder->saveStatus(Payorder::STATUS_DOING,'');
        $postData = $oCjOrder->attributes;
        $postData['phone'] = $oCjOrder->payorder->phone;
        //$postData['return_url'] = $oCjOrder->payorder->callbackurl;
        $postData['notify_url'] = Yii::$app->request->hostInfo.'/cjpay/backpay/'.$this->getCfg($oCjOrder->channel_id);
        //var_dump($postData);die;
        $result = $this->getApi($oCjOrder->channel_id)->creditPay($postData);
        $errResult = json_decode($result,true);
        $AcceptStatus   = isset($errResult['AcceptStatus'])?$errResult['AcceptStatus']:'';
        $RetCode        = isset($errResult['RetCode'])?$errResult['RetCode']:'';
        $RetMsg         = isset($errResult['RetMsg'])?$errResult['RetMsg']:'';
        if($AcceptStatus=='F'){
             // 失败时处理
            $oCjOrder->savePayFail($RetCode, $RetMsg);
            $oCjOrder->payorder->clientNotify();
            return ['res_code' => $RetCode, 'res_data' => $RetMsg];
        }else{
            echo $result;
        }
    }
     /**
     * 保存绑卡和订单信息
     * @param  [] $data
     * @return obj
     */
    private function saveBindOrder($data) {
        //1 获取并保存绑卡信息
       /* $oBind = $this->getBindBank($data);
        if (empty($oBind)) {
            return null;
        }

        //2 保存订单
        $data['bind_id'] = $oBind->id;
        $data['status'] = $oBind->status == BindBank::STATUS_BINDOK ? Payorder::STATUS_BIND : Payorder::STATUS_NOBIND;*/
        $oQuickOrder = new CjQuickOrder();
        $result = $oQuickOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('cjquick', "cj/saveBindOrder", $oQuickOrder->attributes, $oQuickOrder->errors);
        }
        return $result ? $oQuickOrder : null;
    }
    /**
     * 获取并保存绑定信息
     * @param [] $data
     * @return  object
     */
    private function getBindBank($data) {
        //1 获取绑定信息
        $oBind = (new BindBank)->getBindBankInfo($data['aid'], $data['identityid'], $data['cardno'], $data['channel_id']);
        if (empty($oBind)) {
            $bindData = [
                'channel_id' => $data['channel_id'],
                'aid' => $data['aid'],
                'identityid' => $data['identityid'],
                'idcard' => $data['idcard'],
                'name' => $data['name'],
                'cardno' => $data['cardno'],
                'card_type' => $data['card_type'],
                'phone' => $data['phone'],
                'bankname' => $data['bankname'],
                'userip' => $data['userip'],
            ];
            $oBind = new BindBank;
            $result = $oBind->saveOrder($bindData);
            if (!$result) {
                Logger::dayLog('cjquick', "cjquick/getBindBank", $oBind->attributes, $oBind->errinfo);
            }
            return $result ? $oBind : null;
        }
        
        return $oBind;
    }
    /**
     * Undocumented function
     * 畅捷支付请求
     * @param [type] $oCjOrder
     * @return void
     */
    public function pay($oCjOrder){
        if(empty($oCjOrder)) return false;
        $postData = $oCjOrder->attributes;
        $postData['phone'] = $oCjOrder->payorder->phone;
        $postData['notify_url'] = Yii::$app->request->hostInfo.'/cjpay/backpay/'.$this->getCfg($oCjOrder->channel_id);
        //var_dump($postData);die;
        $result = $this->getApi($oCjOrder->channel_id)->pay($postData);
        Logger::dayLog('cjquick/cjquick', 'pay/获取验证码：',$result);
        //var_dump($result);
        $AcceptStatus   = isset($result['AcceptStatus'])?$result['AcceptStatus']:'';
        $RetCode        = isset($result['RetCode'])?$result['RetCode']:'';
        $RetMsg         = isset($result['RetMsg'])?$result['RetMsg']:'';
        if($AcceptStatus=='S'){
            //成功时处理
            $result = $oCjOrder->saveStatus(Payorder::STATUS_PREDO,'');
        }else{
             // 失败时处理
            $result = $oCjOrder->savePayFail($RetCode, $RetMsg);
        }
        //返回当前状态
        return $oCjOrder->status;
    }
    /**
     * Undocumented function
     * 畅捷订单确认
     * @param [type] $oCjOrder
     * @param [type] $smscode
     * @return void
     */
    public function confirmPay($oCjOrder,$smscode){
        if(empty($oCjOrder)) return false;
        if(empty($smscode)) return false;
        $postData = $oCjOrder->attributes;
        $postData['smscode'] = $smscode;

        $result = $oCjOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            #todo
            Logger::dayLog('jd/confirmPay', '修改状态失败', $result->errors);
        }

        $result = $this->getApi($oCjOrder->channel_id)->confirmPay($postData);
        Logger::dayLog('cjquick/cjquick', 'confirmPay/请求支付结果：',$result);
        if(empty($result)){
             //超时
            $oCjOrder->saveStatus(Payorder::STATUS_DOING,'');
            //返回当前状态
            return $oCjOrder->status;
        }
        $AcceptStatus   = isset($result['AcceptStatus'])?$result['AcceptStatus']:'';//请求状态
        $RetCode        = isset($result['RetCode'])?$result['RetCode']:'';
        $RetMsg         = isset($result['RetMsg'])?$result['RetMsg']:'';
        $Status         = isset($result['Status'])?$result['Status']:'';//业务状态
        $OrderTrxid     = isset($result['OrderTrxid'])?$result['OrderTrxid']:'';//畅捷订单流水号

        $AppRetcode        = isset($result['AppRetcode'])?$result['AppRetcode']:'';
        $AppRetMsg        = isset($result['AppRetMsg'])?$result['AppRetMsg']:'';
        //请求的状态码
        if(!in_array($AcceptStatus,['F','S'])){
            $oCjOrder->saveStatus(Payorder::STATUS_DOING,'',['ERROR', '请求状态码不存在！']);
            #$result = $oCjOrder->savePayFail('ERROR', '信息错误请重新发起请求！');
        }
        if($AcceptStatus =='F'){
            if(in_array($AppRetcode,self::$smsSendCode)){
                $oCjOrder->saveStatus(Payorder::STATUS_PREDO,'',[$AppRetcode, $AppRetMsg]);
                return $oCjOrder->status;
            }elseif(in_array($RetCode,self::$smsSendCodes)){
                $oCjOrder->saveStatus(Payorder::STATUS_PREDO,'',[$RetCode, '短信验证码错误！']);
                return $oCjOrder->status;
            }else{
                // 失败时处理
                $oCjOrder->saveStatus(Payorder::STATUS_DOING,$OrderTrxid,[$AppRetcode, $AppRetMsg]);
            }
        }
        if($AcceptStatus =='S'){
            if($Status =='S'){
                //成功时处理
                $oCjOrder->refresh();
                $result = $oCjOrder->savePaySuccess($OrderTrxid);
            }else if($Status =='F'){
                //失败
                $oCjOrder->refresh();
                $result = $oCjOrder->savePayFail($RetCode, $RetMsg);
            }else{
                //处理中
                $oCjOrder->saveStatus(Payorder::STATUS_DOING,$OrderTrxid,[$AppRetcode, $AppRetMsg]);
            }
        }

        if (in_array($oCjOrder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])) {
            $oCjQuickOrder = new CjQuickOrder();
            $oCjOrder_info = $oCjQuickOrder->getByCjId($oCjOrder->id);
            $oCjOrder_info->payorder->clientNotify();
        }
         //异步通知客户端
        return $oCjOrder->status;
    }
    /**
     * Undocumented function
     * 更新绑卡信息
     * @param [type] $oCjOrder
     * @return void
     */
    public function updateBindBank($oCjOrder){
        $bind_id = $oCjOrder->bind_id;
        if(empty($bind_id)) return false;
        $bindBank = (new BindBank)->getById($bind_id);
         Logger::dayLog('cjquick', "cjquick/updateBindBank", $bind_id, $bindBank->attributes);
        if(empty($bindBank)) return false;
        if($bindBank->status==BindBank::STATUS_BINDOK) return false;
        $up_data = [
            'status'        => BindBank::STATUS_BINDOK,
            'validate'      => $oCjOrder->expiry_date,
            'cvv2'          => $oCjOrder->cvv2,
            'modify_time'   => date('Y-m-d H:i:s')
        ];
        $result = (new BindBank)->updateBindBank($up_data,['id'=>$bind_id]);
        if(!$result){
            Logger::dayLog('cjquick', "cjquick/updateBindBank", $up_data, $oCjOrder->attributes);
        }
        return $result;
    }
    /**
     * $desc 处理时间内异常订单
     * @return int
     */
    public function runQuery($start_time, $end_time) {
        $model = new CjQuickOrder();
        $dataList =$model->getAbnorList($start_time, $end_time);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $oCjOrder) {
                $result = $this->orderQuery($oCjOrder);
                if (isset($result['res_code']) && $result['res_code'] == 0) 
                    $success++;
            }
        }
        //5 返回结果
        return $success;
    }
    /**
     * Undocumented function
     * 处理处理中的订单
     * @param [type] $oCjOrder
     * @return void
     */
    private function orderQuery($oCjOrder){

        //1.判断订单状态
        if($oCjOrder->status!=Payorder::STATUS_DOING) return false;
        $postData = $oCjOrder->attributes;
        $result = $this->getApi($oCjOrder->channel_id)->queryOrder($postData);
        if(empty($result)){
             //超时
            return ['res_code'=>-1,'res_data'=>"请求超时，稍后重试"];
        }
        //var_dump($result);die;
        $AcceptStatus   = isset($result['AcceptStatus'])?$result['AcceptStatus']:'';//请求状态
        $RetCode        = isset($result['RetCode'])?$result['RetCode']:'';
        $RetMsg         = isset($result['RetMsg'])?$result['RetMsg']:'';
        $Status         = isset($result['Status'])?$result['Status']:'';//业务状态
        $OrderTrxid     = isset($result['OrderTrxid'])?$result['OrderTrxid']:'';//畅捷订单流水号
        if($AcceptStatus=='F'){
             return ['res_code'=>$RetCode,'res_data'=>$RetMsg];
        }

        $resultInfo = [];
        if($Status=='S'){
            //成功时处理
            $result = $oCjOrder->savePaySuccess($OrderTrxid);
            //更新绑卡表信息
            #$this->updateBindBank($oCjOrder);
            $resultInfo =  ['res_code'=>0,'res_data'=>'操作成功'];
        }else if($Status=='F'){
            //失败
            $result = $oCjOrder->savePayFail($RetCode, $RetMsg,$OrderTrxid);
            $resultInfo =  ['res_code'=>$RetCode,'res_data'=>$RetMsg];
        }
         //异步通知客户端
        $oCjOrder->payorder->clientNotify();
        //var_dump($resultInfo);
        return $resultInfo;
    }
    /**
     * Undocumented function
     * 发送短信
     * @param [type] $oCjOrder
     * @return void
     */
    public function reSend($oCjOrder){
        if($oCjOrder->status!=Payorder::STATUS_PREDO) return false;
        $postData = $oCjOrder->attributes;
        $result = $this->getApi($oCjOrder->channel_id)->reSend($postData);
        Logger::dayLog('cjquick/cjquick', 'reSend/重复获取验证码：',$result);
        if(empty($result)){
             //超时
            return ['res_code'=>-1,'res_data'=>"请求超时，稍后重试"];
        }
        $AcceptStatus   = isset($result['AcceptStatus'])?$result['AcceptStatus']:'';//请求状态
        $RetCode        = isset($result['RetCode'])?$result['RetCode']:'';
        $RetMsg         = isset($result['RetMsg'])?$result['RetMsg']:'';
        $Status         = isset($result['Status'])?$result['Status']:'';//业务状态
        if($AcceptStatus=='F'){
             return false;
        }
        if(empty($Status) || $Status=='F'){
            return false;
        }
        return true;
    }

    /**
     * 查询卡bin信息
     */
    public function getCardInfo($data){
        if(!$data['cardno']){
            return ['code'=>-1,'data'=>"卡号不能为空"];
        }
        $resCardInfo = $this->getApi($data['channelId'])->cjCardInfo($data['cardno']);
        Logger::dayLog('cjt/getCardInfo','请求参数',$data,'畅捷返回', $resCardInfo);//记录日志
//        print_r($resBalance);die;
        if(empty($resCardInfo)){
            return ['code'=>'200_error','data'=>'访问超时'];
        }
        //var_dump($result);die;
        $AcceptStatus   = isset($resCardInfo['AcceptStatus'])?$resCardInfo['AcceptStatus']:'';//请求状态
        $RetCode        = isset($resCardInfo['RetCode'])?$resCardInfo['RetCode']:'';
        $RetMsg         = isset($resCardInfo['RetMsg'])?$resCardInfo['RetMsg']:'';
        if($AcceptStatus=='F'){
            return ['res_code'=>$RetCode,'res_data'=>$RetMsg];
        }

        $OriginalRetCode = isset($resCardInfo['OriginalRetCode'])?$resCardInfo['OriginalRetCode']:'';//原交易返回代码
        $resultInfo = [];
        if($OriginalRetCode=='000000'){
            //成功时处理
            $result = $this->dealResData($resCardInfo);
            $code = 200;
            if(!$result){
                $code = -1;  $result = '卡无效';
            }
            return ['code'=>$code,'data'=>$result];
        }else{
            //失败
            return ['code'=>-1,'data'=>'获取卡bin失败'];
        }
    }
    /**
     * 更新卡bin等信息
     */
    private function dealResData($data){
        //处理请求参数
        $is_valid = isset($data['IsValid'])?$data['IsValid']:'';//卡号是否有效
        if($is_valid != '是'){
            return false;
        }
        $res['card_bin'] = isset($data['CardBin'])?$data['CardBin']:'';//卡bin
        $card_type = isset($data['CardType'])?$data['CardType']:'';//卡类型：借记卡/贷记卡
        $res['card_type'] = ($card_type == 'DC') ? 1 : 2; //1：储蓄卡  2 信用卡
        $res['card_name'] = isset($data['CardName'])?$data['CardName']:'';//卡名称
        $res['common_name'] = isset($data['BankCommonName'])?$data['BankCommonName']:'';//通用银行名称
        return $res;
    }
}
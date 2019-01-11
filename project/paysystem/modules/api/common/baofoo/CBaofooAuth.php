<?php
namespace app\modules\api\common\baofoo;

use app\common\Logger;
use app\models\Payorder;
use app\models\baofoo\BfBindbank;
use app\models\baofoo\BfAuthOrder;
use app\models\StdError;
use Yii;
use app\modules\api\common\baofoo\CBack;

/**
 * @desc 宝付认证API
 * @author lubaba
 */
class CBaofooAuth {

    private $bfAuthOrder;
    //交易结果暂未知，需查询类
    private $bfQueryCode = ['BF00100','BF00112','BF00113','BF00115','BF00144','BF00202','BF00190','100401'];

    /**
     * @desc 获取此通道对应的配置
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
     * @desc 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    public function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new BaofooApi($cfg);
        }
        return $map[$channel_id];
    }

    /**
     * 查询通道余额
     */
    public function getBalance($channelId){
        if(!$channelId){
            return ['res_code'=>-1,'res_data'=>"通道ID不能为空"];
        }
        $result = $this->getApi($channelId)->getBalance();
        Logger::dayLog('bfauth/getbalance', '查询通道余额', $channelId, '宝付结果', $result);
        if(empty($result)){
            return ['res_code'=>'200_error','res_data'=>'访问超时'];
        }
        $return_code = isset($result['trans_content']['trans_head']['return_code'])?$result['trans_content']['trans_head']['return_code']:'';
        $return_msg = isset($result['trans_content']['trans_head']['return_msg'])?$result['trans_content']['trans_head']['return_msg']:'';
        $balance = isset($result['trans_content']['trans_reqDatas']['trans_reqData']['balance'])?$result['trans_content']['trans_reqDatas']['trans_reqData']['balance']:'';
        if($return_code=='0000'){
            return ['res_code'=>0,'res_data'=>$balance];
        }else{
            return ['res_code'=>$return_code,'res_data'=>$return_msg];
        }
    }

    /**
     * @desc 创建支付订单
     * @param  obj $oPayorder
     * @return  [res_code,res_data]
     */
    public function createOrder($oPayorder) {
        //1. 数据检测
        if (empty($oPayorder)) {
            return StdError::returnStdError($oPayorder->channel_id,"0011");
            //return ['res_code' => 2631, 'res_data' => '没有提交数据！'];
        }
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];
        //2. 绑定银行卡
        $res = $this->getBindBank($data);
        if ($res['res_code'] != '0') {
            return ['res_code' => $res['res_code'], 'res_data' => $res['res_data']];
        }
        $oBind = $res['res_data'];
        $data['bind_id'] = $oBind->id;
        $data['cli_identityid'] = $oBind->cli_identityid;
        $data['status'] = Payorder::STATUS_BIND;

        //3. 字段检查是否正确
        $this->bfAuthOrder=$bfAuthOrder = new BfAuthOrder();
        $result = $bfAuthOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('bfauth/createOrder', '提交数据', $data, '失败原因', $bfAuthOrder->errors);
            return StdError::returnStdError($oPayorder->channel_id,"0012");
            //return ['res_code' => 2636, 'res_data' => '订单保存失败'];
        }
        //5. 同步主订单状态
        $result = $oPayorder->saveStatus($bfAuthOrder->status);
        //6. 返回下一步处理流程
        $res_data = $bfAuthOrder->getPayUrls();
        Logger::dayLog('bfauth', 'getPayUrls', $res_data);
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    /**
     * @desc 获取绑卡信息
     * @param  [] $data
     * @return [res_code,res_data]
     */
    private function getBindBank($data) {
        $oBind = (new BfBindbank)->getSameUserCard(
            $data['aid'],
            $data['channel_id'],
            $data['identityid'],
            $data['cardno']
        );
        if ($oBind) {
            return ['res_code' => 0, 'res_data' => $oBind];
        }

        return $this->bindCard($data);
    }
    /**
     * @desc 根据订单号进行直接绑卡
     * @param  [] $data
     * @return [res_code,res_data]
     */
    private function bindCard($data) {
        //1. 保存到宝付绑卡表中
        $oBind = new BfBindbank;
        $result = $oBind->saveCard($data);
        if (!$result) {
            return StdError::returnStdError($oBind->channel_id,"0014");
            //return ['res_code' => 26001, 'res_data' => '数据保存失败'];
        }
        //2. 组合四要素等参数
        $requestid = $oBind->requestid;
        $bfData = [
            'trans_serial_no' => time().uniqid('baofoo'), //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'trans_id' => $requestid, //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'acc_no' => $oBind->cardno, //银行卡号√string
            'id_card' => $oBind->idcard, //证件号√string
            'id_holder' => $oBind->name, //持卡人姓名√string
            'mobile' => $oBind->phone, //银行预留手机号√string
            'pay_code'=>$oBind->baofooBank->baofoo_bankcode,
            'trade_date'=>date('YmdHis',strtotime($oBind['create_time']))
        ];
        $bfResult = $this->getApi($oBind['channel_id'])->directBinding($bfData);
        //5 保存结果状态
        $result = $oBind->saveRspStatus($bfResult);
        if (!$result) {
            return StdError::returnStdError($oBind->channel_id,"0015");
            //$error_msg = $oBind->error_msg ? $oBind->error_msg : '';
            //$error_msg = '更新绑卡信息失败';
            //return ['res_code' => 26002, 'res_data' => $error_msg];

        }
        if (is_array($bfResult) && $bfResult['resp_code']!='0000') {
            return StdError::returnThirdStdError($oBind->channel_id,$oBind->error_code,$oBind->error_msg);
        }
        return ['res_code' => 0, 'res_data' => $oBind];
    }

    /**
     * @desc 预支付交易
     * @param  object $bfauthDetail
     * @return  int $status 
     */
    public function prepPay($bfauthDetail){
        $bfData = [
            'trans_serial_no' => time().uniqid('baofoo'), //     
            'trans_id' => $bfauthDetail->cli_orderid, 
            'bind_id' => $bfauthDetail->bfBindbank->return_bindingid, 
            'txn_amt' => $bfauthDetail->amount, 
            'trade_date' => date('YmdHis',strtotime($bfauthDetail->create_time)),
            'risk_content' => ['client_ip'=>$bfauthDetail->userip]
        ];
        //生产环境
        // if ((defined('SYSTEM_LOCAL') && SYSTEM_LOCAL)) {
        //     $bfData['bind_id'] = '201604271949318660';
        //     $bfData['trans_serial_no'] = $bfauthDetail->cli_orderid.rand(1000,9999);
        //     $bfData['trans_id'] = $bfauthDetail->cli_orderid;
        // }
        $bfResult = $this->getApi($bfauthDetail->channel_id)->prepPay($bfData);
        Logger::dayLog('bfauth/prepPay', '预支付', $bfData, '宝付结果', $bfResult);
        //2. 保存结果信息
        if (is_array($bfResult) && $bfResult['resp_code']!= '0000') {
            // 失败时处理
            $result = $bfauthDetail->savePayFail($bfResult['resp_code'], $bfResult['resp_msg']);
//            Logger::dayLog('bfauth/prepPay', '预支付/预支付成功', $bfData, '宝付结果', $bfResult);
        }else{
            //成功时处理
            $result = $bfauthDetail->saveStatus(Payorder::STATUS_PREDO, $bfResult['business_no']);
            Logger::dayLog('bfauth/prepPay', '预支付/预支付失败', $bfData, '宝付结果', $bfResult);
        }
        //3. 返回当前状态
        return $bfauthDetail->status;
    }

    /**
     * @desc 支付结果
     * @param  object $bfauthDetail
     * @return int 支付状态.
     */
    public function confirmPay($bfauthDetail,$validatecode) { 
        //1. 增加状态锁定
        $result = $bfauthDetail->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return -1;
        }
        $bfData = [
            'trans_serial_no' => time().uniqid('baofoo'), //     
            'business_no' => $bfauthDetail->other_orderid, //   
            'sms_code' => $validatecode, // 
            'trade_date' => date('YmdHis',strtotime($bfauthDetail->create_time)) //   
        ];
        $bfResult = $this->getApi($bfauthDetail->channel_id)->confirmPay($bfData);
        Logger::dayLog('bfauth/confirmPay', '确定支付', $bfData, '宝付结果', $bfResult);
        //2. 保存结果信息
        if(is_array($bfResult)){
           if($bfResult['resp_code'] == "0000"){
                //成功时处理
                $bfauthDetail->refresh();
                $result = $bfauthDetail->savePaySuccess($bfResult['business_no']);
            }elseif(in_array($bfResult['resp_code'],$this->bfQueryCode)){
                ////交易结果暂未知，需查询类
               Logger::dayLog('bfauth/confirmPay', '确定支付/支付结果不明确', $bfData, '宝付结果', $bfResult);
            }else{
                // 失败时处理
                $result = $bfauthDetail->savePayFail($bfResult['resp_code'],$bfResult['resp_msg']);
               Logger::dayLog('bfauth/confirmPay', '确定支付/支付失败', $bfData, '宝付结果', $bfResult);
            }
        }
        //3. 返回当前状态
        return $bfauthDetail->status;
    }

    /**
     * @desc 宝付认证 查询指定时间内异常订单并处理
     * @param string $start_time 
     * @param string $end_time 
     */
    public function authPayQueryMinute($start_time,$end_time){
        $model = new BfAuthOrder();
        $dataList =$model->getAbnorList($start_time,$end_time);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $bfauthOrder) {
            $result = $this->authpayQuery($bfauthOrder);
            if ($result['res_code'] == 0) 
                $success++;
            }
        }
        //5 返回结果
        return $success;
    }

    /**
     * @desc 宝付认证 查询指定时间内状态为3、8订单并处理
     * @param string $start_time 
     * @param string $end_time 
     */
    public function authPayQueryProcess($start_time,$end_time){
        $model = new BfAuthOrder();
        $dataList =$model->getProcessList($start_time,$end_time);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $bfauthOrder) {
            $result = $this->authpayQuery($bfauthOrder);
            if ($result['res_code'] == 0) 
                $success++;
            }
        }
        //5 返回结果
        return $success;
    }
    


    /* 
     * @desc 宝付认证 查询异常单处理
     * @param $bfauthOrder
     * @return array
     */
    public function authpayQuery($bfauthOrder){
        //1.条件判断
        if($bfauthOrder->status != Payorder::STATUS_DOING){
            return ['res_code'=>1050052,'res_data'=>'订单状态有误'];
        }
        $queryData = [];
        $queryData['trans_serial_no'] = time().uniqid('baofoo');
        $queryData['orig_trans_id'] = $bfauthOrder->cli_orderid;
        $queryData['orig_trade_date'] = date('YmdHis',strtotime($bfauthOrder->create_time));
        $resPay = $this->getApi($bfauthOrder->channel_id)->queryPay($queryData);
        $result = false;
        if(!empty($resPay)){
            $oCBack = new CBack;
            if($resPay['resp_code'] == '0000' && $resPay['order_stat'] == 'S'){
                $other_orderid = isset($bfauthOrder->other_orderid)?$bfauthOrder->other_orderid:'';
                $result = $bfauthOrder->savePaySuccess($other_orderid);
                //异步通知客户端
                // $resnotice = $bfauthOrder->payorder->clientNotify();
                $res = $oCBack->clientNotify($bfauthOrder);
                if (!$res) {
                    Logger::dayLog('bfauth', 'bfauth/clientNotify','异步回调通知客户端失败');
                }
            }elseif($resPay['order_stat'] == 'F' || $resPay['order_stat'] == 'FF' ){
                $result = $bfauthOrder->savePayFail($resPay['resp_code'], $resPay['resp_msg']);
                //异步通知客户端
                // $resnotice = $bfauthOrder->payorder->clientNotify();
                $res = $oCBack->clientNotify($bfauthOrder);
                if (!$res) {
                    Logger::dayLog('bfauth', 'bfauth/clientNotify','异步回调通知客户端失败');
                }
            }else if($resPay['order_stat'] == 'I' && $resPay['resp_code'] == 'BF00251'){
                //订单未支付
                $result = $bfauthOrder->savePayFail($resPay['resp_code'], $resPay['resp_msg']);
                //异步通知客户端
                // $resnotice = $bfauthOrder->payorder->clientNotify();
                $res = $oCBack->clientNotify($bfauthOrder);
                if (!$res) {
                    Logger::dayLog('bfauth', 'bfauth/clientNotify','异步回调通知客户端失败');
                }
            }
        }
        if(!$result){
            return StdError::returnStdError($bfauthOrder->channel_id,"0013");
            //return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        }
        //  //标准化输出
        //return $baofooOrder->payorder->clientData();
        return ['res_code'=>0,'res_data'=>'操作成功'];
    }
    
    /**
     * @desc 宝付认证 查询指定时间内异常绑卡记录
     * @param string $start_time 
     * @param string $end_time 
     * @return int 
     */
    public function runBindingMinute($start_time,$end_time){
        $model = new BfBindbank();
        $dataList =$model->getAbnorList($start_time,$end_time);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $bfbindOrder) {
            $result = $this->authbindingQuery($bfbindOrder);
            if ($result['res_code'] == 0) 
                $success++;
            }
        }
        //5 返回结果
        return $success;
    }

    /* 
     * @desc 宝付认证 查询异常绑卡记录处理
     * @param $bfbindOrder
     * @return array
     */
    public function authbindingQuery($bfbindOrder){
        //1.条件判断
        if($bfbindOrder->status != Payorder::STATUS_DOING){
            return ['res_code'=>1050052,'res_data'=>'订单状态有误'];
        }
        $queryData = [];
        $queryData['trans_serial_no'] = time().uniqid('baofoo');
        $queryData['acc_no'] = $bfbindOrder->cardno;
        $queryData['trade_date'] = date('YmdHis',strtotime($bfbindOrder->create_time));
        $resPay = $this->getApi($bfbindOrder->channel_id)->queryBinding($queryData);
        $result = false;
        if(!empty($resPay) && $resPay['resp_code'] == '0000'){
            if($resPay['bind_id']){
                $result = $bfbindOrder->updateStatus($bfbindOrder['requestid'], Payorder::STATUS_PAYOK,$resPay['bind_id']);
            }else{
                $result = $bfbindOrder->updateStatus($bfbindOrder['requestid'], Payorder::STATUS_PAYFAIL,'');
            }
        }
        if(!$result){
            return StdError::returnStdError($bfbindOrder->channel_id,"0013");
            //return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        }
        //if(!$result) return ['res_code'=>1050054,'res_data'=>'同步更新订单失败'];
        //  //标准化输出
        //return $baofooOrder->payorder->clientData();
        return ['res_code'=>0,'res_data'=>'操作成功'];
    }
}

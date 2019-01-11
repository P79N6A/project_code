<?php
namespace app\modules\api\common\lianlian;
use app\common\Logger;
use app\models\lian\LianauthBindbank;
use app\models\lian\LianauthOrder;
use app\models\Payorder;
use Yii;

/**
 * 连连认证支付
 */
class CAuthlian {
    private $oLianApi;
    public function __construct() {

    }
    private function getCfg($channel_id){
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }
    /**
     * 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return []
     */
    private function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new LianApi($cfg);
        }
        return $map[$channel_id];
    }
    /**
     * 通过主订单调用支付流程
     * @param  obj $oPayorder
     * @return [res_code, res_data]
     */
    public function createOrder($oPayorder) {
        //1. 绑定银行卡, 保存当前订单信息
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];
        //2. 保存连连支付订单
        $oLianOrder = $this->saveBindOrder($data);
        if (!$oLianOrder) {
            return ['res_code' => 1080001, 'res_data' => '订单保存失败'];
        }
        //3. 判断状态是否正确
        if (!in_array($oLianOrder->status, [Payorder::STATUS_NOBIND, Payorder::STATUS_BIND])) {
            return ['res_code' => 1080003, 'res_data' => '订单状态不合法'];
        }
        //4. 同步主订单状态
        $result = $oPayorder->saveStatus($oLianOrder->status);
        //5. 返回下一步处理流程
        $res_data = $oLianOrder->getPayUrls();
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    /**
     * 保存绑卡和订单信息
     * @param  [] $data
     * @return obj
     */
    private function saveBindOrder($data) {
        //1 获取并保存绑卡信息
        $oBind = $this->getBindBank($data);
        if (empty($oBind)) {
            return null;
        }
        //2 保存连连订单
        $data['bind_id'] = $oBind->id;
        $data['status'] = $oBind->status == LianauthBindbank::STATUS_BINDOK ? Payorder::STATUS_BIND : Payorder::STATUS_NOBIND;
        $oLianOrder = $this->saveOrderInfo($data);
        return $oLianOrder;
    }
    
    /**
     * 认证支付结果
     * @param  object $oLianOrder
     * @return html
     */
    public function authpay($oLianOrder) {
        //1. 增加状态锁定
        $result = $oLianOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return -1;
        }
        //2. 转成元(目前是金额的单位是分)
        $amount = $oLianOrder->amount / 100;
        $notify_url = Yii::$app->request->hostInfo .'/lianauthpay/backpay/';
        $encryptId = $oLianOrder ->encryptId($oLianOrder->id);
        $url_return = Yii::$app->request->hostInfo .'/lianauthpay/returnurl/?xhhorderid='.$encryptId;
        //3. 银行卡绑定判断
        $user_id = $oLianOrder->getIdentityid($oLianOrder['identityid'], $oLianOrder['aid']);
        $oBind = (new LianauthBindbank)->getByBid($oLianOrder['bind_id']);
        if (!$oBind || !isset($oBind['bind_no'])) {
            return -1;
        }
        //3. 商品名称
        $oPayorder = $oLianOrder->payorder;
        if ($oPayorder) {
            $name_goods = $oPayorder['productname'];
            $info_order = $oPayorder['productdesc'];
        } else {
            $name_goods = '购买电子产品';
            $info_order = '购买电子产品';
        }
        $payData = [
            'user_id'=> $user_id,
            'no_order' => $oLianOrder['cli_orderid'],
            'dt_order' => date('YmdHis',strtotime($oLianOrder['create_time'])),
            'name_goods' => $name_goods,
            'info_order' => $info_order,
            'money_order' => $amount,
            'notify_url' => $notify_url,
            'url_return' => $url_return,
            'no_agree'=>$oBind['bind_no'],
            'id_no' => $oBind['idcard'],
            'acct_name' => $oBind['name'],
            'card_no' => $oBind['cardno'],
            'user_info_bind_phone' => $oBind['phone']
        ];
        $res = $this->getApi($oLianOrder['channel_id'])->authpay($payData);
        //
         if (!empty($res) && $res['res_code'] > 0) {
            return ['res_code' => 1080012, 'res_data' => "调用认证支付接口失败"];
        }
        return $res;
    }
    
    /**
     * 保存连连支付订单
     * @param  [] $data
     * @return [res_code, res_data]
     */
    private function saveOrderInfo($data) {
        $oLianOrder = new LianauthOrder;
        $result = $oLianOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('lian', "cauthlian/saveorderInfo", $oLianOrder->attributes, $oLianOrder->errors);
        }
        return $result ? $oLianOrder : null;
    }

    /**
     * 获取并保存绑定信息
     * @param [] $data
     * @return  object
     */
    private function getBindBank($data) {
        //1 获取绑定信息
        $oBind = (new LianauthBindbank)->getBindBankInfo($data['aid'], $data['identityid'], $data['cardno'], $data['channel_id']);
        if (empty($oBind)) {
            $bindData = [
                'channel_id' => $data['channel_id'],
                'aid' => $data['aid'],
                'identityid' => $data['identityid'],
                'idcard' => $data['idcard'],
                'name' => $data['name'],
                'cardno' => $data['cardno'],
                'card_type' => 1, // 连连仅支付借记卡
                'phone' => $data['phone'],
                'bankname' => $data['bankname'],
                'userip' => $data['userip'],
            ];
            $oBind = new LianauthBindbank;
            $result = $oBind->saveOrder($bindData);
            if (!$result) {
                Logger::dayLog('lian', "cauthlian/getBindBank", $oBind->attributes, $oBind->errinfo);
            }
            return $result ? $oBind : null;
        }
        return $oBind;
    }
    /**
     * 验签方法
     * @param  [] $json_data
     * @return bool
     */
    public function verifyJson($json_data, $channel_id) {
        $data = json_decode($json_data, true);
        return $this->getApi($channel_id)->notifyverify($data);
    }

    /**
     * @desc 连连认证 查询指定时间内异常订单并处理
     * @param string $start_time 
     * @param string $end_time 
     */
    public function runException($start_time,$end_time){
        $model = new LianauthOrder();
        $dataList =$model->getAbnorList($start_time,$end_time);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $oLianOrder) {
            $result = $this->authpayQuery($oLianOrder);
            if ($result['res_code'] == 0) 
                $success++;
            }
        }
        //5 返回结果
        return $success;
    }

    /* 
     * @desc 连连认证 查询异常单处理
     * @param $oLianOrder
     * @return array
     */
    public function authpayQuery($oLianOrder){
        //1.条件判断
        if($oLianOrder->status != Payorder::STATUS_DOING){
            return ['res_code'=>1080052,'res_data'=>'订单状态有误'];
        }
        $queryData = [];
        $queryData['no_order'] = $oLianOrder->cli_orderid;
        $queryData['dt_order'] = date('YmdHis',strtotime($oLianOrder->create_time));
        $resPay = $this->getApi($oLianOrder['channel_id'])->authquery($queryData);
        $result = false;
        if(!empty($resPay) && $resPay['ret_code'] == '0000'){
            if($resPay['result_pay'] == 'SUCCESS'){
                $other_orderid = $oLianOrder->other_orderid ? $oLianOrder->other_orderid : $resPay['oid_paybill'];
                $result = $oLianOrder->savePaySuccess($other_orderid);
            }elseif($resPay['result_pay'] == 'FAILURE'){
                $result = $oLianOrder->savePayFail('', $resPay['memo']);
            }
        }
        if(!$result) return ['res_code'=>1080054,'res_data'=>'同步更新订单失败'];
        //  //标准化输出
        return ['res_code'=>0,'res_data'=>'操作成功'];
    }
    /**
     * Undocumented function
     * 余额查询
     * @param [type] $channel_id
     * @return void
     */
    public function acctQuery($channel_id){
        $res = $this->getApi($channel_id)->acctquery();
        return $res;
    }
}
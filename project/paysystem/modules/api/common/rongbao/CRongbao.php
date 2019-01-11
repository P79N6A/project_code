<?php

namespace app\modules\api\common\rongbao;

use app\models\BindBank;
use app\models\Payorder;
use app\models\rongbao\RongbaoBindbank;
use app\models\rongbao\RongbaoOrder;
use app\common\Logger;
use Yii;

/**
 * 融宝支付类
 * @author YangJinlong
 */
class CRongbao {

    private $oRongApi;

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
            $map[$channel_id] = new RongbaoApi($cfg);
        }
        return $map[$channel_id];
    }

    /*
     * 新用户绑卡
     */

    public function newUserBindCard($rongOrder) {
        //生成绑卡记录
        $postData = $rongOrder->attributes;
        $model    = new RongbaoBindbank();
        $bindCard = $model->saveBindBank($postData);
        if (!$bindCard) {
            return ['res_code' => 1050001, 'res_data' => '绑卡记录生成失败'];
        }

        //参数数组
        $notify_url = Yii::$app->request->hostInfo . '/rongpay/notify/' . $this->getCfg($rongOrder->channel_id);
        $paramArr   = array(
            'card_no'       => $rongOrder->cardno,
            'owner'         => $rongOrder->name,
            'cert_no'       => $rongOrder->idcard,
            'phone'         => $rongOrder->phone,
            'order_no'      => $rongOrder->cli_orderid,
            'total_fee'     => $rongOrder->amount,
            'member_id'     => $rongOrder->identityid,
            'title'         => $rongOrder->productname, //商品名称
            'body'          => $rongOrder->productdesc,
            'notify_url'    => $notify_url,
            'transtime'     => date("Y-m-d H:i:s", time()), //交易时间
            'terminal_type' => 'web', //终端类型
            'terminal_info' => 'web', //终端信息
            'member_ip'     => Yii::$app->request->userIP,
            'token_id'      => md5(uniqid(mt_rand(), true)),
            'version'       => '3.1.3'
        );
        $res        = $this->getApi($rongOrder['channel_id'])->debit($paramArr);
        if ($res['result_code'] != '0000') {  //签约失败
            return ['res_code' => 1050002, 'res_data' => $res['result_msg']];
        }
        
        //绑卡请求成功后，修改绑卡记录的bind_no
        $updateBindId = $model->updateBindno($res['bind_id']);
        if (!$updateBindId) {  //绑卡列表修改bind_id 失败
            return ['res_code' => 1050004, 'res_data' => '修改绑卡ID失败'];
        }
        
        //更新订单绑卡ID
//        $updateOrderBindId = $this -> updateBindId($rongOrder , $model -> id);
//        if (!$updateOrderBindId) {  //绑卡列表修改bind_id 失败
//            return ['res_code' => 1050005, 'res_data' => '修改订单绑卡ID失败'];
//        }
        
        //保存订单状态为重发短信
        $saveOrder = $this -> saveOrderStatus($rongOrder ,Payorder::STATUS_SENDSMS);
        if (!$saveOrder) {
            return ['res_code' => 1050003, 'res_data' => '订单状态修改失败'];
        }
        return ['res_code' => 0, 'res_data' => '绑卡请求成功'];
    }
    
    /*
     * 信用卡签约(暂时有问题 需要设计页面)
     */
    public function cEbitBindCard($rongOrder){
        $notify_url = Yii::$app->request->hostInfo . '/rongpay/notify/' . $this->getCfg($oPayorder->channel_id);
        $paramArr = array(
            'card_no'       => $rongOrder->cardno,
            'owner'         => $rongOrder->name,
            'cert_no'       => $rongOrder->idcard,
            'phone'         => $rongOrder->phone,
            'cvv2'          => $_REQUEST['cvv2'],
            'validthru'     => $_REQUEST['validthru'],
            'order_no'      => $rongOrder->cli_orderid,
            'transtime'     => date("Y-m-d H:i:s", time()), //交易时间
            'total_fee'     => $rongOrder->amount,
            'title'         => $rongOrder->productname, //商品名称
            'body'          => $rongOrder->productdesc,
            'member_id'     => $rongOrder->identityid,
            'terminal_type' => 'web', //终端类型
            'terminal_info' => 'web', //终端信息
            'member_ip'     => Yii::$app->request->userIP,
            'token_id'      => md5(uniqid(mt_rand(), true)),
            'notify_url'    => $notify_url,
            'version'       => '3.1.3'
        );
    }

    /*
     * 卡密验证
     */

    public function certificate($oRongOrder) {
        $notify_url = Yii::$app->request->hostInfo . '/rongpay/catdnotify/' . $this->getCfg($oRongOrder->channel_id);
        //参数数组
        $paramArr = array(
            "member_id"     => $oRongOrder -> identityid,
            "bind_id"       => '14439820',
            'order_no'      => $oRongOrder -> cli_orderid, //商户生成的唯一订单号
            "return_url"    => 'http://www.baidu.com',
            "notify_url"    => $notify_url,
            "terminal_type" => "web",
            'version'       => '3.1.3'                   //版本控制默认3.0
        );
        return $this->getApi($rongOrder['channel_id'])->certificate($paramArr);
        
    }

    /*
     * 老用户绑卡签约
     */

    public function oldUserBindCard($oPayorder) {
        //参数数组
        $notify_url = Yii::$app->request->hostInfo . '/rongpay/notify/' . $this->getCfg($oPayorder->channel_id);
        $paramArr   = array(
            'bind_id'       => $oPayorder->rongbindbank->bind_no,
            'total_fee'     => $oPayorder->amount,
            'member_id'     => $oPayorder->identityid,
            'order_no'      => $oPayorder->cli_orderid,
            'notify_url'    => $notify_url,
            'title'         => $oPayorder->productname, //商品名称
            'body'          => $oPayorder->productdesc,
            'transtime'     => date('YmdHis'),
            'terminal_type' => 'web',
            'terminal_info' => date("Y-m-d H:i:s", time()), //交易时间
            'member_ip'     => Yii::$app->request->userIP,
        );
        $res        = $this->getApi($oPayorder['channel_id'])->bindcard($paramArr);
        if ($res['result_code'] != '0000') {  //签约失败
            return ['res_code' => 1050005, 'res_data' => $res['result_msg']];
        }else{
            return ['res_code' => 0, 'res_data' => '绑卡请求成功'];
        }
    }
    /*
     * 修改订单绑卡ID
     */
    
    public function updateBindId($rongOrder , $bindId){
        $rongOrder -> bind_id = $bindId;
        return $rongOrder -> save();
    }
    
    /*
     * 修改订单状态
     */
    private function saveOrderStatus($rongOrder , $status){
        $result = $saveOrder = false;
        $rongOrder -> status = $status;
        $saveOrder = $rongOrder -> save();
        if($saveOrder){
            $oPayorder = Payorder::findOne($rongOrder -> payorder_id);
            $result   = $oPayorder->saveStatus($status);
        }
        if($result && $saveOrder){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 融宝支付生成订单
     * @author Yangjinlong
     */

    public function createOrder($oPayorder) {
        //是否绑卡成功
        $isBindCard = (new RongbaoBindbank)->getBindBankInfo($oPayorder ->aid ,$oPayorder ->identityid ,$oPayorder ->cardno ,$oPayorder ->channel_id );
        $postData = [];
        if (!empty($isBindCard)) {
            $postData['bind_id']   = $isBindCard->id;
            $postData['status'] = Payorder::STATUS_BIND;
        } else {
            $postData['bind_id'] = '';
            $postData['status'] = Payorder::STATUS_NOBIND;
        }
        $postData['payorder_id']   = $oPayorder->id;
        $postData['aid']           = $oPayorder->aid;
        $postData['channel_id']    = $oPayorder->channel_id;
        $postData['orderid']       = $oPayorder->orderid;
        $postData['identityid']    = $oPayorder->identityid;
        $postData['amount']        = $oPayorder->amount;
        $postData['name']          = $oPayorder->name;
        $postData['idcard']        = $oPayorder->idcard;
        $postData['phone']         = $oPayorder->phone;
        $postData['cardno']        = $oPayorder->cardno;
        $postData['other_orderid'] = $oPayorder->other_orderid;
        $postData['userip']        = $oPayorder->userip;
        $postData['bankname']      = $oPayorder->bankname;
        $postData['card_type']     = $oPayorder->card_type;
        $postData['productname']   = $oPayorder->productname;
        $postData['productdesc']   = $oPayorder->productdesc;
        $model                     = new RongbaoOrder();
        $res                       = $model->saveOrder($postData);
        if (!$res) {
            return ['res_code' => 1050006, 'res_data' => '订单保存失败'];
        }
        //5. 同步主订单状态
        $result   = $oPayorder->saveStatus($model->status);
        //6. 返回下一步处理流程
        $res_data = $model->getPayUrls('rongpay',$oPayorder->channel_id);
        Logger::dayLog('rb', 'getPayUrls', $res_data);
        return ['res_code' => 0, 'res_data' => $res_data];
    }

    /*
     * 确认支付
     */

    public function paysave($oRongOrder , $validatecode) {
        if (!$oRongOrder || !$validatecode) {
            return ['res_code' => 1050005, 'res_data' => '参数错误'];
        }
        
        //1. 增加状态锁定
        $result = $oRongOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return ['res_code' => 1050009, 'res_data' => '订单状态不合法'];
        }
        
        //参数数组
        $paramArr = array(
            'order_no'   => $oRongOrder->cli_orderid,
            'check_code' => $validatecode,
        );
        $res        = $this->getApi($oRongOrder['channel_id'])->pay($paramArr);
        if ($res['result_code'] != '0000') {
            return ['res_code' => $res['result_code'], 'res_data' => $res['result_msg']];
        } else {
            $this -> bindSuccess($oRongOrder);  //支付成功绑卡记录修改成绑卡成功 并在bindbank中生成绑卡成功记录
            return ['res_code' => 0, 'res_data' => '操作成功'];
        }
    }
    
    private function bindSuccess($oRongOrder){
        $where = [
            'AND',
            ['channel_id' => $oRongOrder -> channel_id],
            ['cardno' => $oRongOrder -> cardno],
            ['identityid' => $oRongOrder -> identityid],
        ];
        //不确定是新用户还是老用户支付成功 则先去找绑卡成功的记录 
        $bindBank = RongbaoBindbank::find()->where($where)->andWhere(['status' => BindBank::STATUS_BINDOK])->orderBy('bind_no desc')->one();
        if(empty($bindBank)){
            $bindBank = RongbaoBindbank::find()->where($where)->orderBy('bind_no desc')->one();
        }
        if(!empty($bindBank)){
            return $bindBank -> bindBankSuccess($oRongOrder);
        }
        return true;
    }

    /*
     * 重发短信
     */

    public function resendsms($getRongOrder) {
        //参数数组
        $paramArr = array(
            'order_no' => $getRongOrder -> cli_orderid,
            'version'  => '3.1.2'
        );
        $res        = $this->getApi($getRongOrder['channel_id'])->reSendSms($paramArr);
        if($res['result_code']!='0000'){
            return ['res_code' => 1050007, 'res_data' => '操作失败'];
        }else{
            return ['res_code' => 0, 'res_data' => '操作成功'];
        }
    }
    /**
     * Undocumented function
     * 余额查询接口
     * @param [type] $channel_id
     * @return void
     */
    public function acctQuery($channel_id){
        $res = $this->getApi($channel_id)->acctquery();
        if(empty($res)){
            return ['res_code'=>'200_error','res_data'=>'查询超时'];
        }
        if($res['result_code']!='0001'){
            return ['res_code'=>$res['result_code'],'res_data'=>$res['result_msg']];
        }
        return ['res_code'=>0,'res_data'=>$res['balance']];
    }
}

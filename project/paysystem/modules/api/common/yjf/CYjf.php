<?php
namespace app\modules\api\common\yjf;

use app\models\BindBank;
use app\models\Payorder;
use app\models\yjf\YjfBindbank;
use app\models\yjf\YjfOrder;
use app\common\Logger;
use Yii;
use app\models\StdError;
/**
 * 易极付支付类
 */
class CYjf {

    private $oApi;

    public function __construct() {
        
    }

    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        //$is_prod = true;
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
            $map[$channel_id] = new YjfApi($cfg);
        }
        return $map[$channel_id];
    }
    /**
     * 四要素鉴权
     */ 
    public function verifyBankCard($yjfOrder){
        $postData = $yjfOrder->attributes;
        $oBind = (new YjfBindbank)->getByBid($yjfOrder->bind_id);
        if(empty($oBind)){
            return false;
        }
        $postData['name'] = $oBind['name'];
        $postData['idcard'] = $oBind['idcard'];
        $postData['cardno'] = $oBind['cardno'];
        $postData['phone'] = $oBind['phone'];
        $postData['bind_no'] = $oBind['bind_no'];
        $res = $this->getApi($yjfOrder['channel_id'])->verifyBankCard($postData);
        return $res;
    }
    /**
     * 确认验证码
     */ 
     public function smsVerifyCodeCheck($smscode,$yjfOrder){
        $postData = $yjfOrder->attributes;
        $oBind = (new YjfBindbank)->getByBid($yjfOrder->bind_id);
        if(empty($oBind)){
            return false;
        }
        $postData['smscode'] = $smscode;
        $postData['bind_no'] = $oBind['bind_no'];
        $res = $this->getApi($yjfOrder['channel_id'])->smsVerifyCodeCheck($postData);
        return $res;
     }
   /**
     * 支付
     */ 
    public function pay($yjfOrder){
        $postData = $yjfOrder->attributes;
        $oPayorder = $yjfOrder->payorder;
         //1. 增加状态锁定
        $result = $yjfOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return StdError::returnStdErrorJson($yjfOrder->channel_id,"0005");
        }
        $postData['name'] = $oPayorder['name'];
        $postData['idcard'] = $oPayorder['idcard'];
        $postData['cardno'] = $oPayorder['cardno'];
        $postData['phone'] = $oPayorder['phone'];
        $postData['productname'] = $oPayorder['productname'];
        $res = $this->getApi($yjfOrder['channel_id'])->pay($postData);
        return $res;
    }
    /**
     * 查询四要素验卡结果
     *
     * @return void
     */
    public function queryCardVerify($oBind){
        $postData['outOrderNo'] = $oBind->bind_no;
        $res = $this->getApi($oBind['channel_id'])->queryCardVerify($postData);
        return $res;
    }

    /*
     * 易极付支付生成订单
     */

    public function createOrder($oPayorder) {
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];
        //是否绑卡成功
        $oBind = $this->getBindBank($data);
        if (empty($oBind)) {
            //绑卡信息保存失败
            return StdError::returnStdErrorJson($oPayorder->channel_id,"0014");
        }
        $data['bind_id']   = $oBind->id;
        $data['status'] = $oBind->status == YjfBindbank::STATUS_BINDOK ? Payorder::STATUS_BIND : Payorder::STATUS_NOBIND;
        $model                     = new YjfOrder();
        $res                       = $model->saveOrder($data);
        if (!$res) {
            //订单保存失败
            return StdError::returnStdErrorJson($oPayorder->channel_id,"0012");
        }
        //5. 同步主订单状态
        $result   = $oPayorder->saveStatus($model->status);
        //6. 返回下一步处理流程
        $res_data = $model->getPayUrls();
        return ['res_code' => 0, 'res_data' => $res_data];
    }
     /**
     * 获取并保存绑定信息
     * @param $data
     * @return  object
     */
    private function getBindBank($data) {
        //1 获取绑定信息
        $oBind = (new YjfBindbank)->getBindBankInfo($data['aid'], $data['identityid'], $data['cardno'], $data['channel_id']);
       // var_dump($oBind);die;
        //2 如果没有绑卡信息 或者绑卡不是成功状态 调用四要素查询接口
        if(!empty($oBind)&&$oBind->status!=YjfBindbank::STATUS_BINDOK){
            $res = $this->queryCardVerify($oBind);
            //serviceStatus业务状态为VERIFY_CARD_SUCCESS表示验卡成功
            //var_dump($res['res_data']['serviceStatus']);die;
            $serviceStatus = isset($res['res_data']['serviceStatus'])?$res['res_data']['serviceStatus']:'';
            $description = isset($res['res_data']['description'])?$res['res_data']['description']:'';
            if(isset($res) && $res['res_code']==0 && $serviceStatus=='VERIFY_CARD_SUCCESS'){
                
                $oBind->error_code = $serviceStatus;
                $oBind->error_msg = $description;
                $oBind->status = YjfBindbank::STATUS_BINDOK;
                $oBind->save();
            }

        }
        if (empty($oBind)) {
            $bindData = [
                'bind_no' => date('YmdHis').rand(1000,9999),//易极付四要素鉴权外部订单号
                'channel_id' => $data['channel_id'],
                'aid' => $data['aid'],
                'identityid' => $data['identityid'],
                'idcard' => $data['idcard'],
                'name' => $data['name'],
                'cardno' => $data['cardno'],
                'card_type' => 1, 
                'phone' => $data['phone'],
                'bankname' => $data['bankname'],
                'userip' => $data['userip'],
            ];
            $oBind = new YjfBindbank;
            $result = $oBind->saveBindBank($bindData);
            if (!$result) {
                Logger::dayLog('yjf', "cyjf/getBindBank", $oBind->attributes, $oBind->errors);
            }
            return $result ? $oBind : null;
        }
        return $oBind;
    }
   

}

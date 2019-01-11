<?php
/**
 * 易宝API新版投资通服务
 */
namespace app\modules\api\common\yeepaytzt;
use app\common\Logger;
use app\models\Payorder;
use app\models\yeepay\YpBindbank;
use app\models\yeepay\YpTztOrder;
use app\modules\api\common\yeepaytzt\YeepayTzt;
use Yii;

class CYeepaytzt {

    private $oTztOrder;

    public function init() {
        parent::init();
    }

    /**
     * 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod133
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
            $map[$channel_id] = new YeepayTzt($cfg);
        }
        return $map[$channel_id];
    }

    /**
     * 创建支付订单
     * @param  obj $oPayorder
     * @return  [res_code,res_data]
     */
    public function createOrder($oPayorder) {
        //1. 数据检测
        if (empty($oPayorder)) {
            return ['res_code' => 26031, 'res_data' => '没有提交数据！'];
        }
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];

        //2. 绑定银行卡
        $res = $this->getBindBank($data);
        if ($res['res_code'] != 0) {
            return ['res_code' => $res['res_code'], 'res_data' => $res['res_data']];
        }
        $oBind = $res['res_data'];
        $data['bind_id'] = $oBind->id;
        $data['cli_identityid'] = $oBind->cli_identityid;
        $data['status'] = Payorder::STATUS_BIND;

        //3. 字段检查是否正确
        $this->oTztOrder=$oTztOrder = new YpTztOrder();
        $result = $oTztOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('yeepay/newtzt', '提交数据', $data, '失败原因', $oTztOrder->errors);
            return ['res_code' => 26036, 'res_data' => '订单保存失败'];
        }

        //5. 同步主订单状态
        $result = $oPayorder->saveStatus($oTztOrder->status);

        //6. 返回下一步处理流程
        $res_data = $oTztOrder->getPayUrls('yeepaytzt');
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    /**
     * 获取绑卡信息
     * @param  [] $data
     * @return [res_code,res_data]
     */
    private function getBindBank($data) {
        $oBind = (new YpBindbank)->getSameUserCard(
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
     * 根据订单号进行绑卡
     * @param  [] $oPayorder
     * @return [res_code,res_data]
     */
    private function bindCard($data) {
        //1. 保存到易宝投资通绑卡表中
        $oBind = new YpBindbank;
        $result = $oBind->saveCard($data);
        if (!$result) {
            return ['res_code' => 26001, 'res_data' => '数据保存失败'];
        }

        //2. 组合四要素等参数
        $requestid = $oBind->requestid;
        // 加上前缀，以免不同的通道重复
        $cli_identityid = $oBind->cli_identityid;
        $ybData = [
            'requestid' => $requestid, //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'identityid' => $cli_identityid, //用户标识√string最长50位，商户生成的用户唯一标识
            'cardno' => $oBind['cardno'], //银行卡号√string
            'idcardno' => $oBind['idcard'], //证件号√string
            'username' => $oBind['name'], //持卡人姓名√string
            'phone' => $oBind['phone'], //银行预留手机号√string
            'userip' => $oBind['userip'], //用户请求ip√string用户支付时使用的网络终端IP
        ];

        //3. 调用绑卡接口
        $ybResult = $this->getApi($oBind['channel_id'])->invokebindbankcard($ybData);
        Logger::dayLog('yeepay/newtzt', '请求绑卡', $ybData, '易宝结果', $ybResult);
        //4. 保存短信验证码
        $result = $oBind->saveNewReqStatus($ybResult);
        if (!$result) {
            $error_msg = $oBind->error_msg ? $oBind->error_msg : '';
            return ['res_code' => 26002, 'res_data' => $error_msg];
        }
        //5. 确认绑卡操作
        $validatecode = $oBind->smscode;
        $ybResult = $this->getApi($oBind['channel_id'])->confirmbindbankcard($requestid, $validatecode);
        Logger::dayLog('yeepay/newtzt', '确认绑卡', $requestid,$validatecode, '易宝结果', $ybResult);
        //5 保存结果状态
        $result = $oBind->saveNewRspStatus($ybResult);
        if (!$result) {
            $error_msg = $oBind->error_msg ? $oBind->error_msg : '';
            return ['res_code' => 26002, 'res_data' => $error_msg];
        }

        return ['res_code' => 0, 'res_data' => $oBind];
    }
    /**
     * 支付结果
     * @param  object $oLianOrder
     * @return int 支付状态. 目前只可能是 4, 11(支付中, 支付失败) 和 -1 (无效)
     */
    public function pay($oTztOrder) {
        //1. 增加状态锁定
        $result = $oTztOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return -1;
        }
        $cfg = $this->getCfg($oTztOrder['channel_id']);
        $ybcallbackurl = Yii::$app->request->hostInfo . '/yeepaytzt/tztcallurl/' . $cfg;
        

        $card_top = substr($oTztOrder->cardno, 0, 6);
        $card_last = substr($oTztOrder->cardno, -4);

        $ypData = $oTztOrder->attributes;
        $ypData['callbackurl'] = $ybcallbackurl;
        $ypData['cardtop']  = $card_top;
        $ypData['cardlast'] = $card_last;
        // 生产环境 修改实际回调地址
        $ybResult = $this->getApi($oTztOrder['channel_id'])->payrequest($ypData);
        Logger::dayLog('yeepay/newtzt', '提交数据', $ypData, '易宝结果', $ybResult);
        //2. 保存结果信息
        if (!empty($ybResult['errorcode'])) {
            // 失败时处理
            Logger::dayLog('yeepay/newtzt','errorcode', $ybResult['errorcode'], $ybResult['errormsg'],$ypData);
            $result = $oTztOrder->savePayFail($ybResult['errorcode'], $ybResult['errormsg']);
        }

        //3. 返回当前状态
        return $oTztOrder->status;
    }
    /**
     * 代扣直接支付
     * @param  obj $oPayorder 
     * @return  [res_code,res_data]
     */
    public function directpay($oPayorder){
        //1 创建易宝订单
        $res = $this->createOrder($oPayorder);
        if ($res['res_code'] != 0) {
            return ['res_code' => $res['res_code'], 'res_data' => $res['res_data']];
        }

        //2 直接支付
        $status = $this->pay($this->oTztOrder);
        if($status == Payorder::STATUS_PAYFAIL){
            return ['res_code' => 26003, 'res_data' => "支付失败了"];
        }

        // 重获取数据
        $oPayorder->refresh();
        $data =  $oPayorder->clientData();
        return ['res_code' => 0, 'res_data' => $data];
    }
    public function confirm($requestid,$validatecode){
        $ybResult = $this->getApi('133')->confirmbindbankcard($requestid, $validatecode);
        Logger::dayLog('yeepay/newtzt', '确认绑卡',  '易宝结果', $ybResult);
    }
    public function RSAVerify($return,$sign){
        $ybResult = $this->getApi('133')->RSAVerify($return, $sign);
    }
}

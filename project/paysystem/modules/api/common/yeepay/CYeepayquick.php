<?php
/**
 * 易宝一键支付接口
 */
namespace app\modules\api\common\yeepay;

use app\common\Logger;
use app\models\yeepay\YpQuickOrder;
use app\modules\api\common\yeepay\YeepayQuick;
use Yii;

class CYeepayquick {

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
            $map[$channel_id] = new YeepayQuick($cfg);
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

        //3 保存到一键支付数据表
        $postData = $oPayorder->attributes;
        $postData['payorder_id'] = $postData['id'];
        $oQuickOrder = new YpQuickOrder();
        $result = $oQuickOrder->saveOrder($postData);
        if (!$result) {
            Logger::dayLog('yeepay/quick', 'createOrder', '数据保存失败', '提交数据', $postData, '错误原因', $oQuickOrder->errinfo);
            return ['res_code' => 2004, 'res_data' => '数据保存失败'];
        }
        //4  请求易宝接口:
        $result = $this->payRequest($oQuickOrder->attributes);

        //4.1 无响应时
        if (empty($result)) {
            $oQuickOrder->savePayFail('2003', '支付提交无响应');
            return ['res_code' => $oQuickOrder->error_code, 'res_data' => $oQuickOrder->error_msg];
        }

        //4.2  有错误时
        if (is_array($result) && $result['error_code']) {
            $oQuickOrder->savePayFail($result['error_code'], $result['error_msg']);
            return ['res_code' => $oQuickOrder->error_code, 'res_data' => $oQuickOrder->error_msg];
        }

        //5  正确时是个连接地址
        $oQuickOrder->yeepay_url = $result;
        $r = $oQuickOrder->save();

        $res = $oQuickOrder->getPayUrls();
        return ['res_code' => 0, 'res_data' => $res];
    }
    /**
     * 调用易宝接口
     * @param  [] $quickOrder
     * @return [] | url
     */
    private function payRequest($postData) {
        $cfg = $this->getCfg($postData['channel_id']);
        $callbackurl = Yii::$app->request->hostInfo . '/yeepay/quickcallurl/' . $cfg;
        $ypData = [
            'orderid' => $postData['cli_orderid'], //客户订单号   √   string  商户生成的唯一订单号，最长50位
            'transtime' => intval($postData['transtime']), //交易时间    √   int     时间戳，例如：1361324896，精确到秒
            'amount' => intval($postData['amount']), //交易金额    √   int     以"分"为单位的整型，必须大于零
            'productcatalog' => $postData['productcatalog'], //商品类别码   √   string  详见商品类别码表
            'productname' => $postData['productname'], //商品名称    √   string  最长50位，出于风控考虑，请按下面的格式传递值：'应用商品名称，如“诛仙-3阶成品天琊”，此商品名在发送短信校验的时候会发给用户，所以描述内容不要加在此参数中，以提高用户的体验度。
            'productdesc' => $postData['productdesc'], //商品描述     最长200位
            'identityid' => $postData['cli_identityid'], //用户标识    √   string  最长50位，商户生成的用户账号唯一标识
            'orderexpdate' => intval($postData['orderexpdate']), //订单有效期时间       int     以分为单位
            'userip' => (string) $postData['userip'], //用户IP    √   string  用户支付时使用的网络终端IP
            'callbackurl' => $callbackurl, //商户后台系统的回调地址       string  用来通知商户支付结果，前后台回调地址的回调内容相同
            'fcallbackurl' => $callbackurl, //商户前台系统提供的回调地址     string  '用来通知商户支付结果，前后台回调地址的回调内容相同。用户在网页支付成功页面，点击“返回商户”时的回调地址
            'cardno' => (string) $postData['cardno'], //银行卡序列号   在进行网页支付请求的时候，如果传此参数会把银行卡号直接在银行信息界面显示卡号，注意：P2P商户此参数须必填
            'idcard' => (string) $postData['idcard'], //证件号     注意：P2P商户此参数须必填
            'owner' => (string) $postData['name'], //持卡人姓名
        ];
        $result = $this->getApi($postData['channel_id'])->payRequest($ypData);
        return $result;
    }
}
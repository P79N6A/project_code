<?php

namespace app\modules\payapi\controllers;

use Yii;
use app\commonapi\Logger;
use app\commands\SubController;
use app\commonapi\Wechatpay;

/**
 * 微信公众号支付
 */
class YyygzhpayController extends SubController {

    public $enableCsrfValidation = false;
    private $paytype             = 'gzh';
    private $config = 'Config_test';
    /**
     * 提交订单信息
     */
    public function actionSubmitorderinfo() {
        $params                  = $_POST;
        $params['mch_create_ip'] = Yii::$app->request->userIP;
        $params['sub_openid']    = '';
        $params['body']          = '购买电子产品';
        $service                 = new Wechatpay($this->config, $this->paytype);
        $res                     = $service->submitOrderInfo($params);
        exit(json_encode($res));
    }

    /**
     * 查询订单
     */
    public function actionQueryorder() {
//        $params = $_POST;
        $params  = [
            'out_trade_no'   => '8779700404856298',
            'transaction_id' => '7551000001201612286280630367',
        ];
        $service = new Wechatpay($this -> config, $this->paytype);
        $res     = $service->queryOrder($params);
        var_dump($res);
        die;
    }

    /**
     * 提供给威富通的回调方法
     */
    public function actionNotify() {
        $xml     = file_get_contents('php://input');
        $service = new Wechatpay($this -> config, $this->paytype);
        $res     = $service->notify($xml);
        if ($res) {
            
            echo 'success';
        } else {
            echo 'faild';
        }
    }

}

?>
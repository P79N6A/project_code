<?php

namespace app\modules\payapi\controllers;

use Yii;
use app\commonapi\Logger;
use app\commands\SubController;
use app\commonapi\Wechatpay;

/**
 * 微信扫码支付
 */
class YyyscanController extends SubController {

    public $enableCsrfValidation = false;
    private $paytype = 'scan';
    private $config = 'Config_test';
    /**
     * 提交订单信息
     */
    public function actionSubmitorderinfo() {
//        $params = $_POST;
        $params = [
            'out_trade_no'  => '100001482121112328',
            'body'          => 'iphone6s',
            'attach'        => '附加信息',
            'total_fee'     => '1',
            'mch_create_ip' => '127.0.0.1',
        ];
        
        $service = new Wechatpay($this -> config , $this->paytype);
        $res                  = $service ->submitOrderInfo($params);
        var_dump($res);die;
    }

    /**
     * 查询订单
     */
    public function actionQueryorder() {
//        $params = $_POST;
        $params              = [
            'out_trade_no'   => '100001482345945138',
            'transaction_id' => '7551000001201612284229606893',
        ];
        $service = new Wechatpay($this -> config , $this->paytype);
        $res                 = $service-> queryOrder($params);
        var_dump($res);
        die;
    }

    /**
     * 提供给威富通的回调方法
     */
    public function actionNotify() {
        $xml = file_get_contents('php://input');
        $service = new Wechatpay($this -> config , $this->paytype);
        $res = $service -> notify($xml);
        if($res){
            echo 'success';
        }else{
            
        }
    }

}

?>
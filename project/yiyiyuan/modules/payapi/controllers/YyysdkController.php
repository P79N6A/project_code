<?php

namespace app\modules\payapi\controllers;

use Yii;
use app\commonapi\Logger;
use app\commands\SubController;
use app\commonapi\Wechatpay;
use app\commonapi\weixinapi\Config_test;

/**
 * SDK支付接口
 * ================================================================
 * submitOrderInfo 提交订单信息
 * queryOrder 查询订单
 * 
 * ================================================================
 */
Class YyysdkController extends SubController {

    public $enableCsrfValidation = false;
    private $paytype = 'sdk';
    private $config = 'Config_test';
    /**
     * 提交订单信息
     */
    public function actionSubmitorderinfo() {
        $params                     = [
            'out_trade_no'  => '7251856233010646',
            'body'          => 'iphone6s',
            'attach'        => '附加信息',
            'total_fee'     => '1',
            'mch_create_ip' => '127.0.0.1',
        ];

        $service = new Wechatpay($this -> config , $this->paytype);
        $res    = $service -> submitOrderInfo($params);
        var_dump($res);die;
    }

    /**
     * 界面显示
     */
    public function queryRefund() {
        
    }

    /**
     * 异步通知方法，demo中将参数显示在result.txt文件中
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
<?php

namespace app\modules\payapi\controllers;

use Yii;
use app\commonapi\Logger;
use app\commands\SubController;
use app\modules\payapi\config\union\Config;
use app\modules\payapi\services\union\Services;
use yii\helpers\Json;

/*
 * 支付联盟连连支付
 */

class UnionpayController extends SubController {

    public $enableCsrfValidation = false;

    /**
     * 4.1. WAP签约授权支付接口
     */
    public function actionGrantpay() {
        $url    = 'https://wap.lianlianpay.com/installment.htm';
        $params = [
            'version'        => Config::VERSION,
            'oid_partner'    => Config::OID_PARTNER, #商户编号
            'user_id'        => 1,
            'sign_type'      => Config::SIGN_TYPE,
            'app_request'    => Config::APP_REQUEST,
            'busi_partner'   => '101001', #商户业务类型
            'no_order'       => '20161228093810', #订单号
            'dt_order'       => '20171228171613', #订单时间
            'name_goods'     => 'iphone6s',
            'info_order'     => '买了一部苹果手机，客户要求后天发货',
            'money_order'    => '0.02',
            'notify_url'     => Config::$base_url . '/payapi/unionpay/grantpaynotify',
            'url_return'     => Config::$base_url . '/payapi/unionpay/grantpayreturn',
            'id_type'        => 0,
            'id_no'          => '232331199011111111', #身份证号
            'acct_name'      => '姓名',
            'risk_item'      => "{\"frms_ware_category\":\"2009\",\"user_info_mercht_userno\":\"123456\",\"user_info_dt_register\":\"20141015165530\",\"user_info_full_name\":\"张三\",\"user_info_id_no\":\"6228480010611111111\",\"user_info_identify_type\":\"1\",\"user_info_identify_state\":\"1\"}", #风险控制参数
            'card_no'        => '6228480010611111111',
            'pay_type'       => 'D', #支付方式 默认D ：认证支付渠道
            'repayment_plan' => '{"repaymentPlan":[{"date":"2017-03-07","amount":"0.01"}]}', #还款计划
            'repayment_no'   => '20171225224', #还款计划
            'sms_param'      => '{"contract_type":"融资租赁","contact_way":"0571-12345678"}', #短信参数
        ];

        $res = $this->queryPost($params, $url);
    }

    private function queryPost($params, $url) {
        $linkStr        = Services::buildSign($params);
        $params['sign'] = $linkStr;
        $req_data       = Json::encode($params);
        $res            = Services::buildRequestForm($req_data, $url, 'post');
        die($res);
    }

    private function query($params, $url) {
        $linkStr        = Services::buildSign($params);
        $params['sign'] = $linkStr;
        $req_data       = Json::encode($params);
        $res            = self::dataPost($req_data, $url);
        die($res);
    }

    /*
     * 4.1 notify_urlWAP签约授权支付返回接受参数接口
     */

    public function actionGrantpaynotify() {
        $request = file_get_contents('php://input');
        if (Services::verifyReturn($request)) {
            Logger::errorLog(print_r($request, true), 'grantpaynotify', 'unionpay');
            echo Json::encode(['ret_code' => '0000', 'ret_msg' => '交易成功']);
        } else {
            echo Json::encode(['ret_code' => '1111', 'ret_msg' => '失败']);
        }
    }

    /*
     * 4.1url_return. WAP签约授权支付返回接受参数接口
     */

    public function actionGrantpayreturn() {
        $request = file_get_contents("php://input");
        $verifyReturn = Services::verifyReturn($request['res_data']);
        if ($verifyReturn) {
            Logger::errorLog(print_r($request, true), 'grantpayreturn', 'unionpay');
            var_dump($request);
        } else {
            echo 'faild';
        }
    }

    /*
     * 4.2. WAP  签约授权接口
     * 商户的服务端可以通过连连支付API用户签约信息查询服务来查询用户在连连支付已绑定的银行卡列表信息。用户签约信息查询采用
      https post 方式提交，格式采用 json 报文格式
     */

    public function actionGrantlist() {
        $url    = "https://yintong.com.cn/llpayh5/signApply.htm";
        $params = [
            'version'        => Config::VERSION,
            'oid_partner'    => Config::OID_PARTNER, #商户编号
            'user_id'        => 1,
            'app_request'    => Config::APP_REQUEST,
            'sign_type'      => Config::SIGN_TYPE,
            'id_type'        => 0,
//            'id_no'          => '232331199011111111',
//            'acct_name'      => '姓名',
//            'card_no'        => '6228480010611111111',
            'pay_type'       => 'I',
            'risk_item'      => "{\"frms_ware_category\":\"2009\",\"user_info_mercht_userno\":\"123456\",\"user_info_dt_register\":\"20141015165530\",\"user_info_full_name\":\"张三\",\"user_info_id_no\":\"3306821990012121221\",\"user_info_identify_type\":\"1\",\"user_info_identify_state\":\"1\"}", #风险控制参数
            'url_return'     => Config::$base_url . '/payapi/unionpay/grantlistreturn',
            'repayment_plan' => '{"repaymentPlan":[{"date":"2016-12-28","amount":"0.01"}]}', #还款计划
            'repayment_no'   => '201712281842',
            'sms_param'      => '{"contract_type":"测试","contact_way":"15201155555"}', #短信参数
        ];
        $this->queryPost($params, $url);
    }

    public function actionGrantlistreturn() {
        $request      = $_GET;
        $verifyReturn = Services::verifyReturn($request['result']);
        if (!empty($request) && $verifyReturn) {
            Logger::errorLog(print_r($request, true), 'grantlistreturn', 'unionpay');
            var_dump($request);
        } else {
            echo 'faild';
        }
    }

    public static function dataPost($post_string, $url) { //POST方式提交数据
        $context        = array('http' =>
            array('method'  => "POST",
                'header'  => "Content-type: application/json;charset=UTF-8",
                'content' => $post_string
            )
        );
        $stream_context = stream_context_create($context);
        $data           = file_get_contents($url, FALSE, $stream_context);
        return $data;
    }

    /*
     * 4.3.  授权请 申请 API 
     * 商户的服务端可以通过连连支付授权申请 API 接口给已经签约过的用户进行单独授权
     */

    public function actionGrantquery() {
        header("Content-type: application/json;charset=UTF-8");
        $url    = 'https://repaymentapi.lianlianpay.com/agreenoauthapply.htm';
        $params = [
            'user_id'        => 1,
            'oid_partner'    => Config::OID_PARTNER, #商户编号
            'sign_type'      => Config::SIGN_TYPE,
            'version'        => Config::VERSION,
            'repayment_plan' => '{"repaymentPlan":[{"date":"2017-01-09","amount":"0.01"}]}', #还款计划
            'repayment_no'   => '201701093550',
            'sms_param'      => '{"contract_type":"先花花","contact_way":"15201155555"}', #短信参数
            'pay_type'       => 'D', #支付方式 默认D ：认证支付渠道
            'no_agree'       => '2016122818001461', #支付方式 默认D ：认证支付渠道
        ];
        $this->query($params, $url);
    }

    /*
     * 4.4.  还款计划变更 接口
     */

    public function actionRepayplan() {
        $url    = 'https://repaymentapi.lianlianpay.com/repaymentplanchange.htm';
        $params = [
            'oid_partner'    => Config::OID_PARTNER, #商户编号
            'sign_type'      => Config::SIGN_TYPE,
            'user_id'        => 1,
            'repayment_plan' => '{"repaymentPlan":[{"date":"2017-02-03","amount":"0.01"}]}', #还款计划
            'repayment_no'   => '201701093550', #还款计划
            'sms_param'      => '{"contract_type":"融资租赁","contact_way":"0571-12345678"}', #短信参数
        ];
        $this->query($params, $url);
    }

    /*
     * 4.5.  银行卡还款扣款接口
     */

    public function actionBankrepay() {
        $url    = 'https://repaymentapi.lianlianpay.com/bankcardrepayment.htm';
        $params = [
            'user_id'                 => 1,
            'oid_partner'             => Config::OID_PARTNER, #商户编号
            'sign_type'               => Config::SIGN_TYPE,
            'busi_partner'            => '101001', #商户业务类型
            'version'                 => Config::VERSION,
            'no_order'                => '88880109155555', #订单号
            'dt_order'                => '20170109161856', #订单时间
            'name_goods'              => 'iphone6s',
            'info_order'              => '买了一部苹果手机，客户要求后天发货',
            'money_order'             => '0.01',
            'notify_url'              => Config::$base_url . '/payapi/unionpay/repayreturn',
            'valid_order'             => Config::VALID_ORDER,
            'risk_item'               => "{\"frms_ware_category\":\"2009\",\"user_info_mercht_userno\":\"123456\",\"user_info_dt_register\":\"20141015165530\",\"user_info_full_name\":\" 张 三\",\"user_info_id_no\":\"3306821990012121221\",\"user_info_identify_type\":\"1\",\"user_info_identify_ state\":\"1\", \"frms_ip_addr\":\"183.172.12.108\"}", #风险控制参数
            'schedule_repayment_date' => '2017-01-09',
            'repayment_no'            => '201701093550',
            'pay_type'                => 'D', #支付方式 默认D ：认证支付渠道
            'no_agree'                => '2016122818001461',
        ];
        $this->query($params, $url);
    }

    public function actionRepayreturn() {
        $request = file_get_contents("php://input");
        if(empty($request)){
            $request = $_POST;
        }
        $verifyReturn = Services::verifyReturn($request);
        if ($verifyReturn) {
            Logger::errorLog(print_r($request, true), 'bankrepayreturn', 'unionpay');
            echo Json::encode(['ret_code' => '0000', 'ret_msg' => '交易成功']);die;
        } else {
            Logger::errorLog(print_r('faild', true), 'bankrepayreturnfaild', 'unionpay');
            echo Json::encode(['ret_code' => '1111', 'ret_msg' => '交易失败']);die;
        }
    }

    /*
     * 4.6异步返回通知
     */

    public function actionNotify() {
        $request = file_get_contents('php://input');
        if (Services::verifyReturn($request)) {
            Logger::errorLog(print_r($request, true), 'notify', 'unionpay');
            echo Json::encode(['ret_code' => '0000', 'ret_msg' => '交易成功']);
        } else {
            echo Json::encode(['ret_code' => '1111', 'ret_msg' => '交易失败']);
        }
    }

    /*
     * 4.7.  银行卡卡 BIN  查询 API  接口
     * 商户的服务端可以通过连连支付 API 银行卡卡 BIN 查询服务来查询银行卡的卡 BIN 信息（卡片所属和卡类型）。银行卡卡 BIN 查询采用 httpspost 方式提交，格式采用 json 报文格式
     */

    public function actionCardbin() {
        $url    = 'https://queryapi.lianlianpay.com/bankcardbin.htm';
        $params = [
            'oid_partner' => Config::OID_PARTNER, #商户编号
            'card_no'     => '6228480010616311111',
            'sign_type'   => Config::SIGN_TYPE,
        ];
        $this->query($params, $url);
    }

    /*
     * 4.8.  用户签约信息询 查询 API  接口
     */

    public function actionContract() {
        $url    = 'https://queryapi.lianlianpay.com/bankcardbindlist.htm';
        $params = [
            'oid_partner' => Config::OID_PARTNER, #商户编号
            'user_id'     => 1,
            'pay_type'    => 'D', #支付方式 默认D ：认证支付渠道
            'sign_type'   => Config::SIGN_TYPE,
            'offset'      => 0,
        ];
        $this->query($params, $url);
    }

    /*
     * 4.9.  商户 支付结果查询服务 接口
     */

    public function actionPayresult() {
        $url = 'https://queryapi.lianlianpay.com/orderquery.htm';

        $params = [
            'oid_partner' => Config::OID_PARTNER, #商户编号
            'sign_type'   => Config::SIGN_TYPE,
            'no_order'    => '90001128173555', #订单号
            'dt_order'    => '20171128161855', #订单时间
        ];
        $this->query($params, $url);
    }

}

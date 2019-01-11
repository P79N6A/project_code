<?php
/**
 * 投资通接口文档
 */
namespace app\modules\api\common\yeepaytzt;
use app\common\Logger;
if (!class_exists('yeepayMPay')) {
    include __DIR__ . '/yeepayMPay.php';
}
class YeepayTzt extends \yeepayMPay {
    public function __construct($cfg) {
        // 获取配置文件
        $config = $this->getConfig($cfg);

        parent::__construct(
            $config['merchantaccount'],
            $config['merchantPublicKey'],
            $config['merchantPrivateKey'],
            $config['yeepayPublicKey']
        );
    }
    /**
     * 获取配置文件
     * @param  str $cfg
     * @param  str $aid
     * @return   []
     */
    private function getConfig($cfg) {
        $configPath = __DIR__ . "/tzt_config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }
    /**
     * 随机生成一个订单号
     */
    public function generateOrderId() {
        return "YT" . date('YmdHis') . rand(10000, 99999);
    }

    //************************支付流程 start ****************************/
    /**
     * 绑定卡请求
     */
    public function invokebindbankcard($postData) {
        if (!is_array($postData)) {
            return $this->error(1000, "提交的数据不能为空");
        }
        $query = array(
			'requestno'	           =>$postData['requestid'],
            'identityid'	       =>$postData['identityid'],
			'identitytype'         =>'USER_ID',
			'cardno'	           =>$postData['cardno'],
			'idcardtype'	       =>'ID',//证件类型
			'idcardno'		       =>$postData['idcardno'], //证件号
			'username'		       =>$postData['username'],
			'phone'		           =>$postData['phone'],
			'requesttime'          =>date('Y-m-d H:m:s')
			);
        try {
            return $this->post(YEEPAY_PAY_API, 'bindcard/request', $query);
        } catch (\yeepayMPayException $e) {
            $this->loge('invokebindbankcard', $e, func_get_args());
            return $this->errore($e);
        }

        /*返回结果数据格式如下
    merchantaccount 商户编号string
    requestno 绑卡请求号string
    codesender 短信验证码发送方string YEEPAY：易宝发送   BANK：银行发送    MERCHANT：商户发送
    smscode 短信验证码string为商户发送短验时会返回易宝生成的验证码
    sign  签名
     */
    }
    /**
     * 确定绑卡接口
     */
    public function confirmbindbankcard($requestid, $validatecode) {
        $data = array(
            'requestno' => $requestid, //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'validatecode' => $validatecode, //短信验证码√string短信验证码6位数字
        );
        try {
            return $this->post(YEEPAY_PAY_API, 'bindcard/confirm', $data);
        } catch (\yeepayMPayException $e) {
            $this->loge('confirmbindbankcard', $e, func_get_args());
            return $this->errore($e);
        }
        /**返回结果数据格式如下
    merchantaccount     商户编号string商户编号string
    requestid       绑卡请求号string原样返回商户所传
    bankcode    银行编码string详见银行编码列表
    card_top    卡号前6位string
    card_last   卡号后4位string
    sign            签名string
     */
    }

    /**
     * 支付请求接口
     */
    public function payrequest($postData) {
        if (!is_array($postData)) {
            return $this->error(1000, "提交的数据不能为空");
        }
        $query = array(
            'requestno'	     =>	$postData['cli_orderid'],
            'identityid'	 => $postData['cli_identityid'],
            'identitytype'   =>	'USER_ID',
            'requesttime'	 => date('Y-m-d H:m:s'),
            'amount'	     =>	sprintf("%.2f",$postData['amount']/100),
            'productname'	 =>	$postData['productname'],
            'avaliabletime'  =>	$postData['orderexpdate'],//有效期
            'cardtop'        => $postData['cardtop'],
            'cardlast'       => $postData['cardlast'],
            'callbackurl'	 =>	$postData['callbackurl'],
            'terminalid'     => $postData['identityid'],
            'registtime'     => date('Y-m-d H:m:s'),
            'lastloginterminalid'   => $postData['identityid'],
            'issetpaypwd'    => '0',			  
		);
        logger::dayLog('yeepay/newtzt','提交数据',$query);
        try {
            return $this->post(YEEPAY_PAY_API, 'bindpay/direct', $query);
        } catch (\yeepayMPayException $e) {
            $this->loge('payrequest', $e, func_get_args());
            return $this->errore($e);
        }
        /**返回结果数据格式如下
    merchantaccount     商户账号编号string
    orderid     商户订单号string原样返回商户所传
    phone   手机号string
    smsconfirm  短信确认int 0：建议不需要进行短信校验  1：建议需要进行短信校验
    codesender   短信验证码发送方YEEPAY：易宝发送     BANK：银行发送
    sign
     */
    }

    /**
     * 获取异常的错误原因和错误码，除此与logger函数功能同
     */
    private function errore($e) {
        return $this->error($e->getCode(), $e->getMessage());
    }
    private function error($error_code, $error_msg) {
        return [
            'error_code' => $error_code,
            'error_msg' => $error_msg,
        ];
    }

    /**
     * 获取异常的错误原因和错误码，除此与logger函数功能同
     */
    private function loge($tag, $e, $data) {
        $this->logger($tag, $e->getCode(), $e->getMessage(), $data);
    }
    /**
     * @param $tag 分类
     * @param $error_code 错误码
     * @param $error_msg 错误原因
     * @param $data 以后可使用 call_user_func_array 进行恢复
     */
    private function logger($tag, $error_code, $error_msg, $data) {
        // @todo 这个纪录到数据库里面
        $content = "\n\nerror : {$tag} : {$error_code} : {$error_msg} : " . var_export($data, true);
        //file_put_contents(__DIR__ . '/yeepay.log', $content);
    }
}
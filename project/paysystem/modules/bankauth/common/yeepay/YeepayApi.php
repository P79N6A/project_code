<?php
/**
 * 投资通接口文档
 */
namespace app\modules\bankauth\common\yeepay;

use app\modules\bankauth\common\ExceptionHandler;

/*
identitytype 用户标识类型
√int0：IMEI
1：MAC地址
2：用户ID
3：用户Email
4：用户手机号
5：用户身份证号
6：用户纸质订单协议号
 */
if (!class_exists('yeepayMPay')) {
    include __DIR__ . '/lib/yeepayMPay.php';
}

class YeepayApi extends \yeepayMPay
{
    public function __construct($cfg)
    {
        // 获取配置文件
        $config = $this->getConfig($cfg);

        parent::__construct(
            $config['merchantaccount'],
            $config['merchantPublicKey'],
            $config['merchantPrivateKey'],
            $config['yeepayPublicKey']
        );
        // 投资通一直是生产的链接地址api
        $this->setApiEnv($cfg);
    }
    /**
     * 获取配置文件
     * @param  str $cfg
     * @param  str $aid
     * @return   []
     */
    private function getConfig($cfg)
    {
        $configPath = __DIR__ . "/tzt_config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    //************************支付流程 start ****************************/
    /**
     * 绑定卡请求
     */
    public function invokebindbankcard($postData)
    {
        $data = array(
            'identityid' => (string) $postData['identityid'], //用户标识√string最长50位，商户生成的用户唯一标识
            'identitytype' =>2, /*用户标识类型*/
            'requestid' => (string) $postData['requestid'], //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'cardno' => (string) $postData['cardno'], //银行卡号√string
            'idcardtype' => '01', //证件类型√string固定值:01
            'idcardno' => (string) $postData['idcard'], //证件号√string
            'username' => (string) $postData['username'], //持卡人姓名√string
            'phone' => (string) $postData['phone'], //银行预留手机号√string
            'userip' => (string) $postData['userip'], //用户请求ip√string用户支付时使用的网络终端IP
            'registerphone' =>'', //用户注册手机号string  用户在商户的系统注册的手机号
            'registerdate' => '', //用户注册日期string用户在商户的系统注册的日期，格式：yyyy-mm-dd hh:mm:ss精确到秒
            'registerip' => '', // 用户注册ipstring
            'registeridcardtype' => '', //用户注册证件类型string固定值:01
            'registeridcardno' =>'', //用户注册证件号 string
            'registercontact' => '', //用户注册联系方式string手机号
            'os' =>'', //用户使用的操作系统
            'imei' =>'', //设备唯一标识
            'ua' => '', //用户使用的浏览器信息
        );
        try {
            return $this->post(YEEPAY_PAY_API, 'tzt/invokebindbankcard', $data);
        } catch (\yeepayMPayException $e) {
            ExceptionHandler::make_throw($e->getCode(), $e->getMessage());
        }
    }
    /**
     * 确定绑卡接口
     */
    public function confirmbindbankcard($requestid, $validatecode)
    {
        $data = array(
            'requestid' => $requestid, //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'validatecode' => $validatecode, //短信验证码√string短信验证码6位数字
        );
        try {
            return $this->post(YEEPAY_PAY_API, 'tzt/confirmbindbankcard', $data);
        } catch (\yeepayMPayException $e) {
            ExceptionHandler::make_throw($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 解析错误信息,由|分隔
     */
    public function parseError($err)
    {
        $errs = explode('|', $err);
        $errmsg =  isset($errs[1]) && $errs[1] ? $errs[1] : $errs[0];
        /*if(preg_match ("/^[a-zA-Z]*$/",$errmsg)){
            return '验证失败';
        }*/
        return $errmsg;
    }

    /**
     * 获取异常的错误原因和错误码，除此与error函数功能同
     */
    private function errore($e)
    {
        return $this->error($e->getCode(), $e->getMessage());
    }
    private function error($error_code, $error_msg)
    {
        return [
            'error_code' => $error_code,
            'error_msg' => $error_msg,
        ];
    }
}

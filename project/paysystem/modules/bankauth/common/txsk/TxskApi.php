<?php
/**
 * 天行数科银行卡四要素验证接口
 * 此接口不区分生产环境和测试环境
 * @author lijin
 */
namespace app\modules\bankauth\common\txsk;

use Yii;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use app\common\Http;
use app\common\Logger;
use app\common\Curl;

class TxskApi
{
    public $errinfo;// 错误结果

    private $tokenurl;
    private $bankurl;
    private $account;
    private $signature;
    private $curl;

    private $cacheKey="txsk_token";

    public function __construct($params_data = [])
    {
        $channelId = ArrayHelper::getValue($params_data, 'channelId');
        $config = $this->getConfig($channelId);
        if (!$config){
            $config = [];
        }
        $this->cacheKey = $this->cacheKey . $channelId;
        $this->tokenurl = ArrayHelper::getValue($config, "tokenurl", "http://tianxingshuke.com/api/rest/common/organization/auth");
        $this->bankurl  = ArrayHelper::getValue($config, "bankurl", "http://tianxingshuke.com/api/rest/unionpay/auth/4element");
        $this->account = ArrayHelper::getValue($config, "account", "xhh");
        $this->signature = ArrayHelper::getValue($config, "signature","2788b38a36ca419c90ffa15543915fa3");
//        $this->tokenurl = "http://tianxingshuke.com/api/rest/common/organization/auth";
//        $this->bankurl  = "http://tianxingshuke.com/api/rest/unionpay/auth/4element";
//        $this->account = "xhh";
//        $this->signature = "2788b38a36ca419c90ffa15543915fa3";

        $this->curl = new Curl();
        $this->curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
    }
    /**
     * 获取token值
     * 获取查询授权码（24小时有效）
     * 有缓存 , 24小时有效
     */
    public function getToken()
    {
        //1 从缓存中获取
        $tk = Yii::$app->cache->get($this->cacheKey);
        if (is_array($tk) && isset($tk['expireTime']) && $tk['expireTime'] > time()) {
            return $tk['token'];
        }

        //2 获取接口数据
        $result = $this-> getApiToken();
        if (!$result) {
            return $this->returnError('', "TOKEN_NO_RESPONSE");
        }

        //3 解析json
        $res = json_decode($result, true);
        if (!isset($res['success']) || !$res['success']) {
            $error = isset($res['errorDesc']) ? $res['errorDesc'] : 'TOKEN_GET_FAIL';
            return $this->returnError('', $error);
        }

        //4 过期时间
        $token = ArrayHelper::getValue($res, "data.accessToken");
        // var_dump($token);
        // die();
        $expireTime  = ArrayHelper::getValue($res, "data.expireTime");

        //5 放入缓存中
        $expireTime = intval($expireTime / 1000); //转成s
        Yii::$app ->cache->set($this->cacheKey, ['token'=>$token,'expireTime'=>$expireTime]);

        //6 返回token
        return $token;
    }
    /**
     * 获取token值
     */
    private function getApiToken()
    {
        $data = [
            'account'   => $this->account,
            'signature' => $this->signature,
        ];
        $res = Http::interface_post($this->tokenurl, http_build_query($data));
        return $res;
    }
    /**
     * 检测四要素的状态
     * @param $data
     * @return string
     */
    // public function chk($data){
    //     //1 从接口中获取数据
    //     $result = $this->getApiBank($data);
    //     // if(!$result){
    //     //     //Logger::dayLog("bank4",'getapibank','QUERY_NO_RESPONSE',$this->errinfo);
    //     //     return $this->returnError(FALSE, "服务无响应,请稍后再试");
    //     // }

    //     //2 解析json
    //     $res = json_decode($result, true);
    //     if( !isset($res['success']) || !$res['success'] ){
    //         $error = isset($res['error']) ? $res['error'] : "查询失败";
    //         return $this->returnError(FALSE, $error);
    //     }

    //     //3 获取结果字符串
    //     $r = ArrayHelper::getValue($res, "data.checkStatus");
    //     if( $r == 'SAME' ){
    //         return true;
    //     }else{
    //         $err = ArrayHelper::getValue($res, "data.result");
    //         return $this->returnError(FALSE, $r.'|'.$err);
    //     }
    // }
    /**
     * @param $data
     * @return string json
     */
    public function getApiBank($data)
    {
        // @todo
        //$result = '{"success":true,"data":{"name":"黄鸿婕","identityCard":"350629199409150028","accountNo":"6217001850008924194","bankPreMobile":"13850553698","result":"认证信息匹配"}}';
        //return $result;

        //1 获取 token 数据
        $token = $this->getToken();
        if (!$token) {
            return $this->returnError(null, "token 获取失败:".$this->errinfo);
        }

        //2 组合参数并返回结果
        $queryData = [
            'account'   => $this->account,
            'accessToken' => $token,
            'name'  => $data['username'],
            'idCard' => $data['idcard'],
            'accountNO' => $data['cardno'],
            'bankPreMobile' => $data['phone'],
        ];
        $url = $this->bankurl . '?' . http_build_query($queryData);
        //$url = $this->bankurl . "?account={$queryData['account']}&accssToken={$queryData['accssToken']}&name={$queryData['name']}&idCard={$queryData['idCard']}&accountNO={$queryData['accountNO']}&bankPreMobile={$queryData['bankPreMobile']}";
        $res = $this->HttpClient($url);
        return $res;
    }

    private function HttpClient($url)
    {
        $curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 60);
        $content = '';
        $content = $curl->get($url);
        $status = $curl->getStatus();
        if ($status != 200) {
            Logger::dayLog(
                "txsk",
                "请求信息", $url,
                "http状态", $status,
                "响应内容", $content
            );
        }
        return [
            'status' => $status,
            'data'   => $content
        ];
    }

    /**
     * 返回错误信息
     */
    public function returnError($result, $errinfo)
    {
        $this->errinfo = $errinfo;
        return $result;
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
     * 获取配置文件
     * @param $cfg
     * @return mixed
     * @throws \Exception
     */
    public function getConfig($cfg)
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."prod{$cfg}.php";
        if (!file_exists($configPath)) {
            //throw new \Exception($configPath . "配置文件不存在", 98);
            return false;
        }
        $config = include $configPath;
        return $config;
    }
}

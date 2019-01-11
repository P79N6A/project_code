<?php
/**
 * 天行数科学信网数据接口
 * 此接口不区分生产环境和测试环境
 */
namespace app\models\txsk;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Http;
use app\common\Logger;
use app\common\Curl;
use app\models\xs\YArray;

class TxskApi
{
    public $errinfo;// 错误结果

    private $tokenurl;
    private $bankurl;
    private $account;
    private $signature;
    private $curl;

    private $cacheKey="txsk_token";

    public function __construct()
    {
        $this->tokenurl = "http://tianxingshuke.com/api/rest/common/organization/auth";
        $this->bankurl  = "http://tianxingshuke.com/api/rest/education/degree";
        $this->account = "xhh";
        $this->signature = "2788b38a36ca419c90ffa15543915fa3";

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
     * @param $data
     * @return string json
     */
    public function QueryApiEdu($data)
    {
        //1 获取 token 数据
        $token = $this->getToken();
        if (!$token) {
            return $this->returnError(false, "token 获取失败:".$this->errinfo);
        }
        //2 组合参数并返回结果
        $queryData = [
            'account'   => $this->account,
            'accessToken' => $token,
            'name'  => $data['realname'],
            'idCard' => $data['identity'],
        ];
        $url = $this->bankurl . '?' . http_build_query($queryData);
        // var_dump();die;
        if (SYSTEM_PROD) {
            $res = $this->HttpClient($url);
        } else {
            //test data
            $res = [
                'status' => 200,
                'data' => '{"success":true,"data":{"name":"苏航","identityCard":"110105199002058411","graduateSchool":"北京科技大学","educationBackground":"本科","matriculationTime":"2011","profession":"土木工程","graduationTime":"2014","graduationConclusion":"毕业","educationType":"网络教育","queryResult":"EXIST"}}',
            ];
            // $res = [
            //     'status' => 200,
            //     'data' => '{"success":false,"code":30001002,"error":"POLICE_IDENTITY_CHECK_NAME_INVALID","errorDesc":"身份证验证输入姓名不合法"}',
            // ];
             
        }

        if (isset($res['status']) && $res['status'] != 200 ) {
            return $this->returnError(false, '接口异常');
        }
        $res_data = json_decode($res['data'],true);
        return $res_data;
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
     * 获取本地学信网信息
     */
    public function getTxskedu($data)
    {
        $bd_data = [];
        $oBd = (new Txskedu) -> getResult($data['user_id'],$data['identity']);
        //请求百度金融接口是否成功
        if (empty($oBd)) {
            return $bd_data;
        }
        if (isset($oBd['result_info']) && !empty($oBd['result_info'])) {
            $bd_data = json_decode($oBd['result_info'],true);
        }
        return $bd_data;
    }
}

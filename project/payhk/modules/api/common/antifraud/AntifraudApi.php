<?php
/**
 * 腾讯云反欺诈 xlj 2018年10月22日14:46:09
 */
namespace app\modules\api\common\antifraud;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use Yii;

class AntifraudApi
{
    const STATUS_INIT = 0; // 初始  只请求未抓取
    const STATUS_DOING = 1; // 抓取中
    const STATUS_SUCCESS = 2; // 成功
    /**
     * 获取配置文件
     * @param $cfg
     * @return mixed
     * @throws \Exception
     */
    public function getConfig()
    {
        $is_prod = SYSTEM_PROD ? true : false;
        $cfg = $is_prod ? "prod" : 'dev';
        $configPath = __DIR__ . DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 100);
        }
        $config = include $configPath;
        return $config;

    }


    function sendRequest($url, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (false !== strpos($url, "https")) {
            // 证书
            // curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $resultStr = curl_exec($ch);
        $result = json_decode($resultStr, true);

        return $result;
    }

    /* Generates an available URL */
    function makeURL($method, $action, $region, $secretId, $secretKey, $args)
    {

        $config = $this->getConfig();
        $URL = $config['query_url'];
        /* Add common parameters */
        $args['Nonce'] = (string)rand(0, 0x7fffffff);
        $args['Action'] = $action;
        $args['Region'] = $region;
        $args['SecretId'] = $secretId;
        $args['Timestamp'] = (string)time();

        /* Sort by key (ASCII order, ascending), then calculate signature using HMAC-SHA1 algorithm */
        ksort($args);
        $args['Signature'] = base64_encode(
            hash_hmac(
                'sha1', $method . $URL . '?' . $this->makeQueryString($args, false),
                $secretKey, true
            )
        );

        /* Assemble final request URL */

        return 'https://' . $URL . '?' . $this->makeQueryString($args, true);
    }

    /* Construct query string from array */
    function makeQueryString($args, $isURLEncoded)
    {
        $arr = array();
        foreach ($args as $key => $value) {
            if (!$isURLEncoded) {
                $arr[] = "$key=$value";
            } else {
                $arr[] = $key . '=' . urlencode($value);
            }
        }
        return implode('&', $arr);
    }



    function antiFraud($params, $region='gz')
    {
        $data['name'] = ArrayHelper::getValue($params,'name','0');
        $data['phoneNumber'] = '0086-'.ArrayHelper::getValue($params,'mobile','0');
        $data['idNumber'] = ArrayHelper::getValue($params,'idCardno','0');
        $config = $this->getConfig();
        /*
		* 补充用户、行为信息数据,方便我们做更准确的数据模型
		* 协议参考 https://www.qcloud.com/document/product/295/6584
		*/
        $url = $this->makeURL('GET', 'AntiFraud', $region,$config['SECRET_ID'] ,$config['SECRET_KEY'] , $data);
        $result = $this->sendRequest($url);
        return $result;
    }


    /*
	  *$parm  $value  用户传输的数据
	  *$parm
	  */
    public function sendFormat($value){
        $data=[
            'name'    => ArrayHelper::getValue($value,'name'),
            'mobile'        => ArrayHelper::getValue($value,'mobile'),
            'idcardno'       =>   ArrayHelper::getValue($value,'idCardno'),
            'user_id'       =>   ArrayHelper::getValue($value,'user_id'),
            'aid'       =>   ArrayHelper::getValue($value,'aid'),
            'status'   =>  self::STATUS_INIT,
            'create_time'   =>   date('Y-m-d H:i:s'),
        ];
        return $data;

    }

//    function main(){
//        $params = array(
//            # 基本字段
//            'idNumber'              => '410823199403130017',
//            'phoneNumber'           => '0086-1803917827',
//            # 可选字段
//            'name'                  => '薛林杰',
//        );
//        print_r($this->antiFraud($params));
//    }



}
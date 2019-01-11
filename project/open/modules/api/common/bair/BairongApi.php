<?php
/**
 * 数据魔盒H5 对接接口公共类
 */
namespace app\modules\api\common\bair;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use Yii;

class BairongApi
{

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

    /**
     * 获取tokenid
     * @param
     * @return mixed
     * @throws \Exception
     */
   public function getTokenid(){
       $config = $this->getConfig();
       $apiCode = ArrayHelper::getValue($config,'apiCode');
       $userName = ArrayHelper::getValue($config,'userName');
       $pssword = ArrayHelper::getValue($config,'password');
       $urlToken = ArrayHelper::getValue($config,'urlToken');
       $data =  "apiCode=$apiCode&userName=$userName&password=$pssword";
       $tokenid = $this->getPost($urlToken,$data);
       $result = json_decode($tokenid, true);
       if($result['code']!="00") {
           Logger::dayLog('bairong','Request/error','请求tokenID参数：',$data);
           Logger::dayLog('bairong','Request/error','tokenid返回信息：',$tokenid);
           return false;
       }
       return $result[tokenid];

  }

    /*
    *$parm  $tokenid  tokenid
    *$parm  $postdata  请求接口的数据
    */
    public function getResult($postdata,$tokenid){

        $config = $this->getConfig();
        $apiCode = ArrayHelper::getValue($config,'apiCode');
        $url = ArrayHelper::getValue($config,'url');
        $json = json_encode($postdata);
        $checkCode = MD5($json.MD5($apiCode.$tokenid));
        $data = "tokenid=$tokenid&apiCode=$apiCode&jsonData=$json&checkCode=$checkCode";
        $result = $this->getPost($url,$data);
        $res = json_decode($result,true);
        $code = ArrayHelper::getValue($res,'code');
        if($code != 00 && $code != "100002") {
            Logger::dayLog('bairong','Request/error','请求参数：',$data);
            Logger::dayLog('bairong','Request/error','返回信息：',$result);

            return false;
        }
        /*if($res['code']=="100002"){

            return '查询成功,没有模块命中';
        }*/
        return $result;

    }


    /*
    *$parm  $value  用户传输的数据
    *$parm  $postdata  请求接口的数据
    */
    public function sendFormat($value){

        $data=[
            'user_id'    => ArrayHelper::getValue($value,'user_id'),
            'aid'        => ArrayHelper::getValue($value,'aid',0),
            'name'       =>   ArrayHelper::getValue($value,'name'),
            'idcard'   =>   ArrayHelper::getValue($value,'idcard'),//身份证
            'cell'      =>   ArrayHelper::getValue($value,'cell'),
            'loan_id'   =>  ArrayHelper::getValue($value,'loan_id'),

        ];
        return $data;

    }

    /**
     * 建立post请求
    * $url 请求地址
     * $data 请求数据
     ***/

    public function getPost($url,$data){

        $ch = curl_init();//打开
        curl_setopt($ch, CURLOPT_POST, true);//传输方式
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//返回的内容作为变量储存，而不是直接输出
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER,0);//过滤http头
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//post传输的数据
        $result = curl_exec($ch);
        //var_dump(curl_error($ch));die;//打印错误信息
        curl_close($ch);
        if (empty($result)){
            return false;
        }
        $result_data = json_decode($result, true);
        return $result;

    }



}
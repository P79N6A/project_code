<?php
/**
 * 数据魔盒H5 对接接口公共类
 */
namespace app\modules\api\common\shenyue;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\common\Func;
use Yii;

class ShenyueApi
{
    const STATUS_INIT = 0; // 初始  只请求未抓取
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_FAILURE = 11; // 通知失败
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
    请求神月和流量监控接口
    * @param $postdata 三要素
  */
   public function  createResport($postdata){

       if (empty($postdata['name']) || empty($postdata['idcard']) || empty($postdata['mobile'])) {
           Logger::dayLog('fulin','Request/error','数据不全：',$postdata);
           return false;
       }
       $config = $this->getConfig();
       $data = [

           'name' => ArrayHelper::getValue($postdata,'name'),
           'mobile' => ArrayHelper::getValue($postdata,'mobile'),
           'pid' => ArrayHelper::getValue($postdata,'idcard'),
           'loan_type' => '3',
       ];
       $username =ArrayHelper::getValue($config,'username');
       $pas = ArrayHelper::getValue($config,'pas');
       $source = ArrayHelper::getValue($postdata,'source');//1流量接口2神月接口
       if($source==1){
           $url = ArrayHelper::getValue($config,'query_url');
       }else{
           $url = ArrayHelper::getValue($config,'url');
       }
       $datajson = json_encode($data,JSON_UNESCAPED_UNICODE);//中文不转译
       $pwdStr = $username.':'.$pas;
       $password=base64_encode($pwdStr);
       $res = $this->Posturl($url,$datajson,$password);
       if($res == false){
           return false;
       }else{
           return $res;
       }

   }

    //保存json数据文件
    public function saveJsonData($data,$request_id){
        //线上存储路径共享文件
        $path = '/../../openapi_ofiles/openapi/shenyue/' . date('Ym/d/') . $request_id . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        //访问路径
        $path = '/ofiles/openapi/shenyue/' . date('Ym/d/') . $request_id . '.json';
        $url = [
            'url'=>$path,
        ];
        return $url;
    }


    /*
     * post   Authorization  application/json请求
    */
    public function Posturl($url,$datajson,$password){
        $headers = array();
        $headers[] = 'Accept: text/html,application/json,application/xml;q=0.9,*/*;q=0.8';//请求支持的歌声格式
        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3';
        $headers[] = 'Cache-Control: max-age=0';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Content-Type: application/json ';
        $headers[] = 'Upgrade-Insecure-Requests: 1';
        $headers[] = "Authorization: Basic $password";

        $ch = curl_init();//打开
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);//传输方式
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);//返回的内容作为变量储存，而不是直接输出
        curl_setopt($ch, CURLOPT_TIMEOUT,8);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datajson);//post传输的数据
        curl_setopt($ch, CURLOPT_HEADER,0);//过滤http头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//ssl证书不验证
       /*curl_setopt($ch, CURLOPT_HTTPHEADER, array(    //注意post Authorization加密不能写这个否则用户密码无法获取
            'Content-Type: application/json',
            'Content-Length: ' . strlen($datajson)
        ));*/
        $result = curl_exec($ch);
        //var_dump(curl_error($ch));die;//打印错误信息
        curl_close($ch);
        if (empty($result)){
            return false;
        }
        $result_data = json_decode($result, true);
        $errorCode = ArrayHelper::getValue($result_data,'errorCode');//错误信息
        if(!empty($errorCode)){
            //记录返回错误信息
            Logger::dayLog('shenyue','result/error',"$errorCode：",json_decode($datajson));
            return false;
        }
        return $result_data;
    }

    /*
       *$parm  $value  用户传输的数据
       *$parm
       */
    public function sendFormat($value){

        $data=[
            'name'          => ArrayHelper::getValue($value,'name'),
            'mobile'        => ArrayHelper::getValue($value,'mobile'),
            'idcard'           =>   ArrayHelper::getValue($value,'idcard'),
            'aid'           =>  ArrayHelper::getValue($value,'aid'),
            'loan_id'       =>  ArrayHelper::getValue($value,'loan_id'),
            'user_id'       =>   ArrayHelper::getValue($value,'user_id'),
            'source'        =>   ArrayHelper::getValue($value,'source'),
            'status'         =>  self::STATUS_INIT,
            'create_time'    =>   date('Y-m-d H:i:s'),

        ];
        return $data;

    }


}
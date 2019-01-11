<?php
/**
 * 学历公共类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 16:24
 */
namespace app\modules\api\common\eduauth;

use app\common\Logger;
use app\models\edu\DucreditTicket;

class EduApi
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
     * @param $PostArry
     * @param $request_url
     * @return mixed
     */
    public function Post($PostArry,$request_url){
        $postData = $PostArry;
        $postDataString = http_build_query($postData);//格式化参数
        Logger::dayLog("ducredit/http", 'request',",请求地址:".$request_url."请求参数：".$postDataString);
        //die();
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $request_url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postDataString); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 60); // 设置超时限制防止死循环返回
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $tmpInfo = curl_error($curl);//捕抓异常
            Logger::dayLog("ducredit/http", 'abnormal',"异常："+$tmpInfo);
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

    /**
     * 检查ticket是否在效
     * @param $ducredit_ticket
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function checkTicket($ducredit_ticket)
    {
        if (empty($ducredit_ticket)){
            return false;
        }
        $oDucreditTicket = new DucreditTicket();
        $getRecord = $oDucreditTicket->getTicket($ducredit_ticket);
        if (empty($getRecord)){
            return false;
        }
        return $getRecord;
    }
}
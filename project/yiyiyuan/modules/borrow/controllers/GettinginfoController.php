<?php
/**
 * 提供获取用户信息
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 18:29
 * http://xianhuahua.com/borrow/gettinginfo?token=310a66fb27b5d9bdf581f5766707e87d&timestamp=1544669630&sign=6403DF597F3C869C20ED0FA191A346CA
 * http://yyytest2.xianhuahua.com/borrow/gettinginfo?token=uiOjMe6mMch5Q7peLlnWbA%3D%3D&timestamp=1544669630&sign=6403DF597F3C869C20ED0FA191A346CA
 *
 */

namespace app\modules\borrow\controllers;

use app\common\ApiCrypt;
use app\common\Logger;
use app\models\news\RequestLog;
use app\models\news\User;
use app\models\RequestLogPig;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;

class GettinginfoController extends BorrowController
{
    private $key_code = "wx476bb3649401c450";
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user' => 'newDev',
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [],
                        //'roles' => ['@'], //@代表授权用户
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        //1. 获取请求参数
        $get_data = $this->get();
        Logger::dayLog("gettinginfo", "get:", json_encode($get_data)."\n");
        if (empty($get_data)){
            return $this->returnMessage(1, "请求参数不能为空");
        }
        //Logger::dayLog();
        //获取token
        $token = ArrayHelper::getValue($get_data, "token");
        $sign_data = [
            'token'         => $token,
            'timestamp'     => ArrayHelper::getValue($get_data, "timestamp"),
        ];
        $encrySign = $this->encrySign($sign_data);
        Logger::dayLog("gettinginfo", "sign:", $encrySign."\n");
        $sign = ArrayHelper::getValue($get_data, "sign");

        //验证标签
        if ($sign !== $encrySign){
            return $this->returnMessage(2, "sign验证失败");
        }

        //查找请求数据
        $oRequestLog = new RequestLog();
        $request_info = $oRequestLog -> getDataForKey($token);
        $user_id = ArrayHelper::getValue($request_info, "user_id");
        //$user_id = "79770541";
        //用户信息查找
        $oUser = new User();
        $user_info = $oUser->getUserinfoByUserId($user_id);
        if (empty($user_info)){
            return $this->returnMessage(4, "信息不存在");
        }
        return $this->returnMessage(0,$user_info);

    }

    /**
     * 加密sign
     * @param $data_set
     * @param string $secret
     * @return bool|string
     */
    private function encrySign($data_set, $secret = "yyy")
    {
        if (empty($data_set)) {
            return false;
        }
        ksort($data_set);
        if (SYSTEM_ENV == 'prod'){
            $secret = "I7H40aFW4r2RjYgD";
        }
        else{
            $secret = "yyy";
        }
        $format_url = urldecode(http_build_query($data_set));
        $yyyopenid=$secret.$format_url.$secret;
        Logger::dayLog("gettinginfo", "加密参数:", $yyyopenid."\n");
        $yyyopenid = md5($yyyopenid);
        return strtoupper($yyyopenid);

    }

    private function returnMessage($code, $data_set='')
    {
        $return_data = "";
        $return_data = $data_set;
        if (!empty($data_set) && $code === 0){
            $return_data = [
                "telephone"     => ArrayHelper::getValue($data_set, "mobile"),
                "nickname"      => "",
                "name"          => "",
                "openid"        => ArrayHelper::getValue($data_set, "mobile"),
            ];
        }
        return $this->jsonOutUser($code, $return_data);
    }

    private function jsonOutUser($res_code, $res_data) {
        return json_encode([
            'res_code' => $res_code,
            'data' => $res_data,
        ]);
    }

    //数据解密
    private function decrypt($data_set,$key)
    {
        if (empty($data_set) || empty($key)){
            return false;
        }
        $oApiCrypt = new ApiCrypt();
        $data_set = $oApiCrypt->decrypt($data_set, $key);
        return $data_set;
    }

    //获取token中的user_id
    private function getTokenData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $user_id = mb_substr($data_set, 0,-5);
        return $user_id;
    }

    //生成token
    private function makeTokenData($user_id)
    {
        if (empty($user_id)){
            return false;
        }
        $time = time();
        $time = mb_substr($time, 4, 5);
        $user_str = $user_id.$time;
        $oApiCrypt = new ApiCrypt();
        $data_set = $oApiCrypt->encrypt($user_str, $this->key_code);
        return urlencode($data_set);
    }

}

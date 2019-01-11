<?php

namespace app\modules\mall\controllers;

use app\common\ApiClientCrypt;
use app\common\BaseController;
use app\commonapi\Crypt3Des;
use app\commonapi\ErrorCode;
use app\commonapi\Logger;
use app\models\news\User;
use Yii;
use yii\filters\AccessControl;

/**
 * 微信前端控制器父类
 */
abstract class MallController extends BaseController {

    public $layout = 'main';
    static $_appid;
    static $_appSecret;

    public function init() {
        parent::init();
        self::$_appid = SYSTEM_ENV == 'prod' ? 'wx476bb3649401c450' : 'wxebb286d89943a38b';
        self::$_appSecret = SYSTEM_ENV == 'prod' ? 'a19d2451136f6084048385b93f0625f9' : '9eee5b450374c4f0a4d3205b9833166b';
    }

    /**
     * 返回session信息
     */
    public function getUser() {
        return Yii::$app->newDev->identity;
    }

    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
//        $userId = $this->get('user_id_store');
//        if ($userId) {
//            $api = new ApiClientCrypt();
//            $userid = Crypt3Des::decrypt($userId, $api->getKey()); //24BEFILOPQRUVWXcdhntvwxy
//            if (!$userid) {
//                exit('用户信息不存在');
//            }
//            $userInfo = User::findIdentity($userid);
//            if (!$userInfo) {
//                exit('用户信息不存在');
//            }
//            Yii::$app->newDev->login($userInfo, 1);
//        }
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user' => 'mall',
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [],
                        'roles' => ['@'], //@代表授权用户
                    ],
                ],
            ],
        ];
    }

    //设置session
    public function setVal($key, $val) {
        Yii::$app->session->set($key, $val);
    }

    //获取session
    public function getVal($key) {
        return Yii::$app->session->get($key);
    }

    //删除session
    public function delVal($key) {
        return Yii::$app->session->remove($key);
    }

    //删除redis
    public function delRedis($key) {
        Yii::$app->redis->del($key);
    }

    //获取redis
    public function getRedis($key) {
        return Yii::$app->redis->get($key);
    }

    //设置redis
    public function setRedis($key, $val) {
        return Yii::$app->redis->setex($key, 1800, $val);
    }

    //设置redis 不过期
    public function setNotRedis($key, $val) {
        return Yii::$app->redis->setex($key,172800,$val);
    }

    /**
     * 删除cookie值
     * @param $key
     */
    public function delCookieVal($key) {
        setcookie($key, '', time() - 3600 * 24);
    }

    /**
     * 设置cookie值
     * @param $key
     * @param $val
     */
    public function setCookieVal($key, $val) {
        setcookie($key, $val, time() + 3600 * 24);
    }

    /**
     * 获取cookie值
     * @param $key
     * @return string
     */
    public function getCookieVal($key) {
        if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return '';
        }
    }

    /**
     * 显示结果信息
     * @param $res_code 错误码 0 正确   >0错误
     * @param $res_data 结果   错误原因
     * @param null $type
     * @param null $redirect
     * @return string
     */
    protected function showMessage($res_code, $res_data, $type = null, $redirect = null) {
        // 自动判断返回类型
        if (empty($type)) {
            $type = Yii::$app->request->getIsAjax() ? 'json' : 'html';
        }
        $type = strtoupper($type);

        // 返回结果: 统一json格式或消息提示代码
        switch ($type) {
            case 'JSON':
                return json_encode([
                    'res_code' => $res_code,
                    'res_data' => $res_data,
                ]);
                break;

            default:
                $redirect = is_null($redirect) ? Yii::$app->request->getReferrer() : $redirect;
                $this->view->title = '一亿元';
                return $this->render('/showmessage', [
                            'res_code' => $res_code,
                            'res_data' => $res_data,
                            'redirect' => $redirect,
                ]);
                break;
        }
    }

    //获取msg
    protected function getErrorMsg($code) {
        $errorCode = new ErrorCode();
        $rsp_msg = $errorCode->getMallErrCode($code);
        return $rsp_msg;
    }

    /**
     * 获取csrf
     * @return string
     */
    protected function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 电话验证
     * @param  int   $phone     号码
     * @return bool;
     */
    protected function chkPhone($phone) {
        if (empty($phone)) {
            return false;
        }
        if (!preg_match('/^0\d{2,3}\-?\d{7,8}$/', $phone)) {
            if (!preg_match('/^1(([35678][0-9])|(47))\d{8}$/', $phone)) {
                return false;
            }
        }
        return true;
    }

}

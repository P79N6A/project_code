<?php

namespace app\modules\borrow\controllers;

use app\common\BaseController;
use app\commonapi\ErrorCode;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Accesstoken;
use app\models\news\Common;
use app\models\news\User;
use Yii;
use yii\filters\AccessControl;

/**
 * 微信前端控制器父类
 */
abstract class BorrowController extends BaseController {

    //public $layout = 'main';
    static $_appid;
    //= 'wx476bb3649401c450';
    static $_appSecret;

    //= 'a19d2451136f6084048385b93f0625f9';

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
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user' => 'newDev',
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

    /**
     * 获取csrf
     * @return string
     */
    protected function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
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
    public function setRedis($key, $val, $times = 1800) {
        return Yii::$app->redis->setex($key, $times, $val);
    }

    //设置redis 1天
    public function setRedisDays($key, $val, $times = 172800) {
        return Yii::$app->redis->setex($key, $times, $val);
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

    public function make_signature($nonceStr, $timestamp, $jsapi_ticket, $url) {
        $tmpArr = array(
            'noncestr' => $nonceStr,
            'timestamp' => $timestamp,
            'jsapi_ticket' => $jsapi_ticket,
            'url' => $url
        );
        ksort($tmpArr, SORT_STRING);
        $string1 = http_build_query($tmpArr);
        $string1 = urldecode($string1);
        $signature = sha1($string1);
        return $signature;
    }

    //生成随机字符串
    public function make_nonceStr() {
        $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 16; $i++) {
            $codes[$i] = $codeSet[mt_rand(0, strlen($codeSet) - 1)];
        }
        $nonceStr = implode($codes);
        return $nonceStr;
    }

    /**
     * 完善流程顺序，获取下一个页面路径
     * @param string $string order
     * @param int $type 1:借款;2:实名认证;3:工作信息;4:自拍照;5:联系人;6:绑卡;7:手机认证;8:京东认证;9:借款决策;10:购买担保卡;11:邀请认证;12：储蓄卡绑卡；13：信用卡绑卡;14:担保借款 15:借款不绑卡
     * @param int $end  是否有结束的end_url 0:否，1：是
     * @return string
     */
    public function getNextpage($string, $type, $end = 0) {
        $params = Common::decrypt3Des($string);
        $params = json_decode($params, true);
        $next_url = '';
        if (!isset($params['data'])) {
            return $next_url;
        }
        if ($end == 1) {
            return isset($params['end_url']) ? $params['end_url'] : '';
        }
        $index = array_search($type, array_keys($params['data']));
        $arr1 = array_slice($params['data'], $index + 1, null, true);
        foreach ($arr1 as $key => $val) {
            if ($val['status'] == 0) {
                $next_url = $val['current_url'];
                break;
            }
        }

        if ($next_url == '') {
            $next_url = $params['come_url'];
        }
        return $next_url;
    }

    /**
     * 获取跳转地址
     * @param $orderinfo
     * @param $current_code 发起认证的代码 1:借款;2:实名认证;3:工作信息;4:自拍照;5:联系人;6:绑卡;7:手机认证;8:京东认证;9:借款决策;10:购买担保卡;11:邀请认证;12：储蓄卡绑卡；13：信用卡绑卡;14:担保借款
     * @param int $end 是否有结束的end_url 0:否，1：是
     * @param int $type 1：新增，2：修改
     * @return string
     */
    protected function nextUrl($orderinfo, $current_code, $end = 0, $type = 1) {
        if ($orderinfo == '') {
            exit;
        }
        if ($type == 2) {
            $params = Common::decrypt3Des($orderinfo);
            $params = json_decode($params, true);
            return $params['nextPage'] . '?orderinfo=' . urlencode($orderinfo);
        }
        $nextpage = $this->getNextpage($orderinfo, $current_code, $end);
        if ($current_code == 6 || $current_code == 12 || $current_code == 13) {
            $nextUrl = $nextpage . '&orderinfo=' . urlencode($orderinfo);
        } else {
            $nextUrl = $nextpage . '?orderinfo=' . urlencode($orderinfo);
        }

        return $nextUrl;
    }

    /**
     * 获取图形验证码
     * @param $num
     * @param $w
     * @param $h
     */
    public function getImgCode($num, $w, $h, $mobile = '') {
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }
        //将生成的验证码写入session，备验证页面使用
//        $_SESSION["code_char"] = $code;
        $this->setVal('code_char', $code);
        if (!empty($mobile)) {
            $this->setVal('code_char_' . $mobile, $code);
            $this->setRedis('code_char_' . $mobile, $code);
        } else {
            $this->setVal('code_char', $code);
            $this->setRedis('code_char', $code);
        }
        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im = imagecreate($w, $h);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        $gray = imagecolorallocate($im, 118, 151, 199);
        $bgcolor = imagecolorallocate($im, 235, 236, 237);

        //画背景
        imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
        //画边框
        imagerectangle($im, 0, 0, $w - 1, $h - 1, $gray);
        //imagefill($im, 0, 0, $bgcolor);
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
        }
        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(3, 8);
        for ($i = 0; $i < $num; $i++) {
            $strpos = rand(1, 1);
            imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 14);
        }
        imagepng($im);
        imagedestroy($im);
    }

    //获取邀请码
    public function getCode() {
        $code = $this->makeCode(8, 1);
        $user = new User();
        $isone = $user->getUserinfoByInvitecode($code);
        if (isset($isone->user_id)) {
            return $this->getCode();
        } else {
            return $code;
        }
    }

    //生成6位数的邀请码
    public function makeCode($length = 32, $mode = 0) {
        switch ($mode) {
            case '1':
                $str = '1234567890';
                break;
            case '2':
                $str = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case '3':
                $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                $str = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $result = '';
        $l = strlen($str) - 1;
        $num = 0;

        for ($i = 0; $i < $length; $i ++) {
            $num = rand(0, $l);
            $a = $str[$num];
            $result = $result . $a;
        }
        return $result;
    }

    //获取微信参数
    public function getWxParam() {
        $timestamp = time();
        $access_token = $this->getAccessToken();
        $jsapi_ticket = $this->getJsticket($access_token);
        $nonceStr = $this->make_nonceStr();
        $url = Yii::$app->request->hostInfo . Yii::$app->request->getUrl();
        $signature = $this->make_signature($nonceStr, $timestamp, $jsapi_ticket, $url);
        $data = array(
            'timestamp' => $timestamp,
            'appid' => self::$_appid,
            'access_token' => $access_token,
            'nonceStr' => $nonceStr,
            'signature' => $signature
        );
        return $data;
    }

    //获取access_token值
    public function getAccessToken() {
        $appId = self::$_appid; //定义AppId，需要在微信公众平台申请自定义菜单后会得到
        $appSecret = self::$_appSecret;  //定义AppSecret，需要在微信公众平台申请自定义菜单后会得到
        //先查询对应的数据表是否有token值
        $access_token = Accesstoken::find()->where(['type' => 1])->one();
        if (isset($access_token->access_token)) {
            //判断当前时间和数据库中时间
            $time = time();
            $gettokentime = $access_token->time;
            if (($time - $gettokentime) > 7000) {
                //重新获取token值然后替换以前的token值
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
                $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
                $resultArr = json_decode($data, true); //转为数组
                $accessToken = $resultArr["access_token"]; //获取access_token
                $result = $access_token->update_record($accessToken);
                return $accessToken;
            } else {
                return $access_token->access_token;
            }
        } else {
            //获取token值并把token值保存在数据表中
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
            $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
            $resultArr = json_decode($data, true); //转为数组
            $accessToken = $resultArr["access_token"]; //获取access_token
            $tokenModel = new Accesstoken();
            $result = $tokenModel->add_record($accessToken);
            return $accessToken;
        }
    }

    //获取jsticket值
    public function getJsticket($access_token) {
        //先查询对应的数据表是否有ticket值
        $jsticket = Accesstoken::find()->where(['type' => 2])->one();
        if (isset($jsticket->access_token)) {
            //判断当前时间和数据库中时间
            $time = time();
            $gettokentime = $jsticket->time;
            if (($time - $gettokentime) > 7000) {
                //重新获取ticket值然后替换以前的ticket值
                $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
                $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
                $resultArr = json_decode($data, true); //转为数组
                if ($resultArr['errcode'] != 0) {
                    $accessToken = $this->getAccessToken();
                    $ticket = $this->getJsticket($accessToken);
                    return $ticket;
                } else {
                    $ticket = $resultArr["ticket"]; //获取access_token
                }
                $result = $jsticket->update_record($ticket);
                return $ticket;
            } else {
                return $jsticket->access_token;
            }
        } else {
            //获取ticket值并把ticket值保存在数据表中
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
            $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
            $resultArr = json_decode($data, true); //转为数组
            $ticket = $resultArr["ticket"]; //获取ticket

            $tokenModel = new Accesstoken();
            $result = $tokenModel->add_record($ticket, 2);

            return $ticket;
        }
    }

    //获取msg
    protected function getErrorMsg($code) {
        $errorCode = new ErrorCode();
        $rsp_msg = $errorCode->geterrorcode($code);
        return $rsp_msg;
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

    protected function reback($rsp_code, $array = [], $error_msg = '') {
        $codeArray = (new ErrorCode())->getErrorArr($rsp_code, $error_msg);
        if (!empty($array)) {
            $codeArray = array_merge($codeArray, $array);
        }
        return $codeArray;
    }

}

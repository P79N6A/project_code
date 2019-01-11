<?php
namespace app\modules\sevenday\controllers;

use app\common\BaseController;
use app\commonapi\ErrorCode;
use Yii;
use yii\filters\AccessControl;

abstract class SevendayController extends BaseController {
    public $layout = 'main';

    public function init() {
        parent::init();
    }

    /**
     * 返回session信息
     */
    public function getUser() {
        return Yii::$app->seven->identity;
    }

    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user' => 'seven',
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
    protected function setVal($key, $val) {
        Yii::$app->session->set($key, $val);
    }

    //获取session
    protected function getVal($key) {
        return Yii::$app->session->get($key);
    }

    //删除session
    protected function delVal($key) {
        return Yii::$app->session->remove($key);
    }

    //删除redis
    protected function delRedis($key) {
        Yii::$app->redis->del($key);
    }

    //获取redis
    protected function getRedis($key) {
        return Yii::$app->redis->get($key);
    }

    //设置redis
    protected function setRedis($key, $val) {
        return Yii::$app->redis->setex($key, 1800, $val);
    }

    /**
     * 获取图形验证码
     * @param $num
     * @param $w
     * @param $h
     */
    protected function getImgCode($num, $w, $h) {
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }
        //将生成的验证码写入session，备验证页面使用
//        $_SESSION["code_char"] = $code;
        $this->setVal('code_char', $code);
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

    /**
     * 获取msg信息
     * @param $code
     * @return mixed|string
     * @author 王新龙
     * @date 2018/8/2 18:43
     */
    protected function getErrorMsg($code) {
        $errorCode = new ErrorCode();
        $rsp_msg = $errorCode->geterrorcode($code);
        return $rsp_msg;
    }

    /**
     * 设置cookie值
     * @param $key
     * @param $val
     * @author 王新龙
     * @date 2018/8/2 18:43
     */
    protected function setCookieVal($key, $val) {
        setcookie($key, $val, time() + 3600 * 24);
    }

    /**
     * 获取cookie值
     * @param $key
     * @return string
     * @author 王新龙
     * @date 2018/8/2 18:43
     */
    protected function getCookieVal($key) {
        if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return '';
        }
    }

    /**
     * 删除cookie值
     * @param $key
     * @author 王新龙
     * @date 2018/8/2 18:42
     */
    protected function delCookieVal($key) {
        setcookie($key, '', time() - 3600 * 24);
    }

    /**
     * 获取csrf
     * @return string
     * @author 王新龙
     * @date 2018/8/2 18:42
     */
    protected function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }
}

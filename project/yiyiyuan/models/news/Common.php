<?php

namespace app\models\news;

use app\commonapi\Crypt3Des;
use Yii;

class Common {

    /**
     * 生成图形验证码
     * @param  strint $key 存储验证码的key
     * @param  int $num 生成验证码位数
     * @param  int $w 生成画布宽度
     * @param  int $h 生成画布高度
     * @return text
     */
    public function getImgCode($key, $num, $w, $h) {
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }
        //将生成的验证码写入session，备验证页面使用
        Yii::$app->session->set($key, $code);
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
     * 验证图形验证码是否正确
     * @param  strint $key 存储验证码的key
     * @param  strint $imgcode 验证码
     * @return bool
     */
    public function chkImgCode($key, $imgCode) {
        if (empty($imgCode) || Yii::$app->session->get($key) != strtolower($imgCode)) {
            return FALSE;
        } else {
            Yii::$app->session->remove($key);
            return TRUE;
        }
    }

    /**
     * 验证手机号是否正确
     * @param number $mobile
     * @return bool true | false
     */
    public function isMobile($mobile) {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^((1(([35678][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$#', $mobile) ? true : false;
    }

    /**
     * 获取客户端ip
     * */
    public static function get_client_ip() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "";
        return($ip);
    }

    public static function create3Des($string) {
        $des3key = Yii::$app->params['des3key'];
        return Crypt3Des::encrypt($string, $des3key);
    }

    public static function decrypt3Des($string) {
        $des3key = Yii::$app->params['des3key'];
        return Crypt3Des::decrypt($string, $des3key);
    }

}

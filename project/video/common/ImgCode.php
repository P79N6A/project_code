<?php
namespace app\common;
/**
 * 图形验证码
 */
class ImgCode {
    private $code;
    public function __construct($num) {
        $num = intval($num);
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        $len = strlen($str) - 1;
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[rand(0, $len)];
        }
        $this->code = $code;
    }
    public function getCode() {
        return $this->code;
    }
    public function drawImg($w=90, $h=50) {
        return $this->getImgCode($this->code,$w, $h);
    }
    private function getImgCode($code, $w, $h) {
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
        $strx = rand(18, 20);
        $num = strlen($code);
        for ($i = 0; $i < $num; $i++) {
            $strpos = rand(22, 25);
            imagestring($im, 18, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 14);
        }
        imagepng($im);
        imagedestroy($im);
    }
}
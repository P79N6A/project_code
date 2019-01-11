<?php
/**
 * 图片加密算法
 */
namespace app\common;
class ImgEncrypt {
    static $key = "IpiEuCj2sklfFxHdbt08XoRaAXcF68j8";
    public static function encode($url) {
      $urlen = AES::encode($url, self::$key);
      return $urlen;
    }
    public static function decode($data) {
      $url = AES::decode($data, self::$key);
      return $url;
    }
}

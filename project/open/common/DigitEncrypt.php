<?php
namespace app\common;
/**
 * ,将数字加密成字母短链
 * 52进制算法
 */
class DigitEncrypt {
    /**
     * 默认加密密钥
     * @var integer
     */
    private $key = 2692833;
    public function __construct($key) {
        $key = intval($key);
        $this->key += $key;
    }
    /**
     * 数字加密成串
     * @param  int $num 
     * @return str
     */
    public function encrypt($num) {
        $num = $this->myxor($num);
        $str = $this->decf($num);
        return $str;
    }
    /**
     * 串解密成数字
     * @param string $str
     * @return int 
     */
    public function decrypt($str) {
        $num = $this->fdec($str);
        $num = $this->myxor($num);
        return $num;
    }
    /**
     * 异或
     * @param  int $num
     * @return int
     */
    private function myxor($num) {
        $num = intval($num);
        $num = $num ^ $this->key;
        return $num;
    }
    /**
     * 十进制转52进制
     * @param  int $nums
     * @return str
     */
    public function decf($nums) {
        $strs = [];
        do {
            //余数部分：
            $strs[] = $nums % 52;

            //整数部分：
            $nums = intval(floor($nums / 52));

        } while ($nums > 0);

        $fs = [];
        $strs = array_reverse($strs);
        foreach ($strs as $f) {
            $fs[] = $this->tof($f);
        }
        return implode($fs);
    }

    /**
     * 52进制转10进制
     * @param  str $strs
     * @return int
     */
    public function fdec($strs) {
        $nums = 0;
        $total = strlen($strs) - 1;
        for ($i = $total; $i >= 0; $i--) {
            $num = $this->todec($strs[$i]);
            $nums += $num * pow(52, $total - $i);
        }

        return $nums;
    }
    /**
     * 数字转字母
     * @param  int $num 0-51
     * @return char 字母 a-zA-Z
     */
    private function tof($num) {
        if ($num <= 25) {
            // 0-25 -> 小写字母
            return chr($num + 97);
        } elseif ($num <= 51) {
            // 26-51 -> 大写字母
            return chr($num + 65 - 26);
        }
    }
    /**
     * 字母转10进制数字
     * @param  char $letter 字母表 a-zA-Z
     * @return 0-51
     */
    private function todec($letter) {
        $num = ord($letter);
        if ($num >= 97) {
            // 小写字母 -> 0-25
            return $num - 97;
        } elseif ($num >= 65) {
            // 大写字母 -> 26-51
            return $num - 65 + 26;
        }
    }
}
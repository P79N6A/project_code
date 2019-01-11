<?php

namespace app\models\xs;

/**
 * 身份证检验程序
 */
class XsIdCard
{
    static private $province = [
        11=> "北京",
        12=> "天津",
        13=> "河北",
        14=> "山西",
        15=> "内蒙古",
        21=> "辽宁",
        22=> "吉林",
        23=> "黑龙江",
        31=> "上海",
        32=> "江苏",
        33=> "浙江",
        34=> "安徽",
        35=> "福建",
        36=> "江西",
        37=> "山东",
        41=> "河南",
        42=> "湖北",
        43=> "湖南",
        44=> "广东",
        45=> "广西",
        46=> "海南",
        50=> "重庆",
        51=> "四川",
        52=> "贵州",
        53=> "云南",
        54=> "西藏",
        61=> "陕西",
        62=> "甘肃",
        63=> "青海",
        64=> "宁夏",
        65=> "新疆",
        71=> "台湾",
        81=> "香港",
        82=> "澳门",
        91=> "国外",
    ];
    public function get($idcard){
        //1 非18位按不合法处理
        $idcard = trim($idcard);
        if (strlen($idcard) !== 18) {
            return null;
        }

        //2 进行18位身份证的基本验证和第18位的验证
        $birth = $this->getBirth($idcard);
        if(empty($birth)){
            return null;
        }
        $is_idcard = $this->isIdCard18($idcard);
        if(!$is_idcard){
            return null;
        }

        //3 性别
        $gender = $this->getGender($idcard);
        $province = $this->getProvince($idcard);
        return [
            'idcard' => $idcard,
            'birth' => $birth,
            'gender' => $gender,
            'province' => $province,
            //'city' => $city,
        ];
    }
    /**
     * 验证18位数身份证号码中的生日是否是有效生日
     * @param $idcard18 18位书身份证字符串
     * @return
     */
    private function getBirth($idcard18){
        $year = substr($idcard18,6,4);
        $month= substr($idcard18,10,2);
        $day  = substr($idcard18,12,2);

        $is_valid = $this->chkYmd( $year, $month, $day );
        if(!$is_valid){
            return "";
        }

        return "{$year}-{$month}-{$day}";
    }
    private function getGender($idcard18) {
        $code = substr($idcard18,-2,-1);
        $code = intval($code);
        return $code % 2 === 1 ? 1 : 0;
    }
    private function getProvince($idcard18) {
        $code = substr($idcard18,0,2);
        return isset(self::$province[$code])? self::$province[$code] : '';
    }
    /**
     * 检测年月日是否合法
     */
    private function chkYmd( $year, $month, $day ){
        $temp_date = mktime(0,0,0,$month,$day,$year);
        $format = strlen($year) == 4 ? 'Y-m-d' : 'y-m-d';
        $ymd = explode('-', date($format,$temp_date) ) ;
        return $ymd[0]==$year && $ymd[1]==$month && $ymd[2]==$day;
    }
    /**
     * 判断身份证号码为18位时最后的验证位是否正确
     * @param $idcard 身份证号码数组
     * @return
     */
    private function isIdCard18($idcard){
        // 加权因子
        $wi = array( 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 );

        // 声明加权求和变量
        $sum = 0;
        for ( $i = 0; $i < 17; $i++) {
            $sum += $wi[$i] * $idcard[$i];            // 加权求和
        }
        $pos = $sum % 11;


        // 得到验证码所位置
        $chk = $idcard[17];
        if (strtolower($chk) == 'x') {
            $chk = 10;// 将最后位为x的验证码替换为10方便后续操作
        }
        $valideCode = array(1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 );
        return $chk == $valideCode[$pos];
    }

}

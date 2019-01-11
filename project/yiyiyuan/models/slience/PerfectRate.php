<?php
namespace app\models\slience;
use app\commonapi\Logger;
/**
 * 完美比率计算
 */
class PerfectRate {
    /**
     * 支持的逾期与逾期比率
     * @var ['天':比例范围]
     */
    private $supportRate = [
        1 => [15, 17],
        4 => [9, 10],
        7 => [7, 7.5],
        15 => [6.5, 7],
        31 => [5, 5.5],
        61 => [4.5, 4.7],
        91 => [4.1, 4.4],
    ];
    /**
     * 获取支持的逾期天数
     * @return  []
     */
    public function getSupportDays() {
        return array_keys($this->supportRate);
    }
    public function getPreDays($days){
        $as = $this -> getSupportDays();
        $key = array_search($days,$as);
        if($key>0){
            return $as[$key-1];
        }else{
            return 0;
        }
    }
    /**
     * 获取需要补齐的数量
     * @return int
     */
    public function getNeedNums($days, $rate, $total) {
        //1. 合法性检查
        $days = intval($days);
        if (!$days) {
            return 0;
        }

        $isSpt = $this->isSupport($days);
        if (!$isSpt) {
            return 0;
        }

        $total = intval($total);
        if (!$total) {
            return 0;
        }
        $rate = floatval($rate);

        // 2 是否完美比率
        $isPerfectrate = $this->isPerfectrate($days, $rate);
        if ($isPerfectrate) {
            return 0;
        }

        //3 随机返回一个完美范围内的比率
        $rates = $this->supportRate[$days];
        // 在指定范围内随机一个几率,
        // 由于rand为整数, 故需要*100, 再/100; 可得精度为 0.01
        $newrate = rand(ceil($rates[0] * 100), floor($rates[1] * 100)) / 100;

        //4 需要补齐的条数
        $nums = ceil($total * ($rate - $newrate) / 100);
        return intval($nums);
    }
    /**
     * 是否支持
     * @param  [type]  $days [description]
     * @return boolean       [description]
     */
    public function isSupport($days) {
        $days = intval($days);
        return in_array($days, array_keys($this->supportRate));
    }
    /**
     * 是否完美比率
     * @param  int  $days
     * @param  float  $rate
     * @return boolean
     */
    private function isPerfectrate($days, $rate) {
        $days = intval($days);
        $rates = $this->supportRate[$days];
        // 逾期率<=最大值, 即为完美
        // $rate<= $rates[1];
        return bccomp($rate, $rates[1],2) !== 1;
    }
}
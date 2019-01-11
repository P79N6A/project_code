<?php
/**
 * 逾期
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 15:50
 */
namespace app\modules\balance\common;

class COverdue
{
    /**
     * 逾期类型
     * @param $day
     * @return string
     */
    public function overdueType($day)
    {
        if ($day > 0 and $day <=30){
            return 'M1';
        }
        if ($day > 30 and $day <= 60){
            return "M2";
        }
        if ($day > 60 and $day <= 90){
            return "M3";
        }
        if ($day > 90){
            return "坏账";
        }
        return "M1";
    }

    /**
     * 逾期类型对应天数
     * @param $type
     * @return array
     */
    public function overdueDay($type)
    {
        if ($type == 1){
            return [0, 30];
        }
        if ($type == 2){
            return [30, 60];
        }
        if ($type == 3) {
            return [60, 90];
        }
        return [90];
    }

    /**
     * 还款渠道后台显示
     */
    public static function showRepaymentChannel() {
        return [
            '1' => '线下',
            '2' => '易宝一键支付', //易宝一键支付
            '3' => '易宝投资通', //易宝投资通
            '4' => '微信支付',
            '5' => '支付宝',
            '6' => '连连支付（一亿元）', //连连支付（一亿元）
            '7' => '微信（逾期）', //微信逾期还款
            '8' => '支付宝（逾期）', //支付宝逾期还款
            '9' => '宝付认证支付（一亿元）', //宝付认证支付（一亿元）
            '10' => '连连认证支付（花生米富）', //连连认证支付（花生米富）
            '11' => '易宝代扣', //易宝代扣
            '12' => '融宝快捷（一亿元）', //融宝快捷（一亿元）
            '13' => '融宝快捷（米富）', //融宝快捷（一亿元）
            '14' => '宝付快捷（一亿元）', //融宝快捷（一亿元）
            '15' => '宝付快捷（米富）', //融宝快捷（一亿元）
            '16' => '融宝(逾期)',
            '17' => '宝付(逾期)',
            '18' => '畅捷出款',
            '19' => '畅捷快捷',
            '20' => '存管',//原新支付宝
            '21' => '新支付宝(逾期)',//废弃
            '22' => '新微信',
            '23' => '新支付宝',
            '24' => '新微信(逾期)',
            '25' => '新支付宝(逾期)',
            '26' => '存管还款',
        ];
    }

    /**
     * 资方：1、花生米福，2、玖富，3、联交所，4、金联储, 5、小诺， 6、微神马， 10、银行存管
     * @return array
     */
    public static function fund(){
        return [
            '1' => '花生米富',
            '2' => '玖富',
            '3' => '联交所',
            '4' => '金联储',
            '5' => '小诺',
            '6' => '微神马',
            '10' => '银行存管'
        ];
    }

    /**
     * 已收本金
     * 规则：已收金额-本金 （如果大于0返回本金，否刚就返回已收金额）
     * @param $repay        已收金额
     * @param $principal    实际出款金额
     * @return int
     */
    public function receivedPrincipal($repay, $principal)
    {
        if (empty($repay) || empty($principal)){
            return 0;
        }
        //已收金额-本金
        $cal_money = bcsub($repay, $principal);
        if (bccomp($cal_money, 0) >= 0){
            return $principal;
        }
        return $repay;
    }

    /**
     * 已收利息
     * $repay        已收金额
     * $principal    实际出款金额
     * $interest     实际利息
     */
    public function receivedInterest($repay, $principal, $interest)
    {
        if (empty($repay) || empty($principal) || empty($interest)){
            return 0;
        }
        //已收金额-本金
        $cal_money = bcsub($repay, $principal);
        //如果（已收金额-本金） > 实际利息 则返回 实际利息
        if (bccomp($cal_money, $interest) >= 0){
            return $interest;
        }
        //如果（已收金额-本金） > 实际利息 则返回 0
        if (bccomp($cal_money, 0) < 0){
            return 0;
        }
        //（已收金额-本金）
        return $cal_money;
    }

    /**
     * 已收滞纳金
     * $repay        已收金额
     * $principal    实际出款金额
     * $interest     实际利息
     * $overdue      滞纳金
     */
    public function receivedOverdue($repay, $principal, $interest, $overdue)
    {
        if (empty($repay) || empty($principal) || empty($interest) || empty($overdue)){
            return 0;
        }
        //已收金额-本金-实际利息
        $cal_money = bcsub(bcsub($repay, $principal, 4), $interest, 4);

        //如果 (已收金额-本金-实际利息) > 滞纳金 则返回 滞纳金
        if (bccomp($cal_money, $overdue) >=0){
            return $overdue;
        }
        //如果 (已收金额-本金-实际利息) < 0 则返回 0
        if (bccomp($cal_money, 0) < 0){
            return 0;
        }
        //(已收金额-本金-实际利息)
        return $cal_money;
    }

    /**
     * 应还本金
     * $repay      实际还款
     * $principal  应还本金
     */
    public function shouldPrincipal($repay, $principal)
    {
        if (empty($repay) || empty($principal)){
            return 0;
        }
        //应还本金 - 实际还款
        $cal_money = bcsub($principal, $repay, 4);

        //如果 （应还本金 - 实际还款） < 0 则返回 0
        if (bccomp($cal_money, 0) >=0){
            return 0;
        }
        return abs($cal_money);
    }

    /**
     * 应还利息
     * $repay      实际还款
     * $principal  应还本金
     * $interest   应还利息
     */
    public function shouldInterest($repay, $principal, $interest)
    {
        if (empty($repay) || empty($principal) || empty($interest)){
            return 0;
        }
        //实际还款 - 应还本金
        $cal_money = bcsub($repay, $principal, 4);
        //如果（实际还款 - 应还本金）< 0 则返回 应还利息
        if (bccomp($cal_money, 0) == -1){
            return $interest;
        }

        //如果（实际还款 - 应还本金） > 应还利息 则返回0
         if (bccomp($cal_money, $interest) >= 0){
             return 0;
         }

        return abs($cal_money);
    }

    /**
     * 应还滞纳金
     * $repay      实际还款
     * $principal  应还本金
     * $interest   应还利息
     * $overdue    滞纳金
     */
    public function shouldOverdue($repay, $principal, $interest, $overdue)
    {
        if (empty($repay) || empty($principal) || empty($interest) || empty($overdue)){
            return 0;
        }
        //实际还款-应还本金-应还利息
        $cal_money = bcsub(bcsub($repay, $principal, 4), $interest, 4);

        //如果（实际还款-应还本金-应还利息） < 0 则返回 滞纳金
        if (bccomp($cal_money, 0) == -1){
            return $overdue;
        }

        //如果 （实际还款-应还本金-应还利息）> 滞纳金 则返回 0
        if (bccomp($cal_money, $overdue) >= 0){
            return 0;
        }
        return abs($cal_money);
    }

}
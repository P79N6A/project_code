<?php

namespace app\models\service;

use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\BillRepay;
use app\models\news\Coupon_list;
use app\models\news\GoodsBill;
use app\models\news\Loan_rate;
use app\models\news\User_label;
use app\models\news\User_loan;
use Yii;
use yii\helpers\ArrayHelper;
/**
 * 分期服务
 */
class StageService extends Service
{
    private function my_print($data){
        var_dump("<pre>");
        var_dump($data);
        var_dump("</pre>");die;
    }
    /**
     * 添加数据
     *
     * @param [type] $oUserLoan
     * @return void
     */
    public function addStageBill($oUserLoan){
        if(empty($oUserLoan)){
            Logger::dayLog('stageservice/addStageBill', 'oUserLoan为空');
            return false;
        }
        $oGoodsBills = (new GoodsBill)->getGoodsBills($oUserLoan->loan_id);
        if(!empty($oGoodsBills)){
            Logger::dayLog('stageservice/addStageBill', 'goodsbill已存在该订单信息','loan_id',$oUserLoan->loan_id);
            return false;
        }
        $billarray = $this->getGoodsBillArray($oUserLoan);
        if (empty($billarray)) {
            return false;
        }
        //$this->my_print($billarray);
        $goods_succ = 0;
        $repay_succ = 0;
        foreach($billarray as $bill){
            $goodsBillModel = new GoodsBill();
            $res = $goodsBillModel->addRecord($bill);
            if(!$res){
                Logger::dayLog('stageservice/addStageBill', '添加goods_bill分期账单表失败',$goodsBillModel->errinfo, $oUserLoan->attributes, $bill);
                continue;
            }
            $goods_succ++;
            $repaydata = $this->getRepayBillData($oUserLoan,$goodsBillModel->id);
            $billRepayModel = new BillRepay();
            $res = $billRepayModel->add($repaydata);
            if(!$res){
                Logger::dayLog('stageservice/addStageBill', '添加bill_repay分期还款表失败',$billRepayModel->errinfo, $oUserLoan->attributes, $repaydata);
                continue;
            }
            $repay_succ++;
        }
        $period = $oUserLoan->usercredit->period;
        Logger::dayLog('stageservice/addStageBill', 'loan_id',$oUserLoan->loan_id,'goods_succ',$goods_succ,'repay_succ',$repay_succ,'period',$period);
        if($goods_succ==$repay_succ && $repay_succ==$period){
            return true;
        }else{
            return false;
        }
        
    }
    /**
     * 打开还款页面时校验规则
     * 检查10分钟内是否有修改
     *
     * @param [type] $loan_id
     * @return void
     */
    public function checkRepaybillModifytime($loan_id){
        $oBillRepays = (new BillRepay)->getBillRepayModifyTime($loan_id);
        if(!empty($oBillRepays)){
            return false;
        }
        //同步还款金额
        $oGoodsBills = (new GoodsBill)->getGoodsBills($loan_id);
        if(empty($oGoodsBills)){
            Logger::dayLog('stageservice/checkRepaybillModifytime', 'goodsbill无该订单信息','loan_id',$loan_id);
            return false;
        }
        foreach($oGoodsBills as $oGoodsBill){
            $oBillRepay = $oGoodsBill->billrepay;
            if($oBillRepay->status!=BillRepay::STATUS_SUCCESS && bccomp($oGoodsBill->actual_amount,$oBillRepay->actual_money,2)!=0){
                $oBillRepay->toSyncMoney($oGoodsBill->actual_amount);
            }
        }
        return true;
    }
    /**
     * 确认还款的时候： 检查提交的分期参数
     *
     * @param [type] $periods
     * @param [type] $loan_id
     * @return void
     */
    public function checkSubmitRepayBill($periods,$loan_id){
        if (empty($loan_id)){
            Logger::dayLog('stageservice/checkSubmitRepayBill', '没有用户借款订单信息');
            return false;
        }
        if (empty($periods)){
            Logger::dayLog('stageservice/checkSubmitRepayBill', '分期还款期数为空');
            return false;
        }
        ksort($periods);
        //提交分期键值
        $periods_keys = array_keys($periods);
        $count = count($periods_keys);
        //1.顺序检查
        for($i=0;$i<$count-1;$i++){
            $k = $periods_keys[$i]+1;
            $j = $periods_keys[$i+1];
            if($k!=$j){
                Logger::dayLog('stageservice/checkSubmitRepayBill', '分期还款参数顺序检查错误',$loan_id,$periods,$k,$j);
                return false;
            }
        }
        //2.获取分期待还款数据
        $preGoodsBills = (new GoodsBill)->getPrepayGoodsBills($loan_id);
        if(empty($preGoodsBills)){
            Logger::dayLog('stageservice/checkSubmitRepayBill', '分期还款数据为空',$loan_id);
            return false;
        }
        //查询的分期键值
        $bills_keys = array_keys($preGoodsBills);
         
        //3.判断是否包含
        foreach( $periods_keys as $key ){
            if( !in_array($key, $bills_keys) ){
                Logger::dayLog('stageservice/checkSubmitRepayBill', '分期还款参数包含子集检查错误',$loan_id,$periods,'bills_keys',$bills_keys,'periods_keys',$periods_keys);
                return false;
            }
        }
        //4.最小值检查
        if($periods_keys[0]!=$bills_keys[0]){
            Logger::dayLog('stageservice/checkSubmitRepayBill', '分期还款参数最小值检查错误',$loan_id,$periods);
            return false;
        }
        //5.金额对比并返回对象列表
        $returndata = [];
        foreach($periods as $key=>$period_amount){
            $actual_amount = $preGoodsBills[$key]['actual_amount'];
            if(bccomp($actual_amount,$period_amount,2)!=0){
                Logger::dayLog('stageservice/checkSubmitRepayBill', '分期还款参数还款金额检查错误',$loan_id,$periods);
                return false;
            }
            $returndata[$key] = $preGoodsBills[$key];
        }
        return $returndata;
    }
    /**
     * 请求支付前 锁定到待支付
     */
    public function lockToRepay($repay_id,$oGoodsBills){
        if(empty($repay_id)){
            Logger::dayLog('stageservice/lockToRepay', 'repay_id参数缺失');
            return false;
        }
        if(empty($oGoodsBills)){
            Logger::dayLog('stageservice/lockToRepay', 'oGoodsBills参数缺失,无还款对象');
            return false;
        }
        foreach($oGoodsBills as $oGoodsBill){
            $oBillRepay = $oGoodsBill->billrepay;
            if(empty($oBillRepay)){
                Logger::dayLog('stageservice/lockToRepay', 'oBillRepay无关联信息',$oGoodsBill);
                continue;
            }
            $oBillRepay->toRepay($repay_id);
        }
        return true;
    }
    /**
     * 支付请求后，同步返回结果时， 锁定到支付中
     *
     * @param [type] $repay_id
     * @return void
     */
    public function lockToRepaying($repay_id){
        if(empty($repay_id)){
            Logger::dayLog('stageservice/lockToRepaying', 'repay_id参数缺失');
            return false;
        }
        $billRepayModel = new BillRepay();
        $oBillRepays = $billRepayModel->getStatusRepaybill($repay_id,BillRepay::STATUS_REPAY);
        if(empty($oBillRepays)){
            Logger::dayLog('stageservice/lockToRepaying', '查询不到还款信息','repay_id',$repay_id);
            return false;
        }
        $ids = ArrayHelper::getColumn($oBillRepays,'id');
        $res = $billRepayModel->lockToRepaying($ids);
        if(!$res){
            Logger::dayLog('stageservice/lockToRepaying', '更新billrepay还款状态失败','repay_id',$repay_id,'ids',$ids,'res',$res);
            return false;
        }
        return true;
    }
    /**
     * 支付成功
     *
     * @param [type] $repay_id
     * @return void
     */
    public function toSuccess($repay_id,$loan_repay){
        if(empty($repay_id)){
            Logger::dayLog('stageservice/toSuccess', 'repay_id参数缺失');
            return false;
        }
        $billRepayModel = new BillRepay();
        $oBillRepays = $billRepayModel->getStatusRepaybill($repay_id,[BillRepay::STATUS_REPAY,BillRepay::STATUS_REPAYING]);
        if(empty($oBillRepays)){
            Logger::dayLog('stageservice/toSuccess', '查询不到还款信息','repay_id',$repay_id);
            return false;
        }
        $ids = ArrayHelper::getColumn($oBillRepays,'id');
        $res = $billRepayModel->toSuccess($ids,$loan_repay);
        if(!$res){
            Logger::dayLog('stageservice/toSuccess', '更新billrepay还款状态失败','repay_id',$repay_id,'ids',$ids,'res',$res);
            return false;
        }
        $bill_ids = ArrayHelper::getColumn($oBillRepays,'bill_id');
        $res = (new GoodsBill)->toSuccess($bill_ids);
        if(!$res){
            Logger::dayLog('stageservice/toSuccess', '更新goodsbill还款状态失败','repay_id',$repay_id,'bill_ids',$bill_ids,'res',$res);
            return false;
        }
        return true;
    }
    /**
     * 支付失败
     *
     * @param [type] $repay_id
     * @return void
     */
    public function toFail($repay_id){
        if(empty($repay_id)){
            Logger::dayLog('stageservice/toFail', 'repay_id参数缺失');
            return false;
        }
        $billRepayModel = new BillRepay();
        $oBillRepays = $billRepayModel->getStatusRepaybill($repay_id,[BillRepay::STATUS_REPAY,BillRepay::STATUS_REPAYING]);
        if(empty($oBillRepays)){
            Logger::dayLog('stageservice/toFail', '查询不到还款信息','repay_id',$repay_id);
            return false;
        }
        $ids = ArrayHelper::getColumn($oBillRepays,'id');
        $res = $billRepayModel->toFail($ids);
        if(!$res){
            Logger::dayLog('stageservice/toFail', '更新billrepay还款状态失败','repay_id',$repay_id,'ids',$ids,'res',$res);
            return false;
        }
        return true;
    }

    /**
     * 获取利息金额
     * @param $amount
     * @param $term
     * @param $interest
     * @return string
     */
    public function getInterestFee($amount, $term, $interest){
        //公式：(总期数+1)*总金额*0.00098*30/2
        $periods = bcadd($term,1,0);
        $interest_fee = bcdiv(bcmul(bcmul(bcmul($periods,$amount,4),$interest,4),30,4),2,4);
        return $interest_fee;
    }

    /**
     * 还款计划
     * @param $o_user
     * @param $o_user_credit
     * @param $amount
     * @param $days
     * @param $term
     * @param $coupon_id
     * @param $is_installment
     * @return array
     */
    public function getReayPlan($o_user,$o_user_credit,$amount,$days,$term,$coupon_id,$is_installment){
        $interest = !empty($o_user_credit->interest_rate)?bcdiv($o_user_credit->interest_rate,100,6) : 0.00098;
        $loanfee = (new User_loan())->loan_Fee_rate_new($amount, $interest, $days, $o_user->user_id, $term, $loan_type = 2, $is_installment);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        $charge = (new User_label())->isChargeUser($o_user->mobile);
        if (!$is_installment) {
            if ($charge == false) {
                if ($coupon_id) {//使用优惠卷
                    $couponModel = (new Coupon_list())->getCouponById($coupon_id);
                    $coupon_amount = $couponModel['val'];
                    if ($coupon_amount == 0) {//全免卷
                        $repay_amount = $amount;
                    } else {
                        $jianmian = $interest_fee - $coupon_amount > 0 ? $coupon_amount : $interest_fee;
                        $repay_amount = $amount + $interest_fee - $jianmian;
                    }
                } else {
                    $repay_amount = $amount + $interest_fee;
                }
            } else {
                if ($coupon_id) {
                    $couponModel = (new Coupon_list())->getCouponById($coupon_id);
                    $coupon_amount = $couponModel['val'];
                    if ($coupon_amount == 0) {
                        $repay_amount = $amount + $withdraw_fee;
                    } else {
                        $jianmian = $interest_fee - $coupon_amount > 0 ? $coupon_amount : $interest_fee;
                        $repay_amount = $amount + $withdraw_fee + $interest_fee - $jianmian;
                    }
                } else {
                    $repay_amount = $amount + $interest_fee + $withdraw_fee;
                }
            }
            $repayPlan[] = [
                'term' => $term,
                'show_text' => '应还金额',
                'first_show_date' => date('m月d日', strtotime('+' . $days . ' days', time())),
                'repay_amount' => sprintf('%.2f', $repay_amount),
                'repay_date' => date('Y/m/d', strtotime('+' . $days . ' days', time())),
            ];
            return $repayPlan;
        } else {
            if ($charge == false) {
                $repay_amount = $amount + $interest_fee;
                $additional = $interest_fee;
            } else {
                $repay_amount = $amount + $interest_fee + $withdraw_fee;
                $additional = $interest_fee + $withdraw_fee;
            }
            $repayPlan = [];
            $amount_total = 0;
            for ($i = 0; $i < $term; $i++) {
                $day = $days * ($i + 1);
                if ($i == 0) {
                    $amount_total = $amount_total + sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100) + sprintf('%.2f', $additional);
                    $first_amount = sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100) + sprintf('%.2f', $additional);
                    $repayPlan[$i] = [
                        'term' => $i + 1,
                        'first_show_date' => date('m月d日', strtotime('+' . $day . ' days', time())),
                        'show_text' => '首期应还',
                        'repay_amount' => sprintf('%.2f', $first_amount),
                        'repay_date' => date('Y/m/d', strtotime('+' . $day . ' days', time()))
                    ];
                } elseif ($i == $term - 1) {
                    $repayPlan[$i] = [
                        'term' => $i + 1,
                        'show_text' => '第' . $this->numberChange($i + 1) . '期应还',
                        'repay_amount' => sprintf('%.2f', (sprintf('%.3f', round($repay_amount * 100) / 100) - $amount_total)),
                        'repay_date' => date('Y/m/d', strtotime('+' . $day . ' days', time()))
                    ];
                } else {
                    $amount_total = $amount_total + sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100);
                    $repayPlan[$i] = [
                        'term' => $i + 1,
                        'show_text' => '第' . $this->numberChange($i + 1) . '期应还',
                        'repay_amount' => sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100),
                        'repay_date' => date('Y/m/d', strtotime('+' . $day . ' days', time()))
                    ];
                }
            }
            return $repayPlan;
        }
    }

    /**
     * 获取监管进场时，综合利息
     * @param $amount
     * @param $days
     * @param $term
     * @param $interest_fee
     * @param $coupon_amount
     * @param $repay_plan
     * @param $is_installment
     * @return int|string
     */
    public function getSuperviseInterest($amount, $days, $term, $interest_fee, $coupon_amount, $repay_plan, $is_installment) {
        //监管进场时，拆分利息为综合费用+综合利息
        $surplus_fee = 0;
        if (Keywords::inspectOpen() == 2) {
            if ($is_installment) {
                $amount = bcdiv($amount, $term, 4);
            }
            $str = bcadd($amount, $interest_fee, 4);//6000+176.4
            $jianmian = sprintf('%.2f', ceil($interest_fee * 100) / 100) * 2 - $coupon_amount > 0 ? $coupon_amount : sprintf('%.2f', ceil($interest_fee * 100) / 100) * 2;
            $surplus_fee = bcsub(bcadd($repay_plan[0]['repay_amount'], $jianmian, 4), $str, 2);
        }
        return $surplus_fee;
    }

    /**
     * 组装分期账单表批量添加数据
     * @param $oUserLoan
     * @return array
     */
    private function getGoodsBillArray($oUserLoan)
    {
        if (!is_object($oUserLoan) || empty($oUserLoan)) {
            return [];
        }
        $array = [];
        $time = date('Y-m-d H:i:s');
        $oUserCredit = $oUserLoan->usercredit;
        if (empty($oUserCredit)){
            Logger::dayLog('stageservice','查询不到关联user_credit信息',$oUserLoan->loan_id);
            return false;
        }
        $days = bcdiv($oUserLoan->days, $oUserCredit->period, 0);
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 00:00:00', strtotime($start_time . "+" . $days . " days"));
        $principal_total = $oUserLoan->getPrincipal($oUserLoan->is_calculation, $oUserLoan->amount, $oUserLoan->withdraw_fee);
        $principal = ceil((bcdiv($principal_total, $oUserCredit->period, 3)) * 100) / 100;
        for ($i = 1; $i <= $oUserCredit->period; $i++) {
            if ($i == $oUserCredit->period) {
                $principal = bcsub($principal_total, bcmul($principal, ($i - 1), 2), 2);
            }
            $interest = 0;
            $principal_period = $principal;
            if($i == 1){
                $interest = $oUserLoan->interest_fee;
                $principal_period = bcadd($principal,$interest,2);
            }
            $array[] = [
                'bill_id'       => 'W' . date('Ymdhis') . $oUserLoan->loan_id . $i,
                'goods_id'      => 0,
                'loan_id'       => $oUserLoan->loan_id,
                'user_id'       => $oUserLoan->user_id,
                'fee_type'      => 1,//计息方式 1等额本金
                'type'          => 1,//分期类型 1借款分期2商品分期
                'phase'         => $i,
                'fee'           => $oUserCredit->interest_rate,//日利率
                'number'        => $oUserCredit->period,
                'goods_amount'  => $principal_total,//总金额
                'current_amount'=> $principal_period,//当期应还款金额
                'actual_amount' => $principal_period,//总应还款金额（逾期可变）
                'repay_amount'  => 0,
                'principal'     => $principal,//应还本金
                'interest'      => $interest,
                'start_time'    => $start_time,
                'end_time'      => $end_time,
                'days'          => $days,
                'bill_status'   => GoodsBill::STATUS_NORMAL,
                'repay_time'    => '0000:00:00 00:00:00',
                'create_time'   => $time,
                'last_modify_time'=> $time,
                'version'       => 0,
            ];
            $start_time = $end_time;
            $end_time = date('Y-m-d 00:00:00', strtotime($start_time . "+" . ($days) . " days"));
        }
        return $array;
    }
    private function getRepayBillData($oUserLoan,$bill_id){
        if (empty($oUserLoan)||empty($bill_id)){
            return false;
        }
        $data = [
            'repay_id'      => 0,
            'bank_id'       => 0,
            'user_id'       => $oUserLoan->user_id,
            'loan_id'       => $oUserLoan->loan_id,
            'bill_id'       => $bill_id,
            'status'        => BillRepay::STATUS_INIT,
            'actual_money'  => 0,
            'paybill'       => '',
            'platform'      => 0,
            'source'        => 0,//@todo Something
            'createtime'    => date('Y-m-d H:i:s'),
            'last_modify_time'  => date('Y-m-d H:i:s'),
            'version'       => 0,
            'repay_time'    => '0000:00:00 00:00:00',
        ];
        return $data;
    }
    private function numberChange($number) {
        $arr = [
            1 => '一',
            2 => '二',
            3 => '三',
            4 => '四',
            5 => '五',
            6 => '六',
            7 => '七',
            8 => '八',
            9 => '九',
            10 => '十',
            11 => '十一',
            12 => '十二',
        ];
        if (isset($arr[$number])) {
            return $arr[$number];
        }
        return FALSE;
    }
}
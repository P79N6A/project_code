<?php

namespace app\models\service;

use app\commonapi\Logger;
use app\models\news\Goods;
use app\models\news\GoodsBill;
use app\models\news\GoodsOrder;
use app\models\news\Loan_rate;
use app\models\news\User_loan;
use Yii;

/**
 * 1.获取用户商品数组
 * 2.生成订单id
 * 3.组装分期账单表批量添加数据
 * 4.新增分期账单表记录
 */
class GoodsService extends Service
{
    /**
     * 1.获取用户商品数组
     * @param $amount
     * @param $limit
     * @param $order
     * @return array|\yii\db\ActiveRecord[]\
     */
    public function getGoodsList($amount, $limit = '5', $order = 'price asc')
    {
        if (!intval($amount) && intval($amount) != 0) {
            return [];
        }
        $where = [
            'AND',
            ['>', 'price', $amount],
            ['>', 'stock', 0],
        ];
        $list = Goods::find()->where($where)->orderBy($order)->limit($limit)->all();
        if (empty($list)) {
            return [];
        }
        $goodsList = [];
        foreach ($list as $k => $v) {
            $goodsList[$k]['goods_amount'] = $v['price'];
            $goodsList[$k]['goods_name'] = $v['goods_name'];
            $goodsList[$k]['goods_id'] = $v['goods_id'];
            $goodsList[$k]['goods_pic'] = Yii::$app->params['app_url'] . $v['image_path'];
        }
        return $goodsList;
    }

    /**
     * 2.生成订单id
     * 规则：XHHJF+年+借款ID后两位+用户身份证号第11位至第17位+随机两位数+时+随机两位数+分
     * @param $loanId   借款id
     * @param $identity 用户身份证
     * @return string
     */
    public function createOrderId($loanId, $identity)
    {
        if (strlen($identity) != 18) {
            return false;
        }
        return $orderId = 'XHHJF' . date('Y') . substr($loanId, -2) . substr($identity, 10, 7) . rand(10, 99) . date('H') . rand(10, 99) . date('i');
    }

    /**
     * 3.组装分期账单表批量添加数据
     * @param $goodsOrderObj
     * @return array
     */
    public function getGoodsBillArray($goodsOrderObj)
    {
        if (!is_object($goodsOrderObj) || empty($goodsOrderObj)) {
            return [];
        }
        $array = [];
        $time = date('Y-m-d H:i:s');
        $days = bcdiv($goodsOrderObj->loan->days, $goodsOrderObj->number, 0);
        $start_time = $goodsOrderObj->loan->start_date;
        $loanRateObj = (new Loan_rate())->getRateByLoanId($goodsOrderObj->loan_id);
        $interest_fee = !empty($loanRateObj) ? $loanRateObj->interest / 100 : 0.0005;
        $end_time = date('Y-m-d 00:00:00', strtotime($start_time . "+" . ($days + 1) . " days"));
        $principalTotal = (new User_loan())->getPrincipal($goodsOrderObj->loan->is_calculation, $goodsOrderObj->loan->amount, $goodsOrderObj->loan->withdraw_fee);
        $principal = ceil((bcdiv($principalTotal, $goodsOrderObj->number, 3)) * 100) / 100;
        $principal_bj = ceil((bcdiv($goodsOrderObj->loan->amount, $goodsOrderObj->number, 3)) * 100) / 100;
        for ($i = 1; $i <= $goodsOrderObj->number; $i++) {
            if ($i == $goodsOrderObj->number) {
                $principal = bcsub($principalTotal, bcmul($principal, ($i - 1), 2), 2);
                $principal_bj = bcsub($goodsOrderObj->loan->amount, bcmul($principal_bj, ($i - 1), 2), 2);
            }
//            $interest = round($principal * ($interest_fee * 365) * ($days * $i / 365) * 100) / 100;
            $interest = round($principal_bj * ($interest_fee * 365) * ($goodsOrderObj->loan->days / 365) * 100) / 100;//本金*0.36*（总天数/365）
            $current_amount = bcadd($principal, $interest, 2);
            $array[] = [
                0 => 'W' . date('Ymdhis') . $goodsOrderObj->id . $i,//bill_id
                1 => $goodsOrderObj->order_id,//order_id
                2 => $goodsOrderObj->goods_id,//goods_id
                3 => $goodsOrderObj->loan_id,//loan_id
                4 => $goodsOrderObj->user_id,//user_id
                5 => $i,//phase
                6 => $goodsOrderObj->fee,//fee
                7 => $goodsOrderObj->number,//number
                8 => $goodsOrderObj->order_amount,//goods_amount
                9 => $current_amount,//current_amount
                10 => 0,//actual_amount
                11 => 0,//repay_amount
                12 => $principal,//principal
                13 => 0,//over_principal
                14 => $interest,//interest
                15 => 0,//over_interest
                16 => 0,//over_late_fee
                17 => $start_time,//start_time
                18 => $end_time,//end_time
                19 => $days,//days
                20 => 9,//bill_status
                21 => 'INIT',//remit_status
                22 => $time,//create_time
                23 => $time//last_modify_time
            ];
            $start_time = $end_time;
            $end_time = date('Y-m-d 00:00:00', strtotime($start_time . "+" . ($days) . " days"));
        }
        return $array;
    }

    /**
     * 4.新增分期账单表记录
     * @param $goodsOrderObj
     * @return bool
     */
    public function addGoodsBill($goodsOrderObj, $transaction = true)
    {
        $array = $this->getGoodsBillArray($goodsOrderObj);
        if (empty($array)) {
            return false;
        }
        $goodsBillModel = new GoodsBill();
        if($transaction){
            $transaction = Yii::$app->db->beginTransaction();
        }
        $num = $goodsBillModel->batchAddGoodsBill($array);
        if ($num != $goodsOrderObj->number) {
            if($transaction){
                $transaction->rollBack();
            }
            Logger::dayLog('installment/dogoodsbill', '添加goods_bill分期账单表失败', $goodsOrderObj->id, $array);
            return false;
        }
        if($transaction){
            $transaction->commit();
        }
        $successInfo = $goodsOrderObj->updateSuccess();
        if (!$successInfo) {
            Logger::dayLog('installment/dogoodsbill', '更新goods_order表success状态失败', $goodsOrderObj->id);
            return false;
        }
        return true;
    }

    /**
     * 计算分期利息
     * @param $amount   借款金额
     * @param $days 分期天数
     * @param $term 分期数
     * @return bool|float|int
     */
    public function getInstallmentInterestFee($amount, $days, $term, $interest)
    {
        if ($term == 1) {
            return false;
        }
        $principal = ceil((bcdiv($amount, $term, 3)) * 100) / 100;//分期本金
        $interest_fee = 0;
        for ($i = 1; $i <= $term; $i++) {
            if ($i == $term) {
                $principal = bcsub($amount, bcmul($principal, ($i - 1), 2), 2);//分期最后一期本金
            }
//            $interest_fee += round($principal * ($interest * 365) * ($days / $term * $i / 365) * 100) / 100;
            $interest_fee += round($principal * ($interest * 365) * ($days / 365) * 100) / 100;//本金*0.36*（总天数/365）
        }
        return $interest_fee;
    }

    /**
     * 新增分期订单记录
     * @param $userLoanObj
     * @param $term
     * @param $fee
     * @return GoodsOrder|array|bool
     */
    public function addGoodsOrder($userLoanObj, $term, $fee, $goodsId = 0)
    {
        if (empty($userLoanObj) || !is_object($userLoanObj)) {
            return false;
        }
        $goodsOrderModel = new GoodsOrder();
        $order_id = $this->createOrderId($userLoanObj->loan_id, $userLoanObj->user->identity);
        if (!$order_id) {
            return ['rsp_code' => '10201'];
        }
        $order_amount = $userLoanObj->getOrderAmount($userLoanObj->is_calculation, $userLoanObj->amount, $userLoanObj->withdraw_fee, $userLoanObj->interest_fee);
        if (empty($goodsId)) {
            $goodsObj = (new Goods())->getGoodsByMaxPrice();
            if (!empty($goodsObj)) {
                $goodsId = $goodsObj->goods_id;
            }
        }
        if (empty($goodsId)) {
            return ['rsp_code' => '10204'];
        }
        $goodsOrder = [
            'order_id' => $order_id,
            'goods_id' => $goodsId,
            'loan_id' => $userLoanObj->loan_id,
            'user_id' => $userLoanObj->user_id,
            'number' => $term,
            'fee' => $fee,
            'order_amount' => $order_amount,
        ];
        $goodsOrderInfo = $goodsOrderModel->addGoodsOrder($goodsOrder);
        if (!$goodsOrderInfo) {
            return ['rsp_code' => '10202'];
        }
        return $goodsOrderModel;
    }
}
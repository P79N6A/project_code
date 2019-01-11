<?php
namespace app\commands;

use app\common\Common;
use app\models\SettleBill;
use yii\helpers\ArrayHelper;
/**
 *  汇总清结算结算表单中多条数据结果
 */
class SettlebillController extends BaseController
{
    public function runRepay()
    {
        $oSettleBill = new SettleBill();
        //计算数条数
        $total = $oSettleBill->getGroupBillCount();
        $limit = 100;
        $pages = ceil($total / $limit);
        for ($i=0; $i<$pages; $i++) {
            $data = $oSettleBill->getGroupBillData($limit);
            //锁定
            $loan_ids = Common::ArrayToString($data, 'loan_id');
            $ids_total = $oSettleBill->lockLoan($loan_ids);
            if ($ids_total == 0){
                return false;
            }
            //处理数据
            if ($data){
                foreach($data as $v){
                    $run_remit_state = $this->runRemit(ArrayHelper::getValue($v, 'loan_id', 0));
                    if ($run_remit_state){
                        $v->completeRemitStatus(ArrayHelper::getValue($v, 'loan_id', 0));
                    }
                }
            }
        }
    }

    private function runRemit($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }

        $oSettleBill = new SettleBill();
        //获取数据
        $bill_info = $oSettleBill->getInfo($loan_id);
        $save_data = $this->formatData($bill_info);
        if (empty($save_data)){
            return false;
        }
        //判断是否存在
        $more_info = $oSettleBill->getLoanStatusInfo($loan_id);
        //通过新插入的req_id判断是否存在
        if (empty($more_info)) {
            $more_info = $oSettleBill->getLoanReqId(ArrayHelper::getValue($save_data, 'req_id', 0));
        }
        //记录数据
        if (empty($more_info)) {
            $ret = $oSettleBill->createData($save_data);
        }else{
            $ret = $more_info->updateData($save_data);
        }
        if ($ret){
            return true;
        }
        return false;

    }

    /**
     * 格式数据
     * @param $bill_info
     * @return array|bool
     */
    private function formatData($bill_info)
    {
        if (empty($bill_info)){
            return false;
        }
        $loan_id = ArrayHelper::getValue($bill_info, 'loan_id', '');
        if (empty($loan_id)){
            return false;
        }
        $oSettleBill = new SettleBill();
        //chase_amount相加
        $chase_amount = $oSettleBill->getChaseAmountSum($loan_id);
        //repay_money 相加
        $repay_money = $oSettleBill->getRepayMoneySum($loan_id);
        //status计算
        $status_total = $oSettleBill->getStatusCount($loan_id);
        //interest_fee计算
        $interest_fee = $oSettleBill->getInterestFeeSum($loan_id);
        //repay_actual_money计算
        $repay_actual_money = $oSettleBill->getRepayActualMoneySum($loan_id);
        //getLateFeeSum
        $late_fee = $oSettleBill->getLateFeeSum($loan_id);

        $data_set = [
            'req_id'                => empty($bill_info['req_id']) ? '' : $bill_info['req_id']."_", //商户订单号
            'remit_channel_id'      => ArrayHelper::getValue($bill_info, 'remit_channel_id', ''), //出款通道id
            'remit_channel'         => ArrayHelper::getValue($bill_info, 'remit_channel', ''), //出款通道名称
            'pay_channel_id'        => ArrayHelper::getValue($bill_info, 'pay_channel_id', ''), //还款通道id
            'pay_channel'           => ArrayHelper::getValue($bill_info, 'pay_channel', ''), //还款通道名称
            'remit_type'            => ArrayHelper::getValue($bill_info, 'remit_type', ''), //业务类型
            'loan_id'               => ArrayHelper::getValue($bill_info, 'loan_id', ''), //借款id
            'user_id'               => ArrayHelper::getValue($bill_info, 'user_id', ''), //用户id
            'loan_time'             => ArrayHelper::getValue($bill_info, 'loan_time', ''), //借款时间
            'loan_days'             => ArrayHelper::getValue($bill_info, 'loan_days', ''), //借款周期
            'loan_money'            => ArrayHelper::getValue($bill_info, 'loan_money', ''), //借款本金
            'withdraw_fee'          => ArrayHelper::getValue($bill_info, 'withdraw_fee', ''), //前置服务费
            'interest_fee'          => $interest_fee, //利息
            'fund'                  => ArrayHelper::getValue($bill_info, 'fund', ''), //资金方
            'end_date'              => ArrayHelper::getValue($bill_info, 'end_date', ''), //到期时间
            'repay_status'          => ArrayHelper::getValue($bill_info, 'repay_status', ''), //还款状态
            'all_money'             => ArrayHelper::getValue($bill_info, 'all_money', ''), //需还款总额
            'chase_amount'          => $chase_amount, //滞纳金收益
            'free_amount'           => ArrayHelper::getValue($bill_info, 'free_amount', ''), //免息券
            'settle_status'         => ArrayHelper::getValue($bill_info, 'settle_status', ''), //结算状态
            'is_yq'                 => ArrayHelper::getValue($bill_info, 'is_yq', ''), //是否逾期
            'repay_time'            => ArrayHelper::getValue($bill_info, 'repay_time', ''), //还款时间
            'yq_days'               => ArrayHelper::getValue($bill_info, 'yq_days', ''), //逾期天数
            'repay_money'           => (float)$repay_money, //回款金额
            'repay_actual_money'    => (float)$repay_actual_money, //回款本金
            'late_fee'              => (float)$late_fee,
            'is_badloan'            => ArrayHelper::getValue($bill_info, 'is_badloan', ''), //是否坏账
            'badloan_money'         => ArrayHelper::getValue($bill_info, 'badloan_money', ''), //坏账金额
            'badloan_actualmoney'   => ArrayHelper::getValue($bill_info, 'badloan_actualmoney', ''), //坏账本金
            'badloan_fee'           => ArrayHelper::getValue($bill_info, 'badloan_fee', ''), //坏账利息
            'badloan_back'          => ArrayHelper::getValue($bill_info, 'badloan_back', ''), //坏账收回
            //'create_time'           => date("Y-m-d H:i:s", time()), //创建时间
            //'update_time'           => date("Y-m-d H:i:s", time()), //更新时间
            'status'                => empty($status_total) ? 2 : $status_total, //状态
            'remit_status'          => 1,
            'like_amount'           => ArrayHelper::getValue($bill_info, 'like_amount', ''),
        ];
        return $data_set;
    }

}

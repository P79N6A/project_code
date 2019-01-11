<?php

namespace app\models\ygy;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "yi_user_loan".
 * 一亿元借款记录表
 * @property string $loan_id
 * @property string $parent_loan_id
 * @property integer $number
 * @property integer $settle_type
 * @property string $user_id
 * @property string $loan_no
 * @property string $real_amount
 * @property string $amount
 * @property string $recharge_amount
 * @property string $credit_amount
 * @property string $current_amount
 * @property integer $days
 * @property string $start_date
 * @property string $end_date
 * @property string $open_start_date
 * @property string $open_end_date
 * @property integer $type
 * @property integer $status
 * @property integer $prome_status
 * @property string $interest_fee
 * @property string $desc
 * @property string $contract
 * @property string $contract_url
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 * @property string $repay_time
 * @property string $withdraw_fee
 * @property string $chase_amount
 * @property string $like_amount
 * @property string $collection_amount
 * @property string $coupon_amount
 * @property integer $is_push
 * @property integer $final_score
 * @property integer $repay_type
 * @property integer $business_type
 * @property string $withdraw_time
 * @property string $bank_id
 * @property integer $source
 * @property integer $is_calculation
 */
class YgyUserLoan extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_loan_id', 'number', 'settle_type', 'user_id', 'days', 'type', 'status', 'prome_status', 'version', 'is_push', 'final_score', 'repay_type', 'business_type', 'bank_id', 'source', 'is_calculation'], 'integer'],
            [['user_id', 'amount', 'current_amount', 'bank_id'], 'required'],
            [['real_amount', 'amount', 'recharge_amount', 'credit_amount', 'current_amount', 'interest_fee', 'withdraw_fee', 'chase_amount', 'like_amount', 'collection_amount', 'coupon_amount'], 'number'],
            [['start_date', 'end_date', 'open_start_date', 'open_end_date', 'last_modify_time', 'create_time', 'repay_time', 'withdraw_time'], 'safe'],
            [['loan_no', 'contract'], 'string', 'max' => 64],
            [['desc'], 'string', 'max' => 1024],
            [['contract_url'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'loan_id' => '主键',
            'parent_loan_id' => '主借款ID',
            'number' => '续期次数',
            'settle_type' => '0：初始状态；1：还款结清；2：续期结清；3：续期中',
            'user_id' => '用户ID',
            'loan_no' => '借款编号',
            'real_amount' => '初始借款金额',
            'amount' => '借款金额',
            'recharge_amount' => '值充金额',
            'credit_amount' => '授信额度',
            'current_amount' => '已募集到金额',
            'days' => '借款天数',
            'start_date' => '起息日',
            'end_date' => '到期日',
            'open_start_date' => '募集开始日期',
            'open_end_date' => '募集结束日期',
            'type' => '借款类型:1先花宝；2普通用户',
            'status' => '借款状态：1初始；2通过；3驳回；4失效；5已提现；',
            'prome_status' => '模型初始状态',
            'interest_fee' => '借款利息总额',
            'desc' => '借款说明',
            'contract' => '合同号',
            'contract_url' => '合同存放地址',
            'last_modify_time' => '最后修改时间，对应状态变更时间',
            'create_time' => '创建时间',
            'version' => '乐观所版本号',
            'repay_time' => 'Repay Time',
            'withdraw_fee' => '提现手续费',
            'chase_amount' => '逾期费用',
            'like_amount' => '赞点减息总额',
            'collection_amount' => 'Collection Amount',
            'coupon_amount' => '优惠券金额',
            'is_push' => '是否推送未筹满通知',
            'final_score' => '同盾风险系数',
            'repay_type' => '1:线下;2线上,默认为1',
            'business_type' => '1:好友;2:好人卡;3:担保人',
            'withdraw_time' => '提现时间',
            'bank_id' => '提现银行卡ID',
            'source' => '借款来源',
            'is_calculation' => '1 新的计费方式 0 不变',
        ];
    }
    //未结清的账单
    public function getUncleared($user_id)
    {
        if (empty($user_id)){
            return 0;
        }
        $where = [
            'and',
            ["=", "user_id", $user_id],
            ["in", "status", [6, 9, 11, 12, 13]],
            ["in", "business_type", [1,4]],
        ];
        return $this->find()->where($where)->count();

    }
}

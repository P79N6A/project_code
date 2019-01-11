<?php

namespace app\models\loan;

use Yii;

/**
 * This is the model class for table "loan_repay_success".
 *
 * @property string $id
 * @property string $order_id
 * @property string $bank_id
 * @property string $user_id
 * @property string $loan_id
 * @property string $money
 * @property string $amount
 * @property integer $pay_channel
 * @property string $paybill
 * @property string $modify_time
 * @property integer $come_from
 * @property integer $from_code
 * @property string $create_time
 * @property integer $platform
 * @property string $repay_time
 * @property string $pic_repay1
 * @property string $pic_repay2
 * @property string $pic_repay3
 * @property integer $repay_type
 * @property string $repay_mark
 */
class SfLoanRepaySuccess extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'loan_repay_success';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_id', 'user_id', 'loan_id', 'pay_channel', 'come_from', 'from_code', 'platform', 'repay_type'], 'integer'],
            [['money', 'amount'], 'number'],
            [['modify_time', 'create_time', 'repay_time'], 'safe'],
            [['order_id', 'paybill'], 'string', 'max' => 64],
            [['pic_repay1', 'pic_repay2', 'pic_repay3', 'repay_mark'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => '还款订单号',
            'bank_id' => '还款银行卡ID',
            'user_id' => '用户ID',
            'loan_id' => '借款ID',
            'money' => '还款金额',
            'amount' => '实际还款金额',
            'pay_channel' => '正常： 101  易宝投资通   102  易宝一键支付  112  融宝  114  宝付   逾期：   102   易宝   128 融宝  123  宝付',
            'paybill' => '支付流水号',
            'modify_time' => '最后修改时间',
            'come_from' => '设备来源:1 ios 2 android 3 微信 4 代扣',
            'from_code' => '渠道来源:1 自有2一亿元  3  其他 ',
            'create_time' => '创建时间',
            'platform' => '支付平台(预留)',
            'repay_time' => '还款到账时间',
            'pic_repay1' => '还款凭证URL',
            'pic_repay2' => 'Pic Repay2',
            'pic_repay3' => 'Pic Repay3',
            'repay_type' => '还款类型 1线上还款  2 线下还款',
            'repay_mark' => '后台操作人员备注',
        ];
    }

    //  获取已还款次数
    public function getRepaycnt($loan_id)
    {
        $where = ['loan_id' => $loan_id];
        $repay_cnt = $this->find()->where($where)->count();
        return (int)$repay_cnt;
    }
}

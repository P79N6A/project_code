<?php

namespace app\models\antifraud;

use Yii;

/**
 * This is the model class for table "af_addr_loan".
 *
 * @property string $id
 * @property string $request_id
 * @property integer $aid
 * @property string $user_id
 * @property integer $user_total
 * @property integer $loan_all
 * @property integer $overdue_norepay
 * @property integer $overdue_repay
 * @property integer $overdue7_norepay
 * @property integer $overdue7_repay
 * @property integer $loan_total
 * @property string $last_loan_day
 * @property integer $normal_repay
 * @property integer $realadl_tot_reject_num
 * @property integer $realadl_tot_freject_num
 * @property integer $realadl_tot_sreject_num
 * @property integer $realadl_tot_dlq14_num
 * @property integer $realadl_dlq14_ratio
 * @property integer $history_bad_status
 * @property string $create_time
 */
class AfAddrLoan extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_addr_loan';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_antifraud');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'aid', 'user_id', 'user_total', 'loan_all', 'overdue_norepay', 'overdue_repay', 'overdue7_norepay', 'overdue7_repay', 'loan_total', 'last_loan_day', 'normal_repay', 'create_time'], 'required'],
            [['request_id', 'aid', 'user_id', 'user_total', 'loan_all', 'overdue_norepay', 'overdue_repay', 'overdue7_norepay', 'overdue7_repay', 'loan_total', 'normal_repay', 'realadl_tot_reject_num', 'realadl_tot_freject_num', 'realadl_tot_sreject_num', 'realadl_tot_dlq14_num', 'realadl_dlq14_ratio', 'history_bad_status'], 'integer'],
            [['last_loan_day', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'request_id' => '请求id',
            'aid' => '业务id',
            'user_id' => '用户ID',
            'user_total' => '通讯录是平台用户数量',
            'loan_all' => '通讯录与loan表总数(含申请)',
            'overdue_norepay' => '通讯录逾期未还款数量',
            'overdue_repay' => '通讯录逾期已还款数量',
            'overdue7_norepay' => '通讯录逾期7天未还款数量',
            'overdue7_repay' => '通讯录逾期7天已还款数量',
            'loan_total' => '通讯录通讯录有过放款数量',
            'last_loan_day' => '通讯录最近一次申请借款天数',
            'normal_repay' => '通讯录借款提前/正常还款',
            'realadl_tot_reject_num' => 'Realadl Tot Reject Num',
            'realadl_tot_freject_num' => 'Realadl Tot Freject Num',
            'realadl_tot_sreject_num' => 'Realadl Tot Sreject Num',
            'realadl_tot_dlq14_num' => 'Realadl Tot Dlq14 Num',
            'realadl_dlq14_ratio' => 'Realadl Dlq14 Ratio',
            'history_bad_status' => '通讯录中有过申请且历史最坏账单状态',
            'create_time' => '创建时间',
        ];
    }
}

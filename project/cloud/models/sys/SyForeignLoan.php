<?php

namespace app\models\sys;

use Yii;

/**
 * This is the model class for table "foreign_loan".
 *
 * @property integer $id
 * @property string $loan_id
 * @property string $batch_num
 * @property string $amount
 * @property string $interest
 * @property string $entrust_amount
 * @property string $end_date
 * @property string $contract
 * @property string $loan_money
 * @property string $rate
 * @property string $loan_time
 * @property integer $loan_term
 * @property integer $level
 * @property string $username
 * @property string $mobile
 * @property string $identity
 * @property integer $product_source
 * @property integer $status
 * @property string $create_time
 * @property string $manager_id
 * @property string $operator_id
 * @property string $up_down_relation
 * @property integer $operator_status
 * @property integer $status_type
 * @property string $assign_time
 * @property string $last_modify_time
 * @property integer $company_source
 * @property string $repay_time
 * @property string $settle_point
 */
class SyForeignLoan extends SyBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'foreign_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_num', 'end_date', 'loan_time', 'create_time', 'assign_time', 'last_modify_time', 'repay_time'], 'safe'],
            [['amount', 'interest', 'entrust_amount', 'loan_money', 'rate', 'settle_point'], 'number'],
            [['loan_term', 'level', 'product_source', 'status', 'manager_id', 'operator_id', 'operator_status', 'status_type', 'company_source'], 'integer'],
            [['loan_id', 'identity'], 'string', 'max' => 20],
            [['contract', 'up_down_relation'], 'string', 'max' => 64],
            [['username'], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 18]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => '借款唯一标识(预留)',
            'batch_num' => '批次号',
            'amount' => '逾期本金',
            'interest' => '逾期利息',
            'entrust_amount' => '委托金额',
            'end_date' => '逾期起始日期',
            'contract' => '借款合同号',
            'loan_money' => '贷款金额',
            'rate' => '贷款利率',
            'loan_time' => '贷款日期',
            'loan_term' => '贷款期限（月)',
            'level' => '账龄',
            'username' => '姓名',
            'mobile' => '手机号',
            'identity' => '身份证',
            'product_source' => '借款产品来源',
            'status' => '账单状态  8：结清 12：逾期 ',
            'create_time' => '创建时间',
            'manager_id' => '组织架构ID',
            'operator_id' => '催收员工ID',
            'up_down_relation' => '上下级关系',
            'operator_status' => '1:跟进中 2：已退案 3:停催 4：已结清 5:待分单 6:过往案件7：错误案件',
            'status_type' => '状态类型，1-内催退案待分配，2、委外退案待分配',
            'assign_time' => '分配时间',
            'last_modify_time' => '最后更新时间',
            'company_source' => '所属公司',
            'repay_time' => '还款时间',
            'settle_point' => '结算点',
        ];
    }

    public function getForeignLoan($where,$select = '*')
    {
        return $this->find()->where($where)->select($select)->asArray()->all();
    }

    public function getMaxId()
    {
        $res = static::find()->max('id');
        return $res;
    }
}

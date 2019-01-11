<?php

namespace app\models\cloud;

use Yii;

/**
 * This is the model class for table "dc_loan".
 *
 * @property string $id
 * @property string $basic_id
 * @property integer $aid
 * @property string $identity_id
 * @property string $loan_id
 * @property string $amount
 * @property integer $loan_days
 * @property string $cardno
 * @property string $reason
 * @property string $loan_time
 * @property string $create_time
 */
class DcLoan extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basic_id', 'aid', 'loan_days'], 'integer'],
            [['identity_id', 'loan_id', 'cardno', 'reason', 'loan_time', 'create_time'], 'required'],
            [['amount'], 'number'],
            [['loan_time', 'create_time'], 'safe'],
            [['identity_id', 'cardno'], 'string', 'max' => 50],
            [['loan_id'], 'string', 'max' => 100],
            [['reason'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'basic_id' => '请求表id',
            'aid' => '应用aid',
            'identity_id' => '用户唯一标识',
            'loan_id' => '业务借款id',
            'amount' => '借款金额',
            'loan_days' => '借款期限(天)',
            'cardno' => '银行卡号',
            'reason' => '借款原因',
            'loan_time' => '借款时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 获取高频借款
     * @param  str $identity_id 
     * @param  int $aid         
     * @param  str $start_time  
     * @return  num
     */
    public function  getMultiLoan($identity_id, $aid = 0, $start_time = null){
        $where = [
            'AND', 
            ['identity_id' => (string)$identity_id],
        ];
        if ($aid == 1 || $aid == 14) {
            $where[] = ['aid' => [1,14,17]];
        }
        if (!empty($start_time)) {
           $where[] = ['>','create_time', $start_time];
        }
        $count = static::find() -> where($where) -> count();
        //减去自身
        return $count - 1;
    }
}

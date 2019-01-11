<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "yi_loan_event".
 * 一亿元借款决策表
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $loan_id
 * @property string $loan_no
 * @property integer $old_status
 * @property integer $new_status
 * @property integer $loan_time_start
 * @property integer $loan_time_end
 * @property integer $age_value
 * @property integer $more_loan_value
 * @property integer $one_more_loan_value
 * @property integer $seven_more_loan_value
 * @property integer $one_number_account_value
 * @property integer $is_black
 * @property string $last_modify_time
 * @property string $create_time
 */
class LoanEvent extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_event';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_no', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'type', 'loan_id', 'old_status', 'new_status', 'loan_time_start', 'loan_time_end', 'age_value', 'more_loan_value', 'one_more_loan_value', 'seven_more_loan_value', 'one_number_account_value', 'is_black'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键，自增长',
            'user_id' => '用户ID',
            'type' => '是否是复贷1:否;2:是',
            'loan_id' => '借款ID,预留',
            'loan_no' => '借款编号,loan_no',
            'old_status' => '驳回之前状态,预留',
            'new_status' => '现在状态，预留',
            'loan_time_start' => '借款时间限制起始时间',
            'loan_time_end' => '借款时间限制截至时间',
            'age_value' => '年龄',
            'more_loan_value' => '多头借贷',
            'one_more_loan_value' => '1天内申请借款次数',
            'seven_more_loan_value' => '7天内申请借款次数',
            'one_number_account_value' => '单一设备当月申请借款账户数',
            'is_black' => '黑名单限制 0:不是黑名单；1:黑名单',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
        ];
    }

    public function getLoanEventInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}

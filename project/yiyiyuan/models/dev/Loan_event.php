<?php

namespace app\models\dev;

use app\commonapi\Logger;

/**
 * This is the model class for table "yi_loan_event".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $loan_no
 * @property integer $old_status
 * @property integer $new_status
 * @property integer $loan_time_value
 * @property integer $age_value
 * @property integer $more_loan_value
 * @property integer $one_more_loan_value
 * @property integer $seven_more_loan_value
 * @property integer $one_number_account_value
 * @property integer $is_black
 * @property string $last_modify_time
 * @property string $create_time
 */
class Loan_event extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_event';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
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
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'loan_id' => 'Loan ID',
            'loan_no' => 'Loan No',
            'old_status' => 'Old Status',
            'new_status' => 'New Status',
            'loan_time_start' => 'Loan Time Start',
            'loan_time_end' => 'Loan Time End',
            'age_value' => 'Age Value',
            'more_loan_value' => 'More Loan Value',
            'one_more_loan_value' => 'One More Loan Value',
            'seven_more_loan_value' => 'Seven More Loan Value',
            'one_number_account_value' => 'One Number Account Value',
            'is_black' => 'Is Black',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 添加一条纪录
     */
    public static function addRecord($user_id, $condition) {
        $o = new self;
        // 数据
        $create_time = date('Y-m-d H:i:s');
        $data = [
            'user_id' => $user_id,
            'loan_no' => $condition['loan_no'],
            'type' => isset($condition['type']) ? $condition['type'] : 1,
//            'old_status' => $condition,
//            'new_status' => 1,
            'loan_time_start' => isset($condition['loan_time_start']) ? $condition['loan_time_start'] : null,
            'loan_time_end' => isset($condition['loan_time_end']) ? $condition['loan_time_end'] : null,
            'age_value' => isset($condition['age_value']) ? $condition['age_value'] : null,
            'more_loan_value' => isset($condition['more_loan_value']) ? $condition['more_loan_value'] : null,
            'one_more_loan_value' => isset($condition['one_more_loan_value']) ? $condition['one_more_loan_value'] : null,
            'seven_more_loan_value' => isset($condition['seven_more_loan_value']) ? $condition['seven_more_loan_value'] : null,
            'one_number_account_value' => isset($condition['one_number_account_value']) ? $condition['one_number_account_value'] : null,
            'is_black' => isset($condition['is_black']) ? $condition['is_black'] : 0,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }

}

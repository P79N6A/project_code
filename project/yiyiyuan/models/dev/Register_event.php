<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_register_event".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $old_status
 * @property integer $new_status
 * @property integer $age_value
 * @property string $area_value
 * @property integer $number_value
 * @property integer $ip_value
 * @property integer $is_black
 * @property string $last_modify_time
 * @property string $create_time
 */
class Register_event extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_register_event';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'old_status', 'new_status', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'old_status', 'new_status', 'age_value', 'number_value', 'ip_value', 'is_black'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['area_value'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'old_status' => 'Old Status',
            'new_status' => 'New Status',
            'age_value' => 'Age Value',
            'area_value' => 'Area Value',
            'number_value' => 'Number Value',
            'ip_value' => 'Ip Value',
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
            'old_status' => $condition['old_status'],
            'new_status' => $condition['new_status'],
            'age_value' => isset($condition['age_value']) ? $condition['age_value'] : null,
            'area_value' => isset($condition['area_value']) ? $condition['area_value'] : null,
            'number_value' => isset($condition['number_value']) ? $condition['number_value'] : null,
            'ip_value' => isset($condition['ip_value']) ? $condition['ip_value'] : null,
            'is_black' => isset($condition['is_black']) ? $condition['is_black'] : 0,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }

}

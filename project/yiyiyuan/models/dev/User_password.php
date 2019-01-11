<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class User_password extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_password';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    /*     * 根据userid查询用户的密码
     *  
     */

    public function getUserPassword($user_id) {
        if (empty($user_id)) {
            return null;
        }
        $userpassword = User_password::find()->where(['user_id' => $user_id])->one();
        return $userpassword;
    }

    /**
     * 设置密码
     */
    public function addPassword($user_id, $login_password) {
        $now_time = date('Y-m-d H:i:s');
        $password = new User_password();
        $password->user_id = $user_id;
        $password->login_password = $login_password;
        $password->create_time = $now_time;
        $password->last_modify_time = $now_time;
        $password->version = 1;

        if ($password->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改密码
     */
    public function updatePassword($user_id, $login_password = '', $pay_password = '', $device_tokens = '', $device_type = '') {
        $now_time = date('Y-m-d H:i:s');
        $password = User_password::find()->where(['user_id' => $user_id])->one();
        if (!empty($login_password)) {
            $password->login_password = $login_password;
        }
        if (!empty($pay_password)) {
            $password->pay_password = $pay_password;
        }
        if (!empty($device_tokens)) {
            $password->device_tokens = $device_tokens;
        }
        if (!empty($device_type)) {
            $password->device_type = $device_type;
        }
        $password->last_modify_time = $now_time;
        $password->version = $password->version + 1;

        if ($password->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改记录
     */
    public function updateUserPassword($condition) {
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = $now_time;
        $this->version = $this->version + 1;
        if ($this->save()) {
            return true;
        } else {
            return false;
        }
    }

}

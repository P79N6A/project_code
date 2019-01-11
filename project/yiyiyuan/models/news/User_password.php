<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_user_password".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $user_id
 * @property string $login_password
 * @property string $pay_password
 * @property string $device_tokens
 * @property string $device_type
 * @property string $iden_address
 * @property string $nation
 * @property string $pic_url
 * @property string $iden_url
 * @property double $score
 * @property string $create_time
 * @property string $last_modify_time
 * @property string $version
 */
class User_password extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_password';
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'create_time', 'last_modify_time', 'version'], 'required'],
            [['user_id', 'version'], 'integer'],
            [['score'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['login_password', 'pay_password', 'device_tokens', 'iden_address', 'pic_url', 'iden_url'], 'string', 'max' => 64],
            [['device_type'], 'string', 'max' => 10],
            [['nation'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'login_password' => 'Login Password',
            'pay_password' => 'Pay Password',
            'device_tokens' => 'Device Tokens',
            'device_type' => 'Device Type',
            'iden_address' => 'Iden Address',
            'nation' => 'Nation',
            'pic_url' => 'Pic Url',
            'iden_url' => 'Iden Url',
            'score' => 'Score',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    /**
     * 根据userid查询用户的密码
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
        //$password->version = 1;

        if ($password->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 设置密码
     * @param $condition
     * @return bool
     */
    public function save_password($condition) {
        if (!is_array($condition) || empty($condition) || empty($condition['user_id'])) {
            return false;
        }
        $pass_info = self::find()->where(['user_id' => $condition['user_id']])->one();
        if ($pass_info) {
            return $pass_info->update_password($condition);
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新密码
     * @param $condition
     * @return bool
     */
    public function update_password($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            return $result;
        } catch (\Exception $ex) {
            return FALSE;
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
        //$password->version = $password->version + 1;

        if ($password->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改记录
     * @param  $condition array  array('field'=>fieldval);
     */
    public function updateUserPassword($condition) {
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = $now_time;
        if ($this->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     *  判断是否存在，不存在就新增，存在就修改
     *  @param $condition   array array('field'=>fieldval);
     */
    public function addUserpassword($condition) {
        if (empty($condition) || !isset($condition['user_id'])) {
            return false;
        }
        $user_password = User_password::getUserPassword($condition['user_id']);
        if (!empty($user_password)) {
            $result = $user_password->updateUserPassword($condition);
        } else {
            $passwordModel = new User_password();
            foreach ($condition as $key => $val) {
                $passwordModel->{$key} = $val;
            }
            $passwordModel->version = 1;
            $passwordModel->last_modify_time = date('Y-m-d H:i:s');
            $passwordModel->create_time = date('Y-m-d H:i:s');
            $result = $passwordModel->save();
        }
        return $result;
    }

}

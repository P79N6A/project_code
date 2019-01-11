<?php

namespace app\models\own;

use app\commonapi\Logger;
use Exception;

/**
 * This is the model class for table "reverse_address_list".
 *
 * @property string $id
 * @property integer $aid
 * @property string $user_id
 * @property string $user_phone
 * @property string $phone
 * @property string $name
 * @property string $modify_time
 * @property string $create_time
 */
class Reverse_address_list extends OwnNewBaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'reverse_address_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'phone', 'modify_time', 'create_time'], 'required'],
            [['aid', 'user_id'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['user_phone', 'phone'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'user_id' => 'User ID',
            'user_phone' => 'User Phone',
            'phone' => 'Phone',
            'name' => 'Name',
            'modify_time' => 'Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function findOneByuserIdMobile($user_id, $mobile) {
        if (!is_numeric($user_id) || empty($mobile) || empty($user_id)) {
            return NULL;
        }
        $oReverse = self::find()->where(['phone' => $mobile, 'user_id' => $user_id])->one();
        return $oReverse;
    }

    /**
     * 更新通讯录姓名
     * @param type $name
     * @return boolean
     */
    public function updateName($name) {
        if (empty($name)) {
            return FALSE;
        }
        $this->name = $name;
        $this->modify_time = date('Y-m-d H:i:s');
        try {
            return $this->save();
        } catch (Exception $ex) {
            Logger::errorLog('address_list', 'update', $ex);
            return FALSE;
        }
    }

    /**
     * 添加记录
     * @param type $name
     * @return boolean
     */
    public function saveRecord($user_id, $user_name, $phone, $name) {
        if (empty($name) || empty($user_id) || !is_numeric($user_id) || empty($phone) || empty($user_name)) {
            return FALSE;
        }
        $o = new self;
        $data = [
            'aid' => 1,
            'user_id' => $user_id,
            'user_phone' => $user_name,
            'phone' => $phone,
            'name' => $name,
            'create_time' => date('Y-m-d H:i:s'),
            'modify_time' => date('Y-m-d H:i:s'),
        ];
        $error = $o->chkAttributes($data);
        if ($error) {
            Logger::errorLog('address_list', 'insert', $error);
            return FALSE;
        }
        return $o->save();
    }

}

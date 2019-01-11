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
class Juxinli extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_juxinli';
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

    public function addList($condition) {
        if (!empty($condition['user_id'])) {
            $type = isset($condition['type']) ? $condition['type'] : 1;
            $juxinli = (new Juxinli())->getJuxinliByUserId($condition['user_id'], $type);
            if (!empty($juxinli)) {
                $result = $juxinli->updateJulixin($condition);
                return $result;
            }
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $time = date('Y-m-d H:i:s');
        $this->last_modify_time = $time;
        $this->create_time = $time;
        $result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    public function updateJulixin($condition) {
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $time = date('Y-m-d H:i:s');
        $this->last_modify_time = $time;
        $result = $this->save();
        return $result;
    }

    public function getJuxinliByUserId($user_id, $type = 1) {
        if (empty($user_id)) {
            return false;
        }
        $result = Juxinli::find()->where(['user_id' => $user_id, 'type' => $type])->orderBy('create_time desc')->one();
        return $result;
    }

}

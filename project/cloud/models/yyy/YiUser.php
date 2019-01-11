<?php

namespace app\models\yyy;

/**
 * This is the model class for table "yi_user".
 */
class YiUser extends YyyBaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user';
    }

    public function getByUserId($user_id, $fields) {
        $data = static::find()
            ->select($fields)
            ->asArray()
            ->where(['user_id' => $user_id])
            ->limit(1)->one();
        return $data;
    }

    public function getMaxId()
    {
        $res = static::find()
               ->select('user_id')
               ->orderBy('user_id DESC')
               ->limit(1)
               ->one();
        $max_id = isset($res['user_id']) ? $res['user_id'] : 0;
        return $max_id;
    }
    

    public function getListByUserId($where, $fields) {
        $data = static::find()
            ->select($fields)
            ->asArray()
            ->where($where)
            ->limit(1000)->all();
        return $data;
    }


    public function getUser($where) {
        return $this->find()->where($where)->limit(1)->one();
    }

    public function getUserExtend() {
        return $this->hasOne(YiUserExtend::className(), ['user_id' => 'user_id']);
    }

}

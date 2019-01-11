<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "user_id_temp". 
 * 
 * @property integer $user_id
 * @property integer $status
 */
class User_id_temp extends \app\models\BaseModel {

    /**
     * @inheritdoc 
     */
    public static function tableName() {
        return 'user_id_temp';
    }

    /**
     * @inheritdoc 
     */
    public function rules() {
        return [
            [['user_id', 'status'], 'required'],
            [['user_id', 'status'], 'integer']
        ];
    }

    /**
     * @inheritdoc 
     */
    public function attributeLabels() {
        return [
            'id' => 'Id',
            'user_id' => 'User ID',
            'status' => 'Status',
        ];
    }

    public function setAllStatus($userIds, $stauts) {
        return self::updateAll(['status' => $stauts], ['user_id' => $userIds]);
    }

    public function setStauts($userId, $status) {
        $user = self::find()->where(['user_id' => $userId])->one();
        $user->status = $status;
        return $user->save();        
    }

}

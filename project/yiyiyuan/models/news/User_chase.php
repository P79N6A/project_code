<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_chase".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $status
 * @property string $create_time
 */
class User_chase extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_chase';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'bank_id', 'status'], 'integer'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }

    public function saveSucc() {
        try {
            $this->status = 2;
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    public function saveFail() {
        try {
            $this->status = 3;
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

}

<?php

namespace app\models\dev;

use app\commonapi\Common;
use app\models\xs\XsApi;
use Yii;

class Noah_test_5k extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'noah_test_5k';
    }

    public function rules() {
        return [
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

}

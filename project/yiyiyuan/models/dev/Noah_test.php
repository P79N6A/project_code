<?php

namespace app\models\dev;

use app\commonapi\Common;
use app\models\xs\XsApi;
use Yii;

class Noah_test extends \yii\db\ActiveRecord  {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'noah_test';
    }

    public function rules() {
        return [
        ];
    }

  

}

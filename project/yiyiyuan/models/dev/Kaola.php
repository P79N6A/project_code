<?php

namespace app\models\dev;

use Yii;


class Kaola extends \yii\db\ActiveRecord{
	/**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_kaola';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
        ];
    }
}
?>
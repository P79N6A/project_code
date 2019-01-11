<?php

namespace app\models;

use Yii;
class Qrcode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_qrcode';
    }
    public function rules()
    {
    	return [
					 [['title'], 'required']
    			];
    }
}

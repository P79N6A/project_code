<?php

namespace app\models;

use Yii;
class Collection extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_collection';
    }
    public function rules()
    {
    	return [
				
    			];
    }
}

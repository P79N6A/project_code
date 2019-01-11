<?php

namespace app\models\dev;

use Yii;
use app\models\own\OwnBaseModel;
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
class Statistics extends OwnBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_statistics';
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
    
    public function getStype()
    {
    	return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
}

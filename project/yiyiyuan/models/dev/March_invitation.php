<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string user_id
 * @property string $mobile
 * @property string $create_time
 */
class March_invitation extends \yii\db\ActiveRecord
{
    
    public static function tableName()
    {
        return 'yi_march_invitation';
    }
    
}

<?php

namespace app\models\dev;

use Yii;

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
class Standard_coupon_apply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_standard_coupon_apply';
    }

//     /**
//      * @inheritdoc
//      */
//     public function rules()
//     {
//         return [
            
//         ];
//     }

//     /**
//      * @inheritdoc
//      */
//     public function attributeLabels()
//     {
//         return [
//             'id' => 'ID',
            
//         ];
//     }
   
    
    
    
}

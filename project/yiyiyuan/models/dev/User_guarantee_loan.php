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
class User_guarantee_loan extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_guarantee_loan';
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

    public function getGuater() {
        return $this->hasOne(User::className(), ['user_id' => 'user_guarantee_id']);
    }

	public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

	public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

}

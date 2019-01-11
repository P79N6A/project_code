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
class Standard_coupon_use extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_standard_coupon_use';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public static function addStandardCouponUser($user,$coupon_id,$standard_id) {
        $standard_coupon_use = new Standard_coupon_use();
        $standard_coupon_use->user_id = $user->user_id;
        $standard_coupon_use->discount_id = $coupon_id;
        $standard_coupon_use->standard_id = $standard_id;
        $standard_coupon_use->create_time = date('Y-m-d H:i:s');
        $standard_coupon_use->version = 1;
        $result = $standard_coupon_use->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }

}

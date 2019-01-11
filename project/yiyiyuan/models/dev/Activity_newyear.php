<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "activity_newyear".
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
class Activity_newyear extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_activity_newyear';
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

    public function addNewyearinfo($user_id, $field = "", $type = 1,$num = 1) {
        if (empty($user_id) || !isset($user_id)) {
            return false;
        }
        $new_year = new Activity_newyear();
        $new_year->user_id = $user_id;
        if ($field == "invite_num") {
            $new_year->invite_num = $num;
        } else if($field == "friend_loan_num"){
            $new_year->friend_loan_num = 1;
        }
        $new_year->type = $type;
        $new_year->create_time = date('Y-m-d H:i:s');
        $new_year->last_mofify_time = date('Y-m-d H:i:s');

        if ($new_year->save()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateNum($field, $type = 1,$num = 1) {
        if (empty($field) || !isset($field)) {
            return false;
        }
        $this->last_mofify_time = date('Y-m-d H:i:s');
        if ($field == "invite_num") {
            $this->invite_num = $this->invite_num + $num;
        }elseif ($field == "coupon_num") {
            $this->coupon_num = $this->coupon_num + $num;
        } else {
            $this->friend_loan_num = $this->friend_loan_num + 1;
        }
        $result = $this->save();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}

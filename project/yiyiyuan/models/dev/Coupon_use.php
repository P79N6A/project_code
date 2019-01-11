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
class Coupon_use extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_coupon_use';
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

    //借款被驳回的时候添加一条优惠券使用记录，但是不更改优惠券的状态
    public function addRejectCouponUse($user, $coupon_id, $loan) {
        $time = date('Y-m-d H:i:s');
        $couponUseModel = new Coupon_use();
        $couponUseModel->user_id = $user->user_id;
        $couponUseModel->discount_id = $coupon_id;
        $couponUseModel->loan_id = $loan->loan_id;
        $couponUseModel->create_time = $time;
        $couponUseModel->version = 1;
        $ret_coupon_use = $couponUseModel->save();
        if ($ret_coupon_use) {
            return true;
        } else {
            return false;
        }
    }
    
    public function addCouponUse($user, $coupon_id, $loan) {
        $time = date('Y-m-d H:i:s');
        $couponUseModel = new Coupon_use();
        $couponUseModel->user_id = $user->user_id;
        $couponUseModel->discount_id = $coupon_id;
        $couponUseModel->loan_id = $loan->loan_id;
        $couponUseModel->create_time = $time;
        $couponUseModel->version = 1;
        $ret_coupon_use = $couponUseModel->save();
        if ($ret_coupon_use) {
            $coupon = Coupon_list::findOne($coupon_id);
            $list_result = $coupon->updateCouponList(array('status' => 2, 'use_time' => $time));
            if ($list_result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

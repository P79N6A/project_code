<?php

namespace app\modules\balance\models\yyy;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_coupon_use".
 *
 * @property string $id
 * @property string $user_id
 * @property string $discount_id
 * @property string $loan_id
 * @property string $create_time
 * @property integer $version
 */
class Coupon_use extends YyyBase {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_coupon_use';
    }

    public function getCouponList() {
        return $this->hasOne(Coupon_list::className(), ['id' => 'discount_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'discount_id', 'loan_id', 'version'], 'integer'],
            [['create_time'], 'safe'],
            [['version'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'discount_id' => 'Discount ID',
            'loan_id' => 'Loan ID',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     *
     * @param type $user
     * @param type $coupon_id
     * @param type $loanid
     * @param type $status 1:正常使用优惠券  2：借款驳回的时候，只添加使用记录，不更改优惠券状态
     * @return boolean
     */
    public function addCouponUse($user, $coupon_id, $loanid, $status = 1) {
        $time = date('Y-m-d H:i:s');

        $data['user_id'] = $user->user_id;
        $data['discount_id'] = $coupon_id;
        $data['loan_id'] = $loanid;
        $data['create_time'] = $time;
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        if (!$this->save()) {
            return false;
        }
        if ($status == 1) {
            $coupon = Coupon_list::findOne($coupon_id);
            $list_result = $coupon->update_couponlist(array('status' => 2, 'use_time' => $time));
            return $list_result;
        } else {
            return true;
        }
    }

}

<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_repay_coupon_use".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $discount_id
 * @property string $repay_id
 * @property string $repay_amount
 * @property integer $repay_status
 * @property string $coupon_amount
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class RepayCouponUse extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_repay_coupon_use';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'discount_id', 'repay_id', 'repay_status', 'coupon_amount', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'discount_id', 'repay_id', 'repay_status', 'version'], 'integer'],
            [['repay_amount', 'coupon_amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'discount_id' => 'Discount ID',
            'repay_id' => 'Repay ID',
            'repay_amount' => 'Repay Amount',
            'repay_status' => 'Repay Status',
            'coupon_amount' => 'Coupon Amount',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $date = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $date;
        $data['create_time'] = $date;
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        //更新优惠卷状态
        if (isset($condition['repay_status']) && in_array($condition['repay_status'], [-1, 4])) {
            $coupon = Coupon_list::findOne($this->discount_id);
            if (!empty($coupon)) {
                if ($condition['repay_status'] == -1) {
                    $coupon->_saveCouplist(['use_time' => date('Y-m-d H:i:s')]);
                }
                if ($condition['repay_status'] == 4) {
                    $coupon->updateUseFail();
                }
            }
        }
        return $this->save();
    }

    /**
     * 还款优惠卷更新使用成功，优惠卷更新已使用
     * @return bool
     * @author 王新龙
     * @date 2018/7/25 11:33
     */
    public function updateSuccess() {
        $time = date('Y-m-d H:i:s');
        $data = [
            'repay_status' => 1,
            'last_modify_time' => $time

        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        //更新优惠卷状态
        $coupon = Coupon_list::findOne($this->discount_id);
        if (!empty($coupon)) {
            $coupon->_saveCouplist(['use_time' => $time]);
        }
        return $this->save();
    }

    public function getByLoanId($loanId) {
        if (empty($loanId)) {
            return null;
        }
        return self::find()->where(['loan_id' => $loanId, 'repay_status' => [-1, 1]])->one();
    }

    public function getByDiscountId($discountId) {
        if (empty($discountId)) {
            return null;
        }
        return self::find()->where(['discount_id' => $discountId, 'repay_status' => [-1, 1]])->one();
    }

    public function getByRepayId($repayId) {
        if (empty($repayId)) {
            return null;
        }
        return self::find()->where(['repay_id' => $repayId])->one();
    }
}

<?php

namespace app\modules\balance\models\yx;

use Yii;

/**
 * This is the model class for table "yx_user".
 *
 * @property integer $loan_id
 * @property integer $parent_loan_id
 * @property integer $number
 * @property integer $settle_type
 * @property integer $user_id
 * @property string $loan_no
 * @property string $real_amount
 * @property string $amount
 * @property string $recharge_amount
 * @property string $credit_amount
 */
class CouponList extends \app\modules\balance\models\yx\YxBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_coupon_list';
    }

    public function getOrder() {
        return $this->hasOne(OrderPay::className(), ['user_id' => 'user_id']);
    }


}
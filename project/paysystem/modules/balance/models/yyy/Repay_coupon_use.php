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
class Repay_coupon_use extends YyyBase {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_repay_coupon_use';
    }

//    public function getCouponList() {
//        return $this->hasOne(Coupon_list::className(), ['id' => 'discount_id']);
//    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'discount_id', 'loan_id', 'version','repay_id','repay_amount','repay_status','coupon_amount'], 'integer'],
            [['create_time','last_modify_time'], 'safe'],
            [['version'], 'required']
        ];
    }



    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

}

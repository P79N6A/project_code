<?php

namespace app\modules\balance\models\yyy;

use Yii;

class PaymentRecord extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_renewal_payment_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'parent_loan_id', 'new_loan_id', 'user_id', 'status','version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['money','actual_money'], 'number'],
            [['paybill','order_id'], 'string', 'max' => 64]
        ];
    }


}
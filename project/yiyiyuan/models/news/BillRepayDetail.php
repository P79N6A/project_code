<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_bill_repay_detail".
 *
 * @property string $id
 * @property string $bill_repay_id
 * @property string $repay_id
 * @property string $loan_id
 * @property string $bill_id
 * @property string $principal
 * @property string $interest
 * @property string $late_fee
 * @property string $create_time
 * @property string $last_modify_time
 */
class BillRepayDetail extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_bill_repay_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['bill_repay_id', 'loan_id', 'bill_id'], 'integer'],
            [['principal', 'interest', 'late_fee'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['repay_id'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'bill_repay_id'    => 'Bill Repay ID',
            'repay_id'         => 'Repay ID',
            'loan_id'          => 'Loan ID',
            'bill_id'          => 'Bill ID',
            'principal'        => 'Principal',
            'interest'         => 'Interest',
            'late_fee'         => 'Late Fee',
            'create_time'      => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
        ];
    }

    public function saveDetail($data) {
        if (empty($data)) {
            return false;
        }
        $data['create_time']      = $data['last_modify_time'] = date("Y-m-d H:i:s");

        $error = $this->chkAttributes($data);
        if ($error) {
            return FALSE;
        }

        return $this->save();
    }

}

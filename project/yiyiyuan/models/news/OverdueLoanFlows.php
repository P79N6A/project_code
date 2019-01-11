<?php

namespace app\models\news;

use app\models\BaseModel;

/**
 * This is the model class for table "yi_overdue_loan_flows".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $user_id
 * @property string $bill_id
 * @property integer $business_type
 * @property string $late_fee
 * @property string $chase_amount
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class OverdueLoanFlows extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_overdue_loan_flows';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['loan_id', 'user_id', 'business_type', 'version'], 'integer'],
                [['late_fee', 'chase_amount'], 'number'],
                [['create_time', 'last_modify_time', 'bill_id'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'loan_id'          => 'Loan ID',
            'user_id'          => 'User ID',
            'bill_id'          => 'Bill ID',
            'business_type'    => 'Business Type',
            'late_fee'         => 'Late Fee',
            'chase_amount'     => 'Chase Amount',
            'create_time'      => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version'          => 'Version',
        ];
    }

    /**
     * 将逾期信息添加到逾期流水表
     * @param type $loan        逾期信息
     * @param type $chase_amount 总逾期费用
     */
    public function addOverdueLoanData($loan, $chase_amount) {
        $addData                     = [];
        $addData['loan_id']          = $loan['loan_id'];
        $addData['user_id']          = $loan['user_id'];
        $addData['bill_id']          = isset($loan['bill_id']) ?  $loan['bill_id'] : '';
        $addData['business_type']    = $loan['business_type'];
        $addData['late_fee']         = $loan['late_fee'];
        $addData['chase_amount']     = $chase_amount;
        $addData['create_time']      = date('Y-m-d H:i:s');
        $addData['last_modify_time'] = date('Y-m-d H:i:s');
        $addData['version']          = $loan['version'];
        $error                       = $this->chkAttributes($addData);
        if ($error) {
            return false;
        }
        $res = $this->save();
        return $res;
    }

}

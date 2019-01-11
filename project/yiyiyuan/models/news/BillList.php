<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_bill_list".
 *
 * @property string $id
 * @property string $status
 * @property string $admission_time
 * @property string $transaction
 * @property string $paybill
 * @property string $order_id
 * @property string $financial_type
 * @property string $income
 * @property string $expenditure
 * @property string $balance
 * @property string $service_rate
 * @property string $pay_channel
 * @property string $contract_products
 * @property string $other_account
 * @property string $other_name
 * @property string $bank_order_id
 * @property string $commodity_name
 * @property string $remarks
 * @property string $accounts
 * @property string $apply_id
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class BillList extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_bill_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['admission_time', 'transaction', 'paybill', 'last_modify_time', 'create_time'], 'required'],
            [['admission_time', 'last_modify_time', 'create_time'], 'safe'],
            [['income', 'expenditure', 'balance', 'service_rate'], 'number'],
            [['apply_id', 'version'], 'integer'],
            [['transaction', 'paybill', 'order_id', 'bank_order_id', 'commodity_name'], 'string', 'max' => 64],
            [['financial_type', 'pay_channel', 'contract_products', 'other_account', 'other_name', 'accounts'], 'string', 'max' => 32],
            [['status'], 'string', 'max' => 16],
            [['remarks'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'admission_time' => 'Admission Time',
            'transaction' => 'Transaction',
            'paybill' => 'Paybill',
            'order_id' => 'Order ID',
            'financial_type' => 'Financial Type',
            'income' => 'Income',
            'expenditure' => 'Expenditure',
            'balance' => 'Balance',
            'service_rate' => 'Service Rate',
            'pay_channel' => 'Pay Channel',
            'contract_products' => 'Contract Products',
            'other_account' => 'Other Account',
            'other_name' => 'Other Name',
            'bank_order_id' => 'Bank Order ID',
            'commodity_name' => 'Commodity Name',
            'remarks' => 'Remarks',
            'accounts' => 'Accounts',
            'apply_id' => 'Apply ID',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function getCheckbilllist() {
        return $this->hasOne(CheckBillList::className(), ['bill_id' => 'id']);
    }

    /**
     * 获取多条数据，根据transaction
     * @param $paybill  transaction数组
     * @return array|null|\yii\db\ActiveRecord[]
     * @author 王新龙
     * @date 2018/7/12 20:11
     */
    public function listByTransaction($transaction, $status = 'INIT') {
        if (empty($transaction) || !is_array($transaction)) {
            return null;
        }
        $where = [
            'AND',
            ['transaction' => $transaction],
        ];
        if(!empty($status)){
            $where[] = ['status' => $status];
        }
        return self::find()->where($where)->all();
    }

    /**
     * 获取最大入账时间
     * @return int
     * @author 王新龙
     * @date 2018/7/16 10:51
     */
    public function getMaxAdmissionTime() {
        return self::find()->max('admission_time');
    }


    /**
     * 修改状态为锁定
     * @return bool
     * @author 王新龙
     * @date 2018/7/16 11:50
     */
    public function updateLock() {
        try {
            $this->status = 'LOCK';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 修改状态为对账成功
     * @return bool
     * @author 王新龙
     * @date 2018/7/16 11:50
     */
    public function updateSuccess() {
        try {
            $this->status = 'SUCCESS';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 修改状态为初始化
     * @return bool
     * @author 王新龙
     * @date 2018/8/16 18:38
     */
    public function updateInit() {
        try {
            $this->status = 'INIT';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 获取记录，根据transaction
     * @param $transaction
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/17 12:12
     */
    public function getByTransaction($transaction){
        if(empty($transaction)){
            return null;
        }
        return self::find()->where(['transaction'=>$transaction])->one();
    }
}

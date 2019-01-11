<?php

namespace app\models\day;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_renewal_payment_record".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $order_id
 * @property string $parent_loan_id
 * @property string $user_id
 * @property string $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $money
 * @property string $actual_money
 * @property string $paybill
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 */
class Renewal_payment_record_guide extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_renewal_payment_record';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'order_id', 'parent_loan_id', 'user_id', 'platform', 'source', 'last_modify_time', 'create_time'], 'required'],
            [['loan_id', 'new_loan_id', 'parent_loan_id', 'user_id', 'bank_id', 'platform', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['order_id'], 'string', 'max' => 32],
            [['paybill'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'order_id' => 'Order ID',
            'parent_loan_id' => 'Parent Loan ID',
            'new_loan_id' => 'New Loan ID',
            'user_id' => 'User ID',
            'bank_id' => 'Bank ID',
            'platform' => 'Platform',
            'source' => 'Source',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'status' => 'Status',
            'last_modify_time' => 'Last Modify Time',
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

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getLoanparent() {
        return $this->hasOne(User_loan::className(), ['parent_loan_id' => 'parent_loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getLoan() {
        return $this->hasOne(User_loan_guide::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * 添加一条纪录（如果存在记录则更新记录）
     */
    public static function addBatch($loan, $order_id, $bank_id = '', $money, $platform, $source) {
        if (empty($loan)) {
            return FALSE;
        }
        // 数据
        $create_time = date('Y-m-d H:i:s');
        $o = new self;
        $data = [
            'loan_id' => $loan->loan_id,
            'order_id' => $order_id,
            'parent_loan_id' => $loan->parent_loan_id ? $loan->parent_loan_id : 0,
            'user_id' => $loan->user_id,
            'bank_id' => $bank_id,
            'platform' => $platform,
            'source' => $source,
            'money' => $money,
            'status' => 0,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        // 保存数据
        $errors = $o->chkAttributes($data); //attributes = $data;
        if ($errors) {
            return FALSE;
        }
        $result = $o->save();

        return $result;
    }

    /**
     * 获取进行中的续期还款
     * @param $loan_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/3 16:41
     */
    public function getCarriedRepay($loan_id) {
        if (empty($loan_id)) {
            return null;
        }
        return self::find()->where(['loan_id' => $loan_id, 'status' => '-1'])->orderBy('id desc')->one();
    }

    /**
     * 保存纪录，如果存在，更新，不存在，新增
     * @param $condition
     * @return bool|string
     */
    public function save_batch($condition) {
        if (!is_array($condition) || empty($condition) || !isset($condition['order_id'])) {
            return false;
        }
        $data = $condition;
        $batch = (new self())->find()->where(['order_id' => $data['order_id']])->one();

        if (!empty($batch)) {//更新
            return $batch->update_batch($condition);
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['status'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新
     * @param $condition
     * @return bool
     */
    public function update_batch($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 需求支付失败
     * @return boolean
     */
    public function saveFail() {
        if ($this->status == 1) {
            return FALSE;
        }
        $data['status'] = 4;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $errors = $this->chkAttributes($data);
        if ($errors) {
            return FALSE;
        }
        return $this->save();
    }

    /**
     * 需求支付成功
     * @return boolean
     */
    public function saveSuccess($platform, $actual_money, $paybill) {
        if ($this->status == 4) {
            return FALSE;
        }
        $data['status'] = 1;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['platform'] = $platform;
        $data['actual_money'] = $actual_money;
        $data['paybill'] = $paybill;
        $errors = $this->chkAttributes($data);
        if ($errors) {
            return FALSE;
        }
        return $this->save();
    }

    /**
     * 获取展期费用还款成功的
     * @param $startTime
     * @param $endTime
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRenewalByTime($startTime, $endTime) {
        $where = [
            'and',
            ['>=', 'last_modify_time', $startTime],
            ['<', 'last_modify_time', $endTime],
            ['=', 'status', 1],
        ];
        $loans = self::find()->where($where)->all();
        return $loans;
    }

}

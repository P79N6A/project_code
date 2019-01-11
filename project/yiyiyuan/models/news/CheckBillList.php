<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_check_bill_list".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $repay_id
 * @property string $bill_id
 * @property integer $status
 * @property string $amount
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class CheckBillList extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_check_bill_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'repay_id', 'last_modify_time', 'create_time'], 'required'],
            [['loan_id', 'repay_id', 'bill_id', 'status', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['amount'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'repay_id' => 'Repay ID',
            'bill_id' => 'Bill ID',
            'status' => 'Status',
            'amount' => 'Amount',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function getBilllist() {
        return $this->hasOne(BillList::className(), ['id' => 'bill_id']);
    }

    /**
     * 获取信息根据repay_id
     * @param $repay_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/7/16 14:50
     */
    public function getByRepayId($repay_id) {
        if (empty($repay_id) || !is_numeric($repay_id)) {
            return null;
        }
        return self::find()->where(['repay_id' => $repay_id])->one();
    }

    /**
     * 获取信息根据repay_id（关联bill_list表）
     * @param $repay_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/7/18 19:46
     */
    public function getBillByRepayId($repay_id) {
        if (empty($repay_id) || !is_numeric($repay_id)) {
            return null;
        }
        return self::find()->joinWith('billlist', false, 'LEFT JOIN')->where(['repay_id' => $repay_id])->one();
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/7/16 16:03
     */
    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 修改为对账失败
     * @return bool
     * @author 王新龙
     * @date 2018/8/17 9:02
     */
    public function updateFail(){
        try {
            $this->status = 2;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}

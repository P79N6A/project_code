<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_remit_success_list".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $user_id
 * @property string $bill_id
 * @property integer $business_type
 * @property integer $remit_type
 * @property string $order_id
 * @property string $settle_amount
 * @property string $result_status
 * @property string $secure_status
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class RemitSuccessList extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_remit_success_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'user_id', 'business_type', 'remit_type', 'settle_amount', 'result_status', 'secure_status', 'create_time', 'last_modify_time'], 'required'],
            [['loan_id', 'user_id', 'goods_order_id', 'business_type', 'remit_type', 'version'], 'integer'],
            [['settle_amount'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['result_status', 'secure_status'], 'string', 'max' => 16],
            [['order_id', 'insure_number'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'goods_order_id' => 'Goods order ID',
            'business_type' => 'Business Type',
            'remit_type' => 'Remit Type',
            'settle_amount' => 'Settle Amount',
            'result_status' => 'Result Status',
            'order_id' => 'Order Id',
            'secure_status' => 'Secure Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function getUserloan()
    {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getRemit()
    {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock()
    {
        return "version";
    }

    /**
     * 1.新增记录
     * @param $condition
     * @return bool
     */
    public function addRemitSuccessList($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['result_status'] = 'INIT';
        $data['secure_status'] = 'INIT';
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            if (!$result) {
                return false;
            }
            $orderid = 'R' . date('Ymdhis') . $this->id;
            $this->order_id = $orderid;
            $result = $this->save();
            if (!$result) {
                return false;
            }
            return $this->id;
        } catch (\Exception $ex) {
            return false;
        }
    }

    //保单推送批量锁定
    public function updateAllLock($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        return self::updateAll(['secure_status' => 'LOCK'], ['id' => $ids, 'secure_status' => 'INIT']);
    }

    //保单推送状态单条锁定
    public function updateLock()
    {
        try {
            $this->secure_status = 'LOCK';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //保单推送状态改为成功
    public function updateSuccess()
    {
        try {
            $this->secure_status = 'SUCCESS';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //保单推送状态改为失败
    public function updateFail()
    {
        try {
            $this->secure_status = 'FAIL';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 根据order_id查询记录
     * @param $orderId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getRecordByOrderId($orderId)
    {
        if (empty($orderId)) {
            return null;
        }
        return self::find()->where(['order_id' => $orderId])->one();
    }

    /**
     * 更新投保状态、投保号
     * @param $insureNumber 投保号
     * @param $resultStatus 投保状态
     * @return bool
     */
    public function updateResultStatus($insureNumber, $resultStatus)
    {
        $data['insure_number'] = $insureNumber;
        $data['result_status'] = $resultStatus;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * 获取保单号根据loan_id
     * @param $loanId
     * @return mixed|string
     */
    public function getInsureNumberByLoanId($loanId)
    {
        if (empty($loanId) || !is_numeric($loanId)) {
            return '';
        }
        $result = self::find()->where(['loan_id' => $loanId])->one();
        if (!$result) {
            return '';
        }
        return $result->insure_number;
    }
}

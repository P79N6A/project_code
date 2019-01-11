<?php

namespace app\models\news;

use app\models\service\UserloanService;
use Yii;

/**
 * This is the model class for table "yi_insurance".
 *
 * @property string $id
 * @property string $req_id
 * @property integer $type
 * @property integer $days
 * @property string $loan_id
 * @property integer $status
 * @property double $money
 * @property integer $is_chk
 * @property string $insurance_order
 * @property string $send_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Insurance extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_insurance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id', 'loan_id', 'user_id', 'is_chk', 'last_modify_time', 'create_time', 'version'], 'required'],
            [['loan_id', 'type', 'days', 'user_id', 'status', 'is_chk', 'version'], 'integer'],
            [['money'], 'number'],
            [['result_time', 'last_modify_time', 'create_time'], 'safe'],
            [['req_id', 'insurance_order'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'req_id' => 'Req ID',
            'loan_id' => 'Loan ID',
            'type' => 'Type',
            'days' => 'Days',
            'user_id' => 'User ID',
            'status' => 'Status',
            'money' => 'Money',
            'is_chk' => 'Is Chk',
            'insurance_order' => 'Insurance Order',
            'result_time' => 'Result Time',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock()
    {
        return "version";
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getInsure() {
        return $this->hasOne(Insure::className(), ['req_id' => 'req_id']);
    }

    public function saveData($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $condition = $data;
        $condition['status'] = 0;
        $condition['is_chk'] = 0;
        $condition['last_modify_time'] = $now;
        $condition['create_time'] = $now;
        $condition['version'] = 0;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        $res = $this->save();
        if (!$res) {
            return false;
        }
        $id = Yii::$app->db->getLastInsertID();
        return $id;
    }

    public function updateData($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        $condition = $data;
        $now = date('Y-m-d H:i:s');
        $condition['last_modify_time'] = $now;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }



    public function getRecordById($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }
        return self::findOne($id);
    }

    /**
     * 获取一条数据根据req_id
     * @param $reqId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getRecordByReqId($reqId)
    {
        if (empty($reqId)) {
            return null;
        }
        return self::find()->where(['req_id' => $reqId])->one();
    }

    /**
     * 更新保单号
     * @param $insuranceOrder
     * @param $resultStatus
     * @return bool
     */
    public function updateInsuranceOrder($insuranceOrder = '')
    {
        $time = date('Y-m-d H:i:s');
        if(!empty($insuranceOrder)){
            $data['insurance_order'] = $insuranceOrder;
        }
        $data['result_time'] = $time;
        $data['last_modify_time'] = $time;
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

    public function updateSuccess()
    {
        try {
            $this->status = 1;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function updateFail()
    {
        try {
            $this->status = 2;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function getDateByLoanId($loan_id){
        $loan_id = intval($loan_id);
        if(!$loan_id){
            return null;
        }
        return self::find()->where(['loan_id'=>$loan_id])->one();
    }

    /**
     * 新增核保记录
     * @param $userLoanExtendObj
     * @return string
     */
    public function addInsurance($oUserLoan) {
        if (empty($oUserLoan) || !is_object($oUserLoan)) {
            return false;
        }
        $rate = 0.18;
        $reqId = 'R' . date('Ymdhis') . $oUserLoan->loan_id; //请求序号
        $money = $oUserLoan->real_amount * $rate; //保费
        $condition = [
            'req_id' => $reqId,
            'loan_id' => $oUserLoan->loan_id,
            'user_id' => $oUserLoan->user_id,
            'money' => $money
        ];
        $insuranceId = $this->saveData($condition);
        if (empty($insuranceId)) {
            Logger::dayLog('insurance/insure', 'insurance记录失败', 'loan ID：' . $oUserLoan->loan_id);
            return false;
        }
        $insuranceObj =self::getRecordById($insuranceId);
        //失败

//        $failInfo = $insuranceObj->updateFail();
//        if (!$failInfo) {
//            Logger::dayLog('insurance/insure', 'insurance.fail状态更新失败', 'loan ID：' . $oUserLoan->loan_id);
//            return false;
//        }
//        $userloanServiceModel = new UserloanService();
//        $failResult = $userloanServiceModel->tbReject($oUserLoan->loan_id);
//        if (!$failResult) {
//            Logger::dayLog('insurance/insure', '借款fail状态更新失败', 'loan ID：' . $oUserLoan->loan_id);
//        }

        //成功
        $successInfo = $insuranceObj->updateSuccess();
        if (!$successInfo) {
            Logger::dayLog('insurance/insure', 'insurance.success状态更新失败', 'loan ID：' . $oUserLoan->loan_id);
            return false;
        }
    }
}

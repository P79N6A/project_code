<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_fraudmetrix_return_info".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $seq_id
 * @property string $success
 * @property string $reason_code
 * @property string $final_decision
 * @property integer $final_score
 * @property string $hit_rules
 * @property string $risk_type
 * @property string $device_info
 * @property string $geoip_info
 * @property string $policy_set_name
 * @property string $policy_set
 * @property string $attribution
 * @property string $create_time
 * @property string $rules
 */
class Fraudmetrix_return_info extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_fraudmetrix_return_info';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'final_score'], 'integer'],
            [['hit_rules', 'device_info', 'geoip_info', 'policy_set', 'attribution', 'rules'], 'string'],
            [['create_time'], 'safe'],
            [['loan_id', 'seq_id'], 'string', 'max' => 64],
            [['success'], 'string', 'max' => 10],
            [['reason_code', 'final_decision'], 'string', 'max' => 20],
            [['risk_type'], 'string', 'max' => 50],
            [['policy_set_name'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'seq_id' => 'Seq ID',
            'success' => 'Success',
            'reason_code' => 'Reason Code',
            'final_decision' => 'Final Decision',
            'final_score' => 'Final Score',
            'hit_rules' => 'Hit Rules',
            'risk_type' => 'Risk Type',
            'device_info' => 'Device Info',
            'geoip_info' => 'Geoip Info',
            'policy_set_name' => 'Policy Set Name',
            'policy_set' => 'Policy Set',
            'attribution' => 'Attribution',
            'create_time' => 'Create Time',
            'rules' => 'Rules',
        ];
    }

    public function updateRecord($condition) {
        if (empty($condition)) {
            return FALSE;
        }
        $this->attributes = $condition;
        return $this->save();
    }

    /**
     * 根据loan_no，更新为loan_id
     * @param type $loan_id
     * @param type $loan_no
     * @return boolean
     */
    public function setLoanId($loan_id, $loan_no) {
        if (empty($loan_id) || empty($loan_no)) {
            return FALSE;
        }
        $fraud = static::find()->where(['loan_id' => $loan_no])->one();
        if (!empty($fraud)) {
            return $fraud->updateRecord(['loan_id' => $loan_id]);
        }
        return FALSE;
    }
    public function savefinal_score($UserloanModel,$frModel){
        $fr_condition = [
            'loan_id' => (string)$UserloanModel->loan_id,
        ];
        $fr_ret = $frModel->update_record($fr_condition);
        if(!$fr_ret){
            return false;
        }

        $UserloanModel->final_score = $frModel->final_score;
        return $UserloanModel->save();
    }

    public function update_record($condition) {
        if(!is_array($condition) || empty($condition)){
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] =  date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 根据同盾返回信息存数据
     * @param type $Fraudetrixinfo
     * @param type $user_id
     * @param type $loan_id
     * @return Fraudmetrix_return_info
     */
    public static function CreateFraudmetrix($Fraudetrixinfo, $user_id, $loan_id = '') {
        $fraudmetrix = new Fraudmetrix_return_info();
        $fraudmetrix->user_id = $user_id;
        $fraudmetrix->loan_id = $loan_id;
        $fraudmetrix->seq_id = isset($Fraudetrixinfo->seq_id) ? $Fraudetrixinfo->seq_id : '';
        $fraudmetrix->success = isset($Fraudetrixinfo->success) ? $Fraudetrixinfo->success : '';
        $fraudmetrix->reason_code = isset($Fraudetrixinfo->reason_code) ? $Fraudetrixinfo->reason_code : '';
        $fraudmetrix->final_decision = isset($Fraudetrixinfo->final_decision) ? $Fraudetrixinfo->final_decision : '';
        $fraudmetrix->final_score = isset($Fraudetrixinfo->finalScore) ? $Fraudetrixinfo->finalScore : '';
        $fraudmetrix->hit_rules = ''; //isset($Fraudetrixinfo->hit_rules) ? serialize($Fraudetrixinfo->hit_rules) : '';
        $fraudmetrix->risk_type = isset($Fraudetrixinfo->risk_type) ? $Fraudetrixinfo->risk_type : '';
        $fraudmetrix->device_info = ''; //isset($Fraudetrixinfo->device_info) ? serialize($Fraudetrixinfo->device_info) : '';
        $fraudmetrix->geoip_info = ''; //isset($Fraudetrixinfo->geoip_info) ? serialize($Fraudetrixinfo->geoip_info) : '';
        $fraudmetrix->policy_set_name = isset($Fraudetrixinfo->policy_set_name) ? $Fraudetrixinfo->policy_set_name : '';
        $fraudmetrix->policy_set = ''; //isset($Fraudetrixinfo->policy_set) ? serialize($Fraudetrixinfo->policy_set) : '';
        $fraudmetrix->attribution = ''; //isset($Fraudetrixinfo->attribution) ? serialize($fraudmetrix->attribution) : '';
        $fraudmetrix->rules = ''; //isset($Fraudetrixinfo->rules) ? serialize($Fraudetrixinfo->rules) : '';
        $fraudmetrix->create_time = date('Y-m-d H:i:s');
        $fraudmetrix->save();
        return $fraudmetrix;
    }

    /**
     * 根据同盾返回信息存数据
     * @param $condition
     * @return Fraudmetrix_return_info
     */
    public function Create_Fraudmetrix($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition = [
            'hit_rules' => '',
            'device_info' => '',
            'geoip_info' => '',
            'policy_set' => '',
            'attribution' => '',
            'rules' => '',
            'create_time' => date('Y-m-d H:i:s'),
        ];
        $error = $this->chkAttributes($condition);
        if($error){
            return false;
        }
        return $this->save();
    }

}

<?php

namespace app\models\news;

use app\commonapi\Logger;
use app\models\BaseModel;
use Exception;

/**
 * This is the model class for table "yi_renew_record".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $loan_id_new
 * @property string $registration
 * @property string $authorize
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Renew_record extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_renew_record';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'loan_id_new'], 'required'],
            [['loan_id', 'loan_id_new', 'registration', 'authorize', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'loan_id_new' => 'Loan Id New',
            'registration' => 'Registration',
            'authorize' => 'Authorize',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }
    public function getUserloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id_new']);
    }
    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock() {
        return "version";
    }

    public function saveRecord($loan_id, $loan_id_new) {
        try {
            $this->loan_id = $loan_id;
            $this->loan_id_new = $loan_id_new;
            $this->create_time = date("Y-m-d H:i:s");
            $this->last_modify_time = date("Y-m-d H:i:s");
            $this->version = 0;
            return $this->save();
        } catch (Exception $ex) {
            Logger::dayLog('db', 'renew_record-inster', $ex->getMessage());
            return FALSE;
        }
    }

    public function getRecordByLoanId($loan_id) {
        $oRecord = self::find()->where(['loan_id' => $loan_id])->one();
        return $oRecord;
    }

    public function getRecordBynewId($loan_id) {
        $oRecord = self::find()->where(['loan_id_new' => $loan_id])->one();
        return $oRecord;
    }

    /**
     * 更新标的登记结果
     * @param type $status 6：成功  11：失败
     */
    public function updateRegistration($status) {
        try {
            $this->registration = $status;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            Logger::dayLog('db', 'renew_record-updateRegistration', $ex->getMessage());
            return FALSE;
        }
    }

    /**
     * 更新标的授权结果
     * @param type $status 1：授权中 6：成功  11：失败
     */
    public function updateAuthorize($status) {
        try {
            $this->authorize = $status;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            Logger::dayLog('db', 'renew_record-updateRegistration', $ex->getMessage());
            return FALSE;
        }
    }
}

<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_cm_loans".
 *
 * @property string $id
 * @property string $loan_id
 * @property integer $status
 * @property integer $type
 * @property integer $version
 * @property string $create_time
 * @property string $last_modify_time
 */
class Cm_loans extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_cm_loans';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'status', 'version', 'type'], 'integer'],
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
            'status' => 'Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'last_modify_time',
            'version' => 'version',
            'type' => 'type',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getUserloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getExchange() {
        return $this->hasOne(Exchange::className(), ['loan_id' => 'loan_id']);
    }

    public function saveSucc() {
        try {
            $this->status = 2;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    public function saveFail() {
        try {
            $this->status = 3;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 添加记录
     * @param $condition
     * @return bool
     */
    public function addCm($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 批量新增记录
     * @param $condition
     * @return bool
     */
    public function batchAddCmLoans($value) {
        if (empty($value) || !is_array($value)) {
            return 0;
        }
        $key = ['loan_id','status','create_time','type','version','last_modify_time'];
        try {
            $num = Yii::$app->db->createCommand()->batchInsert(Cm_loans::tableName(), $key, $value)->execute();
        } catch (Exception $e) {
            $num = 0;
        }
        return $num;
    }
}

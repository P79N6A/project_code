<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_loan_contract".
 *
 * @property integer $id
 * @property integer $loan_id
 * @property integer $fund
 * @property string $path
 * @property string $create_time
 * @property string $last_modify_time
 * @property string $status
 */
class Loan_contract extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_contract';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id'], 'required'],
            [['loan_id', 'fund'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['path'], 'string', 'max' => 225],
            [['status'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'fund' => 'Fund',
            'path' => 'Path',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'status' => 'status',
        ];
    }
    /**
     * 获取合同
     * @param  [status] $loan_id [description]
     * @return [type]          [description]
     */
    public function getByLoanId($loan_id) {
        $loan_id = intval($loan_id);
        if (!$loan_id) {
            return null;
        }
        return static::find()->where(['loan_id' => $loan_id])->one();
    }
    /**
     * 添加记录
     * @param $param
     * @return bool
     */
    public function saveData($param) {
        if (!is_array($param) || empty($param)) {
            return false;
        }
        $data = [
            'loan_id' => $param['loan_id'],
            'fund' => $param['fund'],
            'path' => $param['path'],
            'create_time' => date('Y-m-d H:i:s'),
            'last_modify_time' => date('Y-m-d H:i:s'),
            'status' => 'INIT',
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
    public function saveFail() {
        $this->status = 'FAIL';
        $this->last_modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    public function saveSuccess() {
        $this->status = 'SUCCESS';
        $this->last_modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * 修改
     * @param $condition
     * @return bool
     */
    public function updateData($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取指定资金方指定条数的合同数据
     * @param $fund
     * @param $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getInitByFund($fund, $limit) {
        return self::find()->where(['fund' => $fund, 'status' => 'INIT'])->limit($limit)->all();
    }

    public function lock() {
        try {
            $this->status = 'LOCK';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

}

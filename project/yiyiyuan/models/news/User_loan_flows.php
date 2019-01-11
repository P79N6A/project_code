<?php

namespace app\models\news;

use app\models\BaseModel;
use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_user_loan_flows".
 *
 * @property integer $id
 * @property integer $loan_id
 * @property integer $admin_id
 * @property integer $loan_status
 * @property string $relative
 * @property string $reason
 * @property string $create_time
 * @property string $admin_name
 * @property integer $type
 */
class User_loan_flows extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_loan_flows';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'admin_id'], 'required'],
            [['loan_id', 'admin_id', 'loan_status', 'type'], 'integer'],
            [['create_time'], 'safe'],
            [['relative', 'reason'], 'string', 'max' => 1024],
            [['admin_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'admin_id' => 'Admin ID',
            'loan_status' => 'Loan Status',
            'relative' => 'Relative',
            'reason' => 'Reason',
            'create_time' => 'Create Time',
            'admin_name' => 'Admin Name',
            'type' => 'Type',
        ];
    }

    public function addRecord($condition, $type = -100) {

        if (empty($condition)) {
            return FALSE;
        }
        $o = new self;
        $data['loan_id'] = $condition['loan_id'];
        $data['loan_status'] = $condition['loan_status'];
        if (isset($condition['reason'])) {
            $data['reason'] = $condition['reason'];
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        if ($type == -100) {
            $userinfo = Yii::$app->session->get("user");
            $data['admin_id'] = $userinfo->id;
            $data['admin_name'] = $userinfo->realname;
        } else {
            $data['admin_id'] = $type;
        }
        $o->attributes = $data;
        return $o->save();
    }

    /**
     * 重构添加
     * @param $condition
     * @param int $type
     * @return boolc
     */
    public function add_Record($condition, $type = -100) {
        if (empty($condition)) {
            return FALSE;
        }
        $data['loan_id'] = $condition['loan_id'];
        $data['loan_status'] = $condition['loan_status'];
        if (isset($condition['reason'])) {
            $data['reason'] = $condition['reason'];
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        if ($type == -100) {
            $userinfo = Yii::$app->session->get("user");
            $data['admin_id'] = $userinfo->id;
            $data['admin_name'] = $userinfo->realname;
        } elseif ($type == -101) {
            $userinfo = Yii::$app->backstage->identity;
            $data['admin_id'] = $userinfo->id;
            $data['admin_name'] = $userinfo->realname;
        } else {
            $data['admin_id'] = $type;
        }
        $error = $this->chkAttributes($data);
        Logger::errorLog(print_r(array('Error : '.$error), true), 'repay_ret4', 'jiedianqian');
        if($error){
            return false;
        }

        return $this->save();
    }



    /**
     * 批量添加变更记录
     */
    public function addFlows($loan_records){
        if(empty($loan_records)){
            return 0;
        }

        $now_time = date('Y-m-d H:i:s');
        $condition_flows = [];
        foreach ($loan_records as $key => $value) {
            $condition_flows[] = [
                'loan_id' => $value->loan_id,
                'loan_status' => 9,
                'admin_id' => -1,
                'create_time' => $now_time
            ];
        }

        return static::insertBatch($condition_flows);
    }

    /**
     * 通过借款状态和借款id获取flows对象
     * @param $status
     * @param $loan_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getByStatusLoanId($status, $loan_id){
        $status = intval($status);
        $loan_id = intval($loan_id);
        if(!$loan_id || !$status){
            return null;
        }
        return self::find()->where(['loan_status' => $status, 'loan_id' => $loan_id])->one();
    }

    /**
     * 更新操作理由
     * @param $reason
     * @return bool
     */
    public function updateReason($reason){
        if(!$reason){
            return false;
        }
        $this->reason = $reason;
        try {
            $result = $this->save();
            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }

}

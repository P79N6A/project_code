<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_push_user_loan".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $amount
 * @property integer $status
 * @property integer $send_status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class PushUserLoan extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_push_user_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'status', 'send_status', 'version'], 'integer'],
            [['amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe']
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
            'amount' => 'Amount',
            'status' => 'Status',
            'send_status' => 'Send Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 添加新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/9/14 20:38
     */
    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/9/14 20:38
     */
    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 查询记录，根据loan_id
     * @param $loan_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/9/17 17:15
     */
    public function getByLoanId($loan_id) {
        if (empty($loan_id)) {
            return null;
        }
        return self::find()->where(['loan_id' => $loan_id])->one();
    }
}

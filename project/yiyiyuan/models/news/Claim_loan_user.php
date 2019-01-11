<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_claim_loan_user".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $amount
 * @property string $loan_time
 * @property string $send_time
 * @property string $create_time
 */
class Claim_loan_user extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_claim_loan_user';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'amount', 'loan_time', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'is_send'], 'integer'],
            [['amount'], 'number'],
            [['loan_time', 'send_time', 'create_time'], 'safe']
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
            'loan_time' => 'Loan Time',
            'send_time' => 'Send Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 一个月内是否作为第三方债权推送给债匹
     * @param type $user_id
     */
    public function getUserInMonth($user_id) {
        $time = date('Y-m-d H:i:s');
        $start_time = date('Y-m-d 00:00:00', strtotime("-30 days"));
        $where = [
            'AND',
            ['user_id' => $user_id],
            ['is_send' => 1],
            ['between', 'create_time', $start_time, $time]
        ];
        $count = self::find()->where($where)->count();
        if ($count == 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function addRecord($condition) {
        $data['user_id'] = $condition['user_id'];
        $data['loan_id'] = $condition['loan_id'];
        $data['is_send'] = 0;
        $data['amount'] = $condition['amount'];
        $data['loan_time'] = $condition['loan_time'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        print_r($error);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function setSendStatus($is_send = 1) {
        $data['is_send'] = $is_send;
        if ($is_send == 1) {
            $data['send_time'] = date('Y-m-d H:i:s');
        }
        $error = $this->chkAttributes($data);
        if ($error) {
            return FALSE;
        }
        $result = $this->save();
        return $result;
    }

}

<?php

namespace app\models\day;

use app\models\BaseModel;
use Exception;

/**
 * This is the model class for table "yi_user_remit_list_guide".
 *
 * @property string $id
 * @property string $order_id
 * @property string $loan_id
 * @property string $admin_id
 * @property string $settle_request_id
 * @property string $real_amount
 * @property string $settle_fee
 * @property string $settle_amount
 * @property string $rsp_code
 * @property string $rsp_msg
 * @property string $remit_status
 * @property string $create_time
 * @property string $bank_id
 * @property string $user_id
 * @property integer $type
 * @property string $last_modify_time
 * @property string $remit_time
 * @property integer $fund
 * @property integer $payment_channel
 * @property integer $version
 */
class User_remit_list_guide extends BaseModel {

    const CN_BF_PEANUT = 114; // 宝付
    const CN_RB_YYY = 110; //融宝一亿元(同6)
    const CN_RB_PEANUT = 112; // 融宝花生米富
    const CN_CHANGJIE = 117; // 畅捷代付
    const CN_RB_DAY = 168; // 融宝
    const CN_RB_PXHT = 176; // 萍乡海桐融宝

    /**
     * @inheritdoc
     */

    public static function tableName() {
        return 'qj_user_remit_list';
    }

    public function getUser() {
        return $this->hasOne(User_guide::className(), ['user_id' => 'user_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank_guide::className(), ['id' => 'bank_id']);
    }

    public function getLoan() {
        return $this->hasOne(User_loan_guide::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['order_id', 'admin_id', 'create_time', 'bank_id'], 'required'],
            [['loan_id', 'admin_id', 'bank_id', 'user_id', 'type', 'fund', 'payment_channel', 'version'], 'integer'],
            [['real_amount', 'settle_fee', 'settle_amount'], 'number'],
            [['create_time', 'last_modify_time', 'remit_time'], 'safe'],
            [['order_id', 'settle_request_id'], 'string', 'max' => 32],
            [['rsp_code'], 'string', 'max' => 30],
            [['rsp_msg'], 'string', 'max' => 50],
            [['remit_status'], 'string', 'max' => 12]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'loan_id' => 'Loan ID',
            'admin_id' => 'Admin ID',
            'settle_request_id' => 'Settle Request ID',
            'real_amount' => 'Real Amount',
            'settle_fee' => 'Settle Fee',
            'settle_amount' => 'Settle Amount',
            'rsp_code' => 'Rsp Code',
            'rsp_msg' => 'Rsp Msg',
            'remit_status' => 'Remit Status',
            'create_time' => 'Create Time',
            'bank_id' => 'Bank ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'remit_time' => 'Remit Time',
            'fund' => 'Fund',
            'payment_channel' => 'Payment Channel',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock() {
        return "version";
    }

    public function getDoingData($loan_id) {
        $result = self::find()->where(['loan_id' => $loan_id])->andWhere(['!=', 'remit_status', 'FAIL'])->one();
        if (!empty($result)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 出款表插入新的数据
     * @param type $loan
     */
    public function inData($data) {
        if (empty($data)) {
            return FALSE;
        }
        $settle_amount = $data['is_calculation'] == 1 ? $data['amount'] - $data['withdraw_fee'] : $data['amount'];
        $time = date('Y-m-d H:i:s');
        $postData = [
            'order_id' => '-1',
            'loan_id' => $data['loan_id'],
            'admin_id' => '-1',
            'settle_request_id' => '',
            'real_amount' => $data['amount'],
            'settle_fee' => 0,
            'settle_amount' => $settle_amount,
            'rsp_code' => '',
            'remit_status' => 'INIT',
            'create_time' => $time,
            'bank_id' => $data['bank_id'],
            'user_id' => $data['user_id'],
            'type' => 1,
            'last_modify_time' => $time,
            'remit_time' => '0000-00-00',
            'fund' => 11,
            'payment_channel' => self::CN_RB_PXHT,
            'version' => 0,
        ];
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        if ($result) {
            $this->order_id = "YD" . date('Ymdhis') . rand(10, 99) . $this->id;
            $result = $this->save();
        }
        return $result;
    }

    /**
     * 初始数据
     * @param type $fund 11
     * @param type $channel 152
     */
    public function getInitByFund($fund, $channel, $limit = 200) {
        $where = [
            'AND',
            [
                'remit_status' => 'INIT',
                'fund' => $fund,
                'payment_channel' => $channel,
                'type' => 1, //借款
            ],
            ['>', 'create_time', date('Y-m-d H:i:s', strtotime('-5 days'))],
        ];
        $remits = static::find()->where($where)->orderBy('id ASC')->limit($limit)->all();
        return $remits;
    }

    /**
     * 当天出款成功的
     */
    public function getSuccessData() {
        $start_time = date('Y-m-d');
        $end_time = date('Y-m-d H:i:s');
        $where = [
            'AND',
            ['BETWEEN', 'create_time', $start_time, $end_time],
            ['remit_status' => ['INIT', 'LOCK', 'DOREMIT', 'SUCCESS']]
        ];
        return self::find()->where($where)->sum('settle_amount');
    }

    /**
     * 锁定正在出款接口的状态
     */
    public function lockRemits($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => 'LOCK'], ['id' => $ids, 'remit_status' => 'INIT']);
        return $ups;
    }

    /**
     * 保存为锁定: 锁定当前出款纪录
     * @return  bool
     */
    public function lock() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'LOCK';
            $result = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存为出款中状态
     * @return  bool
     */
    public function saveDoRemit() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'DOREMIT';
            $result = $this->save();
            return $result;
        } catch (Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 保存为失败
     * @return bool
     */
    public function savePayFail($rsp_code, $rsp_msg, $client_id = '') {
        $time = date('Y-m-d H:i:s');
        $this->rsp_code = (string) $rsp_code;
        $this->rsp_code = substr($this->rsp_code, 0, 30);
        $this->rsp_msg = mb_substr((string) $rsp_msg, 0, 50); // 预留
        if (!empty($client_id)) {
            $this->settle_request_id = $client_id;
        }
        $this->last_modify_time = $time;
        $this->remit_time = $time;
        $this->remit_status = 'FAIL';
        $result = $this->save();

//        if ($result) {
//            $res = $this->loanExtend->savePayFail();
//        }

        return $result;
    }

    /**
     * [savePaySuccess description]
     * @return [type] [description]
     */
    public function savePaySuccess($client_id = '') {
        $time = date('Y-m-d H:i:s');
        $this->rsp_code = '0000';

        if (!empty($client_id)) {
            $this->settle_request_id = $client_id;
        }
        $this->last_modify_time = $time;
        $this->remit_time = $time;

        $this->remit_status = 'SUCCESS';
        $result = $this->save();
//
//        if ($result) {
//            $res = $this->loanExtend->savePaySuccess();
//        }
        return $result;
    }

}

<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_cg_remit".
 *
 * @property string $id
 * @property string $remit_id
 * @property string $order_id
 * @property string $loan_id
 * @property string $real_amount
 * @property string $settle_amount
 * @property string $rsp_code
 * @property string $rsp_msg
 * @property string $remit_status
 * @property string $create_time
 * @property string $bank_id
 * @property string $user_id
 * @property string $last_modify_time
 * @property string $remit_time
 * @property integer $version
 */
class Cg_remit extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_cg_remit';
    }

    public function getRemitlist() {
        return $this->hasOne(User_remit_list::className(), ['id' => 'remit_id']);
    }

    public function getExchange() {
        return $this->hasOne(Exchange::className(), ['loan_id' => 'loan_id']);
    }

    public function getUserloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getPushnotwithdrawals() {
        return $this->hasOne(Push_not_withdrawals::className(), ['loan_id' => 'loan_id']);
    }
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['remit_id', 'loan_id', 'bank_id', 'user_id', 'version'], 'integer'],
            [['order_id', 'create_time', 'bank_id'], 'required'],
            [['real_amount', 'settle_amount'], 'number'],
            [['create_time', 'last_modify_time', 'remit_time'], 'safe'],
            [['order_id'], 'string', 'max' => 32],
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
            'remit_id' => 'Remit ID',
            'order_id' => 'Order ID',
            'loan_id' => 'Loan ID',
            'real_amount' => 'Real Amount',
            'settle_amount' => 'Settle Amount',
            'rsp_code' => 'Rsp Code',
            'rsp_msg' => 'Rsp Msg',
            'remit_status' => 'Remit Status',
            'create_time' => 'Create Time',
            'bank_id' => 'Bank ID',
            'user_id' => 'User ID',
            'last_modify_time' => 'Last Modify Time',
            'remit_time' => 'Remit Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 根据loan_id查询存管出款子订单
     * @param type $loan_id
     * @return obj|bool
     */
    public function getByLoanId($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return NULL;
        }
        $cg_remit = self::find()->where(['loan_id' => $loan_id])->one();
        return $cg_remit;
    }

    /**
     * 根据order_id查询存管出款子订单
     * @param type $order_id
     * @return obj|bool
     */
    public function getByOrderId($order_id) {
        if (empty($order_id)) {
            return NULL;
        }
        $cg_remit = self::find()->where(['order_id' => $order_id])->one();
        return $cg_remit;
    }

    /**
     * 批量锁定LOCK
     * @param type $ids
     * @return boolean
     */
    public function updateAllLock($ids) {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        return self::updateAll(['remit_status' => 'LOCK'], ['id' => $ids]);
    }

    /**
     * 批量锁定LOCKREMIT
     * @param type $ids
     * @return boolean
     */
    public function updateAllLockremit($ids) {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        return self::updateAll(['remit_status' => 'LOCKREMIT'], ['id' => $ids]);
    }

    /**
     * 添加记录
     * @param $condition
     * @return bool
     */
    public function addCg($condition) {
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
     * 修改
     * @param $condition
     * @return bool
     */
    public function updateCg($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    public function lock() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'LOCK';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function lockRemit() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'LOCKREMIT';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function willRemit($remit_time='') {
        try {
            $time = date('Y-m-d H:i:s');
            if (!empty($remit_time) && $this->remit_time != 0){
                $time = $remit_time;
            }
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'WILLREMIT';
            $this->remit_time   = $time;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        if (!empty($remit_time)){
            $loan_result = $this->userloan->saveStarttime($remit_time,$this->userloan->days);
            if (!$loan_result) {
                return false;
            }
        }
        return $result;
    }

    public function doremit() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'DOREMIT';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function waitRemit() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'WAITREMIT';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function noRemit() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'NOREMIT';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 提现失败，挂起人工处理
     * @param string $rsp_code
     * @param string $rsp_msg
     * @return bool
     */
    public function outMoneyFail($rsp_code = '', $rsp_msg = '') {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'FAIL';
            $this->rsp_code = $rsp_code;
            $this->rsp_msg = $rsp_msg;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        if (!$result) {
            return $result;
        }
        $remit_result = $this->remitlist->savePayFail($rsp_code, $rsp_msg);
        return $remit_result;
    }

    /**
     * 债匹失败，切换通道
     * @param string $rsp_code
     * @param string $rsp_msg
     * @return bool
     */
    public function claimFail($rsp_code = '', $rsp_msg = '') {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'FAIL';
            $this->rsp_code = $rsp_code;
            $this->rsp_msg = $rsp_msg;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        if (!$result) {
            return $result;
        }
        $remit_result = $this->remitlist->changeFund($rsp_code, $rsp_msg);
        if (!$remit_result) {
            return false;
        }
        $ex_res = $this->exchange->saveOutCunguan();
        if (!$ex_res) {
            return false;
        }
        return $ex_res;
    }

    public function outMoneySuccess() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->remit_status = 'SUCCESS';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        if (!$result) {
            return false;
        }
        $remit_result = $this->remitlist->savePaySuccess();
        return $remit_result;
    }

    /**
     * 查询初始状态的推送给债匹
     * @param type $limit
     * @return type
     */
    public function getInitData($limit = 200) {
        $start_date = date('Y-m-d 00:00:00', strtotime('-2 days'));
        $where = [
            'AND',
            ['remit_status' => 'INIT'],
            ['>', "last_modify_time", $start_date],
        ];
        $cg_remit = self::find()->where($where)->limit($limit)->all();
        return $cg_remit;
    }

    /**
     * 查询WILLREMIT状态的进行提现
     * @param type $limit
     * @return type
     */
    public function getWillremitData($limit = 200) {
        $start_date = date('Y-m-d 00:00:00', strtotime('-2 days'));
        $where = [
            'AND',
            ['remit_status' => 'WILLREMIT'],
            ['>', "last_modify_time", $start_date],
        ];
        $cg_remit = self::find()->where($where)->limit($limit)->all();
        return $cg_remit;
    }

    public function getData($remit_status, $limit) {
        $where = [
            'AND',
            [
                'remit_status' => $remit_status,
            ],
            ['>', 'create_time', date('Y-m-d H:i:s', strtotime('-5 days'))],
        ];
        $remits = static::find()->where($where)->orderBy('id ASC')->limit($limit)->all();
        return $remits;
    }

}

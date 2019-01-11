<?php

namespace app\models\news;

use app\models\BaseModel;
use app\models\news\User_loan;
use app\models\news\Exchange;
use app\models\news\User_loan_flows;
use Yii;
use yii\helpers\ArrayHelper;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_user_loan_extend".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $uuid
 * @property integer $outmoney
 * @property integer $payment_channel
 * @property string $userIp
 * @property integer $extend_type
 * @property integer $success_num
 * @property string $last_modify_time
 * @property string $create_time
 */
class User_loan_extend extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_loan_extend';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'outmoney', 'payment_channel', 'extend_type', 'success_num', 'fund', 'version', 'loan_total', 'loan_success'], 'integer'],
            [['loan_quota'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['uuid'], 'string', 'max' => 55],
            [['userIp'], 'string', 'max' => 64],
            [['status'], 'string', 'max' => 16]
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
            'uuid' => 'Uuid',
            'outmoney' => 'Outmoney',
            'payment_channel' => 'Payment Channel',
            'userIp' => 'User Ip',
            'extend_type' => 'Extend Type',
            'success_num' => 'Success Num',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getRate() {
        return $this->hasOne(User_rate::className(), ['user_id' => 'user_id']);
    }

    public function getLoanflow() {
        return $this->hasOne(User_loan_flows::className(), ['loan_id' => 'loan_id'])->where([User_loan_flows::tableName() . '.loan_status' => 6]);
    }

    public function getPushyxl() {
        return $this->hasOne(Push_yxl::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * 关联查询玖富提交表信息
     */
    public function getSubmit() {
        return $this->hasOne(User_submit_list::className(), ['loan_id' => 'loan_id']);
    }

    public function getUserRemit() {
        $remit = User_remit_list::find()->where(['loan_id' => $this->loan_id])->orderBy('id DESC')->limit(1)->one();
        return $remit;
    }

    public function getRemit(){
        return $this->hasMany(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }

    public function getInsurance() {
        return $this->hasOne(Insurance::className(), ['loan_id' => 'loan_id']);
    }

    /*
     * 根据loan_id查询user_loan_extend信息
     */

    public function checkUserLoanExtend($loan_id) {
        if (!$loan_id) {
            return FALSE;
        }
        $user_loan_extend_info = static::find()
                ->where(['loan_id' => $loan_id])
                ->one();

        return $user_loan_extend_info;
    }

    /*
     * 修改user_loan_extend信息
     * @param int $loan_id
     * @param array $update_arr
     * @return NULL | 1
     */

    public function updateLoanExtendInfo($loan_id, $update_arr) {
        if (empty($loan_id) || empty($update_arr) || !is_array($update_arr)) {
            return NULL;
        }
        $up_info = $this->checkUserLoanExtend($loan_id);
        if ($up_info) {
            foreach ($update_arr as $k => $v) {
                $up_info->$k = $v;
            }
            return $up_info->save();
        } else {
            return NULL;
        }
    }

    /**
     * 重构修改user_loan_extend信息
     * @param array $update_arr
     * @return NULL | 1
     * @author Zhangchao <zhangchao@xianhuahua.com>
     */
    public function update_loanextendinfo($loan_id, $update_arr) {
        if (empty($loan_id) || empty($update_arr) || !is_array($update_arr)) {
            return NULL;
        }
        $up_info = $this->checkUserLoanExtend($loan_id);
        $data = $update_arr;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /*
     * 修改已借款却未完成活体的extend信息
     * @return NULL | 1
     */

    public function updateOutMoneyStatus($user_id) {
        if (!$user_id) {
            return NULL;
        }
        $where_arr = array('user_id' => $user_id, 'status' => 6);
        $userloaninfo = (new User_loan())->checkUserLoan($where_arr, 1);

        if (!empty($userloaninfo)) {
            $loanextend = self::find()->where(['loan_id' => $userloaninfo[0]->loan_id])->one();
            $ret = $loanextend->update_loanextendinfo($userloaninfo[0]->loan_id, $data = array('outmoney' => 0, 'status' => 'AUTHED'));
            return $ret;
        } else {
            return NULL;
        }
    }

    //begin to do
    /**
     * 修改 || 新建 用户合同拓展表信息
     * @param $condition  array   array('field'=>fieldvalue);
     * @return bool | id
     */
    public function addList($condition) {
        if (!empty($condition['loan_id'])) {
            $loansubsidiary = (new User_loan_extend())->getUserLoanSubsidiaryByLoanId($condition['loan_id']);
            if ($loansubsidiary) {
                $result = $loansubsidiary->updateUserLoanSubsidiary($condition);
                return $loansubsidiary->id;
            }
        }
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['loan_total'] = empty($condition['user_id']) ? 0 : User_loan::find()->where(['user_id' => $condition['user_id']])->count();
        $data['loan_quota'] = empty($condition['user_id']) ? 0 : (new User_quota())->getUserLoanQuota($condition['user_id']);
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        if ($this->save()) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    /**
     * 通过loan_id获取User_loan_extend对象
     * @param int loan_id
     * @return bool | object
     */
    public function getUserLoanSubsidiaryByLoanId($loan_id) {
        if (empty($loan_id)) {
            return false;
        }
        $result = User_loan_extend::find()->where(['loan_id' => $loan_id])->one();
        return $result;
    }

    /**
     * 更新User_loan_extend
     */
    public function updateUserLoanSubsidiary($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 切换通道
     * @param  integer $channel 通道
     * @param  integer $fund  资金方
     * @return bool
     */
    public function changeChannel($channel, $fund = null) {
        if (!empty($fund)) {
            $this->fund = $fund;
        }
        $this->payment_channel = $channel;
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 债匹失败 存管切换通道
     * @param $loanExtendInfo
     * @return bool
     */
    public function change_channel($loanExtendInfo) {
        $condition = [
            'loan_id' => $loanExtendInfo->loan_id,
            'status' => 'WILLREMIT',
            'payment_channel' => 112,
        ];
        $result = $loanExtendInfo->updateUserLoanSubsidiary($condition);
        if (!$result) {
            return false;
        }
        $exChange = new Exchange();
        $ex_res = $exChange->add_list(["loan_id" => $loanExtendInfo->loan_id, "type" => 2]);
        if (!$ex_res) {
            return false;
        }

        return true;
    }

    /**
     * 2小时未领取借款 存管切换通道
     * @param $loanExtendInfo
     * @return bool
     */
    public function change_channel_noget($loanExtendInfo) {
        $condition = [
            'loan_id' => $loanExtendInfo->loan_id,
            'payment_channel' => 112,
        ];
        $result = $loanExtendInfo->updateUserLoanSubsidiary($condition);
        if (!$result) {
            return false;
        }
        $remirList = User_remit_list::find()->where(["loan_id" => $loanExtendInfo->loan_id])->one();
        $re_res = $remirList->update_remit(["payment_channel" => 112]);
        if (!$re_res) {
            return false;
        }
        $exChange = new Exchange();
        $ex_res = $exChange->add_list(["loan_id" => $loanExtendInfo->loan_id, "type" => 2]);
        if (!$ex_res) {
            return false;
        }

        return true;
    }

    /**
     * 存管切换通道锁定
     * @param $loanExtendInfo
     * @return bool
     */
    public function change_channel_lock($loanExtendInfo) {
        $condition = [
            'loan_id' => $loanExtendInfo->loan_id,
            'payment_channel' => 112,
        ];
        $result = $loanExtendInfo->updateUserLoanSubsidiary($condition);
        if (!$result) {
            return false;
        }
        $remirList = User_remit_list::find()->where(["loan_id" => $loanExtendInfo->loan_id])->one();
        $re_res = $remirList->update_remit(["payment_channel" => 112, "remit_status" => "CHANGELOCK"]);
        if (!$re_res) {
            return false;
        }
        $exChange = new Exchange();
        $ex_res = $exChange->add_list(["loan_id" => $loanExtendInfo->loan_id, "type" => 2]);
        if (!$ex_res) {
            return false;
        }

        return true;
    }

    /**
     * 保存状态为：AUTHED
     * @return  bool
     */
    public function doTbSuccess() {
        try {
            $this->status = 'TB-SUCCESS';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存状态为：AUTHED
     * @return  bool
     */
    public function doAuthed() {
        try {
            $this->status = 'AUTHED';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存为成功
     * @return  bool
     */
    public function savePaySuccess() {
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->status = 'SUCCESS';
        $result = $this->save();
        return $result;
    }

    /**
     * 保存为失败
     * @return  bool
     */
    public function savePayFail() {
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->status = 'FAIL';
        $result = $this->save();
        return $result;
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 保存为锁定: 锁定当前出款纪录
     * @return  bool
     */
    public function doRemit() {
        try {
            $this->status = 'DOREMIT';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存为待出款状态
     * @return  bool
     */
    public function saveWillRemit($fund = '', $payment_channel = '') {
        if (!empty($fund)) {
            $this->fund = $fund;
        }
        if (!empty($payment_channel)) {
            $this->payment_channel = $payment_channel;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->status = 'WILLREMIT';
        $result = $this->save();
        return $result;
    }

    /**
     * 出款失败，放回大池子
     * @return  bool
     */
    public function savePreremit() {
        $this->fund = 0;
        $this->payment_channel = 0;
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->status = 'PRE-REMIT';
        $result = $this->save();
        return $result;
    }

    /**
     * 借款驳回
     * @return  bool
     */
    public function saveReject() {
        try {
            $this->status = 'REJECT';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存为存管待债匹状态
     * @return  bool
     */
    public function savePreAuthed($fund = '', $payment_channel = '') {
        if (!empty($fund)) {
            $this->fund = $fund;
        }
        if (!empty($payment_channel)) {
            $this->payment_channel = $payment_channel;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->status = 'PREAUTHED';
        $result = $this->save();
        return $result;
    }

    /**
     * 批量修改为预处理状态，同时将借款状态修改为9，同时往flows表里添加变更记录
     */
    public function setAllPreremit($loan_list) {
        $loan_ids = ArrayHelper::getColumn($loan_list, 'loan_id');
        $now_time = date('Y-m-d H:i:s');
        $loan_extend_nums = User_loan_extend::updateAll(['status' => 'PREREMIT', 'last_modify_time' => $now_time], ['loan_id' => $loan_ids]);
        $loan_nums = User_loan::updateAll(['status' => 9, 'last_modify_time' => $now_time], ['loan_id' => $loan_ids]);
        Logger::dayLog('distribution', 'index', $now_time, '2锁定user_loan_extend.status=PREREMIT', $loan_extend_nums);
        $user_loan_flows = new User_loan_flows();
        $flow_nums = $user_loan_flows->addFlows($loan_list);
        return $flow_nums;
    }

    /**
     * 批量修改为等待处理的状态
     */
    public function setAllWaitremit($loan_list) {
        $loan_ids = ArrayHelper::getColumn($loan_list, 'loan_id');
        $now_time = date('Y-m-d H:i:s');
        $loan_extend_nums = User_loan_extend::updateAll(['status' => 'WAITREMIT', 'last_modify_time' => $now_time], ['loan_id' => $loan_ids]);
        return $loan_extend_nums;
    }

    /**
     * 批量修改为出款中的状态
     */
    public function setAllDoremit($loan_list) {
        $loan_ids = ArrayHelper::getColumn($loan_list, 'loan_id');
        $now_time = date('Y-m-d H:i:s');
        $loan_extend_nums = User_loan_extend::updateAll(['status' => 'DOREMIT', 'last_modify_time' => $now_time], ['loan_id' => $loan_ids]);

        return $loan_extend_nums;
    }

    public function updateSuccessNum($success_num) {
        $postData = [
            'success_num' => $success_num,
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 获取authed状态数据
     *
     * @return []
     */
    public function getAuthedLists() {
        $lastModifyTime = date('Y-m-d H:i:s', time() - 86400 * 2);
        $where = [
            'AND',
            ['>=', User_loan_extend::tableName() . '.last_modify_time', $lastModifyTime],
            [User_loan_extend::tableName() . '.status' => 'AUTHED'],
        ];
        $authedLists = static::find()
                ->With([
                    'loan' => function($query) {
                $query->andWhere('status=6');
            },
                ])
                ->where($where)
                ->limit(500)
                ->all();
        if (empty($authedLists)) {
            return [];
        }
        return $authedLists;
    }

    /**
     * 获取Pre-remit状态数据
     *
     * @return []
     */
    public function getPreLists() {
        $lastModifyTime = date('Y-m-d H:i:s', time() - 86400 * 2);
        $where = [
            'AND',
            ['>=', User_loan_extend::tableName() . '.last_modify_time', $lastModifyTime],
            [User_loan_extend::tableName() . '.status' => 'PRE-REMIT'],
        ];
        $preLists = static::find()
                ->With([
                    'loan' => function($query) {
                $query->andWhere('status=9');
            },
                ])
                ->with('user')
                ->where($where)
                ->limit(500)
                ->all();
        if (empty($preLists)) {
            return [];
        }
        return $preLists;
    }

    /**
     * 获取willremit状态总金额
     *
     * @param [type] $fund
     * @return void
     */
    public function getWillRemitMoney($fund) {
        if ($fund < 1) {
            return false;
        }
        $begin_time = date("Y-m-d H:i:s", strtotime('-3 day'));
        $end_time = date('Y-m-d 23:59:59');
        $where = [
            'AND',
            [User_loan_extend::tableName() . '.status' => 'WILLREMIT'],
            [User_loan_extend::tableName() . '.fund' => $fund],
            ['>=', User_loan_extend::tableName() . '.create_time', $begin_time],
            ['<', User_loan_extend::tableName() . '.create_time', $end_time],
        ];
        $willRemitMoney = static::find()
                ->joinWith('loan', true, 'LEFT JOIN')
                ->where($where)
                ->sum(User_loan::tableName() . '.real_amount');
        return $willRemitMoney;
    }

    /**
     * 批量修改为PRE-REMIT，同时将借款状态修改为9，同时往flows表里添加变更记录
     *
     * @param [type] $loan_list
     * @return void
     */
    public function batchSetPreremit($loan_list) {
        $loan_ids = ArrayHelper::getColumn($loan_list, 'loan_id');
        $now_time = date('Y-m-d H:i:s');
        $loan_extend_nums = User_loan_extend::updateAll(['status' => 'PRE-REMIT', 'last_modify_time' => $now_time], ['loan_id' => $loan_ids]);
        $loan_nums = User_loan::updateAll(['status' => 9, 'last_modify_time' => $now_time], ['loan_id' => $loan_ids]);
        Logger::dayLog('distribution', 'index', $now_time, '2锁定user_loan_extend.status=PREREMIT', $loan_extend_nums);
        $user_loan_flows = new User_loan_flows();
        $flow_nums = $user_loan_flows->addFlows($loan_list);
        return $flow_nums;
    }

}

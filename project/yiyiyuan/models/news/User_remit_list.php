<?php

namespace app\models\news;

use app\commonapi\Logger;
use app\models\BaseModel;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%yi_user_remit_list}}".
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
 * @property string $remit_status
 * @property string $create_time
 * @property string $bank_id
 * @property string $user_id
 * @property integer $type
 * @property string $last_modify_time
 * @property string $remit_time
 * @property integer $fund
 * @property integer $payment_channel
 */
class User_remit_list extends BaseModel {

    const CN_SINA = 1; // 新浪(废弃)
    const CN_ZX = 2; // 中信(废弃)
    const CN_JF = 3; // 玖富(暂时废弃)
    const CN_BF = 8; // 宝付
    const CN_BF_YYY = 107; // 宝付(同8)
    const CN_BF_PEANUT = 114; // 宝付
    const CN_RB = 6; // 融宝(即将废弃2017.6.26)
    const CN_RB_YYY = 110; //融宝一亿元(同6)
    const CN_RB_PEANUT = 112; // 融宝花生米富
    const CN_RB_PINGXIANG = 168; // 融宝花生米富
    const CN_CJ_PINGXIANG = 174; // 萍乡畅捷出款
    const CN_RB_YGY = 176; // 一个亿融宝出款
    const CN_CHANGJIE = 117; // 畅捷代付
    const FUND_PEANUT = 1; //米富
    const FUND_JF = 2; //玖富
    const FUND_LIANJIAO = 3; //联交所
    const FUND_JINLIAN = 4; //金联储
    const FUND_XIAONUO = 5; //小诺
    const FUND_WEISM = 6; //微神马
    const FUND_CUNGUAN = 10; //存管
    const FUND_QITA = 11; //其他

    /**
     * @inheritdoc
     */

    public static function tableName() {
        return 'yi_user_remit_list';
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getUserExtend() {
        return $this->hasOne(User_extend::className(), ['user_id' => 'user_id']);
    }

    public function getPassword() {
        return $this->hasOne(User_password::className(), ['user_id' => 'user_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getLoanExtend() {
        return $this->hasOne(User_loan_extend::className(), ['loan_id' => 'loan_id']);
    }

    public function getGoodsOrder() {
        return $this->hasOne(GoodsOrder::className(), ['loan_id' => 'loan_id']);
    }

    public function getRepay() {
        return $this->hasOne(Loan_repay::className(), ['loan_id' => 'loan_id']);
    }

    public function getManager() {
        return Manager_logs::find()->where(['log_id' => $this->id])->asArray()->one();
    }

    public function getContacts() {
        return Favorite_contacts::find()->where(['user_id' => $this->user_id])->one();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['admin_id', 'create_time', 'bank_id'], 'required'],
            [['loan_id', 'admin_id', 'bank_id', 'user_id', 'type', 'fund', 'payment_channel', 'version'], 'integer'],
            [['real_amount', 'settle_fee', 'settle_amount'], 'number'],
            [['create_time', 'last_modify_time', 'remit_time'], 'safe'],
            [['order_id', 'settle_request_id'], 'string', 'max' => 32],
            [['rsp_code'], 'string', 'max' => 30],
            [['rsp_msg'], 'string', 'max' => 50],
            [['remit_status'], 'string', 'max' => 12],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'order_id' => '订单编号',
            'loan_id' => 'Loan ID',
            'admin_id' => 'Admin ID',
            'settle_request_id' => '结算请求号',
            'real_amount' => '实际出款金额',
            'settle_fee' => '出款手续费',
            'settle_amount' => '结算金额',
            'rsp_code' => '操作码',
            'rsp_msg' => '出错原因',
            'remit_status' => '打款状态',
            'create_time' => '担保卡添加时间',
            'bank_id' => 'Bank ID',
            'user_id' => '用户ID',
            'type' => '出款类型',
            'last_modify_time' => '最后修改时间',
            'remit_time' => '出款时间',
            'fund' => '资金方',
            'payment_channel' => '出款通道',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 获取未处理的订单
     * @param  int $payment_channel 通道
     * @param  int $limit           条数
     * @return []
     */
    public function getInitData($payment_channel, $limit) {
        $where = [
            'AND',
            [
                'remit_status' => 'INIT',
                'fund' => [1, 3], // 玖富, 新浪
                'payment_channel' => $payment_channel,
                'type' => 1, //借款
            ],
            ['>', 'create_time', date('Y-m-d H:i:s', strtotime('-5 days'))],
        ];
        $remits = static::find()->where($where)->orderBy('id ASC')->limit($limit)->all();
        return $remits;
    }

    /**
     * 锁定正在出款接口的状态
     */
    public function lockRemits($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => 'LOCK'], ['id' => $ids]);
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
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存为出款中状态
     * @return  bool
     */
    public function saveDoRemit() {
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->remit_status = 'DOREMIT';
        $result = $this->save();
        return $result;
    }

    /**
     * 保存为出款中状态
     * @return  bool
     */
    public function savePreRemit() {
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->remit_status = 'PREREMIT';
        $result = $this->save();
        return $result;
    }

    /**
     * remit_status 改为SUCCESS
     * @return  bool
     */
    public function saveSuccess() {
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->remit_status = 'SUCCESS';
        $result = $this->save();
        return $result;
    }

    /**
     * remit_status 改为REJECT
     * @return  bool
     */
    public function saveReject() {
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->remit_status = 'REJECT';
        $result = $this->save();
        return $result;
    }

    /**
     * 当前记录保存为失败，另外切换出款通道
     * @param type $fund
     * @param type $channel
     */
    public function changeFund($rsp_code, $rsp_msg, $client_id = '') {
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
        if ($result) {
            $res = $this->loanExtend->savePreremit();
        }

        return $result;
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

        if ($result) {
            $res = $this->loanExtend->savePayFail();
        }

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

        if ($result) {
            $res = $this->loanExtend->savePaySuccess();
        }
        return $result;
    }

    /**
     * 资方推单修改出款状态
     * @return  bool
     */
    public function saveFundPaySuccess($client_id = '', $req_id = '') {
        $time = date('Y-m-d H:i:s');
        $this->rsp_code = '0000';

        if (!empty($client_id)) {
            $this->settle_request_id = $client_id;
        }

        if (!empty($client_id)) {
            $this->order_id = $req_id;
        }

        $this->last_modify_time = $time;
        $this->remit_time = $time;

        $this->remit_status = 'SUCCESS';
        $result = $this->save();

        if ($result) {
            $res = $this->loanExtend->savePaySuccess();
        }
        return $result;
    }

    /**
     * 保存出款订单
     * @param [] $data
     * @return  bool
     */
    public function saveRemit($data) {
        if ($data['fund'] == 1) {
            $remit_status = 'INIT';
            $payment_channel = static::CN_RB_YGY;
        } elseif ($data['fund'] == 11) {
            $remit_status = 'INIT';
            $loan = User_loan::findOne($data['loan_id']);
            if ($loan->business_type == 10) {
                $payment_channel = static::CN_CJ_PINGXIANG;
            } else {
                $payment_channel = static::CN_RB_PINGXIANG;
            }
        } elseif ($data['fund'] == 2) {
            // 玖富
            $remit_status = 'INIT';
            $payment_channel = 0;
        } elseif ($data['fund'] == 3) {
            // 连交所
            $remit_status = 'INIT';
            $payment_channel = static::CN_RB_YYY;
        } elseif ($data['fund'] == 4) {
            // 金联储
            $remit_status = 'DOREMIT';
            $payment_channel = 0;
        } elseif ($data['fund'] == 6) {
            // 微神马
            $remit_status = 'INIT';
            $payment_channel = 0;
        } elseif ($data['fund'] == 10) {
            // 存管
            $remit_status = 'INIT';
            $payment_channel = 0;
        } elseif ($data['fund'] == 5) {
            // 小诺
            $remit_status = 'INIT';
            $payment_channel = 0;
        } else {
            Logger::dayLog("createremit", $data['loan_id'], "暂未接入此资金方");
            return false;
        }

        $time = date('Y-m-d H:i:s');
        $postData = [
            'order_id' => '',
            'loan_id' => $data['loan_id'],
            'admin_id' => $data['admin_id'],
            'settle_request_id' => $data['settle_request_id'],
            'real_amount' => $data['real_amount'],
            'settle_fee' => 0,
            'settle_amount' => $data['settle_amount'],
            'rsp_code' => '',
            'remit_status' => $remit_status,
            'create_time' => $time,
            'bank_id' => $data['bank_id'],
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'last_modify_time' => $time,
            'remit_time' => '0000-00-00',
            'fund' => $data['fund'],
            'payment_channel' => $payment_channel,
            'version' => 0,
        ];
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        if ($result) {
            $this->order_id = "Y" . date('Ymdhis') . rand(10, 99) . $this->id;
            $result = $this->save();
        }
        return $result;
    }

    /**
     * 查询是否有成功或者出款中的出款记录
     */
    public function getRemitCount($loan_id) {
        $where = [
            'and',
            ['loan_id' => $loan_id],
            ['!=', 'remit_status', 'FAIL'],
        ];
        $count = User_remit_list::find()->where($where)->count();
        return $count;
    }

    public function updateRemit($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        if ($result) {
            return $this;
        } else {
            return false;
        }
    }

    public function update_remit($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }

        return $this->save();
    }

    public function addRecord($condition) {
        if (empty($condition)) {
            return false;
        }
        $o = new self();
        foreach ($condition as $key => $val) {
            $o->{$key} = $val;
        }
        $o->create_time = date('Y-m-d H:i:s');
        $o->last_modify_time = date('Y-m-d H:i:s');
        $result = $o->save();
        return $result;
    }

    /**
     * 查询某个通道当日的已成功出款金额
     * @param $channel_id
     * @param int $fund
     * @param int $type 1实际金额 2合同金额
     * @return bool|int|mixed
     */
    public function todaySuccessMoney($channel_id = 0, $fund = 0, $type = 1) {
        if (empty($fund)) {
            return false;
        }
        $begin_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = [
            'and',
            ['fund' => $fund],
            ['payment_channel' => $channel_id],
            ['remit_status' => 'SUCCESS'],
            ['>=', 'remit_time', $begin_time],
            ['<', 'remit_time', $end_time],
        ];
        if ($type == 1) {
            $amount = User_remit_list::find()->select('sum(settle_amount) settle_amount')->where($where)->one();
            return empty($amount) ? 0 : $amount->settle_amount;
        } elseif ($type == 2) {
            $amount = User_remit_list::find()->select('sum(real_amount) real_amount')->where($where)->one();
            return empty($amount) ? 0 : $amount->real_amount;
        }
    }

    /**
     * 查询当日提交给出款通道的总金额
     */
    public function todayPushMoney() {
        $begin_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = [
            'and',
            ['>=', 'create_time', $begin_time],
            ['<', 'create_time', $end_time],
            ['fund' => [1]],
            ['IN', 'remit_status', ['SUCCESS', 'DOREMIT']],
        ];

        $amount = User_remit_list::find()->select('sum(settle_amount) settle_amount')->where($where)->one();

        return empty($amount) ? 0 : $amount->settle_amount;
    }

    /*
     * 查询指定时间、资方、出款通道，的出款总额
     * @param $begin_time
     * @param $end_time
     * @param array $fund
     * @param string $channel
     * @param string $type 1实际金额 2合同金额
     * @return bool|int|mixed
     */

    public function pushMoney($begin_time, $end_time, $fund = [], $channel = '', $type = 1) {
        if (!is_array($fund) || empty($fund)) {
            return false;
        }
        $begin_time = $begin_time ? $begin_time : date('Y-m-d 00:00:00');
        $end_time = $end_time ? $end_time : date('Y-m-d 23:59:59');
        $channel = $channel !== '' ? $channel : 112;
        $where = [
            'and',
            ['>=', 'create_time', $begin_time],
            ['<', 'create_time', $end_time],
            ['fund' => $fund],
            ['payment_channel' => $channel],
            ['IN', 'remit_status', ['SUCCESS', 'DOREMIT']],
        ];
        if ($type == 1) {
            $amount = User_remit_list::find()->select('sum(settle_amount) settle_amount')->where($where)->one();
            return empty($amount) ? 0 : $amount->settle_amount;
        } elseif ($type == 2) {
            $amount = User_remit_list::find()->select('sum(real_amount) real_amount')->where($where)->one();
            return empty($amount) ? 0 : $amount->real_amount;
        }
    }

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
     * 查询某个通道当日的对应状态出款金额 默认查询所有
     * @param int $fund
     * @param int $type 1实际金额 2合同金额
     * @return int
     */
    public function todayStatusMoney($fund, $status = 'ALL', $type = 2) {
        if (empty($fund)) {
            return false;
        }
        $begin_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $where = [
            'and',
            ['fund' => $fund],
            ['>=', 'create_time', $begin_time],
            ['<', 'create_time', $end_time]
        ];
        if ($status != 'ALL') {
            $where[] = ['remit_status' => $status];
        }
        if ($type == 1) {
            $amount = User_remit_list::find()->where($where)->sum('settle_amount');
        } elseif ($type == 2) {
            $amount = User_remit_list::find()->where($where)->sum('real_amount');
        }
        return isset($amount) ? $amount : 0;
    }

}

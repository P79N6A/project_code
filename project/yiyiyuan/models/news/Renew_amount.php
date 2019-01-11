<?php

namespace app\models\news;

use app\commonapi\Keywords;
use Yii;
use app\commonapi\Apidepository;

/**
 * This is the model class for table "yi_renew_amount".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $renew_fee
 * @property string $chase_fee
 * @property string $create_time
 * @property string $start_time
 * @property string $end_time
 * @property string $mark  1:时效性展期资格 2：无时效展期资格 3：关闭展期资格
 */
class Renew_amount extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_renew_amount';
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'loan_id', 'user_id', 'parent_loan_id', 'mark', 'type','installment_days','period','installment_result'], 'integer'],
            [['renew_fee', 'chase_fee', 'renew', 'installment_money','installment_fee','installment_amount' ], 'number'],
            [['create_time', 'start_time', 'end_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'renew_fee' => 'Renew Fee',
            'chase_fee' => 'Chase Fee',
            'create_time' => 'Create Time',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'user_id' => 'User ID',
            'parent_loan_id' => 'Parent Loan Id',
            'mark' => 'Mark',
            'type' => 'Type',
            'installment_result' => 'Installment Result',
            'period' => 'Reriod',
            'installment_amount' => 'Installment Amount',
            'installment_days' => 'Installment Days',
            'installment_fee' => 'Installment Fee',
            'installment_money' => 'Installment Money',
        ];
    }

    /**
     * 用户是否可以续期
     * @param type $loan
     */
    public function isCanrenew($loan) {
        if (empty($loan)) {
            return FALSE;
        }
        $now_time = date('Y-m-d H:i:s');
        $where = [
            'AND',
            ['loan_id' => $loan->loan_id],
            ['<=', 'start_time', $now_time],
            ['>=', 'end_time', $now_time]
        ];
        $renew = self::find()->where($where)->one();
        if (!empty($renew)) {
            return TRUE;
        }
        return FALSE;
    }

    public function getRenewFee($loan) {
        if (empty($loan)) {
            return FALSE;
        }
        $now_time = date('Y-m-d H:i:s');
        $where = [
            'AND',
            ['loan_id' => $loan->loan_id],
            ['<=', 'start_time', $now_time],
            ['>=', 'end_time', $now_time]
        ];
        $renew = self::find()->where($where)->one();
        if (!empty($renew)) {
            return $loan->withdraw_fee + $loan->interest_fee + $renew->renew_fee + $renew->chase_fee;
        }
        return FALSE;
    }

    public function addrenew($loan_info, $renew, $mark, $type = 1) {
        if (empty($loan_info) || empty($renew) || empty($mark)) {
            return false;
        }
        $nowTime = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d', (time() + ($loan_info->days + 1) * 24 * 3600));
        $renew_fee = $renew * $loan_info->amount + $loan_info->withdraw_fee;
        $condition = [
            'loan_id' => $loan_info->loan_id,
            'renew_fee' => $renew_fee,
            'user_id' => $loan_info->user_id,
            'parent_loan_id' => $loan_info->parent_loan_id,
            'mark' => $mark,
            'type' => $type,
            'renew' => $renew,
            'start_time' => date('Y-m-d'),
            'end_time' => $end_date,
            'create_time' => $nowTime
        ];
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function addRenewOver($loan_info, $data, $mark, $type = 1) {
        if (empty($loan_info) || empty($data) || empty($mark)) {
            return false;
        }
        $nowTime = date('Y-m-d H:i:s');
        $condition = [
            'loan_id' => $loan_info->loan_id,
            'renew_fee' => $data['renew_fee'],
            'user_id' => $loan_info->user_id,
            'parent_loan_id' => $loan_info->parent_loan_id,
            'mark' => $mark,
            'type' => $type,
            'renew' => $data['renew'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'create_time' => $nowTime
        ];
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateRenew($condition) {
        if (empty($condition)) {
            return false;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取借款续期费用
     * @param $loan
     * @return bool|mixed
     */
    public function getRenewFeeNew($loan) {
        if (empty($loan)) {
            return FALSE;
        }
        $now_time = date('Y-m-d H:i:s');
        $where = [
            'AND',
            ['loan_id' => $loan->loan_id],
            ['<=', 'start_time', $now_time],
            ['>=', 'end_time', $now_time]
        ];
        $renew = self::find()->where($where)->one();
        if (!empty($renew)) {
            return $renew->renew_fee;
        }
        return $loan->withdraw_fee + $loan->amount * 0.2;
    }

    /**
     * 查询当前借款是否有续期记录
     * @param $loan
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getRenew($loan_id, $now_date = '' ,$type=1) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return [];
        }
        if($type ==1){
            $renew = self::find()->where(['loan_id' => $loan_id, 'mark' => 2])->orderBy('id desc')->one();
            if (!empty($renew)) {
                return $renew;
            }
        }
       
        if (empty($now_date)) {
            $now_date = date('Y-m-d H:i:s');
        }
        $where = [
            'AND',
            ['loan_id' => $loan_id],
            ['<=', 'start_time', $now_date],
            ['>=', 'end_time', $now_date],
            ['mark' => 1]
        ];
        $renew = self::find()->where($where)->orderBy('id desc')->one();
        return $renew;
    }

    /**
     * 查询当前借款是否有续期记录
     * @param $loan
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getRenewOne($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return false;
        }
        $where = ['loan_id' => $loan_id];
        $renew = self::find()->where($where)->orderBy(['create_time' => 'DESC'])->one();
        return $renew;
    }

    /**
     * 添加状态为一的续期记录
     * @param $parent_loan
     * @param $renew
     * @param $end_date
     * @param $res
     * @return bool
     */
    public function addExtension($parent_loan, $renew, $end_date, $res) {
        if (empty($parent_loan) || empty($renew) || empty($end_date) || empty($res)) {
            return false;
        }
        $new_loan = User_loan::findOne($res);
        $renew_fee = $renew->renew_fee;
        $re = $renew->renew;
        if ($renew->mark == 2) {
            $renew_fee = $parent_loan->amount * 0.05 + $parent_loan->withdraw_fee;
            $re = 0.05;
        }
        $nowTime = date('Y-m-d H:i:s');
        $data = [
            'loan_id' => $res,
            'renew_fee' => $renew_fee,
            'user_id' => $parent_loan->user_id,
            'parent_loan_id' => $parent_loan->parent_loan_id,
            'mark' => 1,
            'type' => 1,
            'start_time' => date("Y-m-d H:i:s", strtotime("-6 day", strtotime($new_loan->end_date))),
            'end_time' => $end_date,
            'create_time' => $nowTime,
            'renew' => $re
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 受托债权推送
     */
    public function entrustloan($loanObj) {
        /*
         * 优化7天的数据
         * */
        if($loanObj->days==7){
            $days=56;
        }else{
            $days=$loanObj->days;
        }
        $data = [
            'loan_id' => $loanObj->loan_id,
            'user_id' => $loanObj->user_id,
            'amount' => $loanObj->amount,
            'days' => $days,
            'true_days' => $loanObj->days,
            'fee_day' => !empty($loanObj->start_date) ? $loanObj->start_date : date("Y-m-d 00:00:00"),
            'fee' => $loanObj->is_calculation == 1 ? $loanObj->interest_fee : $loanObj->withdraw_fee + $loanObj->interest_fee,
            'coupon_amount' => !empty($loanObj->coupon_amount) ? $loanObj->coupon_amount : 0,
            'repay_day' => !empty($loanObj->end_date) ? $loanObj->end_date : date("Y-m-d 00:00:00", strtotime("+$loanObj->days days")),
            'repay_type' => 1,
            'username' => $loanObj->user->realname,
            'mobile' => $loanObj->user->mobile,
            'identity' => $loanObj->user->identity,
            'company' => $loanObj->user->extend->company,
            'desc' => $loanObj->transDesc(),
            'yield' => '0.0005',
            'tag_type' => 3,
            'accountid' => $loanObj->accountid,
            'from' => 1,
            'total_callback_url' => Yii::$app->params['outmoneynotify'],
        ];
        $result = (new Apidepository())->entrustloan($data);
        if (!$result) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 受托支付申请
     * come_from,0:默认从renew模块发起的申请，1:从new模块发起的
     */
    public function entrustpay($loanObj,$bank_id,$source = 2,$come_from=0) {
        $from = $source == 1 ? 'weixin' : 'app';
        $data = [
            'loanId' => $loanObj->loan_id,
            'source' => 1,
            'accountId' => $loanObj->accountid,
            'idNo' => $loanObj->user->identity,
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $loanObj->user_id . '&from=' . $from,
            'notifyUrl' => Yii::$app->params['renewcunguan_notify'],
            'retUrl' => Yii::$app->params['renewcunguan_notify'] . '?loan_id=' . $loanObj->loan_id .'&bank_id='.$bank_id. '&source=' . $source.'&come_from='.$come_from,
            //'retUrl' => Yii::$app->params['renewcunguan_notify'] . '?loan_id=' . $loanObj->loan_id . '&source=' . $source,
        ];

        $result = (new Apidepository())->entrustpay($data);
        if (!$result) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 续期入口是否显示
     * 1普通展期 2免费受托展期 3合规展期
     * @param $loan_id
     * @return array
     * @author 王新龙
     * @date 2018/11/5 下午12:44
     */
    public function entry($loan_id){
        if(empty($loan_id)){
            return ['type' => 0];
        }
        $open = Keywords::renewalInspectOpen();
        if($open == 1){
            $o_renew_amount = $this->getRenew($loan_id);
            if(!empty($o_renew_amount) && $o_renew_amount->type == 3){
                return ['type' => 2];
            }
            if(!empty($o_renew_amount)){
                return ['type' => 1];
            }
        }else{
            $o_user_loan = (new User_loan())->getById($loan_id);
            if(empty($o_user_loan)){
                return ['type' => 0];
            }
            $is_inspect = (new RenewalInspect())->getByLoanId($loan_id);
            if(!empty($is_inspect)){
                return ['type' => 0];
            }
            $tiem = date('Y-m-d H:i:s');
            $time_in = date("Y-m-d H:i:s",strtotime("-5 day",strtotime($o_user_loan->end_date)));
            $over_time_in = date("Y-m-d H:i:s",strtotime("+3 day",strtotime($o_user_loan->end_date)));
            if($tiem > $time_in && $tiem < $over_time_in){
                return ['type' => 3];
            }
        }
        return ['type' => 0];
    }
}

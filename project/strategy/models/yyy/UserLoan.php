<?php

namespace app\models\yyy;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "yi_user_loan".
 * 一亿元借款记录表
 * @property string $loan_id
 * @property string $parent_loan_id
 * @property integer $number
 * @property integer $settle_type
 * @property string $user_id
 * @property string $loan_no
 * @property string $real_amount
 * @property string $amount
 * @property string $recharge_amount
 * @property string $credit_amount
 * @property string $current_amount
 * @property integer $days
 * @property string $start_date
 * @property string $end_date
 * @property string $open_start_date
 * @property string $open_end_date
 * @property integer $type
 * @property integer $status
 * @property integer $prome_status
 * @property string $interest_fee
 * @property string $desc
 * @property string $contract
 * @property string $contract_url
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 * @property string $repay_time
 * @property string $withdraw_fee
 * @property string $chase_amount
 * @property string $like_amount
 * @property string $collection_amount
 * @property string $coupon_amount
 * @property integer $is_push
 * @property integer $final_score
 * @property integer $repay_type
 * @property integer $business_type
 * @property string $withdraw_time
 * @property string $bank_id
 * @property integer $source
 * @property integer $is_calculation
 */
class UserLoan extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_loan_id', 'number', 'settle_type', 'user_id', 'days', 'type', 'status', 'prome_status', 'version', 'is_push', 'final_score', 'repay_type', 'business_type', 'bank_id', 'source', 'is_calculation'], 'integer'],
            [['user_id', 'amount', 'current_amount', 'bank_id'], 'required'],
            [['real_amount', 'amount', 'recharge_amount', 'credit_amount', 'current_amount', 'interest_fee', 'withdraw_fee', 'chase_amount', 'like_amount', 'collection_amount', 'coupon_amount'], 'number'],
            [['start_date', 'end_date', 'open_start_date', 'open_end_date', 'last_modify_time', 'create_time', 'repay_time', 'withdraw_time'], 'safe'],
            [['loan_no', 'contract'], 'string', 'max' => 64],
            [['desc'], 'string', 'max' => 1024],
            [['contract_url'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'loan_id' => '主键',
            'parent_loan_id' => '主借款ID',
            'number' => '续期次数',
            'settle_type' => '0：初始状态；1：还款结清；2：续期结清；3：续期中',
            'user_id' => '用户ID',
            'loan_no' => '借款编号',
            'real_amount' => '初始借款金额',
            'amount' => '借款金额',
            'recharge_amount' => '值充金额',
            'credit_amount' => '授信额度',
            'current_amount' => '已募集到金额',
            'days' => '借款天数',
            'start_date' => '起息日',
            'end_date' => '到期日',
            'open_start_date' => '募集开始日期',
            'open_end_date' => '募集结束日期',
            'type' => '借款类型:1先花宝；2普通用户',
            'status' => '借款状态：1初始；2通过；3驳回；4失效；5已提现；',
            'prome_status' => '模型初始状态',
            'interest_fee' => '借款利息总额',
            'desc' => '借款说明',
            'contract' => '合同号',
            'contract_url' => '合同存放地址',
            'last_modify_time' => '最后修改时间，对应状态变更时间',
            'create_time' => '创建时间',
            'version' => '乐观所版本号',
            'repay_time' => 'Repay Time',
            'withdraw_fee' => '提现手续费',
            'chase_amount' => '逾期费用',
            'like_amount' => '赞点减息总额',
            'collection_amount' => 'Collection Amount',
            'coupon_amount' => '优惠券金额',
            'is_push' => '是否推送未筹满通知',
            'final_score' => '同盾风险系数',
            'repay_type' => '1:线下;2线上,默认为1',
            'business_type' => '1:好友;2:好人卡;3:担保人',
            'withdraw_time' => '提现时间',
            'bank_id' => '提现银行卡ID',
            'source' => '借款来源',
            'is_calculation' => '1 新的计费方式 0 不变',
        ];
    }
    public function getUserLoanExtend() {
        return $this->hasOne(UserLoanExtend::className(), ['loan_id' => 'loan_id']);
    }
    public function getLoanInfo($postData)
    {
        return $this->find()->where($postData)->one();
    }

    public function getAllLoan($postData)
    {
        return $this->find()->where($postData)->all();
    }
    
    public function getThreeMcount($user_id,$loan_id)
    {
        $time = date("Y-m-d 00:00:00",strtotime('-89 days'));
        $where = ['and',['!=','loan_id',$loan_id],['>=','create_time',$time],['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]];
        return $this->find()->where($where)->count();
        
    }

    public function getSixMcount($user_id,$loan_id)
    {
        $time = date("Y-m-d 00:00:00",strtotime('-179 days'));
        $where = ['and',['!=','loan_id',$loan_id],['>=','create_time',$time],['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]];
        return $this->find()->where($where)->count();
         
    }
    //未结清的账单
    public function getUncleared($user_id)
    {
        if (empty($user_id)){
            return 0;
        }
        $where = [
            'and',
            ["=", "user_id", $user_id],
            ["in", "status", [6, 9, 11, 12, 13]],
            ["in", "business_type", [1,4]],
        ];
        return $this->find()->where($where)->count();

    }
    public function getBankEdit($where,$loan_id)
    {
        $last_where = ['and',$where,['in','status','8']];
        $this_where = ['and',$where,['loan_id' => $loan_id]];
        //获取最近一次成功的借款银行卡ID
        $last_loan = $this->find()->where($last_where)->select('bank_id,loan_id')->orderBy('loan_id DESC')->one();
        //获取本次借款的银行卡ID
        $this_loan = $this->find()->where($this_where)->select('bank_id,loan_id')->one();
        if ($last_loan->bank_id == $this_loan->bank_id) {
            return 0;
        }
        return 1;
    }

    public function getOneValue($user_id)
    {
        $last_day = date("Y-m-d 00:00:00");
        $where = ['and',['user_id'=>$user_id],['>=','create_time',$last_day]];
        $one_value = $this->find()->select('loan_id,user_id,loan_no')->where($where)->asArray()->all();
        $one = count($one_value);
        if ($one > 0) {
            Logger::dayLog('one_value', '借款一', json_encode($one_value));
        }
        return $one;
    }

    public function getSevenValue($user_id)
    {
        $last_week = date("Y-m-d 00:00:00",strtotime('-6 days'));
        $where = ['and',['user_id'=>$user_id],['>=','create_time',$last_week]];
        $seven_value = $this->find()->where($where)->count();
        return $seven_value;
    }

    public function getTotalCount($where)
    {
        $count = $this->find()->where($where)->groupBy('parent_loan_id')->count();
        return $count;
    }
    public function complexData($data)
    {
        $loan_id = $data['loan_id'];
        $user_id = $data['user_id'];
        $loan_create_time = $data['loan_create_time'];
        $create_time_now = substr($data['loan_create_time'], 0,10);
        // $time_step = date("Y-m-d 00:00:00",strtotime('-89 days',strtotime($loan_create_time)));
        $where = ['and',
            ['!=','loan_id',$loan_id],
            ['<','create_time',$loan_create_time],
            ['user_id'=>$user_id,'business_type'=>[1,4]],
        ];
        $loanAll = $this->find()->where($where)->all();
        $retData = [
            'last_succ_loan_create_time' => '1111-11-11 11:11:11',
            'mth1_app_cnt' => 0,
            'mth3_dlq14_num' => 0,
            'mth6_acp_num' => 0,
            'tot_prepmt_num' => 0,
            'tot_accept_num' => 0,
            'tot_dlq14_num' => 0,
        ];
        foreach ($loanAll as $key => $value) {
            if( $value->loan_id < $loan_id ){
                //借款状态
                $status = $value->status;
                $repay_time = substr($value['repay_time'], 0,10);
                $end_date = substr($value['end_date'], 0,10);
                //逾期时间
                $due_day = (int)((strtotime($repay_time)-strtotime($end_date))/(60*60*24));

                $create_time_old = substr($value['create_time'], 0,10);
                //据本次借款天数
                $loanTime = (int)((strtotime($create_time_now)-strtotime($create_time_old))/(60*60*24));
                //所有历史借款不能等于本次日期
                if ($loanTime > 0) {
                    //(据本次借款180天以内)最近一次成功借款的申请时间
                    if (in_array($status, [8,9,11,12,13]) && $loanTime < 180 && $value->create_time > $retData['last_succ_loan_create_time']) {
                        $retData['last_succ_loan_create_time'] = $value->create_time;
                    }
                    //(据本次借款30天内)申请借款次数
                    if ($loanTime < 30) {
                        $retData['mth1_app_cnt'] +=1;
                    }
                    //(据本次借款90天内)逾期14天以上的借款次数
                    if( $loanTime < 90 && in_array($status, [8,9,11,12,13]) && $due_day > 14) {
                        $retData['mth3_dlq14_num'] += 1;
                    }
                    //据本次借款180天内放款次数
                    if( $loanTime < 180 && in_array($status, [8,9])) {
                        $retData['mth6_acp_num'] += 1;
                    }
                    //(本次借款之前)历史提前还款次数
                    if (!empty($repay_time) && $due_day < 0) {
                        $retData['tot_prepmt_num'] += 1;
                    }
                    //本次借款之前成功放款次数
                    if (in_array($status, [8,9])) {
                        $retData['tot_accept_num'] += 1;
                    }
                    //本次借款之前逾期14天以上放款次数
                    if ($due_day > 14) {
                        $retData['tot_dlq14_num'] +=1;
                    }
                }
            }
        }
        return $retData;
    }

    public function getSuLoan($user_id){
        if (empty($user_id)){
            return false;
        }
        return self::find()->where(['user_id'=>$user_id, 'status'=>8])->orderBy("repay_time desc")->one();
    }

    public function getLoanOne($loan_id){
        if (empty($loan_id)){
            return false;
        }
        return self::find()->where(['loan_id'=>$loan_id])->one();
    }

    /**
     * 获取56天成功的产品
     * @param $user_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getSuLoanData($user_id, $create_time){
        if (empty($user_id)){
            return false;
        }
        if (empty($create_time)){
            $create_time = date("Y-m-d H:i:s");
        }
        $where_config = [
            'AND',
            ['=', 'user_id', $user_id],
            ['=', 'status', 8],
            ['=', 'days', 56],
            ['!=', 'settle_type', 2],
            [">=", 'create_time', date("Y")."-01-01"],
            ['<=', 'create_time', $create_time],
        ];
        return self::find()->where($where_config)->count();
    }

    public function getAllLoanByUids($user_ids){
        $loan_all = static::find()->where(['user_id'=>$user_ids])->count();
        return (int)$loan_all;
    }

    public function getHistroyBadStatus($user_ids){
        $userLoan = static::tableName();
        $userLoanFlows = UserLoanFlows::tableName();
        $res = static::find()
                ->select("{$userLoan}.loan_id,{$userLoan}.status,{$userLoan}.repay_time,{$userLoan}.last_modify_time,{$userLoan}.end_date,{$userLoanFlows}.admin_id,{$userLoanFlows}.loan_status")
                ->leftJoin($userLoanFlows,"{$userLoanFlows}.loan_id={$userLoan}.loan_id and {$userLoanFlows}.loan_status in(3,7)")
                ->where(
                    ['and',
                        ["{$userLoan}.user_id"=>$user_ids],
                        ["{$userLoan}.status"=>[3,7,8,9,11,12,13]]
                    ])
                ->limit(1000)
                ->asArray()
                ->all();
        if (empty($res)) {
            return [];
        }
        $analysisData = [
            'realadl_dlq14_ratio' => 0,
            'realadl_tot_dlq14_num' => 0,
            'realadl_tot_freject_num' => 0,
            'realadl_tot_reject_num' => 0,
            'realadl_tot_sreject_num' => 0,
            'realadl_wst_dlq_sts' => 0,
            'history_bad_status'=>0,
        ];
        $tmp_num = 0;
        try {
            foreach ($res as $value) {
                if ($value['status'] == 3){
                    $analysisData['realadl_tot_reject_num'] += 1;
                } elseif ($value['status'] == 7 && isset($value['admin_id']) && $value['admin_id'] == -1){
                    $analysisData['realadl_tot_reject_num'] += 1;
                    $analysisData['realadl_tot_sreject_num'] += 1;
                } elseif ($value['status'] == 7 && isset($value['admin_id']) && $value['admin_id'] == -2){
                    $analysisData['realadl_tot_reject_num'] += 1;
                    $analysisData['realadl_tot_freject_num'] += 1;
                } elseif ($value['status'] == 7 && isset($value['admin_id']) && $value['admin_id'] > 0){
                    $analysisData['realadl_tot_reject_num'] += 1;
                } elseif ($value['status'] ==9){
                       continue; 
                } elseif ($value['status'] ==8){
                    $date = empty($value['repay_time']) ? $value['last_modify_time'] : $value['repay_time'];
                    $diffDay = ((int)((strtotime($date)-strtotime($value['end_date']))/(60*60*24)))-1;
                    if ($diffDay > 14) {
                        $analysisData['realadl_tot_dlq14_num'] += 1;
                    }
                    if ($analysisData['realadl_wst_dlq_sts'] < $diffDay) {
                        $analysisData['realadl_wst_dlq_sts'] = $diffDay;
                    }
                    $tmp_num ++;
                } elseif (in_array($value['status'],[11,12,13])){
                    $date = empty($value['repay_time']) ? date('Y-m-d H:i:s') : $value['repay_time'];
                    $diffDay = (int)((strtotime($date)-strtotime($value['end_date']))/(60*60*24));
                    if ($diffDay > 14) {
                        $analysisData['realadl_tot_dlq14_num'] += 1;
                    }
                    $tmp_num ++;
                    if ($analysisData['realadl_wst_dlq_sts'] < $diffDay) {
                        $analysisData['realadl_wst_dlq_sts'] = $diffDay;
                    }
                }else{
                    $analysisData['realadl_tot_reject_num'] += 1;
                }       
            }
            if ($tmp_num > 0) {
                $analysisData['realadl_dlq14_ratio'] = round($analysisData['realadl_tot_dlq14_num']/$tmp_num,2);
            }
            $analysisData['history_bad_status'] = $analysisData['realadl_wst_dlq_sts'];
        } catch (\Exception $e) {
            Logger::dayLog('userLoan/error', $e->getMessage());
        }
        return $analysisData;
    }
}

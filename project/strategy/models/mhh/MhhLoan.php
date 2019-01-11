<?php

namespace app\models\mhh;

use Yii;

/**
 * This is the model class for table "loan".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $loan_no
 * @property string $user_id
 * @property string $amount
 * @property string $need_repay_amount
 * @property string $repay_amount
 * @property integer $days
 * @property string $start_date
 * @property string $end_date
 * @property integer $status
 * @property string $interest_fee
 * @property string $desc
 * @property string $contract
 * @property string $contract_url
 * @property string $rate
 * @property string $withdraw_fee
 * @property integer $business_type
 * @property string $withdraw_time
 * @property string $bank_id
 * @property integer $come_from
 * @property integer $from_code
 * @property integer $is_calculation
 * @property string $apply_time
 * @property string $repay_time
 * @property string $modify_time
 * @property string $create_time
 * @property integer $version
 */
class MhhLoan extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'user_id', 'start_date', 'contract', 'contract_url', 'bank_id'], 'required'],
            [['loan_id', 'user_id', 'days', 'status', 'business_type', 'bank_id', 'come_from', 'from_code', 'is_calculation', 'version'], 'integer'],
            [['amount', 'need_repay_amount', 'repay_amount', 'interest_fee', 'rate', 'withdraw_fee'], 'number'],
            [['start_date', 'end_date', 'withdraw_time', 'apply_time', 'repay_time', 'modify_time', 'create_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 32],
            [['desc'], 'string', 'max' => 1024],
            [['contract'], 'string', 'max' => 20],
            [['contract_url'], 'string', 'max' => 128],
            [['loan_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'loan_id',
            'loan_no' => '借款编号',
            'user_id' => '借款用户ID',
            'amount' => '借款金额',
            'need_repay_amount' => '应还款金额',
            'repay_amount' => '已还款金额',
            'days' => '借款天数',
            'start_date' => '起息日',
            'end_date' => '到期日',
            'status' => '借款状态:1初始；3审核驳回；4失效；6审核通过；7出款驳回；；8已还款；9出款成功；10出款失败；11待确认还款；12还款异常(未还款逾期)；13 还款异常(部分还款 逾期)；14 超额还款(借款完成)；15 异常提现驳回；20出款中；23 发起提现；24 待出款；25 验卡失败；26 验卡成功；27 决策通过需购买保险；28 保险购买成功；29 保险购买失败驳回；30 债匹成功，用户体现；31 提现成功；32 提现失败',
            'interest_fee' => '借款利息总额',
            'desc' => '借款说明',
            'contract' => '合同编号',
            'contract_url' => '合同存放地址',
            'rate' => '该笔账单利率',
            'withdraw_fee' => '提现手续费',
            'business_type' => '业务类型: 1:有卡 2无卡',
            'withdraw_time' => '提现时间',
            'bank_id' => '提现银行卡ID',
            'come_from' => '设备来源:1 ios 2 android 3 微信 4 web ',
            'from_code' => '渠道来源 1 自有2一亿元  3  其他',
            'is_calculation' => '1 前置；2 后置 默认1',
            'apply_time' => '可发起提现时间，超过24小时未提现驳回借款',
            'repay_time' => '还款时间',
            'modify_time' => '最后修改时间',
            'create_time' => '创建时间',
            'version' => '乐观锁版本号',
        ];
    }

    public function getLoanInfo($postData)
    {
        return $this->find()->where($postData)->one();
    }

    public function getAllLoan($postData)
    {
        return $this->find()->where($postData)->all();
    }
    //三个月借款数
    public function getThreeMcount($user_id,$loan_id)
    {
        $time = date("Y-m-d 00:00:00",strtotime('-89 days'));
        $where = ['and',['!=','loan_id',$loan_id],['>=','create_time',$time],['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]];
        return $this->find()->where($where)->count();
        
    }
    //六个月借款数
    public function getSixMcount($user_id,$loan_id)
    {
        $time = date("Y-m-d 00:00:00",strtotime('-179 days'));
        $where = ['and',['!=','loan_id',$loan_id],['>=','create_time',$time],['user_id'=>$user_id,'status'=>[8,9,11,12,13],'business_type'=>[1,4]]];
        return $this->find()->where($where)->count();
         
    }

    /**
     * [complexData 获取7-14数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
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
}

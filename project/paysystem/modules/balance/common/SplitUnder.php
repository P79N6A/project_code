<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * 线下还款数据拆分
 *
 */
namespace app\modules\balance\common;

use app\modules\balance\models\PaymentDetails;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\balance\common\PaymentCommon;
use app\modules\balance\models\SplitUnderRepay;

use app\modules\balance\models\yyy\LoanRepay;
use app\modules\balance\models\yyy\UserLoan;
use app\modules\balance\models\yyy\User_remit_list;

set_time_limit(0);


class SplitUnder
{
    protected $oSplitUnderRepay;


    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oSplitUnderRepay = new SplitUnderRepay();
    }


    public  function runAll($start_time,$end_time) {
        $oLoanRepay = new LoanRepay();
        $dataList = $oLoanRepay->getUnderRepay($start_time,$end_time);
//        var_dump(count($dataList));die;
        return  $success =  $this->runCollection($dataList);
    }

    /**
     * 暂时五分钟跑一批:
     */
    public function runCollection($dataList) {
        //1 验证
        if (!$dataList) {
            return false;
        }

//        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
//        $ups = $this->oPaymentDetails->lockStatus($ids);    // 锁定出款接口的请求
//        #$ups = true;
//        if (!$ups) {
//            return false;
//        }

        //4 逐条处理
        //$total = count($dataList);
        $success = 0;
        foreach ($dataList as $oCollection) {
            $result = $this->doCollection($oCollection);
            if ($result) {
                $success++;
            }
        }
        logger::dayLog('Splict/Unders','runCollection','抓取成功条数：'.$success.',数据：',$ids);
        var_dump($success);die;
        //5 返回结果
        return $success;
    }
    /**
     * 处理单条拆账
     * @param object $oRemit
     * @return bool
     */
    public  function doCollection($oCollection) {

        //1 参数验证
        if (!$oCollection) {
            return false;
        }
//        //2 实例化 还款表
//        $oLoanRepay = new LoanRepay();
//        $loanRepayObj = $oLoanRepay->getOneByData(ArrayHelper::getValue($oCollection,'client_id'));
//        if(empty($loanRepayObj)){
//            $this->oPaymentDetails->saveOneCollectionStatus($oCollection,$this->oPaymentDetails->gStatus('STATUS_FAILURE'), "还款表查询失败");
//            logger::dayLog('Splict','doCollection/LoanRepay','还款表查询失败，数据:',$oCollection);
//            return false;
//        }
        $loanRepayObj = $oCollection;
        //实例化 借款表
        $oUserLoan = new UserLoan();
        $UserLoanObj = $oUserLoan->getOneByData(ArrayHelper::getValue($loanRepayObj,'loan_id'));

        if(empty($UserLoanObj)){
            logger::dayLog('Splict/UndersError','doCollection/UserLoan','借款表查询失败，数据:',$loanRepayObj);
            return false;
        }
        //实例化  实际出款表
        $oUserRemitList = new User_remit_list();
        $UserRemitListObj = $oUserRemitList->getOneByDataUnder(ArrayHelper::getValue($UserLoanObj,'parent_loan_id'));
        if(empty($UserRemitListObj)){
            logger::dayLog('Splict/UndersError','doCollection/User_remit_list','实际出款表查询失败，数据:',$UserLoanObj);
            return false;
        }
        $result = $this->formatDate($loanRepayObj,$UserLoanObj,$UserRemitListObj);
        //本账单总利息
        $result['total_interest'] = $this->totalInterest(ArrayHelper::getValue($result,'is_calculation',0),ArrayHelper::getValue($result,'jk_interest_fee',0),ArrayHelper::getValue($result,'withdraw_fee',0));
        $splitMoney = $this->billSplit($result,$loanRepayObj,$UserLoanObj);
        $result['split_principal'] = $splitMoney['principal'];
        $result['split_interest'] = $splitMoney['interest'];
        $result['split_fine'] = $splitMoney['fine'];
        $result['repay_total_money'] = $splitMoney['repay_total_money'];
        $oSplicU = new SplitUnderRepay();
        $re = $oSplicU->saveData($result);
        if($re){
            return true;
        }
        logger::dayLog('Splict/Unders','doCollection/save','保存失败，数据:',$result);
        return false;
    }


    /*
     *  格式化数据
     *  $loanRepayObj       还款数据
     *  $UserLoanObj        借款数据
     *  $UserRemitListObj   实际出账金额
     *  $oCollection        三方数据
     * */
    public function formatDate($loanRepayObj,$UserLoanObj,$UserRemitListObj){
        if(empty($loanRepayObj) || empty($UserLoanObj) || empty($UserRemitListObj) ){
            return false;
        }
        $new_time = date('Y-m-d H:i:s',time());
        $save_data = [
            'repay_id'             => ArrayHelper::getValue($loanRepayObj, 'repay_id',''),//还款订单号
            'user_id'            => ArrayHelper::getValue($loanRepayObj, 'user_id', ''), //用户id
            'loan_id'                => ArrayHelper::getValue($loanRepayObj, 'loan_id', ''), //借款订单（副）
            'paybill'                => ArrayHelper::getValue($loanRepayObj, 'paybill', ''), //支付流水号
            'repay_time'                => ArrayHelper::getValue($loanRepayObj, 'repay_time', ''), //支付流水号
            'bill_money' => ArrayHelper::getValue($loanRepayObj, 'actual_money', '0'),//还款金额

//   start_date     征用成为还款最后修改时间 财务点击确认时间
            'start_date'       => ArrayHelper::getValue($loanRepayObj, 'last_modify_time', '0'),//借款起息日

            'settle_amount'        => ArrayHelper::getValue($UserRemitListObj, 'settle_amount', 0), //出款表-实际出款金额
            'settle_fee'                  => ArrayHelper::getValue($UserRemitListObj, 'settle_fee', 0), //出款表-出款手续费
            'fund'          => ArrayHelper::getValue($UserRemitListObj, 'fund', 0), //出款表-资金方',
            'fund_party'         => $this->realFundParty(ArrayHelper::getValue($UserRemitListObj, 'fund', '0')), //出款资金方主体（1.小小黛朵2,.先花花）
            'jk_amount'            => ArrayHelper::getValue($UserLoanObj, 'amount', 0), //借款表-借款金额
            'jk_interest_fee'           => ArrayHelper::getValue($UserLoanObj, 'interest_fee', 0), //借款表-利息
            'jk_number'                => ArrayHelper::getValue($UserLoanObj, 'number', '0'), //续期（展期）次数
            'jk_settle_type'        => ArrayHelper::getValue($UserLoanObj, 'settle_type', 0), //续期（展期）状态；0：初始状态；1：还款结清；2：续期结清；3：续期中
            'parent_loan_id'            => ArrayHelper::getValue($UserLoanObj, 'parent_loan_id', ''), //借款id（主）
            'withdraw_fee'          => ArrayHelper::getValue($UserLoanObj, 'withdraw_fee', '0'), //借款服务费（is_calculation状态为1才会用到）
            'is_calculation'           => ArrayHelper::getValue($UserLoanObj, 'is_calculation', '0'), //1 新的计费方式 0 不变（服务费属于利息）
            'jk_status'               => ArrayHelper::getValue($UserLoanObj, 'status', '0'), //借款表-借款状态：1初始；2通过；3驳回；4失效；5已提现
            'days'       => ArrayHelper::getValue($UserLoanObj, 'days', '0'),//借款天数

//            'start_date'       => ArrayHelper::getValue($UserLoanObj, 'start_date', '0'),//借款起息日  在上面
            'end_date'       => ArrayHelper::getValue($UserLoanObj, 'end_date', '0'),//借款到期日

            #'total_interest'           => $getErrorTypes, //新计算后的利息--以这个为准（本订单的利息）
            #'split_interest'                  => $getErrorTyp3es, //本次还款拆分-利息
            #'split_principal'                   => ArrayHelper::getValue($file_data, 'uid', ''), //本次还款拆分-本金
            #'split_fine'                  => ($getErro22rTypes == 0) ? 1 : 2, //本次还款拆分-罚息
            'create_time'                 => $new_time, //创建时间
            'last_modify_time'       => $new_time,//最后更新时间
            'status'       => 0,//状态0为初始
            'remark'       => ArrayHelper::getValue($loanRepayObj, 'repay_id','repay_mark'),//备注

        ];
        return $save_data;
    }


    /*
     *  出款资金方主体
     *  公司主体  $fund int
     *  $result 1 小小黛朵  2 先花花
     * */
    public function realFundParty($fund){

        $result  = SplitUnderRepay::FUND_XHH;
        if(($fund == 1) || ($fund == 10)){
            $result  = SplitUnderRepay::FUND_XXDD;
        }
        if(($fund == 11)){
            $result  = SplitUnderRepay::FUND_PXHT;
        }
        return $result;
    }

    /**
     * 本账单总利息
     * @param $interest
     *  $is_calculation 是否新计算算方式  0不变  1为新
     *  $interest_fee   本账单原始利息
     *  $withdraw_fee   服务费
     */
    public function totalInterest($is_calculation,$interest_fee,$withdraw_fee){
        $result = 0;
        if($is_calculation == 0){
            return $result+$interest_fee;
        }
        return ($result+$interest_fee+$withdraw_fee);
    }

    /**
     *  本期账单拆分
     * $oneData  格式完成的拆账数据
     * $loanRepayObj    本次还款表的数据
     * $UserLoanObj     本次借款表的数据
     */
    public function billSplit($oneData,$loanRepayObj,$UserLoanObj){

        if(empty($oneData) || !is_array($oneData)){
            logger::dayLog('Splict/Unders','billSplit','拆账数据不存在。'.$oneData);
            return false;
        }

        $split_principal = 0;
        $split_interest = 0;
        $split_fine = 0;
        $repay_total_money = 0;

        $settle_amount = ArrayHelper::getValue($oneData,'settle_amount'); //出款金额   借款金额
        $total_interest = ArrayHelper::getValue($oneData,'total_interest');  // 利息
        $bill_money = ArrayHelper::getValue($oneData,'bill_money'); //本次还款金额   先算成 包含手续费也就是用户还款的所有金额
        //$principal_and_interest = $settle_amount+$total_interest;  //本金加利息

        //实例化 借款表 查出所有的借款 考虑到展期的问题
        $oUserLoan = new UserLoan();
        $all_loan_id = $oUserLoan->getAllByData(ArrayHelper::getValue($UserLoanObj,'parent_loan_id'));
        $loan_ids = ArrayHelper::getColumn($all_loan_id, 'loan_id');
        //查询本账单之前是否还款还过
        $oLoanRepay = new LoanRepay();
        $recordList = $oLoanRepay->getAllRecord($loan_ids,ArrayHelper::getValue($loanRepayObj,'repay_time'));
        //本次为第一次还款
        if(empty($recordList)){
            //还款大于本金
            if($bill_money >= $settle_amount){
                $split_principal = $settle_amount;
                //还款-本金  >= 利息
                if(($bill_money - $settle_amount) >= $total_interest){
                    $split_interest = $total_interest;
                    $split_fine = $bill_money-$settle_amount-$total_interest;
                    return [
                        'principal'=>$split_principal,
                        'interest'=>$split_interest,
                        'fine'=>$split_fine,
                        'repay_total_money'=>$repay_total_money,
                    ];
                }
                $split_interest = $bill_money -$settle_amount;
                return [
                    'principal'=>$split_principal,
                    'interest'=>$split_interest,
                    'fine'=>$split_fine,
                    'repay_total_money'=>$repay_total_money,
                ];
            }
            $split_principal = $bill_money;

            return [
                'principal'=>$split_principal,
                'interest'=>$split_interest,
                'fine'=>$split_fine,
                'repay_total_money'=>$repay_total_money,
            ];

        }

        $repayment_total_principal = 0; //之前还款 本金总金额
        $repayment_total_interest = 0; //之前还款 利息总金额
        //获取已经还清的  本金和利息
        $repay_data = $this->Reimbursement($recordList,$settle_amount,$total_interest);
        $repay_total_money = $repay_total_money + ArrayHelper::getValue($repay_data,'repay_total_money',0);
        $repayment_total_principal = $repayment_total_principal + ArrayHelper::getValue($repay_data,'principal',0);
        $repayment_total_interest = $repayment_total_interest + ArrayHelper::getValue($repay_data,'interest',0);

        //本金已经还完  总本金还款  大于等于  本金
        if($repayment_total_principal == $settle_amount){
            //利息已还完
            if($repayment_total_interest == $total_interest){
                $split_fine = $bill_money;
                return [
                    'principal'=>$split_principal,
                    'interest'=>$split_interest,
                    'fine'=>$split_fine,
                    'repay_total_money'=>$repay_total_money,
                ];
            }

            //还款大于利息
            if($bill_money >= $total_interest){
                $split_interest = $total_interest;
                $split_fine = ($bill_money - $total_interest);
                return [
                    'principal'=>$split_principal,
                    'interest'=>$split_interest,
                    'fine'=>$split_fine,
                    'repay_total_money'=>$repay_total_money,
                ];
            }
            $split_interest = $bill_money;
            return [
                'principal'=>$split_principal,
                'interest'=>$split_interest,
                'fine'=>$split_fine,
                'repay_total_money'=>$repay_total_money,
            ];

        }

        //还款  大于等于  未还本金
        if($bill_money >= ($settle_amount-$repayment_total_principal)){
            $split_principal = ($settle_amount-$repayment_total_principal);

            //（还款-未还本金）>= 利息
            if(($bill_money-($settle_amount-$repayment_total_principal)) >= $total_interest){
                $split_interest = $total_interest;
                $split_fine = $bill_money - ($settle_amount-$repayment_total_principal) -$total_interest;
                return [
                    'principal'=>$split_principal,
                    'interest'=>$split_interest,
                    'fine'=>$split_fine,
                    'repay_total_money'=>$repay_total_money,
                ];
            }
            $split_interest = $bill_money-($settle_amount-$repayment_total_principal);
            return [
                'principal'=>$split_principal,
                'interest'=>$split_interest,
                'fine'=>$split_fine,
                'repay_total_money'=>$repay_total_money,
            ];
        }
        $split_principal = $bill_money;
        return [
            'principal'=>$split_principal,
            'interest'=>$split_interest,
            'fine'=>$split_fine,
            'repay_total_money'=>$repay_total_money,
        ];
    }

    /**
     * 获取已经还款的本金
     * @param $oCollection
     * @return bool
     * $principal  总本金
     * $total_interest  总利息
     * $list    已经还款的数据
     */
    public function Reimbursement($list,$principal,$total_interest){
        $total_amount = 0;      //已还的总金额
        $repay_interest =0;     //已经还款的利息
        $repay_principal = 0;   //已经还款的本金
        foreach($list as $v){
            $total_amount = $total_amount + ArrayHelper::getValue($v,'actual_money',0);
        }
        //已还款 大于本金
        if($total_amount >= $principal){
            $repay_principal = $repay_principal +  $principal;
            if(($total_amount-$repay_principal) >= $total_interest){
                $repay_interest = $total_interest;
                return ['principal'=>$repay_principal,'interest'=>$repay_interest,'repay_total_money'=>$total_amount];
            }
            $repay_interest = $total_amount-$repay_principal;
            return ['principal'=>$repay_principal,'interest'=>$repay_interest,'repay_total_money'=>$total_amount];
        }
        $repay_principal = $total_amount;
        return ['principal'=>$repay_principal,'interest'=>$repay_interest,'repay_total_money'=>$total_amount];
    }








}
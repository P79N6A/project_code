<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/6
 * Time: 14:21
 *      新版拆账  主要数据来源从放款开始
 */
namespace app\modules\balance\common;

use app\modules\balance\models\PaymentDetails;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\balance\common\PaymentCommon;
use app\modules\balance\models\SplitBusiness;
use app\modules\balance\models\yyy\LoanRepay;
use app\modules\balance\models\yyy\UserLoan;
use app\modules\balance\models\yyy\User_remit_list;

set_time_limit(0);

class SplitNew
{
    protected $oPaymentDetails;
    protected $oSplitBusiness;


    /**
     * 初始化接口
     */
    public function __construct() {
//        $this->oPaymentDetails = new PaymentDetails();
//        $this->oSplitBusiness = new SplitBusiness();
    }


    public  function runAll($start_time,$end_time) {
        $oUserRemitList = new User_remit_list();
        $dataList = $oUserRemitList->getRequestList($start_time,$end_time);
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

        //2 锁定状态为处理中
       $ids = ArrayHelper::getColumn($dataList, 'id');
//        $ups = $this->oPaymentDetails->lockStatus($ids);    // 锁定出款接口的请求
//        #$ups = true;
//        if (!$ups) {
//            return false;
//        }

        //4 逐条处理
        //$total = count($dataList);
        $success = 0;
        $resultData = [];
        foreach ($dataList as $oCollection) {
            $result = $this->getRepayInfo($oCollection);
            foreach($result as $k=>$v){
                foreach($v as $kk=>$vv){
                    if(empty($resultData[$k][$kk])){
                        $resultData[$k][$kk]['split_principal'] = $vv['split_principal'];
                        $resultData[$k][$kk]['split_interest'] = $vv['split_interest'];
                        $resultData[$k][$kk]['split_fine'] = $vv['split_fine'];
                        $resultData[$k][$kk]['is_overdue'] = $vv['is_overdue'];
                        $success++;
                    }else{
                        $resultData[$k][$kk]['split_principal'] += $vv['split_principal'];
                        $resultData[$k][$kk]['split_interest'] += $vv['split_interest'];
                        $resultData[$k][$kk]['split_fine'] += $vv['split_fine'];
                        $success++;
                    }
                }
//            if(!empty($result['2018-08-07'])){
//                print_r($result);die;
//            }
//                print_R($resultData);die;
            }
//            if ($result) {
//                $resultData = array_merge($resultData,$result);
////                $resultData[]=$result;
////                $this->oPaymentDetails->saveOneCollectionStatus($oCollection,$this->oPaymentDetails->gStatus('STATUS_SUCCESS'),'');
//                $success++;
//            }
        }
        echo $success;
        echo json_encode($resultData);die;
//        $this->downlist_xls($resultData);die;
        print_r($resultData);die;
        logger::dayLog('Splict','runCollection','抓取成功条数：'.$success.',数据：',$ids);
        var_dump($success);die;
        //5 返回结果
        return $success;
    }

    /**
     * 根据放款查询 借款表，然后获取所有的还款
     * @param $oRemitInfo
     */
    public function getRepayInfo($oRemitInfo){
        //实例化 借款表
        $oUserLoan = new UserLoan();
        $UserLoanObj = $oUserLoan->getAllByData(ArrayHelper::getValue($oRemitInfo,'loan_id'));
//        var_dump($UserLoanObj);die;
        $result = [];
        foreach($UserLoanObj as $k=>$v){
//            var_dump($v);die;
            $oLoanRepay = new LoanRepay();
            $loanRepayObj = $oLoanRepay->getLoanIdByData(ArrayHelper::getValue($v,'loan_id'));
            $res = $this->doCollection($loanRepayObj,$oRemitInfo);
//            if($res['bill_time']  == '2018-08-10'){
//                print_r($res);die;
//            }
            if(empty($result[$res['bill_time']][$res['is_overdue']])){
                $result[$res['bill_time']][$res['is_overdue']]['split_principal'] = $res['split_principal'];
                $result[$res['bill_time']][$res['is_overdue']]['split_interest'] = $res['split_interest'];
                $result[$res['bill_time']][$res['is_overdue']]['split_fine'] = $res['split_fine'];
                $result[$res['bill_time']][$res['is_overdue']]['is_overdue'] = $res['is_overdue'];
            }else{
                $result[$res['bill_time']][$res['is_overdue']]['split_principal'] += $res['split_principal'];
                $result[$res['bill_time']][$res['is_overdue']]['split_interest'] += $res['split_interest'];
                $result[$res['bill_time']][$res['is_overdue']]['split_fine'] += $res['split_fine'];
            }
//            if(!empty($result['2018-08-10'])){
//                print_r($result);die;
//            }
        }
//        print_R($result);die;
        return $result;

    }


    public function getRepayInfos(){
        //实例化 还款表
        $oLoanRepay = new LoanRepay();

        $loanRepayObj = $oLoanRepay->getLoanIdByData();
//        var_dump($loanRepayObj);die;
//            if($res['bill_time']  == '2018-08-10'){
//                print_r($res);die;
//            }
//        $res = $this->doCollection($loanRepayObj,$oCollection=null);
        foreach($loanRepayObj as $k=>$v){
//            var_dump($v);die;
            $res = $this->doCollection($v,$oCollection=null);
            if(empty($result[$res['bill_time']][$res['is_overdue']])){
                $result[$res['bill_time']][$res['is_overdue']]['split_principal'] = $res['split_principal'];
                $result[$res['bill_time']][$res['is_overdue']]['split_interest'] = $res['split_interest'];
                $result[$res['bill_time']][$res['is_overdue']]['split_fine'] = $res['split_fine'];
                $result[$res['bill_time']][$res['is_overdue']]['coupon_amount'] = $res['coupon_amount'];
                $result[$res['bill_time']][$res['is_overdue']]['is_overdue'] = $res['is_overdue'];
            }else{
                $result[$res['bill_time']][$res['is_overdue']]['split_principal'] += $res['split_principal'];
                $result[$res['bill_time']][$res['is_overdue']]['split_interest'] += $res['split_interest'];
                $result[$res['bill_time']][$res['is_overdue']]['split_fine'] += $res['split_fine'];
                $result[$res['bill_time']][$res['is_overdue']]['coupon_amount'] += $res['coupon_amount'];
            }
        }

//            if(!empty($result['2018-08-10'])){
//                print_r($result);die;
//            }
//        print_R($result);die;
        echo json_encode($result);die;
        return $result;

    }

    /**
     * 处理单条拆账
     * @param object $oRemit
     * @return bool
     */
    public  function doCollection($loanRepayObj,$oCollection) {
        //1 参数验证
        if (!$loanRepayObj) {
            return false;
        }
        //2 实例化 还款表
//        $oLoanRepay = new LoanRepay();
//        $loanRepayObj = $oLoanRepay->getOneByData(ArrayHelper::getValue($oCollection,'client_id'));
//        if(empty($loanRepayObj)){
//            $this->oPaymentDetails->saveOneCollectionStatus($oCollection,$this->oPaymentDetails->gStatus('STATUS_FAILURE'), "还款表查询失败");
//            logger::dayLog('Splict','doCollection/LoanRepay','还款表查询失败，数据:',$oCollection);
//            return false;
//        }
//        var_dump($loanRepayObj);die;
        //实例化 借款表
//        var_dump(ArrayHelper::getValue($loanRepayObj,'loan_id'));die;
        $oUserLoan = new UserLoan();
        $UserLoanObj = $oUserLoan->getOneByData(ArrayHelper::getValue($loanRepayObj,'loan_id','0'));
//        var_dump(ArrayHelper::getValue($UserLoanObj,'coupon_amount','0'));die;
        if(empty($UserLoanObj)){
//            $this->oPaymentDetails->saveOneCollectionStatus($oCollection,$this->oPaymentDetails->gStatus('STATUS_FAILURE'), "借款表查询失败");
            logger::dayLog('Splict','doCollection/UserLoan','借款表查询失败，数据:',$loanRepayObj);
            return false;
        }
        //实例化  实际出款表
        $oUserRemitList = new User_remit_list();
        $UserRemitListObj = $oUserRemitList->getOneByData(ArrayHelper::getValue($UserLoanObj,'parent_loan_id','0'));

        if(empty($UserRemitListObj)){
//            $this->oPaymentDetails->saveOneCollectionStatus($oCollection,$this->oPaymentDetails->gStatus('STATUS_FAILURE'), "实际出款表查询失败");
            logger::dayLog('Splict','doCollection/User_remit_list','实际出款表查询失败，数据:',$UserLoanObj);
            return false;
        }
        $result = $this->formatDate($loanRepayObj,$UserLoanObj,$UserRemitListObj,$oCollection);

//        print_R($result);die;
        //本账单总利息
        $result['total_interest'] = $this->totalInterest(ArrayHelper::getValue($result,'is_calculation',0),ArrayHelper::getValue($result,'jk_interest_fee',0),ArrayHelper::getValue($result,'withdraw_fee',0));
        $splitMoney = $this->billSplit($result,$loanRepayObj,$UserLoanObj);
        $result['split_principal'] = $splitMoney['principal'];
        $result['split_interest'] = $splitMoney['interest'];
        $result['split_fine'] = $splitMoney['fine'];
        $result['coupon_amount'] = ArrayHelper::getValue($result,'coupon_amount','0');  //优惠卷
        $result['repay_total_money'] = $splitMoney['repay_total_money'];    //之前还款总金额
        if($result['last_modify_time'] <= $result['end_date']){
            $result['is_overdue'] = 0;//未逾期
        }else{
            $result['is_overdue'] = 1;//逾期
        }

//        print_r($result);die;
        return $result;
        print_R($result);die;
        $oSplicB = new SplitBusiness();
        $re = $oSplicB->saveData($result);
        if($re){
            return true;
        }
//        $this->oPaymentDetails->saveOneCollectionStatus($oCollection,$this->oPaymentDetails->gStatus('STATUS_FAILURE'), "保存失败");
        logger::dayLog('Splict','doCollection/save','保存失败，数据:',$result);
        return false;
    }


    /*
     *  格式化数据
     *  $loanRepayObj       还款数据
     *  $UserLoanObj        借款数据
     *  $UserRemitListObj   实际出账金额
     *  $oCollection        三方数据
     * */
    public function formatDate($loanRepayObj,$UserLoanObj,$UserRemitListObj,$oCollection){
        if(empty($loanRepayObj) || empty($UserLoanObj) || empty($UserRemitListObj || empty($oCollection))){
            return false;
        }

        $coupon_amount = ArrayHelper::getValue($loanRepayObj,'coupon_amount','0');    //本次还款使用优惠卷金额 没有使用将是null
        if(empty($coupon_amount)){
            $coupon_amount = 0;
        }

        $new_time = date('Y-m-d H:i:s',time());
        $bill_time =date('Y-m-d',strtotime(ArrayHelper::getValue($loanRepayObj, 'last_modify_time', 0))) ;
        $save_data = [
            'days'             => ArrayHelper::getValue($UserLoanObj, 'days',''),//借款期限
            'start_date'             => ArrayHelper::getValue($UserLoanObj, 'start_date',''),//起息日
            'end_date'             => ArrayHelper::getValue($UserLoanObj, 'end_date',''),//应还款日期
            'last_modify_time'             => ArrayHelper::getValue($loanRepayObj, 'last_modify_time',''),//还款时间
            'repay_id'             => ArrayHelper::getValue($loanRepayObj, 'repay_id',''),//还款订单号
            'user_id'            => ArrayHelper::getValue($loanRepayObj, 'user_id', ''), //用户id
            'loan_id'                => ArrayHelper::getValue($loanRepayObj, 'loan_id', ''), //借款订单（副）
            'settle_amount'        => ArrayHelper::getValue($UserRemitListObj, 'settle_amount', 0), //出款表-实际出款金额
            'settle_fee'                  => ArrayHelper::getValue($UserRemitListObj, 'settle_fee', 0), //出款表-出款手续费
            'fund'          => ArrayHelper::getValue($UserRemitListObj, 'fund', 0), //出款表-资金方',
            'fund_party'         => $this->realFundParty(ArrayHelper::getValue($UserRemitListObj, 'fund', '0')), //出款资金方主体（1.小小黛朵2,.先花花）
            'jk_amount'            => ArrayHelper::getValue($UserLoanObj, 'amount', 0), //借款表-借款金额
            'jk_interest_fee'           => ArrayHelper::getValue($UserLoanObj, 'interest_fee', 0), //借款表-利息
//            'jk_number'                => ArrayHelper::getValue($UserLoanObj, 'number', '0'), //续期（展期）次数
//            'jk_settle_type'        => ArrayHelper::getValue($UserLoanObj, 'settle_type', 0), //续期（展期）状态；0：初始状态；1：还款结清；2：续期结清；3：续期中
            'parent_loan_id'            => ArrayHelper::getValue($UserLoanObj, 'parent_loan_id', ''), //借款id（主）
            'withdraw_fee'          => ArrayHelper::getValue($UserLoanObj, 'withdraw_fee', '0'), //借款服务费（is_calculation状态为1才会用到）
            'is_calculation'           => ArrayHelper::getValue($UserLoanObj, 'is_calculation', '0'), //1 新的计费方式 0 不变（服务费属于利息）
//            'jk_status'               => ArrayHelper::getValue($UserLoanObj, 'status', '0'), //借款表-借款状态：1初始；2通过；3驳回；4失效；5已提现
//            'bill_service_charge'       => 0,#ArrayHelper::getValue($UserRemitListObj, 'settle_fee', 0), //三方手续费
            'bill_money'           =>ArrayHelper::getValue($loanRepayObj, 'actual_money', 0), //三方金额
            'bill_time'                => $bill_time,#ArrayHelper::getValue($loanRepayObj, 'last_data', ''), //第三账单日期（以第三方时间为准）
//            'total_interest'           => $getErrorTypes, //新计算后的利息--以这个为准（本订单的利息）
            #'split_interest'                  => $getErrorTyp3es, //本次还款拆分-利息
            #'split_principal'                   => ArrayHelper::getValue($file_data, 'uid', ''), //本次还款拆分-本金
            #'split_fine'                  => ($getErro22rTypes == 0) ? 1 : 2, //本次还款拆分-罚息
            'create_time'                 => $new_time, //创建时间
            'coupon_amount'                 => $coupon_amount, //创建时间
//            'last_modify_time'       => $new_time,//最后更新时间
//            'status'       => 0,//状态0为初始
//            'remark'       => '',//备注
//            'channel_id'       =>0,# ArrayHelper::getValue($oCollection, 'channel_id', '0'),//商编号id
//            'aid'       =>0,# ArrayHelper::getValue($oCollection, 'aid', '0'),//应用id
//            'mechart_num'       => 0,#ArrayHelper::getValue($oCollection, 'series', '0'),//商编号
//            'return_channel'       =>0,# ArrayHelper::getValue($oCollection, 'return_channel', '0'),//商编号
        ];
        return $save_data;
    }


    /*
     *  出款资金方主体
     *  公司主体  $fund int
     *  $result 1 小小黛朵  2 先花花
     * */
    public function realFundParty($fund){

        $result  = SplitBusiness::FUND_XHH;
        if(($fund == 1) || ($fund == 10)){
            $result  = SplitBusiness::FUND_XXDD;
        }
        if(($fund == 11)){
            $result  = SplitBusiness::FUND_PXHT;
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
            logger::dayLog('Splict','billSplit','拆账数据不存在。'.$oneData);
            return false;
        }
        $split_principal = 0;       //本金
        $split_interest = 0;    //利息
        $split_fine = 0;    //罚息
        $repay_total_money = 0;

        $settle_amount = ArrayHelper::getValue($oneData,'settle_amount'); //出款金额   借款金额  本金
        //先将优惠卷从本金中减出来
        $settle_amount = bcsub($settle_amount,ArrayHelper::getValue($oneData,'coupon_amount','0'),2);
        if($settle_amount<=0){
            $settle_amount=0;
        }
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
                if(bcsub($bill_money , $settle_amount,2) >= $total_interest){
                    $split_interest = $total_interest;
                    $split_fine = bcsub($bill_money,bcadd($settle_amount,$total_interest,2),2);
                    return [
                        'principal'=>$split_principal,
                        'interest'=>$split_interest,
                        'fine'=>$split_fine,
                        'repay_total_money'=>$repay_total_money,
                    ];
                }
                $split_interest = bcsub($bill_money ,$settle_amount,2);
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
        $repay_total_money = bcadd($repay_total_money , ArrayHelper::getValue($repay_data,'repay_total_money',0),2);
        $repayment_total_principal = bcadd($repayment_total_principal , ArrayHelper::getValue($repay_data,'principal',0),2);
        $repayment_total_interest = bcadd($repayment_total_interest , ArrayHelper::getValue($repay_data,'interest',0),2);

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
                $split_fine = bcsub($bill_money , $total_interest,2);
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
        if($bill_money >= bcsub($settle_amount,$repayment_total_principal,2)){
            $split_principal = bcsub($settle_amount,$repayment_total_principal,2);

            //（还款-未还本金）>= 利息
            if(bcsub($bill_money,bcsub($settle_amount,$repayment_total_principal,2),2) >= $total_interest){
                $split_interest = $total_interest;
                $split_fine = bcsub(bcsub($bill_money , bcsub($settle_amount,$repayment_total_principal,2),2) ,$total_interest,2);
                return [
                    'principal'=>$split_principal,
                    'interest'=>$split_interest,
                    'fine'=>$split_fine,
                    'repay_total_money'=>$repay_total_money,
                ];
            }
            $split_interest = bcsub($bill_money,bcsub($settle_amount,$repayment_total_principal,2),2);
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


    /*
     *  智融钥匙  添加数据
     * */
    public function doZrys($oCollection){
        $result = $this->formatZrysDate($oCollection);
        //本账单总利息
        $oSplicB = new SplitBusiness();
        $re = $oSplicB->saveData($result);
        if($re){
            return true;
        }
        $this->oPaymentDetails->saveOneCollectionStatus($oCollection,$this->oPaymentDetails->gStatus('STATUS_FAILURE'), "保存失败");
        logger::dayLog('Splict','doCollection/save','保存失败，数据:',$result);
        return false;
    }


    /**
     *  智融钥匙 数据格式处理
     */
    public function formatZrysDate($oCollection){
        if( empty($oCollection)){
            return false;
        }
        $new_time = date('Y-m-d H:i:s',time());
        $save_data = [
            'repay_id'             => ArrayHelper::getValue($oCollection, 'client_id',''),//还款订单号
            'user_id'            => 0, //用户id
            'loan_id'                => 0, //借款订单（副）
            'settle_amount'        => 0, //出款表-实际出款金额
            'settle_fee'                  => 0, //出款表-出款手续费
            'fund'          => 0, //出款表-资金方',
            'fund_party'         => SplitBusiness::FUND_ZRYS, //出款资金方主体（1.小小黛朵2,.先花花）
            'jk_amount'            => 0, //借款表-借款金额
            'jk_interest_fee'           => 0, //借款表-利息
            'jk_number'                => 0, //续期（展期）次数
            'jk_settle_type'        => 0, //续期（展期）状态；0：初始状态；1：还款结清；2：续期结清；3：续期中
            'parent_loan_id'            => 0, //借款id（主）
            'withdraw_fee'          => 0, //借款服务费（is_calculation状态为1才会用到）
            'is_calculation'           => 0, //1 新的计费方式 0 不变（服务费属于利息）
            'jk_status'               => 0, //借款表-借款状态：1初始；2通过；3驳回；4失效；5已提现
            'bill_service_charge'       => ArrayHelper::getValue($oCollection, 'settle_fee', 0), //三方手续费
            'bill_money'           => ArrayHelper::getValue($oCollection, 'amount', 0), //三方金额
            'bill_time'                => ArrayHelper::getValue($oCollection, 'payment_date', ''), //第三账单日期（以第三方时间为准）
            'total_interest'           => 0, //新计算后的利息--以这个为准（本订单的利息）
            'split_interest'                  => 0, //本次还款拆分-利息
            'split_principal'                   => 0, //本次还款拆分-本金
            'split_fine'                  => 0, //本次还款拆分-罚息
            'create_time'                 => $new_time, //创建时间
            'last_modify_time'       => $new_time,//最后更新时间
            'status'       => 0,//状态0为初始
            'remark'       => '',//备注
            'channel_id'       => ArrayHelper::getValue($oCollection, 'channel_id', ''),//商编号id
            'aid'       => ArrayHelper::getValue($oCollection, 'aid', '0'),//应用id
            'mechart_num'       => ArrayHelper::getValue($oCollection, 'series', ''),//商编号
            'return_channel'       => ArrayHelper::getValue($oCollection, 'return_channel', '0'),//商编号
        ];
        return $save_data;
    }


//
//* 下载成功对账成功数据
//* @param $orderData
//* @throws \Exception
//*/
    protected function downlist_xls($orderData) {
        $icount = count($orderData);
        // 创建一个处理对象实例
        $objExcel = new \PHPExcel();
        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        for($a = 0; $a <= 13; $a ++){
            $chr_asc = 65 + $a;
            $objActSheet->getColumnDimension(chr($chr_asc))->setWidth(30);
        }
        $objActSheet->setCellValue('A1', '序号');
        $objActSheet->setCellValue('B1', '借款天数');
        $objActSheet->setCellValue('C1', '起息日');
        $objActSheet->setCellValue('D1', '到期日');
        $objActSheet->setCellValue('E1', '最后修改时间（还款时间）');
        $objActSheet->setCellValue('F1', '还款单号');
        $objActSheet->setCellValue('G1', '用户id');
        $objActSheet->setCellValue('H1', '出款金额');
        $objActSheet->setCellValue('I1', '出款手续费');
        $objActSheet->setCellValue('J1', '出款公司主体');
        $objActSheet->setCellValue('K1', '实际公司主体');
        $objActSheet->setCellValue('L1', '借款总额');
        $objActSheet->setCellValue('M1', '借款预计总利息');
        $objActSheet->setCellValue('N1', '主借款id');
        $objActSheet->setCellValue('O1', '服务费');
        $objActSheet->setCellValue('P1', '是否计算服务费');
        $objActSheet->setCellValue('Q1', '总还款金额');
        $objActSheet->setCellValue('R1', '还款时间');
        $objActSheet->setCellValue('S1', '本数据创建时间');
        $objActSheet->setCellValue('T1', '预计总利息');
        $objActSheet->setCellValue('U1', '拆分--本金');
        $objActSheet->setCellValue('V1', '拆分--利息');
        $objActSheet->setCellValue('W1', '拆分--罚息');
        $objActSheet->setCellValue('X1', '拆分--借款，本次还款之前的总还款');
        $num = 0;
        for ($i = 0; $i < $icount; $i++) {
            $num ++;
            $data_set = $orderData[$i];
            //时间计算
            $objActSheet->setCellValue('A' . ( $i + 2), $i+1);//公司主体
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($data_set, 'days')); //借款天数
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($data_set, 'start_date'));//起息日
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($data_set, 'end_date'));//到期日
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($data_set, 'last_modify_time'));//利息
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($data_set, 'repay_id'));//滞纳金
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($data_set, 'user_id'));//展期服务费
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($data_set, 'loan_id'));//减免金额
            $objActSheet->setCellValue('I' . ( $i + 2), ArrayHelper::getValue($data_set, 'settle_amount'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'settle_fee'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'fund'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'fund_party'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'parent_loan_id'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'withdraw_fee'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'is_calculation'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'bill_money'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'bill_time'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'create_time'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'total_interest'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'split_principal'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'split_interest'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'split_fine'));//手续费
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'repay_total_money'));//手续费



        }
        $outputFileName = date('Y-m-d', time())  . "账目月账单统计" . ".xls";
        //到文件
        //$objWriter->save($outputFileName);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outputFileName . '"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }


}


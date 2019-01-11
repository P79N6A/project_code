<?php
/**
 * 逾期已收统计
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 9:53
 */
namespace app\modules\balance\controllers;
use app\modules\balance\common\COverdue;
use app\modules\balance\models\yyy\LoanRepay;
use app\modules\balance\models\yyy\OverdueLoan;
use app\modules\balance\models\yyy\Renew_amount;
use app\modules\balance\models\yyy\RenewalPaymentRecord;
use app\modules\balance\models\yyy\UserLoan;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ReceivedcountController extends  AdminController
{
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    public function actionList()
    {
        $getData = $this->get();

        //业务类型
        $debtType = $this->debtType();

        //分期类型
        $types_of_stages = $this->typesOfStages();

        //资金方
        $capital_side = $this->capitalSide();

        //出款方式
        //$way_of_payment = $this->wayOfPayment();

        //
        $overdueType = $this->overdueType();
        $condition = [
            'loan_id'           => ArrayHelper::getValue($getData, 'loan_id'), //借款编号
            'days'              => ArrayHelper::getValue($getData, 'days'), //业务类型
            'overdue_type'      => ArrayHelper::getValue($getData, 'overdue_type'), //逾期类型
            'mobile'            => ArrayHelper::getValue($getData, 'mobile'), //手机号
            'accountId'         => ArrayHelper::getValue($getData, 'accountId'), //存管电子账户
            'types_of_stages'   => ArrayHelper::getValue($getData, 'types_of_stages'), //分期类型
            'capital_side'      => ArrayHelper::getValue($getData, 'capital_side'), //资金方
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")), //账单日期
        ];
        //逾期类
        $oOverdueLoan = new OverdueLoan();
        //总笔数
        $total = $oOverdueLoan->getCollectCount($condition);
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => self::PAGE_SIZE,
        ]);
        $getData = $oOverdueLoan->getCollectData($pages, $condition);
        $getDatas = $oOverdueLoan->getCollectDatas($condition);
       //已收本金累计
        $amount = array_sum(array_column($getDatas, 'amount'));//本金累计

        //已收滞纳金累计
        $late_fee = array_sum(array_column($getDatas, 'late_fee'));

        //已收利息累计
        $interest_fee = array_sum(array_column($getData, 'interest_fee'));

        $total_money = $amount+$late_fee+$interest_fee;

        return $this->render('list', [
            'condition'                 => $condition,
            'pages'                     => $pages,
            'getData'                   => $getData,
            'total'                     => $total,
            'debtType'                  => $debtType,
            'overdueType'               => $overdueType,
            'amount'                    => $amount,
            'late_fee'                  => $late_fee,
            'interest_fee'              =>$interest_fee,
            'total_money'               => $total_money,
            'types_of_stages'           => $types_of_stages,
            'capital_side'              => $capital_side,
            //'way_of_payment'            => $way_of_payment,
        ]);
    }

    public function actionDetails()
    {
        $getData = $this->get();
        if (empty($getData)){
            return false;
        }
        $loan_id = ArrayHelper::getValue($getData, 'loan_id');
        //查找借款表信息
        $oUserLoan = new UserLoan();
        $loan_info = $oUserLoan->getLoanById($loan_id);
        if (empty($loan_info)){
            return false;
        }
        //用户信息
        $user_info = $loan_info->user;
        //出款表信息
        $remit_info = $loan_info->remit;
        //应还本金
        $need_money = $oUserLoan->getAllMoney($loan_id);
        //利率
        $interest_rate = "10%";
        //逾期表信息
        $overdue_info = $loan_info->overdueLoan;
        //展期表信息
        $oRenew_amount= new Renew_amount();
        $renew_info = $oRenew_amount->getDataByLoanid($loan_id);
        //还款记录信息
        $oLoanRepay = new LoanRepay();
        $repay_info = $oLoanRepay->getDataByLoanid($loan_id);
        //电子账户表
        $pay_account = $loan_info->payaccount;


        //借款金额
        $amount = ArrayHelper::getValue($loan_info, 'amount', 0);
        $need_money_all = 0;
        $not_money =0;
        if(!$loan_info){
            //应还本金
            $need_money_all = $oUserLoan->getRepaymentAmount($loan_info, 1);
            //未还本金
            $not_money = $oUserLoan->getRepaymentAmount($loan_info, 2);
        }
        //已还本金--(应还本金 - 未还本金)
        $over_money = bcsub($need_money, $not_money, 2);

        //应还服务费
        $need_service = $oUserLoan->getServiceAmount($amount);
        //已还服务费--(已还本金 - 借款金额)
        $over_service = bcsub($over_money, $amount, 2);
        if ($over_service <= 0){
            $over_service = 0;
        }
        //未还服务费--(已还服务费 - 应还服务费)
        $not_service = abs(bcsub($over_service, $need_service, 2));

        //应还利息
        $need_interest = $oUserLoan->loan_Fee_new($amount, ArrayHelper::getValue($loan_info, 'days'), ArrayHelper::getValue($loan_info, 'user_id'));
        $need_interest = ArrayHelper::getValue($need_interest, 'interest_fee', 0);
        //已还利息--(已还本金 - 借款金额 - 应还服务费)
        $over_interest = bcsub(bcsub($over_money, $amount, 2), $need_service, 2);
        if ($over_interest <= 0){
            $over_interest = 0;
        }
        //未还利息
        $not_interest = abs(bcsub($over_interest, $need_interest, 2));

        //应还还滞纳金
        $need_overdue = $oUserLoan->getOverdueAmount($loan_id);
        //已还滞纳金--(已还本金 - 借款金额 - 应还服务费 - 应还利息)
        $over_overdue = bcsub(bcsub(bcsub($over_money, $amount, 2), $need_service, 2), $need_interest, 2);
        if ($over_overdue <= 0){
            $over_overdue = 0;
        }
        //未还滞纳金
        $not_overdue = abs(bcsub($over_overdue, $need_overdue, 2));


        return $this->render('details', [
            'loan_info'             => $loan_info,
            'user_info'             => $user_info,
            'remit_info'            => $remit_info,
            'need_money'            => $need_money,
            'interest_rate'         => $interest_rate,
            'overdue_info'          => $overdue_info,
            'renew_info'            => $renew_info,
            'repay_info'            => $repay_info,
            'need_money_all'        => $need_money_all,
            'not_money'             => $not_money,
            'over_money'            => $over_money,
            'need_service'          => $need_service,
            'over_service'          => $over_service,
            'not_service'           => $not_service,
            'need_interest'         => $need_interest,
            'over_interest'         => $over_interest,
            'not_interest'          => $not_interest,
            'need_overdue'          => $need_overdue,
            'over_overdue'          => $over_overdue,
            'not_overdue'           => $not_overdue,
            'pay_account'           => $pay_account,
        ]);
    }

    public function actionDowndata()
    {
        $getData = $this->get();
        $condition = [
            'loan_id'           => ArrayHelper::getValue($getData, 'loan_id'), //借款编号
            'days'              => ArrayHelper::getValue($getData, 'days'), //业务类型
            'overdue_type'      => ArrayHelper::getValue($getData, 'overdue_type'), //逾期类型
            'mobile'            => ArrayHelper::getValue($getData, 'mobile'), //手机号
            'types_of_stages'   => ArrayHelper::getValue($getData, 'types_of_stages'), //分期类型
            'capital_side'      => ArrayHelper::getValue($getData, 'capital_side'), //资金方
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")), //账单日期
        ];
        $oOverdueLoan = new OverdueLoan();
        $data = $oOverdueLoan->getCollectDataDown($condition);
        $this->downlist_xls($data);
        return json_encode(['msg'=>json_encode($getData)]);
    }

    /**
     * 下载成功对账成功数据
     * @param $orderData
     * @throws \Exception
     */
    protected function downlist_xls($orderData) {
        $oCOverdue = new COverdue();

        $icount = count($orderData);

        //资金方
        $capital_side = $this->capitalSide();

        // 创建一个处理对象实例
        $objExcel = new \PHPExcel();

        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        for($a = 0; $a <= 15; $a ++){
            $chr_asc = 65 + $a;
            $objActSheet->getColumnDimension(chr($chr_asc))->setWidth(30);
        }

        $objActSheet->setCellValue('A1', '借款编号');
        $objActSheet->setCellValue('B1', '业务类型');
        $objActSheet->setCellValue('C1', '逾期类型');
        $objActSheet->setCellValue('D1', '订单号');
        $objActSheet->setCellValue('E1', '手机号');
        $objActSheet->setCellValue('F1', '存管电子账户');
        $objActSheet->setCellValue('G1', '资金方');
        $objActSheet->setCellValue('H1', '借款日期');
        $objActSheet->setCellValue('I1', '还款日期（到期日）');
        $objActSheet->setCellValue('J1', '已收本金');
        $objActSheet->setCellValue('K1', '已收利息');
        $objActSheet->setCellValue('L1', '已收滞纳金');
        $objActSheet->setCellValue('M1', '手续费（第三方支付手续费）');
        $objActSheet->setCellValue('N1', '已收总计（已还本金+已还利息+已还滞纳金-手续费）');
        $objActSheet->setCellValue('O1', '还款日期（该笔还款日期）');
        $objActSheet->setCellValue('P1', '分期类型');
        $objActSheet->setCellValue('Q1', '出款方式');
        $num = 0;
        //资方
        $fund_name = $oCOverdue->fund();
        //出款
        $oUserLoan = new UserLoan();
        for ($i = 0; $i < $icount; $i++) {
            $num ++;
            $data_set = $orderData[$i];
            //时间计算
            $end_time = strtotime(ArrayHelper::getValue($data_set, 'end_date'));
            $days = ceil((time()-$end_time)/60/60/24);
            //===
            $user_loan_info = $oUserLoan->getLoanById(ArrayHelper::getValue($data_set, 'loan_id'));
            $current_amount = $oUserLoan->getRepaymentAmount($user_loan_info, 2); //应还剩余本金
            $all_amount = ArrayHelper::getValue($data_set, 'amount'); //借款金额
            //计算利息
            $loan_Fee_new  = $oUserLoan->loan_Fee_new($all_amount, ArrayHelper::getValue($data_set, 'days'), ArrayHelper::getValue($data_set, 'user_id'));
            //逾期费用
            $need_overdue = $oUserLoan->getOverdueAmount(ArrayHelper::getValue($data_set, 'loan_id'));

            //已收本金
            $received = bcsub($all_amount, $current_amount, 2);
            if ($received <=0){
                $received = $all_amount;
            }
            //已收利息
            $interest = bcsub(bcsub($all_amount, $current_amount, 2), ArrayHelper::getValue($loan_Fee_new, 'interest_fee', 0), 2);
            if ($interest <= 0){
                $interest = ArrayHelper::getValue($loan_Fee_new, 'interest_fee', 0);
            }
            //已收滞纳金
            $overdue = bcsub(bcsub(bcsub($all_amount, $current_amount, 2), ArrayHelper::getValue($loan_Fee_new, 'interest_fee', 0), 2), $need_overdue, 2);
            if ($overdue <= 0){
                $overdue = $need_overdue;
            }
            //资金方
            $fund = ArrayHelper::getValue($data_set, "fund");
            $capital_side = ArrayHelper::getValue($capital_side, $fund);

            //出款方式
            $types_of_stages = ($fund==10) ? "体内" : "体外";

            $objActSheet->setCellValue('A' . ( $i + 2), $num);//借款编号
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($data_set, 'days')); //业务类型
            $objActSheet->setCellValue('C' . ( $i + 2), $oCOverdue->overdueType($days));//逾期类型
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($data_set, 'loan_id'));//订单号
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($data_set, 'mobile'));//手机号
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($data_set, 'accountId'));//存管电子账户
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($fund_name, ArrayHelper::getValue($data_set, 'fund'), ''));//资金方
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($data_set, 'start_date'));//借款日期
            $objActSheet->setCellValue('I' . ( $i + 2), ArrayHelper::getValue($data_set, 'end_date'));//还款日期（到期日）
            $objActSheet->setCellValue('J' . ( $i + 2), $received);//已收本金
            $objActSheet->setCellValue('K' . ( $i + 2), $interest);//已收利息
            $objActSheet->setCellValue('L' . ( $i + 2), $overdue);//已收滞纳金
            $objActSheet->setCellValue('M' . ( $i + 2), 1);//手续费（第三方支付手续费）
            $objActSheet->setCellValue('N' . ( $i + 2), $received+$interest+$overdue-1);//已收总计（已还本金+已还利息+已还滞纳金-手续费）
            $objActSheet->setCellValue('O' . ( $i + 2), ArrayHelper::getValue($data_set, 'repay_time'));//还款日期（该笔还款日期）
            $objActSheet->setCellValue('P' . ( $i + 2), "单期"); //分期类型
            $objActSheet->setCellValue('Q' . ( $i + 2), $types_of_stages); //出款方式
        }
        $outputFileName = date('Y-m-d', time())  . "逾期已收统计" . ".xls";
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
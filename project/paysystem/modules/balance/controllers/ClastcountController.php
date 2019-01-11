<?php
/**
 * 逾期待收统计（2017年）
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

class ClastcountController extends  AdminController
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

        $condition = [
            'days'              => ArrayHelper::getValue($getData, 'days'),
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")), //账单日期
        ];
        $oUserLoan = new UserLoan();
        //总笔数
        $total = $oUserLoan->getOverdueCount($condition);
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => self::PAGE_SIZE,
        ]);
        $data_set = $oUserLoan->getOverdueData($pages, $condition);
        $new_total =  array_sum(array_column($data_set, 'total'));
        $page = new Pagination([
            'totalCount' => $total,
            'pageSize' => $total,
        ]);
        $data_sets = $oUserLoan->getOverdueDatas($page, $condition);
        //已收金额
        $received_repay = $oUserLoan->getReceivedRepay($condition);
        //应收利息
        $received_interest = $oUserLoan->getReceivedInterest($condition);
        //本金
        $received_money = $oUserLoan->getReceivedMoney($condition);
        //滞纳金
        $received_overduc = bcsub( $oUserLoan->getReceivedOverdue($condition), $received_money, 4); //滞纳金+本金
        $received_overduc = $received_overduc < 0 ? 0 : $received_overduc;

        $oCOverdue = new COverdue();
        //应还本金累计
        $should_money = $oCOverdue->shouldPrincipal($received_repay, $received_money);
        //应还利息累计
        $should_interest = $oCOverdue->shouldInterest($received_repay, $received_money, $received_interest);
        //滞纳金累计
        $should_overdue = $oCOverdue->shouldOverdue($received_repay, $received_money, $received_interest, $received_overduc);
        //应还总额累计
        $all_should = bcadd(bcadd($should_money, $should_interest, 4), $should_overdue, 4);
        /****************************/
        //应还本金累计
        $collect_amount = 0;
        foreach($data_sets as $k => $v){
            $collect_amount += $oCOverdue->shouldPrincipal(ArrayHelper::getValue($v, 'all_actual_money', 0),ArrayHelper::getValue($v, 'all_amount', 0));
        }
        //应还利息累计
        $collect_interest = 0;
        foreach($data_sets as $k => $v){
            $collect_interest += ArrayHelper::getValue($v, 'all_interest_fee', 0);
        }
        //滞纳金累计
        $collect_overdue = 0;
        foreach($data_sets as $k => $v){
            $collect_overdue += $oCOverdue->receivedOverdue(ArrayHelper::getValue($v, 'all_actual_money', 0), ArrayHelper::getValue($v, 'all_amount', 0), ArrayHelper::getValue($v, 'all_interest_fee', 0),bcsub(ArrayHelper::getValue($v, 'all_chase_amount', 0), ArrayHelper::getValue($v, 'all_amount', 0),2));
        }
        if($collect_overdue<=0){
            $collect_overdue=0;
        }
        //总金额累计
        $all_amount = $collect_amount+$collect_interest+$collect_overdue;//bcadd(bcadd($collect_amount, $collect_interest, 4), $collect_overdue, 4);
        return $this->render('list', [
            'debtType'              => $debtType,
            'pages'                 => $pages,
            'data_set'              => $data_set,
            'total'                 => $new_total,
            'condition'             => $condition,
            'should_money'          => number_format($collect_amount, 2),
            'should_interest'       => number_format($collect_interest, 2),
            'should_overdue'        => number_format($collect_overdue, 2),
            'all_should'            => number_format($all_amount, 2),
        ]);
    }

    public function actionDowndata()
    {
        $getData = $this->get();

        if (empty($getData)){
            return false;
        }
        if (empty($getData['days']) || empty($getData['bill_data'])){
            return false;
        }

        $condition = [
            'days'              => ArrayHelper::getValue($getData, 'days'),
            'start_time'        => date("Y-m-d 00:00:00", strtotime(ArrayHelper::getValue($getData, 'bill_data'))), //账单日期
            'end_time'          => date("Y-m-d 23:59:59", strtotime(ArrayHelper::getValue($getData, 'bill_data'))), //账单日期
        ];
        $oUserLoan = new UserLoan();
        $data_set = $oUserLoan->getDownOverData($condition);
        $this->downlist_xls($data_set);
    }

    /**
     * 下载成功对账成功数据
     * @param $orderData
     * @throws \Exception
     */
    protected function downlist_xls($orderData) {
        $oCOverdue = new COverdue();
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
        $objActSheet->setCellValue('A1', '订单号');
        $objActSheet->setCellValue('B1', '手机号');
        $objActSheet->setCellValue('C1', '对应资方');
        $objActSheet->setCellValue('D1', '借款日期');
        $objActSheet->setCellValue('E1', '应还款日期');
        $objActSheet->setCellValue('F1', '待收本金');
        $objActSheet->setCellValue('G1', '待收利息');
        $objActSheet->setCellValue('H1', '待收滞纳金');
        $num = 0;
        //资方
        $fund_name = $oCOverdue->fund();
        //出款
        $oUserLoan = new UserLoan();
        for ($i = 0; $i < $icount; $i++) {
            $num ++;
            $data_set = $orderData[$i];

            //借款金额
            $amount = ArrayHelper::getValue($data_set, 'amount', 0);
            //var_dump($amount);die;
            //还款金额
            $actual_money = ArrayHelper::getValue($data_set, 'actual_money', 0);
            $actual_money = explode(',', $actual_money);
            $actual_money = array_reduce($actual_money, ["self", 'sum']);
            //利息
            $interest_fee = ArrayHelper::getValue($data_set, 'interest_fee', 0);
            
            //滞纳金
            $chase_amount = ArrayHelper::getValue($data_set, 'chase_amount', 0);
            $chase_amount = bcsub($chase_amount, $amount, 4);
            $chase_amount = ($chase_amount < 0) ? 0 : $chase_amount;

            //应还本金
            $need_money = $oCOverdue->shouldPrincipal($actual_money, $amount);
            //应还利息
            $need_interest = $oCOverdue->shouldInterest($actual_money, $amount, $interest_fee);
            //应还滞纳金
            $need_overdue = $oCOverdue->shouldOverdue($actual_money, $amount, $interest_fee, $chase_amount);

            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($data_set, 'loan_id')); //订单号
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($data_set, 'mobile')); //手机号
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($fund_name, ArrayHelper::getValue($data_set, 'fund')));//对应资方
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($data_set, 'start_date')); //借款日期
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($data_set, 'end_date')); //应还款日期
            $objActSheet->setCellValue('F' . ( $i + 2), number_format($need_money, 2)); //待收本金
            $objActSheet->setCellValue('G' . ( $i + 2), number_format($need_interest, 2)); //待收利息
            $objActSheet->setCellValue('H' . ( $i + 2), number_format($need_overdue, 2));//待收滞纳金
            $objActSheet->setCellValue('I' . ( $i + 2), $need_money+$need_interest+$need_overdue);//
        }
        $outputFileName = date('Y-m-d', time())  . "逾期待收统计（2017）" . ".xls";
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

    private function sum($carry, $item)
    {
        $carry += $item;
        return $carry;
    }
}
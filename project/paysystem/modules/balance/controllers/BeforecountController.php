<?php
/**
 * 逾期待收统计
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
use yii\web\User;

class BeforecountController extends  AdminController
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

        //
        $overdueType = $this->overdueType();
        $condition = [
            'loan_id'           => ArrayHelper::getValue($getData, 'loan_id'), //借款编号
            'days'              => ArrayHelper::getValue($getData, 'days'), //业务类型
           // 'overdue_type'      => ArrayHelper::getValue($getData, 'overdue_type'), //逾期类型
            'mobile'            => ArrayHelper::getValue($getData, 'mobile'), //手机号
            'types_of_stages'   => ArrayHelper::getValue($getData, 'types_of_stages'), //分期类型
            'capital_side'      => ArrayHelper::getValue($getData, 'capital_side'), //资金方
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")), //账单日期
        ];
        //逾期类
        $oOverdueLoan = new UserLoan();
        //总笔数
        $total = $oOverdueLoan->getAllcount($condition);
       // var_dump($total);die;
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => self::PAGE_SIZE,
        ]);

        $getData = $oOverdueLoan->getCollectData($pages, $condition);
        $getDatas = $oOverdueLoan->getCollectDatas($condition);
        //借款本金累计
        $amount = array_sum(array_column($getDatas, 'real_amount'));
        //待收利息累计利息累计
        $withdraw_fee = array_sum(array_column($getDatas, 'withdraw_fee'));
        //滞纳金累计
        //应还总额累计
        $total_fee = $amount+$withdraw_fee;
        return $this->render('list', [
            'condition'                 => $condition,
            'pages'                     => $pages,
            'getData'                   => $getData,
            'total'                     => $total,
            'amount'                    => $amount,
            'withdraw_fee'              => $withdraw_fee,

            'debtType'                  => $debtType,
            'overdueType'               => $overdueType,
            'total_fee'                 =>  $total_fee,
            'types_of_stages'           => $types_of_stages,
            'capital_side'              => $capital_side,
        ]);
    }

    public function actionDowndata()
    {

        $getData = $this->get();
        $condition = [
            'loan_id'           => ArrayHelper::getValue($getData, 'loan_id'), //借款编号
            'days'              => ArrayHelper::getValue($getData, 'days'), //业务类型
            //'overdue_type'      => ArrayHelper::getValue($getData, 'overdue_type'), //逾期类型
            'mobile'            => ArrayHelper::getValue($getData, 'mobile'), //手机号
            'types_of_stages'   => ArrayHelper::getValue($getData, 'types_of_stages'), //分期类型
            'capital_side'      => ArrayHelper::getValue($getData, 'capital_side'), //资金方
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")), //账单日期
        ];
        $oOverdueLoan = new UserLoan();
        $data = $oOverdueLoan->getCollectDatas($condition);
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
        $objActSheet->setCellValue('B1', '分期类型');
        $objActSheet->setCellValue('C1', '手机号');
        $objActSheet->setCellValue('D1', '存管电子账户');
        $objActSheet->setCellValue('E1', '资金方');
        $objActSheet->setCellValue('F1', '借款日期');
        $objActSheet->setCellValue('G1', '应还款日期');
        $objActSheet->setCellValue('H1', '待收本金');
        $objActSheet->setCellValue('I1', '待收利息');
        $objActSheet->setCellValue('J1', '待收总计');
        $objActSheet->setCellValue('K1', '出款方式');
        $objActSheet->setCellValue('L1', '业务类型');
        $num = 0;
        //资方
        $fund_name = $oCOverdue->fund();
        //$source="花生米富";
        //出款
        $oUserLoan = new UserLoan();
        for ($i = 0; $i < $icount; $i++) {
            $num ++;
            $data_set = $orderData[$i];
            //资金方
            $fund = ArrayHelper::getValue($data_set, "fund");
            $capital_side = ArrayHelper::getValue($capital_side, $fund);

            //出款方式
            $types_of_stages = ($fund==10) ? "体内" : "体外";

            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($data_set, 'loan_id'));//借款编号
            $objActSheet->setCellValue('B' . ( $i + 2), "单期"); //分期类型
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($data_set, 'mobile'));//手机号
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($data_set, 'accountId'));//存管电子账户
            $objActSheet->setCellValue('E' . ( $i + 2), $capital_side);//资金方
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($data_set, 'create_time'));//借款日期
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($data_set,'end_date'));//应还款日期
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($data_set,'real_amount'));//本金
            $objActSheet->setCellValue('I' . ( $i + 2), ArrayHelper::getValue($data_set, 'withdraw_fee'));//利息
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'real_amount')+ArrayHelper::getValue($data_set, 'withdraw_fee'));
            $objActSheet->setCellValue('K' . ( $i + 2), $types_of_stages); //出款方式
            $objActSheet->setCellValue('L' . ( $i + 2), ArrayHelper::getValue($data_set, 'days')); //业务类型


        }
        $outputFileName = date('Y-m-d', time())  . "未到期统计" . ".xls";
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
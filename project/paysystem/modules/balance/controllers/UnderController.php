<?php
/**
 * 线下还款 拆分统计
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 9:53
 */
namespace app\modules\balance\controllers;



use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\modules\balance\common\PaymentCommon;
use app\modules\balance\common\SplitCommon;
use app\modules\balance\models\SplitUnderRepay;
use app\modules\balance\common\COverdue;
use app\models\yyy\YiRenewalPrecord;



class UnderController extends  AdminController
{

   public function init(){
       $this->vvars['menu'] = 'account';
       $this->vvars['nav'] = 'account';
   }

    public function actionIndex()
    {
        return $this->render('index');
    }

    //日统计
    public function actionList()
    {
        $get  = $this -> get();
        $time =time();
        $startday = date('Y-m-d', $time - (24*3600*2));
        $endday =  date('Y-m-d', $time - (24*3600*2));
        $getdata = [
            'mechart_num'    => trim(ArrayHelper::getValue($get, 'mechart_num')),
            'start_time'      => trim(ArrayHelper::getValue($get, 'repay_time',$startday)),
            'end_time'        => trim(ArrayHelper::getValue($get, 'end_time',$endday)),
        ];

        $oSplitUnderRepay = new SplitUnderRepay();

        $total = $oSplitUnderRepay->getTotal($getdata);
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => 30,
        ]);

        $resultAllData = $oSplitUnderRepay->getAllData($getdata,$pages);

        //获取总金额
        $total_money = $oSplitUnderRepay->getTotalMoney($getdata);
        //本金总额
        $total_principal_money = $oSplitUnderRepay->split_principal($getdata);
        //利息总额
        $total_interest_money = $oSplitUnderRepay->split_interest($getdata);
        //罚息总额
        $total_fine_money = $oSplitUnderRepay->split_fine($getdata);
        return $this->render('list',[
            'start_time'            =>ArrayHelper::getValue($getdata, 'start_time'),
            'end_time'           =>ArrayHelper::getValue($getdata, 'end_time'),
            'total_money'      => $total_money,
            'resultAllData'      => $resultAllData,
            'total'              =>$total,
            'pages'              => $pages,
            'total_principal_money'              => $total_principal_money,
            'total_interest_money'              => $total_interest_money,
            'total_fine_money'              => $total_fine_money,

        ]);

        //var_dump($filter_where['mobile']);die;
       // return $this->render('list');
    }


    public function actionDowndata()
    {


        $getData = $this->get();
        $condition = [
            'start_time'           => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'             => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")), //账单日期
        ];

        $oSplitUnderRepay = new SplitUnderRepay();
        $total = $oSplitUnderRepay->getTotal($condition);
        $resultAllData = $oSplitUnderRepay->getAllData($condition,$pages=null);
        $this->downlist_xls($resultAllData);
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
        $objActSheet->setCellValue('B1', '已收本金');
        $objActSheet->setCellValue('C1', '已收利息');
        $objActSheet->setCellValue('D1', '已收滞纳金');
//        $objActSheet->setCellValue('E1', '已收总金额');
        $objActSheet->setCellValue('E1', '账单日期');

//        $num = 0;
        for ($i = 0; $i < $icount; $i++) {
//            $num ++;
            $data_set = $orderData[$i];
            //时间计算
            $objActSheet->setCellValue('A' . ( $i + 2), $i+1);//类型
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($data_set, 'principal'));//本金
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($data_set, 'interest'));//利息
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($data_set, 'fine'));//滞纳金
//            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($data_set, 'total_money'));//手续费
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($data_set, 'repayTime'));//手续费
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
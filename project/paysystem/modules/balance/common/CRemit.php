<?php
/**
 * 放款统计
 */
namespace app\modules\balance\common;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
if (!class_exists('PHPExcel')) {
    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

class CRemit{
    
    /**
     * Undocumented function
     * 导出Excel
     * @param [type] $orderData
     * @return void
     */
    public function exportExcel($orderData,$capitalSide) {

        $icount = count($orderData);
// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $sheetName = '业务明细';      
//设置当前活动sheet的名称
        $objActSheet->setTitle($sheetName);
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(30);
        $objActSheet->getColumnDimension('E')->setWidth(30);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(30);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        $objActSheet->getColumnDimension('J')->setWidth(30);
        $objActSheet->getColumnDimension('K')->setWidth(30);
        $objActSheet->getColumnDimension('L')->setWidth(30);
        $objActSheet->setCellValue('A1', '借款编号');
        $objActSheet->setCellValue('B1', '订单号');
        $objActSheet->setCellValue('C1', '借款人');
        $objActSheet->setCellValue('D1', '手机号');
        $objActSheet->setCellValue('E1', '资金方');
        $objActSheet->setCellValue('F1', '通道手续费');
        $objActSheet->setCellValue('G1', '借款天数');
        $objActSheet->setCellValue('H1', '借款日期');
        $objActSheet->setCellValue('I1', '应还本金');
        $objActSheet->setCellValue('J1', '应还利息');
        $objActSheet->setCellValue('K1', '应还总计');
        $objActSheet->setCellValue('L1', '账单日期');
       // $objActSheet->setCellValue('M1', '借款天数');
        $objActSheet->setCellValue('M1', '分期类型');
        for ($i = 0; $i < $icount; $i++) {          
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'loan_id'));
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'order_id'));
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'realname'));
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'mobile'));
            $fund =  ArrayHelper::getValue($orderData[$i], 'fund');
            $objActSheet->setCellValue('E' . ( $i + 2),ArrayHelper::getValue($capitalSide,$fund));//默认花生米富
            $objActSheet->setCellValue('F' . ( $i + 2), '1');//手续费1元 每笔
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'days'));
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'create_time'));
            $objActSheet->setCellValue('I' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'amount'));
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'interest_fee'));
            $objActSheet->setCellValue('K' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'amount')+ArrayHelper::getValue($orderData[$i], 'interest_fee'));
            $objActSheet->setCellValue('L' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'bill_date'));
           // $objActSheet->setCellValue('M' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'days'));
            $objActSheet->setCellValue('M' . ( $i + 2), '单期');//默认

        }
        $outputFileName = date('Y-m-d', time())  . $sheetName . ".xls";
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
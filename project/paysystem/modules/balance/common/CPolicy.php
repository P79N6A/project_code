<?php
/**
 * 保险统计
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

class CPolicy{
    
    /**
     * Undocumented function
     * 导出Excel
     * @param [type] $orderData
     * @param [type] $type
     * @return void
     */
    public function exportExcel($orderData) {

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
        $objActSheet->setCellValue('E1', '保费金额');
        $objActSheet->setCellValue('F1', '保险公司');
        $objActSheet->setCellValue('G1', '保单生成日期');
        $objActSheet->setCellValue('H1', '保单支付日期');
        $objActSheet->setCellValue('I1', '账单日期');
        for ($i = 0; $i < $icount; $i++) {          
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'req_id'));
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'client_id'));
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'user_name'));
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'user_mobile'));
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'premium'));
            $objActSheet->setCellValue('F' . ( $i + 2), '众安保险');
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'create_time'));
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'policy_time'));
            $objActSheet->setCellValue('I' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'bill_date'));

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
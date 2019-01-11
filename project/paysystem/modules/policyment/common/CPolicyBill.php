<?php
/**
 * 保险对账
 */
namespace app\modules\policyment\common;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
if (!class_exists('PHPExcel')) {
    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

class CPolicyBill{
    
    /**
     * Undocumented function
     * 导出Excel
     * @param [type] $orderData
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
        $sheetName = '保险费用';      
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
        $objActSheet->setCellValue('A1', '对账日期');
        $objActSheet->setCellValue('B1', '出单笔数');
        $objActSheet->setCellValue('C1', '出单金额');
        $objActSheet->setCellValue('D1', '退保笔数');
        $objActSheet->setCellValue('E1', '退保金额');
        $objActSheet->setCellValue('F1', '打款金额');
        for ($i = 0; $i < $icount; $i++) {          
            $policy_money = ArrayHelper::getValue($orderData[$i], 'policy_money');
            $cancel_money = ArrayHelper::getValue($orderData[$i], 'cancel_money');
            $return_money = ($policy_money-$cancel_money)*0.9;//90%返回
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'bill_date'));
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'policy_num'));
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'policy_money'));
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'cancel_num'));
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'cancel_money'));
            $objActSheet->setCellValue('F' . ( $i + 2), $return_money);
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

    /**
     * Undocumented function
     * 导出Excel
     * @param [type] $orderData
     * @return void
     */
    public function exportExcelDetail($orderData) {
        $policyStatus = \app\models\policy\PolicyBill::getPolicyStatus();
        $icount = count($orderData);
// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $sheetName = '保险数据';      
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
        $objActSheet->setCellValue('A1', '账单日期');
        $objActSheet->setCellValue('B1', '保单号');
        $objActSheet->setCellValue('C1', '用户姓名');
        $objActSheet->setCellValue('D1', '保费');
        $objActSheet->setCellValue('E1', '保险状态');
        for ($i = 0; $i < $icount; $i++) {          
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'bill_date'));
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'policyNo'));
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'policyHolderUserName'));
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'premium'));
            $objActSheet->setCellValue('E' . ( $i + 2), $policyStatus[ArrayHelper::getValue($orderData[$i], 'dataType')]);
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
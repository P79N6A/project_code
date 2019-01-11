<?php
/**
 * 放款统计
 */
namespace app\modules\balance\common;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\balance\models\peanut\StandardOrder;
if (!class_exists('PHPExcel')) {
    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

class CInvest{
    private $pageSize = 1000;
    /**
     * Undocumented function
     * 同步存管放款信息
     * @param [type] $bill_date
     * @return void
     */
    public function syncInvest($bill_date){
        $model = new StandardOrder;
        $count = $model->getOrderCount($bill_date);     
        $num = ceil($count/$this->pageSize);
        $success = 0;
        for($i=0;$i<$num;$i++){
            $offset = $i*$this->pageSize;
            $pages = ['offset'=>$offset,'limit'=>$this->pageSize];
            $dataList = $model->getOrderData($pages,$bill_date);
            if(!empty($dataList)){
                foreach($dataList as $key=>$data){
                    $res = $this->doSync($data,$bill_date);
                    if($res){
                        $success++;
                    }
                }
            }
        }
        return json_encode(['count'=>$count,'success'=>$success]);
       
    } 
    
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
        $sheetName = '理财明细';      
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
        $objActSheet->setCellValue('A1', '订单号');
        $objActSheet->setCellValue('B1', '借款人');
        $objActSheet->setCellValue('C1', '手机号');
        $objActSheet->setCellValue('D1', '第三方通道');
        $objActSheet->setCellValue('E1', '起息日');
        $objActSheet->setCellValue('F1', '到期日');
        $objActSheet->setCellValue('G1', '类型');
        $objActSheet->setCellValue('H1', '投资本金');
        $objActSheet->setCellValue('I1', '预计收益率');
        $objActSheet->setCellValue('J1', '预计收益');
        $objActSheet->setCellValue('K1', '加息券');
        $objActSheet->setCellValue('L1', '现金红包');
        $objActSheet->setCellValue('M1', '好友返利');
        $objActSheet->setCellValue('N1', '应付本息金额');
        for ($i = 0; $i < $icount; $i++) {          
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'order_no'));
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'name'));
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'mobile'));
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'channel'));
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'start_date'));
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'end_date'));
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'invest_day'));
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'buy_amount'));
            $objActSheet->setCellValue('I' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'yield'));
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'buy_interest'));
            $objActSheet->setCellValue('K' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'coupon_interest'));
            $objActSheet->setCellValue('L' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'red_amount'));
            $objActSheet->setCellValue('M' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'invite_interest'));
            $objActSheet->setCellValue('N' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'buy_amount')+ArrayHelper::getValue($orderData[$i], 'buy_interest'));

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
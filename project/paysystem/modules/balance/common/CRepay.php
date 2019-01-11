<?php
/**
 * 资金对账
 */
namespace app\modules\balance\common;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\balance\models\yyy\User;
use app\modules\balance\models\yyy\UserLoan;



if (!class_exists('PHPExcel')) {

    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

set_time_limit(0);

class CRepay{

    public function downRepayXls($orderData) {

        $icount = count($orderData);
// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $oUser = new User();
        $sheetName = '正常回款统计';        
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
        $objActSheet->getColumnDimension('M')->setWidth(30);
        $objActSheet->getColumnDimension('N')->setWidth(30);
        $objActSheet->getColumnDimension('O')->setWidth(30);
        $objActSheet->setCellValue('A1', '借款编号');
        $objActSheet->setCellValue('B1', '手机号');
        $objActSheet->setCellValue('C1', '对应资方');
        $objActSheet->setCellValue('D1', '借款日期');
        $objActSheet->setCellValue('E1', '到期日期');
        $objActSheet->setCellValue('F1', '已还本金');
        $objActSheet->setCellValue('G1', '已还利息');
        $objActSheet->setCellValue('H1', '减免金额');
        $objActSheet->setCellValue('I1', '点赞减息');
        $objActSheet->setCellValue('J1', '已还金额');
        $objActSheet->setCellValue('K1', '借款天数');
        $objActSheet->setCellValue('L1', '还款日期');
        $objActSheet->setCellValue('M1', '账单日期');
        for ($i = 0; $i < $icount; $i++) {
            $userId = ArrayHelper::getValue($orderData[$i], 'user_id',0);
            $userInfo = $oUser->getUserInfo($userId);
            $phone = ArrayHelper::getValue($userInfo, 'mobile','');
            $amount = ArrayHelper::getValue($orderData[$i], 'amount', 0 );
            $interest_fee = ArrayHelper::getValue($orderData[$i], 'interest_fee',0);
            $coupon = ArrayHelper::getValue($orderData[$i], 'coupon_amount',0);
            $likes = ArrayHelper::getValue($orderData[$i], 'like_amount',0);

            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'loan_id'));
            $objActSheet->setCellValue('B' . ( $i + 2), $phone);
            $objActSheet->setCellValue('C' . ( $i + 2), '江西存管');
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'start_date'));
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'end_date'));
            $objActSheet->setCellValue('F' . ( $i + 2), $amount);
            $objActSheet->setCellValue('G' . ( $i + 2), $interest_fee);
            $objActSheet->setCellValue('H' . ( $i + 2), $coupon);
            $objActSheet->setCellValue('I' . ( $i + 2), $likes);
            $objActSheet->setCellValue('J' . ( $i + 2), ($amount + $interest_fee - $coupon - $likes));
            $objActSheet->setCellValue('K' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'days'));
            $objActSheet->setCellValue('L' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'repay_time'));
            $objActSheet->setCellValue('M' . ( $i + 2), date('Y-m-d', strtotime(ArrayHelper::getValue($orderData[$i], 'repay_time'))));

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

    public function getRenewalDay($end_date,$repay_time,$status){
        $yq_days = 0;
        if($status != 8){//非全部结清
            $repay_time = date('Y-m-d H:i:s');
        }
        if($repay_time >= $end_date){
            $yq_days = ceil((strtotime(date('Y-m-d',strtotime($repay_time)))-strtotime($end_date))/86400)+1;
        }
        return $yq_days;
    }


/***
 * 获得逾期类型  M1 M2 M3 坏账
 * end_date 到期时间    $repay_date 还款时间    $status结清状态
 */
    public function getRenewalType($end_date,$repay_time,$status){
        $yq_days = $this->getRenewalDay($end_date,$repay_time,$status);
        $type = '正常';
        
        if($yq_days > 0 && $yq_days <= 30){
            $type  = 'M1';
        }
        if($yq_days > 30 && $yq_days <= 60){
            $type  = 'M2';
        }

        if($yq_days > 60 && $yq_days <= 90){
            $type  = 'M3';
        }

        if($yq_days > 90){
            $type  = '坏账';
        }
        return $type;

    }

/***
 * get 是否逾期
 * end_date 到期时间    $repay_date 还款时间    $status结清状态
 */
    public function getIsyq($end_date,$repay_time,$status){
        $yq_days = $this->getRenewalDay($end_date,$repay_time,$status);
        $res = '未逾期';
        if($yq_days > 0 ){
            $res  = '已逾期';
        }
        return $res;

    }

    public function downRenewalXls($orderData) {

        $icount = count($orderData);
// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $oUser = new User();
        $oLoan = new UserLoan();
        $sheetName = '展期统计';        
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
        $objActSheet->getColumnDimension('M')->setWidth(30);
        $objActSheet->getColumnDimension('N')->setWidth(30);
        $objActSheet->getColumnDimension('O')->setWidth(30);
        $objActSheet->setCellValue('A1', '借款编号');
        $objActSheet->setCellValue('B1', '订单号');
        $objActSheet->setCellValue('C1', '逾期类型');
        $objActSheet->setCellValue('D1', '展期类型');
        $objActSheet->setCellValue('E1', '手机号');
        $objActSheet->setCellValue('F1', '借款日期');
        $objActSheet->setCellValue('G1', '到期日期');
        $objActSheet->setCellValue('H1', '应还本金');
        $objActSheet->setCellValue('I1', '应还利息');
        $objActSheet->setCellValue('J1', '减免金额');
        $objActSheet->setCellValue('K1', '点赞减息');
        $objActSheet->setCellValue('L1', '应还总计');
        $objActSheet->setCellValue('M1', '展期费用');
        $objActSheet->setCellValue('N1', '借款天数');
        $objActSheet->setCellValue('O1', '展期发生日期');
        $objActSheet->setCellValue('P1', '累计发生展期次数');
        $objActSheet->setCellValue('Q1', '资金方');
        for ($i = 0; $i < $icount; $i++) {
            $userId = ArrayHelper::getValue($orderData[$i], 'user_id',0);
            $userInfo = $oUser->getUserInfo($userId);
            $phone = ArrayHelper::getValue($userInfo, 'mobile','');
            $amount = ArrayHelper::getValue($orderData[$i], 'amount', 0 );
            $interest_fee = ArrayHelper::getValue($orderData[$i], 'interest_fee',0);
            $coupon = ArrayHelper::getValue($orderData[$i], 'coupon_amount',0);
            $likes = ArrayHelper::getValue($orderData[$i], 'like_amount',0);
            $yqType = $this->getRenewalType(ArrayHelper::getValue($orderData[$i], 'end_date',''),ArrayHelper::getValue($orderData[$i], 'create_time',''),ArrayHelper::getValue($orderData[$i], 'loan_status',''));
            $zqType = $this->getIsyq(ArrayHelper::getValue($orderData[$i], 'end_date',''),ArrayHelper::getValue($orderData[$i], 'create_time',''),ArrayHelper::getValue($orderData[$i], 'loan_status',''));
            
            $loanInfo = $oLoan->getRenewalNum(ArrayHelper::getValue($orderData[$i], 'loan_id','0'));
            $number = ArrayHelper::getValue($loanInfo, 'number',0);
            
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'loan_id'));
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'order_id'));
            $objActSheet->setCellValue('C' . ( $i + 2), $yqType);
            $objActSheet->setCellValue('D' . ( $i + 2), $zqType);
            $objActSheet->setCellValue('E' . ( $i + 2), $phone);
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'start_date'));
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'end_date'));
            $objActSheet->setCellValue('H' . ( $i + 2), $amount);
            $objActSheet->setCellValue('I' . ( $i + 2), $interest_fee);
            $objActSheet->setCellValue('J' . ( $i + 2), $coupon);
            $objActSheet->setCellValue('K' . ( $i + 2), $likes);
            $objActSheet->setCellValue('L' . ( $i + 2), ($amount + $interest_fee - $coupon - $likes));
            $objActSheet->setCellValue('M' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'money'));
            $objActSheet->setCellValue('N' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'days'));
            $objActSheet->setCellValue('O' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'create_time'));
            $objActSheet->setCellValue('P' . ( $i + 2), $number);
            $objActSheet->setCellValue('Q' . ( $i + 2), '江西存管');

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
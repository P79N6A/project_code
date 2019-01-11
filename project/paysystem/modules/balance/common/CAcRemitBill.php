<?php
/**
 * 资金对账
 */
namespace app\modules\backstage\common;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\backstage\models\AcRemitBill;
use app\modules\backstage\models\AutoLendPay;
use app\modules\backstage\common\CBillCommon;
use app\modules\backstage\models\CmRemit;
if (!class_exists('PHPExcel')) {
    include Yii::$app->basePath.'/modules/backstage/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/modules/backstage/common/phpexcel/Excel5.php';
}

set_time_limit(0);

class CAcRemitBill{
    private $pageSize = 1000;
    private $oRemitBill;
    /**
     * 初始化接口 上标接口
     */
    public function __construct() {
        $this->oRemitBill = new AcRemitBill;
    }
    /**
     * Undocumented function
     * 同步存管放款信息
     * @param [type] $bill_date
     * @return void
     */
    public function syncRemit($bill_date){
        $model = new AutoLendPay;
        $count = $model->getCgRemitCount($bill_date);     
        $num = ceil($count/$this->pageSize);
        $success = 0;
        for($i=0;$i<$num;$i++){
            $pages = (new CBillCommon)->getPageInfo($i,$this->pageSize);
            $cgdataList = $model->getCgRemitData($pages,$bill_date);
            if(!empty($cgdataList)){
                foreach($cgdataList as $key=>$cgdata){
                    $res = $this->doSync($cgdata,$bill_date);
                    if($res){
                        $success++;
                    }else{
                        Logger::dayLog('backstage/fund','同步保存资金账单失败',$postdata,$oM->errinfo);
                    }
                }
            }
        }
        return json_encode(['count'=>$count,'success'=>$success]);
       
    }
    private function doSync($cgdata,$bill_date){
        $oM = new AcRemitBill;
        $order_no = ArrayHelper::getValue($cgdata,'orderId','');
        $oRemitBill = $oM->getDataByOrderNo($order_no);
        $postdata = [
            'bill_date' => $bill_date,
            'cg_accountId' => ArrayHelper::getValue($cgdata,'accountId',''),
            'order_no'  => $order_no,                  
            'cg_amount' => ArrayHelper::getValue($cgdata,'txAmount','0'),
            'reason'    => '',
        ];
        if($oRemitBill){
            return true;
        }  
        $res = $oM->saveData($postdata);
        if(!$res){
            Logger::dayLog('backstage/remit','同步保存放款债权账单失败',$postdata,$oM->errinfo);
        }
        return $res;
    }
    /**
     * Undocumented function
     * 出款债权对账
     * @return void
     */
    public function runRemit(){
        $initRet = json_encode(['total' => 0, 'success' => 0]);
        $num = 500;
        $dataList = $this->oRemitBill->getRemitInitData($num);
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oRemitBill->lockBill($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        $order_nos = ArrayHelper::getColumn($dataList, 'order_no');
        $cmDataList = (new CmRemit)->getCmRemitData($order_nos);
        $count = count($dataList);
        $success = 0;
        if(empty($cmDataList)){
            $up_data = [
                'type'          => AcRemitBill::BILL_TYPE_FAIL,
                'error_info'    => AcRemitBill::BILL_ERROR_ONE,
                'modify_time'   => date('Y-m-d H:i:s')
            ];
            $res= $this->oRemitBill->upRemitBill($up_data,$ids);
            if(!$res){
                Logger::dayLog('backstage/runRemit','upRemitBill','更新数据失败',$up_data,$ids);
            }else{
                $success = $count;
            }
            return json_encode(['total' => $count, 'success' => $success]);
        }
        foreach($dataList as $key=>$oRemitBill){
            $res = $this->doRemit($oRemitBill,$cmDataList);          
            if($res){
                $success++;
            }
        }
        return json_encode(['total' => $count, 'success' => $success]);
    }
    private  function doRemit($oRemitBill,$cmDataList){
        $oRemitBill->refresh();
        $isLock=$oRemitBill->lockOneBill();
        if(!$isLock){
            Logger::dayLog('backstage/runRemit', 'lockOneBill', '锁失败', $oRemitBill->id);
            return false;
        }
        $order_no   = $oRemitBill->order_no;
        $cg_amount  = $oRemitBill->cg_amount;
        $cg_accountId = $oRemitBill->cg_accountId;
        if(array_key_exists($order_no,$cmDataList)){
            $cm_data = $cmDataList[$order_no];
            $type = AcRemitBill::BILL_TYPE_SUCC;//账单类型 //成功 差错
            $error_info = $this->oRemitBill->getErrorInfo($cg_amount,$cg_accountId,$cm_data);
            $standard_id = ArrayHelper::getValue($cm_data,'standard_id','');
            if(!empty($error_info)){
                //存在差错
                $type = AcRemitBill::BILL_TYPE_FAIL;
            }
            $up_data = [
                'standard_id'       => $standard_id,                  
                'cm_amount'         => ArrayHelper::getValue($cm_data,'money','0'),
                'cm_status'         => ArrayHelper::getValue($cm_data,'status','0'),
                'cm_accountId'      => ArrayHelper::getValue($cm_data,'account_id',''),
                'error_info'        => $error_info,
                'type'              => $type,
                'investAccountId'   => $this->oRemitBill->getInvestAccountId($standard_id),
                'aid'               => empty($cm_data['comeFrom'])?0:$cm_data['comeFrom'],
                'duration'          => empty($cm_data['duration'])?0:$cm_data['duration'],
            ];
        }else{
            $error_info = AcRemitBill::BILL_ERROR_ONE;
            $type = AcRemitBill::BILL_TYPE_FAIL;
            $up_data = [
                'type'          => $type,
                'error_info'    => $error_info,
            ];
        }
        //更新数据
        $res = $oRemitBill->updateData($up_data);
        if(!$res){
            Logger::dayLog('backstage/runRemit','updateData','更新数据失败',$oRemitBill->errinfo,$up_data,$oRemitBill->attributes);
        }
        return $res;
    }
    
    
    public function downRemitXls($orderData ,$type) {

        $icount = count($orderData);
// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        $aidList = AcRemitBill::getAid();
        $cmStatus = AcRemitBill::getCmStatus();
        $billType = AcRemitBill::getBillType();
        if($type ==  2){
            $sheetName = '存管明细';
        }else{
            $sheetName = '债匹明细';
        }
        
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
        $objActSheet->setCellValue('A1', '账单日期');
        $objActSheet->setCellValue('B1', '业务名称');
        $objActSheet->setCellValue('C1', '债权类型');
        $objActSheet->setCellValue('D1', '订单号');
        $objActSheet->setCellValue('E1', '投资人电子账户');
        $objActSheet->setCellValue('F1', '借款人电子账号');
        $objActSheet->setCellValue('G1', '订单状态');
        $objActSheet->setCellValue('H1', '金额');
        $objActSheet->setCellValue('I1', '对账状态');
        for ($i = 0; $i < $icount; $i++) {
            if($type == 2){//存管
                $status = 'SUCCESS';
                $money = ArrayHelper::getValue($orderData[$i], 'cg_amount');
                $accountId =  ArrayHelper::getValue($orderData[$i], 'cg_accountId');
            }else{//债匹
                $status = ArrayHelper::getValue($orderData[$i], 'cm_status');
                $status = $cmStatus[$status];
                $money = ArrayHelper::getValue($orderData[$i], 'cm_amount');
                $accountId =  ArrayHelper::getValue($orderData[$i], 'cm_accountId');
            }
            $aid = isset($aidList[ArrayHelper::getValue($orderData[$i], 'aid')])?$aidList[ArrayHelper::getValue($orderData[$i], 'aid')]:'未知';
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'bill_date'));
            $objActSheet->setCellValue('B' . ( $i + 2), $aid);
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'duration'));
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'order_no'));
            $objActSheet->setCellValue('E' . ( $i + 2), $accountId);
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'investAccountId'));
            $objActSheet->setCellValue('G' . ( $i + 2), $status);
            $objActSheet->setCellValue('H' . ( $i + 2), $money);
            $objActSheet->setCellValue('I' . ( $i + 2), $billType[ArrayHelper::getValue($orderData[$i], 'type')]);

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
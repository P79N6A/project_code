<?php
/**
 * 资金对账
 */
namespace app\modules\backstage\common;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\backstage\models\AcFundBill;
use app\modules\backstage\common\CBillCommon;
use app\modules\backstage\models\CgRechargeOrder;
use app\modules\backstage\models\CgWithdrawOrder;
use app\modules\backstage\models\CgAgreeWithdraw;
use app\modules\backstage\models\PeaRechargeOrder;
use app\modules\backstage\models\PeaWithdrawOrder;
if (!class_exists('PHPExcel')) {
    include Yii::$app->basePath.'/modules/backstage/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/modules/backstage/common/phpexcel/Excel5.php';
}

set_time_limit(0);

class CAcFundBill{
    private $pageSize = 1000;
    private $oFundBill;
    /**
     * 初始化接口 上标接口
     */
    public function __construct() {
        $this->oFundBill = new AcFundBill;
    }
    /**
     * Undocumented function
     * 同步存管充值订单信息
     * @param [type] $bill_date
     * @return void
     */
    public function syncRecharge($bill_date){
        $model = new CgRechargeOrder;
        $count = $model->getCgRechargeCount($bill_date);     
        $num = ceil($count/$this->pageSize);
        $success = 0;
        for($i=0;$i<$num;$i++){
            $pages = (new CBillCommon)->getPageInfo($i,$this->pageSize);
            $cgdataList = $model->getCgRechargeData($pages,$bill_date);
            if(!empty($cgdataList)){
                foreach($cgdataList as $key=>$cgdata){
                    $res = $this->doSync($cgdata,$bill_date,AcFundBill::BILL_RECHARGE);
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
     * 同步存管提现订单信息
     * @param [type] $bill_date
     * @return void
     */
    public function syncWithdraw($bill_date){
        $model = new CgWithdrawOrder;
        $count = $model->getCgWithdrawCount($bill_date);     
        $num = ceil($count/$this->pageSize);
        $success = 0;
        for($i=0;$i<$num;$i++){
            $pages = (new CBillCommon)->getPageInfo($i,$this->pageSize);
            $cgdataList = $model->getCgWithdrawData($pages,$bill_date);
            if(!empty($cgdataList)){
                foreach($cgdataList as $key=>$cgdata){
                    $res = $this->doSync($cgdata,$bill_date,AcFundBill::BILL_WITHDRAW);
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
     * 同步存管免密提现订单信息
     * @param [type] $bill_date
     * @return void
     */
    public function syncAgreeWithdraw($bill_date){
        $model = new CgAgreeWithdraw;
        $count = $model->getCgAgreeWithdrawCount($bill_date);    
        $num = ceil($count/$this->pageSize);
        $success = 0;
        for($i=0;$i<$num;$i++){
            $pages = (new CBillCommon)->getPageInfo($i,$this->pageSize);
            $cgdataList = $model->getCgAgreeWithdrawData($pages,$bill_date);
            if(!empty($cgdataList)){
                foreach($cgdataList as $key=>$cgdata){
                    $res = $this->doSync($cgdata,$bill_date,AcFundBill::BILL_WITHDRAW);
                    if($res){
                        $success++;
                    }
                }
            }
        }
        return json_encode(['count'=>$count,'success'=>$success]);
    }
    private function doSync($cgdata,$bill_date,$bill_type){
        $oM = new AcFundBill;
        $order_no = ArrayHelper::getValue($cgdata,'order_no','');
        $oFundBill = $oM->getDataByOrderNo($order_no);
        $postdata = [
            'bill_date' => $bill_date,
            'bill_type' => $bill_type,
            'cg_accountId' => ArrayHelper::getValue($cgdata,'accountId',''),
            'order_no'  => $order_no,                  
            'idNo'      => ArrayHelper::getValue($cgdata,'idNo',''),
            'name'      => ArrayHelper::getValue($cgdata,'name',''),
            'mobile'    => ArrayHelper::getValue($cgdata,'mobile',''),
            'cg_amount' => ArrayHelper::getValue($cgdata,'txAmount','0'),
            'reason'    => '',
            'cg_fee'    => ArrayHelper::getValue($cgdata,'txFee','0'),
        ];
        if($oFundBill){
            return true;
        }  
        $res = $oM->saveData($postdata);
        if(!$res){
            Logger::dayLog('backstage/fund','同步保存资金账单失败',$postdata,$oM->errinfo);
        }
        return $res;
    }
    /**
     * Undocumented function
     * 充值对账
     * @return void
     */
    public function runRecharge(){
        $initRet = json_encode(['total' => 0, 'success' => 0]);
        $num = 500;
        $bill_type = AcFundBill::BILL_RECHARGE;
        $dataList = $this->oFundBill->getFundInitData($bill_type,$num);
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oFundBill->lockBill($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        $order_nos = ArrayHelper::getColumn($dataList, 'order_no');
        $peaDataList = (new PeaRechargeOrder)->getPeaRechargeData($order_nos);
        $count = count($dataList);
        $success = 0;
        if(empty($peaDataList)){
            $up_data = [
                'type'          => AcFundBill::BILL_TYPE_FAIL,
                'error_info'    => AcFundBill::BILL_ERROR_ONE,
                'modify_time'   => date('Y-m-d H:i:s')
            ];
            $res= $this->oFundBill->upFundBill($up_data,$ids);
            if(!$res){
                Logger::dayLog('backstage/runRecharge','upFundBill','更新数据失败',$up_data,$ids);
            }else{
                $success = $count;
            }
            return json_encode(['total' => $count, 'success' => $success]);
        }
        foreach($dataList as $key=>$oFundBill){
            $res = $this->doFund($oFundBill,$peaDataList);          
            if($res){
                $success++;
            }
        }
        return json_encode(['total' => $count, 'success' => $success]);
    }

    /**
     * Undocumented function
     * 提现对账
     * @return void
     */
    public function runWithdraw(){
        $initRet = json_encode(['total' => 0, 'success' => 0]);
        $num = 500;
        $bill_type = AcFundBill::BILL_WITHDRAW;//提现
        $dataList = $this->oFundBill->getFundInitData($bill_type,$num);
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oFundBill->lockBill($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        $order_nos = ArrayHelper::getColumn($dataList, 'order_no');
        $peaDataList = (new PeaWithdrawOrder)->getPeaWithdrawData($order_nos);
        //var_dump($peaDataList);
        $count = count($dataList);
        $success = 0;
        if(empty($peaDataList)){
            $up_data = [
                'type'          => AcFundBill::BILL_TYPE_FAIL,
                'error_info'    => AcFundBill::BILL_ERROR_ONE,
                'modify_time'   => date('Y-m-d H:i:s')
            ];
            $res= $this->oFundBill->upFundBill($up_data,$ids);
            if(!$res){
                Logger::dayLog('backstage/runWithdraw','upFundBill','更新数据失败',$up_data,$ids);
            }else{
                $success = $count;
            }
            return json_encode(['total' => $count, 'success' => $success]);
        }
        foreach($dataList as $key=>$oFundBill){
            $res = $this->doFund($oFundBill,$peaDataList);          
            if($res){
                $success++;
            }
        }
        return json_encode(['total' => $count, 'success' => $success]);
    }
    private  function doFund($oFundBill,$peaDataList){
        $oFundBill->refresh();
        $isLock=$oFundBill->lockOneBill();
        if(!$isLock){
            Logger::dayLog('backstage/doFund', 'lockOneBill', '锁失败', $oFundBill->id);
            return false;
        }
        $order_no   = $oFundBill->order_no;
        $cg_amount  = $oFundBill->cg_amount;
        $cg_fee     = $oFundBill->cg_fee;
        $cg_accountId = $oFundBill->cg_accountId;
        if(array_key_exists($order_no,$peaDataList)){
            $pea_data = $peaDataList[$order_no];
            $type = AcFundBill::BILL_TYPE_SUCC;//账单类型 //成功 差错
            $error_info = $this->oFundBill->getErrorInfo($cg_amount,$cg_accountId,$cg_fee,$pea_data);
            if(!empty($error_info)){
                //存在差错
                $type = AcFundBill::BILL_TYPE_FAIL;
            }
            $up_data = [
                'type'          => $type,
                'error_info'    => $error_info,
                'pea_amount'    => ArrayHelper::getValue($pea_data,'settle_amount',0),
                'pea_fee'       => ArrayHelper::getValue($pea_data,'settle_fee',0),
                'pea_status'    => ArrayHelper::getValue($pea_data,'status',''),
                'pea_accountId' => ArrayHelper::getValue($pea_data,'accountId',''),
                'user_id'       => ArrayHelper::getValue($pea_data,'user_id',''),
            ];
        }else{
            $error_info = AcFundBill::BILL_ERROR_ONE;
            $type = AcFundBill::BILL_TYPE_FAIL;
            $up_data = [
                'type'          => $type,
                'error_info'    => $error_info,
            ];
        }
        //更新数据
        $res = $oFundBill->updateData($up_data);
        if(!$res){
            Logger::dayLog('backstage/doFund','updateData','更新数据失败',$oFundBill->errinfo,$up_data,$oFundBill->attributes);
        }
        return $res;
    }
    public function runRepair(){
        $result = (new AcFundBill)->repairData();
    }
    /**
     * Undocumented function
     * 导出Excel
     * @param [type] $orderData
     * @param [type] $type
     * @return void
     */
    public function downFundXls($orderData ,$type) {

        $icount = count($orderData);
// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        if($type ==  2){
            $sheetName = '存管明细';
        }else{
            $sheetName = '业务明细';
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
        $objActSheet->setCellValue('B1', '订单号');
        $objActSheet->setCellValue('C1', '电子账户');
        $objActSheet->setCellValue('D1', '手机号');
        $objActSheet->setCellValue('E1', '姓名');
        $objActSheet->setCellValue('F1', '证件号');
        $objActSheet->setCellValue('G1', '类型');
        $objActSheet->setCellValue('H1', '订单状态');
        $objActSheet->setCellValue('I1', '金额');
        $objActSheet->setCellValue('J1', '手续费');
        $objActSheet->setCellValue('K1', '对账状态');
        $fund_type = AcFundBill::getFundType();//提现|充
        $bill_type = AcFundBill::getBillType();//正常|查错
        for ($i = 0; $i < $icount; $i++) {
            if($type == 2){//存管
                $status = 'SUCCESS';
                $money = ArrayHelper::getValue($orderData[$i], 'cg_amount');
                $fee =  ArrayHelper::getValue($orderData[$i], 'cg_fee');
                $accountId =  ArrayHelper::getValue($orderData[$i], 'cg_accountId');
            }else{//米富
                $status = ArrayHelper::getValue($orderData[$i], 'pea_status');
                $money = ArrayHelper::getValue($orderData[$i], 'pea_amount');
                $fee =  ArrayHelper::getValue($orderData[$i], 'pea_fee');
                $accountId =  ArrayHelper::getValue($orderData[$i], 'pea_accountId');
            }
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'bill_date'));
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'order_no'));
            $objActSheet->setCellValue('C' . ( $i + 2), $accountId);
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'mobile'));
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'name'));
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'idNo'));
            $objActSheet->setCellValue('G' . ( $i + 2), $fund_type[ArrayHelper::getValue($orderData[$i], 'bill_type')]);
            $objActSheet->setCellValue('H' . ( $i + 2), $status);
            $objActSheet->setCellValue('I' . ( $i + 2), $money);
            $objActSheet->setCellValue('J' . ( $i + 2), $fee);
            $objActSheet->setCellValue('K' . ( $i + 2), $bill_type[ArrayHelper::getValue($orderData[$i], 'type')]);

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
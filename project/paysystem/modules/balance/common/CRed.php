<?php
/**
 * 资金对账
 */
namespace app\modules\backstage\common;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\backstage\models\RedBill;
use app\modules\backstage\models\PeaRed;
use app\modules\backstage\models\CgRed;
use app\modules\backstage\common\CBillCommon;



if (!class_exists('PHPExcel')) {
    include Yii::$app->basePath.'/modules/backstage/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/modules/backstage/common/phpexcel/Excel5.php';
}

set_time_limit(0);

class CRed{

    private $pageSize = 1000;
    /**
     * Undocumented function
     * 同步存管红包信息
     * @param [type] $bill_date
     * @return void
     */
    public function syncRed($bill_date){
        $model = new CgRed;
        $count = $model->getCgredCount($bill_date);
        $num = ceil($count/$this->pageSize);
        $success = 0;
        for($i=0;$i<$num;$i++){
            $pages = (new CBillCommon)->getPageInfo($i,$this->pageSize);
            $redData = $model->getCgredData($pages,$bill_date);
            if(!empty($redData)){
                foreach($redData as $key=>$red){
                    $res = $this->doSync($red,$bill_date);
                    if($res){
                        $success++;
                    }
                }
            }
        }
        return json_encode(['count'=>$count,'success'=>$success]);
       
    }

    private function doSync($data,$bill_date){
        $oRed = new RedBill();
        $orderId = ArrayHelper::getValue($data,'orderId','');
        $oBill = $oRed->getRedBill($orderId);//数据是否已跑
        if($oBill){
            return true;
        }
        if($data['desLine'] == '红包领取'){
            $redType = 1;
        }

        if($data['desLine'] == '邀请返利'){
            $redType = 2;
        }

        $postdata = [
            'orderId' => $data['orderId'],
            'accountId' => $data['accountId'],
            'actAmount' => $data['actAmount'],
            'forAccountId' => $data['forAccountId'],
            'desLine' => $data['desLine'],
            'acqRes' => $data['acqRes'],
            'retCode' => $data['retCode'],
            'create_time' => $data['create_time'],
            'modify_time' => $data['modify_time'],
            'bill_time' => $bill_date,
            'red_type' => $redType
        ];
         
        $res = $oRed->createData($postdata);
        if(!$res){
            Logger::dayLog('backstage/red','同步保存资金账单失败',$postdata,$oRepay->errinfo);
        }
        return $res;
    }


    /**
     * Undocumented function
     * 充值对账
     * @return void
     */
    public function runRecharge(){
        $initRet = ['total' => 0, 'success' => 0];
        $num = 500;
        $oRed = new RedBill();
        $billData = $oRed->getRedInitData($num);
        if (!$billData) {
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($billData, 'id');
        $orders = ArrayHelper::getColumn($billData, 'orderId');
        //获取米富红包
        $peaData = (new PeaRed)->getRedData($orders);
        $count = count($billData);
        $success = 0;
        if(empty($peaData)){
            $up_data = [
                'type'          => RedBill::BILL_TYPE_FAIL,
                'error_info'    => RedBill::BILL_ERROR_ONE,
                'modify_time'   => date('Y-m-d H:i:s')
            ];
            $res= $oRed->upRepayBill($up_data,$ids);
            if(!$res){
                Logger::dayLog('backstage/runRed','upRepayBill','更新数据失败',$up_data,$ids);
            }else{
                $success = $count;
            }
            return ['total' => $count, 'success' => $success];
        }
        foreach($billData as $key=>$oRedBill){
            $res = $this->doRecharge($oRedBill,$peaData);          
            if($res){
                $success++;
            }
        }
        return ['total' => $count, 'success' => $success];
    }

    private  function doRecharge($oRedBill,$cgData){
        $oRedBill->refresh();
        $isLock = $oRedBill->lockOneBill();
        if(!$isLock){
            Logger::dayLog('backstage/runRepay', 'lockOneBill', '锁失败', $oRedBill->id);
            return false;
        }
        $orderId = $oRedBill->orderId;
        $cgAmount = $oRedBill->actAmount;
        $cgAccountId = $oRedBill->accountId;
        if(array_key_exists($orderId,$cgData)){
            $redData = $cgData[$orderId];
            $type = RedBill::BILL_TYPE_SUCC;//账单类型 //成功 差错
            $error_info = $this->getErrorInfo($cgAmount,$cgAccountId,$redData);
            
            if($error_info){
                //存在差错
                $type = RedBill::BILL_TYPE_FAIL;
            }

            $up_data = [
                'type' => $type,
                'error_info' => $error_info,
                'user_id' => ArrayHelper::getValue($redData,'user_id',0),
                'pea_type' => ArrayHelper::getValue($redData,'type',0),
                'standard_id' =>  ArrayHelper::getValue($redData,'standard_id',0),
                'pea_accountId' => ArrayHelper::getValue($redData,'accountId',0),
                'pea_txAmount' => ArrayHelper::getValue($redData,'txAmount',0),
                'pea_forAccountId' => ArrayHelper::getValue($redData,'forAccountId',''),
                'pea_desLine' => ArrayHelper::getValue($redData,'desLine',''),
                'order_id' => ArrayHelper::getValue($redData,'orderId',''),
                'pea_status' => ArrayHelper::getValue($redData,'status',0)
            ];
        }else{
            $error_info = RedBill::BILL_ERROR_ONE;
            $type = RedBill::BILL_TYPE_FAIL;
            $up_data = [
                'type'          => $type,
                'error_info'    => $error_info,
            ];
        }
        //更新数据
        $res = $oRedBill->updateData($up_data);
        if(!$res){
            Logger::dayLog('backstage/runRed','updateData','更新数据失败',$oRedBill->errinfo,$up_data,$oRedBill->attributes);
        }
        return $res;
    }
    public function getErrorInfo($cgAmount,$cgAccountId,$redData){

        $error_info = RedBill::BILL_ERROR_INIT;//差错类型 
        if(empty($redData)){
            $error_info = RedBill::BILL_ERROR_ONE;
            return $error_info;
        }
        $peaAmount = ArrayHelper::getValue($redData,'txAmount',0);
        $peaStatus = ArrayHelper::getValue($redData,'status',0);
        $peaAccountId = ArrayHelper::getValue($redData,'accountId','');
        if($peaStatus != '3'){
            $error_info = RedBill::BILL_ERROR_STATUS;
            return $error_info;
        }
        if(intval($cgAmount) != intval($peaAmount)){
            $error_info = RedBill::BILL_ERROR_AMOUNT;
            return $error_info;
        }       
        if($cgAccountId != $peaAccountId){
            $error_info = RedBill::BILL_ERROR_ACCOUNT;
            return $error_info;
        }
        return $error_info;
    }



    public function downRedXls($orderData ,$type) {

        $icount = count($orderData);
    // 创建一个处理对象实例
        $objExcel = new \PHPExcel();

    // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();
        if($type ==  2){
            $sheetName = '米富明细';
        }else{
            $sheetName = '存管明细';
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
        $objActSheet->setCellValue('C1', '贴息类型');
        $objActSheet->setCellValue('D1', '电子账号');
        $objActSheet->setCellValue('E1', '金额');
        $objActSheet->setCellValue('F1', '订单状态');
        $objActSheet->setCellValue('G1', '对账状态');
        for ($i = 0; $i < $icount; $i++) {
            if($type == 2){
                $orderId = ArrayHelper::getValue($orderData[$i], 'order_id');
                $accountId = ArrayHelper::getValue($orderData[$i], 'pea_accountId');
                $txState =  ArrayHelper::getValue($orderData[$i], 'pea_status');
                $status = ($txState == '3') ? '成功':'失败';
                $money =  ArrayHelper::getValue($orderData[$i], 'pea_txAmount');
            }else{
                $orderId = ArrayHelper::getValue($orderData[$i], 'orderId');
                $accountId = ArrayHelper::getValue($orderData[$i], 'accountId');
                $status = '成功';
                $money =  ArrayHelper::getValue($orderData[$i], 'actAmount');
            }
            $types = ArrayHelper::getValue($orderData[$i], 'type');
            $redType = $this->getBedtype();
            $res = ($types == '1') ? '正常':'差错';

            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'bill_time'));
            $objActSheet->setCellValue('B' . ( $i + 2), $orderId);
            $objActSheet->setCellValue('C' . ( $i + 2), $redType[ArrayHelper::getValue($orderData[$i], 'red_type')]);
            $objActSheet->setCellValue('D' . ( $i + 2), $accountId);
            $objActSheet->setCellValue('E' . ( $i + 2), $money);
            $objActSheet->setCellValue('F' . ( $i + 2), $status);
            $objActSheet->setCellValue('G' . ( $i + 2), $res);

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

    public function getBedtype(){
        return [
            '1' => '红包领取',
            '2' => '好友分润',
            // '3' => '加息券',
        ];
    }
    
}
<?php
/**
 * 清结算对账
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27
 * Time: 14:25
 */
namespace app\commands;
use app\models\bill\ComparativeBill;
use app\models\bill\UpBillFile;
use app\models\Channel;
use app\models\yyy\YiUserRemitList;
use app\modules\settlement\common\CbillRemit;
use Yii;
use yii\helpers\ArrayHelper;
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

$basePath = Yii::$app->basePath.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'phpexcel'.DIRECTORY_SEPARATOR;
if (!class_exists('PHPExcel')) {
    include $basePath.'PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include $basePath.'Excel5.php';
}
if (!class_exists('Spreadsheet_Excel_Reader')) {
    include $basePath.'reader.php';
}
include $basePath.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php';

class CheckremitController extends BaseController
{

    public function runRemits()
    {

        $oUpBillFile = new UpBillFile();
        //查找数据
        $file_data = $this->getData($oUpBillFile);
        if (empty($file_data)){
            return false;
        }
        //锁定数据
        $ids = ArrayHelper::getColumn($file_data, 'id');
        $ups = $oUpBillFile->lockRemit($ids);
        if (empty($ups)){
            return false;
        }
        //处理数据
        foreach($file_data as $value){
            //处理数据
            $this->processingData($value);
            //修改表中的状态 
            $value->successRemit();
        }
    }

    /**
     * 查找数据
     * @param UpBillFile $oUpBillFile
     * @return array|\yii\db\ActiveRecord[]
     */
    private function getData(UpBillFile $oUpBillFile)
    {
        $limit = 100;
        return $oUpBillFile->getData($limit);
    }

    /**
     * 数据处理
     * @param $data_set
     * @return bool
     */
    private function processingData($data_set)
    {
        //判断文件是否存在
        if (empty($data_set['bill_file'])){
            return false;
        }
        $channel_id = ArrayHelper::getValue($data_set, 'channel_id' ,0);
        if ($channel_id == 0){
            return false;
        }
        //读取excel文件数据
        $oPHPExcel = $this->readExcelData($data_set['bill_file']);

        //数据处理
        $sheet = $oPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(10); // 取得总列数
        $num = 0;
        for($j=2;$j<=$highestRow;$j++) {
            $data_key = $this->fileFormatInfo();
            $data_value = [];
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                $key_val = $oPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                $data_value[$data_key[$k]] = empty($key_val) ? "" : trim($key_val);
            }
            if (empty($data_value)){
                continue;
            }
            $oComparativeBill = new ComparativeBill();
            //如果存在不插入
            $client_info = $oComparativeBill->getClientChannelOne($data_value['client_id'], $channel_id);
            if (!empty($client_info)){
                continue;
            }
            $data_value['uid'] = $data_set['uid'];
            $data_value['bill_create_time'] = $data_set['create_time'];
            $data_value['channel_id'] = $channel_id;
            $save_data = $this->contrastData($data_value);
            //插入数据
            $oComparativeBill->saveData($save_data);
            $num ++;
        }
        return $num;
    }

    /**
     * 读取excel对象
     * @param $bill_file
     * @return bool|\PHPExcel
     * @throws \PHPExcel_Reader_Exception
     */
    private function readExcelData($bill_file)
    {
        $php_ext = explode(".", $bill_file);
        if (empty($php_ext[1])){
            return false;
        }
        if($php_ext[1] == 'xlsx'){
            $oReader= \PHPExcel_IOFactory::createReader('Excel2007');
            $oPHPExcel = $oReader->load($bill_file,'utf-8');
        }elseif($php_ext[1] == 'xls'){
            $oReader= \PHPExcel_IOFactory::createReader('Excel5');
            $oPHPExcel = $oReader->load($bill_file,'utf-8');
        }else{
            return false;
        }
        return $oPHPExcel;
    }

    /**
     * @return array
     */
    private function fileFormatInfo()
    {
        return [
            'A' => 'client_id', //商户订单号
            'B' => 'guest_account_name', //收款人姓名
            'C' => 'guest_account_bank',//收款人开户行
            'D' => 'guest_account',//收款人银行卡号
            'E' => 'identityid',//收款人证件号
            'F' => 'user_mobile',//收款人手机号
            'G' => 'settle_amount',//金额
            'H' => 'settle_fee',//手续费
            'I' => 'status',//付款状态
            'J' => 'bill_number',//账单日期
        ];
    }

    /**
     * 数据对比
     * @param array $data_set
     * @return array
     */
    private function contrastData(array $data_set)
    {
        //1.上游通道数据
        $up_status   = $this->getUpPassagewayStatus(ArrayHelper::getValue($data_set, 'status', ''));
        //2.支付通道数据
        //获取库类
        $oCbillRemit = new CbillRemit();
        $oRemit      = $oCbillRemit->createObject(ArrayHelper::getValue($data_set, 'channel_id', 0));
        //获取出款数据
        $remit_data  = $oRemit -> getRemitOne(ArrayHelper::getValue($data_set, 'client_id', 0));
        //$remit_data = $oRemit -> getRemitOne("201705091659373732");

        //支付系统状态
        $remit_status = $oCbillRemit->payPassagewayStatus(ArrayHelper::getValue($remit_data, 'remit_status', 0));

        //3.一亿元通道数据
        $req_id = ArrayHelper::getValue($remit_data, 'req_id', 0);
        $oUserRemitList  = new YiUserRemitList();
        $user_remit_data = $oUserRemitList->getDataByReqId($req_id);
        $yi_remit_status = $oCbillRemit->yiPassagewayStatus(ArrayHelper::getValue($user_remit_data, 'remit_status', ''));

        //4.差错类型
        $error_types = $this->errorTypes($data_set, $remit_data, $user_remit_data);

        //5. 账单类型
        $type = 2;
        if ($error_types == 0 && $up_status == 1 && $remit_status == 2 && $yi_remit_status == 4){
            $type = 1;
        }
        //通道商编号client_number
        $oChannel                       = new Channel();
        $data_set['client_number']      = $oChannel->getMechartNum(ArrayHelper::getValue($user_remit_data, 'payment_channel', 0));
        $data_set['bill_create_time']   = ArrayHelper::getValue($data_set, 'bill_create_time', 0); //连接文件上传时间
        $data_set['type']               = $type; //账单类型：1正常，2差错, 3处理错误
        $data_set['error_types']        = $error_types; //差错类型
        $data_set['channel_status']     = (int)$up_status + (int)$remit_status + (int)$yi_remit_status;
        $data_set['amount']             = ArrayHelper::getValue($remit_data, 'settle_amount', 0);
        return $this->formatData($data_set);

    }


    /**
     * 获取上游通道出款状态
     * @param $status_str
     * @return int  0失败  1成功
     */
    private function getUpPassagewayStatus($status_str)
    {
        if (empty($status_str)){
            return 0;
        }
        preg_match("/成功/", $status_str, $succ_str);
        if (!empty($succ_str)){
            return 1;
        }
        return 0;
    }


    /**
     * error_types 差错类型:
     * 2:支付系统单边账
     * 6:业务系统单边账
     * @param $up_data
     * @param $pay_data
     * @param $yi_data
     * @return int
     */
    private function errorTypes($up_data, $pay_data, $yi_data)
    {
        //支付通道和业务系统不存在
        if (empty($pay_data) && empty($yi_data)){
            return 1;  //1:通道单边账
        }
        //支付系统有误
        $up_money  = ArrayHelper::getValue($up_data, 'settle_amount', '');
        $pay_money = ArrayHelper::getValue($pay_data, 'settle_amount', '');
        if (bccomp($up_money, $pay_money) != 0){
            return 3; //3:支付系统有误
        }

        //支付系统状态有误
        $pay_status = ArrayHelper::getValue($pay_data, 'remit_status', 0);
        if (!in_array($pay_status, [6, 11])){
            return 4; //4:支付系统状态有误
        }

        //业务系统不存在
        if (empty($yi_data)){
            return 5; //5:支付对业务单边账
        }

        //业务系统金额有误
        $yi_money = ArrayHelper::getValue($yi_data, 'settle_amount', '');
        if (bccomp($pay_money, $yi_money) != 0){
            return 7; //7:业务系统金额有误
        }
        //业务系统状态有误
        $yi_status = ArrayHelper::getValue($yi_data, 'remit_status', '');
        if (!in_array(strtolower($yi_status),['success', 'fail'])){
            return 8; //8:业务系统状态有误
        }
        return 0;

    }

    /**
     * 格式保存在数据
     * @param array $data_set
     * @return array
     */
    private function formatData(array $data_set)
    {
        return [
            'client_id' 			=> ArrayHelper::getValue($data_set, 'client_id', ''), //商户订单号',
            'channel_id' 			=> ArrayHelper::getValue($data_set, 'channel_id', 0), //出款通道id：0:未知,1:融宝,2:宝付,3:畅捷,4:玖富,5:微神马,6:新浪,7:小诺理财',
            'child_channel_id' 		=> ArrayHelper::getValue($data_set, 'client_number', 0), //出款通道子集',
            'client_number' 		=> ArrayHelper::getValue($data_set, 'client_number', 0), //通道商编号',
            'guest_account_name' 	=> ArrayHelper::getValue($data_set, 'guest_account_name', ''), //收款人姓名',
            'guest_account_bank' 	=> ArrayHelper::getValue($data_set, 'guest_account_bank', 'ff'), //收款人银行',
            'guest_account' 		=> ArrayHelper::getValue($data_set, 'guest_account', ''), //收款人银行卡号',
            'identityid' 			=> ArrayHelper::getValue($data_set, 'identityid', ''), //收款人证件号',
            'user_mobile'	 		=> ArrayHelper::getValue($data_set, 'user_mobile', ''), //收款人手机号',
            'settle_amount' 		=> ArrayHelper::getValue($data_set, 'settle_amount', ''), //借款本金(单位：元)',
            'amount' 				=> ArrayHelper::getValue($data_set, 'amount', ''), //出款借款本金(单位：元)',
            'settle_fee' 			=> ArrayHelper::getValue($data_set, 'settle_fee', ''), //手续费(单位：元)',
            'uid' 					=> ArrayHelper::getValue($data_set, 'uid', 1), //用户uid',
            'error_types' 			=> ArrayHelper::getValue($data_set, 'error_types', 0), //差错类型:1:通道单边账,2:支付系统单边账,3:支付系统有误,4:支付系统状态有误,5:支付对业务单边账,6:业务系统单边账,7:业务系统金额有误,8:业务系统状态有误,9:关闭订单',
            'error_status' 			=> ArrayHelper::getValue($data_set, 'error_status', 2), //差错状态 1:已处理 2:未处理  3:关闭订单',
            'channel_status' 		=> ArrayHelper::getValue($data_set, 'channel_status', 0), //通道状态',
            'type' 					=> ArrayHelper::getValue($data_set, 'type', 0), //账单类型：1正常，2差错, 3处理错误',
            'reason' 				=> ArrayHelper::getValue($data_set, 'reason', ''), //原因',
            'bill_create_time' 		=> ArrayHelper::getValue($data_set, 'bill_create_time', ''), //连接文件上传时间',
            'bill_number' 			=> ArrayHelper::getValue($data_set, 'bill_number', ''), //账单日期',
        ];
    }

}
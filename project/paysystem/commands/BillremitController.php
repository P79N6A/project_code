<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27
 * Time: 14:25
 */
namespace app\commands;
use app\common\Logger;
use app\models\bill\BillDetails;
use app\models\bill\BillOriginal;
use app\models\bill\ChannelBills;
use app\models\open\BfRemit;
use app\models\open\CjRemit;
use app\models\open\Rbremit;
use Yii;
use yii\helpers\ArrayHelper;
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

if (!class_exists('PHPExcel')) {

    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

if (!class_exists('Spreadsheet_Excel_Reader')) {
    include Yii::$app->basePath.'/common/phpexcel/reader.php';
}
include Yii::$app->basePath.'/common/phpexcel/PHPExcel/IOFactory.php';

class BillremitController extends BaseController
{
    private $channel_bill_object;
    private $bill_detail_object;
    public function runRemits()
    {
        $this->channel_bill_object = new ChannelBills();
        $this->bill_detail_object = new BillDetails();

        $initRet = ['total' => 0, 'success' => 0];

        //获取数据
        $bill_data = $this->channel_bill_object->getChannelBillData();
        if (!$bill_data) {
            return $initRet;
        }


        //锁定状态为出款中
        $ids = ArrayHelper::getColumn($bill_data, 'id');
        $ups = $this->channel_bill_object->lockRemit($ids);
        if (!$ups) {
            return $initRet;
        }
        //逐条处理
        $total = count($bill_data);
        $success = 0;
        foreach($bill_data as $value){
            $state = $this->processingData($value);
            if ($state){
                $success = $state;
            }
        }
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        Logger::dayLog("bill/billremit", "content:".implode(',',$initRet));
    }

    /**
     * 数据处理
     * @param $data_set
     * @return bool
     */
    private function processingData($data_set)
    {
        if (empty($data_set->channel_file)){
            return false;
        }
        $num = $this->readExcelData($data_set);
        $this->updateChannelBill($data_set);
        return $num;
    }

    private function readExcelData($data_set)
    {
        $file_name = $data_set->channel_file;
        $php_ext = explode(".", $file_name);
        if (empty($php_ext[1])){
            return false;
        }
        if($php_ext[1] == 'xlsx'){
            $objReader= \PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($file_name,'utf-8');
        }elseif($php_ext[1] == 'xls'){
            $objReader= \PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($file_name,'utf-8');
        }else{
            return false;
        }
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(10); // 取得总列数
        $num = 0;
        for($j=2;$j<=$highestRow;$j++) {
            $str = '';
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() . '\\';//读取单元格
            }
            //Logger::dayLog("bill/billremit", "content:".$str);
            $strs = explode("\\", $str);

            //插入BillOriginal数据
            $obilloriginal = new BillOriginal();
            $original_one_data = $obilloriginal->getOneData($strs[0], $data_set->channel_id);
            if (empty($original_one_data)){
                $bill_original_state = $this->saveBillOriginal($strs, $obilloriginal, $data_set->channel_id);
                if (empty($bill_original_state)){
                    continue;
                }
            }
            //查看数据是否存在
            $obill_detail = new BillDetails();
            $bill_info = $obill_detail->getClientId($strs[0], $data_set->channel_id);
            if (!empty($bill_info)){
                continue;
            }

            $diff_bill_data = $this->getBillData($data_set->channel_id, $strs[0]);
            if (empty($diff_bill_data)){
                $s_state = $this->saveBill($strs, $data_set, "上游单边账");
            }else{
                $money_diff_stats = $this->montyDiff($diff_bill_data->settle_amount, $strs[6]);
                $error_types = $money_diff_stats ? "" : "金额有误";
                $s_state = $this->saveBill($strs, $data_set, $error_types, $diff_bill_data);
            }
            if ($s_state){
                $num ++;
            }
        }
        return $num;
    }

    private function saveBillOriginal($data_set, $obilloriginal, $type)
    {
        if (empty($type)){
            return false;
        }
        $data = [
            'client_id'=>ArrayHelper::getValue($data_set, 0, 0), //商户订单号',
            'guest_account_bank'=>ArrayHelper::getValue($data_set, 2, 0), //$data_set[2], //收款人开户行',
            'settle_amount'=>ArrayHelper::getValue($data_set, 6, 0), //$data_set[6], //金额(单位：元)',
            'settle_fee'=>ArrayHelper::getValue($data_set, 7, 0), //$data_set[7], //手续费',
            'identityid'=>ArrayHelper::getValue($data_set, 4, 0), //$data_set[4], //收款人证件号',
            'user_mobile'=>ArrayHelper::getValue($data_set, 5, ''), //$data_set[5], //收款人手机号',
            'guest_account_name'=>ArrayHelper::getValue($data_set, 1, ''), //$data_set[1], //收款人姓名',
            'guest_account'=>ArrayHelper::getValue($data_set, 3, 0), //$data_set[3], //收款人银行卡号',
            'status'=>0, //状态:0:初始;1:成功3:重试;11:失败',
            'type'=>$type, //状态:1:融宝 2:宝付;3:畅捷',
            'bill_type'=>ArrayHelper::getValue($data_set, 8, ''), //$data_set[8], //付款状态',
            'bill_time'=>ArrayHelper::getValue($data_set, 9, ''), //$data_set[9], //账单日期',
            //'create_time'=>$data_set[0], //创建时间',
            //'update_time'=>$data_set[0], //修改时间',
        ];
        return $obilloriginal -> saveData($data);
    }

    /**
     * 金额对比
     * @param $channel_money
     * @param $money
     * @return bool
     */
    private function montyDiff($channel_money, $money)
    {
        if (empty($channel_money) || empty($money)){
            return false;
        }
        if (bccomp($channel_money, $money)==0){
            return true;
        }
        return false;
    }


    /**
     * 获取商务订单号对应的数据
     * @param $channel_id
     * @param $client_id
     * @return array
     */
    private function getBillData($channel_id, $client_id)
    {
        //融宝
        if ($channel_id == 1){
            $remit_object = new Rbremit();
        }elseif($channel_id == 2){ //宝付
            $remit_object = new BfRemit();
        }elseif($channel_id == 3){ //畅捷
            $remit_object = new CjRemit();
        }else{
            return false;
        }
        $remit_one_info = $remit_object->getRemitOne($client_id);
        return $remit_one_info;
    }

    /**
     *  记录对账的
     * @param $data_set
     * @param $bill_data
     * @param string $error_types
     * @param string $channel_bill
     * @return bool
     */
    private function saveBill($data_set, $bill_data, $error_types='', $channel_bill='')
    {
        if (empty($data_set) || empty($bill_data)){
            return false;
        }
        $save_data = [
            'client_id' => ArrayHelper::getValue($data_set, 0, 0), //商户订单号',
            'channel_id' => ArrayHelper::getValue($bill_data, 'channel_id', 0), //出款通道id',
            'guest_account_bank' => ArrayHelper::getValue($data_set, 2, 0), //收款人银行',
            'guest_account_name' => ArrayHelper::getValue($data_set, 1, 0), //收款人姓名',
            'guest_account' => ArrayHelper::getValue($data_set, 3, 0), //收款人银行卡号',
            'identityid' => ArrayHelper::getValue($data_set, 4, 0), //收款人证件号',
            'settle_amount' =>  ArrayHelper::getValue($data_set, 6, 0), //借款本金(单位：元)',
            'amount' => (float)$channel_bill->settle_amount, //出款借款本金(单位：元)',
            'settle_fee' => ArrayHelper::getValue($data_set, 7, 0), //结算手续费',
            'user_mobile' => ArrayHelper::getValue($data_set, 5, 0), //收款人手机号',
            'error_types' => (string)$error_types, //差错类型',
            'error_status' => 2, //差错状态:1差错已处理',
            'type' => empty($error_types) ? 1 : 2, //账单类型：1正常，2差错',
            'bill_number' => (int)ArrayHelper::getValue($bill_data, 'bill_number', ''), //账单编号',
            'reason' => '', //原因',
            //'create_time' => '', //创建时间',
            //'modify_time' => '', //更新时间',
        ];
        $obill_detail = new BillDetails();
        $save_state = $obill_detail->saveBillDetails($save_data);
        return $save_state;
    }

    private function updateChannelBill($data_set)
    {
        //总笔数
        $total_pen_count = $this->bill_detail_object->totalPenCount($data_set->channel_id, $data_set->bill_number);
        //交易总金额
        $total_money = $this->bill_detail_object->totalMoney($data_set->channel_id, $data_set->bill_number);
        //结算手续费
        $total_settle_fee = $this->bill_detail_object->totalSettleFee($data_set->channel_id, $data_set->bill_number);

        $update_data = [
            'total_pen_count' => (int)$total_pen_count, //总笔数',
            'total_money' => (float)$total_money, //总金额/元',
            'withdraw_fee' => (float)$total_settle_fee, //手续费/元',
            //`source` tinyint(1) unsigned DEFAULT '1' COMMENT '来源：1未下载，2，已上传，3已下载',
            'audit_status' => 3,//对账状态：1未对账，2锁定 3已对账',
        ];
        return $data_set -> updateChannelBill($update_data);
    }

}
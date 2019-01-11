<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/27
 * Time: 14:25
 */
namespace app\commands;

use app\common\Common;
use app\common\Logger;
use app\models\bill\BillDetails;
use app\models\bill\BillOriginal;
use app\models\open\BfRemit;
use app\models\open\CjRemit;
use app\models\open\Rbremit;
use Yii;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class BilloriginalController extends BaseController
{
    public function runRemits()
    {
        $initRet = ['total' => 0, 'success' => 0];
        $obilloriginal = new BillOriginal();
        //计算有多少条

        $total = $obilloriginal->getCount();
        if ($total == 0 ){
            return $initRet;
        }
        $limit = 100;
        $pages = ceil($total / $limit);
        $success = 0;
        for($i=0; $i<$pages; $i++){
            //获取数据
            $bill_data = $obilloriginal->getData($limit);
            if (empty($bill_data)){
                return false;
            }
            //修改中间状态
            $id_string = Common::ArrayToString($bill_data, 'id');
            $lock_num = $obilloriginal->lockStatus($id_string);
            if ($lock_num == 0){
                return false;
            }
            foreach($bill_data as $value){
                $state = $this->processingData($value);
                if ($state){
                    $success ++ ;
                }
            }

        }
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        print_r($initRet);
    }
    private function processingData($data_set)
    {

        if (empty($data_set) || empty($data_set['client_id'])){
            return false;
        }
        //查看数据是否存在
        $obill_detail = new BillDetails();
        $bill_info = $obill_detail->getClientId($data_set['client_id'], $data_set['type']);
        if (!empty($bill_info)){
            return false;
        }
        //获取对比数据
        $ocontrast = $this->getChannelObject($data_set);
        $contrast_info = $ocontrast->getRemitOne($data_set['client_id']);
        $state = $this->saveBill($data_set, $contrast_info);
        if ($state){
            $obilloriginal = new BillOriginal();
            $get_bill_info = $obilloriginal->getInfo($data_set['id']);
            $get_bill_info->successStatus();
        }
        return $state;

    }

    /**
     * 获取出款类
     * @param $data_set
     * @return BfRemit|CjRemit|Rbremit|bool
     */
    private function getChannelObject($data_set)
    {
        if (empty($data_set['type'])){
            return false;
        }
        //融宝
        if ($data_set['type'] == 1){
            return new Rbremit();
        }
        //宝付
        if ($data_set['type'] == 2){
            return new BfRemit();
        }
        //畅捷
        if ($data_set['type'] == 3){
            return new CjRemit();
        }
        return false;
    }

    /**
     * 记录对账的
     * @param $otherbill
     * @param $targetbill
     * @return bool
     */
    private function saveBill($otherbill, $targetbill)
    {
        $settle_amount =  (float)ArrayHelper::getValue($otherbill, 'settle_amount', 0);
        $amount = (float)ArrayHelper::getValue($targetbill, 'settle_amount', 0);
        $money_diff_stats = $this->montyDiff($settle_amount, $amount);
        //错误类型
        $error_types = '';
        if (empty($targetbill)){
            $error_types = '上游单边账';
        }
        if ($money_diff_stats === 0){
            $error_types = '金额有误';
        }
        //bill_number
        $bill_number = date("Ymd", strtotime(ArrayHelper::getValue($otherbill, 'bill_time', '')));

        $save_data = [
            'client_id' => ArrayHelper::getValue($otherbill, 'client_id', 0), //商户订单号',
            'channel_id' => ArrayHelper::getValue($otherbill, 'type', 0), //出款通道id',
            'guest_account_bank' => ArrayHelper::getValue($otherbill, 'guest_account_bank', 0), //收款人银行',
            'guest_account_name' => ArrayHelper::getValue($otherbill, 'guest_account_name', ''), //收款人姓名',
            'guest_account' => ArrayHelper::getValue($otherbill, 'guest_account', 0), //收款人银行卡号',
            'identityid' => ArrayHelper::getValue($otherbill, 'identityid', 0), //收款人证件号',
            'settle_amount' => $settle_amount, //借款本金(单位：元)',
            'amount' => $amount, //出款借款本金(单位：元)',
            'settle_fee' => ArrayHelper::getValue($otherbill, 'settle_fee', 0), //结算手续费',
            'user_mobile' => ArrayHelper::getValue($otherbill, 'user_mobile', 0), //收款人手机号',
            'error_types' => (string)$error_types, //差错类型',
            'error_status' => 2, //差错状态:1差错已处理',
            'type' => empty($error_types) ? 1 : 2, //账单类型：1正常，2差错',
            'bill_number' => $bill_number, //账单编号',
            'reason' => '', //原因',
            //'create_time' => '', //创建时间',
            //'modify_time' => '', //更新时间',
        ];
        //Logger::dayLog("bill/billremit", "content:".implode(",", $save_data));

        $obill_detail = new BillDetails();
        $save_state = $obill_detail->saveBillDetails($save_data);
        return $save_state;
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
}
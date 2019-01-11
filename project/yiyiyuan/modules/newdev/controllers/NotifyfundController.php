<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Crypt3Des;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\User_loan;
use app\models\news\User_remit_list;
use Yii;

class NotifyfundController extends NewdevController{
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    public function actionIndex(){
        $data = $this->post('res_data');
        Logger::errorLog(print_r($data, true), 'FundNotify', 'Fund');
        $api = new ApiClientCrypt();
        $result = Crypt3Des::decrypt($data,$api->getKey());
        Logger::errorLog(print_r($result, true), 'FundNotify', 'Fund');
        $remitArr = json_decode($result, true);
        if(!is_array($remitArr)){
            $return = [
                'res_code' => '200',
                'res_data' => '解析失败',
            ];
            return Crypt3Des::encrypt(json_encode($return),$api->getKey());
        }

        $returnData = [];
        foreach ($remitArr as $k => $v){
            $returnData[$v] = $this->todo($v);
        }
        $returnArr = [
            'res_code' => '0000',
            'res_data' => $returnData,
        ];
        return Crypt3Des::encrypt(json_encode($returnArr),$api->getKey());

    }

    private function todo($orderId){
        if(!$orderId){
            return null;
        }
        $userRemit = User_remit_list::find()->where(['order_id'=>$orderId])->one();
        if(!$userRemit || empty($userRemit->loan)){
            return null;
        }

        $repaidCapital = $userRemit->loan->getRepayAmount(2);
        $repaidCapital = $repaidCapital === NULL ? 0 : $repaidCapital;

        return [
            'dueAt' => !empty($userRemit->loan->end_date) ? date('Y-m-d', (strtotime($userRemit->loan->end_date) - 24 * 3600)) : '',//应还日期
            'repayAtPartial' => $userRemit->loan->status == 8 ? $userRemit->loan->repay_time : '',//本息还清日期
            'repayAt' => $userRemit->loan->status == 8 ? $userRemit->loan->repay_time : '',//实际全部还清日期（+罚息）
            'status' => $this->getStatus($userRemit),//还款状态（1.还款中 2.本息已完清 罚息未还清 3.全部已还清 ）
            'principal' => $userRemit->loan->amount,//应还本金
            'repaidCapital' => $repaidCapital,//已还本金
            'interest' => $userRemit->loan->interest_fee,//应还利息
            'repaidInterest' => $userRemit->loan->status == 8 ? $userRemit->loan->interest_fee : 0,//已还利息
            'repaidPenalty' => $userRemit->loan->status == 8 ? $userRemit->loan->interest_fee : 0,//已还罚息
            'overdues' => $userRemit->loan->getOverdueDays($userRemit->loan),//逾期天数
            'amount' => $userRemit->real_amount,//合同金额 没有就等于应还本金
            'lastRepayAt' => !empty($userRemit->loan->repay) ? $userRemit->loan->repay->createtime : '',//最近还款日期
            'loanSuccess' => $userRemit->remit_status == 'SUCCESS' ? 1 : 2,//是否放款成功（1.是 2.否）
            'periodStage' => 1,//当前期数
        ];
    }

    private function getStatus($userRemit){
        if ($userRemit->loan->status == 8){
            return 3;
        }else{
            return 1;
        }
    }

}

<?php

namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\OverdueLoan;
use app\models\news\Renew_amount;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_rate;
use app\modules\api\common\ApiController;
use Yii;

class BilldetailController extends ApiController
{

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $loan_id = Yii::$app->request->post('loan_id');
        if (empty($version) || empty($loan_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $oUserLoan = User_loan::find()->where(['loan_id'=>$loan_id])->one();
        if (empty($oUserLoan)) {
            exit($this->returnBack('10052'));
        }
        $user_id=$oUserLoan->user_id;
        $renewal_day=0;
        $amount=(new User_loan())->getRepaymentAmount($oUserLoan);//还款金额
        $principal=$oUserLoan->amount;//应还本金
        $loan_status=1;//正常
        if(in_array($oUserLoan->status,[12,13])){
            $loan_status=2;//已逾期
            $day=(new User_loan())->getOverdueDays($oUserLoan);//逾期天数
            //贷后管理费（即逾期费）
            $oOverdueLoan=OverdueLoan::find()->where(['loan_id'=>$loan_id])->one();
            if(!empty($oOverdueLoan)){
                $tem_amount = bcsub($oOverdueLoan->chase_amount,$oUserLoan->amount,2);
                $tem_amount = bcsub($tem_amount,$oUserLoan->withdraw_fee,2);
                $management_amount = bcsub($tem_amount,$oOverdueLoan->interest_fee,2);
            }else{
                $management_amount=0;
            }
        }else{
            $day_diff=strtotime($oUserLoan->end_date)-(24*3600)-strtotime(date('Y-m-d'));
            if($day_diff>0){
                $day=ceil(($day_diff)/3600/24);
            }else{
                $day=0;
            }
//            if(date('Y-m-d')==date('Y-m-d',strtotime($oUserLoan->end_date)-(24*3600))){
//                $day=1;
//            }
            $management_amount=0;
            if($oUserLoan->settle_type==3){
                $loan_status=3;//已续期
                $renewal_day=$oUserLoan->days;//借款天数
            }
        }

        //是否可以续期
        $renew_amout = (new Renew_amount())->entry($oUserLoan->loan_id);
        $is_renewal_able = 2;
        $is_inspect = 0;//合规展期
        if($renew_amout['type'] != 0){
            $is_renewal_able = 1;
            if($renew_amout['type'] == 3){
                $is_inspect = 1;
            }
        }

        //获取用户利率及日息
        $days=$oUserLoan->days;
        $rate = (new User_rate())->getRate($user_id);
        //利息
        $interest_amount=ceil(($oUserLoan->getInterestFee()) * 100) / 100;

        //最后还款日
        $last_day=date('Y-m-d',strtotime($oUserLoan->end_date)-24*3600);
        $array = [
            'amount'=>$amount,
            'principal'=>$principal,
            'loan_status'=>$loan_status,
            'day'=>$day,
            'renewal_day'=>$renewal_day,
            'interest_amount'=>$interest_amount,
            'management_amount'=>$management_amount,
            'is_renewal_able'=>$is_renewal_able,
            'last_day'=>$last_day,
            'is_inspect'=>$is_inspect,
        ];

        exit($this->returnBack('0000', $array));

    }


    
}

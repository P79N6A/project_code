<?php

namespace app\modules\api\controllers\controllers314;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\OverdueLoan;
use app\models\news\Renew_amount;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_rate;
use app\models\news\GoodsBill;
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
        $data =  ( new User_loan() )->getBillDetailData($oUserLoan);
        $array = [
            'amount'=>$data['amount'],
            'principal'=>$data['principal'],
            'loan_status'=>$data['loan_status'],
            'day'=>$data['day'],
            'renewal_day'=>$data['renewal_day'],
            'interest_amount'=>$data['interest_amount'],
            'management_amount'=>$data['management_amount'],
            'is_renewal_able'=>$data['is_renewal_able'],
            'last_day'=>$data['last_day'],
            'is_inspect'=>$data['is_inspect'],
            'period_num'=>$data['period_num'],
            'loan_type'=>$data['loan_type'],
            'overdue_bjamount'=>$data['overdue_bjamount'],
            'pay_goods_bill_id'=> empty($data['pay_goods_bill_id']) ? [] : $data['pay_goods_bill_id'],
        ];
        exit($this->returnBack('0000', $array));
    }
}

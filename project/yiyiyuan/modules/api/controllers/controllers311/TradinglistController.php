<?php

namespace app\modules\api\controllers\controllers311;

use app\models\news\Insure;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class TradinglistController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type', 1);
        if (empty($version) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $user = User::findOne($user_id);
        if (empty($user)) {
            $array = $this->returnBack('99997');
            echo $array;
            exit;
        }
        $array['list'] = $this->getLoanlist($user);
        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;
    }

    private function getLoanlist($user) {
        $loan = (new User_loan())->listLoan($user->user_id,[5,6,8,9,11,12,13]);
        $wx = $user->userwx;
        $list = [];
        if (empty($loan)) {
            return $list;
        }
        $userLoanModel = new User_loan();
        foreach ($loan as $key => $val) {
            $list[$key]['amount'] = round($val->amount, 2);
            $list[$key]['type'] = $val->business_type;
            $list[$key]['loan_id'] = $val->loan_id;
            if ($val->loan_id != $val->parent_loan_id && !empty($val->parent_loan_id)) {
                $list[$key]['time'] = $val->start_date;
            } else {
                $list[$key]['time'] = $val->create_time;
            }
            $list[$key]['head'] = !empty($wx) ? $wx->head : '';
            $list[$key]['settle_type'] = $val->settle_type;
            //借款状态
            $loanStatue = $userLoanModel->getLoanStatusView($val);
            if ($val->status == 9) {
                if ($val->settle_type == 3) {//已续期
                    $loanStatue['status'] = 24;
                }
            }
            $list[$key]['status'] = $loanStatue['status'];
        }
        return $list;
    }

}

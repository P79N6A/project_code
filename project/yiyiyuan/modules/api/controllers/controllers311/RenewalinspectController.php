<?php

namespace app\modules\api\controllers\controllers311;

use app\commonapi\Keywords;
use app\models\news\RenewalInspect;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class RenewalinspectController extends ApiController
{

    public $enableCsrfValidation = FALSE;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $loan_id = Yii::$app->request->post('loan_id');
        if (empty($version) || empty($loan_id)) {
            exit($this->returnBack('99994'));
        }
        if (Keywords::renewalInspectOpen() != 2) {
            exit($this->returnBack('10241'));
        }
        $o_user_loan = (new User_loan())->getById($loan_id);
        if (empty($o_user_loan)) {
            exit($this->returnBack('10052'));
        }
        $tiem = date('Y-m-d H:i:s');
        $time_in = date("Y-m-d H:i:s", strtotime("-5 day", strtotime($o_user_loan->end_date)));
        $over_time_in = date("Y-m-d H:i:s", strtotime("+3 day", strtotime($o_user_loan->end_date)));
        if ($tiem < $time_in || $tiem > $over_time_in) {
            exit($this->returnBack('10242'));
        }
        $o_renewal_inspect = (new RenewalInspect())->getByLoanId($loan_id);
        if (!empty($o_renewal_inspect)) {
            exit($this->returnBack('10243'));
        }
        $condition = [
            'loan_id' => $loan_id,
            'user_id' => $o_user_loan->user_id,
            'status' => 0,
            'is_show_status' => 0
        ];
        $result = (new RenewalInspect())->addRecord($condition);
        if (!empty($result)) {
            exit($this->returnBack('0000'));
        }
        exit($this->returnBack('10244'));
    }
}

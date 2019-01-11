<?php
namespace app\modules\sysloan\controllers;

use app\commonapi\ErrorCode;
use app\commonapi\Logger;
use app\models\news\GoodsBill;
use app\models\news\Promes;
use app\models\news\User_loan;
use app\models\news\User_remit_list;
use app\models\service\UserloanService;
use app\modules\sysloan\common\ApiController;
use Yii;

class LoanafterController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $required = ['loan_id'];  //必传参数
        $httpParams = $this->post();
        $this->BeforeVerify($required, $httpParams);

        $userLoanService = new UserloanService();
        $transaction = Yii::$app->db->beginTransaction();
        $result = $userLoanService->loanInstallment($httpParams['loan_id']);
        if ($result['rsp_code'] != '0000') {
            $transaction->rollBack();
            $result['rsp_msg'] = $this->codeReback($result['rsp_code']);
            exit(json_encode($result));
        }
        $transaction->commit();
        $result['rsp_msg'] = $this->codeReback($result['rsp_code']);
        $result['data'] = $this->resultArray($result['loan_id'], $httpParams['loan_id']);
        Logger::daylog('sysloan/loanafter', 'loan_id：' . $httpParams['loan_id'], $result);
        exit(json_encode($result));
    }

    private function resultArray($loanId, $oldLoanId)
    {
        $result = [];
        $array = [];
        $userLoanObj = (new User_loan())->getLoanById($loanId);
        if (!empty($userLoanObj) && is_object($userLoanObj)) {
            foreach ($userLoanObj as $key => $value) {
                $array[$key] = $value;
            }
            $result['username'] = $userLoanObj->user->realname;
            $result['mobile'] = $userLoanObj->user->mobile;
            $result['identity'] = $userLoanObj->user->identity;
            //普罗米
            $promesObj = (new Promes())->find()->where(['loan_id' => $oldLoanId])->one();
            $array['prome_score'] = !empty($promesObj) ? $promesObj->prome_score : 0;
            $array['prome_subject'] = !empty($promesObj) ? $promesObj->prome_subject : '';
            //放款时间
            $userRemitListObj = (new User_remit_list())->find()->where(['loan_id' => $oldLoanId, 'remit_status' => 'SUCCESS'])->one();
            $array['remit_time'] = !empty($userRemitListObj) ? $userRemitListObj->remit_time : '';
            $result['user_loan'] = $array;
        }
        $goodsBillObj = (new GoodsBill())->getRepaylist($loanId);
        if (!empty($goodsBillObj)) {
            foreach ($goodsBillObj as &$item) {
                $item['chase_amount'] = 0;
            }
            $result['bill'] = $goodsBillObj;
        }
        return $result;
    }

    private function codeReback($code)
    {
        $errorCode = new ErrorCode();
        return $errorCode->geterrorcode($code);
    }
}
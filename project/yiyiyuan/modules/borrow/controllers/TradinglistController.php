<?php

namespace app\modules\borrow\controllers;

use app\commonapi\Apidepository;
use app\commonapi\Keywords;
use app\models\news\Loan_pic;
use app\models\news\Renewal_payment_record;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_loan_flows;
use app\models\service\UserloanService;
use Yii;
use yii\helpers\ArrayHelper;

class TradinglistController extends BorrowController
{
    public $layout = 'custody/custody';

    public function behaviors()
    {
        $user_id = $this->get('user_id', 0);
        if (empty($user_id) || $user_id == 'empty') {
            return parent::behaviors();
        } else {
            $o_user = User::findOne($user_id);
            if (empty($o_user)) {
                parent::behaviors();
            }
            Yii::$app->newDev->login($o_user, 1);
            return [];
        }
    }

    public function actionIndex()
    {
        $this->getView()->title = "借款记录";
        $userInfo = $this->getUser();

        $jsinfo = $this->getWxParam();
        $loan_list = (new User_loan())->listLoan($userInfo->user_id, [],[1, 4, 5, 6, 9,11]);
        if (!empty($loan_list)) {
            $userLoanModel = new User_loan();
            foreach ($loan_list as $key => $value) {
                //判断借款初次发生时间
                if ($value['loan_id'] != $value['parent_loan_id'] && !empty($value['parent_loan_id)'])) {
                    $loan_list[$key]['create_time'] = $value['start_date'];
                }
                //借款状态
                $loanStatue = $userLoanModel->getLoanStatusView($value);
                $loan_list[$key]['status'] = $loanStatue['status'];
            }
        }
        return $this->render('index', [
            'loan_list' => $loan_list,
        ]);
    }

    public function actionList()
    {
        $this->getView()->title = "消费凭证上传";
        $user_id = $this->get('user_id', 0);
        $source = $this->get('source', 1);
        if ($user_id == 0) {
            $userInfo = $this->getUser();
        } else {
            $userInfo = User::findOne($user_id);
        }
        $oLoanpicModel = new Loan_pic();
        $list = $oLoanpicModel->getByUserId($userInfo->user_id);
        $loan_ids = ArrayHelper::getColumn($list, 'loan_id');
        $loan_list = User_loan::find()->where(['loan_id' => $loan_ids])->orderBy('create_time desc')->all();
        if (!empty($loan_list)) {
            $userLoanModel = new User_loan();
            foreach ($loan_list as $key => $value) {
                //判断借款初次发生时间
                if ($value['loan_id'] != $value['parent_loan_id'] && !empty($value['parent_loan_id)'])) {
                    $loan_list[$key]['create_time'] = $value['start_date'];
                }
                //借款状态
                $loanStatue = $userLoanModel->getLoanStatusView($value);
                $loan_list[$key]['status'] = $loanStatue['status'];
            }
        }
        return $this->render('list', [
            'source' => $source,
            'loan_list' => $loan_list,
        ]);
    }

    public function actionDetail()
    {
        $this->getView()->title = "借款详情";
        $loan_id = $this->get('loan_id');
        $userInfo = $this->getUser();
        $oUserLoan = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($oUserLoan)) {//loaninfo为空走首页,lml17/8/31
            return $this->redirect("/borrow/loan/");
        }
//        $jsinfo = $this->getWxParam();
        $userloanService = new UserloanService();
        $repay = $userloanService->getHuankuanTime($oUserLoan);
        $desc = '';
        $repay_time = $repay['huankuantime'];
        if ($oUserLoan->status == 7) {
            $reason = User_loan_flows::find()->select('reason')->where(['loan_id' => $loan_id])->orderBy('create_time desc')->one();
            $desc = !empty($reason['reason']) ? $reason['reason'] : '不符合借款标准';
        }
        if ($oUserLoan['settle_type'] == 2) {
            $repay = Renewal_payment_record::find()->select(array('last_modify_time'))->where(['loan_id' => $loan_id, 'status' => 1])->one();
            $repay_time = $repay['last_modify_time'];
        }

        $contract_show = FALSE;
        $contract_url = '';
        $is_contract = Keywords::contract();
        if($is_contract == 1){
            //借款合同
            $data = [
                'loan_id' => $loan_id,//测试loan_id：2237281365
                'dpi' => '160'
            ];
            $contract = (new Apidepository())->getContract($data);
            $contract = json_decode($contract, true);
            if ($contract['rsp_code'] == '0000' && !empty($contract['rsp_data']['url'])) {
                $contract_show = TRUE;
                $contract_url = $contract['rsp_data']['url'];
            }
        }

        return $this->render('detail', [
            'loan_info' => $oUserLoan,
            'repay_time' => $repay_time,
            'desc' => $desc,
            'contract_show' => $contract_show,
            'contract_url' => $contract_url
        ]);
    }

}

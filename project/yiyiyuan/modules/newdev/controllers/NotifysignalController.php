<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\User;
use app\models\news\User_credit;
use app\models\news\User_loan;
use app\models\news\UserCreditList;
use Yii;

class NotifysignalController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        $openApi = new ApiClientCrypt;
        $data = $this->post('data');
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('Signal_notify', $parr);
        $status=$parr['res_data']['status'];
        $mobile=$parr['res_data']['mobile'];
        $oUser=(new User())->getUserinfoByMobile($mobile);
        $oUserCredit=(new User_credit())->getUserCreditByUserId($oUser->user_id);
        if(empty($oUserCredit)){
            exit;
        }
        if($status==1) {
            $condition = [
                'pay_status' => 1,
                'invalid_time' => date('Y-m-d H:i:s', (time() + 24 * 3600)),
            ];
            $res = $oUserCredit->updateUserCredit($condition);
            if (!$res) {
                Logger::dayLog('Signal_notify_new', 'Authed err：' . $parr['res_data']['req_id'], $parr);
                $re = [
                    'res_code' => '0001'
                ];
                echo json_encode($re);
                exit;
            }
            //记录同步至历史记录表
            $list_result = (new UserCreditList())->synchro($oUserCredit->req_id);
            if (!empty($oUserCredit->loan_id)) {
                //老流程修改TB-SUCCESS
                $loanModel = User_loan::findOne($oUserCredit->loan_id);
                $this->postNotify($loanModel, $parr);
            }
            $re = [
                'res_code' => '0000'
            ];
            echo json_encode($re);
            exit();
        }
    }

    private function postNotify($loanModel, $parr)
    {
        if (empty($loanModel) || empty($parr)) {
            exit;
        }
        if (empty($loanModel->loanextend) || $loanModel->loanextend->status != 'TB-SUCCESS') {
            Logger::errorLog(print_r([$loanModel->loan_id=> "TB-SUCCESS"], true), 'signalNotify', 'Signal_notify');
            exit;
        }
        if ($loanModel->loanextend->status == 'AUTHED') {
            echo 'SUCCESS';
            exit;
        }
        if ($parr['res_code'] == 0) {
            $doAuthed = $loanModel->loanextend->doAuthed();
            if(!$doAuthed){
                Logger::dayLog('Signal_notify', 'Authed err：' . $loanModel->loan_id, $parr);
                exit;
            }
            $re = [
                'res_code' => '0000'
            ];
            echo json_encode($re);
            exit();
        }
        Logger::dayLog('Signal_notify', '未定义状态ID：' . $loanModel->loan_id, $parr);
    }
}

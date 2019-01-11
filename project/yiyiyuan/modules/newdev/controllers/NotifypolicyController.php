<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Crypt3Des;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use Yii;

class NotifypolicyController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        $data = $this->post('res_data');
        Logger::dayLog('notify/notifypolicy', date('Y-m-d H:i:s'), $data);
        $api = new ApiClientCrypt();
        $result = Crypt3Des::decrypt($data, $api->getKey());
        $remitArr = json_decode($result, true);
        if (!is_array($remitArr)) {
            $return = [
                'res_code' => '200',
                'res_data' => '解析失败',
            ];
            return Crypt3Des::encrypt(json_encode($return), $api->getKey());
        }

        $insuranceObj = (new Insurance())->getRecordByReqId($remitArr['req_id']);
        $result = '';
        //成功
        if (!empty($insuranceObj) && $remitArr['remit_status'] == 6) {
            $result = $insuranceObj->updateInsuranceOrder($remitArr['policy_no']);
        }
        //失败
        if (!empty($insuranceObj) && in_array($remitArr['remit_status'], [9, 11])) {
            $result = $insuranceObj->updateInsuranceOrder();
        }
        if ($result === true) {
            echo 'SUCCESS';
            exit();
        } elseif ($result === false) {
            Logger::dayLog('notify/notifypolicy', '保单状态更新失败：' . $remitArr['req_id'], $remitArr);
            exit();
        }
        Logger::dayLog('notify/notifypolicy', '未定义状态req_id：' . $remitArr['req_id'], $remitArr);
    }

    public function actionPay()
    {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = $this->get('res_data');
        } else {
            $data = $this->post('res_data');
        }
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('Pay_notify', $parr);
        
        $insure = (new Insure())->getInsuranceByOrderIdReqId($parr['res_data']['client_id'], $parr['res_data']['req_id']);
        if (empty($insure)) {
            return false;
        }
        $isPost = Yii::$app->request->isPost;
        if ($isPost) {
            $this->postNotify($insure, $parr);
        } else {
            return $this->getNotify($insure, $parr);
        }
    }

    private function getNotify($insure, $parr)
    {
        if ($insure->status == 0) {
            $conditon = ['status' => -1];
            $get_up_result = $insure->updateData($conditon);
            if (!$get_up_result) {
                Logger::dayLog('Pay_notify', 'get_update_faile' . $parr['res_data']['client_id'], $conditon);
            }
        }
        return $this->redirect('/new/repay/payverify?source='.$insure->source);
    }

    private function postNotify($insure, $parr)
    {
        if (empty($insure || empty($parr))) {
            exit;
        }
        if ($insure->status == 1 || $insure->status == 4) {
            echo 'SUCCESS';
            exit;
        }
        //$amount = isset($parr['res_data']['amount']) ? $parr['res_data']['amount'] / 100 : 0;
        if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 6) {//成功处理
            $data = [
                'status' => 1,
                'actual_money' => $insure->money,
                //'paybill' => $parr['res_data']['paybill'],
                'repay_time' => date('Y-m-d H:i:s'),
            ];
            $res = $insure->updateData($data);
            $loanExtend = (new User_loan_extend())->getUserLoanSubsidiaryByLoanId($insure->loan_id);
            if(empty($loanExtend)){
                Logger::dayLog('Pay_notify', 'empty Loan_entend：' . $parr['res_data']['client_id'], $parr);
                exit;
            }
            $doAuthed = $loanExtend->doAuthed();
            if(!$doAuthed){
                Logger::dayLog('Pay_notify', 'Authed err：' . $parr['res_data']['client_id'], $parr);
                exit;
            }
        } else if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 11) {//失败处理
            $data = [
                'status' => 4,
                //'paybill' => $parr['res_data']['paybill'],
                'repay_time'=>date('Y-m-d H:i:s'),
            ];
            $res = $insure->updateData($data);
        } else if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 4) {//失效处理
            $data = [
                'status' => 5,
                //'paybill' => $parr['res_data']['paybill'],
                //'repay_time'=>date('Y-m-d H:i:s'),
            ];
            $res = $insure->updateData($data);
        }
        if ($res === true) {
            echo 'SUCCESS';
            exit();
        } elseif ($res === false) {
            Logger::dayLog('Pay_notify', '保单状态更新失败：' . $parr['res_data']['client_id'], $parr);
            exit();
        }
        Logger::dayLog('Pay_notify', '未定义状态ID：' . $parr['res_data']['client_id'], $parr);
    }

    public function actionPayz()
    {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = $this->get('res_data');
        } else {
            $data = $this->post('res_data');
        }
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('Pay_notify', $parr);

        $insure = (new Insure())->getInsuranceByOrderIdReqId($parr['res_data']['client_id'], $parr['res_data']['req_id']);
        if (empty($insure)) {
            return false;
        }
        $isPost = Yii::$app->request->isPost;
        if ($isPost) {
            $this->postNotifyz($insure, $parr);
        } else {
            return $this->getNotify($insure, $parr);
        }
    }

    private function postNotifyz($insure, $parr)
    {
        if (empty($insure || empty($parr))) {
            exit;
        }
        if ($insure->status == 1 || $insure->status == 4) {
            echo 'SUCCESS';
            exit;
        }
        if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 6) {//成功处理
            $data = [
                'status' => 1,
                'actual_money' => $insure->money,
                'repay_time' => date('Y-m-d H:i:s'),
            ];
            $res = $insure->updateData($data);
        } else if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 11) {//失败处理
            $data = [
                'status' => 4,
                'repay_time'=>date('Y-m-d H:i:s'),
            ];
            $res = $insure->updateData($data);
        }
        if ($res === true) {
            echo 'SUCCESS';
            exit();
        } elseif ($res === false) {
            Logger::dayLog('Pay_notify', '保单状态更新失败：' . $parr['res_data']['client_id'], $parr);
            exit();
        }
        Logger::dayLog('Pay_notify', '未定义状态ID：' . $parr['res_data']['client_id'], $parr);
    }

    //续期还款服务器异步通知地址
    public function actionPayx() {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        $isPost = Yii::$app->request->isPost;
        if ($isPost) {
            $nofify_type = 'post';
        } else {
            $nofify_type = 'get';
        }
        $parr = $openApi->parseReturnData($data);
        Logger::errorLog(print_r($parr, true), 'renewal_repay_inc');

//        $nofify_type = 'post';
//        $parr = [
//            'res_code' => 0,
//            'res_data' => [
//                'req_id' => 'X748976420180201102008',
//                'client_id' => '748976420180201102008',
//                'remit_status' => '6',
//                'rsp_status' => 'S',
//                'tip' => '_unknown',
//                'policy_no' => '',
//                'app_id' => '2810335722015',
//            ]
//        ];


        $insure = (new Insure())->getInsuranceByOrderIdReqId($parr['res_data']['client_id'], $parr['res_data']['req_id']);
        if(!$insure || !$insure->loan_id){
            exit('error empty');
        }
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                return $this->getNotify($insure, $parr);
            } else {
                return $this->redirect('/new/repay/errorapp');
            }
        }
        $loaninfo = User_loan::findOne($insure->loan_id);
        if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 6) {//处理成功
            if ($insure->status == 1 || $insure->status == 4) {
                echo 'SUCCESS';
                exit;
            }
            $data = [
                'status' => 1,
                'actual_money' => $insure->money,
                'repay_time' => date('Y-m-d H:i:s'),
            ];
            $res = $insure->updateData($data);
            Logger::dayLog('renew_notify', $insure->loan_id, '续期记录' . $res);
            if (!$res) {
                exit;
            }
            $res = $loaninfo->createRenewLoan($insure->create_time, $insure->id);
            Logger::dayLog('renew_notify', $insure->loan_id, '新建借款期记录' . $res);
            if ($res) {
                echo 'SUCCESS';
                exit;
            } else {
                exit;
            }

        } elseif ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 11) {//失败处理
            $data = [
                'status' => 4,
                'repay_time'=>date('Y-m-d H:i:s'),
            ];
            $res = $insure->updateData($data);
            if ($res) {
                echo 'SUCCESS';
            }
            exit;
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

}

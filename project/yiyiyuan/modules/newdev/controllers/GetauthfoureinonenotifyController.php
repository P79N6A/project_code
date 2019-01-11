<?php

namespace app\modules\newdev\controllers;

use app\models\news\Payaccount;
use app\commonapi\Logger;
use app\common\ApiClientCrypt;
use app\models\news\PayAccountExtend;
use app\models\news\User_bank;
use app\models\news\PayAccountError;

class GetauthfoureinonenotifyController extends NewdevController {
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /*
     * 四合一授权结果
     */
    public function actionIndex() {
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'Authfoureinonenotify', 'debt');
        $postData['data'] = $payExtendJson = preg_replace('/\s/', '', $postData['data']);
        $result = json_decode($postData['data'], true);
        if (!is_array($result) || !isset($result['res_data'])) {
            exit('error1');
        }
        $result = json_decode($result['res_data'], true);
        Logger::errorLog(print_r($result, true), 'Authfoureinonenotify', 'debt');
        if (!is_array($result) || !isset($result['retCode']) || !isset($result['accountId']) || !isset($result['paymentAuth']) || !isset($result['repayAuth'])) {
            exit('error3');
        }
        $res = $result['retCode'];
        $step = 6;
        $accountInfo = Payaccount::find()->where(['accountId' => $result['accountId'], 'type' => 2, 'step' => $step])->orderBy('activate_time desc')->one();

        //判断授权截止时间是否大于1年
        $paymentDeadline = date('Y-m-d H:i:s', strtotime($result['paymentDeadline']));
        $repayDeadline = date('Y-m-d H:i:s', strtotime($result['repayDeadline']));
        $deadline = date("Y-m-d H:i:s", time() + 365 * 24 * 60 * 60);
        if ($paymentDeadline < $deadline || $repayDeadline < $deadline || $result['paymentMaxAmt'] < '5000' || $result['repayMaxAmt'] < '5000') {
            $pAccountErrorParams = array(
                'user_id' => isset($accountInfo->user_id) ? $accountInfo->user_id : "",
                'type' => 3,
                'res_code' => '2',
                'res_json' => addslashes($postData['data']),
                'res_msg' => "授权时间或金额错误",
                'status' => 0,
            );
            $repayinfo = (New PayAccountError())->save_error($pAccountErrorParams);
            if (!$repayinfo) {
                Logger::errorLog(print_r($pAccountErrorParams, true), 'Authfoureinonenotify', 'debt');
            }
            exit('Deadline error');
        }
        //插入payaccounterror数据
        $pAccountErrorParams = array(
            'user_id' => isset($accountInfo->user_id) ? $accountInfo->user_id : "",
            'type' => 3,
            'res_code' => $res,
            'res_json' => addslashes($postData['data']),
            'res_msg' => $result['retMsg'],
            'status' => 0,
        );
        $repayinfo = (New PayAccountError())->save_error($pAccountErrorParams);
        if (!$repayinfo) {
            Logger::errorLog(print_r($pAccountErrorParams, true), 'Authfoureinonenotify', 'debt');
        }
        if (!$accountInfo) {
            exit('Payaccount error');
        }

        //插入payaccountextedn数据
        $pAccountExtendParams = array(
            'pay_account_id' => isset($accountInfo->id) ? $accountInfo->id : "",
            'user_id' => isset($accountInfo->user_id) ? $accountInfo->user_id : "",
            'step' => 6,
            'paymax' => $result['paymentMaxAmt'],
            'paydeadline' => $paymentDeadline,
            'repaymax' => $result['repayMaxAmt'],
            'repaydeadline' => $repayDeadline,
            'res_json' => $payExtendJson
        );
        $payExtendInfo = PayAccountExtend::find()->where(['user_id' => $accountInfo->user_id, 'step' => 6])->one();
        if (!empty($payExtendInfo) && $result['retCode'] == '00000000') {
            $payExtendRes = $payExtendInfo->updateRecord($pAccountExtendParams);
        } elseif (empty($payExtendInfo) && $result['retCode'] == '00000000') {
            $payExtendRes = (new PayAccountExtend())->addRecord($pAccountExtendParams);
        }
        if (!$payExtendRes) {
            Logger::errorLog(print_r($pAccountExtendParams, true), 'Authfoureinonenotify', 'debt');
        }

        if ($accountInfo->activate_result == 1) {
            return "SUCCESS";
        }
        //响应超时应答码
        $timeOutCodes = [
            'CT9903',
            'CT990300',
            'CE999999',
        ];
        //状态吗判断，缴费授权结果，还款授权结果判断
        if ($res == '00000000' && $result['paymentAuth'] == 1 && $result['repayAuth'] == 1) {
            $condition['activate_result'] = 1;
            $condition['activate_time'] = date('Y-m-d H:i:s');
            $open_res = $accountInfo->update_list($condition);
            if (!$open_res) {
                $pass_res = false;
            } else {
                $pass_res = true;
            }
        } elseif (!in_array($res, $timeOutCodes)) {
            $pass_res = $accountInfo->update_list(['activate_result' => 2]);
        }

        if (!$pass_res) {
            exit('error');
        }
        return "SUCCESS";
    }

}

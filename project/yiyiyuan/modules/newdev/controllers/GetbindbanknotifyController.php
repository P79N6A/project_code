<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Logger;
use app\models\news\Card_bin;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;

class GetbindbanknotifyController extends NewdevController {

    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /*
     * 接收存管授权结果
     */

    public function actionIndex() {
        $postData = $this->post();
        Logger::dayLog('debt/bindbank', $postData);
        if (!is_array($postData) || !isset($postData['data'])) {
            exit('error1');
        }
        $result = json_decode($postData['data'], true);
        Logger::dayLog('debt/bindbank', $result);
        if (!$result || $result['res_code'] != 0) {
            exit('error2');
        }
        $res_data = $result['res_data'];
        $res_arr = json_decode($res_data, true);
        if (!is_array($res_arr) || !isset($res_arr['retCode']) || !isset($res_arr['accountId']) || !isset($res_arr['order_id'])) {
            exit('error3');
        }
        
        $accountId = $res_arr['accountId'];
        $orderId = $res_arr['order_id'];
        $res = $res_arr['retCode'];
        $cardNo = $res_arr['cardNo'];
        if ($res != '00000000') {
            echo 'SUCCESS';
        }
        $isAuth = Payaccount::find()->where(['accountId' => $accountId, 'type' => 2, 'step' => 1])->one();
        if (!$isAuth) {
            exit('error');
        }
        $userModel = new User();
        $user = $userModel->getUserinfoByUserId($isAuth->user_id);
        $bankModel = new User_bank();
        $bankRes = $bankModel->bindBank($user, $cardNo);
        if (!$bankRes) {
            exit('bindBank error');
        }
        $cardInfo = $bankModel->getBankByConditions(['card' => $cardNo, 'user_id' => $isAuth->user_id, 'status' => 1]);
        $cardId = $cardInfo[0]->id;
        $pass_res = $isAuth->update_list(['card' => (string)$cardId]);
        if (!$pass_res) {
            exit('error');
        }
        return "SUCCESS";
    }

}

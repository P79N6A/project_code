<?php

namespace app\modules\dev\controllers;

use app\commonapi\ApiSms;
use app\commonapi\Logger;
use app\common\ApiClientCrypt;
use app\models\news\GoodsLoan;
use app\models\news\User_remit_list;
use app\models\news\YiLoanNotify;
use Yii;
use yii\web\Controller;

class Remit_notifyController extends Controller {

    public $enableCsrfValidation = false;

    //出款异步通知地址
    public function actionNotify() {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        if (empty($data)) {
            echo '非法请求';
            exit;
        }
        $parr = $openApi->parseReturnData($data);
        if ($parr['res_code'] == '11' && $parr['res_data'] == '数据为空') {
            $openApi = new \app\common\Api7ClientCrypt();
            $parr = $openApi->parseReturnData($data);
        }
        Logger::errorLog(print_r($parr, true), 'remit_notify');
        if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 6) {
            //出款成功
            $status = 'SUCCESS';
        } else if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 11) {
            //出款失败
            $status = 'FAIL';
        } else {
            exit;
        }

        //订单号
        $req_id = $parr['res_data']['req_id'];
        //出款请求号
        $client_id = isset($parr['res_data']['client_id']) ? $parr['res_data']['client_id'] : '';
        $user_remit_list = User_remit_list::find()->joinWith('loanExtend', true, 'LEFT JOIN')->where([User_remit_list::tableName() . '.order_id' => $req_id])->one();
        if (empty($user_remit_list)) {
            Logger::dayLog("remit_notify", $req_id, "出款订单号不存在");
            echo 'SUCCESS';
            exit;
        }
        if ($status == 'FAIL') {
            //1 将出款表中的状态改为失败，将借款附属表中的状态改为失败
            $rsp_code = '9999';
            $rsp_msg = isset($parr['res_data']['rsp_status_text']) ? $parr['res_data']['rsp_status_text'] : '失败';
            if (in_array($user_remit_list->fund, [5, 6])) {
                $result_remit = $user_remit_list->changeFund($rsp_code, $rsp_msg, $client_id);
            } else if (in_array($user_remit_list->fund, [1, 11])) {
                $result_remit = $user_remit_list->savePayFail($rsp_code, $rsp_msg, $client_id);
            } else {
                exit;
            }
        } else {
            $result_remit = $user_remit_list->savePaySuccess($client_id);
        }

        if (!$result_remit) {
            Logger::dayLog("remit_notify", $req_id, "出款记录表修改失败");
            return false;
        }

        $loan_notify = new YiLoanNotify();
        $loan_notify->saveNotifyRecord($user_remit_list);

        //出款成功短信通知
        (new ApiSms())->sendLoanSuccessSms($user_remit_list->loan_id);

        echo 'SUCCESS';
        exit;
    }

}

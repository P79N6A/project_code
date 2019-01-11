<?php

namespace app\modules\dev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\GoodsLoan;
use app\models\news\User_loan;
use app\models\news\User_remit_list;
use app\models\news\YiLoanNotify;
use Yii;
use yii\web\Controller;

class Jiufu_notifyController extends Controller {

    public $enableCsrfValidation = false;

    //玖富出款异步通知地址
    public function actionJiufu() {
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
        Logger::dayLog('remit_notify/jf', print_r($parr, true));
//         $parr = [
//             'res_code' => '0',
//             'res_data'=>[
//                 'remit_status' => 6,
//                 'req_id' => 'Y20171021054202ID2415714',
//                 'client_id' => 'L1NJ20170515035444292911',
//             ],
//         ];
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
        $user_remit_list = User_remit_list::find()->where(['order_id' => $req_id, 'fund' => 2, 'payment_channel' => 0, 'remit_status' => ['DOREMIT','LOCK']])->one();
        if (empty($user_remit_list)) {
            Logger::dayLog("'remit_notify/jf'", $req_id, "出款remit不存在");
            echo 'SUCCESS';
            exit;
        }
        //出款请求号
        $client_id = isset($parr['res_data']['client_id']) ? $parr['res_data']['client_id'] : '';
        if ($status == 'FAIL') {
            //1 将出款表中的状态改为失败，将借款附属表中的状态改为失败
            $rsp_code = '9999';
            $rsp_msg = isset($parr['res_data']['rsp_status_text']) ? $parr['res_data']['rsp_status_text'] : '失败';
//            $result_remit = $user_remit_list->savePayFail($rsp_code, $rsp_msg, $client_id);
            $result_remit = $user_remit_list->changeFund($rsp_code, $rsp_msg, $client_id);
        } elseif ($status == 'SUCCESS') {
            $loan = User_loan::findOne($user_remit_list->loan_id);
            if ($loan->status != 9) {
                Logger::dayLog("'remit_notify/jf'", $user_sub->loan_id, $loan->status, "借款状态不正确");
                exit;
            }
            $loan_res = $loan->saveEndtime($loan->days);
            if (!$loan_res) {
                Logger::dayLog("'remit_notify/jf'", $user_sub->loan_id, "借款更新起息日失败");
            }
            $result_remit = $user_remit_list->savePaySuccess($client_id);
        } else {
            exit;
        }

        if (!$result_remit) {
            Logger::dayLog("'remit_notify/jf'", $req_id, "出款记录表修改失败");
            return false;
        }

        $loan_notify = new YiLoanNotify();
        $loan_notify->saveNotifyRecord($user_remit_list);

        //出款成功加入分期中间表
        $goods_loan = new GoodsLoan();
        $goods_loan->addSuccessGoodsLoan($user_remit_list);

        echo 'SUCCESS';
        exit;
    }

}

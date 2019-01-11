<?php

namespace app\modules\sevenday\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\day\User_remit_list_guide;
use app\models\news\GoodsLoan;
use app\models\news\User_remit_list;
use app\models\news\YiLoanNotify;
use Yii;
use yii\web\Controller;

class NotifyController extends Controller {

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
//        $data = 'UnV0PSmLQuTH1LRO+hqBu6UtbTmmgIJqUdKA/k7HvmQc9oBPm2OEgw90yMVDGLNJQgt9KfJuRTIpemY2SfqR3cK323VvQkUQM2BfCBB+ePFVPAXveVHGXz2dN/wlEIpHdn2ZUOjQDoONUy66qH1W81L3YBtZ9F8crdb1vi/kEZFoRGMb3icfvOLT5QKcjZB73v+wuTVYYj7M/5XD44+ImU/bhv8ojWrsjJRjY1CXMY3iIGw00ROTSNInDwbvOkxyAaJOnCMuJ/YxuePaciqrZqjkHr4+FKVWxbCe52pQr3IZs6kehUOCYUn/IuvHvURzBFUV5fh1gur/i8V/xka8vJASxggcPRgIUqx0I2ia4BOPFvOHtSj6Nfj6KiB6/f2DeAIZfDk8qdp45pRwf620gI/EwsvMCw0wYAcH2hFZEUQ=';
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('day_remit_notify', $parr);
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
        $user_remit_list = User_remit_list_guide::find()->where(['order_id' => $req_id])->one();
        if (empty($user_remit_list)) {
            Logger::dayLog("remit_notify", $req_id, "出款订单号不存在");
            echo 'SUCCESS';
            exit;
        }
        if ($status == 'FAIL') {
            //1 将出款表中的状态改为失败，将借款附属表中的状态改为失败
            $rsp_code = '9999';
            $rsp_msg = isset($parr['res_data']['rsp_status_text']) ? $parr['res_data']['rsp_status_text'] : '失败';
            if (in_array($user_remit_list->fund, [11])) {
                $result_remit = $user_remit_list->savePayFail($rsp_code, $rsp_msg, $client_id);
            } else {
                exit;
            }
        } else {
            $result_remit = $user_remit_list->savePaySuccess($client_id);
            $loan = $user_remit_list->loan;
            $end_date = date('Y-m-d', strtotime($loan->end_date) - 86400);
            if ($loan->is_calculation == 1) {
                $allmoney = $loan->amount + $loan->interest_fee;
                $outmoney = $loan->amount - $loan->withdraw_fee;
            } else {
                $allmoney = $loan->amount + $loan->interest_fee + $loan->withdraw_time;
                $outmoney = $loan->amount;
            }
            $sms = (new \app\models\day\Sms_guide())->sendSevendayOutmoney($user_remit_list->user->mobile, round($loan->amount, 2), $end_date, round($outmoney, 2), round($allmoney, 2), 3);
            $renew = (new \app\models\day\Renew_amount_guide())->addFirstRecord($user_remit_list->loan, 0, 1, 1);
        }

        if (!$result_remit) {
            Logger::dayLog("remit_notify", $req_id, "出款记录表修改失败");
            return false;
        }

        echo 'SUCCESS';
        exit;
    }

}

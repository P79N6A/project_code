<?php

namespace app\modules\newdev\controllers;

use app\commonapi\ApiSign;
use app\models\news\Cg_remit;
use app\models\news\CommonNotify;
use app\models\news\GoodsLoan;
use app\models\news\User_remit_list;
use app\commonapi\Logger;
use app\models\news\YiLoanNotify;
use app\models\remit\FundCunguan;
use Yii;

class GetmoneynotifyController extends NewdevController {

    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /*
     * 接收存管提现结果
     */

    public function actionIndex() {
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'GetmoneyNotify', 'debt');
        if (!is_array($postData) || !isset($postData['data'])) {
            exit('error1');
        }
        $result = json_decode($postData['data'], true);
        Logger::errorLog(print_r($result, true), 'GetmoneyNotify', 'debt');
        if (!$result) {
            exit('error2');
        }
        $res_data = $result['res_data'];
        $res_arr = json_decode($res_data, true);
        if (!is_array($res_arr) || !isset($res_arr['accountId']) || !isset($res_arr['acqRes'])) {
            exit('error3');
        }
        $parr = [
            'order_no' => $res_arr['acqRes'],
            'retCode' => $res_arr['retCode'],
        ];
        if ($parr['retCode'] == '00000000') {//出款成功
            $status = 'SUCCESS';
        } else if ((new FundCunguan())->getNotifyFails($parr['retCode'])) {
            $status = 'FAIL';
        } else {
            exit;
        }

        //订单号
        $req_id = $parr['order_no'];
        $cgModel = new Cg_remit();
        $cgRemit = $cgModel->getByOrderId($req_id);
        if (empty($cgRemit) || $cgRemit->remit_status != 'DOREMIT') {
            Logger::errorLog(print_r([$cgRemit->id => "存管出款子表=》不存在或者状态不为DOREMIT"], true), 'GetmoneyNotifyError', 'debt');
            exit;
        }
        if ($status == 'FAIL') {
            $fail = $cgRemit->outMoneyFail('1', 'outmoneyfail');
            if (!$fail) {
                Logger::errorLog(print_r([$req_id => "存管出款子表=》FAIL状态修改失败"], true), 'GetmoneyNotifyError', 'debt');
                exit;
            }
        } elseif ($status == 'SUCCESS') {
            $success = $cgRemit->outMoneySuccess();
            if (!$success) {
                Logger::errorLog(print_r([$req_id => "存管出款子表=》SUCCESS状态修改失败"], true), 'GetmoneyNotifyError', 'debt');
                exit;
            }
        } else {
            exit;
        }

        //@todo remitlist 对象核查
        $loan_notify = new YiLoanNotify();
        $loan_notify->saveNotifyRecord($cgRemit->remitlist);

        //出款成功加入分期中间表
        $goods_loan = new GoodsLoan();
        $goods_loan->addSuccessGoodsLoan($cgRemit->remitlist);
        echo 'SUCCESS';
        exit;
    }

    public function actionNewnotify(){
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'GetmoneyNotifyNew', 'debt');
        if (!is_array($postData) || !isset($postData['data']) || !isset($postData['_sign'])) {
            exit('error1');
        }
        $apiSignModel = new ApiSign();
        $verify = $apiSignModel->verifyData($postData['data'], $postData['_sign']);
        if(!$verify){
            exit('sign error');
        }
        $result = json_decode($postData['data'], true);
        Logger::errorLog(print_r($result, true), 'GetmoneyNotifyNew', 'debt');
        if (!$result || !$result['loan_id'] || !$result['res_status']) {
            exit('error2');
        }

        if ($result['res_status'] == 6) {//出款成功
            $status = 'SUCCESS';
        } else if ($result['res_status'] == 11) {
            $status = 'FAIL';
        } else {
            exit;
        }

        $cgModel = new Cg_remit();
        $cgRemit = $cgModel->getByLoanId($result['loan_id']);
        if (empty($cgRemit) || $cgRemit->remit_status != 'WAITREMIT') {
            Logger::errorLog(print_r([$result['loan_id'] => "存管出款子表=》不存在或者状态不为WAITREMIT"], true), 'GetmoneyNotifyErrorNew', 'debt');
            exit;
        }
        if ($status == 'FAIL') {
            $fail = $cgRemit->outMoneyFail('1', 'outmoneyfail');
            if (!$fail) {
                Logger::errorLog(print_r([$result['loan_id'] => "存管出款子表=》FAIL状态修改失败"], true), 'GetmoneyNotifyErrorNew', 'debt');
                exit;
            }
        } elseif ($status == 'SUCCESS') {
            $success = $cgRemit->outMoneySuccess();
            if (!$success) {
                Logger::errorLog(print_r([$result['loan_id'] => "存管出款子表=》SUCCESS状态修改失败"], true), 'GetmoneyNotifyErrorNew', 'debt');
                exit;
            }
        } else {
            exit;
        }

        //@todo remitlist 对象核查
        $loan_notify = new YiLoanNotify();
        $loan_notify->saveNotifyRecord($cgRemit->remitlist);

        //出款成功加入分期中间表
        $goods_loan = new GoodsLoan();
        $goods_loan->addSuccessGoodsLoan($cgRemit->remitlist);
        echo 'SUCCESS';
        exit;
    }

}

<?php

namespace app\modules\renew\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\Renew_amount;
use app\models\news\Renew_record;
use app\models\news\User_loan;
use app\models\news\User_bank;
use app\models\news\Renewal_payment_record;
use Yii;

class NotifyrenewcunguanController extends RenewbaseController {

    public $enableCsrfValidation = false;
    private $quickYeepay;
    private $notify_url;
    private $error_url;

//    public function beforeAction() {
    public function behaviors() {
        return [];
    }

    //在线还款服务器异步通知地址
    public function actionIndex() {
        $loan_id = $this->get('loan_id', 0);
        $bank_id = $this->get('bank_id', 0);
        $come_from = $this->get('come_from', 0); //0:从renew模块发起的 1：从new模块下发起的
        $source = Yii::$app->request->get('source', '');
        $openApi = new ApiClientCrypt;
        if (isset($_GET['data'])) {
            $data = $this->get('data');
        } else {
            $data = $this->post('data');
        }
        Logger::dayLog('notify/renew', $loan_id, $bank_id, $come_from, 'nn', $this->post(), $data);
        $isPost = Yii::$app->request->isPost;
        $parr = [];
        if (!empty($data)) {
            $parr = json_decode($data, true);
            $isPost = Yii::$app->request->isPost;
            Logger::dayLog('notify/renew', $loan_id, 'mmm', $parr);
        }
        Logger::dayLog('notify/renew', 'tt', $loan_id, $parr);
        $loan_id = isset($parr['loan_id']) ? $parr['loan_id'] : $loan_id;
        if ($loan_id == 0) {
            Logger::dayLog('notify/renew', 'get-notifyrenewcunguan-empty-loan_id', $loan_id);
            exit;
        }
        $oRenewModel = new Renew_record();
        $oRenew = $oRenewModel->getRecordBynewId($loan_id);
        if (empty($oRenew)) {
            Logger::dayLog('notify/renew', 'get-notifyrenewcunguan-empty-renewrecord', $loan_id);
            exit;
        }
        $loaninfo = (new User_loan())->getLoanById($oRenew->loan_id);
        $oRenewAmount = (new Renew_amount())->getRenew($oRenew->loan_id);
        if (!$isPost) {
            if (!in_array($oRenew->authorize, [1, 6, 11])) {
                $oRes = $oRenew->updateAuthorize(1);
            }
            //判断受托收费or免费
            if ($oRenewAmount->type == 3) { //免费受托
                if ($come_from == 1) {
                    return $this->redirect('/new/renewal/renewalsuccess?source=' . $source);
                }
                return $this->redirect('/renew/renewal/renewal?source=' . $source);
            }
            if ($oRenewAmount->type == 4) { //收费受托
                //计算还款金额               
                $renew_fee = $oRenewAmount->renew_fee;
                $money_order = round($renew_fee, 2);
                $user = $loaninfo->user;
                $user_id = $user['user_id'];
                $orderid = date('YmdHis') . rand(1000, 9999);
                $money = $money_order * 100;
                if (empty($bank_id)) {
                    $bankModel = new User_bank();
                    $banklist = $bankModel->limitCardsSort($user_id, 1);
                    //是否有可用银行卡
                    $mark = !empty($banklist) && $banklist[0]['sign'] == 2 ? 1 : 0;
                    $bank_id = $mark == 1 ? $banklist[0]['id'] : '';
                }
                $bank = User_bank::findOne($bank_id);
                $card_id = $bank_id;
                $times = date('Y-m-d H:i:s');
                $loan_renewal = new Renewal_payment_record();
                $renewal_record = $loan_renewal->addBatch($loaninfo, $orderid, $card_id, $money_order, 2, 1);
                if (!$renewal_record) {
                    if ($come_from == 1) {
                        return $this->redirect('/new/renewal/renewalsuccess?source=' . $source);
                    }
                    return $this->redirect('/renew/renewal/renewal?source=' . $source);
                }
                $card_type = ($bank->type == 0) ? 1 : 2;
                $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
                $postData = array(
                    'orderid' => $orderid, // 请求唯一号
                    'identityid' => (string) $user_id, // 用户标识
                    'bankname' => $bank->bank_name, //银行名称
                    'bankcode' => $bank->bank_abbr, //银行编码
                    'card_type' => $card_type, // 卡类型
                    'cardno' => $bank->card, // 银行卡号
                    'idcard' => $user->identity, // 身份证号
                    'username' => $user->realname, // 姓名
                    'phone' => $phone, // 预留手机号
                    'productcatalog' => '7', // 商品类别码
                    'productname' => '购买电子产品', // 商品名称
                    'productdesc' => '购买电子产品', // 商品描述
                    'amount' => $money, // 交易金额
                    'orderexpdate' => 60, // 交易金额
                    'business_code' => 'YYYZQKJ',
                    'userip' => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '', // IP
                    'callbackurl' => Yii::$app->params['yibao_renewal'], //异步地址
                );
                $openApi = new ApiClientCrypt;
                Logger::errorLog(print_r($postData, true), 'renewal');
                $res = $openApi->sent('payroute/pay', $postData, 2);
                $result = $openApi->parseResponse($res);
                Logger::errorLog(print_r($result, true), 'renewal');
                Logger::dayLog('payroute/pay', '请求支付结果', $result);
                if ($result['res_code'] == 0 && !empty($result['res_data']['url'])) {
                    return $this->redirect($result['res_data']['url']);
                } else {
                    Logger::dayLog('notify/renew/pay', '请求支付失败', $result);
                    if ($come_from == 1) {
                        return $this->redirect('/new/renewal/renewalsuccess?source=' . $source);
                    }
                    return $this->redirect('/renew/renewal/renewal?source=' . $source);
                }
                //                
            }
        }
        if (empty($parr)) {
            exit;
        }
        if (!in_array($parr['res_status'], [6, 11])) {
            exit;
        }
        $result = $oRenew->updateAuthorize($parr['res_status']);
        if ($parr['res_status'] == 6 && $oRenewAmount->type == 3) {
            $now = date('Y-m-d H:i:s');
            $res = $loaninfo->createRenewCunguanLoan($now, 0, $oRenew);
            Logger::dayLog('notify/renew', $oRenew->loan_id, '新建借款期记录' . $res);
        }
        return $result ? 'SUCCESS' : '';
    }

}

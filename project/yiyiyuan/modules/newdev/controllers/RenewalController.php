<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\commonapi\Wechatpay;
use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\news\Loan_renew_user;
use app\models\news\Renewal_payment_record;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_rate;
use app\models\service\InsuranceService;
use app\models\news\Loan_repay;
use app\models\news\Payaccount;
use app\models\news\Renew_amount;
use app\models\news\Renew_record;
use Yii;

class RenewalController extends NewdevController {

    public $layout = 'data';
    public $enableCsrfValidation = false;
    private $paytype = 'wrenewal';

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $this->getView()->title = '续期';
        $loan_id = Yii::$app->request->get('loan_id');
        $loan = User_loan::findOne($loan_id);
        if (empty($loan)) {
            return $this->redirect('/new/loan');
        }
        $renewModel = new Renew_amount();
        $renew_amount = $renewModel->getRenew($loan->loan_id);
        if (empty($renew_amount)) {
            return $this->redirect('/new/loan');
        }
        //重新计算续期金额
//        $rate_setting = (new User_rate())->getrateone($loan->user_id,$loan->days);
//        $with_fee = $rate_setting['rate'];
//        $renew_fee = $loan->amount*$renew_amount->renew+$loan->amount*$with_fee;
        $renew_fee = $renew_amount->renew_fee;
        $bankModel = new User_bank();
        $banklist = $bankModel->limitCardsSort($loan->user_id, 1);
        $bank_count = User_bank::find()->where(['user_id' => $loan['user_id'], 'status' => 1])->count();
        //是否有可用银行卡
        $mark = !empty($banklist) && $banklist[0]['sign'] == 2 ? 1 : 0;
        $jsinfo = $this->getWxParam();

        //还款时间
        $end_date = (new User_loan())->getHuankuanTime($loan->status, $loan->end_date);
        $is_show = 0;
        return $this->render('cards', [
                    'jsinfo' => $jsinfo,
                    'loan' => $loan,
                    'end_date' => $end_date,
                    'banklist' => $banklist,
                    'money' => $renew_fee,
                    'mark' => $mark,
                    'is_show' => $is_show,
                    'bank_count' => $bank_count,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    /**
     * 获取csrf
     * @return string
     */
    public function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 
     * @return type res_code 1:借款不存在  2：参数错误 3：续期记录生成失败
     */
    public function actionSubpay() {
        $post_data = Yii::$app->request->post();
        $loan_id = isset($post_data['loan_id']) ? intval($post_data['loan_id']) : 0;
        Logger::errorLog(print_r($loan_id, true), 'renewal');
        if (!$loan_id || !is_numeric($loan_id)) {
            $array = ['res_code' => '2', 'res_msg' => '参数错误', 'url' => ''];
            return json_encode($array);
        }
        $loaninfo = User_loan::findOne($loan_id);
        if (empty($loaninfo) || $loaninfo->status == 8) {
            $array = ['res_code' => '1', 'res_msg' => '可续期借款不存在', 'url' => ''];
            return json_encode($array);
        }
        $renewUserModel = new Renew_amount();
        $renew = $renewUserModel->getRenew($loaninfo->loan_id);
        if (empty($renew)) {
            $array = ['res_code' => '5', 'res_msg' => '您暂时不能申请续期', 'url' => ''];
            return json_encode($array);
        }
        $renew_pay = Renewal_payment_record::find()->where(['loan_id' => $loaninfo->loan_id])->orderBy('id desc')->one();
        if (!empty($renew_pay)) {
            if ($renew_pay->status == '-1') {
                $array = ['res_code' => '5', 'res_msg' => '您有支付中的续期还款', 'url' => ''];
                return json_encode($array);
            }
            $time = strtotime(date('Y-m-d H:i:s', time())) - strtotime(date($renew_pay->create_time, time()));
            if ($time < 120) {
                $array = ['res_code' => '5', 'res_msg' => '操作频繁,请' . (120 - $time) . '秒后重试', 'url' => ''];
                return json_encode($array);
            }
        }
        //重新计算续期金额
//        $rate_setting = (new User_rate())->getrateone($loaninfo->user_id,$loaninfo->days);
//        $with_fee = $rate_setting['rate'];
//        $renew_fee = $loaninfo->amount*$renew->renew+$loaninfo->amount*$with_fee;
        $renew_fee = $renew->renew_fee;
        $money_order = round($renew_fee, 2);
        $user = $loaninfo->user;
        $user_id = $user['user_id'];
        $orderid = date('YmdHis') . rand(1000, 9999);
        $money = $money_order * 100;
//            $money = 1;
        $card_id = $post_data['bank_id'];
        $bank = User_bank::findOne($card_id);
        $times = date('Y-m-d H:i:s');
        $loan_renewal = new Renewal_payment_record();
        $renewal_record = $loan_renewal->addBatch($loaninfo, $orderid, $card_id, $money_order, 2, 1);
        if (!$renewal_record) {
            $array = ['res_code' => '3', 'res_msg' => '续期记录生成失败', 'url' => ''];
            return json_encode($array);
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
            'userip' => $_SERVER["REMOTE_ADDR"], // IP
            'callbackurl' => Yii::$app->params['yibao_renewal'], //异步地址
        );
        $openApi = new ApiClientCrypt;
        Logger::errorLog(print_r($postData, true), 'renewal');
        $res = $openApi->sent('payroute/pay', $postData, 2);
        $result = $openApi->parseResponse($res);
        Logger::errorLog(print_r($result, true), 'renewal');
        if ($result['res_code'] == 0 && !empty($result['res_data']['url'])) {
            $array = ['res_code' => '0', 'res_msg' => '请求成功', 'url' => $result['res_data']['url']];
            return json_encode($array);
        } else {
            $array = ['res_code' => '4', 'res_msg' => '请求支付失败', 'url' => ''];
            return json_encode($array);
        }
    }

    /**
     * 续期收费受托
     * @return type
     */
    public function actionSubpaynew() {
        $post_data = Yii::$app->request->post();
        $loan_id = isset($post_data['loan_id']) ? intval($post_data['loan_id']) : 0;
        $bank_id = $post_data['bank_id'];
        Logger::errorLog(print_r($loan_id, true), 'renewal');
        if (!$loan_id || !is_numeric($loan_id)) {
            $array = ['res_code' => '2', 'res_msg' => '参数错误', 'url' => ''];
            return json_encode($array);
        }
        $loaninfo = User_loan::findOne($loan_id);
        if (empty($loaninfo) || $loaninfo->status == 8) {
            $array = ['res_code' => '1', 'res_msg' => '可续期借款不存在', 'url' => ''];
            return json_encode($array);
        }

        $renewUserModel = new Renew_amount();
        $renew = $renewUserModel->getRenew($loaninfo->loan_id);
        if (empty($renew)) {
            $array = ['res_code' => '5', 'res_msg' => '您暂时不能申请续期', 'url' => ''];
            return json_encode($array);
        }
        $renew_pay = Renewal_payment_record::find()->where(['loan_id' => $loaninfo->loan_id])->orderBy('id desc')->one();

        if (!empty($renew_pay)) {
            if ($renew_pay->status == '-1') {
                $array = ['res_code' => '5', 'res_msg' => '您有支付中的续期还款', 'url' => ''];
                return json_encode($array);
            }
            $time = strtotime(date('Y-m-d H:i:s', time())) - strtotime(date($renew_pay->create_time, time()));
            if ($time < 120) {
                $array = ['res_code' => '5', 'res_msg' => '操作频繁,请' . (120 - $time) . '秒后重试', 'url' => ''];
                return json_encode($array);
            }
        }
        $repay = Loan_repay::find()->where(['loan_id' => $loaninfo->loan_id, 'status' => '-1'])->orderBy('id desc')->one();
        if (!empty($repay)) {
            $array = ['res_code' => '6', 'res_msg' => '续期失败,请稍后尝试', 'url' => ''];
            return json_encode($array);
        }
        if (in_array($renew->type, [1, 2])) {
            $bankpay_result = $this->bankPay($renew, $loaninfo, $bank_id);
            return json_encode($bankpay_result);
        }
        //判断用户有没有开户、设置密码
        $isCungan = (new Payaccount())->isCunguan($loaninfo->user_id,1);
        unset($isCungan['isCard']);
        if (in_array(0, $isCungan)) {
            $array = ['res_code' => '7', 'res_msg' => '', 'url' => ''];
            return json_encode($array);
        }

        $result = $this->runCunguanRenew($loaninfo, $bank_id, 1);
        Logger::dayLog('notify/renew', 'runCunguanRenew', $result);
        if ($result['rsp_code'] != '0000' || !isset($result['url'])) {
            if (in_array($result['rsp_code'], ['10001', '10002', '10003'])) { //标的登记以及受托成功与否都进行支付
                $bankpay_result = $this->bankPay($renew, $loaninfo, $bank_id);
                return json_encode($bankpay_result);
            }
            return json_encode(['res_code' => $result['rsp_code'], 'res_msg' => '续期失败,请稍后尝试']);
            exit;
        }
        if (isset($result['url'])) {
            $array['redirect_url'] = $result['url'];
            return json_encode(['res_code' => '0', 'res_msg' => '', 'url' => $result['url']]);
            exit;
        }
        return json_encode(['res_code' => '5000', 'res_msg' => '续期失败,请稍后尝试',]);
        exit;
    }

    private function bankPay($renew, $loaninfo, $bank_id) {
        //计算还款金额
        $renew_fee = $renew->renew_fee;
        $money_order = round($renew_fee, 2);
        $user = $loaninfo->user;
        $user_id = $user['user_id'];
        $orderid = date('YmdHis') . rand(1000, 9999);
        $money = $money_order * 100;
        $card_id = $bank_id;
        $bank = User_bank::findOne($card_id);
        $times = date('Y-m-d H:i:s');
        $loan_renewal = new Renewal_payment_record();
        $renewal_record = $loan_renewal->addBatch($loaninfo, $orderid, $card_id, $money_order, 2, 1);
        if (!$renewal_record) {
            $array = ['res_code' => '3', 'res_msg' => '续期记录生成失败', 'url' => ''];
            return json_encode($array);
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
            'userip' => $_SERVER["REMOTE_ADDR"], // IP
            'callbackurl' => Yii::$app->params['yibao_renewal'], //异步地址
        );
        $openApi = new ApiClientCrypt;
        Logger::dayLog('payroute/pay', $postData, 'renewal');
        $res = $openApi->sent('payroute/pay', $postData, 2);
        $result = $openApi->parseResponse($res);
        Logger::dayLog('payroute/payresult', $result, 'renewal');
        if ($result['res_code'] == 0 && !empty($result['res_data']['url'])) {
            $array = ['res_code' => '0', 'res_msg' => '请求成功', 'url' => $result['res_data']['url']];
            return $array;
        } else {
            $array = ['res_code' => '4', 'res_msg' => '请求支付失败', 'url' => ''];
            return $array;
        }
    }

    private function runCunguanRenew($loaninfo, $bank_id, $source) {
        $oRenewRecordModel = new Renew_record();
        $oRenewRecord = $oRenewRecordModel->getRecordByLoanId($loaninfo->loan_id);
        if (empty($oRenewRecord)) {
            $result = $loaninfo->createInvalidloan();
            if (!$result) {
                return ['rsp_code' => '10089'];
            }
            $oRenewRecord = $oRenewRecordModel->getRecordByLoanId($loaninfo->loan_id);
        }

        $loan_new = User_loan::findOne($oRenewRecord->loan_id_new);

        if ($oRenewRecord->registration != 6) { //标的登记失败
            $result = $this->renewEntrustloan($oRenewRecord, $loan_new);
            if (!$result) {
                return ['rsp_code' => '10001'];
            }

            $oRenewRecord->refresh();
        }

        //`registration`  '标的登记状态:0:初始;1:已发送;6:成功;11:失败',
        //`authorize` '授权状态:0:初始;1:授权中;6:成功;11:失败',
        if (in_array($oRenewRecord->authorize, [1, 6])) {  //授权中，或者已授权成功
            return ['rsp_code' => '10002'];
        }
        // 0,11 重新授权
        $result = $this->renewEntrustpay($loan_new, $bank_id, $source);
        if (!$result) { //重新授权失败
            return ['rsp_code' => '10003'];
        }
        $oRenewRecord->refresh();
        $url = $result['rsp_msg'];

        return ['rsp_code' => '0000', 'url' => $url];
    }

    private function renewEntrustpay($loan, $bank_id, $source = 2) {
        $oRenewAmountModel = new Renew_amount();
        $come_from = 1;
        $result = $oRenewAmountModel->entrustpay($loan, $bank_id, $source, $come_from);
        if (!$result) {
            return FALSE;
        }
        return $result;
    }

    private function renewEntrustloan($oRecord, $loan) {
        $oRenewAmountModel = new Renew_amount();
        $result = $oRenewAmountModel->entrustloan($loan);
        if (!$result) {
            $oRecord->updateRegistration(11);
            return FALSE;
        }
        $oRecord->updateRegistration(6);
        return $result;
    }

    /**
     * 
     * @return type res_code 1:借款不存在  2：参数错误 3：续期记录生成失败
     */
    public function actionWeixinsubpay() {
        $psstArr = Yii::$app->request->post();
        $orderid = date('YmdHis') . rand(1000, 9999);
        $loan_id = intval($psstArr['loan_id']);
//        $total_fee = intval($psstArr['total_fee'] * 100);
        if ($loan_id <= 0) {
            exit(json_encode(['status' => '1006', 'msg' => '借款信息错误']));
        }

        $loaninfo = User_loan::findOne($loan_id);
        if (empty($loaninfo)) {
            exit(json_encode(['status' => '1007', 'msg' => '借款信息不存在']));
        }

        //获取用户Openid
        $openid = $this->getVal('openid');
        if (empty($openid)) {
            exit(json_encode(['status' => '1010', 'msg' => '获取openid失败']));
        }

        //获取应还款的金额
        $renewUserModel = new Renew_amount();
        $renew = $renewUserModel->getRenew($loaninfo->loan_id);
        if (empty($renew)) {
            exit(json_encode(['status' => '1009', 'msg' => '您暂时不能申请续期']));
        }
        $money_order = round($renew->renew_fee, 2);
        $total_fee = intval($money_order * 100);
        if ($total_fee <= 0) {
            exit(json_encode(['status' => '1008', 'msg' => '续期还款金额不正确']));
        }
        $loan_renewal = new Renewal_payment_record();
        $renewal_record = $loan_renewal->addBatch($loaninfo, $orderid, $card_id = null, $money_order, 4, 1);
        if (!$renewal_record) {
            exit(json_encode(['status' => '1009', 'msg' => '续期还款记录创建失败']));
        } else {
            $params['total_fee'] = $total_fee;
            $params['out_trade_no'] = $orderid;
            $params['mch_create_ip'] = Yii::$app->request->userIP;
            $params['sub_openid'] = SYSTEM_ENV == 'prod' ? $openid : $openid;
            $params['body'] = '购买电子产品';
            $service = new Wechatpay(SYSTEM_ENV == 'prod' ? 'Config_pro' : 'Config_test', $this->paytype);
//            exit(json_encode($params));
//            exit();
            $res = $service->submitOrderInfo($params);
            exit(json_encode($res));
        }
    }

    /*
     * 微信提交时，在还款表中添加一条记录
     */

    private function addRepay($loan_id, $user_id, $orderid, $total_fee) {
        $user = User::findOne($user_id);
        $user_id = $user['user_id'];
        $money = floatval($total_fee);
        $loan_renewal = new Renewal_payment_record();
        $loan = User_loan::findOne($loan_id);
        $ret = $loan_renewal->addBatch($loan, $orderid, NULL, $money, 4, 1);
        return $ret;
    }

    public function actionRenewalsuccess($source = 'weixin') {
        $this->getView()->title = '提交成功';
        $this->layout = 'data';
        $jsinfo = $this->getWxParam();
        return $this->render('renewalsuccess', ['jsinfo' => $jsinfo, 'source' => $source]);
    }

    public function actionRenewalnotifysuccess() {
        $this->layout = 'data';
        $order_id = $_GET['order_id'];
        $loan_repay = Renewal_payment_record::find()->where(['order_id' => $order_id])->one();
        $this->getView()->title = '提交成功';
        $jsinfo = $this->getWxParam();
        if ($loan_repay->source == 1) {
            $source = 'weixin';
        } else {
            $source = 'app';
        }
        return $this->render('renewalsuccess', ['jsinfo' => $jsinfo, 'source' => $source]);
    }

    /**
     * 核保
     */
    public function actionPolicy() {
        $loan_id = $this->post('loan_id');
        $money = $this->post('money');
        $loaninfo = User_loan::findOne($loan_id);
        if (empty($loaninfo)) {
            echo json_encode(['code' => '10002', 'msg' => '系统错误']);
            exit;
        }
        $renewModel = new Renew_amount();
        $is_allow = $renewModel->getRenew($loaninfo->loan_id);
        if (!$is_allow || $loaninfo->status == 8) {
            echo json_encode(['code' => '10002', 'msg' => '您暂时不能进行续期']);
            exit;
        }
        $insure = \app\models\news\Insure::find()->where(['loan_id' => $loan_id, 'type' => 3])->orderBy('id desc')->one();
        if (!empty($insure)) {
            if ($insure->status == '-1') {
                echo json_encode(['code' => '10002', 'msg' => '有续期中的支付，请勿重复续期']);
                exit;
            }
            $time = strtotime(date('Y-m-d H:i:s', time())) - strtotime(date($insure->create_time, time()));
            if ($time < 120) {
                $new_time = 120 - $time;
                echo json_encode(['code' => '10002', 'msg' => '操作续期频繁，请' . $new_time . '秒后重试']);
                exit;
            }
        }
        $repay = Loan_repay::find()->where(['loan_id' => $loan_id, 'status' => '-1'])->orderBy('id desc')->one();
        if (!empty($repay)) {
            echo json_encode(['code' => '10002', 'msg' => '您有支付中的还款']);
            exit;
        }
        $insuranceService = new InsuranceService();
        $result = $insuranceService->policy($loan_id, $money, 3);
        if (!empty($result) && is_array($result) && $result['code'] = '0000' && !empty($result['data'])) {
            echo json_encode(['code' => '0000', 'msg' => '核保成功', 'data' => $result['data']]);
            exit;
        }
        echo json_encode(['code' => '10001', 'msg' => '续期失败']);
        exit;
    }

    /**
     * 保险购买
     */
    public function actionBuy() {
        $insuranceId = $this->post('insuranceId');
        $source = 1;
        $insuranceService = new InsuranceService();
        $result = $insuranceService->buy($insuranceId, $source, 3);
        if (!empty($result) && is_array($result) && $result['code'] = '0000' && !empty($result['url'])) {
            $result['url'] = str_replace('xianhuahua', 'yaoyuefu', $result['url']);
            echo json_encode(['code' => '0000', 'msg' => '', 'url' => $result['url']]);
            exit;
        }
        echo json_encode(['code' => '10001', 'msg' => '续期失败']);
        exit;
    }

}

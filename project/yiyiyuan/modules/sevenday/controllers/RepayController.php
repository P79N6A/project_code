<?php

namespace app\modules\sevenday\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\ImageHandler;
use app\commonapi\Logger;
use app\models\day\Loan_repay_guide;
use app\models\day\Overdue_loan_guide;
use app\models\day\Renew_amount_guide;
use app\models\day\Renewal_payment_record_guide;
use app\models\day\Scan_times;
use app\models\day\User_bank_guide;
use app\models\day\User_loan_guide;
use app\models\news\Coupon_list;
use app\models\news\User;
use app\models\onlyread\Loan_repay;
use Yii;
use yii\web\Response;

class RepayController extends SevendayController {

    /**
     * 还款页
     * @return string
     * @author 王新龙
     * @date 2018/8/3 11:30
     */
    public function actionIndex() {
        $this->getView()->title = '立即还款';
        $repayerror = $this->get('repay', '');
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_loan_guide = (new User_loan_guide())->getHaveinLoan($o_user_guide->user_id);
        if (empty($o_user_loan_guide)) {
            return $this->redirect('/day/loan');
        }
        $page = $o_user_loan_guide->showPage('/day/repay');
        if (!empty($page)) {
            return $this->redirect($page);
        }
        $repayment = (new User_loan_guide())->getRepayment($o_user_loan_guide, 2); //应还金额
        $chase_amount = (new Overdue_loan_guide())->getLateFeeByLoanId($o_user_loan_guide->loan_id);
        $day = 0;
        if ($o_user_loan_guide->end_date < date('Y-m-d H:i:s')) {
            $day = $this->countDays($o_user_loan_guide->end_date, date('Y-m-d H:i:s'));
        }
        $renew = (new Renew_amount_guide())->getRenew($o_user_loan_guide->loan_id);
        //还款&续期弹窗
        $popup = (new Scan_times())->getRepayPopup($o_user_guide->user_id,1);
        if($popup['is_popup'] == 2){
            $popup = (new Scan_times())->getRepayPopup($o_user_guide->user_id,2);
        }
        return $this->render('index', [
                    'user_loan' => $o_user_loan_guide,
                    'user' => $o_user_guide,
                    'repayerror' => $repayerror,
                    'repayment' => $repayment,
                    'chase_amount' => $chase_amount,
                    'day' => $day,
                    'renew' => $renew,
                    'popup' => $popup,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 还款详情页
     * @return string|Response
     * @author 王新龙
     * @date 2018/8/3 16:20
     */
    public function actionShowrepay() {
        $this->getView()->title = '还款';
        $bank_id = $this->get('bank_id', '');
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_loan_guide = (new User_loan_guide())->getHaveinLoan($o_user_guide->user_id);
        if (empty($o_user_guide)) {
            return $this->redirect('/day/loan');
        }
        $o_user_bank_guide = (new User_bank_guide())->listByUserId($o_user_guide->user_id, $type = 0);
        $user_bank = $o_user_bank_guide[0];
        if (!empty($bank_id)) {
            $user_bank = (new User_bank_guide())->getById($bank_id);
            if (empty($user_bank) || $user_bank->user_id != $o_user_guide->user_id) {
                return $this->redirect('/day/repay/showrepay');
            }
        }
        $repayment = (new User_loan_guide())->getRepayment($o_user_loan_guide, 2);
        return $this->render('showrepay', [
                    'user_bank_arr' => $o_user_bank_guide,
                    'user_bank' => $user_bank,
                    'user_loan' => $o_user_loan_guide,
                    'repayment' => $repayment,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 还款详情页
     * @return string|Response
     * @author 王新龙
     * @date 2018/8/3 16:20
     */
    public function actionRenew() {
        $this->getView()->title = '续期';
        $bank_id = $this->get('bank_id', '');
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_loan_guide = (new User_loan_guide())->getHaveinLoan($o_user_guide->user_id);
        if (empty($o_user_loan_guide) || empty($o_user_loan_guide->user_remit_list_guide) || !in_array($o_user_loan_guide->status, [9, 11, 12, 13]) || $o_user_loan_guide->user_remit_list_guide->remit_status != 'SUCCESS') {
            return $this->redirect('/day/loan');
        }
        $o_user_bank_guide = (new User_bank_guide())->listByUserId($o_user_guide->user_id, $type = 0);
        $user_bank = $o_user_bank_guide[0];
        if (!empty($bank_id)) {
            $user_bank = (new User_bank_guide())->getById($bank_id);
            if (empty($user_bank) || $user_bank->user_id != $o_user_guide->user_id) {
                return $this->redirect('/day/repay/showrepay');
            }
        }
        $repayment = (new Renew_amount_guide())->getRenewFeeNew($o_user_loan_guide);
        return $this->render('renew', [
                    'user_bank_arr' => $o_user_bank_guide,
                    'user_bank' => $user_bank,
                    'user_loan' => $o_user_loan_guide,
                    'repayment' => $repayment,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 还款操作
     * @author 王新龙
     * @date 2018/8/3 11:55
     */
    public function actionDorepay() {
        if (!$this->isPost()) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $loan_id = $this->post('loan_id', '');
        $bank_id = $this->post('bank_id', '');
        $type = $this->post('type', 1);
        if (empty($loan_id) || empty($bank_id)) {
            exit(json_encode(['rsp_code' => '0002', 'rsp_msg' => '还款失败，请刷新后再试']));
        }
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0003', 'rsp_msg' => '还款失败，请刷新后再试']));
        }
        $o_user_loan_guide = (new User_loan_guide())->getById($loan_id);
        if (empty($o_user_loan_guide)) {
            exit(json_encode(['rsp_code' => '0004', 'rsp_msg' => '该笔借款不存在']));
        }
        if (!in_array($o_user_loan_guide->status, [9, 12, 13])) {
            if ($o_user_loan_guide->status == 8) {
                exit(json_encode(['rsp_code' => '0005', 'rsp_msg' => '该借款已还清']));
            }
            exit(json_encode(['rsp_code' => '0006', 'rsp_msg' => '没有可还借款']));
        }
        $carried_repay = (new Loan_repay_guide())->getCarriedRepay($loan_id);
        if (!empty($carried_repay)) {
            exit(json_encode(['rsp_code' => '0007', 'rsp_msg' => '有正在进行中的还款，请稍后再试']));
        }
        $o_user_bank_guide = (new User_bank_guide())->getById($bank_id);
        if (empty($o_user_bank_guide)) {
            exit(json_encode(['rsp_code' => '0008', 'rsp_msg' => '银行卡不可用']));
        }
        $repayment = (new User_loan_guide())->getRepayment($o_user_loan_guide); //应还金额
        $platform = $type == 1 ? 1 : 2;
        $condition = array(
            'repay_id' => '',
            'user_id' => $o_user_guide->user_id,
            'loan_id' => $o_user_loan_guide->loan_id,
            'bank_id' => $bank_id,
            'money' => floatval($repayment),
            'platform' => $platform,
            'source' => 1
        );
        $m_loan_repay_guide = (new Loan_repay_guide());
        $loan_repay_result = $m_loan_repay_guide->addRecord($condition);
        if (empty($loan_repay_result)) {
            exit(json_encode(['rsp_code' => '0009', 'rsp_msg' => '还款失败，请稍后再试']));
        }
        $callbackurl = Yii::$app->params['sevenday_repay_url'];
        $order_id = $m_loan_repay_guide->repay_id;
        $result = $this->pay($o_user_bank_guide, $o_user_guide, $order_id, $repayment, $callbackurl, $type);
        if (empty($result)) {
            exit(json_encode(['rsp_code' => '0010', 'rsp_msg' => '还款失败，请稍后再试']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '请求成功', 'url' => $result]));
    }

    /**
     * 还款操作
     * @author 王新龙
     * @date 2018/8/3 11:55
     */
    public function actionDorenew() {
        if (!$this->isPost()) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $loan_id = $this->post('loan_id', '');
        $bank_id = $this->post('bank_id', '');
        $type = $this->post('type', 1);
        if (empty($loan_id) || empty($bank_id)) {
            exit(json_encode(['rsp_code' => '0002', 'rsp_msg' => '还款失败，请刷新后再试']));
        }
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0003', 'rsp_msg' => '还款失败，请刷新后再试']));
        }
        $o_user_loan_guide = (new User_loan_guide())->getById($loan_id);
        if (empty($o_user_loan_guide)) {
            exit(json_encode(['rsp_code' => '0004', 'rsp_msg' => '该笔借款不存在']));
        }
        if (!in_array($o_user_loan_guide->status, [9, 12, 13])) {
            if ($o_user_loan_guide->status == 8) {
                exit(json_encode(['rsp_code' => '0005', 'rsp_msg' => '该借款已还清']));
            }
            exit(json_encode(['rsp_code' => '0006', 'rsp_msg' => '没有可还借款']));
        }
        $carried_repay = (new Loan_repay_guide())->getCarriedRepay($loan_id);
        if (!empty($carried_repay)) {
            exit(json_encode(['rsp_code' => '0007', 'rsp_msg' => '有正在进行中的还款，请稍后再试']));
        }

        $carried_repay = (new Renewal_payment_record_guide())->getCarriedRepay($loan_id);
        if (!empty($carried_repay)) {
            exit(json_encode(['rsp_code' => '0007', 'rsp_msg' => '有正在进行中的展期，请稍后再试']));
        }
        $o_user_bank_guide = (new User_bank_guide())->getById($bank_id);
        if (empty($o_user_bank_guide)) {
            exit(json_encode(['rsp_code' => '0008', 'rsp_msg' => '银行卡不可用']));
        }
        $renew = (new Renew_amount_guide())->getRenew($o_user_loan_guide->loan_id);
        if (empty($renew)) {
            exit(json_encode(['rsp_code' => '0008', 'rsp_msg' => '暂无展期资格']));
        }
        $repayment = (new Renew_amount_guide())->getRenewFeeNew($o_user_loan_guide);
        $platform = $type == 1 ? 1 : 2;
        $oRenewalModel = new Renewal_payment_record_guide();
        $order_id = 'D' . date("YmdHis") . $o_user_loan_guide->loan_id;
        $loan_repay_result = $oRenewalModel->addBatch($o_user_loan_guide, $order_id, $bank_id, floatval($repayment), $platform, 1);
        if (empty($loan_repay_result)) {
            exit(json_encode(['rsp_code' => '0009', 'rsp_msg' => '还款失败，请稍后再试']));
        }
        $callbackurl = Yii::$app->params['sevenday_renew_url'];
        $platform = $platform == 1 ? 3 : 4;
        $result = $this->pay($o_user_bank_guide, $o_user_guide, $order_id, $repayment, $callbackurl, $platform);
        if (empty($result)) {
            exit(json_encode(['rsp_code' => '0010', 'rsp_msg' => '还款失败，请稍后再试']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '请求成功', 'url' => $result]));
    }

    /**
     * 还款请求
     * @param $bank
     * @param $user
     * @param $orderid
     * @param $money
     * @param $callbackurl
     * @param string $loaninfo
     * @param int $type
     * @return bool|string
     * @author 王新龙
     * @date 2018/8/3 16:58
     */
    private function pay($bank, $user, $orderid, $money, $callbackurl, $type = 1) {
        $callbackurl = $callbackurl . '?source=sevenday';
        $card_type = ($bank->type == 0) ? 1 : 2;
        $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
        if ($type == 1 || $type == 2) {
            $business_code = $type == 1 ? "QTLKJ" : "QTLZFB";
            $url = $type == 1 ? 'payroute/pay' : 'payment/pay';
        } else {
            $business_code = $type == 3 ? "QTLZQ" : "QTLZFB";
            $url = $type == 3 ? 'payroute/pay' : 'payment/pay';
        }

        $postData = array(
            'orderid' => $orderid, // 请求唯一号
            'identityid' => (string) $user->user_id, // 用户标识
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
            'amount' => $this->getAccuracyAmount($money), // 交易金额
            'orderexpdate' => 60, // 交易金额
            'business_code' => $business_code,
            'userip' => $_SERVER["REMOTE_ADDR"],
            'coupon_repay_amount' => 0,
            'callbackurl' => $callbackurl,
        );
        Logger::dayLog("sevenday/pepay/pay", $type, $user->user_id, $postData, $url);
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $postData, 2);
        if ($type == 1 || $type == 3) {
            $result = $openApi->parseResponse($result);
            Logger::dayLog("sevenday/pepay/pay", $result, $user->user_id);
            if ($result['res_code'] == '0') {
                if (isset($result['res_data']) && isset($result['res_data']['url'])) {
                    $redirect_url = (string) $result['res_data']['url'];
                    if (empty($redirect_url)) {
                        return false;
                    }
                    return $redirect_url;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        } else {
            Logger::dayLog("sevenday/pepay/pay", $result, $user->user_id);
            $res = json_decode($result, true);
            if ($res['res_code'] != '0000') {
                return FALSE;
            }
            return $res['res_data'];
        }
    }

    private function countDays($startdate, $enddate) {
        $second1 = strtotime($startdate);
        $second2 = strtotime($enddate);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        $time = ($second1 - $second2) / 86400;
        return ceil($time);
    }

    //获取精确金额，单位分
    private function getAccuracyAmount($amount) {
        if (empty($amount) && $amount != 0) {
            return false;
        }
        $amount = floatval($amount);
        $amount = (int) round($amount * 100);
        return $amount;
    }

    public function actionUnderline() {
        $this->getView()->title = '立即还款';
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_loan_guide = (new User_loan_guide())->getHaveinLoan($o_user_guide->user_id);
        if (empty($o_user_loan_guide)) {
            return $this->redirect('/day/loan');
        }
        $repayment = (new User_loan_guide())->getRepayment($o_user_loan_guide, 2); //应还金额
        $chase_amount = (new Overdue_loan_guide())->getLateFeeByLoanId($o_user_loan_guide->loan_id);
        $day = 0;
        if ($o_user_loan_guide->end_date < date('Y-m-d H:i:s')) {
            $day = $this->countDays($o_user_loan_guide->end_date, date('Y-m-d H:i:s'));
        }
        $renew = (new Renew_amount_guide())->getRenew($o_user_loan_guide->loan_id);
        return $this->render('underline', [
                    'user_loan' => $o_user_loan_guide,
                    'user' => $o_user_guide,
                    'repayment' => $repayment,
                    'chase_amount' => $chase_amount,
                    'day' => $day,
                    'renew' => $renew,
                    'encrypt' => ImageHandler::encryptKey($o_user_guide->user_id, 'sevenrepay'),
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 线下还款保存
     * @return Response
     * @author 代威群
     * @date 2018/7/25 9:31
     */
    public function actionRepaysave() {
        $loan_id = $this->post('loan_id', '');
        $supplyUrl = $this->post('supplyUrl', '');
        if (empty($loan_id)) {
            return $this->redirect('/borrow/loan');
        }
        Logger::dayLog('sevenday/repaysave', '线下还款', $loan_id, $supplyUrl);
        //还款凭证、还款订单号不能为空
        if (empty($supplyUrl)) {
            return $this->redirect('/day/loan');
        }
        $user = $this->getUser();
        $user_id = $user->user_id;
        $o_user_loan = User_loan_guide::find()->where(['loan_id' => $loan_id])->one();
        if ($o_user_loan->status == 8 || $o_user_loan->status == 11) {
            return $this->redirect('/day/loan');
        }
        $transaction = Yii::$app->db->beginTransaction();
        $loan_repay = new Loan_repay_guide();
        $condition = [
            'repay_id' => ' ',
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'money' => 0,
        ];
        foreach ($supplyUrl as $name => $up_info) {
            if (!empty($up_info)) {
                $name = 'pic_repay' . $name;
                $condition[$name] = $up_info;
            }
        }
        if (!isset($condition['pic_repay1']) || empty($condition['pic_repay1'])) {
            $transaction->rollBack();
            Logger::dayLog('sevenday/repaysave', '还款凭证缺失', $loan_id, $condition);
            return $this->redirect('/day/loan');
        }
        $condition['status'] = 3;
        $loan_result = $loan_repay->save_repay($condition);
        if (!$loan_result) {
            $transaction->rollBack();
            Logger::dayLog('sevenday/repaysave', '还款记录更新失败', $loan_id, $condition, $loan_result);
            return $this->redirect('/day/loan');
        }
        //修改借款记录的状态为11
        $status = 11;
        $ret = $o_user_loan->changeStatus($status);
        if (!$ret) {
            $transaction->rollBack();
            Logger::dayLog('sevenday/repaysave', '借款记录=》11更新失败', $loan_id);
            return $this->redirect('/day/loan');
        }

        $transaction->commit();
        return $this->redirect('/day/repay/verify');
    }

    public function actionVerify() {
        $this->getView()->title = '还款中';
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect('/day/reg');
        }
        return $this->render('verify', [
                    'user' => $user
        ]);
    }

}

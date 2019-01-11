<?php

namespace app\modules\api\controllers\controllers312;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\BillRepay;
use app\models\news\Coupon_list;
use app\models\news\GoodsBill;
use app\models\news\Insure;
use app\models\news\Loan_repay;
use app\models\news\Payaccount;
use app\models\news\Renew_amount;
use app\models\news\Renew_record;
use app\models\news\Renewal_payment_record;
use app\models\news\RepayCouponUse;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\service\InsuranceService;
use app\modules\api\common\ApiController;
use Yii;
use yii\helpers\ArrayHelper;

class RepayController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $bank_id = Yii::$app->request->post('bank_id');
        $loan_id = Yii::$app->request->post('loan_id');
        $repay_amount = Yii::$app->request->post('repay_amount');
        $source = Yii::$app->request->post('source', 2);
        $type = Yii::$app->request->post('type', 1); //1线上借款还款 2线上借款续期
        $couponId = Yii::$app->request->post('coupon_id', 0);

        if (empty($version) || empty($bank_id) || empty($loan_id) || empty($repay_amount)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        //判断借款是否存在
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            $array = $this->returnBack('10052');
            echo $array;
            exit;
        }

        //判断还款金额是否为0
        if ($repay_amount <= 0) {
            $array = $this->returnBack('10084');
            echo $array;
            exit;
        }

        $userObj = (new User())->getById($loaninfo->user_id);
        if (empty($userObj)) {
            exit($this->returnBack('10214'));
        }

        $couponVal = 0;
        //判断还款是否使用优惠卷
        if (!empty($couponId) && $type == 1) {
            $couponListResult = (new Coupon_list())->chkCoupon($userObj->mobile, $couponId, $loan_id);
            if ($couponListResult['rsp_code'] != '0000') {
                exit($this->returnBack($couponListResult['rsp_code']));
            }
            $couponVal = $couponListResult['data']->val;
            //部分还款，不能使用优惠卷
            $userLoanList = (new User_loan())->listRenewal($loan_id);
            $loanIds = $loan_id;
            if (!empty($userLoanList)) {
                $loanIds = ArrayHelper::getColumn($userLoanList, 'loan_id');
            }
            $loanRepayList = (new Loan_repay)->getRepayByLoanId($loanIds);
            $amount = (new User_loan())->getRepaymentAmount($loaninfo);
            if (!empty($loanRepayList) || (sprintf('%.2f', $amount) != bcadd($repay_amount, $couponVal, 2))) {
                exit($this->returnBack('10222'));
            }
        }

        //获取精确金额，单位分
        $repayAmountA = $this->getAccuracyAmount($repay_amount);
        $couponValA = $this->getAccuracyAmount($couponVal);

        $overdue = false; //逾期标识
        $account_id = '';
        $user = $loaninfo->user;
        //验证银行卡
        $bank = User_bank::findOne($bank_id);
        if (empty($bank)) {
            $array = $this->returnBack('10043');
            echo $array;
            exit;
        }
        //是不是分期  是否逾期
        if (in_array($loaninfo['business_type'], [5, 6])) {
            $overdue = $overdue;
        } else {
            $overdue = (in_array($loaninfo->status, [12, 13])) ? TRUE : FALSE;
        }
        //判断是否是分期
        if (in_array($loaninfo['business_type'], [5, 6])) { //分期
            $platform = 2;
            //看是否已逾期
            $overdueLoan = (new GoodsBill())->find()->where(['loan_id' => $loan_id, 'bill_status' => 12])->one();
            if (!empty($overdueLoan)) {
                $overdue = true;
            }
            $result = $this->repayLoan($loaninfo, $repay_amount, $bank_id, $source, $platform);
            if (!$result) {
                $array = $this->returnBack('10061');
                echo $array;
                exit;
            }
            $callbackurl = Yii::$app->params['newdev_notify_url'];
            $order_id = $result->repay_id;
        } elseif ($type == 1) {
            $repay = Loan_repay::find()->where(['loan_id' => $loaninfo->loan_id, 'status' => '-1'])->orderBy('id desc')->one();
            if (!empty($repay)) {
                $array = $this->returnBack('10115');
                echo $array;
                exit;
            }
            $insure = Insure::find()->where(['loan_id' => $loaninfo->loan_id, 'type' => 3])->orderBy('id desc')->one();
            if (!empty($insure) && $insure->status == '-1') {
                $array = $this->returnBack('10106');
                echo $array;
                exit;
            }
            $platform = 2;
            //判断是否进行存管内还款
            $user = User::findOne($loaninfo->user_id);
            $bank = User_bank::findOne($bank_id);

            $amount = bcadd($repayAmountA, $couponValA);
            $pay_account = (new Loan_repay())->isDepositoryRepay($loaninfo, $user, $bank, $amount);
            if ($pay_account) {
                $platform = 26;
                $account_id = $pay_account->accountId;
            }
            //还款记录
            $result = $this->repayLoan($loaninfo, $repay_amount, $bank_id, $source, $platform);
            if (!$result) {
                $array = $this->returnBack('10061');
                echo $array;
                exit;
            }
            $callbackurl = Yii::$app->params['newdev_notify_url'];
            $order_id = $result->repay_id;
            //优惠卷使用记录
            $this->couponUse($userObj->user_id, $loan_id, $couponId, $result->id, $repay_amount, $couponVal);
        } else {
            $renewModel = new Renew_amount();
            $is_allow = $renewModel->getRenew($loaninfo->loan_id);
            if (!$is_allow || $loaninfo->status == 8) {
                $array = $this->returnBack('10090');
                echo $array;
                exit;
            }
            $renew_pay = Renewal_payment_record::find()->where(['loan_id' => $loaninfo->loan_id])->orderBy('id desc')->one();
            if (!empty($renew_pay)) {
                if ($renew_pay->status == '-1') {
                    $array = $this->returnBack('10106');
                    echo json_encode($array);
                    exit;
                }
                $time = strtotime(date('Y-m-d H:i:s', time())) - strtotime(date($renew_pay->create_time, time()));
                if ($time < 120) {
                    $array = $this->getMsg((120 - $time), '10107');
                    echo json_encode($array);
                    exit;
                }
            }

            $callbackurl = Yii::$app->params['yibao_renewal'];
            if (in_array($is_allow->type, [1, 2])) {
                $result_loan = $this->renewalLoan($loaninfo, $repay_amount, $bank_id, $source);
                if (!$result_loan) {
                    $array = $this->returnBack('10089');
                    echo json_encode($array);
                    exit;
                }
                $order_id = $result_loan->order_id;
            } else if (in_array($is_allow->type, [3, 4])) {//受托支付
                //判断用户有没有开户、设置密码
                $isCungan = (new Payaccount())->isCunguan($loaninfo->user_id,$type = 1);
                unset($isCungan['isCard']);
                if (in_array(0, $isCungan)) {
                    $array['redirect_url'] = Yii::$app->request->hostInfo . '/borrow/custody/list?type=10&user_id=' . $userObj->user_id.'&list_type=1';
                    $array = $this->returnBack('0000', $array);
                    echo $array;
                    exit;
                }
                $result_new = $this->runCunguanRenew($loaninfo, $bank_id, $source);
                Logger::dayLog('api/repay', 'runCunguanRenew', $result_new);
                if ($result_new['rsp_code'] != '0000' || !isset($result_new['url'])) {
                    if (in_array($result_new['rsp_code'], ['10001', '10002', '10003'])) { //标的登记以及受托成功与否都进行支付
                        $result_loan = $this->renewalLoan($loaninfo, $repay_amount, $bank_id, $source);
                        if (!$result_loan) {
                            $array = $this->returnBack('10089');
                            echo json_encode($array);
                            exit;
                        }
                        $order_id = $result_loan->order_id;
                    } else {
                        $array = $this->returnBack('10089');
                        echo json_encode($array);
                        exit;
                    }
                }
                if (isset($result_new['url'])) {
                    $array['redirect_url'] = $result_new['url'];
                    $array = $this->returnBack('0000', $array);
                    echo $array;
                    exit;
                }
            }
        }
        $post_data = $this->pay($bank, $user, $order_id, $repayAmountA, $callbackurl, $loaninfo, $overdue, $account_id, $couponVal, $type);
        if ($post_data) {
            $array['redirect_url'] = $post_data;
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        } else {
            $array = $this->returnBack('10061');
            echo $array;
            exit;
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
     * 借款还款
     */
    private function repayLoan($loaninfo, $repay_amount, $card_id, $source, $platform = 2) {
        //判断是否可还款
        $chk_loan = (new Loan_repay())->check_repay($loaninfo);
        if (!$chk_loan) {
            return FALSE;
        }
        $user = $loaninfo->user;
        $user_id = $user['user_id'];

        $loan_repay = new Loan_repay();
        $condition = array(
            'repay_id' => '',
            'user_id' => $user_id,
            'loan_id' => $loaninfo->loan_id,
            'bank_id' => $card_id,
            'money' => floatval($repay_amount),
            'platform' => $platform,
            'source' => $source
        );
        $ret = $loan_repay->save_repay($condition);
        if ($ret) {
            return $loan_repay;
        } else {
            return FALSE;
        }
    }

    private function couponUse($userId, $loan_id, $couponId, $order_id, $repay_amount, $couponVal) {
        if (empty($couponId) || empty($couponVal)) {
            return false;
        }
        $condition = [
            'user_id' => (int) $userId,
            'loan_id' => (int) $loan_id,
            'discount_id' => (int) $couponId,
            'repay_id' => (int) $order_id,
            'repay_amount' => $repay_amount,
            'repay_status' => 0,
            'coupon_amount' => $couponVal,
        ];
        $result = (new RepayCouponUse())->addRecord($condition);
        if (empty($result)) {
            exit($this->returnBack('99987'));
        }
        return true;
    }

    /**
     * 借款续期
     */
    private function renewalLoan($loaninfo, $repay_amount, $card_id, $source) {
        $moneys = $loaninfo->getRenewalMoneyNew($loaninfo->loan_id);
        if ($moneys != $repay_amount) {
            return FALSE;
        }
        $user = $loaninfo->user;
        $user_id = $user['user_id'];
        $orderid = date('YmdHis') . rand(1000, 9999);
        $loan_renewal = new Renewal_payment_record();
        $data = [
            'loan_id' => $loaninfo->loan_id,
            'order_id' => $orderid,
            'parent_loan_id' => $loaninfo->parent_loan_id ? $loaninfo->parent_loan_id : 0,
            'user_id' => $loaninfo->user_id,
            'bank_id' => $card_id,
            'platform' => 2,
            'source' => $source,
            'money' => floatval($repay_amount),
        ];
        $ret = $loan_renewal->save_batch($data);
        if ($ret) {
            return $loan_renewal;
        } else {
            return FALSE;
        }
    }

    /**
     * 续期核保、保险购买
     * @param $loaninfo
     * @param $repay_amount
     * @param $source
     * @return bool|mixed
     */
    private function renewalInsurance($loaninfo, $repay_amount, $source) {
        $insuranceService = new InsuranceService();
        $policy_result = $insuranceService->policy($loaninfo->loan_id, floatval($repay_amount), 3);
        if (empty($policy_result) || !is_array($policy_result) || $policy_result['code'] != '0000' || empty($policy_result['data'])) {
            return false;
        }
        $buy_result = $insuranceService->buy($policy_result['data'], $source, 3);
        if (empty($buy_result) || !is_array($buy_result) || $buy_result['code'] != '0000' || empty($buy_result['url'])) {
            return false;
        }

        return $buy_result['url'];
    }

    /**
     * 请求支付
     */
    private function pay($bank, $user, $orderid, $money, $callbackurl, $loaninfo = '', $overdue = false, $account_id = '', $couponVal, $type) {
        $callbackurl = $callbackurl . '?source=app';
        $card_type = ($bank->type == 0) ? 1 : 2;
        $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
        if ($type == 2) {
            $business_code = "YYYZQKJ";
        } else {
            $business_code = $overdue ? "YYYTJYXKJ" : "YYYWX";
        }
//        $business_code = $overdue ? "YYYTJYXKJ" : "YYYZQKJ";
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
            'amount' => $money, // 交易金额
            'orderexpdate' => 60, // 交易金额
            'business_code' => $business_code,
            'userip' => $_SERVER["REMOTE_ADDR"], //
            'coupon_repay_amount' => $couponVal,
            'callbackurl' => $callbackurl,
        );
        if (!empty($account_id)) {
            $postData['loan_id'] = $loaninfo->loan_id;
            $postData['account_id'] = $account_id;
            $postData['business_code'] = 'YYCGKJ';
            $postData['bankname'] = empty($postData['bankname']) ? '工商银行' : $postData['bankname'];
            $postData['bankcode'] = empty($postData['bankcode']) ? 'ICBC' : $postData['bankcode'];
            $postData['forgotPwdUrl'] = Yii::$app->request->hostInfo.'/borrow/custody/setpwdnew?userid='.$user->user_id.'&from=app';
        }
        if ($loaninfo->type == 3) {
            $postData['interest_fee'] = $loaninfo->getInterestFee();
        }
        Logger::dayLog("api/repay", $user->user_id, $postData);
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('payroute/pay', $postData, 2);
        $result = $openApi->parseResponse($res);
        Logger::dayLog("api/repay", $result, $user->user_id);
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
    }

    private function getMsg($time, $code) {
        $array = $this->returnBack($code);
        $array = json_decode($array, true);
        $array['rsp_msg'] = str_replace('{{{time}}}', $time, $array['rsp_msg']);
        return $array;
    }

    /**
     * 分期借款还款
     */
    private function saveBillRepay($data, $loaninfo) {
        if (empty($loaninfo)) {
            return false;
        }
        //判断是否可还款
        $repay_satus = [9, 12, 13];
        if (!in_array($loaninfo['status'], $repay_satus)) {
            return FALSE;
        }
        $loan_repay = new BillRepay();
        //保存还款记录
        $res = $loan_repay->saveRepayInfo($data);
        if ($res) {
            return $loan_repay;
        } else {
            return false;
        }
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

}

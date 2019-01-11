<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Alipay;
use app\commonapi\ApiSms;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Coupon_list;
use app\models\news\RepayCouponUse;
use app\models\news\User_bank;
use app\models\news\BillRepay;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use app\models\news\OverdueLoan;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\service\StageService;
use Yii;
use yii\helpers\ArrayHelper;

class AlipayController extends NewdevController
{

    public function behaviors()
    {
        return [];
    }

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $postData = Yii::$app->request->post();
        if (empty($postData['loan_id']) || $postData['loan_id'] <= 0) {
            exit('借款信息错误');
        }
        $loaninfo = User_loan::findOne($postData['loan_id']);
        if (empty($loaninfo)) {
            exit('借款信息不存在');
        }
        //获取用户id
        if (empty($loaninfo->user_id)) {
            exit('获取user_id失败');
        }
        //获取用户还款金额
        if (empty($postData['money']) || !is_numeric($postData['money'])) {
            exit('还款金额信息错误');
        }
        //获取来源$postData['source']
        if (empty($postData['source'])) {
            exit('来源信息错误');
        }
        //判断借款状态
        $repay_satus = [9, 12, 13];
        if (!in_array($loaninfo->status, $repay_satus)) {
            exit('借款状态错误');
        }
        //判断还款金额是否为0
        if ($postData['money'] <= 0) {
            exit('还款金额不能小于0');
        }
        $repay_info_obj = Loan_repay::find()->select('status')->where(['loan_id' => $loaninfo['loan_id']])->all();
        if (!empty($repay_info_obj)) {
            $status_string = Common::ArrayToString($repay_info_obj, 'status');
            $status_arr = explode(',', $status_string);
            if (in_array("-1", $status_arr)) {
                exit('还款金额信息错误');
            }
        }
        //生成还款订单并保存
        $loanRepayModel = new Loan_repay();
        //看是否已逾期
        if (in_array($loaninfo['business_type'], [5, 6])) {
            $is_yq = (new GoodsBill())->find()->where(['loan_id' => $postData['loan_id'], 'bill_status' => 12])->one();
        } else {
            $is_yq = (in_array($loaninfo->status, [12, 13])) ? TRUE : FALSE;
        }
        $is_yq = TRUE;
        $condition = array(
            'repay_id' => '',
            'user_id' => $loaninfo->user_id,
            'loan_id' => $postData['loan_id'],
            'money' => $postData['money'],
            'platform' => $is_yq ? 8 : 5, //支付宝支付
            'source' => $postData['source'], //还款来源（5 android；6 IOS）
        );
        $repay_res = $loanRepayModel->save_repay($condition);


        if (!$repay_res) {
            exit(json_encode(['status' => '1009', 'msg' => '还款记录创建失败']));
        }


        $orderid = Loan_repay::findOne($repay_res);
        if (empty($orderid->repay_id) || !isset($orderid->repay_id)) {
            exit(json_encode(['status' => '1010', 'msg' => '请求还款失败']));
        }
        $condition['repay_id'] = $orderid->repay_id;
        $aliPay = new Alipay($is_yq);
        $aliPayURL = $aliPay->getAlipayUrl($condition['repay_id'], $condition['money']);
        $this->layout = 'alipay';
        $this->getView()->title = '支付中';
        return $this->render('index', [
            'aliPayURL' => $aliPayURL
        ]);
    }

    //接收异步通知
    public function actionNotify()
    {
        $postData = Yii::$app->request->post();

//        $postData = [
//            'merid'              => '101104116',
//            'merchantOutOrderNo' => 'Y12151436448980064',
//            'orderNo'            => '24101104116170825000925710',
//            'payResult'          => 1,
//            'msg'                => '{"tradeDate":"20170825000934","payMoney":"17000","buyerLogonId":"2088412403442257","tradeNo":"2017082521001004250289256203","thirdNo":"399590022639201708251111071188","rtCode":"00","merid":"101104116"}',
//            'noncestr'           => '43b94115acef46158067d7b793b51367',
//            'sign'               => '97db3073486402dfe70f9660abff53e3',
//        ];
        if (empty($postData)) {
            exit();
        }
        $msg_arr = json_decode($postData['msg'], true);
        Logger::dayLog('Alpay', "postData", $postData);
        //校验还款状态

        $loan_repay = (new Loan_repay())->getRepayByOrderId($postData['merchantOutOrderNo']);
        if (empty($loan_repay) || in_array($loan_repay->status, [1])) {
            $this->sendSuccess('还款状态错误' . $postData['merchantOutOrderNo']);
        }

        //校验借款信息
        $loan_id = $loan_repay->loan_id;
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            $this->sendSuccess('借款信息错误' . $postData['merchantOutOrderNo']);
        }

        //校验用户信息
        $userModel = new User();
        $userinfo = $userModel->find()->select(['user_id', 'mobile'])->where(['user_id' => $loaninfo['user_id']])->one();
        if (empty($userinfo)) {
            $this->sendSuccess('用户信息错误' . $postData['merchantOutOrderNo']);
        }
        if (in_array($loaninfo->business_type, [1, 4])) {
            $this->repaySuccess($postData, $loan_repay, $loaninfo, $userinfo);
        } else {
            $this->stagesRepaySuccess($postData, $loan_repay, $loaninfo, $userinfo);
        }
    }

    //不分期还款失败更新还款表
    private function repayFail($loan_repay, $paybill)
    {
        $conditon = [
            'status' => 4,
            'paybill' => $paybill ? $paybill : '',
        ];
        $up_result = $loan_repay->update_repay($conditon);
        return $up_result;
    }

    //分期还款失败更新分期还款表
    private function stagesRepayFail($loan_repay, $paybill)
    {
        $conditon = [
            'status' => 4,
            'paybill' => $paybill ? $paybill : '',
        ];
        $up_result = $loan_repay->update_repay($conditon);
        return $up_result;
    }

    private function stagesRepaySuccess($postData, $loan_repay, $loaninfo, $userinfo)
    {
        $msg_arr = json_decode($postData['msg'], true);

        //校验还款金额
        $total_fee = isset($msg_arr['payMoney']) ? $msg_arr['payMoney'] : 0;
        //校验还款金额和返回金额是否一致
        if (bccomp($loan_repay->money, $msg_arr['payMoney'], 2) == -1) {
            $this->sendSuccess('还款金额错误' . $postData['merchantOutOrderNo']);
        }
        $huankuan_money = $loaninfo->getStagesAllRepayAmount();
        //回调返回还款失败
        if (!$postData['payResult']) {
            $up_result = $this->stagesRepayFail($loan_repay, $msg_arr['tradeNo']);
            if (!$up_result) {
                $this->sendSuccess('更新还款状态为失败，失败->' . $loan_repay);
            }
            //支付请求异步结果回来后更新失败状态
            $payfailres = (new StageService())->toFail($loan_repay->id);
//            $res = $sms->sendSmsByRepaymentFailed($userinfo['mobile'], $huankuan_money['total_amount']);
            $this->sendSuccess('还款失败->' . $postData['merchantOutOrderNo']);
        }
        //记录接口返回成功的数据
        Logger::errorLog(print_r($postData, true), 'notifysuccess', 'alipay_notify');
        $times = date('Y-m-d H:i:s');
        $leftAmount = bcsub($huankuan_money['total_amount'], $msg_arr['payMoney'], 2); //剩余应还款金额
        $ret = $this->updateStagesRepay($loan_repay, $msg_arr['tradeNo'], $total_fee, $leftAmount);
        if (!$ret) {
            $this->sendSuccess('修改还款信息失败->' . $postData['merchantOutOrderNo']);
        }
        //支付请求异步结果回来后更新成功状态
        $paysuccessres=(new StageService())->toSuccess($loan_repay->id);

        //插入billrepay记录
        $billrepay = (new BillRepay())->getRepayByOrderId($loan_repay['repay_id']);
        if (empty($billrepay)) {
            $repayModel = new BillRepay();
        } else {
            $repayModel = $billrepay;
        }
        $data = [
            'user_id' => $loaninfo->user_id,
            'loan_id' => $loan_repay['loan_id'],
            'bank_id' => $loan_repay['bank_id'] ? $loan_repay['bank_id'] : 0,
            'paybill' => $loan_repay['paybill'],
            'repay_id' => $loan_repay['repay_id'],
            'bill_id' => '',
            'left_money' => $leftAmount,
            'status' => '-1',
            'actual_money' => $loan_repay['money'],
            'platform' => $loan_repay['platform'],
            'repay_time' => $loan_repay['repay_time'],
            'source' => $loan_repay['source'],
        ];
        //保存还款记录
        $repay_res = $repayModel->saveRepayInfo($data);

        $this->sendSuccess('');
    }

    private function getDetailParams($loan_repay, $billInfo, $val)
    {
        $detail = [
            'bill_repay_id' => $loan_repay->id,
            'repay_id' => $loan_repay->repay_id,
            'loan_id' => $loan_repay->loan_id,
            'bill_id' => $billInfo->id,
            'principal' => $val['principal'],
            'interest' => $val['interest'],
            'late_fee' => $val['late_fee'],
        ];
        return $detail;
    }

    //分期还款成功 更新分期还款记录
    private function updateStagesRepay($loan_repay, $transaction_id, $amount, $leftAmount = 0)
    {
        $times = date('Y-m-d H:i:s');
        $repay_condition = [
            'status' => 1,
            'actual_money' => floor($amount * 100) / 100,
            'paybill' => $transaction_id,
            'left_money' => $leftAmount,
            'repay_time' => $times,
        ];
        $ret = $loan_repay->update_repay($repay_condition);
        if (!$ret) {
            Logger::dayLog('new_notify', $loan_repay->repay_id, $parr['res_data']['status'], '更新还款订单失败');
        }
        return $ret;
    }

    private function repaySuccess($postData, $loan_repay, $loaninfo, $userinfo)
    {
        $msg_arr = json_decode($postData['msg'], true);
        //校验还款金额
        $total_fee = isset($msg_arr['payMoney']) ? $msg_arr['payMoney'] : 0;
        //校验还款金额和返回金额是否一致
        if (bccomp($loan_repay->money, $msg_arr['payMoney'], 2) == -1) {
            $this->sendSuccess('还款金额错误' . $postData['merchantOutOrderNo']);
        }
        //获取应还款的金额
        $huankuan_money = $loaninfo->getRepaymentAmount($loaninfo);
        $sms = new ApiSms();
        //回调返回还款失败
        if (!$postData['payResult']) {
            $this->repayFail($loan_repay, $msg_arr['tradeNo']);
            $res = $sms->sendSmsByRepaymentFailed($userinfo['mobile'], $huankuan_money);
            $this->sendSuccess('还款失败->' . $postData['merchantOutOrderNo']);
        }
        //记录接口返回成功的数据
        Logger::errorLog(print_r($postData, true), 'notifysuccess', 'alipay_notify');
        $times = date('Y-m-d H:i:s');

        //修改还款信息
        $repayRes = $this->updateRepay($loan_repay, $msg_arr['tradeNo'], $total_fee, $times);
        if (!$repayRes) {
            $this->sendSuccess('修改还款信息失败->' . $postData['merchantOutOrderNo']);
        }
        //全额还款(应还款金额=实际还款金额) 修改借款状态为已完成
        if ($huankuan_money <= $msg_arr['payMoney']) {
            $status = 8;
            $loanres = $loaninfo->changeStatus($status);
            $loanresult = $loaninfo->update_userLoan(['repay_type' => 2, 'repay_time' => $times]);
            if ($loanres == false || $loanresult == false) {
                $this->sendSuccess('修改借款状态失败->' . $postData['merchantOutOrderNo']);
            }


            if (in_array($loaninfo->business_type, [1, 4])) {
                $where = [
                    "AND",
                    ['loan_id' => $loaninfo->loan_id],
                    ['!=', 'loan_status', 8],
                ];
                $overdusLoan = OverdueLoan::find()->where($where)->one();
                if (!empty($overdusLoan)) {
                    $overdusLoan->clearOverdueLoan();
                    if (!$overdusLoan) {
                        Logger::dayLog('new_notify', $overdusLoan, '更新逾期账单结清状态失败');
                        exit;
                    }
                }
            }


            $ret = $userinfo->inputWhite($userinfo['user_id']);
            $res = $sms->sendSmsByRepaymentAll($userinfo['mobile']);
        } else {
            $res = $sms->sendSmsByRepaymentPortion($userinfo['mobile'], $total_fee, $huankuan_money - $total_fee);
        }
        $this->sendSuccess('');
    }

    private function sendSuccess($info)
    {
        if (!empty($info)) {
            Logger::errorLog(print_r($info, true), 'notifyfaild', 'alipay_notify');
        }
        echo 'success';
        exit();
    }

    //修改还款信息
    private function updateRepay($repay, $transaction_id, $total_fee, $times)
    {
        $params['status'] = 1;
        $params['actual_money'] = round($total_fee, 2);
        $params['paybill'] = $transaction_id;
        $params['repay_time'] = $times;
        return $repay->update_repay($params);
    }

    /**
     * 开放平台支付宝支付
     * @return \yii\web\Response
     */
    public function actionAlipaynew()
    {
        $post_data = Yii::$app->request->post();
        Logger::dayLog('newdev/alipay/alipaynew', $post_data);
        if (empty($post_data['loan_id']) || $post_data['loan_id'] <= 0) {
            exit('借款信息错误');
        }
        $loan_id = $post_data['loan_id'];
        $loaninfo = User_loan::findOne($loan_id);
        if (empty($loaninfo)) {
            exit('借款信息不存在');
        }
        //获取用户id
        if (empty($loaninfo->user_id)) {
            exit('获取user_id失败');
        }
        //获取用户还款金额
        if (empty($post_data['money']) || !is_numeric($post_data['money'])) {
            exit('还款金额信息错误');
        }
        //获取来源$postData['source']
        if (empty($post_data['source'])) {
            exit('来源信息错误');
        }
        //判断借款状态
        $repay_satus = [9, 12, 13];
        if (!in_array($loaninfo->status, $repay_satus)) {
            exit('借款状态错误');
        }
        //判断还款金额是否为0
        if ($post_data['money'] <= 0) {
            exit('还款金额不能小于0');
        }
        //是否是分期     是否可以还款校验
        if (in_array($loaninfo['business_type'], [5, 6, 11])) {
            $goodbillids=$post_data['goodbillids'];
            $goodbill_arr=[];
            if(!empty($goodbillids)){
                $goodbill_arr=explode(',',$goodbillids);
            }
            if(empty($goodbillids) || empty($goodbill_arr)){
                exit('缺少必要数据');
            }
            //重组分期数组
            $periods=(new GoodsBill())->getPeriods($goodbill_arr);
            if(!$periods){
                exit('分期账单不存在');
            }
            //校验期数是否符合续规则
            $is_repay_arr=(new StageService())->checkSubmitRepayBill($periods,$loan_id);
            if(!$is_repay_arr){
                exit('数据不符合规则');
            }
            $chk_repay= (new GoodsBill())->check_repay($loaninfo,$post_data['money'],$goodbill_arr);
        } else {
            $chk_repay = (new Loan_repay())->check_repay($loaninfo);
        }
        if (!$chk_repay) {
            return $this->redirect('/new/repay/error');
        }
        $user = User::findOne($loaninfo['user_id']);

        $couponId = 0;
        $couponVal = 0;
        if (isset($post_data['coupon_id']) && !empty($post_data['coupon_id'])) {
            //分期还款，不能使用优惠券
            if(in_array($loaninfo['business_type'], [5,6,11])){
                exit('分期还款，不能使用优惠券');
            }
            $couponListResult = (new Coupon_list())->chkCoupon($user->mobile, $post_data['coupon_id'], $post_data['loan_id']);
            if ($couponListResult['rsp_code'] != '0000') {
                exit('无效优惠卷');
            }
            $couponId = $post_data['coupon_id'];
            $couponVal = $couponListResult['data']->val;
            //部分还款，不能使用优惠卷
            $userLoanList = (new User_loan())->listRenewal($loan_id);
            $loanIds = $loan_id;
            if (!empty($userLoanList)) {
                $loanIds = ArrayHelper::getColumn($userLoanList, 'loan_id');
            }
            $loanRepayList = (new Loan_repay)->getRepayByLoanId($loanIds);
            $amount = (new User_loan())->getRepaymentAmount($loaninfo);
            if (!empty($loanRepayList) || (sprintf('%.2f', $amount) != bcadd($post_data['money'], $couponVal, 2))) {
                exit('部分还款时，禁用还款优惠券');
            }
        }

        //获取精确金额，单位分
        $repayAmountA = $this->getAccuracyAmount($post_data['money']);
        $couponValA = $this->getAccuracyAmount($couponVal);

        $user_id = $user->user_id;
        $money = isset($post_data['money']) ? floatval($post_data['money']) * 100 : '';
        $bank = User_bank::find()->where(['user_id' => $user_id])->one();
        $repay_info_obj = Loan_repay::find()->select('status')->where(['loan_id' => $loaninfo['loan_id']])->all();
        if (!empty($repay_info_obj)) {
            $status_string = Common::ArrayToString($repay_info_obj, 'status');
            $status_arr = explode(',', $status_string);
            if (in_array("-1", $status_arr)) {
                exit('还款金额信息错误');
            }
        }
        //生成还款订单并保存
        $loanRepayModel = new Loan_repay();
        //看是否已逾期
        if (in_array($loaninfo['business_type'], [5, 6, 11])) {
            $isOverdue = (new GoodsBill())->find()->where(['loan_id' => $post_data['loan_id'], 'bill_status' => 12])->one();
        } else {
            $isOverdue = (in_array($loaninfo->status, [12, 13])) ? TRUE : FALSE;
        }
        $condition = array(
            'repay_id' => '',
            'user_id' => $loaninfo->user_id,
            'loan_id' => $post_data['loan_id'],
            'money' => $post_data['money'],
            'platform' => $isOverdue ? 25 : 23, //支付宝支付
            'source' => $post_data['source'], //还款来源（5 android；6 IOS）
        );
        $repay_res = $loanRepayModel->save_repay($condition);
        if (!$repay_res) {
            exit(json_encode(['status' => '1009', 'msg' => '还款记录创建失败']));
        }
        //是否是分期
        if (in_array($loaninfo['business_type'], [5, 6, 11])) {
            if(empty($is_repay_arr)){
                exit('缺少必要数据');
            }
            // 请求支付前 锁定到待支付
            $locktorepay =(new StageService())->locktorepay($repay_res,$is_repay_arr);
        }

        $orderid = Loan_repay::findOne($repay_res);
        if (empty($orderid->repay_id) || !isset($orderid->repay_id)) {
            exit(json_encode(['status' => '1010', 'msg' => '请求还款失败']));
        }
        //优惠卷使用记录 
        $this->couponUse($loaninfo->user_id, $loan_id, $couponId, $orderid->id, $post_data['money'], $couponVal);
        $card_type = ($bank->type == 0) ? 1 : 2;
        $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
        $business_code = $isOverdue ? "ZFBPAYTJYX" : "ZFBPAY";
        $postData = array(
            'orderid' => $orderid->repay_id, // 请求唯一号
            'identityid' => (string)$user_id, // 用户标识
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
            'amount' => $repayAmountA, // 交易金额
            'orderexpdate' => 60,
            'business_code' => $business_code,
            'userip' => $_SERVER["REMOTE_ADDR"], //ip
            'callbackurl' => Yii::$app->params['newdev_notify_url'], // 异步回调地址
            'source' => empty($post_data['source']) ? 5 : $post_data['source'], //还款来源（5 android；6 IOS）
            'coupon_repay_amount' => $couponVal,
        );
        Logger::errorLog(print_r($postData, true), 'openpay');
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('payment/pay', $postData, 2);
        Logger::errorLog(print_r($res, true), 'huankuan_alipay_url');
        $res = json_decode($res, true);
        if ($res['res_code'] != '0000') {
            exit('支付失败');
        }
        $aliPayURL = $res['res_data'];
        $this->layout = 'alipay';
        $this->getView()->title = '支付中';
        return $this->render('newalipay', [
            'aliPayURL' => $aliPayURL
        ]);
    }

    //核实优惠卷
    private function chkCoupon($userObj, $couponId, $loan_id)
    {
        if (empty($userObj) || !is_object($userObj)) {
            exit('非法用户');
        }
        $couponListObj = (new Coupon_list())->getByIdAndMobile($couponId, $userObj->mobile);
        if (empty($couponListObj)) {
            exit('无效优惠卷');
        }
        if ($couponListObj->status != 1) {
            exit('无效优惠卷');
        }
        $date = date('Y-m-d H:i:s');
        if ($couponListObj->start_date > $date || $couponListObj->end_date < $date) {
            exit('优惠卷不在有效期内');
        }
        $repayCouponUseObj = (new RepayCouponUse())->getByDiscountId($couponId);
        if (!empty($repayCouponUseObj)) {
            exit('该优惠卷已使用');
        }
        $repayCouponUse = (new RepayCouponUse())->getByLoanId($loan_id);
        if (!empty($repayCouponUse)) {
            exit('该账单只允许使用一次优惠卷');
        }
        //续期
        $userLoanList = (new User_loan())->listRenewal($loan_id);
        if (!empty($userLoanList)) {
            $loanIdArr = ArrayHelper::getColumn($userLoanList, 'loan_id');
            $repayCouponUseList = (new RepayCouponUse())->getByLoanId($loanIdArr);
            if (!empty($repayCouponUseList)) {
                exit('该账单只允许使用一次优惠卷');
            }
        }
        return $couponListObj;
    }

    private function couponUse($userId, $loan_id, $couponId, $order_id, $repay_amount, $couponVal)
    {
        if (empty($couponId) || empty($couponVal)) {
            return false;
        }
        $condition = [
            'user_id' => (int)$userId,
            'loan_id' => (int)$loan_id,
            'discount_id' => (int)$couponId,
            'repay_id' => (int)$order_id,
            'repay_amount' => $repay_amount,
            'repay_status' => 0,
            'coupon_amount' => $couponVal,
        ];
        $result = (new RepayCouponUse())->addRecord($condition);
        if (empty($result)) {
            exit(json_encode(['status' => '11', 'msg' => '优惠卷记录失败']));
        }
        return true;
    }

    //获取精确金额，单位分
    private function getAccuracyAmount($amount)
    {
        if (empty($amount) && $amount != 0) {
            return false;
        }
        $amount = floatval($amount);
        $amount = (int)round($amount * 100);
        return $amount;
    }
}

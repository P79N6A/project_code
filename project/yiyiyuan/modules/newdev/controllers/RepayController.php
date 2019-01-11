<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Apidepository;
use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\BillRepay;
use app\models\news\Common as Common2;
use app\models\news\Coupon_list;
use app\models\news\Exchange;
use app\models\news\Function_control;
use app\models\news\PayAccountExtend;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use app\models\news\Money_limit;
use app\models\news\OverdueLoan;
use app\models\news\Payaccount;
use app\models\news\Renew_amount;
use app\models\news\RepayCouponUse;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\service\StageService;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class RepayController extends NewdevController {

    public $layout = 'loan';

    public function beforeAction($action) {
        return TRUE;
    }

    public function actionRepaychoose() {
        $this->layout = '_data';
        $this->getView()->title = "还款确认";
        $loan_id = $this->get('loan_id');
        $coupon_amount = 0;
        $loan = User_loan::findOne($loan_id);
        if (empty($loan)) {
            return $this->redirect('/new/loan');
        }
        $userObj = $this->getUser();
        if (empty($userObj)) {
            return $this->redirect('/new/loan');
        }
        $coupon_id = !empty($this->get('coupon_id')) ? $this->get('coupon_id') : '';
        //计算应还款金额
        $actual_amount = (new User_loan())->getRepaymentAmount($loan);
        //判断是否分期
        if (in_array($loan['business_type'], [5, 6])) {
            //查询账单表状态！=8的记录 取还款时间
            $billInfo = (new GoodsBill())->getLatelyPhase($loan_id);
            //最后还款时间
            $end_date = date("Y年m月d日", strtotime($billInfo['end_time']) - 86400);
            //还款计划
            $repay_info = (new GoodsBill())->getRepaylist($loan_id);
            if (!empty($repay_info)) {
                $amount = $loan->getLoanStagesRepay($returnArray = true, $actual = true);
                unset($amount['total_amount']);
                foreach ($repay_info as $val) {
                    $repay_plan[] = [
                        'amount' => isset($amount[$val['id']]) ? $amount[$val['id']] : $val['current_amount'],
                        'status' => $val['bill_status'],
                        'days' => date("Y-m-d", strtotime($val['end_time']) - 86400),
                        'now_term' => $val['phase'],
                        'total_term' => $val['number'],
                        'is_click' => $this->getIsLoanClick($val['bill_status']),
                    ];
                }
            }
        } else {  //不是分期的
            //最后还款时间
            $end_date = date("Y年m月d日", strtotime($loan['end_date']) - 86400);
            $repay_plan[] = [
                'amount' => $actual_amount,
                'status' => $loan['status'],
                'days' => date("Y-m-d", strtotime($loan['end_date']) - 86400),
                'now_term' => 1,
                'total_term' => 1,
                'is_click' => $this->getIsLoanClick($loan['status']),
            ];
        }
        $bankModel = new User_bank();
        $bank_count = User_bank::find()->where(['user_id' => $loan['user_id'], 'status' => 1])->count();
        $banklist = $bankModel->limitCardsSort($loan->user_id, 1);
        $bank_str = Common::ArrayToString($banklist, 'sign');
        $bank_arr = explode(',', $bank_str);
        if (!in_array('2', $bank_arr)) {
            //无可用卡
            $flag = 2;
        } else {
            $flag = 1;
        }
        //用户是否可续期
        $renewModel = new Renew_amount();
        $user_allow = $renewModel->getRenew($loan->loan_id);
        //微信还款方式
        $function_control_model = new Function_control();
        $wxpay_type = $function_control_model->getPatmenthod([1, 6]);
        if (!empty($wxpay_type)) {
            $wx_type = $wxpay_type->type;
        } else {
            $wx_type = 0;
        }
        //判断是否可体内还款 1:支持，2：不支持
        $payCg = (new Loan_repay())->payCg($loan);
        if ($payCg) {
            $is_support = 1;
        } else {
            $is_support = 2;
        }

        //拉取面向全部用户类型的有效优惠券
        (new Coupon_list())->pullCoupon($userObj->mobile);

        $total_amoun = $actual_amount; //总还款金额
        $couponCount = 0;
        $isCoupon = $this->chkCoupon($loan_id);
        if (!empty($isCoupon)) {
            $couponlist = (new Coupon_list())->getValidList($userObj->mobile, $term = 1, $coupon_type = 5);
            $couponCount = count($couponlist);
            //有还款时，不允许使用优惠卷
            $loanRepayList = (new Loan_repay)->getRepayByLoanId($loan_id);
            if (!empty($loanRepayList)) {
                $couponCount = 0;
            }
            if (!empty($coupon_id)) {
                $couponListObj = (new Coupon_list())->getByIdAndMobile($coupon_id, $userObj->mobile);
                if (empty($couponListObj)) {
                    exit('非法优惠卷');
                }
                $repayCouponUseObj = (new RepayCouponUse())->getByDiscountId($coupon_id);
                if (!empty($repayCouponUseObj)) {
                    exit('该优惠卷已使用');
                }
                $coupon_amount = $couponListObj->val;
                $actual_amount = bcsub($actual_amount, $coupon_amount, 2);
                if ($actual_amount < 0) {
                    $actual_amount = 0;
                }
            }
        }
        //判断用户卡是否有存管开户卡
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($loan->user_id, 2, 1);
        $order = (new User())->getPerfectOrder($loan->user_id, 4, 15, 1);
        $orderInfo = (new Common2())->create3Des(json_encode($order, true));
        $jsinfo = $this->getWxParam();
        return $this->render('repaychoose', [
                    'user_allow' => $user_allow,
                    'coupon_count' => $couponCount,
                    'coupon_id' => $coupon_id,
                    'coupon_amount' => $coupon_amount,
                    'jsinfo' => $jsinfo,
                    'flag' => $flag,
                    'loan' => $loan,
                    'end_date' => $end_date,
                    'banklist' => $banklist,
                    'account_bank' => $isAccount,
                    'bank_count' => $bank_count,
                    'orderInfo' => $orderInfo,
                    'repay_plan' => $repay_plan,
                    'actual_amount' => $actual_amount,
                    'total_amoun' => $total_amoun,
                    'csrf' => $this->getCsrf(),
                    'wxpay_type' => $wx_type,
                    'is_support' => $is_support,
                    'user_info' => $userObj,
        ]);
    }

    /*
     * 还款券
     * */

    public function actionHgcoupon() {
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "还款券";
        $couponlist = '';
        $coupon_id = Yii::$app->request->get('coupon_id', '');
        $loan_id = Yii::$app->request->get('loan_id');
        $loan = User_loan::findOne($loan_id);
        $userInfo = (new User())->getUserinfoByUserId($loan->user_id);
        //优惠卷列表 2:还款券
        $coupon = new Coupon_list();
        $couponlist = $coupon->getValidList($userInfo->mobile, 1, 5);
        return $this->render('hgcoupon', [
                    'couponlist' => $couponlist,
                    'loan' => $loan,
                    'coupon_id' => $coupon_id,
                    'loan_id' => $loan_id,
                    'jsinfo' => $jsinfo,
        ]);
    }

    /**
     * 判断子订单是否可点击
     */
    public function getIsLoanClick($bill_status) {
        if (empty($bill_status)) {
            return NULL;
        }
        if ($bill_status == 8 || $bill_status == 12) {
            $is_click = 2;
        } else {
            $is_click = 1;
        }
        return $is_click;
    }

    /**
     * 还款:线下还款
     * @return string|Response
     */
    public function actionRepay() {
        $this->layout = "main";
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "还款方式";
        $loan_id = $this->get('loan_id');
        $coupon_id = $this->get('coupon_id');
        if(empty($loan_id)){
            return $this->redirect('/borrow/loan');
        }
        Logger::dayLog('weixin/repay/repay', 'newdev,线下还款loan_id：', $loan_id);
        return $this->redirect('/borrow/repay/repay?loan_id='.$loan_id.'&coupon_id='.$coupon_id);
        //判断借款的状态，如果是已完成状态，则直接跳转
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo) || $loaninfo['status'] == 8 || $loaninfo['status'] == 11) {
            return $this->redirect('/new/loan');
        }
        $loaninfo['huankuan_amount'] = $loaninfo->getRepaymentAmount($loaninfo);
        //春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');

        return $this->render('repay', [
                    'encrypt' => ImageHandler::encryptKey($loaninfo->user_id, 'repay'),
                    'jsinfo' => $jsinfo,
                    'loan_id' => $loan_id,
                    'loaninfo' => $loaninfo,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'saveMsg' => '',
        ]);
    }

    /**
     * 还款:线下还款
     * @return string|Response
     */
    public function actionRepaysave() {
        return $this->redirect('/borrow/loan');
        $postdata = $this->post();
        $loan_id = $postdata['loan_id'];
        if (!isset($loan_id)) {
            return $this->redirect('/new/loan');
        }
        $user = $this->getUser();
        $user_id = $user->user_id;
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if ($loaninfo['status'] == 8 || $loaninfo['status'] == 11) {
            return $this->redirect('/new/loan');
        }
        $transaction = Yii::$app->db->beginTransaction();
        $loan_repay = new Loan_repay();
        $condition = [
            'repay_id' => ' ',
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'money' => 0,
        ];
        foreach ($postdata['supplyUrl'] as $name => $up_info) {
            if (!empty($up_info)) {
                $name = 'pic_repay' . $name;
                $condition[$name] = $up_info;
            }
        }
        $condition['status'] = 3;
        $loan_result = $loan_repay->save_repay($condition);
        if (!$loan_result) {
            $transaction->rollBack();
            return $this->redirect('/new/loan');
        }
        //修改借款记录的状态为11
        $loan = User_loan::findOne($loan_id);
        $status = 11;
        $ret = $loan->changeStatus($status);
        if (!$ret) {
            $transaction->rollBack();
            return $this->redirect('/new/loan');
        }

        $transaction->commit();
        return $this->redirect('/new/repay/verify?loan_id=' . $loan_id);
    }

    /**
     * 还款:银行卡还款
     * @return Response
     */
    public function actionPayyibao() {
        $post_data = $this->post();
        $loan_id = isset($post_data['loan_id']) ? intval($post_data['loan_id']) : 0;
        $coupon_id = isset($post_data['coupon_id']) ? intval($post_data['coupon_id']) : 0;
        $money = isset($post_data['money_order']) ? floatval($post_data['money_order']) * 100 : '';
        $card_id = isset($post_data['card_id']) ? $post_data['card_id'] : '';
        $goodbillids = isset($post_data['goodbillids']) ? $post_data['goodbillids'] : '';
        $lastperiod=1;
//        var_dump($card_id);die;
        if (empty($loan_id) || empty($card_id)) {
            echo "数据错误,请刷新页面重新获取";
            exit;
        }
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            exit("该笔账单不存在");
        }
        $insure = \app\models\news\Insure::find()->where(['loan_id' => $loaninfo->loan_id, 'type' => 3])->orderBy('id desc')->one();
        if (!empty($insure) && $insure->status == '-1') {
            echo "您有支付中的续期还款";
            exit;
        }
        $repay = Loan_repay::find()->where(['loan_id' => $loaninfo->loan_id, 'status' => '-1'])->orderBy('id desc')->one();
        if (!empty($repay)) {
            echo "您有支付中的还款";
            exit;
        }

        $user = User::findOne($loaninfo['user_id']);
        if (empty($user)) {
            exit('非法用户');
        }
        $goodbill_arr=[];
        if(!empty($goodbillids)){
            $goodbill_arr=explode(',',$goodbillids);
        }
        if(!empty($goodbill_arr)){
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
        }
        $coupon_amount = 0;
        if (!empty($coupon_id)) {
            //分期还款，不能使用优惠券
            if(in_array($loaninfo['business_type'], [5,6,11])){
                exit('分期账单，不能使用优惠券');
            }
            $couponListResult = (new Coupon_list())->chkCoupon($user->mobile, $coupon_id, $loan_id);
            if ($couponListResult['rsp_code'] != '0000') {
                exit('该优惠券已使用或无法使用');
            }
            $coupon_amount = $couponListResult['data']->val;
            //部分还款，不能使用优惠卷
            $userLoanList = (new User_loan())->listRenewal($loan_id);
            $loanIds = $loan_id;
            if (!empty($userLoanList)) {
                $loanIds = ArrayHelper::getColumn($userLoanList, 'loan_id');
            }
            $loanRepayList = (new Loan_repay)->getRepayByLoanId($loanIds);
            $amount = (new User_loan())->getRepaymentAmount($loaninfo);
            if (!empty($loanRepayList) || (sprintf('%.2f', $amount) != bcadd($post_data['money_order'], $coupon_amount, 2))) {
                $coupon_amount = 0;
                $coupon_id = 0;
            }
        }

        //获取精确金额，单位分
        $repayAmountA = $this->getAccuracyAmount($post_data['money_order']);
        $couponValA = $this->getAccuracyAmount($coupon_amount);

        //校验是否可还款
        $loan_repay = new Loan_repay();
        $chk_repay = $loan_repay->check_repay($loaninfo);
        if (!$chk_repay) {
            return $this->redirect('/new/repay/error');
        }

        $user_id = $user->user_id;
        $bank = User_bank::findOne($card_id);
        $platform = 2;
        $isOverdue = false;  //定义变量 默认没有逾期
        //是否是分期
        if (in_array($loaninfo['business_type'], [5, 6, 11])) {
            //看是否已逾期
            $overdueLoan = (new GoodsBill())->find()->where(['loan_id' => $loan_id, 'bill_status' => 12])->one();
            if (!empty($overdueLoan)) {
                $isOverdue = true;
            }
        }
        //判断是否进行存管内还款
        $repay_dep = 'NO';
        $depositoryAmount = bcadd($repayAmountA, $couponValA);
        if (in_array($loaninfo['business_type'], [5,6,11])) {//分期是否能走体内
            $periods_keys= array_keys($periods);
            $lastperiod=end($periods_keys);//取出最后一期期数
            $pay_account = $loan_repay->isDepositoryRepaytemrs($loaninfo, $user, $bank, $repayAmountA, $isOverdue, $goodbill_arr, $lastperiod);
        }else{
            $pay_account = $loan_repay->isDepositoryRepay($loaninfo, $user, $bank, $depositoryAmount);
        }
        if ($pay_account) {
            $payAccount = new Payaccount();
            $isAuth = $payAccount->getPaysuccessByUserId($user->user_id, 2, 6);
            $authTimeOut = true;
            if(!empty($isAuth)){
                $o_pay_account_extend = (new PayAccountExtend())->getByUserIdAndStep($user->user_id, 6);
                if(!empty($o_pay_account_extend)){
                   $payaccount_res = $o_pay_account_extend->getLegal(2);
                   if(!$payaccount_res){
                        $authTimeOut = false;
                   }
                }
            }
            if(empty($isAuth) || empty($authTimeOut) ){
                //还款授权
                $isRepayAuth = $payAccount->getPaysuccessByUserId($user->user_id, 2, 4);
                //缴费授权
                $isFundAuth = $payAccount->getPaysuccessByUserId($user->user_id, 2, 5);
                if (empty($isFundAuth) || empty($isRepayAuth)) {
                    $fundAuthUrl = '/new/depositorynew/choice?user_id=' . $user->user_id;
                    return $this->redirect($fundAuthUrl);
                }
                $repayTimeOut = $isRepayAuth->isTimeOut();//true过期 false未过期
                $fundTimeOut = $isFundAuth->isTimeOut();//true过期 false未过期
                if($repayTimeOut || $fundTimeOut){
                    $fundAuthUrl = '/new/depositorynew/choice?user_id=' . $user->user_id;
                    return $this->redirect($fundAuthUrl);
                }
            }
            $repay_dep = 'YES';
            $platform = 26;
        }
        $condition = [
            'repay_id' => '',
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'bank_id' => $card_id,
            'money' => isset($post_data['money_order']) ? floatval($post_data['money_order']) : '',
            'platform' => $platform,
        ];
        Logger::errorLog(print_r($condition, true), 'huankuan_qingqiu');
        $ret = $loan_repay->save_repay($condition);
        if (!$ret) {
            return $this->redirect('/new/repay/error');
        }
        if(!empty($is_repay_arr)){
            // 请求支付前 锁定到待支付
            $locktorepay =(new StageService())->locktorepay($ret,$is_repay_arr);
        }
        $orderid = Loan_repay::findOne($ret);
        if (empty($orderid->repay_id) || !isset($orderid->repay_id)) {
            return $this->redirect('/new/repay/error');
        }

        if (!empty($coupon_id)) {
            //添加使用优惠券
            $coupon_use_condition = array(
                'user_id' => $user_id,
                'discount_id' => $coupon_id,
                'repay_id' => $orderid->id,
                'repay_amount' => isset($post_data['money_order']) ? floatval($post_data['money_order']) : '',
                'repay_status' => 0, //还款中
                'coupon_amount' => $coupon_amount,
                'loan_id' => $loan_id,
            );
            $repay_coupon_use = new RepayCouponUse();
            $repayCouponUseResult = $repay_coupon_use->addRecord($coupon_use_condition);
            if (empty($repayCouponUseResult)) {
                exit('优惠卷记录添加失败');
            }
        }

        $orderid = $orderid->repay_id;
        $card_type = ($bank->type == 0) ? 1 : 2;
        $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
        //是否是分期  是否逾期
        if (in_array($loaninfo['business_type'], [5, 6], 11)) {
            $overdue = $isOverdue;
        } else {  //如果不是分期 根据状态判断是否逾期
            $overdue = (in_array($loaninfo->status, [12, 13])) ? TRUE : FALSE;
        }
        $business_code = $overdue ? "YYYTJYXKJ" : "YYYWX";

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
            'amount' => $repayAmountA, // 交易金额
            'orderexpdate' => 60,
            'business_code' => $business_code,
            'userip' => $_SERVER["REMOTE_ADDR"], //ip
            'coupon_repay_amount' => $coupon_amount,
            'period' => empty($lastperiod) ? 1 : $lastperiod,
            'callbackurl' => Yii::$app->params['newdev_notify_url'], // 异步回调地址
        );

        if ($repay_dep == 'YES') {//存管内还款添加字段
            $postData['loan_id'] = $loan_id;
            $postData['account_id'] = $pay_account->accountId;
            $postData['business_code'] = 'YYCGKJ';
            $postData['bankname'] = empty($postData['bankname']) ? '工商银行' : $postData['bankname'];
            $postData['bankcode'] = empty($postData['bankcode']) ? 'ICBC' : $postData['bankcode'];
            $postData['forgotPwdUrl'] = Yii::$app->request->hostInfo.'/borrow/custody/setpwdnew?userid='.$user_id.'&from=weixin';

        }
        if ($loaninfo->type == 3) {
            $postData['interest_fee'] = $loaninfo->getInterestFee();
        }
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('payroute/pay', $postData, 2);
        $result = $openApi->parseResponse($res);
        Logger::dayLog('weixin/repay/payyibao',$postData,$result);
        if ($result['res_code'] != 0 || !isset($result['res_data']['url']) || empty($result['res_data']['url'])) {
            return $this->redirect('/new/repay/error');
        }
        $redirect_url = (string) $result['res_data']['url'];
        if (SYSTEM_ENV == 'prod') {
            $redirect_url = str_replace('xianhuahua', 'yaoyuefu', $redirect_url);
        }
        return $this->redirect($redirect_url);
    }

    /**
     * 微信支付失败页面
     * @param string $source
     * @return string
     */
    public function actionError($source = '') {
        $this->getView()->title = '还款失败';
        $jsinfo = $this->getWxParam();
         $user = $this->getUser();
        //$user_id = $user->user_id;
        $user_id = !empty($user)?$user->user_id:'';
        return $this->render('error', [
                    'source' => $source,
                    'jsinfo' => $jsinfo,
                    'user_id' => $user_id
                        ]
        );
    }

    /**
     * app支付失败页面
     * @param string $source
     * @return string
     */
    public function actionErrorapp($source = '') {
        $this->layout = "loan_app";
        $this->getView()->title = '还款失败';
        $jsinfo = $this->getWxParam();
        return $this->render('errorapp', [
                    'source' => $source,
                    'jsinfo' => $jsinfo
                        ]
        );
    }

    /**
     * 支付中页面
     * @param string $source
     * @return string
     */
    public function actionVerify($source = 1) {
        $this->getView()->title = "提交审核中";
        $this->layout = 'data';
        $jsinfo = $this->getWxParam();

        return $this->render('verify', [
                    'source' => $source,
                    'jsinfo' => $jsinfo
        ]);
    }

    /**
     * 支付中页面
     * @param string $source
     * @return string
     */
    public function actionPayverify($source = 1) {
        $this->getView()->title = "提交审核中";
        $this->layout = 'data';

        return $this->render('payverify', [
                    'source' => $source,
        ]);
    }

    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    //监测是否可以显示优惠卷
    private function chkCoupon($loanId) {
        if (empty($loanId)) {
            return false;
        }
        $repayCouponUse = (new RepayCouponUse())->getByLoanId($loanId);
        if (!empty($repayCouponUse)) {
            return false;
        }
        $userLoanList = (new User_loan())->listRenewal($loanId);
        if (!empty($userLoanList)) {
            $loanIdArr = ArrayHelper::getColumn($userLoanList, 'loan_id');
            $repayCouponUseList = (new RepayCouponUse())->getByLoanId($loanIdArr);
            if (!empty($repayCouponUseList)) {
                return false;
            }
        }
        return true;
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

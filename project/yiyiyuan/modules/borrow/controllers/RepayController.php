<?php

namespace app\modules\borrow\controllers;

use app\commonapi\ImageHandler;
use app\commonapi\Logger;
use app\commonapi\Common;
use app\models\news\BillRepay;
use app\models\news\Common as Common2;
use app\models\news\Coupon_list;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use app\models\news\Payaccount;
use app\models\news\Renew_amount;
use app\models\news\RepayCouponUse;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\Function_control;
use app\models\news\User_loan;
use app\models\service\StageService;
use Yii;
use yii\helpers\ArrayHelper;

class RepayController extends BorrowController {
    public $layout = 'repay/repay';

    public function actionHelp() {
        $this->getView()->title = "帮助中心";
        $userInfo = $this->getUser();

        return $this->render('help');
    }

    /**
     * 还款确认页
     * @return string
     */
    public function actionRepaychoose() {
        $this->layout = 'repay/repay';
        $this->getView()->title = "还款确认";
        $loan_id = $this->get('loan_id');
        $goodbillids=$this->get('goods_bill');
        $coupon_amount = 0;
        $lastperiod=1;
        $loan = User_loan::findOne($loan_id);
        if (empty($loan)) {
            return $this->redirect('/borrow/loan');
        }
        $userObj = $this->getUser();
        if (empty($userObj)) {
            return $this->redirect('/borrow/loan');
        }
        $goodbill_arr=[];
        if(!empty($goodbillids)){
            $goodbill_arr=explode(',',$goodbillids);
        }
        if(!empty($goodbill_arr)){
            //查询分期账单是不是真是存在
            $periods=(new GoodsBill())->getPeriods($goodbill_arr);
            if(!$periods){
                return $this->redirect('/borrow/loan');
            }
            $periods_keys= array_keys($periods);
            $lastperiod=end($periods_keys);//取出最后一期期数
            //时间是否大于10分钟限制&& 同步还款金额到billrepay
            $is_can_repay=(new StageService())->checkRepaybillModifytime($loan_id);
            if(!$is_can_repay){
                return $this->redirect('/borrow/loan');
            }
        }

        $coupon_id = !empty($this->get('coupon_id')) ? $this->get('coupon_id') : '';
        //计算应还款金额
        $actual_amount = (new User_loan())->getRepaymentAmount($loan,1,$goodbill_arr);

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
        //判断是否可以线下还款
        if (in_array($loan['business_type'], [5, 6, 11])) {
            $xianxia_type = 0;
        }else{
            $xianxia_type = 1;
        }

        //判断是否可体内还款 1:支持，2：不支持
        $payCg = (new Loan_repay())->payCg($loan,$lastperiod);
        if ($payCg) {
            $is_support = 1;
        } else {
            $is_support = 2;
        }
        //拉取面向全部用户类型的有效优惠券
        (new Coupon_list())->pullCoupon($userObj->mobile);
        $total_amoun = $actual_amount; //总还款金额
        $couponCount = 0;
        $isCoupon = (new Coupon_list())->chkCouponShow($loan_id,$loan,$goodbill_arr);
        if (!empty($isCoupon)) {
            $couponlist = (new Coupon_list())->getValidList($userObj->mobile, $term = 1, $coupon_type = 5);
            $couponCount = count($couponlist);
            $loanRepayList = (new Loan_repay)->getRepayByLoanId($loan_id);
            if (!empty($loanRepayList)) {
                $couponCount = 0;
            }
            if (!empty($coupon_id)) {
                $Coupon_arr = (new Coupon_list())->geHhgCouponDate($coupon_id,$userObj->mobile);
                if(empty($Coupon_arr)){
                    exit('无效优惠卷');
                }else{
                    $coupon_amount =$Coupon_arr['coupon_amount'];//优惠卷金额
                }
            } else {
                if(!empty($couponlist) && $couponCount>0){
                    $MaxKey = (new Coupon_list())->getArrayMax($couponlist,'val');
                    $coupon_id = $couponlist[$MaxKey]['id'];
                    $coupon_amount = $couponlist[$MaxKey]['val'];
                }
            }
            $actual_amount = bcsub($actual_amount, $coupon_amount, 2);
            if ($actual_amount < 0) {
                $actual_amount = 0;
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
            'xianxia_type' => $xianxia_type,
            'is_support' => $is_support,
            'user_info' => $userObj,
            'goodbillids'=>$goodbillids,
        ]);
    }

    /**
     * 线下还款页面
     * @return string|\yii\web\Response
     * @author 王新龙
     * @date 2018/7/25 9:31
     */
    public function actionRepay() {
        $this->layout = "repay/offline";
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "转账还款";
        $loan_id = $this->get('loan_id');
        $coupon_id = $this->get('coupon_id');
        Logger::dayLog('weixin/repay/repay', '线下还款loan_id：', $loan_id);
        //无借款 or 已结清 or 还款中 跳转首页
        $o_user_loan = (new User_loan())->getById($loan_id);
        if (empty($o_user_loan) || $o_user_loan->status == 8 || $o_user_loan->status == 11) {
            return $this->redirect('/borrow/loan');
        }
        //用户
        $o_user = (new User())->getById($o_user_loan->user_id);
        if (empty($o_user)) {
            return $this->redirect('/borrow/loan');
        }
        $huankuan_amount = $o_user_loan->getRepaymentAmount($o_user_loan);
        //优惠卷
        if (!empty($coupon_id)) {
            $coupon_result = (new Coupon_list())->chkCoupon($o_user->mobile, $coupon_id, $loan_id);
            if ($coupon_result['rsp_code'] != '0000') {
                return $this->redirect('/borrow/loan');
            }
            $coupon_val = $coupon_result['data']->val;
            $huankuan_amount = bcsub($huankuan_amount, $coupon_val, 2);
        }

        //春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');

        return $this->render('offline', [
            'encrypt' => ImageHandler::encryptKey($o_user_loan->user_id, 'repay'),
            'jsinfo' => $jsinfo,
            'loan_id' => $loan_id,
            'coupon_id' => $coupon_id,
            'loaninfo' => $o_user_loan,
            'huankuan_amount' => $huankuan_amount,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'now_time' => $now_time,
            'saveMsg' => '',
            'user_id' => $o_user->user_id
        ]);
    }

    /**
     * 线下还款保存
     * @return \yii\web\Response
     * @author 王新龙
     * @date 2018/7/25 9:31
     */
    public function actionRepaysave() {
        $loan_id = $this->post('loan_id', '');
        $coupon_id = $this->post('coupon_id', '');
        $supplyUrl = $this->post('supplyUrl', '');
        $paybill = $this->post('paybill', '');
        if (!isset($loan_id)) {
            return $this->redirect('/borrow/loan');
        }
        Logger::dayLog('weixin/repay/repaysave', '线下还款', $loan_id, $coupon_id, $supplyUrl, $paybill);
        //还款凭证、还款订单号不能为空
        if (empty($supplyUrl) || empty($paybill)) {
            return $this->redirect('/borrow/repay/repay?loan_id=' . $loan_id . '&coupon_id=' . $coupon_id);
        }
        $user = $this->getUser();
        $user_id = $user->user_id;
        $o_user_loan = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if ($o_user_loan->status == 8 || $o_user_loan->status == 11) {
            return $this->redirect('/borrow/loan');
        }
        //用户
        $o_user = (new User())->getById($o_user_loan->user_id);
        if (empty($o_user)) {
            return $this->redirect('/borrow/loan');
        }
        //优惠卷
        $coupon_val = 0;
        if (!empty($coupon_id)) {
            $coupon_result = (new Coupon_list())->chkCoupon($o_user->mobile, $coupon_id, $loan_id);
            if ($coupon_result['rsp_code'] != '0000') {
                return $this->redirect('/borrow/loan');
            }
            $coupon_val = $coupon_result['data']->val;
        }
        $transaction = Yii::$app->db->beginTransaction();
        $loan_repay = new Loan_repay();
        $condition = [
            'repay_id' => ' ',
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'money' => 0,
            'paybill' => $paybill
        ];
        foreach ($supplyUrl as $name => $up_info) {
            if (!empty($up_info)) {
                $name = 'pic_repay' . $name;
                $condition[$name] = $up_info;
            }
        }
        if (!isset($condition['pic_repay1']) || empty($condition['pic_repay1'])) {
            $transaction->rollBack();
            Logger::dayLog('weixin/repay/repaysave', '还款凭证缺失', $loan_id, $condition);
            return $this->redirect('/borrow/loan');
        }
        $condition['status'] = 3;
        $loan_result = $loan_repay->save_repay($condition);
        if (!$loan_result) {
            $transaction->rollBack();
            Logger::dayLog('weixin/repay/repaysave', '还款记录更新失败', $loan_id, $condition, $loan_result);
            return $this->redirect('/borrow/loan');
        }
        //修改借款记录的状态为11
        $status = 11;
        $ret = $o_user_loan->changeStatus($status);
        if (!$ret) {
            $transaction->rollBack();
            Logger::dayLog('weixin/repay/repaysave', '借款记录=》11更新失败', $loan_id);
            return $this->redirect('/borrow/loan');
        }
        //优惠卷使用
        if (!empty($coupon_id)) {
            $coupon_result = $this->couponUse($user_id, $loan_id, $coupon_id, $loan_repay->id, 0, $coupon_val, $repay_status = -1);
            if (!$coupon_result) {
                $transaction->rollBack();
                Logger::dayLog('weixin/repay/repaysave', '还款优惠卷记录失败', $loan_id, $coupon_result);
                return $this->redirect('/borrow/loan');
            }
        }

        $transaction->commit();
        return $this->redirect('/new/repay/verify?loan_id=' . $loan_id);
    }

    /**
     * 优惠卷使用保存
     * @param $userId
     * @param $loan_id
     * @param $couponId
     * @param $repay_id
     * @param $repay_amount
     * @param $couponVal
     * @return bool
     * @author 王新龙
     * @date 2018/7/25 9:42
     */
    private function couponUse($userId, $loan_id, $couponId, $repay_id, $repay_amount, $couponVal, $repay_status = 0) {
        if (empty($couponId) || empty($couponVal)) {
            return false;
        }
        $condition = [
            'user_id' => (int)$userId,
            'loan_id' => (int)$loan_id,
            'discount_id' => (int)$couponId,
            'repay_id' => (int)$repay_id,
            'repay_amount' => $repay_amount,
            'repay_status' => 0,
            'coupon_amount' => $couponVal,
            'repay_status' => $repay_status
        ];
        $result = (new RepayCouponUse())->addRecord($condition);
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * 判断子订单是否可点击
     * @param $bill_status
     * @return int|null
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

}

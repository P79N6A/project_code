<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 17:00
 */
namespace app\modules\newdev\controllers;
use app\commonapi\Logger;
use app\models\news\Fraudmetrix_return_info;
use app\models\news\User;
use app\commonapi\Common;
use app\models\news\Common as Common2;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\White_list;
use app\models\news\Coupon_use;
use app\models\news\User_label;
use Yii;
class CreditloanController extends NewdevController
{
    public $enableCsrfValidation = false;
    public function actionSecond()
    {
        $this->layout = "loan";
        $post_data = $this->post();
        $userinfo = $this->getUser();
        //判断用户是否是黑名单用户
        if ($userinfo['status'] == 5) {
            //如果是黑名单用户则直接跳转到黑名单用户页面
            return $this->redirect('/new/account/black');
        }
        //格式数据
        $desc = isset($post_data['desc']) ? $post_data['desc'] : $this->getCookieVal('loan_desc');
        $days = isset($post_data['day']) ? $post_data['day'] : $this->getCookieVal('loan_days');
        $amount = isset($post_data['amount']) ? $post_data['amount'] : $this->getCookieVal('loan_amount');
        $coupon_id = isset($post_data['coupon_id']) ? $post_data['coupon_id'] : $this->getCookieVal('coupon_id');
        $coupon_amount = isset($post_data['coupon_amount']) ? $post_data['coupon_amount'] : $this->getCookieVal('coupon_amount');
        //把借款信息存到cookie里
        if (isset($_POST['desc'])) {
            $this->setCookieVal('loan_desc', $desc);
        }
        if (isset($_POST['day'])) {
            $this->setCookieVal('loan_days', $days);
        }
        if (isset($_POST['amount'])) {
            $this->setCookieVal('loan_amount', $amount);
        }
        if (isset($_POST['coupon_id'])) {
            $this->setCookieVal('coupon_id', $coupon_id);
        }
        if (isset($_POST['coupon_amount'])) {
            $this->setCookieVal('coupon_amount', $coupon_amount);
        }
        $this->setCookieVal('business_type', 1);
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('loan', 'loan_button', $ip, 'weixin', $userinfo->user_id);
        /*         * *************记录访问日志end******************* */
        //用户验证
        $order = $userinfo->getPerfectOrder($userinfo->user_id, 4, 1);
        $orderInfo = (new Common2())->create3Des(json_encode($order, true));
        if($order['nextPage']){
            if(strstr($order['nextPage'], '?') ){
                $nextPage = $order['nextPage'].'&orderinfo='.urlencode($orderInfo);
            }else{
                $nextPage = $order['nextPage'].'?orderinfo='.urlencode($orderInfo);
            }
            return $this->redirect($nextPage);
        }
        $loan_no_keys = $userinfo->user_id . "_loan_no";
        $loan_no = Yii::$app->redis->get($loan_no_keys);
        Logger::errorLog(print_r($userinfo->user_id . '------' . $loan_no, true), 'loan_no_loan');
        $user_loan = new User_loan();
        $where = [
            'user_id' => $userinfo->user_id,
            'status' => 1,
            'default_bank' => 1,
            'type' => 0
        ];
        $userbank = User_bank::find()->where($where)->one();
        //计算
        $day_rate = $user_loan->fee;
        //利息
        $interest_fee = round($amount * $day_rate * $days, 2);
        //服务费
        $withdraw_fee = round($amount * $user_loan->with_fee);
        //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
        $coupon_amount = $coupon_amount?$coupon_amount:0;
        if ($interest_fee < $coupon_amount) {
            $coupon_amount = $interest_fee;
        }
        //是否为系统指定后置用户
        $charge = (new User_label())->isChargeUser($userinfo->mobile);
        //到期应还
        if($charge == false){
             $repay_amount = $amount + $interest_fee - $coupon_amount;
             $amount_due = $amount - $withdraw_fee;
        }else{
             $repay_amount = $amount + $interest_fee - $coupon_amount + $withdraw_fee;
            $amount_due = $amount;
        }
        $count = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1, 'type' => 0])->count();
        $bank_count = User_bank::find()->where(['user_id' => $userinfo->user_id, 'status' => 1])->count();
        $this->getView()->title = "借款确认";

        //是否只有一张卡并且被限制
        $flag = 1;
        if ($count > 1) {
            $user_bankinfo1 = (new User_bank())->limitCardsSort($userinfo->user_id, 0);
        }
        if ($count == 1) {
            $user_bankinfo1 = (new User_bank())->limitCardsSort($userinfo->user_id, 0);
            if ($user_bankinfo1[0]['sign'] == 1) {
                $flag = 2;
            }
        }
        $jsinfo = $this->getWxParam();
        return $this->render(
            'confirm', [
            'bank_count' => $bank_count,
            'flag' => $flag,
            'desc' => $desc,
            'days' => $days,
            'amount' => $amount,
            'coupon_id' => $coupon_id,
            'coupon_amount' => $coupon_amount,
            'repay_amount' => $repay_amount,
            'userbank' => $userbank,
            'withdraw_fee' => $withdraw_fee,
            'interest_fee' => $interest_fee,
            'user_bankinfo1' => $user_bankinfo1,
            'amount_due' => $amount_due,
            'jsinfo'=>$jsinfo,
            'orderinfo'=>$orderInfo,
            'csrf' => $this->getCsrf(),

        ]);
    }

    public function actionConfirm()
    {
        $data = $this->post();
        $ip = Common::get_client_ip();
        if(isset($_POST) && empty($_POST)){
            $resultArr = array('ret' => '3', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        }
        $userinfo = $this->getUser();
        //判断是否存在驳回订单
        $loan_info = new User_loan();
        $judgment = $loan_info->LoanJudgment($userinfo->user_id);
        if (!$judgment) {
            $resultArr = array('ret' => '7', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        }
        if ($userinfo->status != 3) {
            return $this->redirect('/new/account/distribute?type=1&from=nameauth');
        }
        /*             * *************记录访问日志beigin******************* */
        $result_log = Common::saveLog('loan', 'loan_confirm_button', $ip, 'weixin', $userinfo->user_id);
        /*             * *************记录访问日志end******************* */
        //判断用户是否是黑名单用户
        if ($userinfo['status'] == 5) {
            //如果是黑名单用户则直接跳转到黑名单用户页面
            $resultArr = array('ret' => '6', 'url' => '/new/account/black');
            echo json_encode($resultArr);
            exit;
        }
        //判断用户是否有借款
        $User_loan = (new User_loan())->getHaveinLoan($userinfo->user_id);
        if(!empty($User_loan)){
            return $this->redirect("/new/loan/showloan?loan_id=".$User_loan);
        }
        if ($userinfo->status == '4') {
            $resultArr = array('ret' => '5', 'url' => '/new/account/distribute?type=1&from=nameauth');
            echo json_encode($resultArr);
            exit;
        }
        $status = 5;
        $time = date('Y-m-d H:i:s');
        $user_id = $userinfo->user_id;
        $desc = $data['desc'];
        $days = $data['days'];
        $amount = $data['amount'];
        $coupon_id = $data['coupon_id'];
        $coupon_amount = $data['coupon_amount'];
        $bank_id = $data['bank_id'];
        $recharge_amount = 0;
        $type = 2;
        $credit_amount = 0;
        $day_rate = $loan_info->fee;
        $interest_fee = round($amount * $day_rate * $days, 2);
        $withdraw_fee = round($amount * $loan_info->with_fee);;
        $loanModel = new User_loan();
        $loan_no_keys = $user_id . "_loan_no";
        $loan_no = Yii::$app->redis->get($loan_no_keys);
        //是否为系统指定后置用户
        $charge = (new User_label())->isChargeUser($userinfo->mobile);
        if($charge == false){
            $is_calculation = 1;
        }else{
            $is_calculation = 0;
        }
        $condition = array(
            'user_id' => $user_id,
            'loan_no' => $loan_no,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => $credit_amount,
            'recharge_amount' => $recharge_amount,
            'current_amount' => $amount,
            'days' => $days,
            'type' => $type,
            'status' => $status,
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
            'desc' => $desc,
            'bank_id' => $bank_id,
            'withdraw_time' => $time,
            'is_calculation' => $is_calculation,
        );
        if (empty($loan_no)) {
            $resultArr = array('ret' => '3', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        }
        $whiteModel = new White_list();
        $white = $whiteModel->isWhiteList($userinfo->user_id);
        if ($white) {
            $condition['final_score'] = -1;
        }
        if (!empty($coupon_id)) {
            $condition['coupon_amount'] = $coupon_amount;
        }
        $transaction = Yii::$app->db->beginTransaction();
        $loan_id = $loanModel->addUserLoan($condition);
        if (!$loan_id) {
            $transaction->rollBack();
            Yii::$app->redis->del($loan_no_keys);
            $resultArr = array('ret' => '3', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        }
        if (!$white) {
            $loan = $loanModel->findOne($loan_id);
            $ret = $loanModel->saveFinalScore($loan);
        }
        Yii::$app->redis->del($loan_no_keys);
        if (!empty($coupon_id)) {
            //记录优惠券使用情况
            $loan_flows  = new Coupon_use();
            $ret_coup_use = $loan_flows->addCouponUse($user_id,$coupon_id,$loan_id,$time,1);
        }
        //判断是否有默认卡
        $ret_user_bank = new User_bank();
        $user_bank = $ret_user_bank->_saveuUerbankid($user_id,$bank_id);
        $loanextendModel = new User_loan_extend();
//        $success_num_where = [
//            'user_id' => $user_id,
//            'business_type' => [1,4],
//            'status' => 8
//        ];
//        $success_num = User_loan::find()->where($success_num_where)->count();
        $success_num = (new User())->isRepeatUser($user_id);
        $extend = array(
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'outmoney' => 0,
            'payment_channel' => 0,
            'userIp' => $ip,
            'extend_type' => '1',
            'success_num' => $success_num,
            'status' => 'INIT',
        );
        $extendId = $loanextendModel->addList($extend);
        if ($extendId) {
            $transaction->commit();
            $resultArr = array('ret' => '1', 'url' => '/new/loanrecord/creditdetails?loan_id=' . $loan_id);
            echo json_encode($resultArr);
            exit;
        } else {
            $transaction->rollBack();
            $resultArr = array('ret' => '3', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        }
    }
    /**
     * 获取nextPage
     * @param int $user_id
     * @return string
     */
    private function nextPage($user_id) {
        $UserModel = new User();
        $order = $UserModel->getPerfectOrder($user_id, 4, 1);
        $nextPage = $order['nextPage'];
        $orderJson = (new Common2())->create3Des(json_encode($order, true));
        if($nextPage != ''){
            $str = substr($nextPage , strrpos($nextPage , '/') + 1);
            if(strpos($str, "?")){
                return $nextPage . '&orderinfo=' . urlencode($orderJson);
            }else{
                return $nextPage . '?orderinfo=' . urlencode($orderJson);
            }
        }else{
            return false;
        }
    }

    //设置cookie值
    public function setCookieVal( $key , $val ) {
        setcookie( $key , $val , time()+3600*24) ;
    }

    //获取cookie值
    public function getCookieVal( $key ) {
        if( isset( $_COOKIE[$key] ) && !empty( $_COOKIE[$key] ) ){
            return $_COOKIE[$key] ;
        }else{
            return '';
        }
    }

    /**
     * 获取跳转地址
     * @param $orderinfo
     * @param $current_code
     * @param int $end
     * @param int $type
     *
     *
     * @return string
     */
    protected function nextUrl($orderinfo, $current_code, $end=0){
        if($orderinfo == ''){
            exit;
        }
        $nextpage = $this->getNextpage($orderinfo, $current_code, $end);
        $nextUrl = $nextpage.'?orderinfo='.urlencode($orderinfo);

        return $nextUrl;
    }
    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf(){
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

}
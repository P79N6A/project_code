<?php

namespace app\modules\api\controllers\controllers312;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Keywords;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Fraudmetrix_return_info;
use app\models\news\User;
use app\models\news\User_auth;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_extend;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_password;
use app\models\news\User_rate;
use app\models\news\White_list;
use app\models\news\No_repeat;
use app\models\Flow;
use app\models\news\Bankbill;
use app\models\service\GoodsService;
use app\models\service\UserloanService;
use app\modules\api\common\ApiController;
use app\models\news\User_label;
use Yii;

class GetuserloanController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $amount = Yii::$app->request->post('amount');
        $day_key = Yii::$app->request->post('day_key');
        $term = Yii::$app->request->post('term');
        $desc = Yii::$app->request->post('desc');
        $business_type = empty(Yii::$app->request->post('business_type')) ? 1 : Yii::$app->request->post('business_type');
        $coupon_id = !empty(Yii::$app->request->post('coupon_id')) ? Yii::$app->request->post('coupon_id') : '';

        if (empty($version) || empty($user_id) || empty($amount) || empty($day_key) || empty($term)|| empty($desc)) {
            exit($this->returnBack('99994'));
        }
        $days_arr = (new User_loan())->getMaxLoanDays($user_id);
        $days = !empty($days_arr[0])?$days_arr[0]:'56';
        $days_list=[
            ['day_key'=>1,'days'=>$days],
        ];
        if(!empty($days_arr)){
            $days_list = [];
            foreach ($days_arr as $key => $val){
                $days_list[] = [
                    'day_key' => $key + 1,
                    'days' => $val,
                ];
            }
        }
        if(!empty($day_key)){
            foreach ($days_list as $item){
                if($item['day_key'] == $day_key){
                    $days = $item['days'];
                }
            }
        }

        $userModel = new User();
        $userLoanService = new UserloanService();
        $userinfo = $userModel->getUserinfoByUserId($user_id);
        if (!$userinfo) {
            exit($this->returnBack('10001'));
        }
        //获取用户利率及日息
//        $rate = (new User_rate())->getRate($user_id);
//        if(!$rate){
//            exit($this->returnBack('10100'));
//        }
        //优惠卷列表
        $coupon = new Coupon_list();
        //拉取面向全部用户类型的有效优惠券
        $couponlist_pull = $coupon->pullCoupon($userinfo->mobile);
 	    //只获取借款优惠券
        $coupon_type = [1,2,3,4];
        $couponlist = $coupon->getValidList($userinfo->mobile, $term,$coupon_type);
        //获取用户出款卡
        $bank = (new User_bank())->limitCardsSort($userinfo->user_id, 0);
        $bankArr = empty($bank) ? [] : $bank[0];
//        //服务费
//        $withdraw = $amount * $rate['withdraw'][$days];
//        //利息
//        if($term == 1){
//            $interest = $amount * $rate['interest'][$days] * $days;
//        }else{
//            $goodsService = new GoodsService();
//            $interest = $goodsService->getInstallmentInterestFee($amount, $days, $term, $rate['interest'][$days]);
//        }
        $oUserCredit=(new User_credit())->getUserCreditByUserId($user_id);
        //1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期;7：存在未支付的白条
        if(!empty($oUserCredit)){
            $interest=isset($oUserCredit->interest_rate) ? ($oUserCredit->interest_rate)/100 : 0.00098;
        }else{
            $result = (new Apihttp())->getUserCredit(['mobile' => $userinfo->mobile]);
            $result_subject=json_decode($result['result_subject'],true);
            if(!empty($result_subject)){
                $interest=isset($result_subject['INTEREST_RATE']) ? ($result_subject['INTEREST_RATE']) : 0.00098;
            }else{
                $interest=0.00098;
            }
        }
        $loanfee = (new User_loan())->loan_Fee_rate_new($amount,$interest,$days,$userinfo->user_id, $term,1);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        $newinterest_fee= (new User_loan())->loan_Fee_rate_new($amount,$interest,$days,$userinfo->user_id, $term,2)['interest_fee'];
        //到手金额
        $getamount = (new UserloanService())->getGetMoney($userinfo, $amount, $withdraw_fee,$term);
        ;
        //还款计划
        $repay_plan = $userLoanService->getReayPlan($userinfo, $amount, $term, $days, $coupon_id, sprintf('%.2f', $withdraw_fee), sprintf('%.2f', ceil($newinterest_fee * 100) / 100));
        $cj= bcsub($repay_plan[0]['repay_amount'],$interest_fee,2);
        $compre_interest= bcsub($cj,$amount,2);
        $couponModel = (new Coupon_list())->getCouponById($coupon_id);
        if(!empty($couponModel)){
            $coupon_amount = $couponModel['val'];
            $jianmian = sprintf('%.2f', ceil($newinterest_fee * 100) / 100) - $coupon_amount > 0 ? $coupon_amount : sprintf('%.2f', ceil($newinterest_fee * 100) / 100);
            if ($jianmian > 0) {
                $compre_interest=bcadd($compre_interest,$jianmian,2);
            }
        }
        $array = $this->reback(sprintf('%.2f',$amount), $days, $term, $business_type, sprintf('%.2f',$withdraw_fee), sprintf('%.2f',ceil($interest_fee * 100) / 100),$compre_interest, sprintf('%.2f',ceil($getamount * 100) / 100), $repay_plan, $couponlist, $bankArr, $desc);
        exit($this->returnBack('0000', $array));
    }

    private function reback($amount, $days, $term, $business_type, $withdraw, $interest_fee, $compre_interest,$getamount, $repay_plan, $couponlist, $bank, $desc) {
        $array['amount'] = $amount;
        $array['days'] = $days;
        $array['term'] = $term;
        $array['business_type'] = $business_type;
        $array['interest'] = (string) $interest_fee;
        $array['compre_interest'] = (string) $compre_interest;
        $array['insurance'] = (string) $withdraw;
        $array['repayplan_open'] = 1;//还款计划开关1：开启2：关闭
        $array['contact_url'] = '/borrow/agreeloan/contactlist';
        $array['getamount'] = $getamount;
        $array['repay_plan'] = $repay_plan;
        if (!empty($couponlist)) {
            foreach ($couponlist as $key => $val) {
                $array['coupon_list'][$key]['id'] = $val['id'];
                $array['coupon_list'][$key]['val'] = $val['val'];
                $array['coupon_list'][$key]['title'] = $val['title'];
                $array['coupon_list'][$key]['end_date'] = date('Y年m月d日', strtotime($val['end_date'])-24*3600);
            }
        } else {
            $array['coupon_list'] = array();
        }
        $array['bank'] = null;
        if(!empty($bank)){
            $array['bank'] = [
                'bank_id' => $bank['id'],
                'type' =>  trim($bank['bank_name'], " "),
                'card' => substr($bank['card'], strlen($bank['card']) - 4, 4),
                'bank_abbr' => $bank['bank_abbr'],
                'bank_icon_url' => $this->getImageUrl($bank['bank_abbr']),
                'default_card' => $bank['default_bank'] == 0 ? 2 : $bank['default_bank'],
            ];
        }
        $array['desc'] = $desc;

        return $array;
    }

    private function getImageUrl($abbr) {
        $bankAbbr = [
            'ABC',
            'BCCB',
            'BCM',
            'BOC',
            'CCB',
            'CEB',
            'CIB',
            'CMB',
            'CMBC',
            'ECITIC',
            'GDB',
            'HXB',
            'ICBC',
            'PAB',
            'PSBC',
            'SPDB'
        ];
        if (!empty($abbr) && in_array($abbr, $bankAbbr)) {
            $abbr_url = $abbr;
        } else {
            $abbr_url = 'ALL';
        }
        return "http://weixin.xianhuahua.com/images/bank_logo/" . $abbr_url . ".png";
    }
}

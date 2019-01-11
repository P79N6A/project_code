<?php

namespace app\modules\api\controllers\controllers311;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\models\news\Coupon_list;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_loan;
use app\models\news\User_rate;
use app\models\service\GoodsService;
use app\models\service\UserloanService;
use app\modules\api\common\ApiController;
use Yii;

class GetuserloanoneController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $amount = Yii::$app->request->post('amount');
        $term = Yii::$app->request->post('term');
        $day_key=Yii::$app->request->post('day_key');
        $business_type = empty(Yii::$app->request->post('business_type')) ? 1 : Yii::$app->request->post('business_type');
        $coupon_id = !empty(Yii::$app->request->post('coupon_id')) ? Yii::$app->request->post('coupon_id') : '';
        if (empty($version) || empty($user_id) ||  empty($term)) {
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

        if(empty($amount)){
            $amount=0;
        }
        $desc= Keywords::getAppLoanDesc();

        $userModel = new User();
        $userLoanService = new UserloanService();
        $userinfo = $userModel->getUserinfoByUserId($user_id);
        if (!$userinfo) {
            exit($this->returnBack('10001'));
        }
        //获取评测失效时间
        $oUserCredit=(new User_credit())->getUserCreditByUserId($user_id);
        //1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期;7：存在未支付的白条
        if(!empty($oUserCredit)){
            $invalid_time=$oUserCredit->invalid_time;
            $interest=isset($oUserCredit->interest_rate) ? ($oUserCredit->interest_rate)/100 : 0.00098;
        }else{
            $result = (new Apihttp())->getUserCredit(['mobile' => $userinfo->mobile]);
            if(in_array($result['user_credit_status'],[2,4,5])){
                $invalid_time = $result['credit_invalid_time'];
            }
            $result_subject=json_decode($result['result_subject'],true);
            if(!empty($result_subject)){
                $interest=isset($result_subject['INTEREST_RATE']) ? ($result_subject['INTEREST_RATE']) : 0.00098;
            }else{
                $interest=0.00098;
            }
        }

        if(!empty($invalid_time)){
            $time_diff=strtotime($invalid_time)-time();
           if ($time_diff>0){
               $syhour=ceil($time_diff/3600);
           }else{
               $syhour=0;
           }
        }else{
            $invalid_time='';
            $syhour=0;
        }
//        //获取用户利率及日息
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
        //服务费
//        $withdraw = $amount * $rate['withdraw'][$days];
        //利息
//        if($term == 1){
//            $interest = $amount * $rate['interest'][$days] * $days;
//        }else{
//            $goodsService = new GoodsService();
//            $interest = $goodsService->getInstallmentInterestFee($amount, $days, $term, $rate['interest'][$days]);
//        }
//
        $loanfee = (new User_loan())->loan_Fee_rate_new($amount,$interest,$days,$userinfo->user_id, $term,2);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        $repay_plan = $userLoanService->getReayPlan($userinfo, $amount, $term, $days, $coupon_id, sprintf('%.2f', $withdraw_fee), sprintf('%.2f', ceil($interest_fee * 100) / 100));

        $array = $this->reback(sprintf('%.2f',$amount), $days, $days_list, $invalid_time, $syhour, $term, $business_type,$repay_plan, $couponlist, $bankArr, $desc);
        exit($this->returnBack('0000', $array));
    }

    private function reback($amount, $days, $days_list, $invalid_time, $syhour, $term, $business_type,$repay_plan, $couponlist, $bank, $desc) {
        $array['amount'] = $amount;
        $array['min_amount'] = $this->getMinAmount($days);//最小可借金额
        $array['days'] = $days;
        $array['days_list']=$days_list;
        $array['term'] = $term;
        $array['business_type'] = $business_type;
        $array['repayplan_open'] = 1;//还款计划开关1：开启2：关闭
        $array['contact_url'] = Yii::$app->request->hostInfo .'/borrow/agreeloan/contactlist';
        $array['invalid_time'] = $invalid_time;
        $array['syhour'] = $syhour;
        $array['repay_plan'] = $repay_plan;
        $array['coupon_num']=count($couponlist);//优惠券数量
        $array['bank'] = null;
        if(!empty($bank)){
            $array['bank'] = [
                'bank_id' => $bank['id'],
                'type' => !empty($bank['bank_name']) ? trim($bank['bank_name'], " ") : '银行卡',
                'card' => substr($bank['card'], strlen($bank['card']) - 4, 4),
                'bank_abbr' => !empty($bank['bank_abbr']) ? $bank['bank_abbr'] : 'ICON' ,
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

    private function getMinAmount($days){
        if(empty($days)){
            return 1000;
        }
        $amounts = Keywords::getMinAmounts();
        if(isset($amounts[$days]) && !empty($amounts[$days])){
            return $amounts[$days];
        }
        return 1000;
    }
}

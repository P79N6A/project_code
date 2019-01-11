<?php

namespace app\modules\api\controllers\controllers314;

use app\models\news\Coupon_list;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_loan;
use app\models\service\StageService;
use app\models\service\UserloanService;
use app\modules\api\common\ApiController;
use app\commonapi\Logger;
use Yii;

class GetuserloanController extends ApiController {
    public $enableCsrfValidation = FALSE;
    private $o_user = NULL;
    private $o_user_credit = NULL;
    private $business_type = 1;//1信用借款 5借款分期
    private $interest = '';
    private $period = 1;//分期，默认单期
    private $coupon_list = [];
    private $bank = [];
    private $amount = 0;
    private $get_money = 0;
    private $compre_interest = 0;
    private $interest_fee = 0;
    private $withdraw_fee = 0;
    private $days = 0;
    private $days_show = '';
    private $desc = '';
    private $repay_plan = [];
    private $is_installment = FALSE;//true 是分期；false 不是分期

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $amount = Yii::$app->request->post('amount');
        $day_key = Yii::$app->request->post('day_key');
        $term = Yii::$app->request->post('term');
        $desc = Yii::$app->request->post('desc');
        $coupon_id = Yii::$app->request->post('coupon_id', '');

        if (empty($version) || empty($user_id) || empty($amount) || empty($day_key) || empty($term) || empty($desc)) {
            exit($this->returnBack('99994'));
        }

        $this->o_user = (new User())->getById($user_id);
        if (empty($this->o_user)) {
            exit($this->returnBack('10214'));
        }
        $this->desc = $desc;

        //获取评测信息
        $this->getCredit();

        //获取天数
        $this->getDays($day_key);//223728735

        //获取优惠卷
        $this->getCoupon();

        //获取银行卡
        $this->getBank();

        //获取还款计划
        $this->getRepayPlan($coupon_id);

        $array = $this->reback();
        exit($this->returnBack('0000', $array));
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

    private function getCredit() {
        $o_user_credit = (new User_credit())->checkCanCredit($this->o_user);
        if ($o_user_credit === FALSE) {
            exit($this->returnBack('10233'));
        }
        //评测信息
        $this->o_user_credit = $o_user_credit;
        //金额
        $this->amount = $o_user_credit->amount;
        //天数
        $this->days = $o_user_credit->days;
        //日息费
        $this->interest = !empty($o_user_credit->interest_rate) ? ($o_user_credit->interest_rate) / 100 : 0.00098;
        //是否分期
        $this->is_installment = $o_user_credit->installment_result == 1 ? TRUE : FALSE;
        //借款分期
        if ($o_user_credit->installment_result == 1) {
            $this->business_type = 5;
        }
        //期数
        $this->period = !empty($o_user_credit->period) ? $o_user_credit->period : 1;
    }

    private function getDays($day_key) {
        if (!$this->is_installment) {
            $days_list = [['day_key' => 1, 'date_show' => $this->days . '天x' . $this->period . '期', 'days' => $this->days]];
        } else {
            $days_arr = (new User_loan())->getMaxLoanDays($this->o_user->user_id);
            $days = !empty($days_arr[0]) ? $days_arr[0] : '56';
            $days_list = [
                ['day_key' => 1, 'date_show' => $days . '天x' . $this->period . '期', 'days' => $days],
            ];
            if (!empty($days_arr)) {
                $days_list = [];
                foreach ($days_arr as $key => $val) {
                    $days_list[] = [
                        'day_key' => $key + 1,
                        'date_show' => $days . '天x' . $this->period . '期',
                        'days' => $val,
                    ];
                }
            }
        }
        foreach ($days_list as $item) {
            if ($item['day_key'] == $day_key) {
                $this->days = $item['days'];
                $this->days_show = $item['date_show'];
            }
        }
        if (empty($this->days)) {
            exit($this->returnBack('99996', [], 'day_key,参数错误'));
        }
    }

    private function getCoupon() {
        $coupon = new Coupon_list();
        //拉取优惠卷
        $coupon->pullCoupon($this->o_user->mobile);
        //只获取借款优惠券
        $coupon_type = [1, 2, 3, 4];
        $couponlist = $coupon->getValidList($this->o_user->mobile, $this->period, $coupon_type, $this->is_installment);
        if (!empty($couponlist)) {
            $coupon_list = [];
            foreach ($couponlist as $key => $val) {
                $coupon_list[$key]['id'] = $val['id'];
                $coupon_list[$key]['val'] = $val['val'];
                $coupon_list[$key]['title'] = $val['title'];
                $coupon_list[$key]['end_date'] = date('Y年m月d日', strtotime($val['end_date']) - 24 * 3600);
            }
            $this->coupon_list = $coupon_list;
        }
    }

    private function getBank() {
        $bank_arr = (new User_bank())->limitCardsSort($this->o_user->user_id, 0);
        $bank = empty($bank_arr) ? [] : $bank_arr[0];
        if (!empty($bank)) {
            $this->bank = [
                'bank_id' => $bank['id'],
                'type' => !empty($bank['bank_name']) ? trim($bank['bank_name'], " ") : '银行卡',
                'card' => substr($bank['card'], strlen($bank['card']) - 4, 4),
                'bank_abbr' => !empty($bank['bank_abbr']) ? $bank['bank_abbr'] : 'ICON',
                'bank_icon_url' => $this->getImageUrl($bank['bank_abbr']),
                'default_card' => $bank['default_bank'] == 0 ? 2 : $bank['default_bank'],
            ];
        }
    }

    private function getRepayPlan($coupon_id) {
        $this->repay_plan = (new StageService())->getReayPlan($this->o_user, $this->o_user_credit, $this->amount, $this->days, $this->period, $coupon_id, $this->is_installment);

        $loanfee = (new User_loan())->loan_Fee_rate_new($this->amount, $this->interest, $this->days, $this->o_user->user_id, $this->period, 1, $this->is_installment);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        //到手金额
        $this->get_money = (new UserloanService())->getGetMoney($this->o_user, $this->amount, $withdraw_fee, $this->period);
        $o_coupon = (new Coupon_list())->getCouponById($coupon_id);
        $coupon_amount = 0;
        if (!empty($o_coupon) && !$this->is_installment) {
            $coupon_amount = $o_coupon['val'];
            $coupon_amount = sprintf('%.2f', ceil($interest_fee * 100) / 100) - $coupon_amount > 0 ? $coupon_amount : sprintf('%.2f', ceil($interest_fee * 100) / 100);
        }
        $this->interest_fee = sprintf('%.2f', $interest_fee);//利息
        $this->withdraw_fee = sprintf('%.2f', $withdraw_fee);//手续费
        //综合利息（监管进场拆分利息为综合费用+综合利息）
        $this->compre_interest = (new StageService())->getSuperviseInterest($this->amount, $this->days, $this->period, $interest_fee, $coupon_amount, $this->repay_plan, $this->is_installment);
    }

    private function reback() {
        $array['interest'] = (string)$this->interest_fee;
        $array['insurance'] = (string)$this->withdraw_fee;
        $array['amount'] = sprintf('%.2f', $this->amount);
        $array['days'] = $this->days;
        $array['days_show'] = $this->days_show;
        $array['term'] = $this->period;
        $array['is_installment'] = $this->is_installment;
        $array['getamount'] = sprintf('%.2f', $this->get_money);//到手金额
        $array['compre_interest'] = sprintf('%.2f', $this->compre_interest);//综合利息
        $array['business_type'] = $this->business_type;
        $array['repay_plan'] = $this->repay_plan;
        $array['coupon_num'] = $this->coupon_list;//优惠券列表
        $array['bank'] = $this->bank;
        $array['desc'] = $this->desc;//借款用途
        $array['repayplan_open'] = 1;//还款计划开关1：开启2：关闭
        $array['contact_url'] = Yii::$app->request->hostInfo . '/borrow/agreeloan/contactlist';//借款协议
        return $array;
    }
}

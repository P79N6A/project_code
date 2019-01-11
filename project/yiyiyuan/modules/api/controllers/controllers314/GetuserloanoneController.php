<?php

namespace app\modules\api\controllers\controllers314;

use app\commonapi\Keywords;
use app\models\news\Coupon_list;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_loan;
use app\models\service\StageService;
use app\models\service\UserloanService;
use app\modules\api\common\ApiController;
use Yii;

class GetuserloanoneController extends ApiController {
    public $enableCsrfValidation = FALSE;
    private $o_user = NULL;
    private $o_user_credit = NULL;
    private $business_type = 1;//1信用借款 5借款分期
    private $invalid_time = '';
    private $interest = '';
    private $syhour = 0;
    private $period = 1;//分期，默认单期
    private $coupon_list = [];
    private $bank = [];
    private $amount = 0;
    private $days = 0;
    private $days_list = [];
    private $repay_plan = [];
    private $is_installment = FALSE;//true 是分期；false 不是分期

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
//        $amount = Yii::$app->request->post('amount');//金额
//        $term = Yii::$app->request->post('term');//期数
//        $day_key=Yii::$app->request->post('day_key');//借款周期
//        $business_type = Yii::$app->request->post('business_type',1);//借款类型
        $coupon_id = Yii::$app->request->post('coupon_id', '');
        if (empty($version) || empty($user_id)) {
            exit($this->returnBack('99994'));
        }
        $this->o_user = (new User())->getById($user_id);
        if (empty($this->o_user)) {
            exit($this->returnBack('10214'));
        }

        //获取评测信息
        $this->getCredit();

        //获取天数
        $this->getDays();

        //获取优惠卷列表
        $this->getCoupon();

        //获取银行卡列表
        $this->getBank();

        //获取还款计划
        $this->getRepayPlan($coupon_id);

        $array = $this->reback();
        exit($this->returnBack('0000', $array));
    }

    private function reback() {
        $array['amount'] = $this->amount;
        $array['min_amount'] = $this->amount;//$this->getMinAmount($this->days)//最小可借金额
        $array['days'] = $this->days;
        $array['days_list'] = $this->days_list;
        $array['repay_plan'] = $this->repay_plan;
        $array['invalid_time'] = $this->invalid_time;
        $array['term'] = $this->period;
        $array['is_installment'] = $this->is_installment;
        $array['syhour'] = $this->syhour;
        $array['business_type'] = $this->business_type;
        $array['repayplan_open'] = 1;//还款计划开关1：开启2：关闭
        $array['coupon_num'] = count($this->coupon_list);//优惠券数量
        $array['bank'] = $this->bank;
        $array['desc'] = Keywords::getAppLoanDesc();//借款用途
        $array['contact_url'] = Yii::$app->request->hostInfo . '/borrow/agreeloan/contactlist';//借款协议
        return $array;
    }

    //获取评测信息
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
        //失效时间
        $this->invalid_time = $o_user_credit->invalid_time;
        $time_diff = strtotime($this->invalid_time) - time();
        if ($time_diff > 0) {
            //失效小时
            $this->syhour = ceil($time_diff / 3600);
        }
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

    //获取优惠卷列表
    private function getCoupon() {
        $coupon = new Coupon_list();
        //拉取优惠卷
        $coupon->pullCoupon($this->o_user->mobile);
        //只获取借款优惠券
        $coupon_type = [1, 2, 3, 4];
        $this->coupon_list = $coupon->getValidList($this->o_user->mobile, $this->period, $coupon_type, $this->is_installment);
    }

    //获取银行卡列表
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

    private function getDays() {
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
        $this->days_list = $days_list;
    }

    private function getRepayPlan($coupon_id) {
        $this->repay_plan = (new StageService())->getReayPlan($this->o_user, $this->o_user_credit, $this->amount, $this->days, $this->period, $coupon_id, $this->is_installment);
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

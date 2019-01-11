<?php
namespace app\modules\api\controllers\controllers310;

use app\models\news\Function_control;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use app\models\news\RepayCouponUse;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\Coupon_list;
use app\modules\api\common\ApiController;
use Yii;
use yii\helpers\ArrayHelper;

class RepayshowController extends ApiController
{
    public $enableCsrfValidation = false;
    private $alipayUrl = "/new/alipay/";
    private $alipayUrlNew = "/new/alipay/alipaynew";

    /**
     * 还款页面展示
     */
    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $loanId = Yii::$app->request->post('loan_id');
        $couponId = Yii::$app->request->post('coupon_id', 0);
        //请求参数不能为空
        if (empty($version) || empty($loanId)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        //查询借款是否存在
        $user_loan = (new User_loan())->getLoanById($loanId);
        if (!$user_loan) {
            $array = $this->returnBack('10052');
            echo $array;
            exit;
        }
        $userObj = (new User())->getUserinfoByUserId($user_loan->user_id);
        if (empty($userObj)) {
            exit($this->returnBack('10214'));
        }
        //拉取优惠卷
        (new Coupon_list())->pullCoupon($userObj->mobile);

        $array['coupon_id'] = 0;//优惠卷id
        $array['coupon_val'] = 0;//优惠卷金额
        $array['coupon_count'] = 0;//可用优惠卷张数
        $isCoupon = $this->chkCoupon($loanId);
        if (!empty($isCoupon)) {
            $couponlist = (new Coupon_list())->getValidList($userObj->mobile, $term = 1, $coupon_type = 5);
            $array['coupon_count'] = count($couponlist);
            $loanRepayList = (new Loan_repay)->getRepayByLoanId($loanId);
            if (!empty($loanRepayList)) {
                $array['coupon_count'] = 0;
            }
            if (!empty($couponId)) {
                $couponListObj = (new Coupon_list())->getByIdAndMobile($couponId, $userObj->mobile);
                if (empty($couponListObj)) {
                    exit($this->returnBack('10215'));
                }
                $repayCouponUseObj = (new RepayCouponUse())->getByDiscountId($couponId);
                if (!empty($repayCouponUseObj)) {
                    exit($this->returnBack('10217'));
                }
                $array['coupon_id'] = $couponListObj->id;
                $array['coupon_val'] = $couponListObj->val;
            }
            if(!empty($couponlist)){
                $is_repay= Loan_repay::find()->where(['user_id'=>$user_loan->user_id,'loan_id'=>$user_loan->loan_id,'status'=>1])->one();
                if(empty($is_repay)){
                    $MaxKey=$this->getArrayMax($couponlist,'val');
                    $array['maxcoupon_id'] = $couponlist[$MaxKey]['id'];
                    $array['maxcoupon_val'] = $couponlist[$MaxKey]['val'];
                }else{
                    $array['maxcoupon_id'] = '';
                    $array['maxcoupon_val'] = '';
                }
            }
        }

        //计算应还款金额
        $array['total_amount'] = sprintf('%.2f', (new User_loan())->getRepaymentAmount($user_loan));
        $array['amount'] = bcsub(sprintf('%.2f', (new User_loan())->getRepaymentAmount($user_loan)), $array['coupon_val'], 2);
        if($array['amount'] <= 0){
            $array['amount'] = 0;
        }

        //是否展开计划
        $array['is_show'] = 2;  //2 不展开  1：展开
        //判断是否可体内还款
        $payCg = (new Loan_repay())->payCg($user_loan);
        if ($payCg) {
            $is_support = 2;
        } else {
            $is_support = 1;
        }
        //获取支付方式
        $array['repay_method'] = $this->getPayMethod($user_loan, $is_support);
        if (!$array['repay_method']) {
            $array = $this->returnBack('99999');
            echo $array;
            exit;
        }
        //还款计划 判断是否是分期
        $business_type = [5, 6];
        if (in_array($user_loan['business_type'], $business_type)) { //分期
            //查询账单表状态！=8的记录 取还款时间
            $billInfo = (new GoodsBill())->getLatelyPhase($loanId);
            //最后还款时间
            $array['repay_date'] = date("Y年m月d日", strtotime($billInfo['end_time']) - 86400);
            //还款详情
            $repay_info = (new GoodsBill())->getRepaylist($loanId);
            if (!empty($repay_info)) {
                //获取每一期的应还款金额
                $amount = $user_loan->getLoanStagesRepay($returnArray = true, $actual = true);
                unset($amount['total_amount']);
                //组装还款计划
                foreach ($repay_info as $val) {
                    $array['repay_plan'][] = [
                        'amount' => isset($amount[$val['id']]) ? sprintf('%.2f', $amount[$val['id']]) : sprintf('%.2f', $val['current_amount']),
                        'status' => $val['bill_status'],
                        'days' => date("Y-m-d", strtotime($val['end_time']) - 86400),
                        'now_term' => $val['phase'],
                        'total_term' => $val['number'],
                        'is_click' => $this->getIsLoanClick($val['bill_status']),
                    ];
                }
            }
        } else { //没有分期
            //最后还款时间
            $array['repay_date'] = date("Y年m月d日", strtotime($user_loan['end_date']) - 86400);
            $array['repay_plan'][] = [
                'amount' => $array['amount'],
                'status' => $user_loan['status'],
                'days' => date("Y-m-d", strtotime($user_loan['end_date']) - 86400),
                'now_term' => 1,
                'total_term' => 1,
                'is_click' => $this->getIsLoanClick($user_loan['status']),
            ];
        }
        exit($this->returnBack('0000', $array));
    }

    /**
     * 获取支付方式
     */
    public function getPayMethod($loaninfo, $is_support)
    {
        $function_control_model = new Function_control();
        $alipay_info = $function_control_model->getPatmenthod([2, 5]);
        $wechatpay_info = $function_control_model->getPatmenthod([1, 6]);;
        $offline_info = $function_control_model->getPatmenthod([4]);
        if (!empty($alipay_info)) {
            $alipay_type = $alipay_info->type;
        } else {
            $alipay_type = 2;
        }

        $user_id = $loaninfo->user_id;
        $user_ids = array('9982', '3885272', '2910310');
        if (in_array($user_id, $user_ids)) {
            $alipay_type = 5;
        }
        $payArray = [
            [
                "is_open" => !empty($wechatpay_info) ? 1 : 2, //1:开启，2：关闭
                "is_support" => $is_support,//1:支持，2：不支持
                "repayment_type" => "wechatpay",
                "request_url" => ""
            ],
            [
                "is_open" => !empty($alipay_info) ? 1 : 2, //1:开启，2:关闭
                "is_support" => $is_support,//1:支持，2：不支持
                "repayment_type" => "alipay",
                "request_url" => $alipay_type == 2 ? $this->alipayUrl : $this->alipayUrlNew
            ],
            [//线下还款
                "is_open" => !empty($offline_info) ? 1 : 2, //1:开启，2：关闭
                "is_support" => 1,//1:支持，2：不支持
                "repayment_type" => "offline",
                "request_url" => ""
            ],
        ];
        //判断是否是分期  分期没有线下还款
        if (in_array($loaninfo['business_type'], [5, 6])) {
            $payArray = [
                [
                    "is_open" => !empty($wechatpay_info) ? 1 : 2, //1:开启，2：关闭
                    "is_support" => $is_support,//1:支持，2：不支持
                    "repayment_type" => "wechatpay",
                    "request_url" => ""
                ],
                [
                    "is_open" => !empty($alipay_info) ? 1 : 2, //1:开启，2:关闭
                    "is_support" => $is_support,//1:支持，2：不支持
                    "repayment_type" => "alipay",
                    "request_url" => $alipay_type == 2 ? $this->alipayUrl : $this->alipayUrlNew
                ],
//                    [//线下还款
//                    "is_open" => (!empty($offline_info) && $offline_info['status'] == 1) ? 1 : 2, //1:开启，2：关闭
//                    "repayment_type" => "offline",
//                    "request_url" => ""
//                ],
            ];
        }
        if (!empty($payArray)) {
            return $payArray;
        } else {
            return false;
        }
    }

    /**
     * 判断子订单是否可点击
     */
    public function getIsLoanClick($bill_status)
    {
        if (empty($bill_status)) {
            return NULL;
        }
        //已结清、逾期
        if (in_array($bill_status, [8, 12, 13])) {
            $is_click = 2;
        } else {
            $is_click = 1;
        }
        return $is_click;
    }

    //监测是否可以显示优惠卷
    private function chkCoupon($loanId)
    {
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

    private function getArrayMax($arr,$field){
        foreach ($arr as $k=>$v){
            $temp[]=$v[$field];
        }
        $MaxCouponVal= max($temp);
        foreach ($arr as $key=>$val){
            if ($val[$field]==$MaxCouponVal){;
                return $key;
            }
        }

    }
}

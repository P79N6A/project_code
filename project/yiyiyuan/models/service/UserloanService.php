<?php

namespace app\models\service;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\commonapi\policy\policyApi;
use app\models\news\BehaviorRecord;
use app\models\news\Common;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Fraudmetrix_return_info;
use app\models\news\GoodsBill;
use app\models\news\GoodsOrder;
use app\models\news\Insurance;
use app\models\news\Loan_repay;
use app\models\news\No_repeat;
use app\models\news\OverdueLoan;
use app\models\news\Payaccount;
use app\models\news\PayAccountError;
use app\models\news\Renew_amount;
use app\models\news\Renewal_payment_record;
use app\models\news\RenewalInspect;
use app\models\news\TemQuota;
use app\models\news\Term;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit_qj;
use app\models\news\User_label;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_loan_flows;
use app\models\news\White_list;
use Yii;

/**
 * 1.通过user_id获取用户进行中的借款
 * 2.通过userid、普罗米状态获取用户单条借款
 * 3.返回驳回提示
 * 4.获取金额、分期、天数展示数组
 * 5.获取用户分期信息
 * 6.获取用户担保借款可借额度数组
 * 7.获取用户信用借款可借额度数组
 * 8.计算到手金额
 * 9.还款计划
 * 10.获取借款分期
 * 11.检测是否允许借款
 * 12.监测借款数据是否合法
 * 13.生产借款
 * 14.获取借款详情
 * 15.通过loanid获取借款对象
 * 16.获取还款时间
 */
class UserloanService extends Service {

    private $user;

    public function __construct($user = null) {
        $this->user = $user;
    }

    /**
     * 1.通过user_id获取用户进行中的借款
     * @param $user_id
     * @return \app\models\news\type|null
     */
    public function getUserLoan($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return null;
        }
        $loanModel = new User_loan();
        $status = array('1', '2', '5', '6', '9', '10', '11', '12', '13');
        $userloan = $loanModel->getUserLoan($user_id, $status, array(1, 3, 4));
        return $userloan;
    }

    /**
     * 2.通过userid、普罗米状态获取用户单条借款
     * @param $user_id
     * @param $prome_status
     * @param string $orderby
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getLoanByProme($user_id, $prome_status, $orderby = '') {
        $user_id = intval($user_id);
        if (!$user_id) {
            return null;
        }
        $prome_status = intval($prome_status);
        if (!$prome_status) {
            return null;
        }
        $loanModel = User_loan::find()->where(['user_id' => $user_id, 'prome_status' => $prome_status]);
        if ($orderby == '') {
            return $loanModel->one();
        }
        return $loanModel->orderBy(['last_modify_time' => $orderby])->one();
    }

    /**
     * 3.返回驳回提示
     * @param $loan
     * @return array
     */
    public function loanReject($loan) {
        if (!empty($loan)) {
            if ($loan->status == 3 || $loan->status == 7) {
                $loan_flower = User_loan_flows::find()
                        ->select(User_loan_flows::tableName() . ".create_time," . User_loan_flows::tableName() . ".reason, " . BehaviorRecord::tableName() . '.type')
                        ->leftJoin(BehaviorRecord::tableName(), BehaviorRecord::tableName() . ".loan_id = " . User_loan_flows::tableName() . ".loan_id")
                        ->where([
                            User_loan_flows::tableName() . ".loan_status" => $loan['status'],
                            User_loan_flows::tableName() . ".loan_id" => $loan['loan_id']])
                        ->one();
                if (!empty($loan_flower) && empty($loan_flower['type'])) {
                    $reason = '您暂时不满足借款条件\n您可以通过完善选填资料后重新认证';
                    if (!empty($loan_flower['reason'])) {
                        $reason = $loan_flower['reason'];
                    }
                    $rejectReason = Keywords::getRejectReason();
                    $guideUrl = '';
                    if (in_array($reason, $rejectReason)) {
//                        $number = rand(0, 9);
//                        if (in_array($number, [0, 1, 2, 3, 4])) {
//                            $guideUrl = Yii::$app->params['reject_reason_url'] . '?user_id=' . $loan['user_id'];
//                        } else {
                        $o_user = (new User())->getById($loan['user_id']);
                        $guideUrl = 'http://dc.zhirongyaoshi.com/?utm_source=reject&phone=' . $o_user->mobile;
//                        }
                    }
                    $behavior_record_data = array(
                        'user_id' => $loan['user_id'],
                        'loan_id' => $loan['loan_id'],
                        'type' => $guideUrl == '' ? 1 : 2,
                    );
                    $behavior_record_info = new BehaviorRecord();
                    $behavior_record_info->addList($behavior_record_data);
                    $isSelection = 0; //不显示立即完善按钮
                    if ($reason == '您暂时不满足借款条件\n您可以通过完善选填资料后重新认证') {
                        $isSelection = 1; //显示立即完善按钮
                    }
                    //监管进场不弹窗口
                    if (Keywords::inspectOpen() == 2) {
                        return array(
                            'is_reject' => 0,
                            'guide_url' => '',
                            'is_selection' => 0,
                            'reject_data' => array()
                        );
                    }
                    return array(
                        'is_reject' => 1,
                        'guide_url' => $guideUrl,
                        'is_selection' => $isSelection,
                        'reject_data' => array($loan_flower['create_time'], $reason)
                    );
                }
            }
        }
        return array(
            'is_reject' => 0,
            'guide_url' => '',
            'is_selection' => 0,
            'reject_data' => array()
        );
    }

    /**
     * 4.获取金额、分期、天数展示数组
     * @param $userinfo
     * @param $amounts
     * @param $type 借款类型，1：信用 2：担保
     * @return array
     */
    public function getList($userinfo, $amounts, $type) {
        $returnData = $this->getInitData($userinfo, $amounts);
        $return = $returnData['return'];
        $allAmount = $returnData['amount'];

        $attayTerm = $this->loanTerm($userinfo->user_id);
        if ($type == 1) {
            $canTerm = $attayTerm['xy']['canTerm'];
            $terms = $attayTerm['xy']['terms'];
            $amountTerms = $attayTerm['xy']['amount'];
        }
        if ($canTerm == 1) {//可分期
            //$amount 固定额度数组
            //获取分期额度数据
            $amountTermStart = end($amounts) + 500;
            $fq_amounts = $this->getArrayAmount($amountTermStart, $amountTerms); //可分期额度数组
            $new_amounts = array_merge($amounts, $fq_amounts);
            $newAllAmount = [];
            foreach ($allAmount as $k => $v) {
                if (in_array($v, $new_amounts)) {
                    $newAllAmount[$k]['money'] = $v;
                    $newAllAmount[$k]['enabled'] = '1'; //可选
                } else {
                    $newAllAmount[$k]['money'] = $v;
                    $newAllAmount[$k]['enabled'] = '2'; //不可选
                }
            }
            foreach ($return as $k => $v) {
                foreach ($v as $vk => $vv) {
                    if (in_array($k, $amounts)) {
                        if ($vv["term"] <= $terms) {
//                            if (in_array($return[$k][$vk]['days'], [21, 28])) {
//                                $return[$k][$vk]['enabled'] = '2'; //不可选
//                            } else {
                            $return[$k][$vk]['enabled'] = '1'; //可选
//                            }
                        } else {
                            $return[$k][$vk]['enabled'] = '2'; //不可选
                        }
                    } elseif (in_array($k, $fq_amounts)) {
                        if ($vv["term"] <= $terms && $vv["term"] > 1) {
                            $return[$k][$vk]['enabled'] = '1'; //可选
                        } else {
                            $return[$k][$vk]['enabled'] = '2'; //不可选
                        }
                    } else {
                        $return[$k][$vk]['enabled'] = '2'; //不可选
                    }
                }
            }
        } else {
            foreach ($allAmount as $k => $v) {
                if (in_array($v, $amounts)) {
                    $newAllAmount[$k]['money'] = $v;
                    $newAllAmount[$k]['enabled'] = '1'; //可选
                } else {
                    $newAllAmount[$k]['money'] = $v;
                    $newAllAmount[$k]['enabled'] = '2'; //不可选
                }
            }
            foreach ($return as $k => $v) {
                foreach ($v as $vk => $vv) {
                    if (in_array($k, $amounts) && $vv["term"] == 1) {
                        if (in_array($return[$k][$vk]['days'], [21, 28]) || ($return[$k][$vk]['days'] == 7 && $k != 500)) {
                            $return[$k][$vk]['enabled'] = '2'; //不可选
                        } else {
                            $return[$k][$vk]['enabled'] = '1'; //可选
                        }
                    } else {
                        $return[$k][$vk]['enabled'] = '2'; //不可选
                    }
                }
            }
        }

        $arrList['amount'] = $return;
        $arrList['money_list'] = $newAllAmount;
        $arrList['goods_list'] = count($amounts) == 0 ? (new GoodsService())->getGoodsList(0) : (new GoodsService())->getGoodsList($amounts[count($amounts) - 1]);
        $arrList['term_msg_list'] = $this->getMsgList($allAmount);
        $arrList['amount_msg'] = '金额不可用！';

        return $arrList;
    }

    /**
     * 5.获取用户分期信息
     * @param $user_id
     * @return array
     */
    public function loanTerm($user_id) {
        if (Keywords::machTermOpen() == 2) {//分期开关2:关闭
            return $apiTerm = ['xy' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0], 'db' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0]];
        }

        $userTerm = ( new Term())->getTremByUserId($user_id);
        //已经存在分期数据
        if ($userTerm) {
            $term = [
                'xy' => ['canTerm' => $userTerm->xy_canterm, 'terms' => (int) $userTerm->xy_term, 'amount' => $userTerm->xy_amount],
                'db' => ['canTerm' => $userTerm->db_canterm, 'terms' => (int) $userTerm->db_term, 'amount' => $userTerm->db_amount]
            ];
            //存储的分期数据为不可分期@todo 12-11,暂时不进行决策请求，小鲁需求
//            if ($userTerm->db_canterm == 0 || $userTerm->db_canterm == 0) {
//                //判断用户在最后一次时间之后是否有过正常还款行为
//                $isRepeatCount = (new User())->isRepeat($user_id, $userTerm->last_modify_time);
//                if ($isRepeatCount && $isRepeatCount > 0) {
//                    $term = $this->getTermApi($user_id);
//                }
//            }
        } else {
//            $term = $this->getTermApi($user_id);
            $term = ['xy' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0], 'db' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0]];
        }
        return $term;
    }

    /**
     * 调用接口获取
     * @param $user_id
     * @return array
     */
    private function getTermApi($user_id) {
        $policyApi = new policyApi();
        $data = [
            'user_id' => $user_id,
            'aid' => 1,
        ];
        $ret = $policyApi->antiperiod($data);
        $result = json_decode($ret, true);
        if (!empty($result) && $result['rsp_code'] == '0000' && !empty($result['result']) && is_array($result['result'])) {
            $userinfo = User::findOne($user_id);
            $amountMaxXy = (new User())->getUserLoanAmount($userinfo); //获取用户最大信用额度
            $amountMaxDb = 2500;
            if ($result['result']['xy']['amount'] < $amountMaxXy || $result['result']['db']['amount'] < $amountMaxDb) {
                Logger::dayLog('antiperiodError', print_r(array($user_id => $result), true));
                $apiTerm = [
                    'xy' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0],
                    'db' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0]
                ];
            } else {
                $apiTerm = $result['result'];
            }
        } else {
            //调用接口失败，记录日志
            Logger::dayLog('antiperiodError', print_r(array($user_id => $result), true));
            $apiTerm = [
                'xy' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0],
                'db' => ['canTerm' => 0, 'terms' => 0, 'amount' => 0]
            ];
        }
        $condition = [
            "user_id" => $user_id,
            "db_canterm" => (int) $apiTerm['db']['canTerm'], //0：不可分期 1：可分期
            "db_amount" => (int) $apiTerm['db']['amount'], //分期额度
            "db_term" => (string) $apiTerm['db']['terms'], //期数
            "xy_canterm" => (int) $apiTerm['xy']['canTerm'],
            "xy_amount" => (int) $apiTerm['xy']['amount'],
            "xy_term" => (string) $apiTerm['xy']['terms'],
        ];
        $termModel = new Term();
        $termResult = $termModel->getTremByUserId($user_id);
        if ($termResult) {
            $result = $termResult->updateTerm($condition);
        } else {
            $result = $termModel->saveTerm($condition);
        }
        $term = [
            'xy' => ['canTerm' => $result->xy_canterm, 'terms' => (int) $result->xy_term, 'amount' => $result->xy_amount],
            'db' => ['canTerm' => $result->db_canterm, 'terms' => (int) $result->db_term, 'amount' => $result->db_amount]
        ];
        return $term;
    }

    /**
     * 6.获取用户担保借款可借额度数组
     * @param $userinfo
     * @param $num_amount
     * @return array
     */
    public function getCreditArrayAmount($userinfo, $num_amount = 500) {

        $qj_user = (new User_credit_qj())->getByIdentity($userinfo->identity);
        if (!empty($qj_user)) {
            return ['500'];
        } else {
            return ['500', '1000', '1500'];
        }
        //用户评测
        $creditResult = (new Apihttp())->getUserCredit(['mobile' => $userinfo->mobile]);
        //1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期
        if (!empty($creditResult['rsp_code']) && $creditResult['rsp_code'] === '0000' && !empty($creditResult['user_credit_status']) && $creditResult['user_credit_status'] == 5 && intval($creditResult['order_amount']) % 500 == 0) {
            $amount = (string) (int) $creditResult['order_amount'];
            return [$amount];
        }

        $temQuotaModel = new TemQuota();
        $quotaData = $temQuotaModel->getByUserId($userinfo->user_id);
        if (!$quotaData) {
            return ['500', '1000'];
        }
        $amount = (string) $quotaData->quota;

        //$amount = (new User())->getUserLoanAmount($userinfo);
        $array = [];
        while ($num_amount <= $amount) {
            $array[] = $num_amount;
            $num_amount += 500;
        }
        $arr = [];
        foreach ($array as $k => $v) {
            $arr[$k] = (string) $v;
        }
        return $arr;
    }

    /**
     * 传入起始额度、结束额度、步长，返回额度数组
     * @param $start
     * @param $end
     * @param $step
     * @return array
     */
    private function getArrayAmount($start, $end, $step = 500) {
        $array = [];
        while ($start <= $end) {
//            if($start <= 1500){
//                $array[] = $start;
//                $start += $step;
//            }else if($start>=3000){
//                $array[] = $start;
//                $start += 3000;
//            }else{
            $array[] = $start;
            $start += $step;
//            }
        }
        $arr = [];
        foreach ($array as $k => $v) {
            $arr[$k] = (string) $v;
        }
        return $arr;
    }

    /**
     * 7.获取用户信用借款可借额度数组
     * @return array
     */
    public function getGuaranteeArrayAmount() {
        return ['1500', '2000', '2500'];
    }

    /**
     * 8.计算到手金额
     * @param $userinfo
     * @param $amount
     * @param $withdraw_fee
     * @param $term
     * @return mixed
     */
    public function getGetMoney($userinfo, $amount, $withdraw_fee, $term) {
        if ($term == 1) {
            $charge = (new User_label())->isChargeUser($userinfo->mobile);
            if ($charge == false) {
                return $amount - $withdraw_fee;
            } else {
                return $amount;
            }
        } else {
            $charge = (new User_label())->isChargeUser($userinfo->mobile);
            if ($charge == false) {
                return $amount - $withdraw_fee;
            } else {
                return $amount;
            }
        }
    }

    /**
     * 9.还款计划
     * @param object $userinfo 用户对象
     * @param integer $amount 借款金额
     * @param string $term 期数
     * @param integer $days 天数
     * @param integer $coupon_id 优惠卷id
     * @param integer $withdraw 服务费
     * @param integer $interest 利息
     * @return array
     */
    public function getReayPlan($userinfo, $amount, $term, $days, $coupon_id, $withdraw, $interest,$is_installment = FALSE) {
        $charge = (new User_label())->isChargeUser($userinfo->mobile);
        if (!$is_installment) {
            if ($charge == false) {
                if ($coupon_id) {//使用优惠卷
                    $couponModel = (new Coupon_list())->getCouponById($coupon_id);
                    $coupon_amount = $couponModel['val'];
                    if ($coupon_amount == 0) {//全免卷
                        $repay_amount = $amount;
                    } else {
                        $jianmian = $interest - $coupon_amount > 0 ? $coupon_amount : $interest;
                        $repay_amount = $amount + $interest - $jianmian;
                    }
                } else {
                    $repay_amount = $amount + $interest;
                }
            } else {
                if ($coupon_id) {
                    $couponModel = (new Coupon_list())->getCouponById($coupon_id);
                    $coupon_amount = $couponModel['val'];
                    if ($coupon_amount == 0) {
                        $repay_amount = $amount + $withdraw;
                    } else {
                        $jianmian = $interest - $coupon_amount > 0 ? $coupon_amount : $interest;
                        $repay_amount = $amount + $withdraw + $interest - $jianmian;
                    }
                } else {
                    $repay_amount = $amount + $interest + $withdraw;
                }
            }
            $days = $days;
            $repayPlan[] = [
                'term' => $term,
                'first_show_date' => date('m月d日', strtotime('+' . $days . ' days', time())),
                'repay_amount' => sprintf('%.2f', $repay_amount),
                'repay_date' => date('Y/m/d', strtotime('+' . $days . ' days', time())),
            ];
            return $repayPlan;
        } else {
            if ($charge == false) {
                $repay_amount = $amount + $interest;
                $additional = $interest;
            } else {
                $repay_amount = $amount + $interest + $withdraw;
                $additional = $interest + $withdraw;
            }

            $repayPlan = [];
            $amount_total = 0;
            for ($i = 0; $i < $term; $i++) {
                $day = ($days / $term) * ($i + 1);
                if ($i == 0) {
                    $amount_total = $amount_total + sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100) + sprintf('%.2f', $additional);
                    $first_amount = sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100) + sprintf('%.2f', $additional);
                    $repayPlan[$i] = [
                        'term' => $i + 1,
                        'first_show_date' => date('m月d日', strtotime('+' . $day . ' days', time())),
                        'show_text' => '首期应还',
                        'repay_amount' => sprintf('%.2f', $first_amount),
                        'repay_date' => date('Y/m/d', strtotime('+' . $day . ' days', time()))
                    ];
                } elseif ($i == $term - 1) {
                    $repayPlan[$i] = [
                        'term' => $i + 1,
                        'show_text' => '第' . $this->numberChange($i + 1) . '期应还',
                        'repay_amount' => sprintf('%.2f', (sprintf('%.3f', round($repay_amount * 100) / 100) - $amount_total)),
                        'repay_date' => date('Y/m/d', strtotime('+' . $day . ' days', time()))
                    ];
                } else {
                    $amount_total = $amount_total + sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100);
                    $repayPlan[$i] = [
                        'term' => $i + 1,
                        'show_text' => '第' . $this->numberChange($i + 1) . '期应还',
                        'repay_amount' => sprintf('%.2f', round((sprintf('%.3f', $amount / $term)) * 100) / 100),
                        'repay_date' => date('Y/m/d', strtotime('+' . $day . ' days', time()))
                    ];
                }
            }
            return $repayPlan;
        }
    }

    /**
     * 10.获取借款分期
     * @param $loanId 借款id
     * @return int|mixed
     */
    public function getTerm($loanId) {
        $userLoanModel = new User_loan();
        $userLoanObj = $userLoanModel->getLoanById($loanId);
        if (empty($userLoanObj)) {
            return 0;
        }
        if (in_array($userLoanObj->business_type, [1, 4])) {
            return 1;
        }
        $goodsOrderModel = new GoodsOrder();
        $goodsOrderObj = $goodsOrderModel->getLoanByLoanId($loanId);
        if (empty($goodsOrderObj)) {
            return 0;
        }
        return $goodsOrderObj->number;
    }

    /**
     * 11.检测是否允许借款
     * @param $userObj  用户对象
     * @return string
     */
    public function checkCanLoan() {
        if (!is_object($this->user) || empty($this->user)) {
            return '10001';
        }

        //用户状态判断
        if ($this->user->status == 5) {
            return '10097';
        }

        //连点
        $norepet = (new No_repeat())->norepeat($this->user->user_id, $type = 2);
        if (!$norepet) {
            return '99991';
        }

        $loan_info = new User_loan();
        //判断是否存在借款
        $loan = $loan_info->getHaveinLoan($this->user->user_id, [1, 4, 5, 6, 9, 10]);
        if ($loan !== 0) {
            return '10050';
        }

        //判断是否存在驳回订单
        $judgment = $loan_info->LoanJudgment($this->user->user_id);
        if (!$judgment) {
            return '10098';
        }

        //判断7-14产品中是否有进行中的借款
        if (!empty($this->user->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $this->user->identity]);
            if (!$canLoan) {
                return '99990';
            }
        }
        return '0000';
    }

    /**
     * 12.监测借款数据是否合法
     * @param $user 用户对象
     * @param $amount   借款金额
     * @param $days 借款天数
     * @param $bank 银行卡对象
     * @param $coupon_id    优惠券id
     * @param int $coupon_val   优惠券金额
     * @param $business_type    借口类型
     * @return string
     */
    public function checkLoanField($amount, $days, $bankObj, $coupon_id, $coupon_val = 0, $business_type, $source, $term) {
        if (!is_object($this->user) || empty($this->user)) {
            return '10001';
        }
        if (!is_object($bankObj) || empty($bankObj)) {
            return '10043';
        }
        if ($term == 1) {
            //最大额度限制
            //$max_amount = $this->user->getUserLoanAmount($this->user, $type = 1);
            $max_amount = 10000;
            //担保借款最大额度限制
            if ($business_type == 4) {
//                $max_amount = 2500;
            }
        } else {
            //最大额度限制
            $max_amount = (new Term())->getTremAmountMax($this->user->user_id, 1);
            //担保借款最大额度限制
            if ($business_type == 4) {
                $max_amount = (new Term())->getTremAmountMax($this->user->user_id, 4);
            }
        }
        if (intval($amount) < 500 || intval($amount) > $max_amount || intval($amount) % 500 != 0) {
            return '10048';
        }
        if (intval($days) < 56 || intval($days) > 336 || intval($days) % 7 != 0) {
            return '10048';
        }

        if ($this->user->status != 3) {
            return '10023';
        }
        if (in_array($source, [1, 2, 3, 4]) && ($this->user->extend->company == '' || $this->user->extend->telephone == '')) {
            return '10047';
        }
        if ($this->user->pic_identity == '' || ($this->user->pic_identity != '' && $this->user->status == '4')) {
            return '10047';
        }
        if ($bankObj->user_id != $this->user->user_id) {
            return '10044';
        }
        $coupon = '';
        if (!empty($coupon_id)) {
            $coupon = Coupon_list::findOne($coupon_id);
        }
        if (!empty($coupon)) {
            if (($coupon->mobile != $this->user->mobile) || $coupon->status != 1 || ($coupon->val != $coupon_val)) {
                return '10049';
            }
        }
        $isOpen = (new Payaccount())->getPaysuccessByUserId($this->user->user_id, 2, 1);
        $isPassword = (new Payaccount())->getPaysuccessByUserId($this->user->user_id, 2, 2);
        if (empty($isOpen) || empty($isPassword)) {
            return '10210';
        }
        if ($isOpen->card != $bankObj->id) {
            return '10211';
        }
        return '0000';
    }

    /**
     * 13.生产借款
     * @param $amount
     * @param $days
     * @param $bankObj
     * @param $coupon_id
     * @param $coupon_val
     * @param $business_type 借款类型：1信用 2担保 （注：不与user_loan字段business_type同含义）
     * @param int $source
     * @param $uuid
     * @param $term
     * @param $goods_id
     * @param $desc
     * @return array
     */
    public function addLoan($amount, $days, $bankObj, $coupon_id, $coupon_val, $business_type, $source = 3, $uuid, $term, $goods_id, $desc = '个人或家庭消费') {
        if (!is_object($this->user) || empty($this->user)) {
            return ['rsp_code' => '10001'];
        }
        if (!is_object($bankObj) || empty($bankObj)) {
            return ['rsp_code' => '10043'];
        }
        //分期开关判断
        $userTerm = ( new Term())->getTremByUserId($this->user->user_id);
        if (empty($userTerm) && $term > 1) {
            return ['rsp_code' => '10200'];
        }
        if (!empty($userTerm)) {
            if ($business_type == 4 && $userTerm->db_canterm == 0 && $term > 1) {
                return ['rsp_code' => '10200'];
            } elseif ($business_type == 1 && $userTerm->xy_canterm == 0 && $term > 1) {
                return ['rsp_code' => '10200'];
            }
        }


        $source = ($source == 3) ? 2 : $source;
        $status = 5;
        $type = 2;
        $ip = Common::get_client_ip();
        if ($term > 1) {
            $business_type = ($business_type == 4) ? 6 : 5; //5信用分期 6担保分期
            $coupon_id = 0;
            $coupon_val = 0;
        } else {
            $business_type = ($business_type == 4) ? 4 : 1; //1信用 4担保
        }

        $loanModel = new User_loan();
        //计算用户手续费、利率
        $loanfee = $loanModel->loan_Fee_new($amount, $days, $this->user->user_id, $term);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        $fee = $loanfee['fee'] * 100;

        //是否为系统指定后置用户
        $charge = (new User_label())->isChargeUser($this->user->mobile);
        if ($charge === false) {
            $charge = 1;
        } else {
            $charge = 0;
        }
        $condition = array(
            'user_id' => $this->user->user_id,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'current_amount' => $amount,
            'days' => $days,
            'type' => $type,
            'status' => $status,
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
            'desc' => $desc,
            'bank_id' => $bankObj->id,
            'source' => !empty($source) ? (int) $source : 2,
            'is_calculation' => $charge,
            'business_type' => $business_type,
        );

        //借款决策
        $loan_no_keys = $this->user->user_id . "_loan_no";
        $loan_no = Yii::$app->redis->get($loan_no_keys);
        if (!empty($loan_no)) {
            $condition['loan_no'] = (string) $loan_no;
        } else {
            $condition['status'] = 3;
            $condition['prome_status'] = 1;
        }

        //白名单
        $whiteModel = new White_list();
        $white = $whiteModel->isWhiteList($this->user->user_id);
        if ($white) {
            $condition['final_score'] = -1;
        }

        //优惠卷金额
        if (!empty($coupon_id)) {
            if ($interest_fee > $coupon_val) {
                $condition['coupon_amount'] = $coupon_val;
            } else {
                $condition['coupon_amount'] = $interest_fee;
            }
        }

        $condition['withdraw_time'] = date('Y-m-d H:i:s');
        $ret = $loanModel->addUserLoan($condition, $business_type);
        Logger::dayLog('service/userloan/addLoan', '添加userloan', $condition, $ret); //@todo 监测使用，后期请删除
        Yii::$app->redis->del($loan_no_keys);
        if (empty($ret) || $condition['status'] == 3) {
            return ['rsp_code' => '10051'];
        }
        $loan = $loanModel;
        if (!$white) {
            $frModel = Fraudmetrix_return_info::find()->where(['loan_id' => $loan_no])->one();
            if (!empty($frModel)) {
                $loan->refresh();
                $frModel->savefinal_score($loan, $frModel);
            }
        }
        //记录优惠券使用情况
        if (!empty($coupon_id)) {
            $couponUseModel = new Coupon_use();
            $couponUseModel->addCouponUse($this->user, $coupon_id, $loan->loan_id);
        }
        if ($loan->status == 5) {
            $success_num = (new User())->isRepeatUser($loan->user_id);
            $loanextendModel = new User_loan_extend();
            $extend = array(
                'user_id' => $loan->user_id,
                'loan_id' => $loan->loan_id,
                'outmoney' => 0,
                'payment_channel' => 0,
                'userIp' => $ip,
                'extend_type' => '1',
                'success_num' => $success_num,
                'uuid' => $uuid
            );
            $extendId = $loanextendModel->addList($extend);
            if (empty($extendId)) {
                Logger::dayLog('service/userloan/addLoan', '添加userloanextend失败', 'loan_id：' . $loan->loan_id, $extend);
                return ['rsp_code' => '10051'];
            }
        }
        if ($term > 1) {
            $goodsOrderModel = new GoodsOrder();
            $goodsService = new GoodsService();
            $order_id = $goodsService->createOrderId($loan->loan_id, $this->user->identity);
            if (!$order_id) {
                return ['rsp_code' => '10201'];
            }
            $order_amount = $loanModel->getOrderAmount($charge, $amount, $withdraw_fee, $interest_fee);
            $goodsOrder = [
                'order_id' => $order_id,
                'goods_id' => $goods_id,
                'loan_id' => $loan->loan_id,
                'user_id' => $loan->user_id,
                'number' => $term,
                'fee' => $fee,
                'order_amount' => $order_amount,
            ];
            $goodsOrderInfo = $goodsOrderModel->addGoodsOrder($goodsOrder);
            if (!$goodsOrderInfo) {
                return ['rsp_code' => '10202'];
            }
        }
        return ['rsp_code' => '0000', 'data' => $loan];
    }

    /**
     * 14.获取借款详情
     * @param $loanId   借款id
     * @return array
     */
    public function getLoanDetaile($loanId) {
        if (!is_numeric($loanId) || empty($loanId)) {
            return ['rsp_code' => '99996'];
        }
        //查询借款人的信息
        $loaninfo = User_loan::find()->joinWith('user', true, 'LEFT JOIN')->where(['loan_id' => $loanId])->one();
        if (empty($loaninfo)) {
            return ['rsp_code' => '10052'];
        }
        if (empty($loaninfo->user)) {
            return ['rsp_code' => '10001'];
        }

        //账单数量
        $bill_num = 0;
        if ((in_array($loaninfo->status, [9, 11, 12, 13]) && $loaninfo->loanextend && $loaninfo->loanextend->status == 'SUCCESS') || (in_array($loaninfo->status, [9, 11, 12, 13]) && $loaninfo->settle_type == 3)) {
            $bill_num = $bill_num + 1;
        }

        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $loaninfo->user->mobile]);
        if (!empty($iousResult)) {
            $bill_num = $bill_num + 1;
        }
        $userLoanModel = new User_loan();
        $loaninfo->chase_amount = $userLoanModel->getChaseamount($loaninfo->loan_id); //逾期金额，分期后更改逾期存储方式
        //应还款金额
        $huankuan_amount = $userLoanModel->getRepaymentAmount($loaninfo);
        //已还款金额
        $already_amount = $loaninfo->getRepayAmount(2);
        //服务费
        $service_amount = $userLoanModel->interest_fee;
        //还款时间&第几期
        $huankuan = $this->getHuankuanTime($loaninfo);
        //逾期罚息
        $overdue_amount = $userLoanModel->getOverdueAmount($loaninfo->loan_id);
        //逾期天数
        $overdue_days = $userLoanModel->getOverdueDays($loaninfo);
        //借款银行卡号
        $bank_number = User_bank::find()->select(array('id', 'card', 'bank_name'))->where(['id' => $loaninfo->bank_id])->one();
        //驳回理由
        $loan_coupon = $loaninfo->couponUse;
        $reason = User_loan_flows::find()->select('reason')->where(['loan_id' => $loanId])->orderBy('create_time desc')->one();
        $coupon_amount = !empty($loan_coupon) ? ((($loan_coupon->couponList->val == 0) && ($loan_coupon->couponList->status == 2)) ? $service_amount : $loaninfo->coupon_amount) : $loaninfo->coupon_amount;
        //借款状态分类 1审核界面 2待还款界面 3逾期界面 4提现页面 5投保页面
        $loanStatusView = $userLoanModel->getLoanStatusView($loaninfo);
        //已续期
        if ($loaninfo->status == 9) {
            if ($loaninfo->settle_type == 3) {
                $loanStatusView['status'] = 24;
            }
        }

        //是否可续期
        $renewModel = new Renew_amount();
        $is_allow = $renewModel->getRenew($loanId);
        $renewal_allow = 0;
        $renewal_money = 0;
        if (!empty($is_allow)) {
            $renewal_allow = 1;
            $renewal_money = $is_allow->renew_fee;
        }
        //续期金额
        $renewAmountObj = $renewModel->getRenewOne($loanId);
        if (empty($is_allow) && !empty($renewAmountObj)) {
            $renewal_money = $renewAmountObj->renew_fee;
        }
        //续期时间
        $parent_loan_id = ($loaninfo->parent_loan_id != 0) ? $loaninfo->parent_loan_id : $loaninfo->loan_id;
        $sql = "select r.last_modify_time from " . Renewal_payment_record::tableName() . " r left join " . User_loan::tableName() . " l on r.loan_id = l.loan_id where r.status = 1 and r.loan_id = " . $loaninfo->loan_id . " and r.parent_loan_id = " . $parent_loan_id;
        $repay_arr = Yii::$app->db->createCommand($sql)->queryOne();
        if (!empty($repay_arr)) {
            $renewal_time = $repay_arr['last_modify_time'];
        } else {
            $renewal_time = "";
        }
        //监管进场
        if (Keywords::renewalInspectOpen() == 2) {
            //进场展期申请中
            $o_renewal_inspect = (new RenewalInspect())->getByUserIdAndStatus($loaninfo->user_id, [0, 3]);
            if (!empty($o_renewal_inspect)) {
                $loanStatusView['status'] = 25;
                $renewal_time = $o_renewal_inspect->create_time;
            }

        }

        //分期
        $term = $this->getTerm($loanId);
        //保单号
        $insuranceObj = (new Insurance())->getDateByLoanId($loanId);
        $insuranceOrder = !empty($insuranceObj) ? $insuranceObj->insurance_order : '';
        //状态18，提现url
        $withdraw_url = '';
        if ($loanStatusView['status'] == 18) {
            $withdraw_url = Yii::$app->request->hostInfo . '/new/depositoryapi/withdraw?loan_id=' . $loanId;
        }
        //有信令认证有效时间
        $time = '0';
        if ($loanStatusView['status'] == 21) {
            $loanFlow = User_loan_flows::find()->where(['loan_id' => $loanId, 'loan_status' => 6])->one();
            $time = strtotime($loanFlow->create_time) + 86400 - time();
        }
        //借款审核通过时间
        if ($loanStatusView['status'] == 22) {
            $loanFlow = User_loan_flows::find()->where(['loan_id' => $loanId, 'loan_status' => 6])->one();
            $loan_flow_time = !empty($loanFlow) ? $loanFlow->create_time : '';
        }
        //待还款确认时间
        if ($loanStatusView['status'] == 11) {
            $oLoanRepay = Loan_repay::find()->where(['status' => [-1, 3], 'loan_id' => $loanId])->orderBy('id desc')->one();
            if (!empty($oLoanRepay)) {
                $repayment_time = $oLoanRepay->createtime;
                if ($oLoanRepay->status == 3) {//线下还款
                    $expect_time = date('Y-m-d H:i:s', strtotime($repayment_time) + 24 * 3600);
                } else {
                    $expect_time = date('Y-m-d H:i:s', strtotime($repayment_time) + 2 * 3600);
                }
            }
        }
        //有信令认证地址
        // $yxl_authentication_url = 'new/auth/jump?userToken='.$loaninfo->user->mobile;
//        if($loanId%2==0){
//            $yxl_authentication_url = 'new/auth/toauthone';
//        }else{
//            $yxl_authentication_url = 'new/auth/howtoauth';
//        }
        //协议地址
        $insurance_url = Yii::$app->request->hostInfo . '/new/agreeloan/toubao?loan_id=' . $loanId;
        //购买保险配置信息
        $buyInsurance_info = Keywords::buyInsurance();
        //激活
        if ($loanStatusView['status'] == 21) {
            //智融接口返回测评支付结果
            $apiHttp = new Apihttp();
            $payResult = $apiHttp->getYxlpayresult(['loan_id' => $loanId, 'source' => 1]);
            $btn_status = $this->getBtnStatus($payResult);
            //测评激活(user_id分桶：0 1 2 3 4下载智融app ,5 6 7 8 9直接跳转智融H5)
            $evaluation_activation_info = (new User())->getEvaluationChannel($loaninfo->user->user_id, $loaninfo->user->mobile);

            //请求智融钥匙接口获取安卓apk下载地址、ios App Store地址
            $apiHttp = new Apihttp();
            $downResult = $apiHttp->getYxldownurl([]);
            $ios_down_url = $downResult['ios_url'];
            $android_down_url = $downResult['android_url'];
        } else {
            $btn_status = 0;
            $ios_down_url = '';
            $android_down_url = '';
            $evaluation_activation_info['yxl_authentication_url'] = '';
            $evaluation_activation_info['channel'] = 0;
            $evaluation_activation_info['youxin_down_url'] = '';
        }

        //直接激活
        $direct_activation_url = ''; //直接激活h5地址
        $redict_activation_num = 0;
        if ($btn_status) {
            $num = Yii::$app->redis->get($loanId);
            $redict_activation_num = empty($num) ? 0 : $num;
            $direct_activation_url = Yii::$app->request->hostInfo . '/borrow/directactivation/activating?loan_id=' . $loanId;
        }
        $credit_is_show = Keywords::getIsCreditShow();
        //利息
        $interest_amount = $loaninfo->getInterestFee();
        //综合费用、综合利息（合规进场时，利息拆分）
        $surplus_amount = 0;
        $is_installment = in_array($loaninfo->business_type,[5,6,11]) ? TRUE : FALSE;
        if(in_array($loanStatusView['status'],[18,19])){
            $interest_amount = $loaninfo->interest_fee;
            $o_user_credit = $loaninfo->usercredit;
            if(!empty($o_user_credit)){
                $o_coupon = (new Coupon_use())->getByLoanId($loaninfo->loan_id);
                $coupon_id = '';
                if(!empty($o_coupon)){
                    $coupon_id = $o_coupon->discount_id;
                }
                $repay_plan = (new StageService())->getReayPlan($loaninfo->user, $o_user_credit, $loaninfo->amount, $loaninfo->days, $o_user_credit->period, $coupon_id, $is_installment);
                $surplus_amount = (new StageService())->getSuperviseInterest($loaninfo->amount, $loaninfo->days, $o_user_credit->period, $interest_amount, $loaninfo->coupon_amount, $repay_plan, $is_installment);
                $surplus_amount = bcsub($surplus_amount,$loaninfo->coupon_amount,2);
            }
        }
        $bills = $loaninfo->goodsbills;
        $days_show = $loaninfo->days.'天x1期';
        if(!empty($bills)){
            $bill_term = count($bills);
            $days_show = $bills[0]['days'].'天x'.$bill_term.'期';
        }
        return $array = [
            'rsp_code' => '0000',
            'bill_num' => $bill_num,
            'credit_is_show' => $credit_is_show,
            'repayment_time' => empty($repayment_time) ? '' : $repayment_time,
            'expect_time' => empty($expect_time) ? '' : $expect_time,
            'days' => $loaninfo->days, //借款天数
            'days_show' => $days_show, //借款天数
            'desc' => $loaninfo->desc, //借款理由，290版本后默认其他
            'status' => $loanStatusView['status'], //借款状态
            'loan_amount' => sprintf("%.2f", $loaninfo->amount), //借款金额
            'out_amount' => $loaninfo->is_calculation == 1 ? sprintf("%.2f", $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf("%.2f", $loaninfo->amount), //出款金额
            'interest_amount' => sprintf("%.2f", $interest_amount), //利息
            'service_amount' => sprintf("%.2f", $loaninfo->withdraw_fee), //服务费
            'insurance_amount' => $loaninfo->amount * 0.18 . "/份X1", //投保费
            'coupon_amount' => $coupon_amount == 0 ? sprintf("%.2f", $coupon_amount) : '-' . sprintf("%.2f", $coupon_amount), //优惠券金额
            'like_amount' => $loaninfo->like_amount == 0 ? sprintf("%.2f", $loaninfo->like_amount) : '-' . sprintf("%.2f", $loaninfo->like_amount), //点赞减息
            'huankuan_amount' => sprintf("%.2f", $huankuan_amount), //应还款金额
            'already_amount' => sprintf("%.2f", $already_amount), //已还款金额
            'renewal_money' => sprintf("%.2f", $renewal_money), //续期还款金额
            'principal' => sprintf("%.2f", $huankuan['principal']), //当期应还本金
            'interest' => sprintf("%.2f", $huankuan['interest']), //当期应还利息
            'surplus_amount' => sprintf("%.2f", $surplus_amount),//监管进场，综合利息
            'overdue_days' => $overdue_days, //逾期天数
            'overdue_amount' => !empty($overdue_amount) ? sprintf("%.2f", $overdue_amount) : 0, //逾期罚金
            'credit_amount' => sprintf("%.2f", $loaninfo->credit_amount), //我的额度
            'bank_number' => isset($bank_number) ? substr($bank_number->card, -4) : '', //银行卡后四位
            'bank_name' => isset($bank_number) ? $bank_number->bank_name : '', //银行名称
            'bank_id' => isset($bank_number) ? $bank_number->id : '', //银行卡id
            'update_time' => $loaninfo->last_modify_time, //驳回时间，取消时间，失效时间
            'loan_create_time' => $loaninfo->create_time, //申请提现时间
            'loan_flow_time' => empty($loan_flow_time) ? '' : $loan_flow_time,
            'huankuantime' => $huankuan['huankuantime'], //还款时间
            'reject_reason' => !empty($reason['reason']) ? $reason['reason'] : '不符合借款标准', //驳回理由
            'business_type' => ($loaninfo['business_type'] == 4) ? 2 : $loaninfo['business_type'], //借款类型 1信用 4担保 5分期信用 6分期担保
            'renewal_allow' => $renewal_allow, //是否可续期
            'renewal_num' => $loaninfo->number, //续期次数
            'settle_type' => $loaninfo->settle_type, //账单状态 0初始状态 1还款结清 2续期结清 3续期中
            'renewal_poundage' => sprintf("%.2f", $renewal_money), //续期手续费
            'renewal_time' => $renewal_time, //续期时间
            'insurance_order' => $insuranceOrder, //投保号
            'term' => $term, //分期总期数
            'phase' => $huankuan['phase'], //还款第几期
            'user_status' => $loaninfo->user->status, //用户状态
            'loanview' => !empty($loanStatusView) ? $loanStatusView['view'] : 1, //借款状态归类 1审核界面 2待还款界面 3逾期界面
            'withdraw_url' => $withdraw_url, //提现url
            'insurance_url' => $insurance_url, //投保协议地址
            'insurance_default_check' => $buyInsurance_info['isChk'], //默认是否勾选 1：勾选 2：不勾选
            'insurance_dialog_msg' => '您未选择购买保险，是否放弃极速审核特权？',
            'insurance_dialog_ok_text' => '购买',
            'insurance_dialog_cancel_text' => '放弃',
            'insurance_checked_explain' => '您已享有急速审核特权',
            'insurance_unchecked_explain' => '您已放弃急速审核特权',
            'yxl_count_down' => $time,
            'yxl_authentication_url' => $evaluation_activation_info['yxl_authentication_url'],
            'reject_over_time' => isset($loanStatusView['time']) ? $loanStatusView['time'] : 0,
            'fund_matching_text' => '预计2小时内完成资金匹配',
            'key_count_down_text' => '进入智融钥匙，并在倒计时结束前完成安全认证',
            'activation_btn_status' => $btn_status,
            'evaluation_activation_channel' => $evaluation_activation_info['channel'],
            'youxin_down_url' => $evaluation_activation_info['youxin_down_url'],
            'redict_activation_num' => $redict_activation_num,
            'direct_activation_url' => $direct_activation_url,
            'ios_down_url' => $ios_down_url,
            'android_down_url' => $android_down_url,
            'is_installment' => $is_installment,//是否是分期 true分期 false不是分期
        ];
    }

    /**
     * 
     * @param type $statusArr 智融评测接口返回状态结果数组
     * @return boolean
     */
    public function getBtnStatusByCredit($payResult) {
        $btn_status = 0; //0 不可点击 1：可点击
        if (isset($payResult['res_code'])) {
            if ($payResult['res_code'] == '0000' && empty($payResult['res_data'])) { //可点击激活
                $btn_status = 1;
            }
            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']['create_time']) && $payResult['res_data']['status'] == 0) {
                //四分钟之后可以点击激活
                $end_time = strtotime('-4 minutes');
                if (strtotime($payResult['res_data']['create_time']) < $end_time) {
                    $btn_status = 1;
                } else {
                    $btn_status = 0;
                }
            }

            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']) && $payResult['res_data']['status'] == 1) {
                //已认证支付成功
                $btn_status = 1;
            }
            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']) && $payResult['res_data']['status'] == -1) {
                //支付中
                $btn_status = 0;
            }
            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']) && $payResult['res_data']['status'] == 2) {
                //失效状态下
                $btn_status = 0;
            }
        }
        return $btn_status;
    }

    /**
     * 
     * @param type $statusArr 智融评测接口返回状态结果数组
     * @return boolean
     */
    public function getBtnStatus($payResult) {
        $btn_status = 0;
        if (isset($payResult['res_code'])) {
            if ($payResult['res_code'] == '0000' && empty($payResult['res_data'])) { //可点击激活
                $btn_status = 1;
            }
            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']['create_time']) && $payResult['res_data']['status'] == 0) {
                //四分钟之后可以点击激活
                $end_time = strtotime('-4 minutes');
                if (strtotime($payResult['res_data']['create_time']) < $end_time) {
                    $btn_status = 1;
                } else {
                    $btn_status = 0;
                }
            }

            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']) && $payResult['res_data']['status'] == 1) {
                //已认证支付成功
                $btn_status = 1;
            }
            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']) && $payResult['res_data']['status'] == -1) {
                //支付中
                $btn_status = 0;
            }
            if ($payResult['res_code'] == '0000' && !empty($payResult['res_data']) && $payResult['res_data']['status'] == 2) {
                //失效状态下
                $btn_status = 0;
            }
        }
        return $btn_status;
    }

    /**
     * 15.通过loanid获取借款对象
     * @param $loan_id
     * @return null|static
     */
    public function getLoanByLoanId($loan_id) {
        $loan_id = intval($loan_id);
        if (!$loan_id) {
            return null;
        }
        return User_loan::findOne($loan_id);
    }

    /**
     * 16.获取还款时间
     * @param $userLoanObj 用户对象
     * @return string
     */
    public function getHuankuanTime($userLoanObj) {
        $array = [
            'huankuantime' => '以短信推送时间为准',
            'phase' => '1',
            'principal' => 0,
            'interest' => 0,
        ];
        if (!is_object($userLoanObj) || empty($userLoanObj)) {
            return $array;
        }
        if (in_array($userLoanObj->business_type, [1, 4])) {
            if (in_array($userLoanObj->status,[9,11,12,13])) {
                $array['huankuantime'] = date('Y年n月j日', (strtotime($userLoanObj->end_date) - 24 * 3600));
            }
            $array['principal'] = $userLoanObj->amount;
            $array['interest'] = $userLoanObj->getInterestFee();
        } else {
            $goodsBillModel = new GoodsBill();
            $goodsBillObj = $goodsBillModel->getLatelyPhase($userLoanObj->loan_id);
            if (!empty($goodsBillObj)) {
                $array['huankuantime'] = date('Y年n月j日', (strtotime($goodsBillObj->end_time) - 24 * 3600));
                $array['phase'] = $goodsBillObj->phase;
                $array['principal'] = $goodsBillObj->principal;
                $array['interest'] = $goodsBillObj->interest;
            }
        }
        //订单为还清时，取还清借款时间
        if ($userLoanObj->status == 8) {
            $array['huankuantime'] = date('Y年n月j日', strtotime($userLoanObj->repay_time));
        }
        return $array;
    }

    private function getInitData($userinfo, $amounts) {
        //用户评测
        $creditResult = (new Apihttp())->getUserCredit(['mobile' => $userinfo->mobile]);
        //1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期
        if (!empty($creditResult['rsp_code']) && $creditResult['rsp_code'] === '0000' && !empty($creditResult['user_credit_status']) && $creditResult['user_credit_status'] == 5 && intval($creditResult['order_amount']) % 500 == 0) {
            $amount = (string) (int) $creditResult['order_amount'];
            $getAllAmount = [$amount];
        } else {
            $getAllAmount = $this->getArrayAmount(500, end($amounts), 500);
        }

        if (Keywords::oneTermOpen() == 1) {//单期是否开启开关
            $is = 0;
        } else {
            $is = 1;
        }
        $temQuotaModel = new TemQuota();
//        $quotaData = $temQuotaModel->getByUserId($userinfo->user_id);
        $qj_user = (new User_credit_qj())->getByIdentity($userinfo->identity);
        if ($qj_user) {
            $qu = 0;
        } else {
            $qu = 1;
        }
        $return = [];
        foreach ($getAllAmount as $k => $v) {
            if ($is == 0) {
                if ($qu == 0) {
                    $return[$v] = [
                        ["term" => '1', "days" => '7'],
//                        ["term" => '1', "days" => '56'],
                    ];
                } else {
                    $return[$v] = [
//                        ["term" => '1', "days" => '7'],
                        ["term" => '1', "days" => '56'],
//                        ["term" => '1', "days" => '168'],
//                        ["term" => '1', "days" => '336'],
                    ];
                }
            } else {
                $return[$v] = [];
            }

//            for ($i = $is + 1; $i <= 12; $i++) {
//                if (in_array($i, [1, 3, 6, 9, 12])) {
//                    $return[$v][] = ["term" => (string) ($i * 1), "days" => (string) ($i * 28)];
//                }
//            }
//            $termArr = [3,6,9,11,12];
//            foreach ($termArr as $kv => $vv){
//                $return[$v][] = ["term"=>(string) ($vv * 1), "days"=>(string) ($vv * 28)];
//            }
        }

        return ['return' => $return, 'amount' => $getAllAmount];
    }

    private function getMsgList($amountsArr) {
        $arr = [];
        foreach ($amountsArr as $k => $v) {
            $arr[$v] = '周期不可用！';
        }
        return $arr;
    }

    /**
     * 在贷借款转分期
     * @param $loanId   借款id
     * @param int $term 分期数
     * @return array
     */
    public function loanInstallment($loanId, $term = 3) {
        $userLoanMedel = new User_loan();
        $userLoanObj = $userLoanMedel::findOne($loanId);
        if (empty($userLoanObj)) {
            return ['rsp_code' => '10052'];
        }

        //判断是否已分期
        $newUserLoanObj = $userLoanObj->getInInstallmentByLoanId($userLoanObj->loan_id);
        if (!empty($newUserLoanObj)) {
            $data = [
                'rsp_code' => '0000',
                'loan_id' => $newUserLoanObj->loan_id,
            ];
            return $data;
        }

        $loanfee = $userLoanMedel->loan_Fee_new($userLoanObj->amount, ($userLoanObj->days * $term), $userLoanObj->user_id, $term);
        $interestFee = $loanfee['interest_fee']; //利息
        $withdrawFee = $loanfee['withdraw_fee']; //服务费
        $fee = $loanfee['fee'] * 100;
        //监测是否可分期
        $result = $this->chkCanLoanInstallment($userLoanObj, $interestFee);
        if (!$result) {
            return ['rsp_code' => '10203'];
        }
        //更新原订单状态
        $repayResult = $userLoanObj->saveInstallmentRepay();
        if (empty($repayResult)) {
            return ['rsp_code' => '10206'];
        }
        //更新原订单逾期账单
        $overdueLoanModel = new OverdueLoan();
        $overdueLoanObj = $overdueLoanModel->getLoaninfo(['loan_id' => $loanId, 'loan_status' => [12, 13]]);
        if (!empty($overdueLoanObj)) {
            $overdueInfo = $overdueLoanObj->clearOverdueLoan();
            if (!$overdueInfo) {
                return ['rsp_code' => '10207'];
            }
        }
        //新增user_loan记录
        $newLoanId = $this->loanInstallmentAdd($userLoanObj, $interestFee, $withdrawFee);
        if (empty($newLoanId)) {
            return ['rsp_code' => '10051'];
        }
        $newUserLoanObj = $userLoanMedel::findOne($newLoanId);
        //新增goods_order记录
        $goodsService = new GoodsService();
        $goodsOrderObj = $goodsService->addGoodsOrder($newUserLoanObj, $term, $fee);
        if (empty($goodsOrderObj)) {
            return ['rsp_code' => '10202'];
        }
        $goodsOrderObj->refresh();
        $goodsOrderObj->updateSuccess();
        //新增goods_bill记录
        $successInfo = $goodsService->addGoodsBill($goodsOrderObj, false);
        if (!$successInfo) {
            return ['rsp_code' => '10205'];
        }

        //处理已还金额
        $loanRepayModel = new Loan_repay();
        $loanRepayObj = $loanRepayModel->getRepayByLoanId($userLoanObj->loan_id);
        if (!empty($loanRepayObj)) {
            $loanRepayInfo = true;
            foreach ($loanRepayObj as $item) {
                $loanRepayResult = $loanRepayModel->stagesRepay($item, $newUserLoanObj->loan_id);
                if ($loanRepayInfo) {
                    $loanRepayInfo = $loanRepayResult;
                }
            }
            if (!$loanRepayInfo) {
                return ['rsp_code' => '10208'];
            }
        }

        //新增overdue_loan记录
        $goodsBillObj = (new GoodsBill())->getPostData(['loan_id' => $newUserLoanObj->loan_id]);
        $goodsBillInfo = true;
        foreach ($goodsBillObj as $item) {
            if ($item['bill_status'] == 8) {
                continue;
            }
            $overdue = OverdueLoan::find()->where(['bill_id' => $item->bill_id])->all();
            if (!empty($overdue)) {
                continue;
            }
            if ($item->end_time <= date('Y-m-d 00:00:00')) {
                $billStatusResult = $item->saveGoodsBill(['bill_status' => 12]);
                if ($goodsBillInfo) {
                    $goodsBillInfo = $billStatusResult;
                }
                $data = [];
                $data['loan_id'] = isset($item['loan_id']) ? $item['loan_id'] : '';
                $data['user_id'] = isset($item['user_id']) ? $item['user_id'] : '';
                $data['bill_id'] = isset($item['bill_id']) ? $item['bill_id'] : '';
                $data['bank_id'] = isset($item->userloan['bank_id']) ? $item->userloan['bank_id'] : 0;
                $data['loan_no'] = isset($item->userloan['loan_no']) ? $item->userloan['loan_no'] : '';
                $data['amount'] = isset($item['goods_amount']) ? $item['goods_amount'] : ''; //总金额
                $data['current_amount'] = isset($item['current_amount']) ? $item['current_amount'] : ''; //当期
                $data['days'] = isset($item['days']) ? $item['days'] : '';
                $data['desc'] = isset($item->userloan['desc']) ? $item->userloan['desc'] : '';
                $data['start_date'] = isset($item['start_time']) ? $item['start_time'] : '';
                $data['end_date'] = isset($item['end_time']) ? $item['end_time'] : '';
                $data['loan_status'] = 12;
                $data['interest_fee'] = isset($item['interest']) ? $item['interest'] : ''; //TODO interest_fee
                $data['contract'] = isset($item->userloan['contract']) ? $item->userloan['contract'] : '';
                $data['contract_url'] = isset($item->userloan['contract_url']) ? $item->userloan['contract_url'] : '';
                $data['late_fee'] = 0;
                $data['withdraw_fee'] = isset($item->userloan['withdraw_fee']) ? $item->userloan['withdraw_fee'] : '';
                $data['chase_amount'] = 0;
                $data['is_push'] = 0;
                $data['business_type'] = isset($item->userloan['business_type']) ? $item->userloan['business_type'] : '';
                $data['source'] = isset($item->userloan['source']) ? $item->userloan['source'] : '';
                $data['is_calculation'] = isset($item->userloan['is_calculation']) ? $item->userloan['is_calculation'] : '';
                $data['version'] = 0;
                $data['create_time'] = date('Y-m-d H:i:s');
                $res = (new OverdueLoan)->saveOverdue($data);
                if (!$res) {
                    $goodsBillInfo = false;
                }
            }
        }
        if (!$goodsBillInfo) {
            return ['rsp_code' => '10209'];
        }

        $data = [
            'rsp_code' => '0000',
            'loan_id' => $newUserLoanObj->loan_id,
        ];
        return $data;
    }

    /**
     * 监测在贷订单是否可分期
     * @param $userLoanObj  user_loan对象
     * @param $firstMoney   第一分期应还金额
     * @param $countMoney   总分期本息总和
     * @return bool
     */
    public function chkCanLoanInstallment($userLoanObj, $interestFee, $term = 3) {
        if (empty($userLoanObj) || !is_object($userLoanObj)) {
            return false;
        }
        //状态
        if ($userLoanObj->status == 8) {
            return false;
        }
        //业务类型
        if (!in_array($userLoanObj->business_type, [1, 4])) {
            return false;
        }
        //28天借款
        if ($userLoanObj->days != 28) {
            return false;
        }
        //后置借款
        if ($userLoanObj->is_calculation === 0) {
            return false;
        }
        //逾期账单
        $date = date('Y-m-d 00:00:00');
        if ($userLoanObj->end_date > $date) {
            return false;
        }
        //已还款金额>=首期还款金额（本金+利息）
        $repayMoney = $userLoanObj->getRepayAmount(2);
        $repayMoney = $repayMoney === NULL ? 0 : $repayMoney;
        $principal = ceil(bcdiv($userLoanObj->amount, $term, 3) * 100) / 100;
        $interest = ceil(bcdiv($interestFee, $term, 3) * 100) / 100;
        if ($repayMoney < bcadd($principal, $interest, 2)) {
            return false;
        }
        //已还款金额>=分期后本息总和（本金+利息）
        if ($repayMoney >= ceil(bcadd($userLoanObj->amount, $interestFee, 3) * 100) / 100) {
            return false;
        }
        return true;
    }

    //在贷转分期，新增user_loan转分期记录
    private function loanInstallmentAdd($userLoanObj, $interestFee, $withdrawFee, $term = 3) {
        if (empty($userLoanObj) || !is_object($userLoanObj)) {
            return false;
        }
        foreach ($userLoanObj as $key => $value) {
            $data[$key] = $value;
        }
        $business_type = $userLoanObj->business_type == 1 ? 5 : 6;
        $end_date = date('Y-m-d 00:00:00', strtotime($userLoanObj->start_date . ' +' . ($userLoanObj->days * $term + 1) . ' day'));
        $data['business_type'] = $business_type;
        $data['parent_loan_id'] = $userLoanObj->parent_loan_id;
        $data['number'] = $userLoanObj->number + 1;
        $data['settle_type'] = 5;
        $data['interest_fee'] = $interestFee; //利息
        $data['withdraw_fee'] = $withdrawFee; //服务费
        $data['like_amount'] = 0; //点赞减息
        $data['chase_amount'] = NULL; //逾期费
        $data['coupon_amount'] = 0; //优惠券金额
        $data['status'] = 9;
        $data['days'] = ($userLoanObj->days * $term);
        $data['start_date'] = $userLoanObj->start_date;
        $data['end_date'] = $end_date;
        unset($data['loan_id']);
        $newLoanId = (new User_loan())->addUserLoanByData($data);
        return $newLoanId;
    }

    /**
     * 不牵扯出款表的驳回
     * @param $loan_id
     * @return bool
     */
    public function tbReject($loan_id) {
        $loan_id = intval($loan_id);
        if (!$loan_id) {
            return false;
        }
        $loanInfo = User_loan::findOne($loan_id);
        if (!$loanInfo) {
            return false;
        }
        $loanExtend = $loanInfo->loanextend;
        if (!$loanExtend) {
            return false;
        }
        if (!in_array($loanInfo->business_type, [1, 4, 5, 6, 9, 10, 11])) {
            return false;
        }
        //开启事务
        $transaction = Yii::$app->db->beginTransaction();
        $extend_condition = [
            'status' => 'REJECT',
            'extend_type' => 3,
        ];
        $extend_result = $loanExtend->updateUserLoanSubsidiary($extend_condition);
        if (!$extend_result) {
            $transaction->rollBack();
            return false;
        }

        $result_loan = $loanInfo->changeStatus(7);
        if (!$result_loan) {
            $transaction->rollBack();
            return false;
        }

        //分期驳回 
        if (in_array($loanInfo->business_type, [5, 6, 11])) {
            $result_goods_bill = (new User_loan())->rejectGoodsBill($loanInfo);
            if (!$result_goods_bill) {
                $transaction->rollBack();
                return false;
            }
        }

        $transaction->commit();
        return true;
    }

    private function numberChange($number) {
        $arr = [
            1 => '一',
            2 => '二',
            3 => '三',
            4 => '四',
            5 => '五',
            6 => '六',
            7 => '七',
            8 => '八',
            9 => '九',
            10 => '十',
            11 => '十一',
            12 => '十二',
        ];
        if (isset($arr[$number])) {
            return $arr[$number];
        }
        return FALSE;
    }

}

<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\ErrorCode;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\Common as Common2;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Fraudmetrix_return_info;
use app\models\news\GoodsOrder;
use app\models\news\No_repeat;
use app\models\news\Payaccount;
use app\models\news\Push_yxl;
use app\models\news\Term;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit_qj;
use app\models\news\User_label;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_rate;
use app\models\news\User_remit_list;
use app\models\news\White_list;
use app\models\service\GoodsService;
use app\models\service\UserloanService;
use Yii;
use yii\web\Response;

class LoanController extends NewdevController {

    public function actionIndex() {
        $this->layout = "new/loanindex";
        $this->getView()->title = "借款";
        // $business_type = !empty($this->get('business_type')) ? $this->get('business_type') : 1;
        $business_type = 1;        
        $user = $this->getUser();
        return $this->redirect("/borrow/loan");
        $userModel = new User();
        $userInfo = $userModel->getUserinfoByUserId($user->user_id);
        if ($this->get('from') != 'repay_list') {
            //判断智融钥匙是否有需要支付的白条
            $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $userInfo->mobile]);
            $userLoanInfo = User_loan::find()->where(['user_id' => $user->user_id, 'status' => [8, 9, 11, 12, 13]])->orderBy('create_time DESC')->one();
            $cgRmit = null;
            if (!empty($userLoanInfo)) {
                $cgRmit = Cg_remit::find()->where(['loan_id' => $userLoanInfo->loan_id, 'remit_status' => 'SUCCESS'])->one();
            }
            if (empty($iousResult)) {
                Logger::dayLog('app/getUseriousinfo', '获取用户白条信息失败', $userInfo->user_id, $iousResult);
            }elseif (!empty($iousResult) && !empty($cgRmit)){
                return $this->redirect("/new/repaylist?from=weixin&user_id_store=" . $userInfo->user_id);
            }
        }
        $userLoanService = new UserloanService();
        //添加用户借款利率角色
        (new User_rate())->getRate($userInfo->user_id);
        if ($business_type == 4) {
            //获取用户担保借款可借额度数组
            if (Keywords::oneTermOpen() == 1) {
                //$noTremAmounts = $userLoanService->getGuaranteeArrayAmount();
                $noTremAmounts = $userLoanService->getCreditArrayAmount($userInfo);
            } else {
                $noTremAmounts = [];
            }

            $amounts = $userLoanService->getList($userInfo, $noTremAmounts, 2);
            $index = 'index_db';
        } else {
            //获取用户信用借款可借额度数组
            if (Keywords::oneTermOpen() == 1) {
                $noTremAmounts = $userLoanService->getCreditArrayAmount($userInfo);
            } else {
                $noTremAmounts = [];
            }

            $amounts = $userLoanService->getList($userInfo, $noTremAmounts, 1);
            $index = 'index_xy';
        }

        //判断一亿元产品中是否有进行中的借款
        $haveinLoanId = (new User_loan())->getHaveinLoan($userInfo->user_id);
        
        $loanInfo = null;
        if (!empty($haveinLoanId)) {
            return $this->redirect("/new/loan/showloan?loan_id=" . $haveinLoanId);
        }
        //判断豆荚贷产品中是否有进行中的借款
        if (!empty($userInfo->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $user->identity]);
        } else {
            $canLoan = true;
        }
        //查询是否有驳回借款
        $reject_loan = (new User_loan())->rejectLoanInfo($userInfo->user_id);
        $reject_data = array('is_reject' => 0, 'reject_data' => array());
        if (!empty($reject_loan)) {
            $reject_data = $userLoanService->loanReject($reject_loan);
            if (!empty($reject_data) && isset($reject_data['reject_data'][1]) && !empty($reject_data['reject_data'][1])) {
                $reject_data['reject_data'][1] = str_replace('\n', '<br/>', $reject_data['reject_data'][1]);
            }
        }

        //获取最大可借额度
        $canMaxAmount = (string) (int) (new Term())->getTremAmountMax($userInfo->user_id, $business_type);
        $amountsKeys = array_keys($amounts['amount']);
        if (!in_array($canMaxAmount, $amountsKeys)) {
            foreach ($amountsKeys as $k => $v) {
                if ($v > (int) $canMaxAmount) {
                    if ($k == 0) {
                        $canMaxAmount = $v;
                        break;
                    } else {
                        $canMaxAmount = (string) $amountsKeys[$k - 1];
                        break;
                    }
                }
            }
        }
        $canMaxAmount = (string) (int) (new Term())->getTremAmountMax($userInfo->user_id, $business_type);

        //获取最大可借额度可选最小天数
        $canMaxDays = $this->getMaxDays($amounts, $canMaxAmount);
        //获取最大可借额度可选最小天数的分期
        $canMaxTerm = $this->getMaxTerm($amounts, $canMaxAmount);
        if ($canMaxDays == 7 && $canMaxTerm == 1) {
            $amountArrs = array_keys($amounts['term_msg_list']);
            $canMaxAmount = $amountArrs[0];
        }
        return $this->render($index, [
                    'canLoan' => $canLoan ? 1 : 2, //1:可借2：不可借
                    'reject_data' => $reject_data,
                    'business_type' => empty($business_type) ? 0 : $business_type,
                    'amounts' => $amounts,
                    'userinfo' => $userInfo,
                    'csrf' => $this->getCsrf(),
                    'loanInfo' => $loanInfo,
                    'noTremAmounts' => $noTremAmounts,
                    'canMaxAmount' => $canMaxAmount,
                    'canMaxDays' => $canMaxDays,
                    'canMaxTerm' => $canMaxTerm,
                    'mList' => $this->getMlist($amounts['money_list']),
        ]);
    }

    /**
     * 发起借款
     * @return string
     */
    public function actionSecond() {
        $this->layout = "new/second";
        $this->getView()->title = "借款确认";
        $post = $this->post();
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        //判断用户有没有开户、绑卡、设置密码
        $isCungan = (new Payaccount())->isCunguan($user->user_id);

        $coupon_id = !empty($this->get('coupon_id')) ? $this->get('coupon_id') : '';
        //判断用户是否是黑名单用户
        if ($userInfo->status == 5) {
            return $this->redirect('/new/account/black');
        }
        $userLoanService = new UserloanService();
        //判断一亿元产品中是否有进行中的借款
        $haveinLoanId = (new User_loan())->getHaveinLoan($userInfo->user_id);
        if (!empty($haveinLoanId)) {
            return $this->redirect("/new/loan/showloan?loan_id=" . $haveinLoanId);
        }
        //获取post提交过来的借款信息
        $desc = isset($post['desc']) ? $post['desc'] : $this->getCookieVal('desc');
        $business_type = isset($post['business_type']) ? $post['business_type'] : $this->getCookieVal('business_type');
        $goods_id = isset($post['goods_id']) ? $post['goods_id'] : $this->getCookieVal('goods_id');
        $amount = isset($post['amount']) ? $post['amount'] : $this->getCookieVal('amount');
        $days = isset($post['days']) ? $post['days'] : $this->getCookieVal('days');
        $trem = isset($post['trem']) ? $post['trem'] : $this->getCookieVal('trem');
        $trem = !empty($trem) ? $trem : 1;
        //把借款信息存到cookie里
        $this->setCookieVal('desc', $desc);
        $this->setCookieVal('business_type', $business_type);
        $this->setCookieVal('goods_id', $goods_id);
        $this->setCookieVal('amount', $amount);
        $this->setCookieVal('days', $days);
        $this->setCookieVal('trem', $trem);
        //流程
        $nextPage = $this->nextPage($userInfo->user_id, 4, 1);
        if ($nextPage['status'] == 1) {
            if ($nextPage['url']) {
                return $this->redirect($nextPage['url']);
            }
            $orderInfo = $nextPage['orderinfo'];
        } else {
            $orderInfo = $nextPage['orderinfo'];
        }
        //获取用户利率及日息
        $rate = (new User_rate())->getUserFee($userInfo->user_id, $days);
        if (empty($rate)) {
            (new User_rate())->getRate($userInfo->user_id);
            $rate = (new User_rate())->getUserFee($userInfo->user_id, $days);
        }
        Logger::dayLog('weixin/loan/rate', $userInfo->user_id, $days, $rate);
        $coupon = new Coupon_list();
        //拉取面向全部用户类型的有效优惠券
        $couponlist_pull = $coupon->pullCoupon($userInfo->mobile);
        //优惠卷列表 1:借款卷
        $couponlist = $coupon->getValidList($userInfo->mobile, $trem, [1, 2, 3, 4]);
        //获取用户出款卡
        $bank = (new User_bank())->limitCardsSort($userInfo->user_id, 0);
        //服务费
        if (isset($rate['withdraw']) && isset($rate['withdraw'][$days])) {
            $rateWithdraw = $rate['withdraw'][$days];
        } else {
            $rateWithdraw = 0.0;
        }
        $withdraw = $amount * $rateWithdraw;
        //利息
        if (isset($rate['interest']) && isset($rate['interest'][$days])) {
            $interestDays = $rate['interest'][$days];
        } else {
            $interestDays = 0.00098;
        }
        if ($trem == 1) {
            $interest = $amount * $interestDays * $days;
        } else {
            $goodsService = new GoodsService();
            $interest = $goodsService->getInstallmentInterestFee($amount, $days, $trem, $interestDays);
        }
        //到手金额
        $getamount = (new UserloanService())->getGetMoney($userInfo, $amount, $withdraw, $trem);
        //还款计划
        if ($coupon_id == '') {
            if (empty($couponlist)) {
                $coupon_id = '';
            } else {
                $coupon_id = $couponlist[0]['id'];
            }
        } else {
            $coupon_id = $coupon_id;
        }
        $coupon_amount = 0;
        $couponInfo = '';
        if (!empty($coupon_id)) {
            $couponInfo = $coupon->getCouponById($coupon_id);
            $coupon_amount = $couponInfo->val;
        }
        $repay_plan = $userLoanService->getReayPlan($userInfo, $amount, $trem, $days, $coupon_id, sprintf('%.2f', $withdraw), sprintf('%.2f', ceil($interest * 100) / 100));
        $bankArr = [];
        if (!empty($bank)) {
            $bankArr = [
                'bank_id' => $bank[0]['id'],
                'type' => trim($bank[0]['bank_name'], " "),
                'card' => substr($bank[0]['card'], strlen($bank[0]['card']) - 4, 4),
                'bank_abbr' => $bank[0]['bank_abbr'],
                'bank_icon_url' => $this->getImageUrl($bank[0]['bank_abbr']),
                'default_card' => $bank[0]['default_bank'] == 0 ? 2 : $bank[0]['default_bank'],
            ];
        }
        //用户绑定的所有银行卡
        $banks = (new User_bank())->limitCardsSort($userInfo->user_id, 0, 0, 1);
        $userBanks = [];
        if (!empty($banks)) {
            foreach ($banks as $k => $v) {
                $userBanks[$k]['bank_id'] = $v['id'];
                $userBanks[$k]['type'] = trim($v['bank_name'], " ");
                $userBanks[$k]['card'] = substr($v['card'], strlen($v['card']) - 4, 4);
                $userBanks[$k]['bank_abbr'] = $v['bank_abbr'];
                $userBanks[$k]['bank_icon_url'] = $this->getImageUrl($v['bank_abbr']);
                $userBanks[$k]['default_card'] = $v['default_bank'] == 0 ? 2 : $v['default_bank'];
            }
        }
        //是否只有一张卡并且被限制
        $flag = 1;
        if (count($bank) == 1 && $bank[0]['sign'] == 1) {
            $flag = 2;
        }
        $jsinfo = $this->getWxParam();
        $isopenBank = Keywords::isOpenBank();
        return $this->render('confirm', [
                    'desc' => $desc,
                    'days' => $days,
                    'amount' => sprintf('%.2f', $amount),
                    'couponlist' => $couponlist,
                    'couponInfo' => $couponInfo,
                    'bank' => $bankArr,
                    'term' => $trem,
                    'withdraw' => sprintf('%.2f', $withdraw),
                    'interest' => sprintf('%.2f', sprintf('%.3f', ceil($interest * 100) / 100)),
                    'getamount' => sprintf('%.2f', sprintf('%.3f', ceil($getamount * 100) / 100)),
                    'repay_plan' => $repay_plan,
                    'orderinfo' => $orderInfo,
                    'coupon_id' => $coupon_id,
                    'userinfo' => $userInfo,
                    'coupon_amount' => $coupon_amount,
                    'flag' => $flag,
                    'business_type' => $business_type,
                    'goods_id' => $goods_id,
                    'mkloan' => $this->get('mkloan'),
                    'isCungan' => $isCungan,
                    'csrf' => $this->getCsrf(),
                    'jsinfo' => $jsinfo,
                    'mark' => 2,
                    'userBanks' => $userBanks,
                    'isopenBank' => $isopenBank,
        ]);
    }

    /*
     * 借款卷
     * */

    public function actionJgcoupon() {
        $this->getView()->title = "借款卷";
        $couponlist = '';
        $userInfo = $this->getUser();
        //优惠卷列表 1:借款卷
        $coupon = new Coupon_list();
        $couponlist = $coupon->getValidList($userInfo->mobile, 1, [1, 2, 3, 4]);
        return $this->render('jgcoupon', [
                    'couponlist' => $couponlist,
        ]);
    }

    /**
     * AJAX判断用户是否是黑名单
     */
    public function actionIsblack() {
        $userinfo = $this->getUser();
        if ($userinfo->status == 5) {
            echo $this->showMessage(1, '*用户是黑名单用户', 'json');
            exit;
        }
        echo $this->showMessage(0, '*用户不是黑名单用户', 'json');
    }

    public function actionUserloan() {
        $amount = $this->post('amount'); //金额
        $days = $this->post('days'); //天数
        $bankId = $this->post('bank_id'); //银行卡ID
        $term = $this->post('term'); //分期期数
        $goodsId = $this->post('goods_id'); //商品ID
        $couponId = $this->post('coupon_id'); //优惠卷ID
        $couponVal = $this->post('coupon_amount'); //优惠卷金额
        $businessType = empty($this->post('business_type')) ? 1 : Yii::$app->request->post('business_type'); //借款类型
        $desc = $this->post('desc');
        $source = 1; //借款来源
        if (empty($amount) || empty($days) || empty($bankId) || empty($term) || empty($goodsId) || empty($businessType)) {
            $array = $this->errorreback('99994');
            $array['url'] = $this->getUrlByCode('99994');
            echo json_encode($array);
            exit;
        }
        $userinfo = $this->getUser();
        if (empty($userinfo)) {
            $array = $this->errorreback('10001');
            $array['url'] = $this->getUrlByCode('10001');
            echo json_encode($array);
            exit;
        }
        $user = User::findOne($userinfo->user_id);
        $bankObj = User_bank::findOne($bankId);
        if (empty($bankObj)) {
            $array = $this->errorreback('10043');
            $array['url'] = $this->getUrlByCode('10043', $userinfo->user_id);
            echo json_encode($array);
            exit;
        }

        //判断用户有没有开户、绑卡、设置密码、还款授权、缴费授权
        $isCungan = (new Payaccount())->isCunguan($user->user_id);
        if ($isCungan['isOpen'] != 1 || $isCungan['isCard'] != 1 || $isCungan['isPass'] != 1) {
            $array = $this->errorreback('10210');
            $array['url'] = '/new/loan/second';
            echo json_encode($array);
            exit;
        }

        if ($isCungan['isAuth'] != 1) {//四合一授权
            $array = $this->errorreback('10210');
            $array['url'] = '/new/loan/second';
            echo json_encode($array);
            exit;
        }

        //检测是否允许借款
        $loanCode = $this->checkCanLoan($user);
        if ($loanCode != '0000') {
            $array = $this->errorreback($loanCode);
            $array['url'] = $this->getUrlByCode($loanCode, $userinfo->user_id);
            echo json_encode($array);
            exit;
        }

        //监测数据是否合法
        $code = $this->checkLoanField($user, $amount, $days, $bankObj, $couponId, $couponVal, $businessType, $source, $term);
        if ($code != '0000') {
            $array = $this->errorreback($code);
            $array['url'] = $this->getUrlByCode($code);
            echo json_encode($array);
            exit;
        }

        $transaction = Yii::$app->db->beginTransaction();
        $loaninfo = $this->addLoan($user, $amount, $days, $bankObj, $couponId, $couponVal, $businessType, $source, '', $term, $goodsId, $desc);
        if ($loaninfo['rsp_code'] == '0000') {
            if ($loaninfo['data']['status'] == 6 && $loaninfo['data']['prome_status'] == 5 && $loaninfo['buy_mark'] == 1) {
                $yxArray = [
                    'mobile' => $user->mobile,
                    'amount' => $amount,
                    'loan_id' => $loaninfo['data']['loan_id']
                ];
                $yxResult = (new Apihttp())->postYxLoanInfo($yxArray);
                if ($yxResult['rsp_code'] != '0000') {
                    Logger::dayLog('weixin/loan/userloan', '同步至有信令loan_id失败', $yxArray, $yxResult);
                    $transaction->rollBack();
                    $array = $this->errorreback('10118');
                    $array['url'] = $this->getUrlByCode('10118');
                    echo json_encode($array);
                    exit;
                }
            } else if ($loaninfo['data']['status'] == 6 && $loaninfo['data']['prome_status'] == 5 && $loaninfo['buy_mark'] == 0) {
                $yxl_condition = [
                    'user_id' => $loaninfo['data']->user_id,
                    'loan_id' => $loaninfo['data']->loan_id,
                    'loan_status' => 3,
                    'type' => 1,
                ];
                $oPushModel = new Push_yxl();
                $yxl_res = $oPushModel->saveYxlInfo($yxl_condition);
                if (!$yxl_res) {
                    Logger::dayLog('weixin/loan/userloan', '添加推送智融钥匙失败', $yxl_condition, $yxl_res);
                    $transaction->rollBack();
                    $array = $this->errorreback('10118');
                    $array['url'] = $this->getUrlByCode('10118');
                    echo json_encode($array);
                    exit;
                }
                $push_res = $oPushModel->createLoanNobuy($loaninfo['data']);
                if (!$push_res) {
                    Logger::dayLog('weixin/loan/userloan', '同步至有信令loan_id失败', $yxl_condition, $push_res);
                    $transaction->rollBack();
                    $array = $this->errorreback('10118');
                    $array['url'] = $this->getUrlByCode('10118');
                    echo json_encode($array);
                    exit;
                }
            }
            $transaction->commit();
            $array = $this->errorreback($loaninfo['rsp_code']);
            $array['url'] = $this->getUrlByCode($loaninfo['rsp_code']);
            echo json_encode($array);
            exit;
        } else {
            $transaction->rollBack();
            $array = $this->errorreback($loaninfo['rsp_code']);
            $array['url'] = $this->getUrlByCode($loaninfo['rsp_code']);
            echo json_encode($array);
            exit;
        }
    }

    /**
     * 获取nextPage
     * @param int $user_id
     * @return string
     */
    private function nextPage($user_id, $from, $type) {
        $UserModel = new User();
        $order = $UserModel->getPerfectOrder($user_id, $from, $type);
        $nextPage = $order['nextPage'];
        $orderJson = (new Common2())->create3Des(json_encode($order, true));
        if ($nextPage != '') {
            $str = substr($nextPage, strrpos($nextPage, '/') + 1);
            if (strpos($str, "?")) {
                $url = $nextPage . '&orderinfo=' . urlencode($orderJson);
                return [
                    'status' => 1,
                    'url' => $url,
                    'orderinfo' => urlencode($orderJson)
                ];
            } else {
                $url = $nextPage . '?orderinfo=' . urlencode($orderJson);
                return [
                    'status' => 1,
                    'url' => $url,
                    'orderinfo' => urlencode($orderJson)
                ];
            }
        } else {
            return [
                'status' => 0,
                'orderinfo' => urlencode($orderJson)
            ];
        }
    }

    /**
     * 借款记录
     */
    public function actionLoanlist() {
        $this->getView()->title = "借款记录";
        $this->layout = 'loan';
        $userinfo = $this->getUser();
        //判断用户的性别
        $card_length = strlen($userinfo->identity);
        $sex = $card_length == 15 ? substr($userinfo->identity, 14) : substr($userinfo->identity, 16, 1);
        //取出用户所有借款订单
        $loanlist = User_loan::find()->where(['user_id' => $userinfo->user_id])->orderBy('create_time desc')->all();
        if (!empty($loanlist)) {
            foreach ($loanlist as $key => $value) {
                //9已出款；12还款异常(未还款逾期)；13 还款异常(部分还款 逾期)；
                $loan_status = array(9, 12, 13);
                if (in_array($value->status, $loan_status)) {
                    $loanlist[$key]['shareurl'] = $this->loanCoupon($value->business_type, $value['loan_id']);
                } else {
                    $loanlist[$key]['shareurl'] = '';
                }
                $loanlist[$key]['status'] = $value->prome_status == 1 ? 5 : $value->status;
                if ($loanlist[$key]['status'] == 9) {
                    $remit = User_remit_list::find()->where(['loan_id' => $value->loan_id])->orderBy('create_time desc')->one();
                    if (!empty($remit) && $remit->remit_status != 'SUCCESS') {
                        $loanlist[$key]['status'] = 6;
                    }
                }
                //判断借款初次发生时间
                if ($value->loan_id != $value->parent_loan_id && !empty($value->parent_loan_id)) {
                    $loanlist[$key]['create_time'] = $value->start_date;
                }
            }
        }

        //return $this->render('list', ['loanlist' => $loanlist, 'sex' => $sex]);
    }

    /**
     * 借款对应的优惠券
     * @param unknown $business_type   1:好友;2:好人卡;3:担保人',
     * @param unknown $loan_id         借款id
     * @return boolean|string
     */
    private function loanCoupon($business_type, $loan_id) {
        if (empty($business_type) || empty($loan_id))
            return false;
        $time = time();
        //判断用户优惠券
        $coupon_list_info = new Coupon_list();
        $loan_coupon = $coupon_list_info->getLoanCoupon($loan_id);
        $shareurl = '';
        //val:面值：0表示全免
        //status:2表示已使用
        if (!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) {
            $shareurl = "/new/loan/succ?l=" . $loan_id;
        } else {
            //business_type:1:好友;2:担保;3:担保人
            if ($business_type == 1) {
                $shareurl = "/new/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id);
            } else {
                $shareurl = "/new/loan/succ?l=" . $loan_id;
            }
        }
        return $shareurl;
    }

    /**
     * 借款决策
     * @return Response
     */
    public function actionLoanrules() {
        $userinfo = $this->getUser();
        $norepet = (new No_repeat())->norepeat($userinfo->user_id, $type = 1);
        if (!$norepet) {
            echo "<script>alert('操作频繁，稍后再试');window.location.href='/new/loan'</script>";
            exit;
        }
        $type = 9;
        $orderinfo = $this->get('orderinfo');
        $nextpage = $this->getNextpage($orderinfo, $type);
        $user = $this->getUser();
        $loan_no_keys = $user->user_id . "_loan_no";
        $loan_no = $this->getRedis($loan_no_keys);
        $desc = $this->getCookieVal('desc');
        $days = $this->getCookieVal('days');
        $amount = $this->getCookieVal('amount');
        $coupon_id = $this->getCookieVal('coupon_id');
        $coupon_amount = $this->getCookieVal('coupon_amount');
        $business_type = empty($this->getCookieVal('business_type')) ? 1 : $this->getCookieVal('business_type');
        if (!empty($loan_no)) {
            return $this->redirect($nextpage . '?orderinfo=' . $orderinfo);
        }
        $suffix = $user->user_id . rand(100000, 999999);
        $loan_no = date("YmdHis") . $suffix;
        //$this->setRedis($loan_no_keys,$loan_no);
        Yii::$app->redis->setex($loan_no_keys, 43200, $loan_no); //修改loan_no 有效时间为12小时
        $loanModel = new User_loan();
        $whiteModel = new White_list();
        $apiHttp = new Apihttp();
        $credit = $apiHttp->getUserCredit(['mobile' => $userinfo->mobile]);
        if (isset($credit['rsp_code']) && $credit['rsp_code'] == '0000' && $credit['user_credit_status'] == 5) {
            return $this->redirect($nextpage . '?orderinfo=' . $orderinfo);
        }
        if ($whiteModel->isWhiteList($user->user_id)) {
            $result = $loanModel->getRule($user, 1, $amount, $days, $desc, $loan_no, $business_type);
            return $this->redirect($nextpage . '?orderinfo=' . $orderinfo);
        }
        $rate_setting = (new User_rate())->getrateone($user->user_id, $days);
        $dayratestr = $rate_setting['interest'];
        $with_fee = $rate_setting['rate'];
        $result = $loanModel->getRule($user, 1, $amount, $days, $desc, $loan_no, $business_type);
        if ($result == 1) {
            $userLoanModel = new User_loan();
            $result = $userLoanModel->_addRejectLoan($user, $loan_no, $amount, $days, $desc, 3, 0, $coupon_id, $coupon_amount, 5, 0, $business_type, $dayratestr, $with_fee);

            $this->delRedis($loan_no_keys);
            $nextpage = $this->getNextpage($orderinfo, $type, 1);
            return $this->redirect($nextpage);
        } elseif ($result == 2) {//拉黑之后的操作
            $user->setBlack();
            $nextpage = $this->getNextpage($orderinfo, $type, 1);
            return $this->redirect($nextpage);
        }
        return $this->redirect($nextpage . '?orderinfo=' . urlencode($orderinfo));
    }

    public function actionHuanindex() {
        return $this->render('huanindex');
    }

    public function actionShowloan() {
        $this->layout = "new/showloan";
        $this->getView()->title = "借款";
        $loan_id = $this->get('loan_id');
        if (!$loan_id) {
            exit('借款不存在');
        }
        //Yii::$app->redis->del($loan_id);
        $user = $this->getUser();
        $loanInfo = User_loan::find()->where(['loan_id' => $loan_id, 'user_id' => $user->user_id])->one();
        if (!$loanInfo) {
            exit('借款不存在');
        }
        if ($loanInfo->status == 8) {
            return $this->redirect('/new/loanrecord/creditdetails?loan_id=' . $loanInfo->loan_id);
        }
        $jsinfo = $this->getWxParam();
        $userLoanService = new UserloanService();
        $info = $userLoanService->getLoanDetaile($loan_id);
        if ($info['rsp_code'] != '0000') {
            exit($this->getErrorMsg($info['rsp_code']));
        }
        $info['user_info'] = $user;
        $info['jsinfo'] = $jsinfo;
        $info['loan_coupon'] = $loanInfo->couponUse;
        $info['loan_id'] = $loanInfo->loan_id;
        $info['shareUrl'] = Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . time() . "&d=" . $loan_id . "&s=" . md5(time() . $loan_id);
        $info['csrf'] = $this->getCsrf();
        $info['encodeUserId'] = $user->user_id;

        return $this->render('showloan', $info);
    }

    /**
     * 获取msg
     * @param $code
     * @param string $msg
     * @return mixed
     */
    private function errorreback($code, $msg = '') {
        $errorCode = new ErrorCode();
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = !empty($msg) ? $msg : $errorCode->geterrorcode($code);
        return $array;
    }

    /**
     * 根据code获取跳转url
     * @param $code
     * @param int $userId
     * @return string|Response
     */
    private function getUrlByCode($code, $userId = 0) {
        if (empty($code)) {
            return '/new/loan';
        }
        switch ($code) {
            case '99991':
                $url = '/new/loan/second';
                break;
            case '10097':
                $url = '/new/account/black';
                break;
            case '10050':
                $User_loan = (new User_loan())->getHaveinLoan($userId);
//                $url = '/new/loan/showloan?loan_id='.$User_loan;
                return $this->redirect("/new/loan/showloan?loan_id=" . $User_loan);
                break;
            default:
                $url = '/new/loan';
                break;
        }
        return $url;
    }

    /**
     * 获取csrf
     * @return string
     */
    public function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 获取银行log图片地址
     * @param $abbr
     * @return string
     */
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
        return "http://mp.yaoyuefu.com/images/bank_logo/" . $abbr_url . ".png";
    }

    /**
     * 获取可借最大天数
     * @param $amounts
     * @param $canMaxAmount
     * @return int|mixed
     */
    private function getMaxDays($amounts, $canMaxAmount) {
        if ($canMaxAmount == 0) {
            return 7;
        }
        $canDays = [];
        foreach ($amounts['amount'][$canMaxAmount] as $k => $v) {
            if ($v['enabled'] == 1) {
                $canDays[] = $v['days'];
            }
        };
        if (empty($canDays)) {
            return 7;
        }

        return $canDays[0];
    }

    /**
     * 获取可借最大周期
     * @param $amounts
     * @param $canMaxAmount
     * @return int|mixed
     */
    private function getMaxTerm($amounts, $canMaxAmount) {
        if ($canMaxAmount == 0) {
            return 1;
        }
        $canTerm = [];
        foreach ($amounts['amount'][$canMaxAmount] as $k => $v) {
            if ($v['enabled'] == 1) {
                $canTerm[] = $v['term'];
            }
        };
        if (empty($canTerm)) {
            return 1;
        }
        return $canTerm[0];
    }

    /**
     * 获取可借金额数组
     * @param $amounts
     * @return array
     */
    private function getMlist($amounts) {
        $mList = [];
        foreach ($amounts as $k => $v) {
            $mList[] = $v['money'];
        }
        return $mList;
    }

    /**
     * 检测是否允许借款
     * @param $userObj  用户对象
     * @return string
     */
    private function checkCanLoan($userObj) {
        if (!is_object($userObj) || empty($userObj)) {
            return '10001';
        }

        //用户状态判断
        if ($userObj->status == 5) {
            return '10097';
        }

        //连点
        $norepet = (new No_repeat())->norepeat($userObj->user_id, $type = 2);
        if (!$norepet) {
            return '99991';
        }

        $loan_info = new User_loan();
        //判断是否存在借款
        $loan = $loan_info->getHaveinLoan($userObj->user_id);
        if ($loan !== 0) {
            return '10050';
        }

        //判断是否存在驳回订单
        $judgment = $loan_info->LoanJudgment($userObj->user_id);
        if (!$judgment) {
            return '10098';
        }

        //判断7-14产品中是否有进行中的借款
        if (!empty($userObj->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $userObj->identity]);
            if (!$canLoan) {
                return '99990';
            }
        }
        return '0000';
    }

    /**
     * 监测借款数据是否合法
     * @param $user 用户对象
     * @param $amount   借款金额
     * @param $days 借款天数
     * @param $bank 银行卡对象
     * @param $coupon_id    优惠券id
     * @param int $coupon_val   优惠券金额
     * @param $business_type    借口类型
     * @return string
     */
    private function checkLoanField($userObj, $amount, $days, $bankObj, $coupon_id, $coupon_val = 0, $business_type, $source, $term) {
        if (!is_object($userObj) || empty($userObj)) {
            return '10001';
        }
        if (!is_object($bankObj) || empty($bankObj)) {
            return '10043';
        }
        if ($term == 1) {
            //最大额度限制
//            $max_amount = $userObj->getUserLoanAmount($userObj, $type = 3);
            $max_amount = 3000;
        } else {
            //最大额度限制
            $max_amount = (new Term())->getTremAmountMax($userObj->user_id, 1);
        }
        if (intval($amount) < 500 || intval($amount) > $max_amount || intval($amount) % 500 != 0) {
            return '10048';
        }
        if (intval($days) < 7 || intval($days) > 336 || intval($days) % 7 != 0) {
            return '10048';
        }

        if ($userObj->status != 3) {
            return '10023';
        }
        if (in_array($source, [1, 2, 3, 4]) && ($userObj->extend->company == '' || $userObj->extend->telephone == '')) {
            return '10047';
        }
        if ($userObj->pic_identity == '' || ($userObj->pic_identity != '' && $userObj->status == '4')) {
            return '10047';
        }
        if ($bankObj->user_id != $userObj->user_id) {
            return '10044';
        }
        $coupon = '';
        if (!empty($coupon_id)) {
            $coupon = Coupon_list::findOne($coupon_id);
        }
        if (!empty($coupon)) {
            if (($coupon->mobile != $userObj->mobile) || $coupon->status != 1 || ($coupon->val != $coupon_val)) {
                return '10049';
            }
        }

        $isOpen = (new Payaccount())->getPaysuccessByUserId($userObj->user_id, 2, 1);
        $isPassword = (new Payaccount())->getPaysuccessByUserId($userObj->user_id, 2, 2);
        if (empty($isOpen) || empty($isPassword)) {
            return '10210';
        }
        if ($isOpen->card != $bankObj->id) {
            return '10211';
        }
        return '0000';
    }

    /**
     * 生产借款
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
    private function addLoan($userObj, $amount, $days, $bankObj, $coupon_id, $coupon_val, $business_type, $source = 3, $uuid, $term, $goods_id, $desc = '个人或家庭消费') {
        if (!is_object($userObj) || empty($userObj)) {
            return ['rsp_code' => '10001'];
        }
        if (!is_object($bankObj) || empty($bankObj)) {
            return ['rsp_code' => '10043'];
        }
        //分期开关判断
        $userTerm = ( new Term())->getTremByUserId($userObj->user_id);
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
        $feeOpen = Keywords::feeOpen();
        $type = 2;
        if ($feeOpen == 2) {
            $type = 3;
        }
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
        $loanfee = $loanModel->loan_Fee_new($amount, $days, $userObj->user_id, $term);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        $fee = $loanfee['fee'] * 100;

        //是否为系统指定后置用户
        $charge = (new User_label())->isChargeUser($userObj->mobile);
        if ($charge === false) {
            $charge = 1;
        } else {
            $charge = 0;
        }
        $condition = array(
            'user_id' => $userObj->user_id,
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
        $buy_mark = 0;
        $apiHttp = new Apihttp();
        $credit = $apiHttp->getUserCredit(['mobile' => $userObj->mobile]);
        if (isset($credit['rsp_code']) && $credit['rsp_code'] == '0000' && !empty($credit['user_credit_status'])) {
            if (in_array($credit['user_credit_status'], [3, 4])) {
                return ['rsp_code' => '10116'];
            }
            if ($credit['user_credit_status'] == 7) {
                return ['rsp_code' => '10212'];
            }
            //评测驳回
            if ($credit['user_credit_status'] == 2 && !empty($credit['credit_invalid_time'])) {
                $borrowing = (new User_loan())->getBorrowingByTime($userObj->user_id, $credit['credit_invalid_time']);
                if (!$borrowing) {
                    return ['rsp_code' => '10121'];
                }
            }
            //退卡
            if ($credit['user_credit_status'] == 8) {
                return ['rsp_code' => '10122'];
            }
            if ($credit['user_credit_status'] == 5) {
                if ($credit['order_amount'] != $amount) {
                    return ['rsp_code' => '10117'];
                }
                $buy_mark = 1;
                $condition['status'] = 6;
                $condition['prome_status'] = 5;
            }
        }
        $verify_auth = 0;

        if ($buy_mark != 1) {
            $condition['status'] = 3;
            $condition['prome_status'] = 1;
        }
        if ($amount == 500 && $days == 7) {
            $qj_credit = (new User_credit_qj())->getByIdentity($userObj->identity);
            if (!empty($qj_credit)) {
                $condition['status'] = 6;
                $condition['prome_status'] = 5;
                $verify_auth = 1;
            }
        }
        //借款决策
        $loan_no_keys = $userObj->user_id . "_loan_no";
        $loan_no = Yii::$app->redis->get($loan_no_keys);
        if (!empty($loan_no)) {
            $condition['loan_no'] = (string) $loan_no;
        } else {
            $condition['status'] = 3;
            $condition['prome_status'] = 1;
        }
        //白名单
        $whiteModel = new White_list();
        $white = $whiteModel->isWhiteList($userObj->user_id);
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
        Logger::dayLog('weixin/loan/addLoan', '添加userloan', $condition, $ret); //@todo 监测使用，后期请删除
        Yii::$app->redis->del($loan_no_keys);
        if (empty($ret)) {
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
            $couponUseModel->addCouponUse($userObj, $coupon_id, $loan->loan_id);
        }
        if (in_array($loan->status, [5, 6])) {
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
            if ($buy_mark == 1) {
                $extend['status'] = 'AUTHED';
            }
            if ($verify_auth) {
                $extend['status'] = 'TB-SUCCESS';
            }
            $extendId = $loanextendModel->addList($extend);
            if (empty($extendId)) {
                Logger::dayLog('weixin/loan/addLoan', '添加userloanextend失败', 'loan_id：' . $loan->loan_id, $extend);
                return ['rsp_code' => '10051'];
            }
        }
        if ($term > 1) {
            $goodsOrderModel = new GoodsOrder();
            $goodsService = new GoodsService();
            $order_id = $goodsService->createOrderId($loan->loan_id, $userObj->identity);
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
        return ['rsp_code' => '0000', 'data' => $loan, 'buy_mark' => $buy_mark];
    }

    public function actionGetcanloan() {
        $csrf = $this->post('csrf');
        $amount = $this->post('amount', 0);
        if (empty($amount)) {
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '请10分钟后再发起借款']);
        }
        $user = $this->getUser();
        $userModel = new User();
        $userInfo = $userModel->getUserinfoByUserId($user->user_id);
        $apiHttp = new Apihttp();
        $result = $apiHttp->getUserCredit(['mobile' => $userInfo->mobile]);
        if ($result['rsp_code'] !== '0000') {//请求失败
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '请10分钟后再发起借款']);
        }
        if ($result['user_credit_status'] == 5 && $result['order_amount'] == $amount) {//已购买，额度相等
            return json_encode(['rsp_code' => '0000', 'rsp_msg' => '']);
        }
        if (in_array($result['user_credit_status'], [1, 6])) {//未评测，评测已过期
            return json_encode(['rsp_code' => '0000', 'rsp_msg' => '']);
        }
        if ($result['user_credit_status'] == 2 && !empty($result['credit_invalid_time'])) {//评测驳回
            $borrowing = (new User_loan())->getBorrowingByTime($user->user_id, $result['credit_invalid_time']);
            if (!$borrowing) {
                return json_encode(['rsp_code' => '1000', 'rsp_msg' => '请24小时后重试']);
            }
            return json_encode(['rsp_code' => '0000', 'rsp_msg' => '']);
        }
        return json_encode(['rsp_code' => '1000', 'rsp_msg' => '请10分钟后再发起借款']);
    }

}

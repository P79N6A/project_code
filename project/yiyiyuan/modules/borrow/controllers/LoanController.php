<?php

namespace app\modules\borrow\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\ErrorCode;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Common as Common2;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Insurance;
use app\models\news\Juxinli;
use app\models\news\No_repeat;
use app\models\news\Payaccount;
use app\models\news\PayAccountExtend;
use app\models\news\Push_yxl;
use app\models\news\RenewalInspect;
use app\models\news\ScanTimes;
use app\models\news\TemQuota;
use app\models\news\User_password;
use app\commonapi\ImageHandler;
use app\models\news\Term;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_loan_flows;
use app\models\news\UserCreditList;
use app\models\news\User_label;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_rate;
use app\models\news\GoodsBill;
use app\models\news\User_remit_list;
use app\models\news\White_list;
use app\models\service\GoodsService;
use app\models\service\StageService;
use app\models\service\UserloanService;
use Yii;
use yii\web\Response;
use app\models\news\PayAccountError;

class LoanController extends BorrowController {

    /**
     * 首页 310
     * @return type
     */
    public function actionIndex() {
        $this->getView()->title = "信用借款";
        $this->layout = 'loan';
        $user = $this->getUser();
        $user_id = $user->user_id;
        $reject_data = array('is_reject' => 0, 'reject_data' => array());
        //进场展期申请中
        if (Keywords::renewalInspectOpen() == 2) {
            $o_renewal_inspect = (new RenewalInspect())->getByUserIdAndStatus($user_id, [0, 3]);
            if (!empty($o_renewal_inspect)) {
                return $this->renewalInspect($o_renewal_inspect);
            }
        }

        //查询是否有驳回评测
        $reject_credit = (new User_credit())->getCreditReject($user->user_id);
        if (!empty($reject_credit)) {
            $reject_data = $reject_credit;
        }

        //智融钥匙待支付的账单
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $user->mobile]);
        //一亿元(待还款借款)
        $userLoanInfo = (new User_loan())->getLoan($user->user_id, [9, 11, 12, 13], [1, 4, 5, 6, 11]);
        if ((!empty($userLoanInfo) && ((!empty($userLoanInfo) && ($userLoanInfo->status == 9 && !empty($userLoanInfo->loanextend) && $userLoanInfo->loanextend->status == 'SUCCESS')) || in_array($userLoanInfo->status, [11, 12, 13])))) {
            return $this->loanBill($userLoanInfo, $iousResult);
        } elseif (!empty($userLoanInfo) && in_array($userLoanInfo->status, [9, 11, 12, 13]) && $userLoanInfo->settle_type == 3) {
            return $this->loanBill($userLoanInfo, $iousResult);
        } elseif (!empty($iousResult)) {
            return $this->loanBill($userLoanInfo, $iousResult); // 有待支付的白条且一亿元也处于账单页
        }

        //一亿元进行中或带发起的借款（进入借款状态系列页）
        $userLoaningInfo = (new User_loan())->getLoan($user->user_id, [5, 6, 9], [1, 4, 5, 6]);
        if (!empty($userLoaningInfo) && (in_array($userLoaningInfo->status, [5, 6]) || ($userLoaningInfo->status == 9 && ((!empty($userLoanInfo->cgRemit) && $userLoaningInfo->cgRemit->remit_status != 'SUCCESS')) || empty($userLoanInfo->cgRemit)))) {
            return $this->loanIndex($userLoaningInfo);
        }
        

        //进入评测状态系列页
        $creditModel = new User_credit();
        $credit = $creditModel->checkYyyUserCredit($user_id);
        if ((!empty($credit) && $credit['user_credit_status'] != 1)) {
            return $this->showcredit($credit, $user_id, $reject_data);
        }
        
        //进入未评测首页
        return $this->loanNoamount();

    }

    /**
     * 借款详情系列页面
     */
    private function loanIndex($userLoaningInfo) {

        $this->getView()->title = "信用借款";
        $this->layout = 'loan/loancredit';
        $user = $this->getUser();
        $user_id = $user->user_id;
        //Yii::$app->redis->del($userLoaningInfo->loan_id);
        //1:审核中 2：待激活 3：放款中 4：待提现 5:提现中      
        $jsinfo = $this->getWxParam();
        $userLoanService = new UserloanService();
        $info = $userLoanService->getLoanDetaile($userLoaningInfo->loan_id);
        if ($info['rsp_code'] != '0000') {
            exit($this->getErrorMsg($info['rsp_code']));
        }
        $page_status = $this->getLoanStatus($info['status']); //1:审核中 2：待激活 3：放款中 4：待提现 5:提现中
        Logger::dayLog('api/loan/userloan','借款数据weixin',$info['status'],$page_status);
        if ($page_status == 1) {
            return $this->getLoanwait();
        }
        $time_fk = 0;
        if ($page_status == 3) {
            $flowsModel = (new User_loan_flows())->find()->where(['loan_id' => $userLoaningInfo->loan_id, 'loan_status' => 6])->one();
            $time_fk = !empty($flowsModel) ? $flowsModel->create_time : 0;
        }

        $tixian_fail = 0; //0:无弹窗 1：网络延迟弹窗  (00000000:提现成功 CI68 CI73：存管卡不支持提现 txcgfail001：存管卡无法解绑 其余：网络延迟无法提现)
        $error_id = 0;
        if (in_array($page_status, [4, 5])) { //提现中  网络延迟弹窗
            $toast_result = $this->getPayAccountErrorResult($user_id, 4);
            $tixian_fail = $toast_result['tixian_result'];
            $error_id = $toast_result['error_id'];
        }
        $info['period'] = 1;
        if(in_array($userLoaningInfo->business_type,[5,11])){
            $goodsbill = $userLoaningInfo->goodsbills;
            if(!empty($goodsbill) && !empty($goodsbill[0])){
                $info['days'] = $goodsbill[0]['days'];
                $info['period'] = $goodsbill[0]['number'];
            }
        }
        $info['loan_amount'] = number_format($info['loan_amount'], 0, '.', ',');
        $info['user_info'] = $user;
        $info['page_status'] = $page_status;
        $info['jsinfo'] = $jsinfo;
        $info['loan_coupon'] = $userLoaningInfo->couponUse;
        $info['loan_id'] = $userLoaningInfo->loan_id;
        $info['csrf'] = $this->getCsrf();
        $info['user_id'] = $user_id;
        $info['time_fk'] = $time_fk;
        $info['tixian_fail'] = $tixian_fail;
        $info['error_id'] = $error_id;
        return $this->render('showloan', $info);
    }

    /**
     * 提现结果弹窗
     * pay_accout_error:res_code (00000000:提现成功 CI68 CI73：存管卡不支持提现 txcgfail001：存管卡无法解绑 其余：网络延迟无法提现)
     * @param type $user_id
     * @param type $cate 弹窗分类
     * @param type $type 6：表示提现
     * @return type
     */
    private function getPayAccountErrorResult($user_id, $cate, $type = 6) {
        $tixian_result = 0;
        $error_id = 0;
        $time = 0;
        $res = PayAccountError::find()->where(['user_id' => $user_id, 'type' => $type, 'status' => 0])->orderBy('id desc')->one();
        if ($cate == 1) { //提现成功弹窗
            if ($res && in_array($res->res_code, ['00000000'])) {
                $tixian_result = 1;
                $error_id = $res->id;
            }
        }
        if ($cate == 2) { //存管卡不支持提现弹窗
            if ($res && in_array($res->res_code, ['CI68', 'CI73'])) {
                $tixian_result = 1;
                $error_id = $res->id;
            }
        }
        if ($cate == 3) { //存管卡无法解绑弹窗
            if ($res && in_array($res->res_code, ['txcgfail001'])) {
                $tixian_result = 1;
                $error_id = $res->id;
            }
        }
        if ($cate == 4) { //网络加载延迟弹窗
            if ($res && !in_array($res->res_code, ['00000000', 'CI68', 'CI73', 'txcgfail001', 'fivedayover'])) {
                $tixian_result = 1;
                $error_id = $res->id;
            }
        }
        if ($cate == 5) { //超过5天未提现弹窗
            if ($res && in_array($res->res_code, ['fivedayover'])) {
                $tixian_result = 1;
                $error_id = $res->id;
                $time = $res->create_time;
            }
        }
        return ['tixian_result' => $tixian_result, 'error_id' => $error_id, 'time' => $time];
    }

    /**
     * 1:审核中 2：待激活 3：放款中 4：待提现 5:提现中
     * @param type $status
     * @return int
     */
    private function getLoanStatus($status) {
        switch ($status) {
            case 5:
                $page_status = 1;
                break;
            case 21:
                $page_status = 2;
                break;
            case 22:
                $page_status = 3;
                break;
            case 18:
                $page_status = 4;
                break;
            case 19:
                $page_status = 5;
                break;
            default:
                $page_status = 0;
                break;
        }
        return $page_status;
    }

    private function getLoanwait() {
        $this->getView()->title = "借款审核";
        $user = $this->getUser();
        $user_id = $user->user_id;
        return $this->render('waiting', [
            'user_id' => $user_id
        ]);
    }

    /**
     * 评测一系列页面
     * @param type $credit checkYyyUserCredit的结果
     * @param type $user_id
     * @param type $reject_data
     * @return type
     */
    private function showcredit($credit, $user_id, $reject_data) {
        $this->layout = 'loan';
        $quota_result = $this->getQuotaStatus($credit);
        $audit_status = $quota_result['audit_status'];
//        $audit_status = 4;
        //资料认证时间
        $juxinliModel = new Juxinli();
        $jxl_result = $juxinliModel->isAuthYunyingshang($user_id);
        $userinfo_data_time = '';
        if (!empty($jxl_result)) {
            $juxinli = $juxinliModel->getJuxinliByUserId($user_id);
            $userinfo_data_time = date('Y年m月d日 H:i:s', strtotime($juxinli->last_modify_time));
        }
        $jg_remark = Keywords::inspectOpen(); //1离场 2进场
        $oUser = User::findOne($user_id);
        $oCredit = (new User_credit())->getUserCreditByUserId($user_id);
        $req_id = empty($oCredit->req_id) ? '' : $oCredit->req_id;
        $direct_activation_url = '';
        $btn_status = 0;
        $evaluation_activation_info['channel'] = 0;
        $evaluation_activation_info['yxl_authentication_url'] = '';
        $evaluation_activation_info['youxin_down_url'] = '';
        $redict_activation_num = 0;
        if ($quota_result['user_credit_status'] == 4) {
            //智融接口返回测评支付结果
            $apiHttp = new Apihttp();
            $payResult = $apiHttp->getYxlpayBycredit(['req_id' => $req_id, 'source' => 1]);
            $btn_status = (new UserloanService())->getBtnStatusByCredit($payResult);
            //测评激活(user_id分桶：0 1 2 3 4下载智融app ,5 6 7 8 9直接跳转智融H5)
            $evaluation_activation_info = (new User())->getEvaluationChannel($oUser->user_id, $oUser->mobile);
            //请求智融钥匙接口获取安卓apk下载地址、ios App Store地址
            if ($btn_status) {
                $num = Yii::$app->redis->get($req_id);
                $redict_activation_num = empty($num) ? 0 : $num;
                $direct_activation_url = Yii::$app->request->hostInfo . '/borrow/creditactivation/activating?req_id=' . $req_id;
            }
        }
        $card_tixian_fail = 0; //提示存管卡不支持提现
        $card_unband_fail = 0; //无法更换解绑卡弹窗
        $error_id = 0;
        $bank_id = 0;
        $fivedayover = 0;//超过5天未提现引导
        $over_time = 0;
        if ($quota_result['user_credit_status'] == 6) { //重新获取额度页面 提示存管卡不支持提现以及无法更换解绑卡弹窗
            $tixian_toast_resullt = $this->getPayAccountErrorResult($user_id, 2);
            //查询是否有驳回借款
            $reject_loan = (new User_loan())->rejectLoanInfo($user_id);
            if ($tixian_toast_resullt['tixian_result'] != 0 && $tixian_toast_resullt['error_id'] != 0 && !empty($reject_loan)) {
                $card_tixian_fail = $tixian_toast_resullt['tixian_result'];
                $error_id = $tixian_toast_resullt['error_id'];
                $bank_id = $reject_loan->bank_id;

            } else {
                $uncard_toast_resullt = $this->getPayAccountErrorResult($user_id, 3);
                $card_unband_fail = $uncard_toast_resullt['tixian_result'];
                $error_id = $uncard_toast_resullt['error_id'];
            }
            if ($card_tixian_fail == 0 && $card_unband_fail == 0 && $error_id == 0) {
                $over_toast_resullt = $this->getPayAccountErrorResult($user_id, 5); //超过5天未提现弹窗
                $error_id = $over_toast_resullt['error_id'];
                $fivedayover = $over_toast_resullt['tixian_result'];
                $over_time = $over_toast_resullt['time'];
            }
        }

        return $this->render('showcredit', [
            'invalid_time' => $quota_result['invalid_time'],
            'user_credit_status' => $quota_result['user_credit_status'],
            'can_max_money' => number_format($quota_result['can_max_money'], 0, '.', ','),
            'can_max_days' => $quota_result['days'],
            'period' => $quota_result['period'],
            'audit_status' => $audit_status,
            'credit' => $credit,
            'user_id' => $user_id,
            'userinfo_data_time' => $userinfo_data_time,
            'reject_data' => $reject_data,
            'csrf' => $this->getCsrf(),
            'jg_remark' => $jg_remark,
            'user_info' => $oUser,
            'direct_activation_url' => $direct_activation_url,//直接激活地址
            'activation_btn_status' => $btn_status, //激活按钮是否可点击 0：不可点击 1：可点击
            'evaluation_activation_channel' => $evaluation_activation_info['channel'], //测评激活分桶 1:下载app  2:智融H5认证, 
            'yxl_authentication_url' => $evaluation_activation_info['yxl_authentication_url'], //测评激活-智融H5认证
            'redict_activation_num' => $redict_activation_num, //直接激活次数
            'youxin_down_url' => $evaluation_activation_info['youxin_down_url'], //测评激活-1下载app
            'req_id' => $req_id, //测评激活-1下载app
            'card_tixian_fail' => $card_tixian_fail, //存管卡不支持提现弹窗 0：不弹 1：弹
            'card_unband_fail' => $card_unband_fail, //存管卡无法提现 0：不弹 1：弹
            'error_id' => $error_id, //pay_account_error  id
            'bank_id' => $bank_id, //存管卡id
            'fivedayover' => $fivedayover, //超过五天未提现 0:不弹 1：弹
            'over_time' => $over_time, //超过五天未提现 驳回时间
        ]);
    }


    private function loanNoamount() {
        $this->layout = 'loan';
        $user = $this->getUser();
        $can_max_money = Keywords::getMaxCreditAmounts();   //未登录状态下或未评测状态下默认可借最大金额
        //判断豆荚贷产品中是否有进行中的借款
        if (!empty($user->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $user->identity]);
        } else {
            $canLoan = TRUE;
        }
        $jg_remark = Keywords::inspectOpen(); //2:监管进场 1：离场
        if ($jg_remark == 2) {
            $isShow = FALSE;
        } else {
            $isShow = $this->getIsshow($user);
        }
        return $this->render('index_quota', [
            'isShow' => $isShow,
            'canLoan' => $canLoan ? 1 : 2, //1:可借2：不可借
            'can_max_money' => number_format($can_max_money, 0, '.', ','),
            'csrf' => $this->getCsrf(),
            'user_id' => $user->user_id,
        ]);
    }

    private function getIsshow($user) {
        $isShow = FALSE;
        $apiHttp = new Apihttp();
        $cansysanc = $apiHttp->getYxlisregister(['mobile' => $user->mobile]);
        //身份信息 1：未认证  2:已认证
        $passModel = new User_password();
        $pass = $passModel->getUserPassword($user->user_id);
        $identify_valid = 1;
        if (!empty($pass)) {
            $path = ImageHandler::$img_domain . $pass->iden_url;
            $path = !empty($pass) ? ImageHandler::$img_domain . $pass->iden_url : '';
            if ($user->status == 3 || ($user->identity_valid == 2 && !empty($pass) && !empty($pass->iden_url) && @fopen($path, 'r'))) {
                $identify_valid = 2;
            }
        }

        $sacnTimesModel = new ScanTimes();
        $result = $sacnTimesModel->getByMobileType($user->mobile, 23);
        if ($cansysanc['rsp_code'] == '0000' && $identify_valid == 2 && empty($result)) {
            $isShow = TRUE;
            $sacnTimesModel->save_scan(['mobile' => $user->mobile, 'type' => 23]);
        }
        return $isShow;
    }

    private function loanBill($loan = '', $ious = '') {
        $this->layout = 'loan';
        $user = $this->getUser();
        $user_id = $user->user_id;
        $mobile = $user->mobile;
        $total = 0;
        $ious_url = '';
        if (!empty($loan)) {
            $total++;
        }
        if (!empty($ious)) {
            $total++;
            $url = urlencode('/dev/iousdetails/index?ious_id=' . $ious['ious_id']);
            $youxinDomain = Yii::$app->params['youxin_url'];
            $ious_url = $youxinDomain . 'dev/iousdetails/index?ious_id=' . $ious['ious_id'] . '&userToken=' . $mobile . '&url=' . $url;
        }

        $toast_result = $this->getPayAccountErrorResult($user_id, 1);
        $tixian_success = $toast_result['tixian_result']; //0:不弹框  1：弹成功  2：弹成功去支付
        $error_id = $toast_result['error_id'];
        if (!empty($ious) && $tixian_success == 1) {
            $tixian_success = 2;
        }

        return $this->render('index_bill', [
            'total' => $total,
            'user_id' => $user_id,
            'tixian_success' => $tixian_success,
            'error_id' => $error_id,
            'ious_url' => $ious_url,
            'csrf' => $this->getCsrf(),
        ]);
    }

    public function actionTixianajax() {
        $error_id = $this->post('error_id', 0);
        if (empty($error_id)) {
            return json_encode(['res_code' => '1000', 'res_msg' => '数据不正确']);
        }

        $pay_accout_result = PayAccountError::findOne($error_id);
        if (empty($pay_accout_result)) {
            return json_encode(['res_code' => '1000', 'res_msg' => '数据不正确']);
        }
        $res = $pay_accout_result->updateStatusSuccess();
        if (!$res) {
            return json_encode(['res_code' => '2000', 'res_msg' => '弹窗状态更改失败']);
        }
        return json_encode(['res_code' => '0000', 'res_msg' => '成功']);

    }

    /**
     * 判断审核页面与按钮 310
     * @param type $yyy_credit_status 一亿元测评结果 1:未测评;2已测评不可借;3:评测中;4:已测评可借未购买;6:已过期;
     * @param type $userInfo
     * @return int
     */
    private function getQuotaStatus($yyy_credit_status) {
        $userInfo = $this->getUser();
        //选填资料认证状态(true:有待完善，false:无待完善)
        $selection_status = (new User_loan())->getSelectionStatusNew($userInfo->user_id);
        $audit_status = 0; //0:无按钮 1：立即借款按钮 2：加快审核按钮 3：完善资料按钮 4：重新获取额度按钮 5:重新获取额度和完善资料     
        $block_user = ($userInfo->status == 5) ? TRUE : FALSE;  //判断一亿元黑名单用户
        $oCredit = (new User_credit())->getUserCreditByUserId($userInfo->user_id);
        $rejectCredit = (new User_credit())->getCreditRejectReturn($oCredit); //true：驳回 false:不是驳回
        //最高可借额度
        $can_max_money = Keywords:: getMinCreditAmounts();
        $yyy_credit_status['can_max_money'] = $can_max_money;
        $yyy_credit_status['is_yyy_or_zr_credit'] = 2;  //一亿元评测
        $yyy_credit_status['result_subject'] = '';  //一亿元评测
        $yyy_credit_status['can_max_money'] = $yyy_credit_status['order_amount'];  //一亿元评测金额
        //修改资料(true:已完善 false:未完善)
        $UserCreditByTimeRes = (new User_loan())->getUserCreditByTime($userInfo->user_id, $yyy_credit_status['invalid_time']);
        //是否在24小时之内 (true:超过24小时 false:未超过)
        $is_or_time = $this->getIsortime($yyy_credit_status['invalid_time']);

        //额度审核通过且未购卡 ，且在有效期内（立即借款按钮） 
        if ($yyy_credit_status['user_credit_status'] == 4 || $yyy_credit_status['user_credit_status'] == 5) {
            $audit_status = 1;
        }
        //额度获取中 且在有效期內 （加快审核按钮，若没有可补充资料则不显示按钮）
        if ($yyy_credit_status['user_credit_status'] == 3 && $selection_status) {
            $audit_status = 2;
        }
        //1评测驳回，未失效，无可完善资料：无按钮
        if ($yyy_credit_status['user_credit_status'] == 2 && !$selection_status && !$block_user && !$is_or_time) {
            $audit_status = 0;
        }
        //2评测驳回，未失效，有待完善资料，此次评测驳回从未完善过,不是黑名单用户： 完善资料
        if ($yyy_credit_status['user_credit_status'] == 2 && $selection_status && !$block_user && !$is_or_time && !$UserCreditByTimeRes) {
            $audit_status = 3;
        }
        //3.评测驳回，未失效，有待完善资料，此次评测驳回完善过资料，仍有待完善资料,不是黑名单用户：重新获取额度 完善资料
        if ($yyy_credit_status['user_credit_status'] == 2 && !$is_or_time && $selection_status && !$block_user && $UserCreditByTimeRes) {
            $audit_status = 5;
        }

        //4.评测驳回，未失效，有待完善资料，此次评测驳回完善过资料，已无完善资料,不是黑名单用户：重新获取额度 (与1一样)
        if ($yyy_credit_status['user_credit_status'] == 2 && !$is_or_time && !$block_user && $UserCreditByTimeRes && !$selection_status) {
            $audit_status = 4;
        }

        if (($rejectCredit && $oCredit['invalid_time'] < date('Y-m-d H:i:s', time())) && !$block_user) {
            $audit_status = 4;  //5.评测驳回，已失效，无可完善资料,不是黑名单用户：重新获取额度
            $yyy_credit_status['user_credit_status'] = 2;
            if ($selection_status) {
                $audit_status = 5; //6.评测驳回，已失效，有可完善资料,不是黑名单用户：重新获取额度 完善资料
            }
        }

        //黑名单
        if ($block_user) {
            $yyy_credit_status['user_credit_status'] = 2;
            $audit_status = 0;
        }
        if (empty($yyy_credit_status['can_max_money']) || ($yyy_credit_status['can_max_money'] == 0) || in_array($audit_status, [3, 4, 5]) || $yyy_credit_status['user_credit_status'] == 6) {
            $yyy_credit_status['can_max_money'] = Keywords::getMaxCreditAmounts();
        }
        $yyy_credit_status['audit_status'] = $audit_status;
        return $yyy_credit_status;
    }

    private function getIsortime($last_times) {

        $last_time = strtotime($last_times);
        //超过24小时
        if (date('Y-m-d H:i:s', $last_time + 24 * 3600) < date('Y-m-d H:i:s')) {
            return TRUE;
        }
        return FALSE;
    }

    private function getIdentify($user_id) {
        $res = PayAccountError::find()->where(['user_id' => $user_id, 'type' => 1, 'res_code' => '1'])->one();
        $mark = FALSE;
        if (!empty($res)) {
            $mark = TRUE;
        }
        return $mark;
    }


    /**
     * ‘立即借款’ 按钮ajax 判断存管状态和评测状态 310
     *  未开户 正向列表
     *  已开户 未绑卡 有密码 弹窗绑卡 直接去绑卡
     *  已开户 未绑卡 无密码 弹窗绑卡设密码 反向列表页
     *  已开户 已绑卡 无密码 正向列表
     *  已开户 未授权（还款和缴费授权） 正向列表页
     */
    public function actionGetcunguan() {
        $oUserInfo = $this->getUser();  //app是否调用  
        $isCungan = [];
        //判断身份证号是否一致
        $isIdentify = $this->getIdentify($oUserInfo->user_id);
        if ($isIdentify) {
            $isCungan['rsp_code'] = '3000'; //提示不可借款
            $isCungan['rsp_msg'] = '身份证号不一致!';
            return json_encode($isCungan);
        }
        $isCungan = (new Payaccount())->isCunguan($oUserInfo->user_id);
        if (in_array(0, $isCungan)) {
            $isCungan['password_list'] = 0;  //0正向列表 1：反向列表
            if (($isCungan['isOpen'] == 1) && ($isCungan['isCard'] != 1) && ($isCungan['isPass'] != 1)) {
                $isCungan['password_list'] = 1;
            }
            $isCungan['rsp_code'] = '0000'; //跳转存管
            $isCungan['rsp_msg'] = '';
            //失效弹窗
            $isCungan['auth_error'] = 1;
            $isRepayAuth = (new Payaccount())->getPaysuccessByUserId($oUserInfo->user_id, 2, 4);
            $isFundAuth = (new Payaccount())->getPaysuccessByUserId($oUserInfo->user_id, 2, 5);
            if (empty($isRepayAuth) && empty($isFundAuth)) {
                $isAuth = (new Payaccount())->getPaysuccessByUserId($oUserInfo->user_id, 2, 6);
                if (!empty($isAuth)) {
                    $o_pay_extend_auth = (new PayAccountExtend())->getByUserIdAndStep($oUserInfo->user_id, 6);
                    if ($isCungan['isAuth'] === 0 && !empty($o_pay_extend_auth)) {
                        $pay_res = $o_pay_extend_auth->getLegal(1);
                        if (empty($pay_res)) {
                            $isCungan['isAuth'] = 1;
                            $isCungan['auth_error'] = 0;
                        }
                    }
                }
            }

            return json_encode($isCungan);
        }

        //判断先花商城中订单及借款状况 true:无商城订单可发起评测 false:有订单不可发起
        $shop_res = (new User_credit())->getshopOrder($oUserInfo);
        if (!$shop_res) {
            $isCungan['rsp_code'] = '1000'; //提示不可借款
            $isCungan['rsp_msg'] = '您已有一笔商城订单，暂不可发起';
            return json_encode($isCungan);
        }

        //判断测评结果 一亿元状态4 或者智融测评状态5才能发起借款
        $credit_yyy = (new User_credit())->checkYyyUserCredit($oUserInfo->user_id);

        //评测已过期或未评测
        if ((empty($credit_yyy) || in_array($credit_yyy['user_credit_status'], [1, 6]))) {
            $isCungan['rsp_code'] = '1000'; //提示不可借款
            $isCungan['rsp_msg'] = '请10分钟后再发起借款!';
            return json_encode($isCungan);
        }

        if (!in_array($credit_yyy['user_credit_status'], [4, 5])) {
            $isCungan['rsp_code'] = '1000'; //提示不可借款
            $isCungan['rsp_msg'] = '请10分钟后发起借款!';
            return json_encode($isCungan);
        }

        $isCungan['rsp_code'] = '2000'; //可借款
        $isCungan['password_list'] = 0;
        $isCungan['rsp_msg'] = '';
        return json_encode($isCungan);
    }


    /**
     * 借款流程-借款参数选择页
     * 路径：borrow/loan/startloan
     * @return string
     */
    public function actionStartloan() {
        $this->layout = "loan/loandetail";
        $this->getView()->title = "信用借款";

        $amount = empty($this->get('amount')) ? $this->getCookieVal('amount') : $this->get('amount');
        $agreement = $this->get('agreement', 0);
        $coupon_id = empty($this->get('coupon_id', '')) ? $this->getCookieVal('coupon_id') : $this->get('coupon_id', '');
        $desc = empty($this->get('desc', '购买设备')) ? (!empty($this->getCookieVal('desc')) ? $this->getCookieVal('desc') : '购买设备') : $this->get('desc', '购买设备');

        $o_user = $this->getUser();
        $user_id = $o_user->user_id;

        //检测是否允许借款
        $is_loan = $this->isLoan($o_user);
        if (empty($is_loan) || !$is_loan['status']) {
            return $this->redirect($is_loan['url']);
        }
        //无可用评测记录
        $o_user_credit = (new User_credit())->checkCanCredit($o_user);
        if ($o_user_credit === FALSE) {
            return $this->redirect("/borrow/loan");
        }
        //有效时间
        $invalid_time = $o_user_credit->invalid_time;
        //有效时间（小时）
        $time_hours = strtotime($invalid_time) - time() > 0 ? ceil((strtotime($invalid_time) - time()) / 3600) : 0;
        //是否分期
        $is_installment = $o_user_credit->installment_result == 1 ? TRUE : FALSE;
        //可借天数
        $canMaxDays = (new User_loan())->getMaxLoanDays($user_id);
        $days = $canMaxDays[0];
        //获取优惠卷
        $coupon_list = $this->getCoupon($o_user, $is_installment, $o_user_credit->period);
        $coupon_count = empty(count($coupon_list)) ? 0 : count($coupon_list);
        //验证优惠卷
        $coupon_amount = 0;
        if (!empty($coupon_id) && !$is_installment) {
            $o_coupon_list = (new Coupon_list())->getById($coupon_id);
            if (!empty($o_coupon_list) && ($o_coupon_list->mobile == $o_user->mobile && $o_coupon_list->status == 1)) {
                $coupon_amount = $o_coupon_list->val;
            }
        }
        //可借金额&最大可借金额
        $amount = $o_user_credit->amount;
        $can_max_amount = $o_user_credit->amount;
        //借款类型
        $business_type = $o_user_credit->period > 1 ? 5 : 1;
        //还款计划
        $repay_plan = (new StageService())->getReayPlan($o_user, $o_user_credit, $amount, $days, $o_user_credit->period, $coupon_id, $is_installment);
        //银行卡
        $user_bank_arr = $this->getBank($o_user);
        $bank_arr = $user_bank_arr[0];
        //借款理由
        $desc_list = Keywords::getAppLoanDesc();
        $loanfee = $this->getRateAndWithdraw($amount, $days, $o_user, $o_user_credit->period, $loan_type = 1, $is_installment);
        //综合利息（合规进场时，利息拆分为综合费用+综合利息）
        $surplus_fee = (new StageService())->getSuperviseInterest($amount, $days, $o_user_credit->period, $loanfee['interest_fee'], $coupon_amount, $repay_plan, $is_installment);
        //存储借款信息到cookie
        $this->setCookieVal('desc', $desc);
        $this->setCookieVal('business_type', $business_type);
        $this->setCookieVal('can_max_amount', $can_max_amount);
        $this->setCookieVal('days', $days);
        $this->setCookieVal('agreement', $agreement);
        $this->setCookieVal('amount', $amount);
        $this->setCookieVal('period', $o_user_credit->period);
        $this->setCookieVal('bank_id', $bank_arr['bank_id']);

        return $this->render('startloan', [
            'bank' => $bank_arr,
            'userBanks' => $user_bank_arr,
            'can_max_money' => number_format($can_max_amount, 0, '.', ','),
            'days' => $days,
            'period' => !empty($o_user_credit->period)?$o_user_credit->period:1,
            'coupon_count' => $coupon_count,
            'couponlist' => $coupon_list,
            'repay_plan' => $repay_plan,
            'invalid_time' => $invalid_time,
            'time_hours' => $time_hours,
            'csrf' => $this->getCsrf(),
            'user_id' => $user_id,
            'coupon_amount' => $coupon_amount,
            'coupon_id' => $coupon_id,
            'agreement' => $agreement,
            'desc' => $desc,
            'amount' => $amount,
            'desc_list' => $desc_list,
            'can_input_amount' => FALSE,//默认不可修改金额
            'is_installment' => $is_installment,
            'interest_fee' => $loanfee['interest_fee'],
            'surplus_fee' => $surplus_fee,
        ]);
    }

    private function getRateAndWithdraw($amount, $days, $userObj, $term, $loan_type = 1, $is_installment = FALSE) {
        $oUserCredit = User_credit::find()->where(['user_id' => $userObj->user_id])->one();
        $interest = isset($oUserCredit->interest_rate) ? ($oUserCredit->interest_rate) / 100 : 0.00098;
        $loanfee = (new User_loan())->loan_Fee_rate_new($amount, $interest, $days, $userObj->user_id, $term, $loan_type, $is_installment);
        return $loanfee;
    }

    /**
     * 输入金额判断   310
     */
    public function actionAmountjudge() {
        $desc = $this->post('desc');
        $user_id = $this->post('user_id');
        $days = $this->post('days', 56);
        if (empty($user_id)) {
            $user_id = empty($this->getUser()) ? '' : $this->getUser()->user_id;
        }
        if (empty($user_id)) {
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '用户信息获取失败']);
        }
        $userModel = new User();
        $oUserInfo = $userModel->getUserinfoByUserId($user_id);
        if (empty($oUserInfo)) {
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '用户信息获取失败']);
        }
        $amount = (int)($this->post('amount', 0));

        if ($amount == 0 || empty($amount)) {
            return json_encode(['rsp_code' => '2000', 'rsp_msg' => '输入金额不得为0']);
        }

        if ((!is_int($amount)) || ($amount % 500 != 0)) {
            return json_encode(['rsp_code' => '2000', 'rsp_msg' => '请输入500的整数倍']);
        }

        //一亿元测评结果 1:未测评; 2已测评不可借;3:评测中;4:已测评可借未购买;6:已过期;
        $oUserCredit = (new User_credit())->checkYyyUserCredit($oUserInfo->user_id);
        //判断评测状态                      
        $quota_result = $this->getQuotaStatus($oUserCredit);
        $can_max_money = $quota_result['can_max_money'];

        if ($amount > $can_max_money) {
            return json_encode(['rsp_code' => '2000', 'rsp_msg' => '借款金额不能超过您的最高可借金额' . $can_max_money . '元']);
        }
        $min_amount = $this->getMinAmount($days);
        if ($amount < $min_amount) {
            return json_encode(['rsp_code' => '2000', 'rsp_msg' => '最小借款金额为' . $min_amount]);
        }
        $coupon_id = empty($this->get('coupon_id')) ? '' : $this->get('coupon_id');
        //还款计划
        $repay_data = $this->getRepayData($oUserInfo, $amount, $coupon_id);

        if (empty($repay_data)) {
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '金额判断失败']);
        }

        $repay_data['repay_date'] = date('m月d日', strtotime($repay_data[0]['repay_date']));
        $repay_data['repay_amount'] = $repay_data[0]['repay_amount'];
        $repay_data['amount'] = $amount;

        //把借款信息存到cookie里
        $this->setCookieVal('desc', $desc);
        $this->setCookieVal('amount', $amount);

        return json_encode(['rsp_code' => '0000', 'rsp_msg' => '', 'rsp_data' => $repay_data]);
    }

    /**
     * 输入借款页 还款计划中的数据 310
     */
    private function getRepayData($oUserInfo, $amount, $coupon_id) {
        $userLoanService = new UserloanService();
        $trem = 1;
        $canMaxDays = (new User_loan())->getMaxLoanDays($oUserInfo->user_id); //可借天数
        $days = $canMaxDays[0];

        //到手金额
        //$getamount = (new UserloanService())->getGetMoney($oUserInfo, $amount, $withdraw, $trem);


        //获取用户利息和服务费
        $loanfee = $this->getRateAndWithdraw($amount, $days, $oUserInfo, $trem);
        $interest = $loanfee['interest_fee']; //利息
        $withdraw = $loanfee['withdraw_fee']; //服务费


        //还款计划
        $coupon = new Coupon_list();
        $coupon_amount = 0;
        $couponInfo = '';
        if (!empty($coupon_id)) {
            $couponInfo = $coupon->getCouponById($coupon_id);
            $coupon_amount = empty($couponInfo) ? 0 : $couponInfo->val;
        }
        $repay_plan = $userLoanService->getReayPlan($oUserInfo, $amount, $trem, $days, $coupon_id, sprintf('%.2f', $withdraw), sprintf('%.2f', ceil($interest * 100) / 100));

        return $repay_plan;
    }

    /**
     *  选择优惠券页 310
     */
    public function actionGetloancoupon() {
        $this->getView()->title = "使用优惠券";
        $this->layout = "loan/loancoupon";

        $coupon_id = empty($this->get('coupon_id')) ? $this->getCookieVal('coupon_id') : $this->get('coupon_id');
        $use_coupon = empty($this->get('use_coupon')) ? 1 : $this->get('use_coupon'); //1:使用优惠券 2：不使用
        //$coupon_type = empty($this->get('coupon_type')) ? 1 : $this->get('coupon_type');
        if (!empty($this->get('user_id'))) {
            $user_id = $this->get('user_id');
        } else {
            if (!empty($this->getUser())) {
                $user_id = $this->getUser()->user_id;
            } else {
                exit('获取用户信息失败');
            }
        }
        //$userLoanService = new UserloanService();
        $userModel = new User();
        $oUserInfo = $userModel->getUserinfoByUserId($user_id);
        if (empty($oUserInfo)) {
            exit('获取用户信息失败');
        }

        if (!empty($coupon_id)) {
            $coupon = Coupon_list::find()->where(['mobile' => $oUserInfo->mobile, 'id' => $coupon_id, 'status' => 1, 'type' => [1, 2, 3, 4]])->one();
            if (empty($coupon)) {
                $coupon_id = '';
            }
        }

        $coupon = new Coupon_list();
        //拉取面向全部用户类型的有效优惠券
        $trem = 1;
        $couponlist_pull = $coupon->pullCoupon($oUserInfo->mobile);
        //优惠卷列表 1:借款卷
        $couponlist = $coupon->getValidList($oUserInfo->mobile, $trem, [1, 2, 3, 4]);
        if ($use_coupon == 2) {
            $coupon_id = '';
        }

        $this->setCookieVal('coupon_id', $coupon_id);

        return $this->render('loan_coupon', [
            'couponlist' => $couponlist,
            'coupon_id' => $coupon_id,
            'use_coupon' => $use_coupon,
        ]);
    }


    /**
     * 确认发起借款 310
     * @return string
     */
    public function actionConfirmloan() {
        $this->layout = "loan/confirmloan";
        $this->getView()->title = "借款确认";
        $o_user = $this->getUser();

        //无可用评测记录
        $o_user_credit = (new User_credit())->checkCanCredit($o_user);
        if ($o_user_credit === FALSE) {
            return $this->redirect("/borrow/loan");
        }
        $is_installment = $o_user_credit->installment_result == 1 ? TRUE : FALSE;

        //获取post提交过来的借款信息
        $coupon_id = $this->getCookieVal('coupon_id');
        $desc = $this->getCookieVal('desc');
        $amount = $this->getCookieVal('amount');
        $days = $this->getCookieVal('days');
        $period = !empty($this->getCookieVal('period'))?$this->getCookieVal('period'):1;
        $agreement = $this->getCookieVal('agreement');

        //获取用户出款卡
        $user_bank_arr = $this->getBank($o_user);
        $bank_arr = $user_bank_arr[0];
        //优惠卷
        $coupon_amount = 0;
        if (!empty($coupon_id) && !$is_installment) {
            $couponInfo = (new Coupon_list())->getCouponById($coupon_id);
            $coupon_amount = empty($couponInfo) ? 0 : $couponInfo->val;
        }
        $loanfee = $this->getRateAndWithdraw($amount, $days, $o_user, $period, $loan_type = 1, $is_installment);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        //还款计划
        $repay_plan = (new StageService())->getReayPlan($o_user, $o_user_credit, $amount, $days, $period, $coupon_id, $is_installment);
        //综合利息（合规进场时，利息拆分为综合费用+综合利息）
        $surplus_fee = (new StageService())->getSuperviseInterest($amount, $days, $period, $interest_fee, $coupon_amount, $repay_plan, $is_installment);
        //到手金额
        $getamount = (new UserloanService())->getGetMoney($o_user, $amount, $withdraw_fee, $period);
        $jsinfo = $this->getWxParam();
        return $this->render('confirmloan', [
            'desc' => $desc,
            'days' => $days,
            'amount' => sprintf('%.2f', $amount),
            'bank' => $bank_arr,
            'period' => $period,
            'withdraw' => sprintf('%.2f', $withdraw_fee),
            'interest' => sprintf('%.2f', sprintf('%.3f', ceil($interest_fee * 100) / 100)),
            'getamount' => sprintf('%.2f', sprintf('%.3f', ceil($getamount * 100) / 100)),
            'repay_plan' => $repay_plan,
            'coupon_id' => $coupon_id,
            'userinfo' => $o_user,
            'coupon_amount' => $coupon_amount,
            'csrf' => $this->getCsrf(),
            'user_id' => $o_user->user_id,
            'agreement' => $agreement,
            'jsinfo' => $jsinfo,
            'jg_remark' => Keywords::inspectOpen(), //1离场 2进场,
            'surplus_fee' => $surplus_fee,
            'is_installment' => $is_installment,
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
        $period = $this->post('period',1); //期数
        $days = $this->post('days'); //天数
        $bank_id = $this->post('bank_id'); //银行卡ID
        $coupon_id = $this->post('coupon_id'); //优惠卷ID
        $desc = $this->post('desc', '');
        if (empty($desc)) {
            $desc = !empty($this->getCookieVal('desc')) ? $this->getCookieVal('desc') : '购买设备';
        }
        if (empty($amount) || empty($days) || empty($period) || empty($bank_id)) {
            exit(json_encode($this->getUrlByCode('99996')));
        }

        $o_user = $this->getUser();
        if (empty($o_user)) {
            exit(json_encode($this->getUrlByCode('99994')));
        }

        //监测评测
        $o_user_credit = (new User_credit())->checkCanCredit($o_user);
        if (empty($o_user_credit)) {
            exit(json_encode($this->getUrlByCode('10233')));
        }

        //检测存管
        $isCungan = (new Payaccount())->isCunguan($o_user->user_id);
        if (in_array(0, $isCungan)) {
            exit(json_encode($this->getUrlByCode('10210', $o_user->user_id)));
        }

        //检测是否允许借款
        $loanCode = $this->checkCanLoan($o_user);
        if ($loanCode != '0000') {
            exit(json_encode($this->getUrlByCode($loanCode, $o_user->user_id)));
        }

        //监测数据是否合法
        $o_user_bank = (new User_bank())->getById($bank_id);
        $o_coupon = (new Coupon_list())->getById($coupon_id);
        $field_code = (new User_loan())->checkLoanField($o_user, $o_user_credit, $o_user_bank, $o_coupon, $amount, $days, $period);
        if ($field_code != '0000') {
            Logger::dayLog('weixin/loan/userloan','借款数据非法',$field_code);
            exit(json_encode($this->getUrlByCode($field_code, $o_user->user_id)));
        }

        $business_type = 1;
        if($o_user_credit->installment_result == 1){
            $business_type = 5;
        }

        $loan_info = [
            'amount' => $amount,
            'days' => $days,
            'period' => $period,
            'desc' => $desc,
            'source' => 1,//微信借款
            'business_type' => $business_type,
            'uuid' => '',
        ];
        $transaction = Yii::$app->db->beginTransaction();
        $o_user_loan = new User_loan();
        $loan_result = $o_user_loan->addUserLoanRecord($o_user, $o_user_credit, $o_user_bank, $o_coupon, $loan_info);
        if ($loan_result['rsp_code'] == '0000') {
            $transaction->commit();
            $this->delCookieVal('coupon_id');
            $this->delCookieVal('amount');
            $this->delCookieVal('desc');
            $this->delCookieVal('days');
            $this->delCookieVal('agreement');
            //推送智荣钥匙使用状态
            (new Push_yxl())->saveUseAndSend($o_user,$o_user_loan,$o_user_credit);
            exit(json_encode($this->getUrlByCode($loan_result['rsp_code'])));
        }
        $transaction->rollBack();
        exit(json_encode($this->getUrlByCode($loan_result['rsp_code'])));
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
     * @param unknown $business_type 1:好友;2:好人卡;3:担保人',
     * @param unknown $loan_id 借款id
     * @return boolean|string
     */
    private function loanCoupon($business_type, $loan_id) {
        if (empty($business_type) || empty($loan_id))
            return FALSE;
        $time = time();
        //判断用户优惠券
        $coupon_list_info = new Coupon_list();
        $loan_coupon = $coupon_list_info->getLoanCoupon($loan_id);
        $shareurl = '';
        //val:面值：0表示全免
        //status:2表示已使用
        if (!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) {
            $shareurl = "/borrow/loan/succ?l=" . $loan_id;
        } else {
            //business_type:1:好友;2:担保;3:担保人
            if ($business_type == 1) {
                $shareurl = "/borrow/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id);
            } else {
                $shareurl = "/borrow/loan/succ?l=" . $loan_id;
            }
        }
        return $shareurl;
    }

    public function actionHuanindex() {
        return $this->render('huanindex');
    }

    /**
     * 获取msg
     * @param        $code
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
     * 根据code获取跳转url 310
     * @param     $code
     * @param int $userId
     * @return string|Response
     */
    private function getUrlByCode($code, $user_id = '') {
        if (empty($code)) {
            $array = $this->errorreback('99996');
            $array['url'] = '/borrow/loan';
            return $array;
        }
        $array = $this->errorreback($code);
        switch ($code) {
            case '99991':
                $array['url'] = '/borrow/loan/startloan'; //连点
                break;
            case '10097':
                $array['url'] = '/borrow/account/black'; //黑名单
                break;
            case '10210':
                $array['url'] = '/borrow/custody/list?user_id=' . $user_id; //存管
                break;
            default:
                $array['url'] = '/borrow/loan';
                break;
        }
        return $array;
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
        $loan = $loan_info->getHaveinLoan($userObj->user_id, [1, 4, 5, 6, 9, 10]);
        if ($loan !== 0) {
            return '10050';
        }

        //判断7-14产品中是否有进行中的借款
        if (!empty($userObj->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $userObj->identity]);
            if (!$canLoan) {
                return '99990';
            }
        }

        //判断先花商城中订单及借款状况
        $shop_res = (new User_credit())->getshopOrder($userObj);
        if (!$shop_res) {
            return '10246';
        }

        return '0000';
    }

    /**
     * 生产借款
     * @param     $amount
     * @param     $days
     * @param     $bankObj
     * @param     $coupon_id
     * @param     $coupon_val
     * @param     $business_type 借款类型：1信用 2担保 （注：不与user_loan字段business_type同含义）
     * @param int $source
     * @param     $uuid
     * @param     $term
     * @param     $goods_id
     * @param     $desc
     * @return array
     */
    private function addLoan($userObj, $pay_status, $amount, $days, $bankObj, $coupon_id, $coupon_val, $source = 1, $uuid, $term, $desc = '购买设备') {
        if (!is_object($userObj) || empty($userObj)) {
            return ['rsp_code' => '10001'];
        }
        if (!is_object($bankObj) || empty($bankObj)) {
            return ['rsp_code' => '10043'];
        }
        $jg_remark = Keywords::inspectOpen();
        $status = 6;
        $feeOpen = Keywords::feeOpen();
        $type = 2;
        if ($feeOpen == 2) {
            $ex_status = (new User_loan())->getcreditUserloan($userObj->user_id);
            if ($ex_status) {
                $type = 2;
            } else {
                $type = 3;
            }
        }
        $ip = Common::get_client_ip();
        $loanModel = new User_loan();
        //是否为系统指定后置用户
        $charge = (new User_label())->isChargeUser($userObj->mobile);
        if ($charge === FALSE) {
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
            'desc' => $desc,
            'bank_id' => $bankObj->id,
            'source' => !empty($source) ? (int)$source : 1,
            'is_calculation' => $charge,
            'business_type' => 1,
        );
        $yyy_credit_status = (new User_credit())->checkYyyUserCredit($userObj->user_id);
        if (in_array($yyy_credit_status['user_credit_status'], [2, 3, 6])) {
            return ['rsp_code' => '10212']; //10分钟后重试
        }
        $condition['status'] = 6;
        $condition['prome_status'] = 5;
        $loanfee = $this->getRateAndWithdraw($amount, $days, $userObj, $term);
        $condition['interest_fee'] = $loanfee['interest_fee']; //利息
        $condition['withdraw_fee'] = $loanfee['withdraw_fee']; //服务费
        //$fee = $loanfee['fee'] * 100;

        //白名单
        $whiteModel = new White_list();
        $white = $whiteModel->isWhiteList($userObj->user_id);
        if ($white) {
            $condition['final_score'] = -1;
        }

        //优惠卷金额
        if (!empty($coupon_id)) {
            $interest_fee = empty($condition['interest_fee']) ? 0 : $condition['interest_fee'];
            if ($interest_fee > $coupon_val) {
                $coupon_val = $coupon_val = 0 ? $interest_fee : $coupon_val;
                $condition['coupon_amount'] = $coupon_val;
            } else {
                $condition['coupon_amount'] = $interest_fee;
            }
        }
        $condition['withdraw_time'] = date('Y-m-d H:i:s');
        $ret = $loanModel->addUserLoan($condition);
        $loan = User_loan::findOne($ret);
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

            $extend['status'] = 'TB-SUCCESS';
            if ($pay_status == 1) {
                $extend['status'] = 'AUTHED';
            } elseif ($jg_remark == 2) { //2监管入场 1：离场
                $ex_res = (new User_loan())->getcreditUserloan($loan->user_id);
                if ($ex_res) {
                    $extend['status'] = 'TB-SUCCESS';
                } else {
                    $extend['status'] = 'AUTHED';
                }
            }
            $extendId = $loanextendModel->addList($extend);
            if (empty($extendId)) {
                Logger::dayLog('weixin/loan/addLoan', '添加userloanextend失败', 'loan_id：' . $loan->loan_id, $extend);
                return ['rsp_code' => '10051'];
            }
        }

        return ['rsp_code' => '0000', 'data' => $loan];
    }

    public function actionGetcanloan() {
        $userInfo = $this->getUser();
        $user_id = $userInfo->user_id;
        $black_box = $this->post('black_box', ''); //接受同盾指纹
        $type = $this->post('type', 1); //1:判断信用卡未跳过且未绑定 2：跳过信用卡
        $jxl_result = FALSE;
//        $jxl_result = (new Juxinli())->isAuthYunyingshang($user_id);
        //判断必填资料是否已全部完成
        $info = (new User())->getRequireData($userInfo);
        if ($info['identify_valid'] == 2 && $info['contacts_valid'] == 2 && $info['pic_valid'] == 2 && $info['juxinli_valid'] == 2) {
            $jxl_result = TRUE;
        }
        if (!$jxl_result) {
            return json_encode(['rsp_code' => '0000', 'is_change' => 1]);
        }
        if ($type == 2) {
            //跳过信用卡
            $oCard = ScanTimes::find()->where(['mobile' => $userInfo->mobile, 'type' => 24])->one();
            if (empty($oCard)) {
                $sacnTimesModel = new ScanTimes();
                $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => 24]);
            }
        } else {
            $oUserbank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 1])->one();
            $oCard = ScanTimes::find()->where(['mobile' => $userInfo->mobile, 'type' => 24])->one();
            if (empty($oUserbank) && empty($oCard)) { //必填资料未完善或者信用卡未跳过且未绑定
                return json_encode(['rsp_code' => '0000', 'is_change' => 1]);
            }
        }

        //判断一亿元的测评状态
        $creditModel = new User_credit();
        $oUserCredit = $creditModel->getUserCreditByUserId($user_id);
        $user_credit = (new User_credit())->checkYyyUserCredit($user_id);
        //判断是否允许评测
        $CreditTime = $oUserCredit['last_modify_time'];
        $user_credit_status = $user_credit['user_credit_status'];
        $loan_id = empty($oUserCredit->loan_id) ? '' : $oUserCredit->loan_id;
        if ($user_credit_status == 3) {
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度正在获取中，请10分钟后重试', 'is_change' => 0]);
        }
        if ($user_credit_status == 1) {
            $repeatNum = (new User_loan())->isRepeatUser($user_id);
            if ($repeatNum == 0) {
                $oUserRejectLoan = (new User_loan())->getLastRejectLoan($user_id);
                if (!empty($oUserRejectLoan)) {
                    $CreditTime = $oUserRejectLoan->last_modify_time;//如果他是借款被驳回时间
                }
            }
        }

        if ($user_credit_status == 6) {
            $shop_res = (new User_credit())->getshopOrder($userInfo);
            if (!$shop_res) {
                return json_encode(['rsp_code' => '1000', 'rsp_msg' => '您已有一笔商城订单，暂不可发起', 'is_change' => 0]);
            }
        }
        //判断评测有效期内智融钥匙是否有 购卡记录
        if ($user_credit_status != 6 && !empty($oUserCredit) && $oUserCredit->pay_status == 1) {
            $zrys_credit_result = [
                'rsp_code' => '1000',
                'rsp_msg' => '很抱歉，你有一笔进行中的借款',
                'is_change' => 0,
            ];
            return json_encode($zrys_credit_result);
        }

//        $yyyCredit = $creditModel->getYyyCredit($oUserCredit);
        if (!empty($CreditTime) && ($user_credit_status != 6)) {
            $fillIn = (new User_credit())->chkCreditByMaterial($user_id, $CreditTime);
            $result = (new User_credit())->chkCredit($fillIn, $user_id, $loan_id, $user_credit_status);
            if ($result === FALSE) {
                return json_encode(['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度获取失败请10分钟后重试.', 'is_change' => 0]);
            }
            $shopCredit = (new User_credit())->getShopCredit($oUserCredit);
            if (!empty($oUserCredit) && $oUserCredit->status == 2 && $oUserCredit->res_status == 1 && !$shopCredit) {
                //向智融钥匙推送失效信息
                $zrRes = $this->getZrysres($oUserCredit['req_id'], $oUserCredit['source']);
                if (!$zrRes) {
                    $zrys_credit_result = [
                        'rsp_code' => '1007',
                        'rsp_msg' => '很抱歉，网络异常!',
                        'is_change' => 0,
                    ];
                    return json_encode($zrys_credit_result);
                }
            }
        }
        //判断存在未完成的借款&&借款不是'INIT', 'TB-AUTHED', 'TB-SUCCESS'
        $userLoanId = (new User_loan())->getHaveinLoan($user_id, $business_type = [1, 4, 5, 6, 9, 10]);
        if (!empty($userLoanId)) {
            $oExtend = (new User_loan_extend())->checkUserLoanExtend($userLoanId);
            if (!empty($oExtend) && !in_array($oExtend->status, ['INIT', 'TB-AUTHED', 'TB-SUCCESS'])) {
                return json_encode(['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度获取失败请10分钟后重试!', 'is_change' => 0]);
            }
        }

        $oJuXinLi = (new Juxinli())->getJuxinliByUserId($userInfo->user_id);
        $yyy_credit = $this->getYyyCredit($oJuXinLi, $userInfo);
        $credit_data_result = $this->yyyAddOrUpdateCredit($yyy_credit, $userInfo->user_id, $oUserCredit, $black_box);
        $oUserCreditnew = $creditModel->getUserCreditByUserId($user_id);
        if ($credit_data_result) { //请求评测成功并且新增或修改评测记录成功            
            $list_result = (new UserCreditList())->synchro($oUserCreditnew['req_id']);//credit_list添加一条记录
            if (empty($list_result)) {
                Logger::dayLog('weixin/getcanloan', '评测表记录失败', $result['res_data']['strategy_req_id'], $list_result);
            }
            $source_result = (new User())->getShopRedisResult('shop_info_', $userInfo, 2); //判断是否跳回先花商城
            if ($source_result) {
                return json_encode(['rsp_code' => '0000', 'rsp_msg' => '', 'is_change' => 2, 'shop_url' => $source_result, 'shop_mark' => 1]);
            }
            return json_encode(['rsp_code' => '0000', 'rsp_msg' => '', 'is_change' => 2]);
        } else {
            Logger::dayLog('weixin/getcanloan', '一亿元请求评测结果失败:', $userInfo->user_id, $yyy_credit);
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度获取失败请10分钟后重试', 'is_change' => 0]);
        }
    }

    public function yyyAddOrUpdateCredit($yyy_credit, $user_id, $oUserCredit, $black_box) {

        if ($yyy_credit->res_code === 0 && !empty($yyy_credit->res_data->strategy_req_id)) {
            //从未评测过
            if (empty($oUserCredit)) {
                $data = [
                    'user_id' => $user_id,
                    'req_id' => $yyy_credit->res_data->strategy_req_id,
                    'status' => 1,
                    'source' => 1,
                    'pay_status' => 0,
                    'black_box' => $black_box,
                    // 'uuid' => $uuid,
                    //'device_tokens' => $deviceTokens,
                    'device_type' => 1, //1:微信公众号
                    'device_ip' => Common::get_client_ip(),
                ];
                $creditResult = (new User_credit())->addUserCredit($data);
                if (empty($creditResult)) {
                    Logger::dayLog('weixin/borrow/yyyAddOrUpdateCredit', '一亿元评测表记录新增失败', $data, $creditResult);
                    return FALSE;
                }
                return TRUE;
            }

            //评测过
            $creditArray = [
                'req_id' => $yyy_credit->res_data->strategy_req_id,
                'loan_id' => '',
                'source' => 1,
                'pay_status' => 0,
                'black_box' => $black_box,
                'device_type' => 1,
                'device_ip' => Common::get_client_ip(),
            ];
            $creditResult = $oUserCredit->updateInit($creditArray);
            if (empty($creditResult)) {
                Logger::dayLog('weixin/borrow/yyyAddOrUpdateCredit', '一亿元评测表记录更新失败', $yyy_credit->res_data->strategy_req_id, $creditResult);
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 一亿元发起评测
     * @param type $apiHttp
     * @param type $oJuXinLi
     * @param type $userInfo
     * @return type
     */
    public function getYyyCredit($oJuXinLi, $userInfo) {
        $parms = [
            'aid' => 1,
            'req_id' => $oJuXinLi->requestid,
            'user_id' => $userInfo->user_id,
            'callbackurl' => Yii::$app->request->hostInfo . '/new/notifycredit',
        ];
        $yyy_credit = json_decode((new Apihttp())->postCredit($parms));

        return $yyy_credit;
    }

    /**
     * 根据智融钥匙测评结果返回不同的结果
     * @param type $result 智融钥匙测评结果
     * @return type
     */
    private function getCreditResult($result, $userInfo) {
        if ($result['rsp_code'] !== '0000') {//请求失败
            return ['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度获取失败请10分钟后重试'];
        }
        if (in_array($result['user_credit_status'], [1, 6])) {//未评测，评测已过期
            return ['rsp_code' => '0000', 'rsp_msg' => ''];
        }
        if ($result['user_credit_status'] == 2 && !empty($result['credit_invalid_time'])) {//评测驳回
            $borrowing = (new User_loan())->getUserCreditByTime($userInfo->user_id, $result['credit_invalid_time']);
            if (!$borrowing) {
                return ['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度获取失败请24小时后重试'];
            }
            return ['rsp_code' => '0000', 'rsp_msg' => ''];
        }

        if (in_array($result['user_credit_status'], [7, 8])) {
            return ['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度获取失败请10分钟后重试'];
        }

        return ['rsp_code' => '1000', 'rsp_msg' => '很抱歉，额度获取失败请10分钟后重试'];
    }

    public function actionUrgeajax() {
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        if (empty($userInfo)) {
            return json_encode(['rsp_code' => '1000', 'rsp_msg' => '用户信息获取失败']);
        }

        $sacnTimesModel = new ScanTimes();
        $oScanTimes = ScanTimes::find()->where(['mobile' => $userInfo->mobile, 'type' => 25])->orderBy('id desc')->one();
        if (!empty($oScanTimes)) {
            $time_diff = time() - strtotime($oScanTimes->create_time);
            if ($time_diff < 24 * 3600) {
                return json_encode(['rsp_code' => '2000', 'rsp_msg' => '已经在催了哟']);
            } else {

                $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => 25]);
            }
        } else {
            $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => 25]);
        }
        return json_encode(['rsp_code' => '0000', 'rsp_msg' => '消息发送成功，正在为您加速处理']);
    }

    public function actionSetdesc() {
        $desc = $this->post('desc', '购买设备');
        $this->setCookieVal('desc', $desc);
        return json_encode(['rsp_code' => '0000', 'rsp_msg' => '成功']);
    }

    private function getMinAmount($day) {
        if (empty($day)) {
            return 1000;
        }
        $amounts = Keywords::getMinAmounts();
        $min_amount = 1000;
        if (isset($amounts[$day]) && !empty($amounts[$day])) {
            $min_amount = $amounts[$day];
        }
        return $min_amount;
    }

    private function renewalInspect($o_renewal_inspect) {
        $this->layout = FALSE;
        return $this->render('renewal_inspect', [
            'o_renewal_inspect' => $o_renewal_inspect
        ]);
    }

    /**
     * 向有信令推送失效
     * @param  [type] $req_id [description]
     * @param  [type] $source [description]
     * @return [type]         [description]
     */
    private function getZrysres($req_id, $source) {
        if (in_array($source, [1, 3])) {
            $source = 1;
        }
        $contacts = [
            'req_id' => $req_id,
            'source' => $source,
            'status' => 2,
        ];
        $api = new Apihttp();
        $result = $api->postSignal($contacts, 4);
        if (!empty($result['rsp_code']) && $result['rsp_code'] == '0000') {
            return TRUE;
        }
        Logger::dayLog('signal/signalpush', '有信令推送失败', 'req_id：' . $req_id, $contacts, $result);
        return FALSE;
    }

    private function isLoan($o_user) {
        //判断用户有没有开户、绑卡、设置密码
        $isCungan = (new Payaccount())->isCunguan($o_user->user_id);
        if (in_array(0, $isCungan)) {
            return ['status' => FALSE, 'url' => '/borrow/custody/list'];
        }
        //判断用户是否是黑名单用户
        if ($o_user->status == 5) {
            return ['status' => FALSE, 'url' => '/borrow/loan'];
        }

        //判断一亿元产品中是否有进行中的借款
        $haveinLoanId = (new User_loan())->getHaveinLoan($o_user->user_id, [1, 4, 5, 6, 9, 10, 11, 12]);
        if (!empty($haveinLoanId)) {
            return ['status' => FALSE, 'url' => '/borrow/loan'];
        }
        return ['status' => TRUE, 'url' => ''];
    }

    /**
     * 获取优惠卷列表
     * @param $o_user
     * @param $trem
     * @return array|null
     */
    private function getCoupon($o_user, $is_installment, $trem) {
        $coupon = new Coupon_list();
        //拉取优惠卷
        $coupon->pullCoupon($o_user->mobile);
        //只获取借款优惠券
        $coupon_type = [1, 2, 3, 4];
        return $coupon->getValidList($o_user->mobile, $trem, $coupon_type, $is_installment);
    }


    /**
     * 获取还款计划
     * @param $o_user
     * @param $amount
     * @param $interest
     * @param $days
     * @param $term
     * @param $coupon_id
     * @return array
     */
    private function getRepayPlan($o_user, $amount, $interest, $withdraw, $days, $term, $coupon_id, $is_installment = FALSE) {
        return $repay_plan = (new UserloanService())->getReayPlan($o_user, $amount, $term, $days * $term, $coupon_id, sprintf('%.2f', $withdraw), sprintf('%.2f', ceil($interest * 100) / 100), $is_installment);
    }

    //获取银行卡列表
    private function getBank($o_user) {
        $bank_result = (new User_bank())->limitCardsSort($o_user->user_id, 0);
        $bank_arr = [];
        foreach ($bank_result as $k => $v) {
            $bank_arr[$k]['bank_id'] = $v['id'];
            $bank_arr[$k]['type'] = !empty($v['bank_name']) ? trim($v['bank_name'], " ") : '银行卡';
            $bank_arr[$k]['card'] = substr($v['card'], strlen($v['card']) - 4, 4);
            $bank_arr[$k]['bank_abbr'] = !empty($v['bank_abbr']) ? $v['bank_abbr'] : 'ICON';
            $bank_arr[$k]['bank_icon_url'] = $this->getImageUrl($v['bank_abbr']);
            $bank_arr[$k]['default_card'] = $v['default_bank'] == 0 ? 2 : $v['default_bank'];
        }
        return $bank_arr;
    }
}

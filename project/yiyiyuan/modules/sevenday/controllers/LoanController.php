<?php
namespace app\modules\sevenday\controllers;

use app\models\day\Juxinli_guide;
use app\models\day\Scan_times;
use app\models\day\User_bank_guide;
use app\models\day\User_credit_guide;
use app\models\day\User_loan_guide;
use app\models\day\User_remit_list_guide;
use app\modules\sevenday\controllers\SevendayController;
use Yii;


class LoanController extends SevendayController {

    /**
     * 获取额度页
     * @return string
     * @author 王新龙
     * @date 2018/8/2 20:22
     */
    public function actionIndex() {
        $this->getView()->title = '获取额度';
        $repayerror = $this->get('repay', '');
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_loan_guide = (new User_loan_guide())->getHaveinLoan($o_user_guide->user_id);
        if (!empty($o_user_loan_guide)) {
            $page = $o_user_loan_guide->showPage('/day/loan');
            if (!empty($page)) {
                return $this->redirect($page);
            }
        }
        //还款&续期弹窗
        $popup = (new Scan_times())->getRepayPopup($o_user_guide->user_id,1);
        if($popup['is_popup'] == 2){
            $popup = (new Scan_times())->getRepayPopup($o_user_guide->user_id,2);
        }
        return $this->render('index', [
                    'repayerror' => $repayerror,
                    'user_id' => $o_user_guide->user_id,
                    'user' => $o_user_guide,
                    'popup' => $popup,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 借款详情页
     * @author 王新龙
     * @date 2018/8/3 11:51
     */
    public function actionShowloan() {
        $this->getView()->title = '借款详情';
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_loan_guide = (new User_loan_guide())->getHaveinLoan($o_user_guide->user_id);
        if (empty($o_user_loan_guide)) {
            return $this->redirect('/day/loan');
        }
        $page = $o_user_loan_guide->showPage('/day/loan/showloan');
        if (!empty($page)) {
            return $this->redirect($page);
        }
        $oRemitModel = new User_remit_list_guide();
        $money = $oRemitModel->getSuccessData();
        return $this->render('showloan', [
                    'user' => $o_user_guide,
                    'money' => $money,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 获取额度
     * @author 王新龙
     * @date 2018/8/2 20:22
     */
    public function actionGetcredit() {
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        if ($o_user_guide->identity_valid != 2) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '填写用户信息', 'url' => '/day/userauth/index']));
        }
        $o_user_credit_guide = (new User_credit_guide())->getByIdentity($o_user_guide->identity);
        if (empty($o_user_credit_guide)) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '用户暂无额度', 'url' => '/day/loan/nocredit']));
        }
        $o_user_bank_guide = (new User_bank_guide())->getByUserId($o_user_guide->user_id, $type = 0);
        if (empty($o_user_bank_guide)) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '绑卡', 'url' => '/day/userbank/index']));
        }
        $mobile = (new Juxinli_guide())->getJuxinli($o_user_guide->user_id);
        if (empty($mobile)) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '手机号验证', 'url' => '/day/userauth/mobile']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '借款', 'url' => '/day/loan/credit']));
    }

    /**
     * 暂无额度页
     * @return string
     * @author 王新龙
     * @date 2018/8/2 20:24
     */
    public function actionNocredit() {
        $this->getView()->title = '暂无额度';
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $guide_url = Yii::$app->params['youxin_url'] . '?channel=reject&phone=' . $o_user_guide->mobile;
        return $this->render('nocredit', [
                    'guide_url' => $guide_url
        ]);
    }

    /**
     * 额度页
     * @author 王新龙
     * @date 2018/8/2 20:44
     */
    public function actionCredit() {
        $this->getView()->title = '获取额度';
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        return $this->render('credit', [
                    'user' => $o_user_guide,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 确认借款页
     * @author 王新龙
     * @date 2018/8/2 20:45
     */
    public function actionConfirm() {
        $this->getView()->title = '借款确认';
        $bank_id = $this->get('bank_id', '');
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $o_user_bank_guide = (new User_bank_guide())->listByUserId($o_user_guide->user_id, $typw = 0);
        if (empty($o_user_bank_guide)) {
            return $this->redirect('/day/userbank/index');
        }
        $o_user_bank = $o_user_bank_guide[0];
        if (!empty($bank_id)) {
            $o_user_bank = (new User_bank_guide())->getById($bank_id);
            if (empty($o_user_bank) || $o_user_bank->user_id != $o_user_guide->user_id) {
                return $this->redirect('/day/loan/confirm');
            }
        }
        $amount = sprintf('%.2f', 500);
        $day = 7;
        $actual_amount = sprintf('%.2f', bcsub(500, (bcmul(500, 0.3, 2)), 2));
        $withdraw_fee = sprintf('%.2f', bcmul(500, 0.3, 2));
        $interest_fee = sprintf('%.2f', 0);
        $coupon_amount = sprintf('%.2f', 0);
        return $this->render('confirm', [
                    'amount' => $amount,
                    'day' => $day,
                    'user_id' => $o_user_guide->user_id,
                    'actual_amount' => $actual_amount,
                    'withdraw_fee' => $withdraw_fee,
                    'interest_fee' => $interest_fee,
                    'coupon_amount' => $coupon_amount,
                    'user_bank_arr' => $o_user_bank_guide,
                    'user_bank' => $o_user_bank,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 生成借款
     * @author 王新龙
     * @date 2018/8/2 21:36
     */
    public function actionCreateloan() {
        if (!$this->isPost()) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0002', 'rsp_msg' => '非法访问']));
        }
        $bank_id = $this->post('bank_id');
        if (empty($bank_id)) {
            exit(json_encode(['rsp_code' => '0003', 'rsp_msg' => '参数不能为空']));
        }
        $amount = sprintf('%.2f', 500);
        $day = 7;
        $withdraw_fee = bcmul(500, 0.3, 2);
        $interest_fee = sprintf('%.2f', 0);
        $loan_result = (new User_loan_guide())->checkCanLoan($o_user_guide->user_id);
        if (empty($loan_result)) {
            exit(json_encode(['rsp_code' => '0004', 'rsp_msg' => '借款失败']));
        }
        $suffix = $o_user_guide->user_id . rand(100000, 999999);
        $loan_no = date("YmdHis") . $suffix;
        $condition = array(
            'user_id' => $o_user_guide->user_id,
            'loan_no' => $loan_no,
            'amount' => $amount,
            'days' => $day,
            'type' => 1,
            'status' => 6,
            'prome_status' => 5,
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
            'desc' => '其他',
            'bank_id' => $bank_id,
            'withdraw_time' => date('Y-m-d H:i:s', time()),
            'is_calculation' => 1,
            'source' => 1,
        );
        $user_loan_result = (new User_loan_guide())->addUserLoan($condition);
        if (empty($user_loan_result)) {
            exit(json_encode(['rsp_code' => '0005', 'rsp_msg' => '借款失败']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '借款成功', 'url' => '/day/loan']));
    }

}

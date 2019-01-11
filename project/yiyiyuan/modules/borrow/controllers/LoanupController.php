<?php

namespace app\modules\borrow\controllers;

use app\commonapi\ImageHandler;
use app\commonapi\Logger;
use app\models\news\Areas;
use app\models\news\Coupon_list;
use app\models\news\Loan_pic;
use app\models\news\User;
use app\models\news\User_loan;
use yii\web\Response;

class LoanupController extends BorrowController {

    /**
     * 线下还款页面
     * @return string|Response
     * @author 王新龙
     * @date 2018/7/25 9:31
     */
    public function actionIndex() {
        $this->layout = "repay/offline";
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "上传凭证";
        $loan_id = $this->get('loan_id');
        $source = $this->get('source', 0);
        $coupon_id = $this->get('coupon_id');
        Logger::dayLog('weixin/repay/repay', '线下还款loan_id：', $loan_id);
        //无借款 or 已结清 or 还款中 跳转首页
        $o_user_loan = (new User_loan())->getById($loan_id);
        //用户
        $o_user = (new User())->getById($o_user_loan->user_id);
        if (empty($o_user)) {
            return $this->redirect('/borrow/loan');
        }
        $huankuan_amount = $o_user_loan->getRepaymentAmount($o_user_loan);
        //优惠卷
        if (!empty($coupon_id)) {
            $coupon_result = (new Coupon_list())->chkCoupon($o_user->mobile, $coupon_id, $loan_id);
            if ($coupon_result['rsp_code'] != '0000') {
                return $this->redirect('/borrow/loan');
            }
            $coupon_val = $coupon_result['data']->val;
            $huankuan_amount = bcsub($huankuan_amount, $coupon_val, 2);
        }

        //春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');

        return $this->render('offline', [
                    'source' => $source,
                    'encrypt' => ImageHandler::encryptKey($o_user_loan->user_id, 'buy'),
                    'jsinfo' => $jsinfo,
                    'loan_id' => $loan_id,
                    'coupon_id' => $coupon_id,
                    'loaninfo' => $o_user_loan,
                    'huankuan_amount' => $huankuan_amount,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'saveMsg' => '',
                    'user_id' => $o_user->user_id
        ]);
    }

    /**
     * 线下还款保存
     * @return Response
     * @author 王新龙
     * @date 2018/7/25 9:31
     */
    public function actionRepaysave() {
        $loan_id = $this->post('loan_id', '');
        $supplyUrl = $this->post('supplyUrl', '');
        if (!isset($loan_id)) {
            return $this->redirect('/borrow/loan');
        }
        //消费凭证不能为空
        if (empty($supplyUrl)) {
            return $this->redirect('/borrow/loanup/index?loan_id=' . $loan_id);
        }
        $user = $this->getUser();
        $user_id = $user->user_id;
        $o_user_loan = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (!in_array($o_user_loan->status, [6, 8, 9, 11, 12, 13])) {
            return $this->redirect('/borrow/tradinglist/list');
        }
        $Oloanpic = (new Loan_pic())->getByLoanId($loan_id);
        if (empty($Oloanpic)) {
            return $this->redirect('/borrow/loanup/index?loan_id=' . $loan_id);
        }
//        print_r($supplyUrl);die;
        $result = $Oloanpic->savePic($supplyUrl[1], $supplyUrl[2], $supplyUrl[3]);
        if (!$result) {
            return $this->redirect('/borrow/loanup/index?loan_id=' . $loan_id);
        }
        $this->getView()->title = "上传成功";
        $this->layout = 'data';
        $jsinfo = $this->getWxParam();

        return $this->render('verify', [
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionPagepic() {

        $this->layout = 'buypic/main';
        $loan_id = $this->get('loan_id', 0);
        if (empty($loan_id)) {
            exit;
        }
        $loan = User_loan::findOne($loan_id);
        $user = $loan->user;
        $loanPic = (new Loan_pic())->getByLoanId($loan_id);
        $userextend = $user->extend;
        $areaModel = new Areas();
        $address = '';
        if (!empty($userextend) && !empty($userextend->home_area)) {
            $pro = $areaModel->getProCityArea($userextend->home_area);
            sort($pro);
            foreach ($pro as $key => $val) {
                $address .= $areaModel->getName($val) . ' ';
            }
            $address .= $userextend->home_address;
        } else if (!empty($userextend) && !empty($userextend->company_area)) {
            $pro = $areaModel->getProCityArea($userextend->company_area);
            sort($pro);
            foreach ($pro as $key => $val) {
                $address .= $areaModel->getName($val) . ' ';
            }
            $address .= $userextend->company_address;
        } else {
            
        }
        $oGoodModel = new \app\models\news\Goods_shop();
        $goods = $oGoodModel->getByPrice($loan->amount);
        return $this->render('pagepic', [
                    'user' => $user,
                    'address' => $address,
                    'loanPic' => $loanPic,
                    'goods' => $goods,
        ]);
    }

}

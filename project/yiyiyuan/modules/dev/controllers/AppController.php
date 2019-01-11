<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\models\dev\Coupon_list;
use app\models\dev\User;
use Yii;

class AppController extends SubController {

    public $layout = 'app';
    public $enableCsrfValidation = false;

    /**
     * 
     * @param type $user_id
     * @param type $status 1 代表未使用的， 2代表过期的
     * @return type
     */
    public function actionCouponlist($user_id, $status) {
        $this->getView()->title = '优惠券';
        $user = User::findOne($user_id);
        if (empty($status) || empty($user)) {
            echo '系统错误';
            exit;
        }
        if ($status == 1) {
            $coupon = Coupon_list::getCouponByMobile($user->mobile, $status, 'end_date desc');
        } else {
//            $conpon_y = Coupon_list::getCouponByMobile($user->mobile, 2);
            $coupon = Coupon_list::getCouponByMobile($user->mobile, 3);
//            $coupon = array_merge($conpon_x, $conpon_y);
            usort($coupon, function ($a, $b) {
                if ($a->end_date == $b->end_date) {
                    return 0;
                }
                return $a->end_date > $b->end_date ? -1 : 1;
            });
        }
        return $this->render('couponlist', array(
                    'couponlist' => $coupon,
                    'status' => $status,
                    'user_id' => $user_id,
        ));
    }

    public function actionCouponrule() {
        $this->getView()->title = '使用规则';
        return $this->render('couponrule');
    }

    public function actionRegisterrule() {
        $this->layout = 'pdf';
        $this->getView()->title = '注册协议';
        return $this->render('registerrule');
    }

    public function actionOne() {
        $this->layout = 'applayout';
        $this->getView()->title = "领优惠券";
        return $this->render('four');
    }

    public function actionTwo() {
        $this->layout = 'applayout';
        $this->getView()->title = "快速借款";
        return $this->render('loan');
    }

    public function actionThree() {
        $this->layout = 'applayout';
        $this->getView()->title = "快速借款";
        return $this->render('three');
    }

    public function actionFour() {
        $this->layout = 'applayout';
        $this->getView()->title = "开张大吉";
        return $this->render('five');
    }

    public function actionGoodcomment() {
        $this->layout = 'applayout';
        $type = isset($_GET['type']) ? $_GET['type'] : 'android';
        $this->getView()->title = "好评领券";
        return $this->render('goodcomment', array(
                    'type' => $type,
        ));
    }

    public function actionLoanteach() {
        $this->layout = 'loanteach';
        $this->getView()->title = "借款教程";
        return $this->render('loanteach');
    }

    public function actionAllcomment() {
        $this->layout = 'applayout';
        $this->getView()->title = "好评领券";
        return $this->render('allcomment');
    }

    public function actionFastloan() {
        $this->getView()->title = '新用户借款';
        return $this->render('fastloan');
    }

    public function actionUpamountsecond() {
        $this->getView()->title = '提额盛典';
        return $this->render('upamountsecond');
    }

    public function actionTombactivity() {
        $this->getView()->title = '清明节活动';
        return $this->render('tombactivity');
    }

    public function actionWeekend() {
        $this->getView()->title = '放款不放假';
        return $this->render('weekend');
    }
    
    public function actionLoanguide(){
        $this->getView()->title='借款指引';
        return $this->render('loanguide');
    }
    
    public function actionToaccount(){
        $this->getView()->title='500元已到帐';
        return $this->render('toaccount');
    }

    public function actionBannershare($from = 'app') {
        $this->layout = 'data';
        $this->getView()->title = 'banner';
        return $this->render('zhounian', [
                    'from' => $from,
        ]);
    }
    
    public function actionThreeactivity(){
        $this->getView()->title = '三周年庆典';
        $type = "app";
        return $this->render('threeactivity', [
                        'type' => $type,
        ]);
    }
    public function actionHavecard_old(){
        $this->getView()->title = '担保借款';
        return $this->render('havecard_old');
        
    }
    public function actionHavecard(){
        $this->getView()->title = '双十一活动';
        return $this->render('havecard');
        
    }
    public function actionRenewal(){
        $this->getView()->title = '通告';
        return $this->render('renewal');
        
    }
    
    
    public function actionOutmoney(){
        $this->getView()->title = '提现通知';
        return $this->render('outmoney');
        
    }
    
    public function actionPostal(){
        $this->getView()->title = '维护通知';
        return $this->render('postal');
    }
    //新产品活动页面
    public function actionNewproduct(){
        $this->getView()->title = '新业务';
        return $this->render('newproduct');
    }
    
    //新产品活动页面
    public function actionNewyear(){
        $this->getView()->title = '春节不打烊';
        return $this->render('newyear');
    }

    
}

<?php

namespace app\modules\borrow\controllers;

use app\commonapi\ImageHandler;
use app\models\news\Selection_bankflow;
use app\models\news\User_loan;
use app\models\news\User_bank;
use app\models\news\User;
use app\models\news\User_password;
use app\models\news\Favorite_contacts;
use app\models\news\Juxinli;
use app\models\news\ScanTimes;
use app\models\news\User_credit;
use Yii;


/**
 * 个人资料： 身份信息 联系人信息 视频认证 运营商认证 信用卡 公积金认证  社保认证 学历认证
 */
class UserInfoController extends BorrowController {

    //public $layout = 'main';

    /**
     * 个人资料列表页（包括选填和必填资料）
     */
    public function actionList() {
        $this->getView()->title = "个人资料";
        $this->layout = "userinfo/requireinfo";
        
        $user = $this->getUser();
        if(empty($user)){
            exit('用户不存在');
        }
        $black_user = $user->status == 5 ? true : false ;
        $requireData = (new User())->getRequireData($user); //必填资料
        $identify_valid = $requireData['identify_valid']; 
        $contacts_valid = $requireData['contacts_valid']; 
        $pic_valid = $requireData['pic_valid']; 
        $juxinli_valid = $requireData['juxinli_valid'];

        $selectionData = (new User())->getselectionData($user); //1学历 2社保 3公积金 6淘宝 7银行卡 1:未认证 2：已认证  3认证中 4：已过期
        $bank_valid = $selectionData['bank_valid']   ;
        $edu_valid = $selectionData['edu_valid']  ;
        $social_valid = $selectionData['social_valid'] ;
        $fund_valid = $selectionData['fund_valid'] ;
        $jd_valid = $selectionData['jd_valid'];
        $bankflow_valid = $selectionData['bankflow_valid'] ;
        $taobao_valid = $selectionData['taobao_valid'] ;
        $redirect_info = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/borrow/userinfo/list';
     
        return $this->render('infolist', [
            'identify_valid' => $identify_valid,
            'contacts_valid' => $contacts_valid,
            'pic_valid' => $pic_valid,
            'juxinli_valid' => $juxinli_valid,
            'edu_valid' => $edu_valid,
            'social_valid' => $social_valid,
            'fund_valid' => $fund_valid,
            'bank_valid' => $bank_valid,
            'jd_valid' => $jd_valid,
            'taobao_valid' => $taobao_valid,
            'bankflow_valid' => $bankflow_valid,
            'user_id' => $user->user_id,
            'csrf' => $this->getCsrf(),
            'redirect_info' => $redirect_info,
            'black_user' => $black_user,
        ]);
    }
    

    /**
     * 必填资料列表页
     */
    public function actionRequireinfo(){
        $this->layout = "userinfo/requireinfo";
        $this->getView()->title = "个人资料";
        $source_mall = $this->get('source_mall',0); //1：来源于一亿元商城商品下单
        $source_mark = $this->get('source_mark',0);
        $user = $this->getUser();
        $user_id = $user->user_id;
        if( $source_mark == 1 ){ //从先花商城跳转过来的
            Yii::$app->redis->setex('shop_info_'.$user_id,86400,$user_id);
        }
        $requireData = (new User())->getRequireData($user);
        $identify_valid = $requireData['identify_valid']; 
        $contacts_valid = $requireData['contacts_valid']; 
        $pic_valid = $requireData['pic_valid']; 
        $juxinli_valid = $requireData['juxinli_valid'];

        $user_iden_keys ='user_pic_times_' . $user->user_id;
        $user_iden = Yii::$app->redis->get($user_iden_keys);
        if (empty($user_iden)) {
            $videoTimes = 0;
        } else {
            $videoTimes = $user_iden;
        }
        //信用卡 1:未认证 2：已认证
        $userbank = User_bank::find()->where(['user_id' => $user->user_id, 'status' => 1, 'type' => 1])->one();
        $bank_valid = empty($userbank) ? 1 : 2;
        $jump_card = 1; //1可进入到绑卡页面
        $oCard = ScanTimes::find()->where(['mobile' => $user->mobile, 'type' => 24])->one();
        if (!empty($oCard)) {
            $jump_card = 0;
        }
        $can_credit = 0;
        $shop_url = '';
        $shop_reback_url = (new User())->getShopRedisResult('shop_info_',$user,2);
        if ($identify_valid == 2 && $contacts_valid == 2 && $pic_valid == 2 && $juxinli_valid == 2) {
            $can_credit = 1;
            $shop_url = $shop_reback_url;
        }
        $black_user = $user->status == 5 ? true : false ;
        return $this->render('requireinfo', [
            'times' => $videoTimes,
            'identify_valid' => $identify_valid,
            'contacts_valid' => $contacts_valid,
            'pic_valid' => $pic_valid,
            'juxinli_valid' => $juxinli_valid,
            'bank_valid' => $bank_valid,
            'user_id' => $user_id,
            'can_credit' => $can_credit,
            'csrf' => $this->getCsrf(),
            'jump_card' => $jump_card,
            'shop_url' => $shop_url,
            'shop_reback_url' => $shop_reback_url,
            'black_user' => $black_user,
            'source_mall' => $source_mall,
        ]);

    }

    /**
     * 选填资料列表页
     */
    public function actionSelectioninfo() {
        $this->layout = "userinfo/requireinfo";
        $this->getView()->title = "补充资料";
        $user = $this->getUser();
        if(empty($user)){
            exit('用户不存在');
        }
        $source_mark = $this->get('source_mark',0);
        if( $source_mark == 1 ){ //从先花商城跳转过来的
            Yii::$app->redis->setex('shop_info_'.$user->user_id,86400,$user->user_id);
        }

        $selectionData = (new User())->getselectionData($user); //1学历 2社保 3公积金 4京东 6淘宝 1:未认证 2：已认证  3认证中 4：已过期
        $bank_valid = $selectionData['bank_valid']   ;
        $edu_valid = $selectionData['edu_valid']  ;
        $social_valid = $selectionData['social_valid'] ;
        $fund_valid = $selectionData['fund_valid'] ;
        $jd_valid = $selectionData['jd_valid'];
        $bankflow_valid = $selectionData['bankflow_valid'] ;
        $taobao_valid = $selectionData['taobao_valid'] ;

        //根据测评状态和资料修改时间判断按钮及按钮状态
        $res_status = $this->getBtnstatus($user);
        $shop_url = '';
        $shop_reback_url = (new User())->getShopRedisResult('shop_info_',$user,2);
        if($res_status['isImprove'] ){
            $shop_url = $shop_reback_url;
        }
        $black_user = $user->status == 5 ? true : false ;
        return $this->render('selectioninfo', [
            'edu_valid' => $edu_valid,
            'social_valid' => $social_valid,
            'fund_valid' => $fund_valid,
            'jd_valid' => $jd_valid,
            'bank_valid' => $bank_valid,
            'bankflow_valid' => $bankflow_valid,
            'taobao_valid' => $taobao_valid,
            'user_id' => $user->user_id,
            'csrf' => $this->getCsrf(),
            'isShow' => $res_status['isShow'],
            'isCreditshow' => $res_status['isCreditshow'],
            'isImprove' => $res_status['isImprove'],
            'shop_url' => $shop_url,
            'shop_reback_url' => $shop_reback_url,
            'black_user' => $black_user,

        ]);

    }

    /**
     * 选填资料列表页按钮及按钮状态
     * $isShow 页面下方按钮是否显示 isCreditshow：重新获取额度按钮 isImprove：立即加速按钮
     * @param type $user
     */
    private function getBtnstatus($user) {
        $isShow = 0; //1获取额度 2加速
        $isCreditshow = false;
        $isImprove = false;
        
        //检测是否可点击‘立即加速’按钮
        $selectioStatus= (new User_loan())->getUserInfoByTime($user->user_id);
        $creditModel = new User_credit();
        $user_credit = $creditModel->checkYyyUserCredit($user->user_id);
        $oCredit = (new User_credit())->getUserCreditByUserId($user->user_id);
        $rejectCredit = (new User_credit())->getCreditRejectReturn($oCredit); //true：驳回 false:不是驳回
        if(empty($oCredit)){
            return ['isShow' => 0, 'isCreditshow' => false, 'isImprove' => false];
        }
        $shopcredit = $creditModel->getShopCredit($oCredit);
        if ( ($user_credit['user_credit_status'] == 2 ) || ( $rejectCredit && $oCredit['invalid_time']<date('Y-m-d H:i:s',time()) ) ) { //已驳回
            $isShow = 1;
            $creditLastTime = $user_credit['invalid_time'];//评测时间
            $UserCreditByTimeRes = (new User_loan())->getUserCreditByTime($user->user_id, $creditLastTime);
            if ($UserCreditByTimeRes) {
                $isCreditshow = true;//审批未通过且已重新完善资料
            }
        }elseif($oCredit->status == 2 && $oCredit->res_status == 1 && !$shopcredit){ //商城无额度且评测时可借时
            $isShow = 1;
            $creditLastTime = $oCredit->last_modify_time;//评测时间
            $UserCreditByTimeRes = (new User_loan())->getUserCreditByTime($user->user_id, $creditLastTime);
            if ($UserCreditByTimeRes) {
                $isCreditshow = true;//审批未通过且已重新完善资料
            }
        } else if ($user_credit['user_credit_status'] == 3) { //额度获取中
            $isShow = 2;
            if ($selectioStatus) {
                $isImprove = true;//额度获取中且已重新完善资料 可加速
            }
        } else {
            return ['isShow' => 0, 'isCreditshow' => false, 'isImprove' => false];
        }
        return ['isShow' => $isShow, 'isCreditshow' => $isCreditshow, 'isImprove' => $isImprove];
    }
    
   /**
     * 手机号运行商认证结果 : 1:未认证；2:已认证 3已过期
     */
    private function getJuxinli($user) {
        $juxinliModel = new Juxinli();
        $juxinli = $juxinliModel->getJuxinliByUserId($user->user_id);
        $juli = 0;
        if (empty($juxinli) || $juxinli->process_code != '10008') {
            $juli = 1;
        } else {
            if ($juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) >= $juxinli->last_modify_time) {
                $juli = 3;
            } else {
                $juli = 2;
            }
        }
        $xindiao = \app\commonapi\Keywords::xindiao();
        if (!empty($xindiao) && in_array($user->mobile, $xindiao)) {
            $juli = 2;
        }
        return $juli;
    }


    /**
     * 获取csrf
     * @return string
     */
    public function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }


}

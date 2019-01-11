<?php

namespace app\modules\newdev\controllers;

use app\commands\SubController;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Keywords;
use app\models\dev\ApiSms;
use app\models\news\Areas;
use app\models\news\Coupon_list;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\No_repeat;
use app\models\news\User_extend;
use app\models\yyy\XhhApi;
use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Black_list;
use app\models\news\User_history_info;
use Yii;

class CouponController extends NewdevController {

    
    public $layout = 'coupon';
    private $dayratestr = 0.0005;
    private $with_fee = 0.1;
    public $enableCsrfValidation = false;
    public function behaviors() {
        return [];
    }

    public function actionLoan() {
        $this->layout = 'diversion';
        $this->getView()->title = '先花一亿元';
        $mobile = Yii::$app->request->get('mobile');
        $userinfo = User::find()->where(['mobile'=>$mobile])->one();
        $nowtime = date('Y-m-d H:i:s');
        $coupon = Coupon_list::find()->select(array('id', 'title', 'type', 'val', 'limit', 'end_date', 'status'))->where(['mobile' => $mobile, 'status' => 1])->andWhere("start_date < '$nowtime' and end_date > '$nowtime'")->one();
        $desc = Keywords::getLoanDesc();
        return $this->render('loan', [
                    'mobile' => $mobile,
                    'dayratestr' => $this->dayratestr,
                    'couponlist' => $coupon,
                    'desc' => $desc,
        ]);
    }
    /**
     * 优惠券列表
     */
    public function actionCouponlist()
    {
        $this->getView()->title = '优惠券';
        $userId = $this->get('user_id');
        if(empty($userId)){
            $user = $this->getUser();
            if(empty($user)){
                $this->redirect('/new/loan');
            }
            $userId=$user->id;
        }
        $coupon_wsy='';
        $coupon_ygq='';
        if (!empty($userId)){
            $userObj = (new User())->getById($userId);
            //拉取面向全部用户类型的有效优惠券
            $couponlist_pull = (new Coupon_list)->pullCoupon($userObj->mobile);
            $coupon_wsy = (new Coupon_list)->getCouponByMobile($userObj->mobile, 1, 'end_date desc');
            $coupon_ygq = (new Coupon_list)->getCouponByMobile($userObj->mobile, 3);
        }
        //获取微信分享接口所需相关参数
        $jsinfo = $this->getWxParam();
        return $this->render('couponlist', array(
            'coupon_wsy' => $coupon_wsy,
            'coupon_ygq' => $coupon_ygq,
            'user_id' => $userId,
            'jsinfo' => $jsinfo,
        ));
    }

    /**
     * 优惠券错误页面
     */
    public function actionCouponerror()
    {

        return $this->render('couponerror');
    }

    /**
     * 导流页面完善个人信息
     */
    public function actionPersonal() {
        $this->layout = 'diversion';
        $this->view->title = '完善个人信息';
        $post_data = Yii::$app->request->post();
        $mobile = isset($post_data['mobile']) ? $post_data['mobile'] : '';
        $user = User::find()->where(['mobile' => $mobile])->one();
        $norepet = (new No_repeat())->norepeat($user->user_id,$type = 1);
        if(!$norepet){
            echo "<script>alert('操作频繁，稍后再试');window.location.href='/new/coupon/loan?mobile=$mobile'</script>";
            exit;
        }
        if((!empty($user) && !empty($user['identity'])) || empty($user) || empty($mobile)){
            return $this->redirect('/dev/ds/down');
        }
        if (isset($post_data['desc'])) {
            $this->setCookieVal('loan_desc', $post_data['desc']);
        }
        if (isset($post_data['day'])) {
            $this->setCookieVal('loan_days', $post_data['day']);
        }
        if (isset($post_data['amount'])) {
            $this->setCookieVal('loan_amount', $post_data['amount']);
        }
        if (isset($post_data['coupon_id'])) {
            $this->setCookieVal('coupon_id', $post_data['coupon_id']);
        }
        if (isset($post_data['coupon_amount'])) {
            $this->setCookieVal('coupon_amount', $post_data['coupon_amount']);
        }
        $list = Areas::getAllAreas();
        $userinfo = User::find()->where(['mobile' => $mobile])->one();
        $marriage = Keywords::getMarriage();
        $edu = Keywords::getEdu();
        $jsinfo = $this->getWxParam();
        $user_extend = User_extend::getUserExtend($userinfo->user_id);
        return $this->render('personal', [
                    'userinfo' => $userinfo,
                    'user_extend' => $user_extend,
                    'mobile' => $mobile,
                    'marriage' => $marriage,
                    'edu' => $edu,
                    'jsinfo' => $jsinfo,
                    'list' => $list,
        ]);
    }
    
     /**
     * 保存实名认证数据
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionNameauthajax() {
        $post_data = $this->post();
        if(!isset($post_data['identity']) || !isset($post_data['realname']) || !isset($post_data['edu']) || !isset($post_data['userId'])){
            return $this->showMessage(1, "*请填写必填项", 'json');
        }
        $identity = $post_data['identity'];
        $realname = $post_data['realname'];
        $edu = intval($post_data['edu']);
        $user_id = $post_data['userId'];
        $userinfo = User::findOne($user_id);

        //校验post数据
        if (empty($realname)) {
            return $this->showMessage(1, "*请填写您的真实姓名", 'json');
        }
        if (empty($identity)) {
            return $this->showMessage(1, "*请填写您的身份证号", 'json');
        }
        if (empty($post_data['district'])) {
            return $this->showMessage(1, "*请选择常住地址", 'json');
        }
        if (empty($post_data['home_address'])) {
            return $this->showMessage(1, "*详细地址不能为空", 'json');
        }
        if (empty($edu)) {
            return $this->showMessage(1, "*请选择学历", 'json');
        }
        if (empty($this->post('marriage'))) {
            return $this->showMessage(1, "*请选择婚姻", 'json');
        }
        $id_card = $this->chkIdCard($this->post('identity'));
        if (!$id_card) {
            return $this->showMessage(1, '*请填写正确的身份证', 'json');
        }
        $idValid = $userinfo->getIdentityValid($identity);
        if (!$idValid) {
            $identity_valid = 4;
        } else {
            $postdata = array(
                'name' => $realname,
                'idcard' => $identity
            );
            $openApi = new Apihttp;
            $validIdentity = $openApi->idValid($postdata);
            Logger::errorLog(print_r($validIdentity, true), 'identity');
            if ($validIdentity['res_code'] != '0000') {
                return $this->showMessage(2, "身份证号码与姓名不匹配", 'json');
            }
            $identity_valid = 2;
        }

        //保存User_extend信息
        $extend_condition = array(
            'user_id' => $userinfo->user_id,
            'edu' => $post_data['edu'],
            'marriage' => $post_data['marriage'],
            'home_area' => $post_data['district'],
            'home_address' => $post_data['home_address'],
        );
        $userExtendModel = new User_extend();
        $oldExtend = $userExtendModel->getUserExtend($userinfo->user_id);
        //判断数据是否没有更改
        if(isset($oldExtend)){
            if ($userinfo['identity'] == $identity && $oldExtend->edu == $extend_condition['edu'] && $oldExtend->marriage == $extend_condition['marriage'] && $oldExtend->home_area == $extend_condition['home_area'] && $oldExtend->home_address == $extend_condition['home_address']) {
                return $this->showMessage(1, '*数据没有更改,请更新之后提交', 'json');
            }
        }
        $extend_ret = $userExtendModel->save_extend($extend_condition);

        //验证用户身份证号码是否已经存在
        $userIdInfo = User::find()->where(['identity' => $identity])->one();
//        return $this->showMessage(3, $identity);
        if (!empty($userIdInfo)) {
            return $this->showMessage(3, "身份证号码已经存在！", 'json');
        }
        $user_condition['edu'] = (string) $edu;
        if ($userinfo->identity_valid != 2 || $userinfo->identity_valid != 4) {
            $user_condition['realname'] = $realname;
            $user_condition['identity'] = $identity;
            $user_condition['birth_year'] = intval(substr($identity, 6, 4));
            $user_condition['identity_valid'] = $identity_valid;
        }
        $ret = $userinfo->update_user($user_condition);
        if (!$ret) {
            return $this->showMessage(4, "系统错误！", 'json');
        }

        return $this->showMessage(0, array('msg' => 'success'), 'json');
    }

    /**
     * 导流页面完善工作信息
     */
    public function actionCompany() {
        $this->layout = 'diversion';
        $this->view->title = '完善个人信息';
        $user_id = Yii::$app->request->get('user_id');
        if (empty($user_id)) {
            return $this->redirect('/new/reg/loginloan');
        }
        $users = User::findOne($user_id);
        if (empty($users)) {
            return $this->redirect('/new/reg/loginloan');
        }
        if (($users->identity_valid != 2 || $users->identity_valid != 4) && empty($users->realname)) {
            return $this->redirect('/new/coupon/personal?mobile=' . $users->mobile);
        }
        $jsinfo = $this->getWxParam();
        $list = Areas::getAllAreas();
        $industry = Keywords::getIndustry();
        $profession = Keywords::getProfession();
        $position = Keywords::getPosition();
        $user_extend = $users->extend;
        Logger::dayLog('coupon', 'company_userinfo', $users);
        return $this->render('company', [
                    'jsinfo' => $jsinfo,
                    'users' => $users,
                    'industry' => $industry,
                    'profession' => $profession,
                    'position' => $position,
                    'list' => $list,
                    'user_extend' => $user_extend,
        ]);
    }
    
     /**
     * 保存工作信息
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionWorkinfoajax() {
        $post_data = Yii::$app->request->post();
        if(!isset($post_data['user_id']) || !isset($post_data['district']) || !isset($post_data['industry']) || !isset($post_data['profession']) || !isset($post_data['position'])){
            return $this->showMessage(8, "信息错误", 'json');
        }
        Logger::dayLog('coupon', 'workinfoajax', $post_data);
        if(!isset($post_data['user_id']) && empty($post_data['user_id'])){
            return $this->showMessage(8, "信息错误", 'json');
        }
        $user_id = $post_data['user_id'];
        $userinfo = User::findOne($user_id);
        
        if(!isset($userinfo) && empty($userinfo)){
            return $this->showMessage(8, "信息错误", 'json');
        }
        //验证post提交的数据
        if (empty($post_data['district']) || empty($post_data['industry']) || empty($post_data['profession']) || empty($post_data['position'])) {
            return $this->showMessage(1, "请选择相关信息", 'json');
        }
        if (empty($post_data['company']) || empty($post_data['address']) || empty($post_data['email'])) {
            return $this->showMessage(2, "请完整输入信息", 'json');
            return false;
        }
        $phone_chk = $this->chkPhone($post_data['telephone']);
        if (!$phone_chk) {
            return $this->showMessage(4, '请输入正确的单位电话', 'json');
        }
        $email_chk = $this->chkEmail($post_data['email']);
        if (!$email_chk) {
            return $this->showMessage(5, '您的电子邮件格式不正确', 'json');
        }

        //对数据库进行操作
        $transaction = Yii::$app->db->beginTransaction();
        //更新用户信息User_extend
        $extend_condition = array(
            'user_id' => $user_id,
            'industry' => $post_data['industry'],
            'company' => $post_data['company'],
            'position' => $post_data['position'],
            'profession' => $post_data['profession'],
            'telephone' => trim($post_data['telephone']),
            'email' => trim($post_data['email']),
            'income' => $post_data['income'],
            'company_area' => $post_data['district'],
            'company_address' => trim($post_data['address']),
        );
        $userExtendModel = new User_extend();
        $oldExtend = $userExtendModel->getUserExtend($user_id);
        if(empty($oldExtend)){
            $extend_res = $userExtendModel->save_extend($extend_condition);
        }else{
            if ($oldExtend && $oldExtend->industry == $extend_condition['industry'] && $oldExtend->company == $extend_condition['company'] && $oldExtend->position == $extend_condition['position'] && $oldExtend->profession == $extend_condition['profession'] && $oldExtend->telephone == $extend_condition['telephone'] && $oldExtend->email == $extend_condition['email'] && $oldExtend->income == $extend_condition['income'] && $oldExtend->company_area == $extend_condition['company_area'] && $oldExtend->company_address == $extend_condition['company_address']) {
                return $this->showMessage(7, '数据没有更改,请更新之后提交', 'json');
            }
            $extend_res = $userExtendModel->save_extend($extend_condition);
        }
        if(!$extend_res){
            return $this->showMessage(9, '信息有误，请稍后再试', 'json');
        }

        //更新用户信息User
        $user_condition = array(
            'address' => $post_data['address'],
            'company' => $post_data['company'],
            'position' => $post_data['position'],
            'telephone' => $post_data['telephone'],
            'industry' => $post_data['industry'],
        );
        $user_res = $userinfo->update_user($user_condition);
        if (!$user_res) {
            $transaction->rollBack();
            return $this->showMessage(6, '提交失败，请退出重新提交', 'json');
        }

        //判断黑名单
        $black_list = (new Black_list())->getInBlack($userinfo->identity);
        if ($black_list) {
            //跳转到黑名单错误提示页面 *设置黑名单成功事务提交，否则事务回滚
            $retArr = array("msg" => '*黑名单', 'url' => '/new/account/black');
            $black_res = $userinfo->setBlack();
            if (!$black_res) {
                $transaction->rollBack();
                return $this->showMessage(3, $retArr, 'json');
            }
            $transaction->commit();
            return $this->showMessage(3, $retArr, 'json');
        }
        $transaction->commit();

        //第一次完善工作信息走注册决策引擎
        $history_count = User_history_info::find()->where(['user_id' => $userinfo->user_id, 'data_type' => 2])->count();
        $user_extend = $userinfo->extend;
        if ($history_count == 0 && empty($user_extend->company_area)) {
            $regrule = $userinfo->getRegrule($userinfo, 1);
            if ($regrule == 1) {
                $userinfo->setBlack();
                $retArr = array("msg" => '*黑名单', 'url' => '/new/account/black');
                return $this->showMessage(3, $retArr, 'json');
            }
        }
        return $this->showMessage(0, 'success', 'json');
    }

    /**
     * 导流页面审核页面
     */
    public function actionVerify() {
        $this->layout = 'diversion';
        $this->view->title = '等待审核';
        $diversion_from = $this->getCookieVal('diversion_from');
        if (empty($diversion_from)) {
            $diversion_from = '/dev/ds/down';
        }
        $amount = $this->getCookieVal('loan_amount');
        $days = $this->getCookieVal('loan_days');
        $desc = $this->getCookieVal('loan_desc');
        $user_id = Yii::$app->request->get('user_id');
        $userinfo = User::findOne($user_id);
        if (empty($userinfo)) {
            return $this->redirect('/dev/reg/login');
        }

        $suffix = $userinfo->user_id . rand(100000, 999999);
        $loan_no = date("YmdHis") . $suffix;
        $loan_no_keys = $userinfo->user_id . "_loan_no";
        Yii::$app->redis->setex($loan_no_keys, 43200, $loan_no);
        $usernewModel = new User_loan();
        $result = $usernewModel->getRule($userinfo, 1, $amount, $days, $desc, $loan_no, 1);
        
        if(isset($loan_no_keys)){
            Yii::$app->redis->del($loan_no_keys);
        }
//        echo 2;die;
//        var_dump($result);die;
        if ($result == 0) {
            $user_extend = $userinfo->extend;
            $user_extend->is_callback = 1;
            try {
                $user_extend->save();
            } catch (\Exception $ex) {
                return FALSE;
            }
        }
        return $this->render('verify', [
                    'diversion_from' => $diversion_from,
        ]);
    }
    
    
        //删除cookie值
    public function delCookieVal($key) {
        setcookie($key, '', time() - 3600 * 24);
    }

    //设置cookie值
    public function setCookieVal($key, $val) {
        setcookie($key, $val, time() + 3600 * 24);
    }

    //获取cookie值
    public function getCookieVal($key) {
        if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return '';
        }
    }
    
    /**
     * 身份证验证
     * @param int $idcard
     * @return bool
     */
    private function chkIdCard($idcard) {
        $isIDCard1 = "/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/"; //15位
        $isIDCard2 = "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/"; //18位
        if (!preg_match($isIDCard1, $idcard)) {
            if (!preg_match($isIDCard2, $idcard)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 验证邮箱
     * @param string  $email     邮箱
     * @return bool
     */
    private function chkEmail($email) {
        if (empty($email)) {
            return false;
        }
        $pattern = '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        if (!preg_match($pattern, $email)) {
            return false;
        }
        return true;
    }

    /**
     * 电话验证
     * @param  int   $phone     号码
     * @return bool;
     */
    private function chkPhone($phone) {
        if (empty($phone)) {
            return false;
        }
        if (!preg_match('/^0\d{2,3}\-?\d{7,8}$/', $phone)) {
            if (!preg_match('/^1(([35678][0-9])|(47))\d{8}$/', $phone)) {
                return false;
            }
        }
        return true;
    }
}

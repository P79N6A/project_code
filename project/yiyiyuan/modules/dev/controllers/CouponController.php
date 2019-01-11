<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Keywords;
use app\models\dev\Account;
use app\models\dev\ApiSms;
use app\models\dev\Areas;
use app\models\dev\Coupon_apply;
use app\models\dev\Coupon_list;
use app\models\dev\Sms;
use app\models\dev\Standard_coupon_apply;
use app\models\dev\Standard_coupon_list;
use app\models\dev\Statistics_type;
use app\models\dev\User;
use app\models\dev\User_extend;
use app\models\dev\User_temporary_quota;
use app\models\dev\Userxhh;
use app\models\yyy\XhhApi;
use Yii;

class CouponController extends SubController {

    public $layout = 'coupon';
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $this->getView()->title = "理财页";
        return $this->render('index', [
        ]);
    }

    public function actionSucc() {
        $this->getView()->title = "领取优惠劵成功";
        return $this->render('succ', [
        ]);
    }

    public function actionLoginsave() {
        $mobile = $_POST['mobile'];
        $code = $_POST['code'];
        $create_time = date('Y-m-d H:i:s');
        //验证码是否正确
        $key = "login_reg_" . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        if ($code_byredis != $code) {
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }

        //判断手机是否注册
        $userinfo = User::find()->where(['mobile' => $mobile])->one();
        if (!empty($userinfo)) {
            $user_id = $userinfo->user_id;
        }
        if (empty($userinfo)) {
            //完成注册
            $userxhh = Userxhh::find()->where(['mobile' => $mobile])->one();
            if (!empty($userxhh)) {
                $sql = "insert into " . User::tableName() . "(mobile,user_type,school,school_id,edu,school_time,realname,identity,school_valid,identity_valid,come_from,create_time) ";
                $sql .= "value('$mobile',0,'" . $userxhh->school . "',$userxhh->school_id,$userxhh->edu,'" . $userxhh->school_time . "','" . $userxhh->realname . "','" . $userxhh->identity . "',2,2,1,'" . $create_time . "')";
                $retUpdate = Yii::$app->db->createCommand($sql)->execute();

                $userid = Yii::$app->db->getLastInsertID();
                $ip = Common::get_client_ip();
                $userExtendModel = new User_extend();
                $extend = [
                    'user_id' => $userid,
                    'reg_ip' => $ip,
                ];
                $userExtendModel->addRecord($extend);
                $userinfo['school_id'] = $userxhh->school_id;
                $userinfo['identity'] = $userxhh->identity;
                $userinfo['school_time'] = $userxhh->school_time;
                $userinfo['edu'] = $userxhh->edu;
                $ret_acc = $this->createAccount($userid, $userinfo);
            } else {
                //用户自己的邀请码
                $invite_code = $this->getCode();
                //保存用户信息
                $create_time = date('Y-m-d H:i:s');
                $sql = "insert into " . User::tableName() . "(mobile,user_type,invite_code,create_time) value('$mobile','0','$invite_code','$create_time')";
                $ret = Yii::$app->db->createCommand($sql)->execute();

                $user_id = Yii::$app->db->getLastInsertID();
                $ip = Common::get_client_ip();
                $userExtendModel = new User_extend();
                $extend = [
                    'user_id' => $userid,
                    'reg_ip' => $ip,
                ];
                $userExtendModel->addRecord($extend);
                //创建账户信息
                $ret_acc = $this->createAccount($user_id);
            }
        }
        $this->sendcoupon($user_id, 2);
        $userTempoaryModel = new User_temporary_quota();
        $userTempoaryModel->setTemporary($user_id, 500, 28, '注册提临额', 1);
        $resultArr = array('ret' => '0', 'url' => '');
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 先花花发送7天双倍收益券
     * @param type $user_id
     * @param type $type
     */
    public function sendcoupon($user_id, $type) {
        $nowtime = date('Y-m-d H:i:s');
        $standard = Standard_coupon_apply::find()->where(['type' => $type, 'start_date' => date('Y-m-d 00:00:00'), 'cycle' => 7, 'field' => 2, 'end_date' => date('Y-m-d 00:00:00', strtotime("+7 days")), 'apply_depart' => -1, 'apply_user' => -1, 'audit_person' => -1, 'status' => 3])->one();
        if (empty($standard)) {
            $standard = new Standard_coupon_apply();
            $standard->title = '7天双倍收益优惠券';
            $standard->type = $type;
            $standard->cycle = 7;
            $standard->field = 2;
            $standard->number = 10000;
            $standard->send_num = 0;
            $standard->start_date = date('Y-m-d 00:00:00');
            $standard->end_date = date('Y-m-d 00:00:00', strtotime("+7 days"));
            $standard->apply_depart = -1;
            $standard->apply_user = -1;
            $standard->audit_person = -1;
            $standard->status = 3;
            $standard->create_time = $nowtime;
            $standard->audit_time = $nowtime;
            $standard->version = 1;
            $standard->save();
        }
        $sn = date('ymdHis', time()) . '1';
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        $mobile = $userinfo['mobile'];
        $standlist = Standard_coupon_list::find()->where(['type' => $type, 'mobile' => $mobile, 'cycle' => 7, 'field' => 2])->one();
        if (empty($standlist)) {
            $sql = "insert into " . Standard_coupon_list::tableName() . "(apply_id,title,type,sn,cycle,field,start_date,end_date,mobile,status,create_time) value('" . $standard['id'] . "','" . $standard['title'] . "'," . $standard['type'] . ",'$sn'," . $standard['cycle'] . ",'" . $standard['field'] . "','" . $standard['start_date'] . "','" . $standard['end_date'] . "','$mobile',1,'$nowtime')";
            $ret = Yii::$app->db->createCommand($sql)->execute();
            if ($ret) {
                $send_num = $standard->send_num + 1;
                $stas = $standard->number > $send_num ? 3 : 5;
                $applystatus = "update " . Standard_coupon_apply::tableName() . " set status=$stas,send_num=$send_num where id=" . $standard['id'];
                $ret_apply = Yii::$app->db->createCommand($applystatus)->execute();
            }
        } else {
            $resultArr = array('ret' => '11', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
    }

    public function actionGetcoupon() {
        $this->layout = 'getcoupon';
        $this->getView()->title = '先花一亿元,借款新技能!';
        return $this->render('getcoupon');
    }

    public function actionGetsuccess() {
        $this->layout = 'getcoupon';
        $this->getView()->title = '先花一亿元';
        $mobile = Yii::$app->request->get('mobile');
        $coupon = Coupon_list::find()->where(['title' => '66元借款减息券', 'type' => 1, 'mobile' => $mobile, 'val' => 66])->one();
        return $this->render('getsuccess', array(
                    'mobile' => $mobile,
                    'coupon' => $coupon,
        ));
    }

    public function actionGetcouponcode() {
        $post_data = Yii::$app->request->post();
        $val = Yii::$app->request->post('val', 28);
        if (empty($post_data)) {
            $resultArr = array('ret' => '-1');
            echo json_encode($resultArr);
            exit;
        }
        $imgCode = isset($post_data['img_code']) ? $post_data['img_code'] : '';
        if (empty($imgCode) || $this->getVal('code_char') != strtolower($imgCode)) {
            $resultArr = array('ret' => '6');
            echo json_encode($resultArr);
            exit;
        }
        $reg = "/^(1(([3578][0-9])|(47)))\d{8}$/";
        if (!preg_match($reg, $post_data['mobile'])) {
            $resultArr = array('ret' => '-1');
            echo json_encode($resultArr);
            exit;
        }

        $mobile = $post_data['mobile'];
        $userinfo = User::find()->where(['mobile' => $post_data['mobile']])->one();
        if ($userinfo) {
            if ($val == 66 || $val == 0) {
                $resultArr = array('ret' => '2');
                echo json_encode($resultArr);
                exit;
            }
            $title = $val . '元借款减息券';
            if ($val == 0) {
                $title = '全免券';
            }
            $standlist = Coupon_list::find()->where(['title' => $title, 'type' => 1, 'mobile' => $mobile, 'val' => $val])->one();
            if (!empty($standlist)) {
                $resultArr = array('ret' => '5');
                echo json_encode($resultArr);
                exit;
            }
        }
        $result = $this->sendCode($post_data['mobile'], 18);
        $resultArr = array('ret' => $result);
//        print_r($resultArr);exit;
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 
     * @param type $redis_key
     * @param type $type
     * @param string $content
     * @return int 1:超过限制，2:发送失败，0:发送成功
     */
    private function sendCode($mobile, $type) {
        //一天只能发送6条短信
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $sms_count = Sms::find()->where("recive_mobile='$mobile' and sms_type='$type' and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        if ($sms_count >= 6) {
            return 1;
        }
        $api = new ApiSms();
        $sendRet = $api->sendReg($mobile, 18);
        if ($sendRet) {
            return 0;
        } else {
            return 2;
        }
    }

    public function actionSendcoupon() {
        $post_data = Yii::$app->request->post();
        $imgCode = isset($post_data['img_code']) ? $post_data['img_code'] : '';
        if (empty($imgCode) || $this->getVal('code_char') != strtolower($imgCode)) {
            $resultArr = array('ret' => '6');
            echo json_encode($resultArr);
            exit;
        }
        $from_code = isset($post_data['from_code']) ? $post_data['from_code'] : '';
        if (!empty($from_code)) {
            $invite_user = User::find()->where(['invite_code' => $from_code])->one();
            if (empty($invite_user)) {
                $resultArr = array('ret' => '11'); //邀请码错误,不存在邀请码
                echo json_encode($resultArr);
                exit;
            }
        }
        $val = Yii::$app->request->post('val', 28);
        $reg = "/^(1(([3578][0-9])|(47)))\d{8}$/";
        if (!preg_match($reg, $post_data['mobile'])) {
            $resultArr = array('ret' => '-1'); //手机号格式错误
            echo json_encode($resultArr);
            exit;
        }
        $user = new User();
        $userinfo = $user->getUserinfoByMobile($post_data['mobile']);
        if ($val == 66 || $val == 0) {
            if ($userinfo) {
                $resultArr = array('ret' => '-2');
                echo json_encode($resultArr);
                exit;
            }
        }
        if (!$post_data['code']) {
            $resultArr = array('ret' => '-3');
            echo json_encode($resultArr);
            exit;
        }
        $redis_key = 'getcode_register_' . $post_data['mobile'];
        $code_byredis = Yii::$app->redis->get($redis_key);
        if ($post_data['code'] == $code_byredis) {
            $user = new User();
            $userinfo = $user->getUserinfoByMobile($post_data['mobile']);
            if (empty($userinfo)) {
                $user_id = $this->saveMobile($post_data['mobile'], intval($post_data['come_from']), $from_code);
            } else {
                if ($val == 66 || $val == 0) {
                    $resultArr = array('ret' => '2'); //添加用户失败，领取失败
                    echo json_encode($resultArr);
                    exit;
                }
                $user_id = $userinfo->user_id;
            }
            if (!$user_id) {
                $resultArr = array('ret' => '2'); //添加用户失败，领取失败
                echo json_encode($resultArr);
                exit;
            }
            $days = $val == 66 || $val == 0 ? 31 : 7;
            $standardModel = new Coupon_apply();
            $title = $val . '元借款减息券';
            if ($val == 0) {
                $title = '全免券';
            }
            //2017-11-27 停止优惠券发放
//            $standardModel->sendcoupon($user_id, $title, 1, $days, $val);
            Yii::$app->redis->del($redis_key);
//            $this->sendSms($post_data['mobile']);
            $resultArr = array('ret' => '0'); //发送成功
            echo json_encode($resultArr);
            exit;
        } else {
            $resultArr = array('ret' => '1'); //验证码错误
            echo json_encode($resultArr);
            exit;
        }
    }

    private function sendSms($mobile, $sms_type = 35) {
        $api = new ApiSms();
        $sendRet = $api->sendCoupon($mobile, $sms_type);
        return $sendRet;
    }

    private function saveMobile($mobile, $come_from = 7, $from_code = '') {
        //获取自己的邀请码
        $invite_code = $this->getCode();
        $now_time = date('Y-m-d H:i:s');
        $userxhh = new Userxhh();
        $user = new User();
        $account = new Account();
        $user_array = array(
            'mobile' => $mobile,
            'user_type' => 2,
            'invite_code' => $invite_code,
            'come_from' => $come_from,
            'create_time' => $now_time,
            'last_login_time' => $now_time,
            'last_login_type' => 'weixin'
        );
        if (!empty($from_code)) {
            $invite_user = User::find()->where(['invite_code' => $from_code])->one();
            if (!empty($invite_user)) {
                $user_array['from_code'] = $from_code;
            }
        }
        $transaction = Yii::$app->db->beginTransaction();
        $user_id = $user->addUser($user_array);
        if ($user_id) {
            $userTempoaryModel = new User_temporary_quota();
            $userTempoaryModel->setTemporary($user_id, 500, 28, '注册提临额', 1);
            $ret_acc = $account->createAccount($user_id);
            if ($ret_acc) {
                $transaction->commit();
                return $user_id;
            } else {
                $transaction->rollBack();
                return false;
            }
        } else {
            $transaction->rollBack();
            return false;
        }
    }

    public function actionCouponsix() {
        $this->layout = 'couponsix';
        $come_from = Yii::$app->request->get('from', 7);
        $statistics = Statistics_type::find()->where(['come_from' => $come_from])->one();
        $type = isset($statistics) ? $statistics->id : 46;
        $this->getView()->title = '领取66元的借款优惠券';
        return $this->render('couponsix', [
                    'come_from' => $come_from,
                    'type' => $type,
        ]);
    }

    public function actionSixsuccess() {
        $this->layout = 'couponsix';
        $mobile = Yii::$app->request->get('mobile');
        $this->getView()->title = '领取成功';
        $coupon = Coupon_list::find()->where(['title' => '66元借款减息券', 'type' => 1, 'mobile' => $mobile, 'val' => 66])->one();
        return $this->render('sixsuccess', array(
                    'mobile' => $mobile,
                    'coupon' => $coupon,
        ));
    }

    public function actionCouponappsix() {
        $this->layout = 'couponsix';
        $come_from = Yii::$app->request->get('from', 7);
        $statistics = Statistics_type::find()->where(['come_from' => $come_from])->one();
        $type = isset($statistics) ? $statistics->id : 46;
        $this->getView()->title = '领取66元的借款优惠券';
        return $this->render('couponappsix', [
                    'come_from' => $come_from,
                    'type' => $type,
        ]);
    }
    
    public function actionCouponappsixnew() {
        $this->layout = 'diversion';
        $come_from = Yii::$app->request->get('from', 7);
        $statistics = Statistics_type::find()->where(['come_from' => $come_from])->one();
        $type = isset($statistics) ? $statistics->id : 46;
        $this->getView()->title = '先花一亿元';
        $mob_type = $this->get_device_type();
        $sql = "select * from yi_app_version ORDER BY id desc";
    	$model = \Yii::$app->db->createCommand($sql)->queryOne();
        return $this->render('couponappsixnew', [
                    'come_from' => $come_from,
                    'type' => $type,
                    'mob_type' => $mob_type,
                    'downloan_url' => $model['download_url'],
        ]);
    }
    
    /**
     * 判断是什么手机型号;
     */

    function get_device_type()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        //分别进行判断
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $type = 'ios';
        }

        if (strpos($agent, 'android')) {
            $type = 'android';
        }
        return $type;
    }

    public function actionCouponappsixs() {
        $this->layout = 'couponsix';
        $come_from = Yii::$app->request->get('from', 7);
        $statistics = Statistics_type::find()->where(['come_from' => $come_from])->one();
        $type = isset($statistics) ? $statistics->id : 46;
        $this->getView()->title = '领取66元的借款优惠券';
        return $this->render('couponappsixs', [
                    'come_from' => $come_from,
                    'type' => $type,
        ]);
    }

    public function actionSixappsuccess() {
        $this->layout = 'couponsix';
        $mobile = Yii::$app->request->get('mobile');
        $this->getView()->title = '领取成功';
        $coupon = Coupon_list::find()->where(['title' => '66元借款减息券', 'type' => 1, 'mobile' => $mobile, 'val' => 66])->one();
        return $this->render('sixappsuccess', array(
                    'mobile' => $mobile,
                    'coupon' => $coupon,
        ));
    }

    public function actionCouponall() {
        $this->layout = 'couponsix';
        $come_from = Yii::$app->request->get('from', 7);
        $jsinfo = $this->getWxParam();
        $shareurl = Yii::$app->request->hostInfo . "/dev/coupon/couponall?from=" . $come_from;
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . urlencode($shareurl) . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $this->getView()->title = '领取借款全免优惠券';
        return $this->render('couponall', [
                    'come_from' => $come_from,
                    'jsinfo' => $jsinfo,
                    'shareurl' => $shareUrl
        ]);
    }

    public function actionAllsuccess() {
        $this->layout = 'couponsix';
        $mobile = Yii::$app->request->get('mobile');
        $this->getView()->title = '领取成功';
        $coupon = Coupon_list::find()->where(['title' => '全免券', 'type' => 1, 'mobile' => $mobile, 'val' => 66])->one();
        return $this->render('allsuccess', array(
                    'mobile' => $mobile,
                    'coupon' => $coupon,
        ));
    }

    /**
     * 导流页面 注册
     */
    public function actionDiversion() {
        $this->layout = 'diversion';
        $this->getView()->title = '先花一亿元';
        $come_from = Yii::$app->request->get('from', 2);
        $statistics = Statistics_type::find()->where(['come_from' => $come_from])->one();
        $type = isset($statistics) ? $statistics->id : 46;
        $from_url = Yii::$app->request->referrer;
        if (empty($from_url)) {
            $from_url = '/dev/ds/down';
        }
        $this->setCookieVal('diversion_from', $from_url);
        return $this->render('diversion', [
                    'come_from' => $come_from,
                    'type' => $type,
        ]);
    }

    public function actionLoan() {
        $this->layout = 'diversion';
        $this->getView()->title = '先花一亿元';
        $mobile = Yii::$app->request->get('mobile');
        $nowtime = date('Y-m-d H:i:s');
        $coupon = Coupon_list::find()->select(array('id', 'title', 'type', 'val', 'limit', 'end_date', 'status'))->where(['mobile' => $mobile, 'status' => 1])->andWhere("start_date < '$nowtime' and end_date > '$nowtime'")->one();
        $desc = Keywords::getLoanDesc();
        return $this->render('loan', [
                    'mobile' => $mobile,
                    'dayratestr' => 0.0005,
                    'couponlist' => $coupon,
                    'desc' => $desc,
        ]);
    }

    public function actionLoansecond() {
        $this->layout = 'diversion';
        $this->getView()->title = '先花一亿元';
        $mobile = Yii::$app->request->post('mobile');
        $user = User::find()->where(['mobile' => $mobile])->one();
        if (empty($user)) {
            
        }
        $desc = isset($_POST['desc']) ? $_POST['desc'] : $this->getCookieVal('loan_desc');
        $days = isset($_POST['day']) ? $_POST['day'] : $this->getCookieVal('loan_days');
        $amount = isset($_POST['amount']) ? $_POST['amount'] : $this->getCookieVal('loan_amount');
        $coupon_id = isset($_POST['coupon_id']) ? $_POST['coupon_id'] : $this->getCookieVal('coupon_id');
        $coupon_amount = isset($_POST['coupon_amount']) ? $_POST['coupon_amount'] : $this->getCookieVal('coupon_amount');
        //把借款信息存到cookie里
        if (isset($_POST['desc'])) {
            $this->setCookieVal('loan_desc', $desc);
        }
        if (isset($_POST['day'])) {
            $this->setCookieVal('loan_days', $days);
        }
        if (isset($_POST['amount'])) {
            $this->setCookieVal('loan_amount', $amount);
        }
        if (isset($_POST['coupon_id'])) {
            $this->setCookieVal('coupon_id', $coupon_id);
        }
        if (isset($_POST['coupon_amount'])) {
            $this->setCookieVal('coupon_amount', $coupon_amount);
        }



        $mobile = Yii::$app->request->get('mobile');
        $nowtime = date('Y-m-d H:i:s');
        $coupon = Coupon_list::find()->select(array('id', 'title', 'type', 'val', 'limit', 'end_date', 'status'))->where(['mobile' => $mobile, 'status' => 1])->andWhere("start_date < '$nowtime' and end_date > '$nowtime'")->one();
        $desc = Keywords::getLoanDesc();
        return $this->render('loan', [
                    'mobile' => $mobile,
                    'dayratestr' => 0.0005,
                    'couponlist' => $coupon,
                    'desc' => $desc,
        ]);
    }

    /**
     * 导流页面完善个人信息
     */
    public function actionPersonal() {
        $this->layout = 'diversion';
        $this->view->title = '完善个人信息';
        $post_data = Yii::$app->request->post();
        $mobile = isset($post_data['mobile']) ? $post_data['mobile'] : '';
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
     * 导流页面完善工作信息
     */
    public function actionCompany() {
        $this->layout = 'diversion';
        $this->view->title = '完善个人信息';
        $user_id = Yii::$app->request->get('user_id');
        if (empty($user_id)) {
            return $this->redirect('/dev/reg/login');
        }
        $users = User::findOne($user_id);
        if (empty($users)) {
            return $this->redirect('/dev/reg/login');
        }
        if (($users->identity_valid != 2 || $users->identity_valid != 4) && empty($users->realname)) {
            return $this->redirect('/dev/coupon/personal?mobile=' . $users->mobile);
        }
        $jsinfo = $this->getWxParam();
        $list = Areas::getAllAreas();
        $industry = Keywords::getIndustry();
        $profession = Keywords::getProfession();
        $position = Keywords::getPosition();
        $user_extend = $users->extend;
        return $this->render('company', [
                    'jsinfo' => $jsinfo,
                    'users' => $users,
                    'industry' => $industry,
                    'profession' => $profession,
                    'position' => $position,
                    'list' => $list,
                    'user_extend' => $user_extend,
                    'csrf' => $this->getCsrf(),
        ]);
    }
    
    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 导流页面审核页面
     */
    public function actionVerify() {
        $this->layout = 'diversion';
        $this->view->title = '等待审核';
        $diversion_from = $this->getCookieVal('diversion_from');
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
        $usernewModel = new \app\models\news\User_loan();
        $result = $usernewModel->getRule($userinfo, 1, $amount, $days, $desc, $loan_no, 1);
        $diversion_from = $this->delCookieVal('diversion_from');
        if(isset($loan_no_keys)){
            Yii::$app->redis->del($loan_no_keys);
        }
        $this->delCookieVal('loan_amount');
        $this->delCookieVal('loan_days');
        $this->delCookieVal('loan_desc');
        if ($result == 0) {
            $user_extend = $userinfo->extend;
            $user_extend->is_callback = 1;
            $user_extend->save();
        }
        $jsinfo = $this->getWxParam();
        return $this->render('verify', [
                    'jsinfo' => $jsinfo,
                    'diversion_from' => $diversion_from,
        ]);
    }

    /**
     * 智齿客服系统展示个人邀请码页面
     */
    public function actionShowinvitecode() {
        $user = new User();
        $userinfo = $user->getUserinfoByOpenid($_GET['partnerId']);
        if ($userinfo) {
            $user_attribute = "注册用户";
            $invite_code = $userinfo->invite_code;
        } else {
            $user_attribute = "非注册用户";
            $invite_code = "";
        }

        return $this->render('showinvitecode', [
                    'invite_code' => $invite_code,
                    'user_attribute' => $user_attribute,
        ]);
    }

}

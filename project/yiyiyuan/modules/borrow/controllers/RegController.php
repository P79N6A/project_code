<?php

namespace app\modules\borrow\controllers;

use app\common\ApiCrypt;
use app\commonapi\ApiSms;
use app\commonapi\ApiSobot;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Common;
use app\models\news\Sms;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_password;
use app\models\news\User_wx;
use Yii;

class RegController extends BorrowController {

    public $layout = 'main';

    public function behaviors() {
        return [];
    }

    /**
     * 登录页-手机号码页
     * @return string
     * @author 王新龙
     * @date 2018/8/21 16:52
     */
    public function actionLogin() {
        $code = $this->get('code', '');
        $session_url = $this->get('url', '');
        $agreement = $this->get('agreement', 2);
        $utm_source = $this->get('utm_source', '');
        $utm_medium = $this->get('utm_medium', '');
        $utm_campaign = $this->get('utm_campaign', '');
        $utm_content = $this->get('utm_content', '');
        $utm_term = $this->get('utm_term', '');

        //压力测试活动 邀请码来源
        $invite_code = $this->get('invite_code', '');
        $comeFrom = $this->get('comeFrom', '');
        if (!empty($code)) {
            $this->setVal('code', $code);
        }
        if (!empty($session_url)) {
            $this->setVal('url', $session_url);
        }
        $o_user = $this->getUser();
        if (!empty($o_user)) {
            //保存openid，赚钱妖怪要用
            $this->setSessionOpenid($o_user->user_id);
            $session_url = $this->getVal('url');
            $url = '/borrow/loan';
            if (!empty($session_url)) {
                $this->delVal('url');
                $url = $session_url;
            }
            return $this->redirect('/borrow/loan?utm_source=' . $utm_source . '&utm_medium=' . $utm_medium . '&utm_campaign=' . $utm_campaign . '&utm_content=' . $utm_content . '&utm_term=' . $utm_term);
        }
        $from_code = $this->getVal('from_code');
        $come_from = $this->getVal('come_from');
        $this->layout = 'reg/login';
        $this->getView()->title = "欢迎登录";
        $jsinfo = $this->getWxParam();
        return $this->render('login', [
                    'jsinfo' => $jsinfo,
                    'from_code' => $from_code,
                    'come_from' => $come_from,
                    'csrf' => $this->getCsrf(),
                    'agreement' => $agreement,
                    'invite_code' => $invite_code,
                    'comeFrom' => $comeFrom,
        ]);
    }

    /**
     * ajax_手机号路由
     * @author 王新龙
     * @date 2018/8/22 19:04
     */
    public function actionLoginmobile() {
        if ($this->isPost()) {
            $mobile = $this->post('mobile', '');
            $invite_code = $this->post('invite_code', '');
            $comeFrom = $this->post('comeFrom', '1');
            if (empty($mobile)) {
                exit(json_encode($this->reback('99994', '', '手机号码不能为空'), JSON_UNESCAPED_UNICODE));
            }
            $array = ['type' => 2, 'url' => '/borrow/reg/smspage?mobile=' . $mobile . '&invite_code=' . $invite_code . '&comeFrom=' . $comeFrom];
            $o_user = (new User())->getUserinfoByMobile($mobile);
            if (empty($o_user)) {
                exit(json_encode($this->reback('0000', $array), JSON_UNESCAPED_UNICODE));
            }
            $o_user_password = (new User_password())->getUserPassword($o_user->user_id);
            if (empty($o_user_password) || empty($o_user_password->login_password)) {
                exit(json_encode($this->reback('0000', $array), JSON_UNESCAPED_UNICODE));
            }
            $array = ['type' => 1, 'url' => '/borrow/reg/signin?mobile=' . $mobile];
            exit(json_encode($this->reback('0000', $array), JSON_UNESCAPED_UNICODE));
        } else {
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 密码登录页
     * @return string|\yii\web\Response
     * @author 王新龙
     * @date 2018/8/22 19:20
     */
    public function actionSignin() {
        $this->layout = 'reg/login';
        $this->getView()->title = "欢迎登录";
        $mobile = $this->get('mobile', '');
        if (empty($mobile)) {
            return $this->redirect('/borrow/reg/login');
        }
        $remember_password = $this->getVal('remember_' . $mobile);
        $password = '';
        $remember = 2;
        if (!empty($remember_password)) {
            $password = $remember_password;
            $remember = 1;
        }
        $jsinfo = $this->getWxParam();
        return $this->render('signin', [
                    'jsinfo' => $jsinfo,
                    'mobile' => $mobile,
                    'password' => $password,
                    'remember' => $remember,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    public function actionAjaxsignin() {
        if ($this->isPost()) {
            $mobile = $this->post('mobile', '');
            $password = $this->post('password', '');
            $remember = $this->post('remember', '');

            if (empty($mobile) || empty($password)) {
                exit(json_encode($this->reback('99994', '', '手机号码或密码不能为空'), JSON_UNESCAPED_UNICODE));
            }
            $remember_password = $this->getVal('remember_' . $mobile);
            if (!empty($remember_password)) {
                $password = $remember_password;
            }
            $o_user = (new User())->getUserinfoByMobile($mobile);
            if (empty($o_user)) {
                exit(json_encode($this->reback('10001'), JSON_UNESCAPED_UNICODE));
            }
            $o_user_password = (new User_password())->getUserPassword($o_user->user_id);
            if (empty($o_user_password)) {
                exit(json_encode($this->reback('10007'), JSON_UNESCAPED_UNICODE));
            }
            if ((new ApiCrypt())->decrypt($o_user_password->login_password, 'YnbwODeaphDCl2LlJ7qk0eZ2') != $password) {
                exit(json_encode($this->reback('10013'), JSON_UNESCAPED_UNICODE));
            }
            if (!empty($remember) && $remember == 1) {
                $this->setVal('remember_' . $mobile, $password);
            }
            if (empty($remember) || $remember == 2) {
                $this->delVal('remember_' . $mobile);
            }
            $update_arr = [
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_type' => 'weixin',
            ];
            $o_user->update_user($update_arr);
            Yii::$app->newDev->login($o_user, 1);
            $this->setSessionOpenid($o_user->user_id);
            $session_url = $this->getVal('url');
            $url = '/borrow/loan';
            if (!empty($session_url)) {
                $this->delVal('url');
                $url = $session_url;
            }
            exit(json_encode($this->reback('0000', ['url' => $url]), JSON_UNESCAPED_UNICODE));
        } else {
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 验证码页面
     * @return string|\yii\web\Response
     * @author 王新龙
     * @date 2018/8/22 19:57
     */
    public function actionSmspage() {
        $this->layout = 'reg/login';
        $this->getView()->title = "欢迎登录";
        $mobile = $this->get('mobile', '');
        $type = $this->get('type', 1);

        $invite_code = $this->get('invite_code', '');
        $comeFrom = $this->get('comeFrom', '1');

        if (empty($mobile)) {
            return $this->redirect('/borrow/reg/login');
        }
        preg_match('/([\d]{3})([\d]{4})([\d]{4})/', $mobile, $match);
        unset($match[0]);
        $format_mobile = implode(' ', $match);
        $jsinfo = $this->getWxParam();
        return $this->render('smspage', [
                    'jsinfo' => $jsinfo,
                    'mobile' => $mobile,
                    'format_mobile' => $format_mobile,
                    'type' => $type,
                    'csrf' => $this->getCsrf(),
                    'invite_code' => $invite_code,
                    'comeFrom' => $comeFrom,
        ]);
    }

    /**
     * ajax_短信验证码验证
     * @author 王新龙
     * @date 2018/8/24 14:49
     */
    public function actionSmscode() {
        if ($this->isPost()) {
            $mobile = Yii::$app->request->post('mobile');
            $code = Yii::$app->request->post('code');
            $img_code = Yii::$app->request->post('img_code');

            $invite_code = Yii::$app->request->post('invite_code');
            $comeFrom = Yii::$app->request->post('comeFrom');

            $type = Yii::$app->request->post('type', 1); //1：登录流程 2：重置密码流程
            if (empty($mobile) || empty($code)) {
                exit(json_encode($this->reback('99994'), JSON_UNESCAPED_UNICODE));
            }
            if ($type == 1) {
                $sms_type = 2;
            } else {
                $sms_type = 19;
            }
            $sms_count = (new Sms())->getSmsCount($mobile, $sms_type);
            //已经发送过验证码，需要显示图形验证码
            if ($sms_count > 1 && empty($img_code)) {
                exit(json_encode($this->reback('0000', ['is_imgcode' => '1']), JSON_UNESCAPED_UNICODE));
            }
            $code_char = Yii::$app->session->get('code_char');
            if ($sms_count > 1 && !empty($img_code) && strtolower($img_code) != $code_char) {
                exit(json_encode($this->reback('0000', ['is_imgcode' => '1']), JSON_UNESCAPED_UNICODE));
            }
            $key = "getcode_register_" . $mobile;
            $code_byredis = Yii::$app->redis->get($key);
            $code_byredis = $this->getRedis($key);
            if (empty($code_byredis)) {
                exit(json_encode($this->reback('10004'), JSON_UNESCAPED_UNICODE));
            }
            if ($code != $code_byredis) {
                exit(json_encode($this->reback('10005'), JSON_UNESCAPED_UNICODE));
            }
            $this->delRedis($key);
            $password_kay = md5(md5($mobile . '12w!%x22l%'));
            $this->setRedis('password_key_' . $mobile, $password_kay);
            $o_user = (new User())->getUserinfoByMobile($mobile);
            switch ($type) {
                case 1:
                    $this->loginSmscode($o_user, $mobile, $invite_code, $comeFrom);
                    break;
                case 2:
                    $this->resetSmscode($o_user);
                    break;
                default:
                    exit(json_encode($this->reback('99996'), JSON_UNESCAPED_UNICODE));
                    break;
            }
        } else {
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    public function actionSendsms() {
        if ($this->isPost()) {
            $mobile = $this->post('mobile', '');
            $img_code = $this->post('img_code', '');
            $type = $this->post('type', '');
            $sms_type = $this->post('sms_type', '');
            if ($sms_type == 2) {
                $sms_type = 19; //重置
            } else {
                $sms_type = 2; //登录
            }
            if (empty($mobile)) {
                exit(json_encode($this->reback('99994', '', '手机号码不能为空'), JSON_UNESCAPED_UNICODE));
            }
            $is_mobile = (new Common())->isMobile($mobile);
            if (empty($is_mobile)) {
                exit(json_encode($this->reback('99994', '', '手机号码格式不正确'), JSON_UNESCAPED_UNICODE));
            }
            $sms_count = (new Sms())->getSmsCount($mobile, $sms_type);
            //超过5次限制
            if ($sms_count >= 5) {
                exit(json_encode($this->reback('10003'), JSON_UNESCAPED_UNICODE));
            }
            //已经发送过验证码，需要显示图形验证码
            if ($type == 2) {//强制弹窗刷新图片验证码
                exit(json_encode($this->reback('0000', ['is_imgcode' => '1']), JSON_UNESCAPED_UNICODE));
            }
            if ($sms_count > 0 && empty($img_code)) {
                exit(json_encode($this->reback('0000', ['is_imgcode' => '1']), JSON_UNESCAPED_UNICODE));
            }
            $code_char = Yii::$app->session->get('code_char');
            if ($sms_count > 0 && !empty($img_code) && strtolower($img_code) != $code_char) {
                exit(json_encode($this->reback('0000', ['is_imgcode' => '1']), JSON_UNESCAPED_UNICODE));
            }
            $sendRet = (new ApiSms())->sendReg($mobile, $sms_type);
            if (!$sendRet) {
                exit(json_encode($this->reback('10065'), JSON_UNESCAPED_UNICODE));
            }
            exit(json_encode($this->reback('0000'), JSON_UNESCAPED_UNICODE));
        } else {
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * @return string|\yii\web\Response
     * @author 王新龙
     * @date 2018/8/23 14:28
     */
    public function actionCheckid() {
        $this->layout = 'reg/login';
        $this->getView()->title = "欢迎登录";
        $mobile = $this->get('mobile', '');
        if (empty($mobile)) {
            return $this->redirect('/borrow/reg/login');
        }
        $jsinfo = $this->getWxParam();
        return $this->render('checkid', [
                    'jsinfo' => $jsinfo,
                    'mobile' => $mobile,
                    'csrf' => $this->getCsrf()
        ]);
    }

    public function actionAjaxcheckid() {
        if ($this->isPost()) {
            $mobile = $this->post('mobile', '');
            $identity = $this->post('identity', '');
            if (empty($mobile) || empty($identity)) {
                exit(json_encode($this->reback('99994'), JSON_UNESCAPED_UNICODE));
            }
            $o_user = (new User())->getUserinfoByMobile($mobile);
            if (empty($o_user)) {
                exit(json_encode($this->reback('10001'), JSON_UNESCAPED_UNICODE));
            }
            if (empty($o_user->identity)) {
                exit(json_encode($this->reback('10238'), JSON_UNESCAPED_UNICODE));
            }
            $identity_str = substr($o_user->identity, -6);
            if (strtolower($identity_str) != strtolower($identity)) {
                exit(json_encode($this->reback('10006'), JSON_UNESCAPED_UNICODE));
            }
            exit(json_encode($this->reback('0000'), JSON_UNESCAPED_UNICODE));
        } else {
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 设置密码
     * @return string|\yii\web\Response
     * @author 王新龙
     * @date 2018/8/23 14:20
     */
    public function actionSetpassword() {
        $this->layout = 'reg/login';
        $this->getView()->title = "欢迎登录";
        $mobile = $this->get('mobile', '');
        if (empty($mobile)) {
            return $this->redirect('/borrow/reg/login');
        }
        $password_kay_redis = $this->getRedis('password_key_' . $mobile);
        $password_kay = md5(md5($mobile . '12w!%x22l%'));
        if ($password_kay_redis != $password_kay) {
            return $this->redirect('/borrow/reg/login');
        }
        $o_user = (new User())->getUserinfoByMobile($mobile);
        if (empty($o_user)) {
            return $this->redirect('/borrow/reg/login');
        }
        $o_user_password = (new User_password())->getUserPassword($o_user->user_id);
        $type = 1;
        if (!empty($o_user_password) && !empty($o_user_password->login_password)) {
            $type = 2;
        }
        $jsinfo = $this->getWxParam();
        return $this->render('setpassword', [
                    'jsinfo' => $jsinfo,
                    'mobile' => $mobile,
                    'type' => $type,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * ajax_设置密码
     * @author 王新龙
     * @date 2018/8/23 19:46
     */
    public function actionAjaxsetpassword() {
        if ($this->isPost()) {
            $mobile = $this->post('mobile', '');
            $password = $this->post('password', '');
            $repassword = $this->post('repassword', '');
            $password_kay_redis = $this->getRedis('password_key_' . $mobile);
            $password_kay = md5(md5($mobile . '12w!%x22l%'));
            if ($password_kay_redis != $password_kay) {
                exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
            }
            if ($password != $repassword) {
                exit(json_encode($this->reback('10236'), JSON_UNESCAPED_UNICODE));
            }
            $number = preg_match('/[0-9]/', $password);
            $english = preg_match('/[a-zA-Z]/', $password);
            if ($number == 0 || $english == 0) {
                exit(json_encode($this->reback('10237'), JSON_UNESCAPED_UNICODE));
            }
            if (!preg_match("/^[a-z\d]*$/i", $repassword)) {
                exit(json_encode($this->reback('10237'), JSON_UNESCAPED_UNICODE));
            }
            $is_mobile = (new Common())->isMobile($mobile);
            if (empty($is_mobile)) {
                exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
            }
            $o_user = (new User())->getUserinfoByMobile($mobile);
            if (empty($o_user)) {
                exit(json_encode($this->reback('10001'), JSON_UNESCAPED_UNICODE));
            }
            $o_user_password = (new User_password())->getUserPassword($o_user->user_id);
            if (!empty($o_user_password)) {
                $data = [
                    'login_password' => (new ApiCrypt())->encrypt($password, 'YnbwODeaphDCl2LlJ7qk0eZ2')
                ];
                $result = $o_user_password->update_password($data);
            } else {
                $data = [
                    'user_id' => $o_user->user_id,
                    'login_password' => (new ApiCrypt())->encrypt($password, 'YnbwODeaphDCl2LlJ7qk0eZ2')
                ];
                $result = (new User_password())->save_password($data);
            }
            if (empty($result)) {
                exit(json_encode($this->reback('10010'), JSON_UNESCAPED_UNICODE));
            }
            Yii::$app->newDev->login($o_user, 1);
            $this->delRedis('password_key_' . $mobile);
            $update_arr = [
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_type' => 'weixin',
            ];
            $o_user->update_user($update_arr);
            $this->setSessionOpenid($o_user->user_id);
            $session_url = $this->getVal('url');
            $url = '/borrow/loan';
            if (!empty($session_url)) {
                $this->delVal('url');
                $url = $session_url;
            }
            exit(json_encode($this->reback('0000', ['url' => $url]), JSON_UNESCAPED_UNICODE));
        } else {
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * ajax_图形验证码验证
     * @author 王新龙
     * @date 2018/8/23 16:14
     */
    public function actionCheckimgcode() {
        if ($this->isPost()) {
            $code = $this->post('img_code');
            $code_char = Yii::$app->session->get('code_char');
            if (strtolower($code) != $code_char) {
                exit(json_encode($this->reback('10235'), JSON_UNESCAPED_UNICODE));
            }
            exit(json_encode($this->reback('0000'), JSON_UNESCAPED_UNICODE));
        } else {
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 图像验证码
     * @author 王新龙
     * @date 2018/8/22 17:04
     */
    public function actionImgcode() {
        $mobile = $this->get('mobile', '');
        Logger::dayLog('weixin/reg/imgcode', $mobile);
        return $this->getImgCode(4, 60, 20, $mobile);
    }

    /**
     * 注册协议
     * @return string
     * @author 王新龙
     * @date 2018/8/23 20:44
     */
    public function actionAgreement() {
        $this->layout = false;
        $this->getView()->title = "注册协议";
        $mobile = $this->get('mobile', '');
        return $this->render('agreement', [
                    'mobile' => $mobile
        ]);
    }

    /**
     * 登出
     * @return \yii\web\Response
     */
    public function actionSignout() {
        Yii::$app->newDev->logout();
        return $this->redirect('/borrow/reg/login');
    }

    /**
     * 保存微信信息，保存openid至session
     * @author 王新龙
     * @date 2018/9/4 10:35
     */
    private function setSessionOpenid($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $code = $this->getVal('code');
        $o_user = (new User())->getById($user_id);
        if (empty($o_user)) {
            return false;
        }
        if (!empty($o_user->openid)) {
            $this->setVal('openid', $o_user->openid);
            return true;
        }
        if (!empty($code) && empty($o_user->openid)) {
            $this->saveWeiXin($code);
        }
        $o_user->refresh();
        if (!empty($o_user->openid)) {
            $this->setVal('openid', $o_user->openid);
            return true;
        }
        return false;
    }

    private function wxRedirect($url) {
        Logger::dayLog("wx", $this->get(), $this->post());
        $code = $this->get('code');
        $user = $this->getUser();

        if ($user) {
            if ($code) {
                $this->saveWeiXin($code);
            }
            return $this->redirect($url);
        }
        return $this->redirect("/borrow/reg/login");
    }

    private function saveWeiXin($code) {
        $user = $this->getUser();
        if (isset($user->openid) && !empty($user->openid)) {
            return true;
        }
        //获取用户token值和openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::$_appid . "&secret=" . self::$_appSecret . "&code=" . $code . "&grant_type=authorization_code";
        $data = Http::getCurl($url);
        $resultArr = json_decode($data, true);
        if (!isset($resultArr['openid']) || empty($resultArr['openid'])) {
            return false;
        }
        Logger::dayLog('weixin/reg/saveweixin', $user->mobile, $resultArr, $code);
        //检验openid是否在User_wx中存在,并且存在于user表中
        if ($this->isOpenidReg($resultArr['openid'])) {
            $user_isset = User::find()->where(['openid' => $resultArr['openid']])->one();
            if (!empty($user_isset)) {
                return true;
            }
            $userModel = User::findOne($user->user_id);
            $update_arr = [
                'openid' => $resultArr['openid'],
            ];
            $ret = $userModel->update_user($update_arr);
            if ($ret) {
                return true;
            }
        }

        //获取用户微信资料信息并保存
        $usinfo = $this->getWebAuthThree($resultArr);
        if ($this->openidRegSave($usinfo)) {
            $userModel = User::findOne($user->user_id);
            $update_arr = [
                'openid' => $usinfo['openid'],
            ];
            $ret = $userModel->update_user($update_arr);
            if ($ret) {
                return true;
            }
            return false;
        }
        return false;
    }

    private function getWebAuthThree($ret) {
        $access_token = Http::getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $ret["openid"] . '&lang=zh_CN';
        $data = Http::getCurl($url);
        Logger::dayLog('weixin/reg/webauththree', $data);
        $resultArr = json_decode($data, true);
        return $resultArr;
    }

    private function isOpenidReg($openid) {
        if (empty($openid)) {
            return false;
        }
        $user = User_wx::find()->where(['openid' => $openid])->one();
        if (isset($user->id)) {
            return true;
        } else {
            return false;
        }
    }

    private function openidRegSave($userinfo) {
        if (!isset($userinfo['openid'])) {
            return false;
        }
        $openid = $userinfo['openid'];
        $nickname = isset($userinfo['nickname']) ? $userinfo['nickname'] : '';
        $head = isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : 'http://mp.yaoyuefu.com/images/dev/s2.png';
        $condition = [
            "openid" => $openid,
            "nickname" => addslashes($nickname),
            "head" => $head,
        ];
        $result_sql = (new User_wx())->addUser($condition);
        if (!$result_sql) {
            return false;
        }
        return true;
    }

    private function loginSmscode($o_user, $mobile, $from_code, $comeFrom = 1) {
        if (!empty($o_user)) {
            if ($o_user->status == 3 && !empty($o_user->identity)) {
                $data = ['type' => 2];
                exit(json_encode($this->reback('0000', $data), JSON_UNESCAPED_UNICODE));
            }
            $data = ['type' => 1];
            exit(json_encode($this->reback('0000', $data), JSON_UNESCAPED_UNICODE));
        }
        //用户注册
        $invite_code = $this->getCode();
        $time = date('Y-m-d H:i:s');
        $user_array = array(
            'mobile' => $mobile,
            'user_type' => 2,
            'invite_code' => $invite_code,
            'from_code' => (string) $from_code,
            'come_from' => (int) $comeFrom,
            'create_time' => $time,
            'last_login_time' => $time,
            'last_login_type' => 'app'
        );
        $transaction = Yii::$app->db->beginTransaction();
        $m_user = new User();
        $user_ret = $m_user->addUser($user_array);
        if (empty($user_ret)) {
            $transaction->rollBack();
            exit(json_encode($this->reback('10015'), JSON_UNESCAPED_UNICODE));
        }
        $extend_condition = [
            'user_id' => $m_user->user_id,
            'uuid' => '',
        ];
        $ext_ret = (new User_extend())->save_extend($extend_condition);
        if (empty($ext_ret)) {
            $transaction->rollBack();
            exit(json_encode($this->reback('10015'), JSON_UNESCAPED_UNICODE));
        }
        $transaction->commit();
        $array['mobile'] = $mobile;
        $array['user_id'] = $m_user->user_id;
        $array['type'] = 1;
        exit(json_encode($this->reback('0000', $array), JSON_UNESCAPED_UNICODE));
    }

    private function resetSmscode($o_user) {
        if (empty($o_user)) {
            exit(json_encode($this->reback('10001'), JSON_UNESCAPED_UNICODE));
        }
        if ($o_user->status == 3 && !empty($o_user->identity)) {
            $data = ['type' => 2];
            exit(json_encode($this->reback('0000', $data), JSON_UNESCAPED_UNICODE));
        }
        $data = ['type' => 1];
        exit(json_encode($this->reback('0000', $data), JSON_UNESCAPED_UNICODE));
    }

}

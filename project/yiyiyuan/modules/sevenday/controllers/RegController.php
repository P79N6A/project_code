<?php

namespace app\modules\sevenday\controllers;

use app\models\day\Sms_guide;
use app\models\day\User_guide;
use app\models\news\Common as common2;
use app\models\news\Sms;
use app\models\news\User;
use Yii;
use yii\web\Controller;

class RegController extends SevendayController {

    public $layout = 'main';

    public function behaviors() {
        return [];
    }

    /**
     * 登录入口
     * @return Response
     */
    public function actionIndex() {
        $user = $this->getUser();
        $ip = \app\commonapi\Common::get_client_ip();
        \app\commonapi\Logger::dayLog('sevenday', 'index', $ip,$user);
        //用户已经登录
        if ($user) {
            return $this->redirect("/day/loan/index");
        }
        return $this->redirect("/day/reg/loginloan");
    }

    /**
     * 登录页面显示
     */
    public function actionLoginloan() {
        $this->layout = 'main';
        $this->getView()->title = "登录";
        $user = $this->getUser();
        $ip = \app\commonapi\Common::get_client_ip();
        \app\commonapi\Logger::dayLog('sevenday', 'loginloan', $ip,$user);
        if (!empty($user)) {
            return $this->redirect('/day/loan');
        }
        return $this->render('login', [
                    'csrf' => $this->getCsrf(),
        ]);
    }

    /**
     * 登录AJAX接口方法
     */
    public function actionLoginsave() {
        $mobile = $this->post('mobile');
        $code = $this->post('code');

        $ip = \app\commonapi\Common::get_client_ip();
        \app\commonapi\Logger::dayLog('sevenday', 'loginsave', $ip, $mobile);

        if(empty($mobile)){
            exit(json_encode(['ret' => '1', 'url' => '']));
        }
        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $mobile)) {
            exit(json_encode(['ret' => '1', 'url' => '']));
        }

        //判断手机是否注册
        $isReg = User_guide::find()->where(['mobile' => $mobile])->one();

        //用户已经存在走登录流程
        if (!empty($isReg->user_id)) {
            $result = $this->doLogin($isReg, $mobile, $code);
        } else {
            $oYyyUser = (new User())->getUserinfoByMobile($mobile);
            $come_from = !empty($oYyyUser) ? 1 : 2;
            $result = $this->doReg($mobile, $code, $come_from);
        }
        echo json_encode($result);
        exit;
    }

    /**
     * 发送验证码(登录时)
     */
    public function actionLoginsend() {
        $mobile = $this->post('mobile');
        $ip = \app\commonapi\Common::get_client_ip();
        \app\commonapi\Logger::dayLog('sevenday', 'loginsend', $ip, $mobile);
        //正则验证是不是手机号码
        $is_mobile = (new common2())->isMobile($mobile);
        if (!$is_mobile) {
            $resultArr = array('ret' => '5', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $pic_num = strval($this->post('pic_num'));
        $mark = strval($this->post('mark'));
        $sms_count = (new Sms_guide())->getSmsCount($mobile, 43);
        //超过6次限制
        if ($sms_count >= 6) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //已经发送过验证码，需要显示图形验证码
//        if ($sms_count > 0 && $mark == 0) {
//            $resultArr = array('ret' => '3', 'url' => '');
//            echo json_encode($resultArr);
//            exit;
//        }
        //提交数据中有图形验证码，需要比对
        if ($mark == 1) {
            if (empty($pic_num) || strtolower($pic_num) != $this->getVal('code_char')) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }
        $sendRet = (new Sms_guide())->sendSevendayReg($mobile, 1);
        $resultArr = array('ret' => '0', 'url' => '');
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 获取图形验证码
     */
    public function actionImgcode() {
        return $this->getImgCode(4, 60, 20);
    }

    /**
     * 注册方法
     * @param $mobile
     * @param $code
     * @param $from_code
     * @param $come_from
     * @return array
     */
    public function doReg($mobile, $code, $come_from) {
        $key = "sevenday_getcode_register_" . $mobile;
        $code_byredis = $this->getRedis($key);
        if ($code_byredis != $code) {//验证码错误
            $resultArr = array('ret' => '3', 'url' => '');
            return $resultArr;
        }
        //保存用户信息
        $condition_user = [
            'mobile' => $mobile,
            'come_from' => $come_from,
            'last_login_time' => date('Y-m-d H:i:s'),
        ];
        $userModel = new User_guide();
        $userRet = $userModel->addUser($condition_user);
        if (!$userRet) {//注册失败
            $resultArr = array('ret' => '1', 'url' => '');
            return $resultArr;
        }

        //登录
        $user_info = User_guide::find()->where(['mobile' => $mobile])->one();
        Yii::$app->seven->login($user_info, 1);

        //删除redis里存储的验证码key
        $this->delRedis($key);
        $resultArr = array('ret' => '2', 'url' => "/day/loan");

        return $resultArr;
    }

    /**
     * 登录方法
     * @param $userInfo
     * @param $mobile
     * @param $code
     * @return array
     */
    public function doLogin($userInfo, $mobile, $code) {
        //禁用
        if ($userInfo->status == 6) {
            $resultArr = array('ret' => '4', 'url' => '');
            return $resultArr;
        }
        //验证码是否正确
        $key = "sevenday_getcode_register_" . $mobile;
        $code_byredis = $this->getRedis($key);
        //验证码错误
        if ($code_byredis != $code) {
            $resultArr = array('ret' => '3', 'url' => '');
            return $resultArr;
        }
        //删除redis里存储的key
        $this->delRedis($key);
        //登录
        $user_info = User_guide::find()->where(['mobile' => $mobile])->one();
        Yii::$app->seven->login($user_info, 1);
        $userModel = User_guide::findOne($userInfo->user_id);
        $update_arr = [
            'last_login_time' => date('Y-m-d H:i:s'),
        ];
        $ret = $userModel->update_user($update_arr);

        $resultArr = array('ret' => '0', 'url' => "/day/loan");

        return $resultArr;
    }

}

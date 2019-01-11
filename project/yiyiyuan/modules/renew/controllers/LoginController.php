<?php

namespace app\modules\renew\controllers;

use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\ApiSms;
use app\models\news\Sms;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\Renew_amount;
use app\models\news\Common as common2;
use Yii;

class LoginController extends RenewbaseController {
 
    public $layout = 'main';

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $user = $this->getUser();
        //用户已经登录
        if ($user) {
            return $this->redirect('/renew/loan');
        }

        $this->layout = 'inv';
        $this->getView()->title = "登录";
        $jsinfo = $this->getWxParam();
        return $this->render('login', [
                    'jsinfo' => $jsinfo,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    /**
     * 登录AJAX接口方法
     */
    public function actionLoginsave() {
        $mobile = $this->post('mobile');
        $code = $this->post('code');
        $from_code = $this->post('from_code');
        $come_from = $this->post('come_from') == '' ? 2 : $this->post('come_from');

        //判断手机是否注册
        $isReg = User::find()->where(['mobile' => $mobile])->one();

        //用户已经存在走登录流程
        if (!empty($isReg)) {
            $result = $this->doLogin($isReg, $mobile, $code);
        } else {
            $result = array('ret' => '6', 'url' => "/new/reg");
        }
        echo json_encode($result);
        exit;
    }

    // /**
    //  * 同意协议页面
    //  * @return string
    //  */
    // public function actionAgreement() {
    //     $this->getView()->title = "注册协议";
    //     return $this->render('agreement');
    // }

    /**
     * 发送验证码(登录时)
     */
    public function actionLoginsend() {
        $mobile = $this->post('mobile');
        //正则验证是不是手机号码
        $is_mobile = (new common2())->isMobile($mobile);
        if (!$is_mobile) {
            $resultArr = array('ret' => '5', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        $pic_num = strval($this->post('pic_num'));
        $mark = strval($this->post('mark'));
        $sms_count = (new Sms())->getSmsCount($mobile, 2);
        //超过6次限制
        if ($sms_count >= 6) {
            $resultArr = array('ret' => '2', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //已经发送过验证码，需要显示图形验证码
        if ($sms_count > 0 && $mark == 0) {
            $resultArr = array('ret' => '3', 'url' => '');
            echo json_encode($resultArr);
            exit;
        }
        //提交数据中有图形验证码，需要比对
        if ($mark == 1) {
            if (empty($pic_num) || strtolower($pic_num) != $this->getVal('code_char')) {
                $resultArr = array('ret' => '4', 'url' => '');
                echo json_encode($resultArr);
                exit;
            }
        }

        $sendRet = (new ApiSms())->sendReg($mobile, 2);
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
        $key = "getcode_register_" . $mobile;
        $code_byredis = $this->getRedis($key);
        //验证码错误
        if ($code_byredis != $code) {
            $resultArr = array('ret' => '3', 'url' => '');
            return $resultArr;
        }

        //删除redis里存储的key
        $this->delRedis($key);


        //判断是否有展期
        if (!$this->hasRenew($userInfo)) {
            $resultArr = array('ret' => '7', 'url' => '/new/loan');
            return $resultArr;
        }

        //登录
        $user_info = User::find()->select(['openid', 'user_id', 'mobile'])->where(['mobile' => $mobile])->one();
        Yii::$app->renew->login($user_info, 1);
        //一亿元登陆
        Yii::$app->newDev->login($user_info, 1);

        $userModel = User::findOne($userInfo->user_id);
        $update_arr = [
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_type' => 'weixin',
        ];
        $ret = $userModel->update_user($update_arr);

        $resultArr = array('ret' => '0', 'url' => "/renew/loan");
        return $resultArr;
    }

    /*
     * 	判断是否有借款以及展期资格
     */

    private function hasRenew($userInfo) {
        //判断一亿元产品中是否有进行中的借款
        $haveinLoanId = (new User_loan())->getHaveinLoan($userInfo->user_id);
        if ($haveinLoanId == 0) {
            return false;
        }
        //是否可展期
        $renewModel = new Renew_amount();
        $renew_amount = $renewModel->getRenew($haveinLoanId);
        if (empty($renew_amount) || $renew_amount->type != 3) {
            return false;
        }
        return true;
    }

}

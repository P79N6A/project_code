<?php
namespace app\modules\api\controllers\controllers311;

use Yii;
use app\models\news\User;
use app\models\news\User_password;
use app\commonapi\Crypt3Des;
use app\modules\api\common\ApiController;

class SetpasswordController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');
        $login_password = Yii::$app->request->post('login_password');
        $pay_password = Yii::$app->request->post('pay_password');
        $type = Yii::$app->request->post('type');
        $oldpassword = Yii::$app->request->post('oldpassword');

        if (empty($version) || empty($mobile) || empty($type)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $key = Yii::$app->params['app_key'];
        if ($type == 1) {
            $delogin_password = Crypt3Des::decrypt($login_password, $key);
        }

        if ($type == 2) {
            $depay_password = Crypt3Des::decrypt($pay_password, $key);
        }
        if ($type == 3) {
            $delogin_password = Crypt3Des::decrypt($login_password, $key);
            $oldlogin_password = Crypt3Des::decrypt($oldpassword, $key);
        }
        if ($type == 4) {
            $depay_password = Crypt3Des::decrypt($pay_password, $key);
            $oldpay_password = Crypt3Des::decrypt($oldpassword, $key);
        }
        //验证密码的规则

        $user = new User();
        $userinfo = $user->getUserinfoByMobile($mobile);
        if (!empty($userinfo)) {
            //判断用户是否设置密码
            $password = new User_password();
            $userinfo_password = $password->getUserPassword($userinfo->user_id);
            if (empty($userinfo_password)) {
                if ($type == 3 || $type == 4) {
                    $array = $this->returnBack('10007');
                    echo $array;
                    exit;
                }
                $condition = [
                    'user_id' => $userinfo->user_id,
                    'login_password' => $login_password,
                ];
                $result = $password->save_password($condition);
                if ($result) {
                    $array = $this->returnBack('0000');
                    echo $array;
                    exit;
                } else {
                    $array = $this->returnBack('10010');
                    echo $array;
                    exit;
                }
            } else {
                if ($type == 1) {
                    $up_login_pass_condition = [
                        'login_password' => $login_password,
                    ];
                    $result = $userinfo_password->update_password($up_login_pass_condition);
                } elseif ($type == 2) {
                    $up_pay_pass_condition = [
                        'pay_password' => $pay_password,
                    ];
                    $result = $userinfo_password->update_password($up_pay_pass_condition);
                } else {
                    if (empty($oldpassword)) {
                        $array = $this->returnBack('99996');
                        echo $array;
                        exit;
                    }
                    if ($type == 3) {
                        if ($userinfo_password->login_password == $oldpassword) {
                            $up_login_pass_condition = [
                                'login_password' => $login_password,
                            ];
                            $result = $userinfo_password->update_password($up_login_pass_condition);
                        } else {
                            $array = $this->returnBack('10028');
                            echo $array;
                            exit;
                        }
                    } else {
                        if ($userinfo_password->pay_password == $oldpassword) {
                            $up_pay_pass_condition = [
                                'pay_password' => $pay_password,
                            ];
                            $result = $userinfo_password->update_password($up_pay_pass_condition);
                        } else {
                            $array = $this->returnBack('10029');
                            echo $array;
                            exit;
                        }
                    }
                }
                if ($result) {
                    $array = $this->returnBack('0000');
                    echo $array;
                    exit;
                } else {
                    $array = $this->returnBack('10010');
                    echo $array;
                    exit;
                }
            }
        } else {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
    }
}

<?php

namespace app\modules\api\controllers\controllers312;

use app\models\news\Friends;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_password;
use app\modules\api\common\ApiController;
use Yii;

class RegisterController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');
        $login_password = Yii::$app->request->post('login_password');
        //来源码
        $from_code = Yii::$app->request->post('invite_code');
        $come_from = Yii::$app->request->post('come_from');
        $down_from = Yii::$app->request->post('down_from');
        $uuid = Yii::$app->request->post('uuid');

        if (empty($version) || empty($mobile) || empty($login_password)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $userinfo = (new User())->getUserinfoByMobile($mobile);
        if (!empty($userinfo)) {
            $array = $this->returnBack('10002');
            echo $array;
            exit;
        }

        //获取自己的邀请码
        $invite_code = $this->getCode();
        $now_time = date('Y-m-d H:i:s');
        $userModel = new User();
        $password = new User_password();

        //用户注册
        $user_array = array(
            'mobile' => $mobile,
            'user_type' => 2,
            'invite_code' => $invite_code,
            'from_code' => $from_code,
            'come_from' => 3,
            'create_time' => $now_time,
            'last_login_time' => $now_time,
            'last_login_type' => 'app'
        );
        if (isset($come_from)) {
            $user_array['come_from'] = !empty($come_from) ? $come_from : 3;
        }
        if (isset($down_from)) {
            $user_array['down_from'] = $down_from;
        }
        $transaction = Yii::$app->db->beginTransaction();
        $user_ret = $userModel->addUser($user_array);
        if(!$user_ret){
            $transaction->rollBack();
            $array = $this->returnBack('10015');
            echo $array;
            exit;
        }
        //好友
        if (!empty($from_code) && strlen($from_code) >= 6) {
            $fuserModel = new User();
            $fuser = $fuserModel->getUserinfoByInvitecode($from_code);
            $friendModel = new Friends();
            $friendModel->refreshFriend($userModel->user_id, $fuser->user_id);
        }
        //用户拓展表
        $extend_condition = [
            'user_id'=>$userModel->user_id,
            'uuid'=>$uuid,
        ];
        $ext_ret = (new User_extend())->save_extend($extend_condition);

        //设置登录密码
        $pass_condition = [
            'user_id' => $userModel->user_id,
            'login_password' => $login_password,
        ];
        $ret_password = $password->save_password($pass_condition);
        if(!$ret_password){
            $transaction->rollBack();
            $array = $this->returnBack('10015');
            echo $array;
            exit;
        }
        $transaction->commit();
        $array['mobile'] = $mobile;
        $array['user_id'] = $userModel->user_id;
        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;

    }

    private function getCode() {
        $code = $this->makeCode(8, 1);
        $user = new User();
        $isone = $user->getUserinfoByInvitecode($code);
        if (isset($isone->user_id)) {
            return $this->getCode();
        } else {
            return $code;
        }
    }

    //生成6位数的邀请码
    private function makeCode($length = 32, $mode = 0) {
        switch ($mode) {
            case '1':
                $str = '1234567890';
                break;
            case '2':
                $str = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case '3':
                $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                $str = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $result = '';
        $l = strlen($str) - 1;
        $num = 0;

        for ($i = 0; $i < $length; $i ++) {
            $num = rand(0, $l);
            $a = $str[$num];
            $result = $result . $a;
        }
        return $result;
    }

}

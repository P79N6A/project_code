<?php
namespace app\modules\api\controllers\controllers312;

use Yii;
use app\models\news\User;
use app\models\news\User_password;
use app\modules\api\common\ApiController;
use app\commonapi\Common;

class ChecksmscodeController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');
        $type = Yii::$app->request->post('type');
        $code = Yii::$app->request->post('code');
        $invite_code = Yii::$app->request->post('invite_code');

        if (empty($version) || empty($mobile) || empty($type) || empty($code)) {
            exit($this->returnBack('99994'));
        }

        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $mobile)) {
            exit($this->returnBack('10008'));
        }
        if ($type == 4) {
            $key = "getcode_bank_" . $mobile;
        } else {
            $key = "getcode_register_" . $mobile;
        }

        $usersomeinfo = $this->getSomeinfo($mobile, $invite_code);

        $code_byredis = Yii::$app->redis->get($key);
        if (!empty($code_byredis)) {
            if ($code == $code_byredis) {
                //判断邀请码是否否正确
                if ($usersomeinfo['is_invite'] == 'YES') {
                    //删除redis里存储的key
                    Yii::$app->redis->del($key);
                    $array = $this->reback($usersomeinfo['is_register'], $usersomeinfo['is_password'], $usersomeinfo['is_identity'], $usersomeinfo['is_invite']);
                    exit($this->returnBack('0000', $array));
                } elseif ($usersomeinfo['is_invite'] == 'BLACK') {
                    $array = $this->reback($usersomeinfo['is_register'], $usersomeinfo['is_password'], $usersomeinfo['is_identity'], $usersomeinfo['is_invite']);
                    exit($this->returnBack('10014', $array));
                } else {
                    $array = $this->reback($usersomeinfo['is_register'], $usersomeinfo['is_password'], $usersomeinfo['is_identity'], $usersomeinfo['is_invite']);
                    exit($this->returnBack('10011', $array));
                }
            } else {
                $array = $this->reback($usersomeinfo['is_register'], $usersomeinfo['is_password'], $usersomeinfo['is_identity'], $usersomeinfo['is_invite']);
                exit($this->returnBack('10005', $array));
            }
        } else {
            $array = $this->reback($usersomeinfo['is_register'], $usersomeinfo['is_password'], $usersomeinfo['is_identity'], $usersomeinfo['is_invite']);
            exit($this->returnBack('10004', $array));
        }
    }

    private function getSomeinfo($mobile, $invite_code = '')
    {
        //然后判断该用户是否注册，是否有登录密码，是否有身份证号，如果type=1且已填邀请码，判断邀请码是否正确
        $user = new User();
        $userinfo = $user->getUserinfoByMobile($mobile);
        if (empty($userinfo)) {
            $is_register = 'NO';
            $is_password = 'NO';
            $is_identity = 'NO';
        } else {
            $is_register = 'YES';
            $password = new User_password();
            $userpassword = $password->getUserPassword($userinfo->user_id);
            if (empty($userpassword)) {
                $is_password = 'NO';
            } else {
                $is_password = 'YES';
            }
            $is_identity = !empty($userinfo->identity) ? 'YES' : 'NO';
        }

        if (!empty($invite_code)) {
            $userbyinvitecode = $user->getUserinfoByInvitecode($invite_code);
            if (isset($userbyinvitecode->invite_code) && !empty($userbyinvitecode->invite_code)) {
                if ($userbyinvitecode->status == 5) {
                    $is_invite = 'BLACK';
                } else {
                    $is_invite = 'YES';
                }
            } else {
                $invite_qrcode = Common::invtecodefrombyqrcode($invite_code);
                if ($invite_qrcode) {
                    $is_invite = 'YES';
                } else {
                    $is_invite = 'NO';
                }
            }
        } else {
            $is_invite = 'YES';
        }

        $array['is_register'] = $is_register;
        $array['is_password'] = $is_password;
        $array['is_identity'] = $is_identity;
        $array['is_invite'] = $is_invite;
        return $array;
    }

    private function reback($is_register, $is_password, $is_identity, $is_invite)
    {
        $array['is_register'] = $is_register;
        $array['is_password'] = $is_password;
        $array['is_identity'] = $is_identity;
        $array['is_invite'] = $is_invite;
        return $array;
    }
}

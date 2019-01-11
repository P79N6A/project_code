<?php

namespace app\modules\api\controllers\controllers310;

use app\commonapi\ApiSms;
use app\models\news\Sms;
use app\models\news\User;
use app\models\news\User_password;
use app\modules\api\common\ApiController;
use Yii;

class GetcodeController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {

        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');
        $type = Yii::$app->request->post('type');
        $imgcode = Yii::$app->request->post('imgcode');

        if (empty($version) || empty($mobile) || empty($type)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $mobile)) {
            $array = $this->returnBack('10008');
            echo $array;
            exit;
        }

        $o_user = (new User())->getUserinfoByMobile($mobile);

        //type 1:登录验证码 2：重置密码 4：绑卡
        switch ($type) {
            case 1:
                $this->login($o_user, $mobile, $imgcode);
                break;
            case 2:
                $this->resetPassword($o_user, $mobile, $imgcode);
                break;
            case 3:
                $this->login($o_user, $mobile, $imgcode);
                break;
            case 4:
                $this->bankCard($o_user, $mobile);
                break;
            default:
                exit($this->returnBack('99996'));
                break;
        }
    }

    private function login($o_user, $mobile, $imgcode) {
        //已设置密码，提示去密码登录
        if (!empty($o_user) && !empty($o_user->password) && !empty($o_user->password->login_password)) {
            exit($this->returnBack('10234'));
        }
        $this->checkSmsNum($mobile, $type = 2, $imgcode);
        $result = $this->sendsms($type = 2, $mobile);
        if (empty($result)) {
            exit($this->returnBack('10105'));
        }
        exit($this->returnBack('0000'));
    }

    private function resetPassword($o_user, $mobile, $imgcode) {
        $this->checkSmsNum($mobile, $type = 19, $imgcode);
        $result = $this->sendsms($type = 19, $mobile);
        if (empty($result)) {
            exit($this->returnBack('10105'));
        }
        exit($this->returnBack('0000'));
    }

    private function bankCard($o_user, $mobile) {
        $is_register = 'NO';
        $is_password = 'NO';
        $is_identity = 'NO';
        $result = $this->sendsms(7, $mobile);

        $array = $this->reback($is_register, $is_password, $is_identity);
        $array = $this->returnBack('0000', $array);
        exit($array);
    }

    private function checkSmsNum($mobile, $type, $imgcode) {
        $sms_count = (new Sms())->getSmsCount($mobile, $type);
        if ($sms_count >= 5) {
            exit($this->returnBack('10003'));
        }
        //输入图形验证码
        if ($sms_count > 0 && empty($imgcode)) {
            $data = [
                'is_imgcode' => 1,
                'imgcode_url' => '/borrow/reg/imgcode?mobile=' . $mobile
            ];
            exit($this->returnBack('0000', $data));
        }
        //图形验证码监测
        $code_char = Yii::$app->redis->get('code_char_'.$mobile);
        if ($sms_count > 0 && !empty($imgcode) && strtolower($imgcode) != $code_char) {
            $data = [
                'is_imgcode' => 1,
                'imgcode_url' => '/borrow/reg/imgcode?mobile=' . $mobile
            ];
            exit($this->returnBack('0000', $data));
        }
    }

    private function reback($is_register, $is_password, $is_identity) {
        $array['is_register'] = $is_register;
        $array['is_password'] = $is_password;
        $array['is_identity'] = $is_identity;
        return $array;
    }

    private function sendsms($type, $mobile) {
        if (empty($type) || empty($mobile)) {
            return false;
        }
        $sms = new Sms();
        $sms_count = $sms->getSmsCount($mobile, $type);
        if ($sms_count >= 6) {
            $array = $this->reback('NO', 'NO', 'NO');
            exit($this->returnBack('10003', $array));
        }
        $api = new ApiSms();
        if ($type == 1) {
            $sendRet = $api->sendReg($mobile, 1);
        } elseif ($type == 7) {
            $sendRet = $api->sendBindCard($mobile, 7);
        } elseif ($type == 19) {
            $sendRet = $api->sendReg($mobile, 19);
        } else {
            $sendRet = $api->sendReg($mobile, $type);
        }
        if (empty($sendRet) || empty($sendRet['rsp_code'] || $sendRet['rsp_code'] != '0000')) {
            return false;
        }
        return true;
    }
}

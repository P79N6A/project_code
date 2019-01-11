<?php
namespace app\modules\api\controllers\controllers312;

use app\models\news\User_extend;
use Yii;
use app\models\news\User;
use app\modules\api\common\ApiController;

class LogincodeController extends ApiController {
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');
        $code = Yii::$app->request->post('code');
        $type = Yii::$app->request->post('type');//1：登录流程 2：重置密码流程
        $come_from = Yii::$app->request->post('come_from', '');
        $down_from = Yii::$app->request->post('down_from', '');
        $uuid = Yii::$app->request->post('_uuid', '');

        if (empty($version) || empty($mobile) || empty($code)) {
            exit($this->returnBack('99994'));
        }

        //短信验证码验证
        $key = "getcode_register_" . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        if (empty($code_byredis)) {
            exit($this->returnBack('10004'));
        }
        if ($code != $code_byredis) {
            exit($this->returnBack('10005'));
        }
        Yii::$app->redis->del($key);
        $o_user = (new User())->getUserinfoByMobile($mobile);
        switch ($type) {
            case 1:
                $this->login($o_user, $mobile, $come_from, $down_from, $uuid);
                break;
            case 2:
                $this->reset($o_user);
                break;
            default:
                exit($this->returnBack('99996'));
                break;
        }
    }

    /**
     * 登录流程
     * @param $o_user
     * @param $mobile
     * @param $come_from
     * @param $down_from
     * @param $uuid
     * @author 王新龙
     * @date 2018/8/22 14:51
     */
    private function login($o_user, $mobile, $come_from, $down_from, $uuid) {
        if (!empty($o_user)) {
            if ($o_user->status == 3 && !empty($o_user->identity)) {
                exit($this->returnBack('0000', ['type' => 2]));
            }
            exit($this->returnBack('0000', ['type' => 1]));
        }
        //用户注册
        $invite_code = $this->getCode();
        $time = date('Y-m-d H:i:s');
        $user_array = array(
            'mobile' => $mobile,
            'user_type' => 2,
            'invite_code' => $invite_code,
            'from_code' => '',
            'down_from' => !empty($down_from) ? $down_from : '',
            'come_from' => !empty($come_from) ? $come_from : 3,
            'create_time' => $time,
            'last_login_time' => $time,
            'last_login_type' => 'app'
        );
        $transaction = Yii::$app->db->beginTransaction();
        $m_user = new User();
        $user_ret = $m_user->addUser($user_array);
        if (empty($user_ret)) {
            $transaction->rollBack();
            exit($this->returnBack('10015'));
        }
        $extend_condition = [
            'user_id' => $m_user->user_id,
            'uuid' => $uuid,
        ];
        $ext_ret = (new User_extend())->save_extend($extend_condition);
        if (empty($ext_ret)) {
            $transaction->rollBack();
            exit($this->returnBack('10015'));
        }
        $transaction->commit();
        $array['mobile'] = $mobile;
        $array['user_id'] = $m_user->user_id;
        $array['type'] = 1;
        exit($this->returnBack('0000', $array));
    }

    /**
     * 重置密码流程
     * @param $o_user
     * @author 王新龙
     * @date 2018/8/22 14:50
     */
    private function reset($o_user) {
        if (empty($o_user)) {
            exit($this->returnBack('10001'));
        }
        if ($o_user->status == 3 && !empty($o_user->identity)) {
            exit($this->returnBack('0000', ['type' => 2]));
        }
        exit($this->returnBack('0000', ['type' => 1]));
    }

    /**
     * 生成邀请码
     * @return string
     * @author 王新龙
     * @date 2018/8/22 14:50
     */
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

    /**
     * 生成6位数的邀请码
     * @param int $length
     * @param int $mode
     * @return string
     * @author 王新龙
     * @date 2018/8/22 14:50
     */
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

        for ($i = 0; $i < $length; $i++) {
            $num = rand(0, $l);
            $a = $str[$num];
            $result = $result . $a;
        }
        return $result;
    }
}
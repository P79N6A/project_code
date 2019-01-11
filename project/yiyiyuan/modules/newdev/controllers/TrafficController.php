<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Captcha;
use app\models\news\Common;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\Sms;
use app\models\news\ApiSms;
use app\commonapi\Keywords;
use Yii;

class TrafficController extends NewdevController {

    public $layout = 'traffic';
    private $codeKey = 'traffic_imgcode';
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    public function actionRegtraffic() {
        $from = $this->get('from', "");
        $type = $this->get('type', "955");
        $regtype = $this->get('regtype', "");
        $downtype = $this->get('downtype', "");
        $backGround = $this->getBackground($from);
        $sql = "select * from yi_app_version ORDER BY id desc";
        $model = Yii::$app->db->createCommand($sql)->queryOne();

        if ($backGround['view'] == 'regtraffic') {
            $this->layout = false;
        }
        $server_name = $_SERVER['HTTP_HOST'];
        if ($server_name == "mp.yaoyuefu.com") {
            $com_name = "小小黛朵(北京)科技有限公司";
        } else {
            $com_name = "先花信息技术（北京）有限公司";
        }
        return $this->render($backGround['view'], [
                    'type' => $type,
                    'regtype' => $regtype,
                    'downtype' => $downtype,
                    'downurl' => $model['download_url'],
                    'come_from' => $backGround['come_from'],
                    'com_name' => $com_name,
                    'img_range' => time() . $this->genRandomString(10)
        ]);
    }

    /**
     * 获取图片验证码
     */
    public function actionGetimgcode() {
        $imgRange = $this->get('img_range','');
        if(empty($imgRange)){
            exit('error');
        }
        $common = new Captcha();
        return $common->getImgCode($this->codeKey . '_' . $imgRange, 4);
    }

    /**
     * 验证图片验证码
     * @param string $imgCode
     * @return false
     */
    private function chkImgcode($imgCode, $imgRange) {
        $common = new Captcha();
        $ret = $common->chkImgCode($this->codeKey . '_' . $imgRange, $imgCode);
        return $ret;
    }

    /*
     * 发送短信验证码
     */

    public function actionSend() {
        $mobile = $this->post('mobile');
        $imgRange = $this->post('img_range');
        $imgCode = strval($this->post('img_code'));
        if (empty($mobile)) {
            echo $this->showMessage(1, '请输入手机号', 'json');
            exit;
        }

        //号码格式是否正确
        $mobile_check = (new Common())->isMobile($mobile);
        if (!$mobile_check) {
            echo $this->showMessage(1, '请输入正确手机号码', 'json');
            exit;
        }

        //图形验证码
        if (empty($imgCode)) {
            echo $this->showMessage(1, '请输入图形验证码', 'json');
            exit;
        }
        $ip = \app\commonapi\Common::get_client_ip();
        $img_code_check = $this->chkImgcode($imgCode, $imgRange);
        \app\commonapi\Logger::dayLog('smsTraffic', $mobile, $img_code_check, $imgCode, $ip);
        if (!$img_code_check) {
            echo $this->showMessage(1, '请输入正确的图形验证码', 'json');
            exit;
        }

        //验证手机号是否注册
        $userObj = (new User())->getUserinfoByMobile($mobile);
        if (!empty($userObj)) {
            echo $this->showMessage(2, '您已经注册了', 'json');
            exit;
        }

        //一天只能发送6条短信
        $sms_count = (new Sms())->getSmsCount($mobile, 1);
        if ($sms_count >= 6) {
            echo $this->showMessage(1, '您今天获取验证码的次数过多，请明天再试', 'json');
            exit;
        }
        $api = new ApiSms();
        $sendRet = $api->sendReg($mobile, 1);
        if ($sendRet) {
            echo $this->showMessage(0, '发送成功', 'json');
            exit;
        }
    }

    /*
     * 验证提交的post数据是否合法
     */

    private function chkPostData($post_data) {
        //验证手机号是否填写
        if (empty($post_data['mobile'])) {
            echo $this->showMessage(1, '*请输入手机号', 'json');
            exit;
        }
        //验证手机号是否合法
        $common = new common();
        if (!$common->isMobile($post_data['mobile'])) {
            echo $this->showMessage(1, '*请输入正确手机号码', 'json');
            exit;
        }
        //验证图形验证码是否填写
        if (empty($post_data['img_code'])) {
            echo $this->showMessage(1, '*请输入图形验证码', 'json');
            exit;
        }
        //验证图形验证码是否正确
        $post_data['img_range'] = isset($post_data['img_range']) ? $post_data['img_range'] : '';
        if (!$this->chkImgcode($post_data['img_code'], $post_data['img_range'])) {
            echo $this->showMessage(1, '*请填写正确的图形验证码', 'json');
            exit;
        }
        //验证手机号是否注册
        $userObj = (new User())->getUserinfoByMobile($post_data['mobile']);
        if (!empty($userObj)) {
            echo $this->showMessage(3, '*您已经注册了', 'json');
            exit;
        }
        //验证码是否填写
        if (empty($post_data['code'])) {
            echo $this->showMessage(1, '*请输入短信验证码', 'json');
            exit;
        }
        //判断短信验证码是否正确
        $check_code = (new Sms())->chkCode($post_data['mobile'], $post_data['code']);
        if (!$check_code) {
            echo $this->showMessage(1, '*请输入正确的短信验证码', 'json');
            exit;
        }
    }

    /*
     * 注册
     */

    public function actionRegsave() {
        $post_data = $this->post();
        //检验数据
        $this->chkPostData($post_data);
        //录入信息
        $transaction = Yii::$app->db->beginTransaction();
        //用户主表User信息录入
        $userModel = new User();
        $post_data['invite_code'] = $this->getCode();
        $user_res = $userModel->addUser($post_data);
        if (!$user_res) {
            $transaction->rollBack();
            echo $this->showMessage(1, '*注册失败', 'json');
            exit;
        }
        //用户附属表user_extend信息录入
        $user_id = $userModel->user_id;
        $userExtendModel = new User_extend();
        $extend = [
            'user_id' => $user_id,
            'reg_ip' => Common::get_client_ip(),
        ];
        $user_extend_res = $userExtendModel->save_extend($extend);
        if (!$user_extend_res) {
            $transaction->rollBack();
            echo $this->showMessage(1, '*注册失败', 'json');
            exit;
        }

        $transaction->commit();
        echo $this->showMessage(0, '注册成功', 'json');
    }

    private function getBackground($from) {
        if (empty($from)) {
            return ['view' => $this->getTrafficView(''), 'come_from' => 2];
        }
        preg_match('/\d+/', $from, $array);
        $come_from = 2;
        if (!empty($array)) {
            $come_from = $array[0];
        }
        $arr = explode('_', $from);
        if (count($arr) >= 2) {
            $come_from = $arr['1'];
            $from = $this->Deepinarray($arr['1'], Keywords::getTrafficimg());
            if (empty($from)) {
                return ['view' => $this->getTrafficView($arr['0']), 'come_from' => $come_from];
            }
            return ['view' => $this->getTrafficView($from), 'come_from' => $come_from];
        }
        $from = $this->Deepinarray($come_from, Keywords::getTrafficimg());
        return ['view' => $this->getTrafficView($from), 'come_from' => $come_from];
    }

    /*
     * 查询背景图是否有变更
     */

    private function Deepinarray($value, $array) {
        foreach ($array as $k => $item) {
            if (is_array($item) && in_array($value, $item)) {
                return $k;
            } else {
                continue;
            }
        }
        return "";
    }

    private function getTrafficView($key) {
        $viewArray = Keywords::getTrafficView();
        if (isset($viewArray[$key]) && !empty($viewArray[$key])) {
            return $viewArray[$key];
        }
        return reset($viewArray);
    }

    private function genRandomString($len) {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6",
            "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars); // 将数组打乱
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars [mt_rand(0, $charsLen)];
        }
        return $output;
    }

}

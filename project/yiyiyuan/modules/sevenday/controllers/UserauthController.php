<?php

namespace app\modules\sevenday\controllers;

use app\commonapi\Http;
use app\models\day\Juxinli_guide;
use app\models\day\Sms_guide;
use app\models\day\User_bank_guide;
use app\models\day\User_credit_guide;
use app\models\day\User_guide;
use Yii;

class UserauthController extends SevendayController {

    /**
     * 添加用户姓名及身份证号页
     * @return string
     * @author 王新龙
     * @date 2018/8/2 19:46
     */
    public function actionIndex() {
        $this->getView()->title = '实名信息';
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        return $this->render('index', [
                    'user_id' => $o_user_guide->user_id,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 添加用户姓名及身份证号
     * @author 王新龙
     * @date 2018/8/2 19:46
     */
    public function actionSaveidentity() {
        if (!$this->isPost()) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $realname = $this->post('realname', '');
        $identity = $this->post('identity', '');
        if (empty($realname) || empty($identity)) {
            exit(json_encode(['rsp_code' => '0002', 'rsp_msg' => '姓名或身份证号不能为空']));
        }
        $check_iden = Http::checkIdenCard($identity);
        if (empty($check_iden)) {
            exit(json_encode(['rsp_code' => '0003', 'rsp_msg' => '身份证有误，请重新输入']));
        }
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0004', 'rsp_msg' => '非法访问']));
        }
        $identity_result = (new User_guide())->getByIdentity($identity);
        if (!empty($identity_result) && $o_user_guide->user_id != $identity_result->user_id) {
            exit(json_encode(['rsp_code' => '0005', 'rsp_msg' => '该身份证号码已被他人绑定，请联系客服']));
        }
        $o_user_guide = (new User_guide())->getById($o_user_guide->user_id);
        $data = [
            'realname' => $realname,
            'identity' => $identity,
            'identity_valid' => 2
        ];
        $user_guide_result = $o_user_guide->updateRecord($data);
        if (empty($user_guide_result)) {
            exit(json_encode(['rsp_code' => '0006', 'rsp_msg' => '操作失败']));
        }
        $o_user_credit_guide = (new User_credit_guide())->getByIdentity($identity);
        if (empty($o_user_credit_guide)) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '用户暂无额度', 'url' => '/day/loan/nocredit']));
        }
        $o_user_bank_guide = (new User_bank_guide())->getByUserId($o_user_guide->user_id, $type = 0);
        if (empty($o_user_bank_guide)) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '绑卡', 'url' => '/day/userbank/index']));
        }
        $o_user_bank_guide = (new User_bank_guide())->getByUserId($o_user_guide->user_id, $type = 0);
        if (empty($o_user_bank_guide)) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '绑卡', 'url' => '/day/userbank/index']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '借款', 'url' => '/day/loan/credit']));
    }

    public function actionMobile() {
        $this->getView()->title = '手机认证';
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_guide = (new User_guide())->getById($o_user_guide->user_id);
        if (empty($o_user_guide->realname) || empty($o_user_guide->identity)) {
            return $this->redirect('/day/userauth');
        }
        $mobile = (new Juxinli_guide())->getJuxinli($o_user_guide->user_id);
        if (!empty($mobile)) {
            return $this->redirect('/day/loan');
        }
        return $this->render('mobile', [
                    'user' => $o_user_guide,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 获取运营商验证码
     * @author 代威群
     * @date 2018/8/3 13:24
     */
    public function actionGetcode() {
        if (!$this->isPost()) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $user_id = $this->post('user_id');
        $bank_mobile = $this->post('mobile');
        if (empty($user_id) || empty($bank_mobile)) {
            exit(json_encode(['rsp_code' => '0002', 'rsp_msg' => '参数不能为空']));
        }
        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $bank_mobile)) {
            exit(json_encode(['rsp_code' => '0003', 'rsp_msg' => '手机号码格式不正确']));
        }
        $o_user_guide = (new User_guide())->getById($user_id);
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0004', 'rsp_msg' => '非法访问']));
        }
        $sms = new Sms_guide();
        $type = 5;
        $sms_count = $sms->getSmsCount($bank_mobile, $type);
        if ($sms_count >= 6) {
            exit(json_encode(['rsp_code' => '0005', 'rsp_msg' => '短信次数超限']));
        }
        $send_result = $sms->sendSevendayMobile($bank_mobile, $type);
        if (empty($send_result)) {
            exit(json_encode(['rsp_code' => '0006', 'rsp_msg' => '短信发送失败']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '获取短信成功']));
    }

    /**
     * 运营商验证码验证
     * @author 王新龙
     * @date 2018/8/3 10:09
     */
    public function actionAddjuxinli() {
        if (!$this->isPost()) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        if ($o_user_guide->identity_valid != 2) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '填写用户信息', 'url' => '/day/userauth/index']));
        }
        $mobile = $this->post('mobile', '');
        $code = $this->post('code', '');
        if (empty($mobile) || empty($code)) {
            exit(json_encode(['rsp_code' => '0002', 'rsp_msg' => '参数不能为空']));
        }
        //验证手机号码
        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $mobile)) {
            exit(json_encode(['rsp_code' => '0003', 'rsp_msg' => '手机号码格式不正确']));
        }
        //验证短信验证码
        $key = "sevenday_getcode_mobile_" . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        Yii::$app->redis->del($key);
        if (empty($code_byredis) || $code != $code_byredis) {
            exit(json_encode(['rsp_code' => '0004', 'rsp_msg' => '短信验证码不正确']));
        }
        $oJuxinliModel = new Juxinli_guide();
        $result = $oJuxinliModel->saveRecord($o_user_guide->user_id);
        if (empty($result)) {
            exit(json_encode(['rsp_code' => '0009', 'rsp_msg' => '验证失败']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '绑卡成功', 'url' => '/day/loan']));
    }

}

<?php

namespace app\modules\sevenday\controllers;

use app\commonapi\Apihttp;
use app\commonapi\ApiSms;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\day\Sms_guide;
use app\models\day\User_bank_guide;
use app\models\day\User_guide;
use app\models\news\Areas;
use app\models\news\Card_bin;
use Yii;

class UserbankController extends SevendayController {

    /**
     * 绑卡页
     * @return string
     * @author 王新龙
     * @date 2018/8/2 20:22
     */
    public function actionIndex() {
        $this->getView()->title = '绑卡页面';
        $o_user_guide = $this->getUser();
        if (empty($o_user_guide)) {
            return $this->redirect('/day/reg');
        }
        $o_user_guide = (new User_guide())->getById($o_user_guide->user_id);
        if (empty($o_user_guide->realname) || empty($o_user_guide->identity)) {
            return $this->redirect('/day/userauth');
        }
        return $this->render('index', [
                    'user' => $o_user_guide,
                    'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 获取验证码
     * @author 王新龙
     * @date 2018/8/3 13:24
     */
    public function actionGetcode() {
        if (!$this->isPost()) {
            exit(json_encode(['rsp_code' => '0001', 'rsp_msg' => '非法访问']));
        }
        $user_id = $this->post('user_id');
        $bank_mobile = $this->post('bank_mobile');
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
        $type = 2;
        $sms_count = $sms->getSmsCount($bank_mobile, $type);
        if ($sms_count >= 6) {
            exit(json_encode(['rsp_code' => '0005', 'rsp_msg' => '短信次数超限']));
        }
        $send_result = $sms->sendSevendayCard($bank_mobile, $type);
        if (empty($send_result)) {
            exit(json_encode(['rsp_code' => '0006', 'rsp_msg' => '短信发送失败']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '获取短信成功']));
    }

    /**
     * 绑卡
     * @author 王新龙
     * @date 2018/8/3 10:09
     */
    public function actionAddbank() {
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
        $card = $this->post('card', '');
        $bank_mobile = $this->post('bank_mobile', '');
        $code = $this->post('code', '');
        if (empty($card) || empty($bank_mobile) || empty($code)) {
            exit(json_encode(['rsp_code' => '0002', 'rsp_msg' => '参数不能为空']));
        }
        //验证手机号码
        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $bank_mobile)) {
            exit(json_encode(['rsp_code' => '0003', 'rsp_msg' => '手机号码格式不正确']));
        }
        //验证短信验证码
        $key = "sevenday_getcode_bank_" . $bank_mobile;
        $code_byredis = Yii::$app->redis->get($key);
        Yii::$app->redis->del($key);
        if (empty($code_byredis) || $code != $code_byredis) {
            exit(json_encode(['rsp_code' => '0004', 'rsp_msg' => '短信验证码不正确']));
        }
        //验证卡片是否被绑定
        $card_result = (new User_bank_guide())->getByCard($card);
        if (!empty($card_result) && $card_result->user_id == $o_user_guide->user_id) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '绑卡成功', 'url' => '/day/loan']));
        }
        if (!empty($card_result) && $card_result->user_id != $o_user_guide->user_id) {
            exit(json_encode(['rsp_code' => '0005', 'rsp_msg' => '该银行卡号已经他人绑定，请联系客服']));
        }
        $cardbin = (new Card_bin())->getCardBinByCard($card, "prefix_length desc");
        //只支持借记卡
        $card_type_arr = [0];
        if (empty($cardbin) || !in_array($cardbin['card_type'], $card_type_arr)) {
            exit(json_encode(['rsp_code' => '0006', 'rsp_msg' => '银行卡类型不支持']));
        }
        //卡片不支持
        $bank_array = Keywords::getBankAbbr();
        if (!in_array($cardbin['bank_abbr'], $bank_array[$cardbin['card_type']])) {
            exit(json_encode(['rsp_code' => '0007', 'rsp_msg' => '银行卡类型不支持']));
        }
        //四要素鉴权
        $result = $this->chkBank($o_user_guide, $bank_mobile, $card, $cardbin['card_type']);
        if ($result['res_code'] != '0000') {
            exit(json_encode(['rsp_code' => '0008', 'rsp_msg' => $result['res_msg']]));
        }
        //四要素鉴权通道
        $verify = 1;
        if (!empty($result) && isset($result['res_data']['channel_id'])) {
            $verify = !empty($result['res_data']['channel_id']) ? $result['res_data']['channel_id'] : 1;
        }
        $result = $this->saveBank($o_user_guide->user_id, $card, $bank_mobile, $verify);
        if (empty($result)) {
            exit(json_encode(['rsp_code' => '0009', 'rsp_msg' => '绑卡失败']));
        }
        $mobile = (new \app\models\day\Juxinli_guide())->getJuxinli($o_user_guide->user_id);
        if (empty($mobile)) {
            exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '绑卡成功', 'url' => '/day/userauth/mobile']));
        }
        exit(json_encode(['rsp_code' => '0000', 'rsp_msg' => '绑卡成功', 'url' => '/day/loan']));
    }

    /**
     * 四要素鉴权
     * @param $userObj
     * @param $bankMobile
     * @param $card
     * @param $bankType
     * @return array|bool
     * @author 王新龙
     * @date 2018/8/3 14:15
     */
    private function chkBank($userObj, $bankMobile, $card, $bankType) {
        $postData = array(
            'username' => $userObj->realname,
            'idcard' => $userObj->identity,
            'cardno' => $card,
            'phone' => $bankMobile,
            'identityid' => $userObj->user_id,
            'card_type' => $bankType, //1信用卡 非1储蓄卡
        );
        $openApi = new Apihttp;
        $result = $openApi->bankInfoValids($postData);
        Logger::dayLog('sevenday/creditbank', "四要素鉴权", $userObj->user_id, $postData, $result);
        if ($result['res_code'] != '0000') {
            $resMsg = '';
            switch ($result['res_msg']) {
                case 'DIFFERENT':
                    $resMsg = '请优先确认您输入的手机号码与办理银行卡时预留手机号码一致<br>请确认您的银行卡号是否填写正确';
                    break;
                case 'ACCOUNTNO_INVALID':
                    $resMsg = '请核实您的银行卡状态是否有效';
                    break;
                case 'ACCOUNTNO_NOT_SUPPORT':
                    $resMsg = '暂不支持此银行，请更换您的银行卡';
                    break;
                default:
                    $resMsg = $result['res_msg'];
            }
            return ['res_code' => $result['res_code'], 'res_msg' => $resMsg];
        }
        return $result;
    }

    /**
     * 保存数据
     * @param $userId
     * @param $card
     * @param $bankMobile
     * @param $verify
     * @return string
     * @author 王新龙
     * @date 2018/8/3 14:16
     */
    private function saveBank($userId, $card, $bankMobile, $verify) {
        $cardbinModel = new Card_bin();
        $card_bin = $cardbinModel->getCardBinByCard($card);
        if (empty($card_bin)) {
            return false;
        }

        $areaId = (new Areas())->getAreaOrSubBank(1);
        $subBank = (new Areas())->getAreaOrSubBank(2);
        $condition = array(
            'user_id' => $userId,
            'type' => $card_bin['card_type'],
            'bank_abbr' => $card_bin['bank_abbr'],
            'bank_name' => $card_bin['bank_name'],
            'card' => $card,
            'bank_mobile' => $bankMobile,
            'default_bank' => 0,
            'status' => 1,
            'verify' => $verify,
            'province' => (string) $areaId['province'],
            'city' => (string) $areaId['city'],
            'area' => (string) $areaId['province'],
            'sub_bank' => $subBank,
            'is_new' => 1,
        );

        $bank = User_bank_guide::find()->where(['card' => $card, 'user_id' => $userId, 'default_bank' => 0])->one();
        if (empty($bank)) {
            $result = (new User_bank_guide())->addRecord($condition);
        } else {
            $result = (new User_bank_guide())->updateRecord($condition);
        }
        if (empty($result)) {
            return false;
        }
        return true;
    }

}

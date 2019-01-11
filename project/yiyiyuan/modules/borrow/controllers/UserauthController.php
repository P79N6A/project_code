<?php

namespace app\modules\borrow\controllers;

use app\commonapi\Bank;
use app\commonapi\Apihttp;
use app\commonapi\Http;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\models\dev\Pictype;
use app\models\news\Black_list;
use app\models\news\Common;
use app\models\news\Contacts_flows;
use app\models\news\Favorite_contacts;
use app\models\news\Information_logs;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_bank;
use app\models\news\User_password;
use app\models\news\Video_auth;
use app\models\news\Sms;
use app\commonapi\Logger;
use app\models\news\Card_bin;
use app\models\news\ApiSms;
use app\models\news\Areas;
use app\models\news\ScanTimes;
use Yii;
use yii\helpers\ArrayHelper;

class UserauthController extends BorrowController {

    public $layout = 'main';

    public function actionIndex() {
        $img_url_domain = (new ImageHandler())->img_domain_url;
        $this->get();
        $this->getView()->title = "实名认证";
        $user = $this->getUser();
        $redirect_info = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/new/account/peral';
        $profession = Keywords::getProfession();
        $profession = ArrayHelper::getColumn($profession, 0);
        $jsinfo = $this->getWxParam();
        $passModel = new User_password();
        $pass = $passModel->getUserPassword($user->user_id);
        $path = !empty($pass) ? $img_url_domain . $pass->iden_url : '';
        if ($user->status == 3 || ($user->identity_valid == 2 && !empty($pass) && !empty($pass->iden_url) && @fopen($path, 'r'))) {
            return $this->redirect("/borrow/userauth/modify");
        }
        return $this->render('nameauth', [
                    'redirect_info' => $redirect_info,
                    'user' => $user,
                    'jsinfo' => $jsinfo,
                    'encrypt' => ImageHandler::encryptKey($user->user_id, 'h5'),
                    'csrf' => $this->getCsrf(),
                    'profession' => json_encode(array_values($profession)),
        ]);
    }
    
    public function actionUploading(){
        $post = $this->post();
        $post['csrf'] = $this->getCsrf();
        $post['request_url'] = Yii::$app->params['request_url'];
          return $this->render('uploading', $post);
    }

    public function actionPic() {
        $this->getView()->title = "视频认证";
        $user = $this->getUser();
        $redirect_info = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/borrow/userinfo/list';
        $videoModel = new Video_auth();
        $videoInfo = $videoModel->getAuthByUserID($user->user_id);  //视频认证纪录
        // 认证次数
        
        $user_iden_keys ='user_pic_times_' . $user->user_id;
        $user_iden = Yii::$app->redis->get($user_iden_keys);
        if (empty($user_iden)) {
            $videoTimes = 0;
        } else {
            $videoTimes = $user_iden;
        }
        $callBackUrl = Yii::$app->params['video_notify_url'];
        $requestUrl = Yii::$app->params['request_url'];
        //获取微信参数
        $jsinfo = $this->getWxParam();
        //页面分发
        $type = $this->get('type');
        $view = 'pic';
        if ($videoTimes > 0) {
            $this->layout = 'video';
            $this->getView()->title = '认证失败';
            $view = 'videofails';
        } else {
            //判断认证结果
            if (!empty($videoInfo) && $videoInfo->video_auth_status == '-1') {
                $url = '/borrow/userauth/videowaiting';
                return $this->redirect($url);
            } else if ($videoInfo && in_array($videoInfo->video_auth_status, [1, 2])) {
                $url = '/borrow/userauth/pic?type=1';
                return $this->redirect($url);
            }
        }

        return $this->render($view, [
                    'userinfo' => $user,
                    'redirect_info' => $redirect_info,
                    'callBackUrl' => $callBackUrl,
                    'orderinfo' => $videoInfo,
                    'times' => $videoTimes,
                    'request_url' => $requestUrl,
                    'jsinfo' => $jsinfo,
                    'encrypt' => ImageHandler::encryptKey($user->user_id, 'h5'),
                    'csrf' => $this->getCsrf(),
        ]);
    }

    /**
     * @return string
     * 认证中页面
     */
    public function actionVideowaiting() {
        $this->layout = 'video';
        $this->getView()->title = '视频认证中';
        $user = $this->getUser();
        $videoModel = new Video_auth();
        $videoInfo = $videoModel->getAuthByUserID($user->user_id);  //视频认证纪录
        //判断认证结果
        if ($videoInfo && in_array($videoInfo->video_auth_status, [1, 2])) {
            $url = '/borrow/userauth/pic?type=1';
            return $this->redirect($url);
        } else if (!$videoInfo) {
            $url = '/borrow/userauth/pic';
            return $this->redirect($url);
        } else if (!empty($videoInfo) && $videoInfo->video_auth_status == 3) {
            $url = '/borrow/userinfo/requireinfo';
            return $this->redirect($url);
        }
        return $this->render('videowaiting');
    }

    /**
     * @return string
     * 上传失败页面
     */
    public function actionUploadfailure() {
        $this->layout = 'video';
        $this->getView()->title = '上传失败';
        return $this->render('uploadfailure');
    }

    public function actionModify() {
        header("Content-type: text/html; charset=utf-8");
        $this->getView()->title = "实名认证";
        $user = $this->getUser();
        $redirect_info = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/borrow/userinfo/list';
        $profession = Keywords::getProfession();
        $profession = ArrayHelper::getColumn($profession, 0);
        $jsinfo = $this->getWxParam();
        return $this->render('modifynameauth', [
                    'user' => $user,
                    'redirect_info' => $redirect_info,
                    'jsinfo' => $jsinfo,
                    'encrypt' => ImageHandler::encryptKey($user->user_id, 'h5'),
                    'csrf' => $this->getCsrf(),
                    'profession' => json_encode($profession),
        ]);
    }

    /**
     * orc身份证校验
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionIdenfontajax() {
        $post_data = $this->post();
        $url = $post_data['urls'];
        $type = $post_data['type'];
        $oImage = new ImageHandler();
        $params = [
            'pic_file_path' => $oImage->img_domain_url . $url,
            'pic_type' => $type,
        ];
        $result = (new Apihttp())->postOpenOcr($params);
        $checkInfo = $this->ckeckResult($type, $result);
        if (!$checkInfo) {
            $code = 1;
            $data = [
                'msg' => '身份证信息获取失败',
            ];
            return $this->showMessage($code, $data);
        }
        $data = [
            'nation' => $result['res_data']['info_nation'],
            'iden_address' => $result['res_data']['info_address'],
            'realname' => $result['res_data']['info_name'],
            'identity' => $result['res_data']['info_number'],
            'msg' => '成功',
        ];
        $code = 0;
        return $this->showMessage($code, $data);
    }

    private function ckeckResult($type, $data) {
        if (!is_array($data) || empty($data) || $data['res_code'] != 0) {
            return false;
        }
        //传的正面 返回的不是正面
        if ($type == 1 && $data['res_data']['side'] != 'front') {
            if ($data['res_data']['side'] != 'front') {
                return false;
            }
            if (empty($data['res_data']['info_nation'])) {
                return false;
            }
            if (empty($data['res_data']['info_address'])) {
                return false;
            }
            if (empty($data['res_data']['info_name'])) {
                return false;
            }
            if (empty($data['res_data']['info_number'])) {
                return false;
            }
        }
        //传的反面 返回的不是反面
        if ($type == 2 && $data['res_data']['side'] != 'back') {
            return false;
        }
        return true;
    }

    /**
     * 更新实名数据
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionNameauthmodify() {
        $userinfo = $this->getUser();
        $post_data = $this->post();
        $profession = $post_data['profession'];
        $money = $post_data['money'];
        $company = $post_data['company'];
        $email = $post_data['email'];
        $pro = Keywords::getProfession();
        foreach ($pro as $key => $val) {
            if ($profession == $val[0]) {
                $profession = (string) $key;
            }
        }
        //保存User_extend信息
        $extend_condition = array(
            'user_id' => $userinfo->user_id,
            'profession' => $profession,
            'income' => $money,
            'company' => $company,
            'email' => $email
        );
        $userExtendModel = new User_extend();
        $oldExtend = $userExtendModel->getUserExtend($userinfo->user_id);
        //判断数据是否没有更改
        if (isset($oldExtend)) {
            if ($oldExtend->profession == $profession && $oldExtend->income == $money && $oldExtend->company == $company && $oldExtend->email == $email) {
                return $this->showMessage(1, '*数据没有更改,请更新之后提交');
            }
        }
        $extend_ret = $userExtendModel->save_extend($extend_condition);
        if (!$extend_ret) {
            return $this->showMessage(1, array('msg' => '*数据更新失败,请重新提交'));
        }
        return $this->showMessage(0, array('msg' => 'success'));
    }

    /**
     * 保存实名认证数据
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionNameauthajax() {
        $userinfo = $this->getUser();
        if(empty($userinfo)){
            return $this->showMessage(3, ['msg' => "登录已失效，请重新登录！", 'url' => '/borrow/loan']);
        }
        $userinfo = (new User())->getById($userinfo->user_id);
        $img_url_domain = (new ImageHandler())->img_domain_url;
        $post_data = $this->post();
        $identity = isset($post_data['identity'])?$post_data['identity']:'';
        $realname = isset($post_data['realname'])?$post_data['realname']:'';
        $iden_url = isset($post_data['iden_url'])?$post_data['iden_url']:'';
        $pic_self = isset($post_data['pic_self'])?$post_data['pic_self']:'';
        $nation = isset($post_data['nation'])?$post_data['nation']:'';
        $iden_address = isset($post_data['iden_address'])?$post_data['iden_address']:'';
        $profession = isset($post_data['profession'])?$post_data['profession']:'';
        $money = isset($post_data['money'])?$post_data['money']:'';
        $company = isset($post_data['company'])?$post_data['company']:'';
        $email = isset($post_data['email'])?$post_data['email']:'';
        //_csrf: csrf, realname: name, identity: idcard, iden_url: pic_identity, pic_self: pic_self, iden_address: iden_address, nation: nation， profession: profession, money: money
        //校验post数据
        if (empty($iden_url)) {
            return $this->showMessage(1, "*请上传身份证正面照片");
        }
        if (empty($pic_self)) {
            return $this->showMessage(1, "*请上传身份证反面照片");
        }
        if (empty($identity)) {
            return $this->showMessage(1, "*请填写您的身份证号");
        }
        if (empty($profession)) {
            return $this->showMessage(1, "*请填写您的职业");
        } else {
            $pro = Keywords::getProfession();
            foreach ($pro as $key => $val) {
                if ($profession == $val[0]) {
                    $profession = (string) $key;
                }
            }
        }
        if (empty($money)) {
            return $this->showMessage(1, "*请填写您的收入");
        }
        $id_card = Http::checkIdenCard($this->post('identity'));
        if (!$id_card) {
            return $this->showMessage(1, '*请填写正确的身份证');
        }
        $idValid = $userinfo->getIdentityValid($identity);
        if (!$idValid) {
            $identity_valid = 4;
        } else {
            $identity_valid = 2;
        }

        $num = (new Information_logs())->getMark($userinfo, 1); //获取是否可以再次认证
        if ($num == 0) {
            return $this->showMessage(1, '*调用ORC认证次数超限');
        }

        //保存User_extend信息
        $extend_condition = array(
            'user_id' => $userinfo->user_id,
            'profession' => $profession,
            'income' => $money,
            'company' => $company,
            'email' => $email,
        );
        $userExtendModel = new User_extend();
//        $oldExtend = $userExtendModel->getUserExtend($userinfo->user_id);
//        //判断数据是否没有更改
//        if (isset($oldExtend)) {
//            if ($userinfo->pic_self == $pic_self && (isset($userinfo->password->iden_url) && $userinfo->password->iden_url == $iden_url)) {
//                return $this->showMessage(1, '*数据没有更改,请更新之后提交');
//            }
//        }
        $extend_ret = $userExtendModel->save_extend($extend_condition);
        //验证用户身份证号码是否已经存在
        $userIdInfo = User::find()->where(['identity' => $identity])->one();
        if (!empty($userIdInfo) && $userIdInfo->user_id != $userinfo->user_id) {
            $informationModel = new Information_logs();
            $num = $informationModel->save_idenlogs($userinfo, 1, '', 2, 4);
            return $this->showMessage(3, ['msg' => "身份证号码已经存在！", 'url' => '/new/account/black']);
        }

        $informationModel = new Information_logs();
        $num = $informationModel->save_idenlogs($userinfo, 1, '', 2, 0);

        $canUpdate = FALSE;

        if (@fopen($img_url_domain . $userinfo->password->iden_url, 'r')) {
            $file = true;
        } else {
            $file = false;
        }

        if ($userinfo->identity_valid != 2 || $userinfo->identity_valid != 4 || ($userinfo->identity_valid == 2 && !$file)) {
            $canUpdate = TRUE;
        }
        if ($canUpdate) {
            $user_condition['realname'] = $realname;
            $user_condition['identity'] = $identity;
            $user_condition['pic_self'] = $pic_self;  //身份证反面
            $user_condition['birth_year'] = intval(substr($identity, 6, 4));
            $user_condition['identity_valid'] = $identity_valid;
        }
        $ret = $userinfo->update_user($user_condition);
        if (!$ret) {
            return $this->showMessage(4, "系统错误！");
        }
        if ($canUpdate) {
            $passwordCondition = [
                'user_id' => $userinfo['user_id'],
                'iden_url' => $iden_url,
            ];

            if (!empty($nation)) {
                $passwordCondition['nation'] = $nation;
            }
            if (!empty($iden_address)) {
                $passwordCondition['iden_address'] = $iden_address;
            }

            $setPasswordRet = (new User_password())->save_password($passwordCondition);
            if (!$setPasswordRet) {
                return $this->showMessage(4, "系统错误！");
            }
        }
        $black = new Black_list();
        if ($black->getInBlack($identity)) {
            $result = $userinfo->setBlack();
            if ($result) {
                $retArr = array("msg" => '*黑名单', 'url' => '/new/account/black');
                return $this->showMessage(3, $retArr);
            }
        }
        if ($canUpdate) {
            $regrule = $userinfo->getRegrule($userinfo, 1);
            $xindiao = \app\commonapi\Keywords::xindiao();
            if ($regrule == 1 && (empty($xindiao) || !in_array($userinfo->mobile, $xindiao))) {
                $userinfo->setBlack();
                $retArr = array("msg" => '*黑名单', 'url' => '/new/account/black');
                return $this->showMessage(3, $retArr);
            }
        }
        return $this->showMessage(0, array('msg' => 'success'));
    }

    public function actionMifuidentity(){
        $post_data = $this->post();
        $identity = isset($post_data['identity'])?$post_data['identity']:'';
        $apiHttp = new Apihttp();
        $is_mifuidentity = $apiHttp->sendMifuIdentity($identity);
        if ($is_mifuidentity['res_code']== '0000') {
            return $this->showMessage(1, '此身份证号码暂不支持认证，如有问题请联系客服，给您带来的不便，敬请谅解。');
        }
        return $this->showMessage(0, '成功');
    }
    /**
     * 信用卡认证
     */
    public function actionCard() {
        $this->layout = "userinfo/cardvalid";
        $this->getView()->title = "信用卡绑定";
        $user = $this->getUser();
        $user_name = $user->realname;
        $identity = empty($user->identity) ? '' : substr($user->identity, 0, 4) . "***********" . substr($user->identity, 14, 4);
        $user_id = $user->user_id;
        $type = $this->get('type', 0); //type：1从必填资料项去填写信用卡认证 2：从选填资料
        $redirect_info = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/borrow/userinfo/list';
        return $this->render('cardvalid', [
                    'user_name' => $user_name,
                    'identity' => $identity,
                    'user_id' => $user_id,
                    'data_type' => $type,
                    'csrf' => $this->getCsrf(),
                    'redirect_info' => $redirect_info,
        ]);
    }

    /**
     * 个人信息信用卡绑卡
     */
    public function actionBindcard_xyk() {
        $post_data = $this->post();
        $user = $this->getUser();

        $bank_card = $post_data['card'];
        $mobile = $post_data['mobile'];
        $code = $post_data['code'];
        $key = "bind_bank_" . $mobile;
        $key_requestid = 'requestid_bank_' . $mobile;
        $banktype = $post_data['banktype'];
        $ownerCode = $this->getRedis('getcode_bank_' . $mobile);
        //验证码错误
        if ($ownerCode != $code) {
            $resultArr = array('msg' => '');
            echo $this->showMessage(1, $resultArr, 'json');
            exit;
        }
        //获取卡片信息
        $cardbin = (new Card_bin())->getCardBinByCard($bank_card, "prefix_length desc");
        if (empty($cardbin)) {
            $resultArr = array('msg' => '请输入正确的信用卡号');
            echo $this->showMessage(1, $resultArr, 'json');
            exit;
        }
        //重复提交绑定数据
        $counts = User_bank::find()->where(['card' => $bank_card, 'status' => 1])->all();
        if (count($counts) > 0) {
            $resultArr = array('msg' => '重复提交已绑定的卡号');
            echo $this->showMessage(2, $resultArr, 'json');
            exit;
        }
        //四要素健全验证
        $result = $this->getBankauth($user, $mobile, $bank_card);
        if ($result['res_code'] != '0000') {//未通过
            $re = $this->getTianxingError($result);
            $resultArr = array('msg' => $re);
            echo $this->showMessage(3, $resultArr, 'json');
            exit;
        } else {//通过
            $verify = 1;
        }
        //获取地区对象
        $area = (new Areas())->getAreaOrSubBank(1);
        $post_data['sub_bank'] = (new Areas())->getAreaOrSubBank(2);
        //存储用户银行卡
        $ret_userbank = $this->saveUserBank($user, $cardbin, $area, $verify, $post_data);

        if ($ret_userbank) {
            $resultArr = array('msg' => '成功');
            echo $this->showMessage(0, $resultArr, 'json');
            exit;
        } else {
            $resultArr = array('ret' => '3');
            echo $this->showMessage(3, $resultArr, 'json');
            exit;
        }
    }

    /**
     * 用户绑卡数据
     * @param object $user 用户对象
     * @param array $cardbin carbin数组
     * @param array $area 地区数组
     * @param int $verify 1 天行验证通过；2 易宝验证通过
     * @param array $post_data
     * @return array
     */
    private function saveUserBank($user, $cardbin, $area, $verify, $post_data) {

        $condition['user_id'] = $user->user_id;
        $condition['type'] = $cardbin['card_type'];
        $condition['bank_abbr'] = $cardbin['bank_abbr'];
        $condition['bank_name'] = $cardbin['bank_name'];
        $condition['sub_bank'] = htmlspecialchars($post_data['sub_bank']);
        $condition['city'] = strval($area['city']);
        $condition['area'] = strval($area['area']);
        $condition['province'] = strval($area['province']);
        $condition['card'] = $post_data['card'];
        $condition['bank_mobile'] = $post_data['mobile'];
        $condition['verify'] = $verify;
        $ret_userbank = (new User_bank())->addUserbank($condition);
        return $ret_userbank;
    }

    /**
     * 四要素认证
     * @param object $user
     * @param  string $mobile
     * @param string $cardno
     * @return bool
     */
    private function getBankauth($user, $mobile, $cardno) {
        $postdata = array(
            'identityid' => $user->user_id,
            'username' => $user->realname,
            'idcard' => $user->identity,
            'cardno' => $cardno,
            'phone' => $mobile,
        );

        $openApi = new Apihttp;
        $result = $openApi->bankInfoValidRong($postdata);
        Logger::errorLog(print_r($result, true), 'Bankauth', 'bankauth');

        return $result;
    }

    /**
     * 获取天行返回错误信息
     */
    private function getTianxingError($result) {
        switch ($result['res_msg']) {
            case 'DIFFERENT':
                $result['res_msg'] = '请优先确认您输入的手机号码与办理银行卡时预留手机号码一致<br>请确认您的银行卡号是否填写正确';
                break;
            case 'ACCOUNTNO_INVALID':
                $result['res_msg'] = '请核实您的银行卡状态是否有效';
                break;
            case 'ACCOUNTNO_NOT_SUPPORT':
                $result['res_msg'] = '暂不支持此银行，请更换您的银行卡';
                break;
            default:
                $result['res_msg'] = $result['res_msg'];
        }
        return $result['res_msg'];
    }

    /**
     * 验证supportbank
     * @param string $bank_code
     * @return bool
     */
    private function chkSupport($bank_code) {
        if (!$bank_code) {
            return false;
        }
        return Bank::supportbank($bank_code);
    }

    /**
     * 信用卡发送验证码
     */
    public function actionBanksend() {
        $post_data = $this->post();
        $user = $this->getUser();
        $key = "bind_bank_" . $post_data['mobile'];

        //当天发送短信次数>=6直接结束
        $sms = new Sms();
        $sms_count = $sms->getSmsCount($post_data['mobile'], 7);
        if ($sms_count >= 6) {
            echo $this->showMessage(2, '', 'json');
            exit;
        }
        //获取绑卡信息
        $cardbin = (new Card_bin())->getCardBinByCard($post_data['cardno'], "prefix_length desc");
        $bank_code = !empty($cardbin['bank_abbr']) ? $cardbin['bank_abbr'] : '';
        //银行卡是否支持supportbank并且是否为对应卡类型
        $result = $this->chkSupport($bank_code);
        $banktype_info = TRUE;
        if ($post_data['banktype'] == 1 && $cardbin['card_type'] == 0) {
            $banktype_info = FALSE;
        }
        if ($post_data['banktype'] == 2 && $cardbin['card_type'] == 1) {
            $banktype_info = FALSE;
        }
        if ($post_data['banktype'] == 3) {
            $banktype_info = FALSE;
        }

        if (!$result || $banktype_info) {
            echo $this->showMessage(1, '', 'json');
            exit;
        }

        //发送绑卡手机验证码
        $api = new ApiSms();
        $api->sendBindCard($post_data['mobile'], 7);
        echo $this->showMessage(0, '', 'json');
        exit;
    }

    /**
     * 跳过信用卡ajax
     */
    public function actionJumpcardajax() {
        //$post = $this->post();
        $user = $this->getUser();
        $sacnTimesModel = new ScanTimes();
        $save_res = $sacnTimesModel->save_scan(['mobile' => $user->mobile, 'type' => 24]);
        if (!$save_res) {
            return json_encode(['res_code' => '1000']);
        }
        return json_encode(['res_code' => '0000']);
    }

    /*
     * 人工认证
     */

    public function actionPeoAuth() {
        $this->layout = 'peoauth';
        $this->getView()->title = '人工认证';
        $user = $this->getUser();
        $user_id = $user->user_id;
        $userInfo = User::findOne($user_id);
        $orderInfo = $this->get('orderinfo');
        //随即获取牌照类型
        $type_array = array(1, 2, 4, 5, 6, 8);
        $type_id = $type_array[rand(0, 5)];
        $pic_type = Pictype::find()->where(['id' => $type_id])->one();
        if ($userInfo) {
            if ($userInfo->status == '2') {
                $url = '/borrow/userauth/peoauthwaiting';
                return $this->redirect($url);
            }
        }
        return $this->render('peoauth', [
                    'userinfo' => $userInfo,
                    'pictype' => $pic_type,
                    'encrypt' => ImageHandler::encryptKey($userInfo->user_id, 'identity'),
                    'saveMsg' => '',
                    'access_token' => $this->getAccessToken(),
                    'csrf' => $this->getCsrf(),
                    'imgDefault' => "/images/dev/bczil_photo.png",
                    'orderinfo' => $orderInfo,
        ]);
    }

    /**
     * @return \yii\web\Response
     * 人工认证审核中
     */
    public function actionPeoauthwaiting() {
        $this->layout = 'video';
        $this->getView()->title = '人工审核中';
        $user = $this->getUser();
        $user_id = $user->user_id;
        $userInfo = User::findOne($user_id);
        if ($userInfo) {
            if ($userInfo->status == '3') {
                $url = '/borrow/userinfo/requireinfo';
                return $this->redirect($url);
            } else if ($userInfo->status == '4') {
                $url = '/borrow/userauth/peo-auth';
                return $this->redirect($url);
            }
        }
        return $this->render('peoauthwaiting');
    }

    /**
     * 联系人认证
     */
    public function actionContacts() {
        $this->layout = 'contact';
        $this->getView()->title = '联系人';
        //session验证用户是否登
        if (empty($this->getUser())) {
            return $this->redirect('/new/reg/login');
        } else {
            $user = $this->getUser();
            $user_id = $user->user_id;
        }

        if (!empty($user_id)) {
            $user_info = (new User)->checkUser('user_id', $user_id);
        }
        $relation = Keywords::getRelation();
        $relation_family = json_encode(array_values($relation[1]));
        $relation_common = json_encode(array_values($relation[2]));
        $redirect_info = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/borrow/userinfo/list';
        $result = array(
            'user_info' => $user_info,
            'relation_family' => $relation_family,
            'relation_common' => $relation_common,
            'user_id' => $user_id,
            'redirect_info' => $redirect_info,
        );

        return $this->render('contacts', $result);
    }

    public function actionSavecontacts() {
        $data = $this->post();
        $contacts_name = $data['contacts_name'];
        $relation_common = $data['relation_common'];
        $mobile = $data['mobile']; //py
        $relatives_name = $data['relatives_name']; //配偶
        $relation_family = $data['relation_family'];
        $phone = $data['phone'];
        if (empty($contacts_name) || empty($relatives_name)) {
            return $this->showMessage(2, "", 'json'); //传参格式不正确
        }
        $common_model = new Common();
        if (!($common_model->isMobile($mobile)) || !($common_model->isMobile($phone))) {
            return $this->showMessage(2, "", 'json'); //传参格式不正确
        }
        $contacts_flows = new Contacts_flows();
        $userinfo = (new User)->checkUser('user_id', $data['user_id']);

        if (!empty($userinfo['favorite'])) {
            if ($userinfo['favorite']['contacts_name'] == $contacts_name && $userinfo['favorite']['relatives_name'] == $relatives_name && $userinfo['favorite']['mobile'] == $mobile && $userinfo['favorite']['phone'] == $phone && $userinfo['favorite']['relation_common'] == $relation_common && $userinfo['favorite']['relation_family'] == $relation_family) {
                return $this->showMessage(4, "", 'json'); //和原记录一样
            }
            $result = $userinfo['favorite']->update_favoriteContacts($data);
            $ret = $contacts_flows->save_contactsFlows($data);
            if ($result && $ret) {
                return $this->showMessage(0, "", 'json');
            } else {
                return $this->showMessage(3, "", 'json'); //sql执行错误
            }
        } else {
            $favorite = new Favorite_contacts();
            $result = $favorite->save_favoriteContacts($data);
            $ret = $contacts_flows->save_contactsFlows($data);
            if ($result && $ret) {
                return $this->showMessage(0, "", 'json');
            } else {
                return $this->showMessage(3, "", 'json');
            }
        }
    }

}

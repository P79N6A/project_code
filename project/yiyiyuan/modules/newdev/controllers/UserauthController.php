<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Crypt3Des;
use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Areas;
use app\models\news\Black_list;
use app\models\news\Common;
use app\models\news\Contacts_flows;
use app\models\news\Favorite_contacts;
use app\models\news\Guide;
use app\models\news\Information_logs;
use app\models\news\Juxinli;
use app\models\news\Pictype;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_history_info;
use app\models\news\User_password;
use app\models\news\Video_auth;
use Yii;

class UserauthController extends NewdevController {

    public $layout               = 'reg';
    public $enableCsrfValidation = false;

    /*
     * 实名认证
     */

    public function actionNameauth() {
        $this->layout = "inv";
        $this->getView()->title = "个人资料";
        $user                   = $this->getUser();
        if (!isset($user) && empty($user)) {
            return $this->redirect("/new/reg/loginloan");
        }
        $userinfo  = User::findOne($user->user_id);
        $orderinfo = $this->get("orderinfo");
        if (!$orderinfo) {
            exit;
        }
        $redirect_arr   = $this->nextPage($orderinfo, 2);
        $identity_valid = 0;
        $num = (new Information_logs())->getMark($userinfo, 1); //获取是否可以再次认证

        if (@fopen(ImageHandler::$img_domain . $userinfo->password->iden_url, 'r')) {
            $file = true;
        } else {
            $file = false;
        }
        if ((($userinfo->identity_valid == 2 && $file) || $userinfo->identity_valid == 4 || $num = 0) && !empty($userinfo->realname)) {
            $identity_valid = 1;
        }
        if (@fopen(ImageHandler::$img_domain . $userinfo->pic_self, 'r')) {
            $backIden = true;
        } else {
            $backIden = false;
        }

        $jsinfo      = $this->getWxParam();
        $marriage    = Keywords::getMarriage();
        $edu         = Keywords::getEdu();
        $list        = Areas::getAllAreas();
        $list        = json_encode(array_merge(array($this->defaultArea()), json_decode($list, true)));
        $user_extend = User_extend::getUserExtend($user->user_id);

        return $this->render('nameauth', [
                    'userinfo'       => $userinfo,
                    'user_extend'    => $user_extend,
                    'marriage'       => $marriage,
                    'edu'            => $edu,
                    'jsinfo'         => $jsinfo,
                    'identity_valid' => $identity_valid,
                    'list'           => $list,
                    'backiden'       => $backIden,
                    'csrf'           => $this->getCsrf(),
                    'redirect_info'  => $redirect_arr,
                    'encrypt'        => ImageHandler::encryptKey($user->user_id, 'h5'),
        ]);
    }

    /*
     * 工作信息认证
     */

    public function actionWorkinfo() {
        $this->layout           = "data";
        $this->getView()->title = "工作信息";
        $orderinfo              = $this->get("orderinfo");
        if (!$orderinfo) {
            exit;
        }
        $redirect_arr = $this->nextPage($orderinfo, 3);
        $user         = $this->getUser();
        $jsinfo       = $this->getWxParam();
        $list         = Areas::getAllAreas();
        $list         = json_encode(array_merge(array($this->defaultArea()), json_decode($list, true)));
        $industry     = Keywords::getIndustry();
        $profession   = Keywords::getProfession();
        $position     = Keywords::getPosition();
        $user_extend  = $user->extend;
        return $this->render('workinfo', [
                    'jsinfo'        => $jsinfo,
                    'users'         => $user,
                    'industry'      => $industry,
                    'profession'    => $profession,
                    'position'      => $position,
                    'list'          => $list,
                    'user_extend'   => $user_extend,
                    'csrf'          => $this->getCsrf(),
                    'redirect_info' => $redirect_arr
        ]);
    }

    /*
     * 手机号认证
     */

    public function actionPhoneauth() {
        $this->layout           = 'data';
        $this->getView()->title = "手机号认证";
        $user                   = $this->getUser();
        $userinfo               = User::findOne($user->user_id);
        $orderinfo              = $this->get('orderinfo');
        if (!$orderinfo) {
            exit;
        }
        $redirect_arr = $this->nextPage($orderinfo, 7);
        if ($userinfo->realname == '' || $userinfo->identity == '') {
            exit;
        }
        $jsinfo = $this->getWxParam();
        return $this->render('phoneauth', [
                    'jsinfo'        => $jsinfo,
                    'mobile'        => $userinfo->mobile,
                    'last_mobile'   => substr($userinfo->mobile, -4),
                    'redirect_info' => $redirect_arr,
                    'user'          => $user,
                    'csrf'          => $this->getCsrf()
        ]);
    }

    /**
     * 保存实名认证数据
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionNameauthajax() {
        $post_data = $this->post();
        $identity  = $post_data['identity'];
        $realname  = $post_data['realname'];
        $edu       = intval($post_data['edu']);
        $user      = $this->getUser();
        $userinfo  = User::findOne($user->user_id);


        $iden_url     = $post_data['iden_url'];
        $pic_self     = $post_data['pic_self'];
        $nation       = $post_data['nation'];
        $iden_address = $post_data['iden_address'];
        $ocrApi       = $post_data['ocr_api'];  //是否调用了ocr接口  1 调用 0 未调用
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
        if (empty($post_data['district'])) {
            return $this->showMessage(1, "*请选择常住地址");
        }
        if (empty($post_data['home_address'])) {
            return $this->showMessage(1, "*详细地址不能为空");
        }
        if (empty($edu)) {
            return $this->showMessage(1, "*请选择学历");
        }
        if (empty($this->post('marriage'))) {
            return $this->showMessage(1, "*请选择婚姻");
        }
        $id_card = $this->chkIdCard($this->post('identity'));
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
        if ($num == 0 && $ocrApi == 1) {
            return $this->showMessage(1, '*调用ORC认证次数超限');
        }

        //记录三要素投保导流
        (new Guide())->addGuide(['user_id' => $user->user_id, 'mobile' => $user->mobile, 'identity' => $identity, 'realname' => $realname, 'from' => 2]);

        //保存User_extend信息
        $extend_condition = array(
            'user_id'      => $userinfo->user_id,
            'edu'          => $post_data['edu'],
            'marriage'     => $post_data['marriage'],
            'home_area'    => $post_data['district'],
            'home_address' => $post_data['home_address'],
        );
        $userExtendModel  = new User_extend();
        $oldExtend        = $userExtendModel->getUserExtend($userinfo->user_id);
        //判断数据是否没有更改
        if (isset($oldExtend)) {
            if ($userinfo->pic_self == $pic_self && (isset($userinfo->password->iden_url) && $userinfo->password->iden_url == $iden_url) && $oldExtend->edu == $extend_condition['edu'] && $oldExtend->marriage == $extend_condition['marriage'] && $oldExtend->home_area == $extend_condition['home_area'] && $oldExtend->home_address == $extend_condition['home_address']) {
                return $this->showMessage(1, '*数据没有更改,请更新之后提交');
            }
        }
        $extend_ret = $userExtendModel->save_extend($extend_condition);

        //验证用户身份证号码是否已经存在
        $userIdInfo = User::find()->where(['identity' => $identity])->one();
        if (!empty($userIdInfo) && $userIdInfo->user_id != $userinfo->user_id) {
            if ($ocrApi == 1) {
                $informationModel = new Information_logs();
                $num              = $informationModel->save_idenlogs($userinfo, 1, '', 2, 4);
            }
            return $this->showMessage(3, "身份证号码已经存在！");
        }

        if ($ocrApi == 1) {
            $informationModel = new Information_logs();
            $num              = $informationModel->save_idenlogs($userinfo, 1, '', 2, 0);
        }

        $canUpdate = FALSE;

        if (@fopen(ImageHandler::$img_domain . $userinfo->password->iden_url, 'r')) {
            $file = true;
        } else {
            $file = false;
        }

        if ($userinfo->identity_valid != 2 || $userinfo->identity_valid != 4 || ($userinfo->identity_valid == 2 && !$file)) {
            $canUpdate = TRUE;
        }

        $user_condition['edu'] = (string) $edu;
        if ($canUpdate) {
            $user_condition['realname']       = $realname;
            $user_condition['identity']       = $identity;
            $user_condition['pic_self']       = $pic_self;  //身份证反面
            $user_condition['birth_year']     = intval(substr($identity, 6, 4));
            $user_condition['identity_valid'] = $identity_valid;
        }
        $ret = $userinfo->update_user($user_condition);
        if (!$ret) {
            return $this->showMessage(4, "系统错误！");
        }
        if ($canUpdate) {
            $passwordCondition = [
                'user_id'  => $userinfo['user_id'],
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


        return $this->showMessage(0, array('msg' => 'success'));
    }

    /**
     * 保存工作信息
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionWorkinfoajax() {
        $post_data = $this->post();
        $user      = $this->getUser();
        $userinfo  = User::findOne($user->user_id);
        //验证post提交的数据
        if (empty($post_data['district']) || empty($post_data['industry']) || empty($post_data['profession']) || empty($post_data['position'])) {
            return $this->showMessage(1, "请选择相关信息");
        }
        if (empty($post_data['company']) || empty($post_data['address']) || empty($post_data['email'])) {
            return $this->showMessage(2, "请完整输入信息");
            return false;
        }
        $phone_chk = $this->chkPhone($post_data['telephone']);
        if (!$phone_chk) {
            return $this->showMessage(4, '请输入正确的单位电话');
        }
        $email_chk = $this->chkEmail($post_data['email']);
        if (!$email_chk) {
            return $this->showMessage(5, '您的电子邮件格式不正确');
        }

        //对数据库进行操作
        $transaction      = Yii::$app->db->beginTransaction();
        //更新用户信息User_extend
        $extend_condition = array(
            'user_id'         => $userinfo->user_id,
            'industry'        => $post_data['industry'],
            'company'         => $post_data['company'],
            'position'        => $post_data['position'],
            'profession'      => $post_data['profession'],
            'telephone'       => trim($post_data['telephone']),
            'email'           => trim($post_data['email']),
            'income'          => $post_data['income'],
            'company_area'    => $post_data['district'],
            'company_address' => trim($post_data['address']),
        );
        $userExtendModel  = new User_extend();
        $oldExtend        = $userExtendModel->getUserExtend($userinfo->user_id);
        if ($oldExtend && $oldExtend->industry == $extend_condition['industry'] && $oldExtend->company == $extend_condition['company'] && $oldExtend->position == $extend_condition['position'] && $oldExtend->profession == $extend_condition['profession'] && $oldExtend->telephone == $extend_condition['telephone'] && $oldExtend->email == $extend_condition['email'] && $oldExtend->income == $extend_condition['income'] && $oldExtend->company_area == $extend_condition['company_area'] && $oldExtend->company_address == $extend_condition['company_address']) {
            return $this->showMessage(7, '数据没有更改,请更新之后提交');
        }
        $extend_res = $userExtendModel->save_extend($extend_condition);

        //更新用户信息User
        $user_condition = array(
            'address'   => $post_data['address'],
            'company'   => $post_data['company'],
            'position'  => $post_data['position'],
            'telephone' => $post_data['telephone'],
            'industry'  => $post_data['industry'],
        );
        $user_res       = $userinfo->update_user($user_condition);
        if (!$user_res) {
            $transaction->rollBack();
            return $this->showMessage(6, '提交失败，请退出重新提交');
        }

        //判断黑名单
        $black_list = (new Black_list())->getInBlack($userinfo->identity);
        if ($black_list) {
            //跳转到黑名单错误提示页面 *设置黑名单成功事务提交，否则事务回滚
            $retArr    = array("msg" => '*黑名单', 'url' => '/new/account/black');
            $black_res = $userinfo->setBlack();
            if (!$black_res) {
                $transaction->rollBack();
                return $this->showMessage(3, $retArr);
            }
            $transaction->commit();
            return $this->showMessage(3, $retArr);
        }
        $transaction->commit();

        //第一次完善工作信息走注册决策引擎
        $history_count = User_history_info::find()->where(['user_id' => $userinfo->user_id, 'data_type' => 2])->count();
        $user_extend   = $userinfo->extend;
        if ($history_count == 0 && empty($user_extend->company_area)) {
            $regrule = $userinfo->getRegrule($userinfo, 1);
            if ($regrule == 1) {
                $userinfo->setBlack();
                $retArr = array("msg" => '*黑名单', 'url' => '/new/account/black');
                return $this->showMessage(3, $retArr);
            }
        }
        return $this->showMessage(0, 'success');
    }

    /**
     * orc身份证校验
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionIdenfontajax() {
        $post_data = $this->post();
        $url       = $post_data['urls'];
        $type      = $post_data['type'];
        $params    = [
            'pic_file_path' => ImageHandler::$img_domain . $url,
            'pic_type'      => $type,
        ];
        $result    = (new Apihttp())->postOpenOcr($params);
        $checkInfo = $this->ckeckResult($type, $result);
        if (!$checkInfo) {
            $code = 1;
            $data = [
                'msg' => '身份证信息获取失败',
            ];
            return $this->showMessage($code, $data);
        }
        $data = [
            'nation'       => $result['res_data']['info_nation'],
            'iden_address' => $result['res_data']['info_address'],
            'realname'     => $result['res_data']['info_name'],
            'identity'     => $result['res_data']['info_number'],
            'msg'          => '成功',
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
     *  手机验证
     *  @return json [res_code:res_code, res_data:res_data]
     */
    public function actionPhoneajax() {
        $user     = $this->getUser();
        $userinfo = User::findOne($user->user_id);
        //用户的服务密码
        if (empty($this->post('password'))) {
            return $this->showMessage(2, '*请填写手机服务密码');
        }
        $server_passwd = $this->chkPasswd($this->post('password'));
        if (!$server_passwd) {
            return $this->showMessage(3, '*密码由6-8字母或数字组成');
        }
        //判断验证码
        if ($this->post('type') == 2 && empty($this->post('captcha'))) {
            return $this->showMessage(4, '*请填写短信验证码');
        }
        $postData             = $this->post();
        $postData['get_type'] = 1;
        $juxinliModel         = new Juxinli();
        $array                = $juxinliModel->juxinli($userinfo, $postData);
//        $array = array(
//            "rsp_code"=> "0000",
//            "process_code"=> "10008",
//            "process_msg"=> "测试的返回数据",
//            "step"=> 2,
//            "show_dialog"=> 0
//        );
        return $this->showMessage($array['process_code'], $array);
    }

    /**
     * 验证邮箱
     * @param string  $email     邮箱
     * @return bool
     */
    private function chkEmail($email) {
        if (empty($email)) {
            return false;
        }
        $pattern = '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        if (!preg_match($pattern, $email)) {
            return false;
        }
        return true;
    }

    /**
     * 电话验证
     * @param  int   $phone     号码
     * @return bool;
     */
    private function chkPhone($phone) {
        if (empty($phone)) {
            return false;
        }
        if (!preg_match('/^0\d{2,3}\-?\d{7,8}$/', $phone)) {
            if (!preg_match('/^1(([35678][0-9])|(47))\d{8}$/', $phone)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 身份证验证
     * @param int $idcard
     * @return bool
     */
    private function chkIdCard($idcard) {
        $isIDCard1 = "/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/"; //15位
        $isIDCard2 = "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/"; //18位
        if (!preg_match($isIDCard1, $idcard)) {
            if (!preg_match($isIDCard2, $idcard)) {
                return false;
            }
        }
        return true;
    }

    /**
     *  密码验证
     *  @param int $passwd
     *  @return bool
     */
    private function chkPasswd($passwd) {
        $pass_pattern = "/^[0-9A-Za-z]{6,8}$/";
        if (!preg_match($pass_pattern, $passwd)) {
            return false;
        }
        return true;
    }

    /**
     * 地区默认
     */
    private function defaultArea() {
        return array(
            'code' => 0,
            'name' => '请选择省',
            'area' =>
            array(
                0 =>
                array(
                    'code' => 0,
                    'name' => '请选择市',
                    'area' =>
                    array(
                        0 =>
                        array(
                            'code' => 0,
                            'name' => '请选择区',
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 获取跳转地址
     * @param string $orderinfo
     * @param int $current_code
     * @return array
     */
    private function nextPage($orderinfo, $current_code, $end = 0) {
        if ($orderinfo == '') {
            exit;
        }
        $nextpage      = $this->getNextpage($orderinfo, $current_code, $end);
        //获取下一步信息
        $redirect_info = array(
            'nextPage'  => $nextpage,
            'orderinfo' => "orderinfo=" . urlencode($orderinfo)
        );
        return $redirect_info;
    }

    /*
     * 联系人信息
     */

    public function actionContacts() {
        $this->getView()->title = '联系人';
        $order_info             = $this->get('orderinfo');
        $this->layout           = "data";
        //session验证用户是否登
        if (empty($this->getUser())) {
            return $this->redirect('/new/reg/login');
        } else {
            $user    = $this->getUser();
            $user_id = $user->user_id;
        }

        if (!empty($user_id)) {
            $userinfo = (new User)->checkUser('user_id', $user_id);
        }
        $result = array(
            'userinfo'  => $userinfo,
            'orderinfo' => $order_info,
        );

        return $this->render('contacts', $result);
    }

    /*
     * 联系人信息添加/修改
     */

    public function actionSavecontacts() {
        $data            = $this->post();
        $contacts_name   = $data['contacts_name'];
        $relation_common = $data['relation_common'];
        $mobile          = $data['mobile']; //py
        $relatives_name  = $data['relatives_name']; //配偶
        $relation_family = $data['relation_family'];
        $phone           = $data['phone'];
        if (empty($contacts_name) || empty($relatives_name)) {
            return $this->showMessage(2, "", 'json'); //传参格式不正确
        }
        $common_model = new Common();
        if (!($common_model->isMobile($mobile)) || !($common_model->isMobile($phone))) {
            return $this->showMessage(2, "", 'json'); //传参格式不正确
        }
        $contacts_flows = new Contacts_flows();
        $userinfo       = (new User)->checkUser('user_id', $data['user_id']);

        //跳转页面
        $orderinfo_param            = $data['orderinfo'];
        $order_info                 = $this->getNextpage($orderinfo_param, 5);
        //获取下一步信息
        $redirect_info              = array(
            'current_url' => $order_info,
        );
        $redirect_info['orderinfo'] = urlencode($orderinfo_param);
        //删除多余信息
        unset($data['_csrf']);
        unset($data['orderinfo']);
        if (!empty($userinfo['favorite'])) {
            if ($userinfo['favorite']['contacts_name'] == $contacts_name && $userinfo['favorite']['relatives_name'] == $relatives_name && $userinfo['favorite']['mobile'] == $mobile && $userinfo['favorite']['phone'] == $phone && $userinfo['favorite']['relation_common'] == $relation_common && $userinfo['favorite']['relation_family'] == $relation_family) {
                return $this->showMessage(4, "", 'json'); //和原纪录一样
            }
            $result = $userinfo['favorite']->update_favoriteContacts($data);
            $ret    = $contacts_flows->save_contactsFlows($data);
            if ($result && $ret) {
                return $this->showMessage(0, $redirect_info, 'json');
            } else {
                return $this->showMessage(3, "", 'json'); //sql执行错误
            }
        } else {
            $favorite = new Favorite_contacts();
            $result   = $favorite->save_favoriteContacts($data);
            $ret      = $contacts_flows->save_contactsFlows($data);
            if ($result && $ret) {
                return $this->showMessage(0, $redirect_info, 'json');
            } else {
                return $this->showMessage(3, "", 'json');
            }
        }
    }

    /*
     * 自拍照
     */
    public function actionPic() {
        $this->getView()->title = "我的资料";

        $orderInfo  = $this->get('orderinfo');
        $type       = $this->get('type', 1);
        $user       = $this->getUser();
        $user_id    = $user->user_id;
        $userInfo   = User::findOne($user_id);
        $videoModel = new Video_auth();
        $videoInfo  = $videoModel->getAuthByUserID($user_id);
        if (@fopen(ImageHandler::$img_domain . $userInfo->password->iden_url, 'r')) {
            $file = true;
        } else {
            $file = false;
        }
        if ($userInfo->status != 3 && (in_array($userInfo->identity_valid, [2, 4])) && !$file) {
            $url = '/new/userauth/nameauth?orderinfo=' . $orderInfo;
            return $this->redirect($url);
        }
        switch ($type) {
            case 1:
                if (!empty($videoInfo) && in_array($videoInfo->video_auth_status, [1, 2])) {
                    $url = '/new/userauth/videofail';
                    return $this->redirect($url);
                } elseif (!empty($videoInfo) && $videoInfo->video_auth_status == '-1') {
                    $url = '/new/userauth/videowaiting';
                    return $this->redirect($url);
                }
                return $this->video($orderInfo, $userInfo);
                break;
            case 2:
                return $this->pic($orderInfo, $userInfo);
                break;
            default:
                exit('非法访问');
                break;
        }
    }

    public function pic($orderInfo, $userInfo) {
        $this->layout = "main";
        //随即获取牌照类型
        $type_array   = array(1, 2, 4, 5, 6, 8);
        $type_id      = $type_array[rand(0, 5)];
        $pic_type     = Pictype::find()->where(['id' => $type_id])->one();
        return $this->render('pic', [
                    'userinfo'     => $userInfo,
                    'pictype'      => $pic_type,
                    'encrypt'      => ImageHandler::encryptKey($userInfo->user_id, 'identity'),
                    'saveMsg'      => '',
                    'access_token' => $this->getAccessToken(),
                    'csrf'         => $this->getCsrf(),
                    'imgDefault'   => "/images/dev/bczil_photo.png",
                    'orderinfo'    => $orderInfo,
        ]);
    }

    public function video($orderInfo, $userInfo) {
        $this->layout = "inv";
        $videoModel   = new Video_auth();
        $videoTimes   = $videoModel->getAuthCount($userInfo->user_id);
        $videoInfo    = $videoModel->getAuthByUserID($userInfo->user_id);
        if (!empty($videoInfo) && $videoInfo->video_auth_status == -1) {
            return $this->redirect('/new/userauth/videowaiting');
        }
        $callBackUrl = Yii::$app->params['video_notify_url'];
        $requestUrl  = Yii::$app->params['request_url'];
        return $this->render('video', [
                    'userinfo'     => $userInfo,
                    'times'        => $videoTimes,
                    'access_token' => $this->getAccessToken(),
                    'csrf'         => $this->getCsrf(),
                    'orderinfo'    => $orderInfo,
                    'callBackUrl'  => $callBackUrl,
                    'request_url'  => $requestUrl,
                    'videoInfo'    => $videoInfo
        ]);
    }

    public function actionVideofail() {
        $this->getView()->title = "我的资料";

        $this->layout = "inv";
        $user         = $this->getUser();
        $user_id      = $user->user_id;
        $userInfo     = User::findOne($user_id);
        $videoModel   = new Video_auth();
        $videoTimes   = $videoModel->getAuthCount($userInfo->user_id);
        $videoInfo    = $videoModel->getAuthByUserID($userInfo->user_id);
        $requestUrl   = Yii::$app->params['request_url'];
        if (!empty($videoInfo) && $videoInfo->video_auth_status == -1) {
            return $this->redirect('/new/userauth/videowaiting');
        }
        $callBackUrl = Yii::$app->params['video_notify_url'];
        return $this->render('videofail', [
                    'times'       => $videoTimes,
                    'csrf'        => $this->getCsrf(),
                    'callBackUrl' => $callBackUrl,
                    'userinfo'    => $userInfo,
                    'request_url' => $requestUrl,
                    'videoInfo'   => $videoInfo
        ]);
    }

    /*
     * 等待页刷新按钮
     */

    public function actionVideowaiting() {
        $user    = $this->getUser();
        $user_id = $user->user_id;
        $info    = (new Video_auth())->getAuthByUserID($user_id);
        if (empty($info) || in_array($info['video_auth_status'], [0, -1])) {
            $this->getView()->title = "我的资料";
            $this->layout           = "inv";
            return $this->render('videowaiting', [
                        'csrf' => $this->getCsrf(),
            ]);
        }
        if ($info['video_auth_status'] == 3) {
            $url = '/new/userauth/videosuccess';
        }
        if (in_array($info['video_auth_status'], [1, 2])) {
            $url = '/new/userauth/videofail';
        }
        return $this->redirect($url);
    }

    public function actionVideosuccess() {
        $this->getView()->title = "我的资料";
        $this->layout           = "inv";
        $user                   = $this->getUser();
        $user_id                = $user->user_id;
        $userInfo               = User::findOne($user_id);
        $videoModel             = new Video_auth();
        $videoTimes             = $videoModel->getAuthCount($userInfo->user_id);
        $videoInfo              = $videoModel->getAuthByUserID($userInfo->user_id);
        return $this->render('videosuccess', [
                    'times'     => $videoTimes,
                    'csrf'      => $this->getCsrf(),
                    'videoInfo' => $videoInfo
        ]);
    }

    public function actionPicsave() {
        $postinfo = $this->post();
        Logger::dayLog('weixin/userauth/picsave',$postinfo);
        //@TODO post数据获取方式修改 ok
        if (empty($postinfo)) {
            return $this->redirect('/new/loan'); //@TODO 将else摘出  跳转到借款首页 ok
        }
//        if (!isset($postinfo['orderinfo'])) {
//            exit;
//        }
//        $orderinfo = $postinfo['orderinfo'];
        $user    = $this->getUser();
        $user_id = $user->user_id;

//        $nextPage = $this->nextPage($orderinfo, 4, 1);
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        //判断用户是否是黑名单用户
        if ($userinfo->status == 5) {
            //跳转到黑名单错误提示页面
            return $this->redirect('/new/account/black');
        }
        //判断照片是否存在、是否不为空
        if(!isset($postinfo['supplyUrl']['1']) || empty($postinfo['supplyUrl']['1'])){
            return $this->redirect('/borrow/userauth/peo-auth');
        }
        $pic_type      = $postinfo['pic_type'];
        $serverid      = $postinfo['serverid'];
        //更新用户照片信息
        $filename      = $postinfo['supplyUrl']['1'];
        $pic_up_time   = date('Y-m-d H:i:s', time());
//            $sql = "update " . User::tableName() . " set pic_identity='" . $filename . "',pic_type=" . $pic_type . ",status=2,serverid='" . $serverid . "',pic_up_time = '" . $pic_up_time . "' where user_id=" . $userinfo->user_id;
//            Logger::errorLog($sql, 'sql');
//            $ret = Yii::$app->db->createCommand($sql)->execute();
        $pic_condition = [
            'pic_identity' => $filename,
            'pic_type'     => $pic_type,
            'status'       => 2,
            'serverid'     => $serverid,
            'pic_up_time'  => $pic_up_time
        ];
        $ret           = $userinfo->update_user($pic_condition);
        if ($ret) {
            //验证跳转页面
//            return $this->redirect($nextPage['nextPage']);
            return $this->redirect('/borrow/userauth/peoauthwaiting');
        } else {
            return $this->redirect('/borrow/userauth/peo-auth');
        }
    }

    public function actionVideosave() {
        $post       = $this->post();
        //保存用户视频认证信息
        $request_id = "V" . date('mdHis') . $post['user_id'];
        $res        = [
            'user_id'    => $post['user_id'],
            'request_id' => $request_id,
        ];
        $videoModel = new Video_auth();
        $videoTimes = $videoModel->getAuthCount($post['user_id']);
        if ($videoTimes >= 5) {
            return $this->showMessage(3, '认证次数达到上限：您的认证次数已达上限，请使用人工审核方式或30天后重试');
        }
        $video = $videoModel->getAuthByUserID($post['user_id']);
        if (!empty($video) && in_array($video->video_auth_status, [3, -1])) {
            return $this->showMessage(2, '您有认证中的视频，请稍后再试');
        }
        $videoInfo = $videoModel->saveVideo($res);
        if (!$videoInfo) {
            return $this->showMessage(1, '保存视频认证信息失败');
        }
        $key                = '579BEFGINPQUVZehilprstxy';
        $data['request_id'] = Crypt3Des::encrypt($request_id, $key);
        return $this->showMessage(0, $data);
    }

}

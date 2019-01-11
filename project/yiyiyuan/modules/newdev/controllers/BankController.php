<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apidepository;
use app\commonapi\ApiYaoyuefu;
use app\commonapi\Apihttp;
use app\commonapi\Bank;
use app\commonapi\Keywords;
use app\models\news\ApiSms;
use app\models\news\Areas;
use app\models\news\Card_bin;
use app\models\news\CardLimit;
use app\models\news\Payaccount;
use app\models\news\ScanTimes;
use app\models\news\Sms;
use app\models\news\Sms_depository;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User;
use app\commonapi\Logger;
use app\models\news\Common;
use app\models\news\User_password;
use app\commonapi\ImageHandler;
use Faker\Provider\bg_BG\Payment;
use Yii;

class BankController extends NewdevController {

    public $layout = 'bank';
    public $channel = '000002';

    public function actionIndex() {
        $this->getView()->title = "银行卡";
        $user = $this->getUser();
        $banks = (new User_bank())->getBankByUserId($user->user_id, 0, 'default_bank desc,create_time desc');
        $order = (new User())->getPerfectOrder($user->user_id, 4, 12, 1);
        $orderInfo = (new Common())->create3Des(json_encode($order, true));
        $nextPage = $this->nextUrl($orderInfo, 6, 0, 1);
        $jsinfo = $this->getWxParam();
        $isopenBank = Keywords::isOpenBank();
        $payaccount = new Payaccount();
        $isOpen = $payaccount->getPaysuccessByUserId($user->user_id, 2, 1);
        $cgCardId = "";
        if(!empty($isOpen) && !empty($isOpen->card)){
            $cgCardId = $isOpen->card;
        }
        if ($isopenBank == 2) {
            $this->layout = '_bank';
            return $this->render('index', [
                        'banks' => $banks,
                        'user_id' => $user->user_id,
                        'jsinfo' => $jsinfo,
                        'nextPage' => $nextPage,
                        'orderInfo' => $orderInfo,
                        'cgCardId' => $cgCardId,
            ]);
        } else {
            $this->layout = '_banknew';
            $payAccount = new Payaccount();
            $cunguan = $payAccount->getPaystatusByUserId($user->user_id, 2, 1);
            return $this->render('indexnew', [
                        'banks' => $banks,
                        'user_id' => $user->user_id,
                        'csrf' => $this->getCsrf(),
                        'jsinfo' => $jsinfo,
                        'nextPage' => $nextPage,
                        'orderInfo' => $orderInfo,
                        'cunguan' => $cunguan,
                        'cgCardId' => $cgCardId,
            ]);
        }
    }
    
    public function actionAddcardjump(){
        $user = $this->getUser();
        $identify_valid = $this->getUserIdentify($user);
        if($identify_valid == 2){
            return json_encode(['res_code'=>'0000','res_msg'=>'已实名认证']);
        }
        return json_encode(['res_code'=>'0001','res_msg'=>'未实名认证']);
    }
    
    public function getUserIdentify($user){
        $img_url_domain = (new ImageHandler())->img_domain_url;
        //身份信息 1：未认证  2:已认证
        $passModel = new User_password();
        $pass = $passModel->getUserPassword($user->user_id);
        $identify_valid = 1;
        $oUserExtend = $user->extend;
        if (!empty($pass)) {
            $path = $img_url_domain . $pass->iden_url;
            if ($user->status == 3 || ($user->identity_valid == 2 && !empty($pass) && !empty($pass->iden_url) && @fopen($path, 'r'))) {
                $identify_valid = 2;
                if( empty($oUserExtend) || empty($oUserExtend->profession) || empty($oUserExtend->income) || empty($oUserExtend->company) || empty($oUserExtend->email)){
                     $identify_valid = 1;
                }
            }
        }
        return $identify_valid;
    }

    
    public function actionDelbank() {
        $userinfo = $this->getUser();
        $this->getView()->title = "银行卡详情";
        $this->layout = '_bank';
        $id = intval($this->get('id'));
        if (empty($id) || !isset($id)) {
            return $this->redirect('/new/account/index');
        }
        $userbank = User_bank::findOne($id);
        if (empty($userbank) || ($userbank->user_id != $userinfo->user_id)) {
            return $this->redirect('/new/account/index');
        }
        $jsinfo = $this->getWxParam();
        $userBankModel = new User_bank();
        $hasDefault = $userBankModel->hasDefault($userinfo->user_id);
        return $this->render('delbank', [
                    'userbank' => $userbank,
                    'jsinfo' => $jsinfo,
                    'csrf' => $this->getCsrf(),
                    'hasDefault' => $hasDefault,
        ]);
    }

    /**
     * 解除绑定
     * @return string
     */
    public function actionDelcard() {
        $id = intval($this->get('id'));
        $type_source = $this->get('type',0);
        $userbank = User_bank::findOne($id);
        if ( empty($userbank) ) {
            return json_encode(array('code' => '1', 'message' => '该银行卡不存在'));
        }
        $condition = ['user_id' => $userbank->user_id, 'status' => array('5', '6', '9', '11', '12', '13'), 'bank_id' => $id];
        $loan = User_loan::find()->where($condition)->count();
        if ($loan > 0) {
            return json_encode(array('code' => '1', 'message' => '您存在正在进行中的借款,暂时不能解绑该卡'));
        }
        if ($userbank->type == 0) {
            $bankModel = new User_bank();
            $userbanks = $bankModel->getBankByUserId($userbank->user_id, 0);
            if (count($userbanks) <= 1) {
                return json_encode(array('code' => '1', 'message' => '您目前只有一张借记卡，暂时不能解绑该卡'));
            }
        }
        $payaccount = new Payaccount();
        $isOpen = $payaccount->getPaysuccessByUserId($userbank->user_id, 2, 1);
        if ($isOpen && $isOpen->card == $userbank->id) {
            $isSetpass = $payaccount->getPaysuccessByUserId($userbank->user_id, 2, 2);
            if(empty($isSetpass) || $isSetpass->activate_result != 1){
                return json_encode(array('code' => '1', 'message' => '该卡为存管内绑定卡，请先设置存管交易密码后，再进行解绑！'));
            }
            //查询是否有余额
            $balanceInfo = $this->getBalanceInquiry($isOpen['accountId']);
            if(!empty($balanceInfo) && is_array($balanceInfo) && isset($balanceInfo['currBal'])){
                Logger::dayLog('depository/needcare', 'payaccount', $balanceInfo['currBal'], $isOpen['accountId']);
                if(!empty($balanceInfo['currBal'])){
                    return json_encode(array('code' => '1', 'message' => '您的存管账户中余额，请联系客服解卡'));
                }
            }
            //解除存管内的卡
            $userInfo = User::findOne($userbank->user_id);
            $result = $this->depositoryoverbind($userInfo, $isOpen, $userbank,$type_source);
            if (!$result) {
                return json_encode(array('code' => '2', 'message' => '解绑失败！'));
            }
            if( !empty($result) && $result['res_code'] == 0 &&  !empty($result['res_data']) ){
                 return json_encode(array('code' => '0', 'message' => '成功！','data'=>$result['res_data']));
            }
            return json_encode(array('code' => '2', 'message' => '解绑失败！'));
        }
        if ($userbank->delUserBank()) {
            return json_encode(array('code' => '0', 'message' => '解绑成功！','data'=> ''));
        } else {
            return json_encode(array('code' => '2', 'message' => '解绑失败！'));
        }

    }

    /*
    * 存管卡余额查询
    */
    public function getBalanceInquiry($accountId) {
        $res_data = "";
        $accountId = !empty($accountId) ?trim($accountId):"";
//        $accountId = "6212462040000151250";
        if(!empty($accountId)){
            $postData = array(
                'channel'=>$this->channel,
                'accountId'=>$accountId,
            );
            $openApi = new ApiYaoyuefu();
            $res = $openApi->send('queryall/balancequery', $postData);
            if(!$res){
                Logger::errorLog(print_r($res, true), 'getBalanceInquiry');
            }
            if(!empty($res['res_data'])){
                $res_data = $res['res_data'];
            }

        }
        return $res_data;
    }

    /**
     * 设为默认卡
     */
    public function actionDefcard() {
        $id = intval($this->get('id'));
        $userbank = User_bank::findOne($id);
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $payAccount = new Payaccount();
        $isOpen = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isOpen) {
            $result = $userbank->updateDefaultBank($userInfo->user_id, $userbank->id);
            if (!$result) {
                echo json_encode(array('code' => '1', 'message' => '设置失败'));
                exit;
            }
            echo json_encode(array('code' => '0', 'message' => '设置成功'));
            exit;
        }
        if (empty($isOpen->card)) {
            //短信次数
            $smsCount = (new Sms_depository())->getSmsCount($userInfo->mobile, 2);
            if ($smsCount >= 6) {
                $resultArr = array('code' => '5', 'message' => "您今天获取验证码的次数过多，请明天再试");
                echo json_encode($resultArr);
                exit;
            }
            $sms = $this->sendBindCode($userInfo, $userbank);
            if (!$sms) {
                echo json_encode(array('code' => '4', 'message' => '验证码发送失败'));
                exit;
            }
            (new Sms_depository())->addList(['recive_mobile' => $userInfo->mobile, 'sms_type' => 2]);
            echo json_encode(array('code' => '3', 'message' => '银行存管绑卡需要验证码', 'data' => $sms));
            exit;
        }
        $card = User_bank::find()->where(['user_id' => $userInfo->user_id, 'card' => $isOpen->card, 'status' => 1])->one();
        if ($card) {
            $loan = User_loan::find()->where(['user_id' => $userInfo->user_id, 'bank_id' => $card->id, 'status' => [5, 6, 9, 11, 12, 13]])->one();
            if (!empty($loan)) {
                echo json_encode(array('code' => '2', 'message' => '您存在正在进行中的借款,暂时不能更换默认卡'));
                exit;
            }
        }

        //短信次数
        $smsCount = (new Sms_depository())->getSmsCount($userInfo->mobile, 2);
        if ($smsCount >= 6) {
            $resultArr = array('code' => '5', 'message' => "您今天获取验证码的次数过多，请明天再试");
            echo json_encode($resultArr);
            exit;
        }
        $sms = $this->sendBindCode($userInfo, $userbank);
        if (!$sms) {
            echo json_encode(array('code' => '4', 'message' => '验证码发送失败'));
            exit;
        }
        (new Sms_depository())->addList(['recive_mobile' => $userInfo->mobile, 'sms_type' => 2]);
        echo json_encode(array('code' => '3', 'message' => '银行存管绑卡需要验证码', 'data' => $sms));
        exit;
    }

    /**
     * 存管绑卡
     * @return mixed
     */
    public function actionBinding() {
        $postData = $this->post();
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        if (empty($postData) || !$postData['bank_id'] || !$postData['mobile'] || !$postData['srvAuthCode']) {
            $resultArr = array('ret' => '1', 'msg' => "参数不能为空");
            echo json_encode($resultArr);
            exit;
        }
        if (!$postData['code']) {
            $resultArr = array('ret' => '2', 'msg' => "请输入验证码");
            echo json_encode($resultArr);
            exit;
        }
        $bankInfo = User_bank::findOne($postData['bank_id']);
        if (!$bankInfo) {
            $resultArr = array('ret' => '3', 'msg' => "银行卡信息错误");
            echo json_encode($resultArr);
            exit;
        }
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isAccount) {
            $resultArr = array('ret' => '4', 'msg' => "用户未开户成功");
            echo json_encode($resultArr);
            exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',
            'from' => 1,
            'accountId' => $isAccount->accountId, //存管平台分配的账号
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $bankInfo->card,
            'lastSrvAuthCode' => $postData['srvAuthCode'],
            'smsCode' => $postData['code'],
        ];
        $ret_open = $apiDep->binding($params);
        if (!$ret_open) {
            $resultArr = array('ret' => '5', 'msg' => "绑卡失败");
            echo json_encode($resultArr);
            exit;
        }
        $up_res = $isAccount->update_list(['card' => (string) $bankInfo->id]);
        if (!$up_res) {
            Logger::dayLog('depository/needcare', 'payaccount', $bankInfo->card, $isAccount->user_id);
            $resultArr = array('ret' => '6', 'msg' => "绑卡失败");
            echo json_encode($resultArr);
            exit;
        }
        $bankModel = new User_bank();
        $default_result = $bankModel->updateDefaultBank($userInfo->user_id, $bankInfo->id);
        if (!$default_result) {
            Logger::dayLog('depository/needcare', 'bank', $bankInfo->card, $isAccount->user_id);
            $resultArr = array('ret' => '6', 'msg' => "绑卡失败");
            echo json_encode($resultArr);
            exit;
        }
        $isAuth = '0';
        $isAuthInfo = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 3);
        if ($isAuthInfo) {
            $isAuth = '1';
        }
        $resultArr = array('ret' => '0', 'isAuth' => $isAuth, 'msg' => "绑卡成功");
        echo json_encode($resultArr);
        exit;
    }

    /*     * 银行卡限额页面 */

    public function actionQuota() {
        $this->view->title = '支持银行列表';
        $this->layout = '_bank';
        $credit_limit = (new CardLimit())->find()->where(['type'=>3,'card_type'=>0,'status'=>2])->orderBy('id asc')->asArray()->all();
        $debit_limit = (new CardLimit())->find()->where(['type'=>3,'card_type'=>1,'status'=>2])->orderBy('id asc')->asArray()->all();
        $jsinfo = $this->getWxParam();
        return $this->render('quota', [
            'credit_limit' => $credit_limit,
            'debit_limit' => $debit_limit,
            'jsinfo' => $jsinfo,
        ]);
    }

    /**
     * 获取存管绑卡验证码
     * @return string
     */
    public function actionGetbcode() {
        $mobile = $this->get('mobile');
        if (!$mobile) {
            $resultArr = array('ret' => '1', 'msg' => "获取失败");
            echo json_encode($resultArr);
            exit;
        }
        //短信次数
        $smsCount = (new Sms_depository())->getSmsCount($mobile, 2);
        if ($smsCount >= 6) {
            $resultArr = array('ret' => '1', 'msg' => "您今天获取验证码的次数过多，请明天再试");
            echo json_encode($resultArr);
            exit;
        }
        $params['srvTxCode'] = 'cardBindPlus';
        $params['from'] = 1;
        $params['channel'] = '000002';
        $params['mobile'] = $mobile;
        $apiDep = new Apidepository();
        $codeRes = $apiDep->sendmsg($params);
        if (!$codeRes) {
            $resultArr = array('ret' => '1', 'msg' => "获取失败");
            echo json_encode($resultArr);
            exit;
        }
        (new Sms_depository())->addList(['recive_mobile' => $mobile, 'sms_type' => 2]);
        $resultArr = array('ret' => '0', 'msg' => "获取成功", 'data' => $codeRes);
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 输入银行卡号
     */
    public function actionAddcard() {
        $this->getView()->title = '添加银行卡';
        $user = $this->getUser();
        $source_mark = $this->get('source_mark',0);
        if( $source_mark == 1 ){ //从先花商城跳转过来的
            Yii::$app->redis->setex('shop_addcard_'.$user->user_id,86400,$user->user_id);
        }
        $orderinfo = $this->get("orderinfo");
        $banktype = $this->get("banktype"); //1 为绑定储蓄卡 2为绑定信用卡 3为银行卡
        if (!$orderinfo) {
            return $this->redirect('/new/loan');
        }
        if ($user->status == 5) {
            return $this->redirect('/new/account/black');
        }
        $list = Areas::getAllAreas();
        $list = array_merge(array($this->defaultArea()), json_decode($list, true));
        return $this->render('addcard', [
                    'banktype' => $banktype,
                    'user' => $user,
                    'list' => json_encode($list),
                    'orderinfo' => $orderinfo,
        ]);
    }
    /**
     * 新的绑定信用卡
     */
    public function actionXykadd() {
        $this->getView()->title = '添加信用卡';
        $user = $this->getUser();

        $orderinfo = $this->get("orderinfo");
        if (!$orderinfo) {
            return $this->redirect('/new/loan');
        }

        $newuser['identity']=substr_replace($user->identity,'*********',3,9);
        $newuser['realname']='*'.mb_substr($user->realname,1,mb_strlen($user->realname)-1,'utf8');
        $nextPage = $this->getNext(2);
//        var_dump($nextPage);
        $jsinfo = $this->getWxParam();
        return $this->render('xykadd', [
            'user' => $user,
            'orderinfo' => $orderinfo,
            'newuser' => $newuser,
            'csrf' => $this->getCsrf(),
            'jsinfo' => $jsinfo,
            'url'=>$nextPage,
        ]);
    }

    public function actionTiaozhuan(){
        $scan_times=new ScanTimes();
        $userinfo=$this->getUser();
        $scan_times->getScanCount($userinfo->mobile,24);
        $resultArr = array('msg' => '');
        echo $this->showMessage(0, $resultArr, 'json');
        exit;
    }

    /**
     * 个人信息的添加信用卡
     */
    public function actionXykaddcard() {
        $this->getView()->title = '添加信用卡';
        $user = $this->getUser();
        $jsinfo = $this->getWxParam();
        $newuser['identity']=substr_replace($user->identity,'*********',3,9);

        $newuser['realname']='*'.mb_substr($user->realname,1,mb_strlen($user->realname)-1,'utf8');
        return $this->render('xykaddcard', [
            'user' => $user,
            'newuser' => $newuser,
            'jsinfo' => $jsinfo,
            'csrf' => $this->getCsrf(),
        ]);
    }


    /**
     * 绑卡系统自有验证
     */
    public function actionSavecard() {
        $post_data = $this->get();
        $user = $this->getUser();
        if (empty($post_data['card'])) {
            echo $this->showMessage(1, '*请填写储蓄卡号', 'json');
            exit;
        }
        $card_num = str_replace(' ', '', $post_data['card']);
        //获取绑卡信息
        $cardbin = (new Card_bin())->getCardBinByCard($card_num, "prefix_length desc");
        //验证aiax提交过来的绑卡信息
        if ($this->get('type') == 'very') {
            //验证银行卡信息
            $this->chkCardNum($cardbin, $card_num, $user, $post_data['banktype']);
        } else {
            //验证通过后进行下一步
            if ($post_data['banktype'] == 1) {
                $card_type = "储蓄卡";
                $this->getView()->title = '储蓄卡认证';
            } elseif ($post_data['banktype'] == 2) {
                $card_type = "信用卡";
                $this->getView()->title = '信用卡认证';
            } elseif ($post_data['banktype'] == 3) {
                if ($cardbin['card_type'] == 0) {
                    $card_type = "储蓄卡";
                } elseif ($cardbin['card_type'] == 1) {
                    $card_type = "信用卡";
                }
                $this->getView()->title = '银行卡认证';
            }
            return $this->render('telconfirm', [
                        'user' => $user,
                        'post_data' => $post_data,
                        'orderinfo' => $this->get('orderinfo'),
                        'card_type' => $card_type,
                        'banktype' => $post_data['banktype'],
                        'bank_name' => $cardbin['bank_name'],
            ]);
        }
    }

    /**
     * 第三方验证（易宝、天行）
     */
    public function actionCodeconfirm() {
        $user = $this->getUser();
        $post_data = $this->get();
        $mobile = !empty($post_data['tel']) ? str_replace(' ', '', $post_data['tel']) : '';
        $user_id = !empty($post_data['user_id']) ? str_replace(' ', '', $post_data['user_id']) : '';
        $card = !empty($post_data['card']) ? str_replace(' ', '', $post_data['card']) : '';
        $realname = !empty($post_data['realname']) ? str_replace(' ', '', $post_data['realname']) : '';

        $banktype = $post_data['banktype'];
        if (Yii::$app->request->get('type') == 'very') {
            if (!$mobile) {
                return json_encode(array('code' => 1, 'message' => '*请填写手机号'));
                exit;
            } else {
                return json_encode(array('code' => 0, 'message' => ''));
                exit;
            }
        } else {
            $this->getView()->title = '银行卡认证';
            return $this->render('codeconfirm', [
                        'user_id' => $user_id,
                        'banktype' => $banktype,
                        'card' => $card,
                        'realname' => $realname,
                        'mobile' => $mobile,
                        'orderinfo' => urlencode($this->get('orderinfo')),
            ]);
        }
    }

    /**
     * 发送验证码
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
     * 绑卡
     */
    public function actionBindcard() {
        $post_data = $this->post();
        $user = $this->getUser();
        $banktype = $this->post("banktype");
        $flag = 'shop_addcard_';
        $shop_redis = (new User())->getShopRedisResult($flag,$user,2);
        $nextPage = $this->getNext($banktype);
        if (!$nextPage) {
            $resultArr = array('msg' => '');
            echo $this->showMessage(1, $resultArr, 'json');
            exit;
        }
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
        //重复提交绑定数据
        $counts = User_bank::find()->where(['card' => $bank_card, 'status' => 1])->all();
        if (count($counts) > 0) {
            $resultArr = array('nextPage' => $nextPage);
            echo $this->showMessage(0, $resultArr, 'json');
            exit;
        }
        //四要素健全验证
        $result = $this->getBankauth($user, $mobile, $bank_card);
        if ($result['res_code'] != '0000') {//未通过
            $re = $this->getTianxingError($result);
            $resultArr = array('msg' => $re);
            echo $this->showMessage(2, $resultArr, 'json');
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
            if($shop_redis){ //跳回商城
                $nextPage = $shop_redis;
            }
            $resultArr = array('nextPage' => $nextPage);
            echo $this->showMessage(0, $resultArr, 'json');
            exit;
        } else {
            $resultArr = array('ret' => '3');
            echo $this->showMessage(3, $resultArr, 'json');
            exit;
        }
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
//        echo 123;
//        die;
        if ($ownerCode != $code) {
            $resultArr = array('msg' => '');
            echo $this->showMessage(1, $resultArr, 'json');
            exit;
        }
        //获取卡片信息
        $cardbin = (new Card_bin())->getCardBinByCard($bank_card, "prefix_length desc");
        //重复提交绑定数据
        $counts = User_bank::find()->where(['card' => $bank_card, 'status' => 1])->all();
        if (count($counts) > 0) {
            $resultArr = array('msg' =>'重复提交绑定数据' );
            echo $this->showMessage(0, $resultArr, 'json');
            exit;
        }
        //四要素健全验证
        $result = $this->getBankauth($user, $mobile, $bank_card);
        if ($result['res_code'] != '0000') {//未通过
            $re = $this->getTianxingError($result);
            $resultArr = array('msg' => $re);
            echo $this->showMessage(2, $resultArr, 'json');
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
            $resultArr = array('msg'=>'成功');
            echo $this->showMessage(0, $resultArr, 'json');
            exit;
        } else {
            $resultArr = array('ret' => '3');
            echo $this->showMessage(3, $resultArr, 'json');
            exit;
        }
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
     * 获取天行数据
     * @param object $user
     * @param  string $mobile
     * @param string $cardno
     * @return bool
     */
    private function getTianxing($user, $mobile, $cardno) {
        //获取天行数据
        $postdata = array(
            'username' => $user->realname,
            'idcard' => $user->identity,
            'cardno' => $cardno,
            'phone' => $mobile
        );

        $openApi = new Apihttp;
        $result = $openApi->bankInfoValid($postdata);
        Logger::errorLog(print_r($result, true), 'Tianxing', 'tianXing');

        return $result;
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
     * 转换
     * @param int $identityid 用户id
     * @param int $cardno 卡号
     * @return int;
     */
    private function getPayIdentityid($identityid, $cardno) {
        if (!$identityid || !$cardno) {
            return '';
        }
        $card_top = substr($cardno, 0, 6);
        $card_last = substr($cardno, -4);
        $identityid = $identityid . '-' . $card_top . $card_last;
        return $identityid;
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
     * 验证输入的卡片是否合法
     */
    private function chkCardNum($cardbin, $card_num, $user, $banktype = 1) {
        $nextPage = $this->getNext($banktype);
        if (!$nextPage) {
            echo $this->showMessage(1, '*绑卡失败!', 'json');
            exit;
        }
        //@TODO 对$carbin['card_type']做检验 只支持0和1  ok
        //不支持的卡片
        $card_type_arr = [0, 1];
        if (empty($cardbin) || !in_array($cardbin['card_type'], $card_type_arr)) {
            echo $this->showMessage(1, '*此卡不支持!', 'json');
            exit;
        }
        //卡片不是储蓄卡
        $bank_array = Keywords::getBankAbbr();
        if ($banktype == 1) {
            if ($cardbin['card_type'] != 0) {
                echo $this->showMessage(1, '*请输入正确的储蓄卡号!', 'json');
                exit;
            }
        } elseif ($banktype == 2) {
            if ($cardbin['card_type'] == 0) {
                echo $this->showMessage(1, '*请输入正确的信用卡号!', 'json');
                exit;
            }
        }
        //卡片不支持
        if (!in_array($cardbin['bank_abbr'], $bank_array[$cardbin['card_type']])) {
            echo $this->showMessage(1, '*该储存卡卡片类型不支持!', 'json');
            exit;
        }
        //此卡片已经为绑定状态
        $counts = User_bank::find()->where(['card' => $card_num, 'status' => 1])->all();
        if (count($counts) > 0) {
            echo $this->showMessage(2, '*该卡已经被绑定!', 'json');
            exit;
        }
        //此卡片被当前用户添加过
        $count = User_bank::find()->where(['user_id' => $user->user_id, 'card' => $card_num])->one();
        //没有此卡，进行下一步
        if (!$count) {
            echo $this->showMessage(3, '', 'json');
            exit;
        }
        //有且为绑定状态
        if ($count->status == 1) {
            echo $this->showMessage(2, '*该卡已经被绑定!', 'json');
            exit;
        }
        //有且为解绑状态
        $condition = array("status" => 1);
        $up_res = $count->updateUserBank($condition);
        if ($up_res) {
            echo json_encode(array('code' => 0, 'message' => '*绑卡成功', 'nextPage' => $nextPage));
            exit;
        }
        echo $this->showMessage(1, '*绑卡失败!', 'json');
        exit;
    }

    /**
     * 获取下一步跳转页面
     */
    private function getNext($banktype) {
        $orderJson = $this->get("orderinfo");
        if (!$orderJson) {
            return false;
        }
        if ($banktype == 2) {
            $nextPage = $this->getNextpage($orderJson, 13);
            Logger::errorLog(print_r("13" . $nextPage, true), 'log', 'banktype');
        } else if ($banktype == 1) {
            $nextPage = $this->getNextpage($orderJson, 12);
            Logger::errorLog(print_r("12" . $nextPage, true), 'log', 'banktype');
        } else if ($banktype == 3) {
            $nextPage = $this->getNextpage($orderJson, 6);
            Logger::errorLog(print_r("6" . $nextPage, true), 'log', 'banktype');
        }

        return $nextPage . '?orderinfo=' . urlencode($orderJson);
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
     * 存管个人详情页
     * @return string
     */
    public function actionCgdetail(){
        $userId = $this->get("user_id");
        $this->getView()->title = "存管账户";
        $this->layout = '_cgdetail';
        $userInfo = User::findOne($userId);
        $isCg = (New Payaccount())->isCunguan($userId,1);
        if(empty($isCg)){
            echo "暂无有效信息";exit();
        }
        if(empty($isCg['isOpen'])){
            echo "暂无有效开户信息";exit();
        }
//        print_r($isCg);die;
        $openInfo = (New Payaccount())->getPaystatusByUserId($userId,2,1);
        $cardInfo = "";
        if($isCg['isCard'] != 0){
            $cardInfo = User_bank::findOne($openInfo->card);
        }
        $authStatus = (New Payaccount())->getAuthStatus($userId);
        return $this->render('cgdetail', [
            'isCg' => $isCg,
            'openInfo' => $openInfo,
            'cardInfo' => $cardInfo,
            'userInfo' => $userInfo,
            'authStatus' => $authStatus,
            'csrf' => $this->getCsrf(),
            ]);
    }

    /**
     * 修改密码 新
     */
    public function actionEditpwdnew() {
        $userId = $this->get("user_id","");
        $userInfo = User::findOne($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1000', 'res_msg' => '用户信息获取失败'];
            echo json_encode($arr);
            exit;
        }
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isAccount) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户开户获取失败'];
            echo json_encode($arr);
            exit;
        }
        $isPass = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if (!$isPass) {
            $arr = ['res_code' => '1002', 'res_msg' => '用户设置密码信息获取失败'];
            echo json_encode($arr);
            exit;
        }
        $apiDep = new Apidepository();
        $notifyUrl = Yii::$app->request->hostInfo . '/new/getunbindcardnotify/editbank';
        $params = [
            'channel' => '000002',
            'isUrl' => 1,
            'from' => 1,
            'accountId' => $isAccount->accountId,
            'name' => $userInfo->realname,
            'retUrl' => Yii::$app->request->hostInfo . '/new/bank/cgdetail?user_id='.$userInfo->user_id, //前台跳转链接
            'notifyUrl' => $notifyUrl, //后台通知链接
            'acqRes' => strval($userInfo->user_id),
        ];
        $ret_set = $apiDep->cgupdatepwd($params);
        $ret_set = json_decode($ret_set, true);
        if (!empty($ret_set) && $ret_set['res_code'] != 0) {
            $arr = ['res_code' => '1001', 'res_msg' => '修改密码失败'];
            echo json_encode($arr);
            exit;
        } else {
            return $this->redirect($ret_set['res_data']);
        }
    }

    private function depositoryoverbind($userInfo, $payaccount, $userbank,$type_source) {
        $come_from = 11; //解卡 
        $notifyUrl = Yii::$app->request->hostInfo . '/new/getunbindcardnotify';
        if( $type_source == 1 ){
            $come_from = 13; //跳回借款首页
            $notifyUrl = Yii::$app->request->hostInfo . '/new/getunbindcardnotify?type=1';
        }
        $condition = [
            'channel' => '000002',
            'from' => 1,
            'isUrl' => 1 ,
            'idType' => '01',
            'order_id' => date('YmdHis') . rand(1000, 9999),  //订单号 唯一标识
            'accountId' => $payaccount->accountId,
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $userbank->card,
            'retUrl' => Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=' . $come_from . '&user_id=' . $userInfo->user_id, //前台跳转链接
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&type=9' , //忘记密码跳转
            'notifyUrl' => $notifyUrl, //后台通知链接
            'acqRes' => "$userInfo->user_id",
        ];
        $deposiApi = new Apidepository();
        $result = json_decode( $deposiApi->cgOvercard($condition),true );
         if ( $result['res_code'] != 0 ) {
            if( isset($result['rsp_data'])){
                Logger::dayLog('bank', $result['rsp_data'], 'user_id->' . $userInfo->user_id);
            } 
            if( isset($result['rsp_msg'])){
                Logger::dayLog('bank', $result['rsp_msg'], 'user_id->' . $userInfo->user_id);
            }
            return false;
        }
        return $result;
    }

    private function sendBindCode($user, $userbank) {
        $condition = [
            'channel' => '000002',
            'mobile' => $user->mobile,
            'from' => 1,
            'reqType' => strval(1),
            'srvTxCode' => 'cardBindPlus',
            'cardNo' => $userbank->card,
        ];
        $depositoryApi = new Apidepository();
        $result = $depositoryApi->sendmsg($condition);
        return $result;
    }


}

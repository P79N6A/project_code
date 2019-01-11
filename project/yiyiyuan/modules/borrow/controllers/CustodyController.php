<?php
namespace app\modules\borrow\controllers;

use app\commonapi\Apidepository;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\PayAccountExtend;
use app\models\news\PayAccountError;
use app\models\news\User_loan;
use app\models\news\User;
use app\models\news\Cg_remit;
use app\models\news\User_bank;
use app\models\news\CardLimit;
use Yii;

class CustodyController extends BorrowController {
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /**
     * 存管列表
     */
    public function actionList() {
        $this->layout = "custody/custody";
        $list_type = $this->get('list_type', 0); //0:开户  1：续期
        $source_mark = $this->get('source_mark',0); //1：从商城来的
        $title = '开户操作';
        if ($list_type == 1) {
            $title = '续期';
        }
        $this->getView()->title = $title;
        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        if (empty($user_id)) {
            exit("获取用户信息失败");
        }
        if( $source_mark == 1 ){ //从先花商城跳转过来的
            Yii::$app->redis->setex('shop_cunguan_'.$user_id,86400,$user_id);
        }
        $type = $this->get('type', 0);
        $type = empty($type) ? $this->getRedis('renew_type_' . $user_id) : $type;
        if ($type == 10) {
            $this->setRedis('renew_type_' . $user_id, $type);
        }
        $userModel = new User();
        $oUserInfo = $userModel->getUserinfoByUserId($user_id);
        if (empty($oUserInfo)) {
            exit("获取用户信息失败");
        }
        $isCungan = (new Payaccount())->isCunguan($oUserInfo->user_id);
        //四合一授权是否过期 0 未过期 1过期
        $authIsTimeOut = 0;
        $isAuth = (new Payaccount())->getPaysuccessByUserId($oUserInfo->user_id, 2, 6);
        if(!empty($isAuth)){
            $o_pay_account_extend = (new PayAccountExtend())->getByUserIdAndStep($oUserInfo->user_id, 6);
            $o_pay_account_extend_result = !empty($o_pay_account_extend) ? $o_pay_account_extend->getLegal(1) : 0;
            if(!$o_pay_account_extend_result){
                $authIsTimeOut = 1;
            }
        }
        $isCungan['password_list'] = 0; //正顺序 开户(绑卡)->设置密码
        if (in_array(0, $isCungan)) {
            if ($isCungan['isOpen'] == 1 && $isCungan['isCard'] != 1 && ($isCungan['isPass'] != 1 || $isCungan['isPass'] == 1)) {
                $isCungan['password_list'] = 1; //反顺序 设置密码->开户(绑卡)
            }
        }

        $o_pay_account_error = (new PayAccountError())->getByUserIdAndType($user_id, 1);
        $o_pay_account_auth_error = (new PayAccountError())->getError($user_id, 3, 2);
        $isCungan['auth_error'] = 0;
        if (!empty($o_pay_account_auth_error)) {
            if ($o_pay_account_auth_error->status == 0) {
                $o_pay_account_auth_error->updateStatusSuccess();
                $isCungan['auth_error'] = 1;
            }
        }
        $shop_reback_url = (new User())->getShopRedisResult('shop_cunguan_',$oUserInfo,2); //获取商城链接
        $cardLimt = CardLimit::find()->where(['type'=>5,'status'=>2])->all();
        $isCungan['is_open_new'] = !empty($o_pay_account_error) ? 1 : 0;//1新开户成功
        $isCungan['user_id'] = $oUserInfo->user_id;
        $isCungan['type'] = $type;
        $isCungan['csrf'] = $this->getCsrf();
        $isCungan['list_type'] = $list_type;
        $isCungan['cardLimt'] = $cardLimt;
        $isCungan['shop_reback_url'] = $shop_reback_url;
        $isCungan['authIsTimeOut'] = $authIsTimeOut;

        return $this->render('list', $isCungan);
    }

    /**
     * 获取csrf
     * @return string
     */
    public function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 开户。
     */
    public function actionNewopenwx() {
        $userId = $this->post('user_id');
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
            echo json_encode($arr);
            exit;
        }
        $type = $this->get('type', 0);
        $type = empty($type) ? $this->getRedis('renew_type_' . $userId) : 0;
        if ($type == 10) {
            $this->setRedis('renew_type_' . $userId, $type);
        }
        $ret_open = $this->newopen($userInfo);
        if (!$ret_open) {
            $arr = ['res_code' => '1001', 'res_msg' => '开户失败'];
            echo json_encode($arr);
            exit;
        }

        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_open['res_data']];
        echo json_encode($arr);
        exit;
    }

    /**
     * 绑卡
     */
    public function actionNewbankwx() {
        $userId = $this->post('user_id');
        $type = $this->post('come_from', 6);
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
            echo json_encode($arr);
            exit;
        }
        $ret_open = $this->newbank($userInfo, $type);
        if (!$ret_open) {
            $arr = ['res_code' => '1001', 'res_msg' => '绑卡请求失败'];
            echo json_encode($arr);
            exit;
        }

        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_open];
        echo json_encode($arr);
        exit;
    }

    /**
     * 页面跳转存管开户
     * @param $userInfo
     * @return bool
     */
    private function newopen($userInfo) {
        $apiDep = new Apidepository();
        $idcard_sex = $this->indentify_sex($userInfo->identity);
        $params = [
            'channel' => '000002', //交易渠道
            'isUrl' => '1',
            'from' => '1',
            'idType' => '01',
            'name' => $userInfo->realname,
            'gender' => $idcard_sex,
            'mobile' => $userInfo->mobile,
            'email' => $userInfo->extend->email,
            'acctUse' => '00000',
            'smsFlag' => '0', //0不需要; 1需要
            'identity' => '2', //1：出借角色2：借款角色3：代偿角色
            'coinstName' => '先花一亿元',
            'retUrl' => Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=1&user_id=' . $userInfo->user_id,
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getopennotify',
            'acqRes' => "$userInfo->user_id",
        ];
        $ret_open = json_decode($apiDep->cgkh($params), true);
        $payAccount = new Payaccount();
        $condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 1,
            'activate_result' => 0,
        ];

        if ($ret_open['res_code'] != 0) {
            if (isset($ret_open['rsp_data'])) {
                Logger::dayLog('bank', $ret_open['rsp_data'], 'user_id->' . $userInfo->user_id);
            }
            if (isset($ret_open['rsp_msg'])) {
                Logger::dayLog('bank', $ret_open['rsp_msg'], 'user_id->' . $userInfo->user_id);
            }
            return false;
        }
        $addRes = $payAccount->add_list($condition);
        if (!$addRes) {
            Logger::dayLog('custody', 'pay_account表操作失败', 'user_id->' . $userInfo->user_id);
            return false;
        }

        return $ret_open;
    }

    /**
     * 根据身份证号区别性别
     * @param type $idcard
     * @return string
     */
    private function indentify_sex($idcard) {
        if (empty($idcard)) {
            return '';
        }
        $sexint = (int)substr($idcard, 16, 1);
        return $sexint % 2 === 0 ? 'F' : 'M';
    }

    private function newbank($userInfo, $type) {
        $apiDep = new Apidepository();
        $payAccountModel = new Payaccount();
        $payAccountObj = $payAccountModel->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!empty($payAccountObj) && !empty($payAccountObj->card)) {
            return FALSE;
        }
        $pass_type = $type == 7 ? 9 : 2;
        $postData = [
            'idType' => '01', //证件类型
            'idNo' => $userInfo->identity, //证件号码
            'name' => $userInfo->realname, //姓名
            'accountId' => $payAccountObj->accountId, //电子账号
            'userIP' => Common::get_client_ip(), //客户IP
            'retUrl' => Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=' . $type . '&user_id=' . $userInfo->user_id, //前台跳转链接
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&from=weixin&type=' . $pass_type, //忘记密码跳转
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getbindbanknotify', //后台通知链接
            'isUrl' => '1', //选择获得url地址
            'from' => '1', //来源
            'channel' => '000002', //交易渠道
            'order_id' => date('YmdHis') . $userInfo->user_id . rand(1000, 9999), //订单号
        ];
        $ret_open = $apiDep->bindpage($postData);
        return $ret_open;
    }

    /**
     * 授权
     */
    public function actionAuthorize() {
        $userId = $this->post('user_id');
        $type = $this->post('type');
        $isRepay = $this->post('is_repay', 1); //调用 1借款调用 2还款调用
        if ($type == 2) {
            $step = 4; //还款授权
        } elseif ($type == 1) {
            $step = 5;  //缴费授权
        }
        $userInfo = User::findOne($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
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
        $add_condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => $step,
            'accountId' => $isAccount->accountId,
        ];
        $add_res = $payAccount->add_list($add_condition);
        if (!$add_res) {
            $arr = ['res_code' => '1001', 'res_msg' => '添加信息失败'];
            echo json_encode($arr);
            exit;
        }
        $apiDep = new Apidepository();
        $deadline = date("Ymd", time() + 365 * 5 * 24 * 60 * 60);
        //调用 1借款调用 2还款调用
        $retUrl = Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=' . $step . '&user_id=' . $userInfo->user_id;
        if ($isRepay == 2) {
            $retUrl = Yii::$app->request->hostInfo . '/new/depositorynew/waiting?type=' . $step . '&user_id=' . $userInfo->user_id;
        }
        $params = [
            'from' => 1,
            'channel' => '000002',
            'orderId' => date('YmdHis') . rand(1000, 9999),
            'type' => $type, //1缴费授权；2还款授权
            'accountId' => $isAccount->accountId,
            'maxAmt' => 50000,
            'deadline' => $deadline,
            'retUrl' => $retUrl,
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&from=auth',
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getauthorizenotify?step=4',
            'isUrl' => '1',
        ];
        $ret_set = $apiDep->authorize($params);
        if (!$ret_set) {
            $arr = ['res_code' => '1001', 'res_msg' => '授权失败'];
            echo json_encode($arr);
            exit;
        }
        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_set];
        echo json_encode($arr);
        exit;
    }

    /**
     * 设置密码
     */
    public function actionSetpwd() {
        $userId = $this->post('user_id');
        $type = $this->post('type', 2);
        $userInfo = User::findOne($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
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
        $add_condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 2,
            'accountId' => $isAccount->accountId,
        ];
        $add_res = $payAccount->add_list($add_condition);
        if (!$add_res) {
            $arr = ['res_code' => '1001', 'res_msg' => '添加信息失败'];
            echo json_encode($arr);
            exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'from' => 1,
            'channel' => '000002',
            'accountId' => $isAccount->accountId,
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            //'retUrl' => Yii::$app->request->hostInfo . '/borrow/custody/list?type=2&user_id=' . $userInfo->user_id,
            'retUrl' => Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=' . $type . '&user_id=' . $userInfo->user_id,
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getsetpassnotify',
            'isPage' => '1',
            'isUrl' => '1',
        ];
        $ret_set = $apiDep->pwdset($params);

        if (!$ret_set) {
            $arr = ['res_code' => '1001', 'res_msg' => '设置密码失败'];
            echo json_encode($arr);
            exit;
        }
        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_set];
        echo json_encode($arr);
        exit;
    }

    /**
     * 等待页
     * @return type
     */
    public function actionWaiting() {
        $this->layout = "custody/custody";
        $custody_type = $this->get('type');
        $this->getView()->title = '等待中';
        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        $userModel = new User();
        $oUserInfo = $userModel->getUserinfoByUserId($user_id);
        if (empty($oUserInfo)) {
            exit("获取用户信息失败");
        }
        $renew_type = 0;
        if ($custody_type == 5) {
            $renew_type = $this->getRedis('renew_type_' . $oUserInfo->user_id);
            $this->delRedis('renew_type_' . $oUserInfo->user_id);
        }
        $all = (new Payaccount())->isCunguan($oUserInfo->user_id, 1);
        $cg_whole = 1;
        if (in_array(0, $all)) {
            $cg_whole = 0;
        }
        return $this->render('waiting', [
            'csrf' => $this->getCsrf(),
            'user_id' => $user_id,
            'type' => $custody_type,
            'renew_type' => $renew_type,
            'cg_whole' => $cg_whole,
        ]);
    }

    /**
     * 等待页之后的跳转页面ajax
     */
    public function actionGetnextpage() {
        $user_id = empty($this->getUser()) ? $this->post('user_id') : $this->getUser()->user_id;
        $csrf = $this->post('csrf');
        $step = $this->post('type'); //  1开户 2绑卡 3设置密码 4还款 5缴费
        $userModel = new User();
        $oUserInfo = $userModel->getUserinfoByUserId($user_id);
        if (empty($oUserInfo)) {
            $result['rsp_code'] = '1000';
            $result['rsp_url'] = '';
            $result['rsp_msg'] = '获取用户信息失败';
        }
        $isCungan = (new Payaccount())->isCunguan($oUserInfo->user_id);
        if (in_array(0, $isCungan)) {
            $result['rsp_code'] = '0000';
            $result['rsp_url'] = 'list';
            $result['rsp_msg'] = '';
        } else {
            $result['rsp_code'] = '0000';
            $result['rsp_url'] = 'loan';
            $result['rsp_msg'] = '';
        }

        return json_encode($result);
    }

    /**
     * 开放平台提现
     * @return mixed
     */
    public function actionGetmoneyopen() {
        $loan_id = $this->get('loan_id');
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $loanInfo = User_loan::findOne($loan_id);
        $settle_amount = $loanInfo->getActualAmount($loanInfo->is_calculation, $loanInfo->amount, $loanInfo->withdraw_fee);
        $cgRemitModel = new Cg_remit();
        $cgRemit = $cgRemitModel->getByLoanId($loan_id);
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isAccount) {
            $resultArr = array('ret' => '3', 'msg' => "您未开户");
            return json_encode($resultArr);
        }
        $isPassword = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if (!$isPassword) {
            $resultArr = array('ret' => '4', 'msg' => "您未设置密码");
            return json_encode($resultArr);
        }
        if ($cgRemit->remit_status != 'WILLREMIT') {
            $resultArr = array('ret' => '5', 'msg' => "您正在提现中");
            return json_encode($resultArr);
        }
        $remitting = $cgRemit->doremit();
        if (!$remitting) {
            $resultArr = array('ret' => '6', 'msg' => "提现数据更新失败");
            return json_encode($resultArr);
        }
        $card = User_bank::findOne($isAccount->card);
        $apiDep = new Apidepository();
        $params = [
            'loan_id' => $loan_id,
            'comefrom' => 1,
            'request_no' => date('YmdHis') . rand(1000, 9999),
            'account_id' => $isAccount->accountId,
            'identity' => $userInfo->identity,
            'username' => $userInfo->realname,
            'card_no' => $card->card,
            'mobile' => $userInfo->mobile,
            'withdraw_money' => (string)round($settle_amount, 2),
            'withdraw_fee' => $loanInfo->is_calculation == 1 ? (string)round($loanInfo->withdraw_fee, 2) : '0',
            'forgot_pwdurl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&from=weixin',
            'ret_url' => Yii::$app->request->hostInfo . '/borrow/loan?loan_id=' . $loan_id,
//            'isUrl' => 1,
        ];
        $params['isUrl'] = 1;
        Logger::errorLog(print_r($params, true), 'moneyoutopen_post', 'depository');
        $ret_set = $apiDep->moneyoutopen($params);
        if (!$ret_set || $ret_set['rsp_code'] != 0) {
            $cgRemit->willRemit();
            $resultArr = array('ret' => '2', 'msg' => "提现失败");
            return json_encode($resultArr);
        }
        $resultArr = array('ret' => '0000', 'msg' => $ret_set['rsp_msg']);
        return json_encode($resultArr);
        //return $this->redirect($ret_set['rsp_msg']);
    }

    /**
     * 四合一授权
     */
    public function actionAuthforinone() {
        $userId = $this->post('user_id');
        $isRepay = $this->post('is_repay', 1); //调用 1借款调用 2还款调用
        $channel = $this->post('channel', '000003');
        $step = 6; //四合一授权

        $userInfo = User::findOne($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
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
        $add_condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 6,
            'accountId' => $isAccount->accountId,
        ];
        $add_res = $payAccount->add_list($add_condition);
        if (!$add_res) {
            $arr = ['res_code' => '1001', 'res_msg' => '添加信息失败'];
            echo json_encode($arr);
            exit;
        }
        $apiDep = new Apidepository();
        $deadline = date("Ymd", time() + 365 * 5 * 24 * 60 * 60);
        //调用 1借款调用 2还款调用

        $retUrl = Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=12&user_id=' . $userInfo->user_id;
        if ($isRepay == 2) {
            $retUrl = Yii::$app->request->hostInfo . '/new/depositorynew/waiting?type=12&user_id=' . $userInfo->user_id;
        }
        // 四合一参数
        $params = [
            'channel' => '000002',//交易渠道 000001手机APP  000002网页  000003微信 000004柜面
            'isUrl' => '1',//返回URL 参数不传返回form
            'from' => '1',//操作来源 1 一亿元 2 花生米
            'orderId' => date('YmdHis') . rand(1000, 9999),//订单号
            'accountId' => $isAccount->accountId,//电子账户
            'name' => $userInfo->realname, //姓名
            'idNo' => $userInfo->identity,//身份证号码
            'identity' => 2,//身份属性角色 2：借款角色  3代偿角色
            'paymentAuth' => '1',//开通缴费授权功能标志 0 取消 1开通 空不操作 非必填
            'repayAuth' => '1',//开通还款授权功能标识 0 取消 1开通 空不操作 非必填
            'paymentMaxAmt' => 50000,//缴费授权签约最高金额 签约时必送
            'paymentDeadline' => $deadline,//缴费授权签约到期日
            'repayMaxAmt' => 50000,//还款授权签约最高金额 签约时必送
            'repayDeadline' => $deadline,//还款授权签约到期日
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&from=auth',//忘记密码跳转链接 @TODO
            'retUrl' => $retUrl,//返回交易页面链接
            'successfulUrl' => '',//交易成功跳转链接
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getauthfoureinonenotify',//后台响应链接
        ];
        $ret_set = $apiDep->cgauth($params);
        $ret_set = json_decode($ret_set, true);
        // print_r($ret_set);die;
        if (!empty($ret_set) && $ret_set['res_code'] != 0) {
            $arr = ['res_code' => '1001', 'res_msg' => '授权失败'];
            echo json_encode($arr);
            exit;
        }
        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_set['res_data']];
        echo json_encode($arr);
        exit;
    }

    /**
     * 设置/重置密码 新
     */
    public function actionSetpwdnew() {
        if ($this->isPost()) {
            $userId = $this->post('user_id', '');
            $type = $this->post('type', 2);
        } else {
            $userId = $this->get('userid', '');
            $type = $this->get('type', 2);
        }
        $pwType = $this->post('pwType', 2); //调用 1设置密码 2重置密码
        $userInfo = User::findOne($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
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
        $add_condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 2,
            'accountId' => $isAccount->accountId,
        ];
        $add_res = $payAccount->add_list($add_condition);
        if (!$add_res) {
            $arr = ['res_code' => '1001', 'res_msg' => '添加信息失败'];
            echo json_encode($arr);
            exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'from' => 1,
            'channel' => '000002',
            'accountId' => $isAccount->accountId,
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'retUrl' => Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=' . $type . '&user_id=' . $userInfo->user_id,
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getnewsetpassnotify',
            'isPage' => '1',
            'isUrl' => '1',
            'type' => $pwType,
        ];
        $ret_set = $apiDep->cgrestpwd($params);
        $ret_set = json_decode($ret_set, true);
        if (!empty($ret_set) && $ret_set['res_code'] != 0) {
            $arr = ['res_code' => '1001', 'res_msg' => '设置密码失败'];
            echo json_encode($arr);
            exit;
        }
        if ($this->isPost()) {
            $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_set['res_data']];
            echo json_encode($arr);
            exit;
        } else {
            return $this->redirect($ret_set['res_data']);
        }
    }

    /**
     * 存管结果返回
     * @author 王新龙 
     * type:13:解卡成功跳到存管列表页 失败跳借款首页
     * @date 2018/10/11 11:44
     */
    public function actionGetresult() {
        $userId = $this->post('user_id');
        $type = $this->post('type'); //1：开户 2：设置密码  6：绑卡 7:绑卡且跳转至银行卡列表页 8:绑卡且跳回存管列表页 9:设置密码且跳转至银行卡列表页 10：未知跳转 11：解卡跳回银行卡列表页 12:四合一授权跳回借款首页 13：解卡跳回借款首页
        $o_user = (new User())->getUserinfoByUserId($userId);
        if (!$o_user) {
            $arr = ['res_code' => '1001', 'res_msg' => '跳转至存管列表页'];
            echo json_encode($arr);
            exit;
        }
        $step = $this->getCunguanStep($type);
        if (!in_array($step, [1, 2, 6])) {
            exit(json_encode(['res_code' => '1002', 'res_msg' => '跳转至存管列表页']));
        }
        $o_payaccount = (new Payaccount())->getPaysuccessByUserId($o_user->user_id, 2, $step);
        if (in_array($type, [11, 13])) {
            $default_bank = User_bank::find()->where(['user_id' => $o_user->user_id, 'default_bank' => 1])->one();
            if (!empty($o_payaccount) && (!empty($o_payaccount->card) || !empty($default_bank))) {
                exit(json_encode(['res_code' => '0000', 'res_msg' => '跳转至结果页']));
            }
        }
        $error_type = $this->getCunguanStep($type, 1);
        $end_time = date('Y-m-d H:i:s');
        $start_time = date('Y-m-d H:i:s', strtotime('-1 minutes'));
        $where = [
            'AND',
            ["BETWEEN", "create_time", $start_time, $end_time],
            ['user_id' => $o_user->user_id],
            ['type' => $error_type],
        ];
        $count = (new PayAccountError())->find()->where($where)->count();
        if ($count > 0) {
            exit(json_encode(['res_code' => '0000', 'res_msg' => '跳转至结果页']));
        }
        exit(json_encode(['res_code' => '1002', 'res_msg' => '跳转至存管列表页']));
    }

    /**
     * 存管操作结果页
     * @return string
     * @author 王新龙
     * @date 2018/10/11 11:44
     */
    public function actionShowinfo() {
        $this->layout = "custody/showinfo";
        $this->getView()->title = '操作结果';
        $type = $this->get('type', '');
        $user_id = $this->get('user_id', '');
        $o_user = (new User())->getById($user_id);
        if (empty($o_user) || empty($type)) {
            exit(json_encode(['res_code' => '1001', 'res_msg' => '用户信息错误']));
        }
        $step = $this->getCunguanStep($type);
        if (!in_array($step, [1, 2, 6])) {
            exit(json_encode(['res_code' => '1002', 'res_msg' => '参数错误']));
        }
        $o_payaccount = (new Payaccount())->getPaysuccessByUserId($o_user->user_id, 2, $step);
        $error_type = $this->getCunguanStep($type, 1);
        $o_payaccount_error = (new PayAccountError())->getLastError($o_user->user_id, $error_type);
        //判断是否跳回先花商城
        $shop_result = (new User())->getShopRedisResult('shop_cunguan_',$o_user,2);
        //绑卡成功逻辑
        if (in_array($type, [6, 7, 8])) {
            if (!empty($o_payaccount) && !empty($o_payaccount->card)) {
                $array = [
                    'is_success' => 1,
                    'is_step' => $error_type,
                    'go_where' => $this->getGoWhere($type,$shop_result),
                    'user_id' => $user_id,
                    'csrf' => $this->getCsrf(),
                    'shop_url' =>$shop_result
                        
                ];
                return $this->render('showinfo', $array);
            }
        }
        //解绑卡成功逻辑
        if (in_array($type, [11, 13])) {
            $default_bank = User_bank::find()->where(['user_id' => $o_user->user_id, 'default_bank' => 1])->one();
            if (!empty($o_payaccount) && empty($o_payaccount->card) && empty($default_bank)) {
                $array = [
                    'is_success' => 1,
                    'is_step' => $error_type,
                    'go_where' => $this->getGoWhere($type,$shop_result),
                    'user_id' => $user_id,
                    'csrf' => $this->getCsrf(),
                    'shop_url' =>$shop_result
                ];
                return $this->render('showinfo', $array);
            }
        }
        //其他成功逻辑
        if (!in_array($type, [6, 7, 8, 11, 13]) && !empty($o_payaccount)) {
            $array = [
                'is_success' => 1,
                'is_step' => $error_type,
                'go_where' => $this->getGoWhere($type,$shop_result),
                'user_id' => $user_id,
                'csrf' => $this->getCsrf(),
                'shop_url' =>$shop_result
            ];
            return $this->render('showinfo', $array);
        }
        $array = [
            'is_success' => 2,
            'is_step' => $error_type,
            'go_where' => $this->getGoWhere($type,$shop_result,2),
            'user_id' => $user_id,
            'error_msg' => $this->getRetMsg($error_type, isset($o_payaccount_error->res_code) ? $o_payaccount_error->res_code : 0),
            'csrf' => $this->getCsrf(),
            'shop_url' =>$shop_result
        ];
        return $this->render('showinfo', $array);
    }

    /**
     * 根据操作来源，获取步骤
     * @param $type
     * @param int $is_error
     * @return int
     * @author 王新龙
     * @date 2018/10/11 12:07
     */
    private function getCunguanStep($type, $is_error = 0) {
        $step = $type;
        if (in_array($type, [6, 7, 8, 11, 13])) { //6：绑卡 7:绑卡且跳转至银行卡列表页  8:绑卡且跳回列表页
            $step = 1;
        }
        if (in_array($type, [9])) {
            $step = 2;
        }
        if (in_array($type, [12])) {
            $step = 6;
        }
        if ($is_error != 0) {
            $error_type = $step == 6 ? 3 : $step;//6=》3
            $error_type = in_array($type, [11, 13]) ? 4 : $error_type;//解绑卡
            $step = in_array($type, [6, 7, 8]) ? 7 : $error_type;//绑卡
        }
        return $step;
    }

    /**
     * 存管操作，去哪里
     * @param $type 操作来源
     * @param int $is_success 1成功 2失败
     * @return int
     * @author 王新龙
     * @date 2018/10/11 12:05
     */
    private function getGoWhere($type,$shop_result,$is_success = 1) {
        //1：开户 2：设置密码  6：绑卡 7:绑卡且跳转至银行卡列表页 8:绑卡且跳回存管列表页 9:设置密码且跳转至银行卡列表页 10：未知跳转 11：解卡跳回银行卡列表页 12:四合一授权跳回借款首页 13：解卡跳回借款首页
        $go_where = 1;//1首页 2存管列表页 3银行列表页
        if (in_array($type, [12])) {
            $go_where = 1;
        }
        if (in_array($type, [1, 2, 6, 8, 10,13])) {
            $go_where = 2;
        }
        if (in_array($type, [7, 9, 11])) {
            $go_where = 3;
        }
        //操作失败时候，跳转至存管列表页
        if ($is_success != 1) {
            if($go_where == 1){
               return $go_where = 2;
            }
            if($type == 13){
               return $go_where = 1;
            }
        }
        
        if($shop_result && $go_where==1){
            $go_where = 4; //跳回先花商城
        }
        return $go_where;
    }


    /**
     * 存管错误消息
     * @param $type 操作来源
     * @param $code 错误code
     * @return mixed|string
     * @author 王新龙
     * @date 2018/10/11 12:06
     */
    private function getRetMsg($type, $code) {
        $open_error = [
            '1' => '存管身份证号与本人不一致',
            'TB000028' => '银行卡信息与您的个人信息不匹配，请使用您本人银行卡开户',
            'JX900650' => '短信验证码错误，请重新输入',
            'CE999042' => '短信验证码错误，请重新输入',
            'TBEE9996' => '交易失败，请联系客服',
            'CE999045' => '您输入的卡号与短信验证码卡号不一致，请重新输入您本人银行卡号进行绑定',
            'CE999040' => '您的短信验证码已失效，短信验证码有效期90s，请重新获取',
            'CI61' => '您输入的银行卡为无效银行卡，请换卡重试或联系发卡行',
            'TB000530' => '您的银行卡状态异常，无法完成交易，请换卡重试或联系发卡行',
            'CE999064' => '银行卡交易受限，请更换卡片重试',
            'TB000040' => '银行正在维护中，请稍后重试',
            'JX900014' => '重复提交，温馨提示：等待过程中请勿刷新页面，以免重复提交',
            'TBEE9999' => '银行系统错误，请稍后重试',
            'TBCT9902' => '交易超时，请重试',
            'CI68' => '您的银行卡暂不支持该业务，请向您的银行或致电95516咨询',
            'JX900012' => '验证码输入错误，请重新输入',
            'CI77' => '您的银行卡未开通认证支付，请联系发卡行开通后重试或换卡重试',
            'CI66' => '银行卡信息与您的个人信息不匹配，请使用您本人银行卡开户',
            'CT9905' => '系统维护中，请稍后重试',
            'TBEE1022' => '交易失败，请更换银行卡重试',
            'CA110749' => '您已开通存管账户，请继续后续操作'
        ];
        $password_error = [
            'JX900024' => '您已成功设置密码，请勿重复操作',
            'JX900014' => '您已成功设置密码，请勿重复操作',
            'CT990300' => '交易超时，请稍后重试',
            'JX900664' => '访问人数过多，请稍后重试'
        ];
        $auth_error = [
            '2' => '授权时间或金额错误',
            'JX900045' => '您已完成授权，请继续下一步操作',
            'CA100976' => '您今日密码错误次数已达3次，请明日重试',
            'CA003811' => '密码错误，请确认后重试。温馨提示：密码每日连续错误次数上限为3次，超限后今日无法申请',
            'JX900014' => '重复提交，温馨提示：等待过程中请勿刷新页面，以免重复提交',
            'JX900101' => '账户不存在，请联系客服',
            'CT990300' => '交易超时，请稍后重试',
            'CA110836' => '您已完成还款授权，请勿重复授权',
            'JX900664' => '访问人数过多，请稍后重试'
        ];
        switch ($type) {
            case 1:
                if (isset($open_error[$code])) {
                    return $open_error[$code];
                }
                return '开户失败请重试';
                break;
            case 2:
                if (isset($password_error[$code])) {
                    return $password_error[$code];
                }
                return '密码设置失败，请重试';
                break;
            case 3:
                if (isset($auth_error[$code])) {
                    return $auth_error[$code];
                }
                return '授权失败，请重试';
                break;
            default:
                return '操作失败，请重试';
                break;
        }
    }
}

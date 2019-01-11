<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apidepository;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\PayAccountError;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_bank;
use Yii;

class DepositorynewController extends NewdevController
{

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        $userId = $this->get('user_id');
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            exit("获取用户信息失败");
        }
        $payAccount = new Payaccount();
        //判断是否完成设置密码
        $isPassword = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        //判断是否完成还款授权 是否过期
        $isrepayAuth = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 4);
        $isRepayAuthTimeOut = true;
        if ($isrepayAuth) {
            $isRepayAuthTimeOut = $isrepayAuth->isTimeOut();
        }
        //判断是否完成缴费授权 是否过期
        $isfundAuth = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 5);
        $isfundAuthTimeOut = true;
        if ($isfundAuth) {
            $isfundAuthTimeOut = $isfundAuth->isTimeOut();
        }
        $this->layout = 'depos/index';
        $this->getView()->title = '操作步骤';
        return $this->render('index', [
                'csrf' => $this->getCsrf(),
                'user_id' => $userId,
                'isPassword' => $isPassword,
                'isrepayAuth' => $isrepayAuth,
                'isfundAuth' => $isfundAuth,
                'isRepayAuthTimeOut' => $isRepayAuthTimeOut, //还款授权 是否过期 true 过期 false 未过期
                'isfundAuthTimeOut' => $isfundAuthTimeOut, //缴费授权 是否过期 true 过期 false 未过期
            ]
        );
    }

    public function actionNewopen()
    {
        $userId = $this->get('user_id');
        if (!$userId) {
            exit("参数错误");
        }
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            exit("获取用户信息失败");
        }
        $ret_open = $this->newopen($userInfo);

        $this->layout = 'depos/index';
        return $this->render('toopen', ['base_form' => $ret_open,]);
    }

    public function actionNewopenwx()
    {
        $userId = $this->post('user_id');
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
            echo json_encode($arr);
            exit;
        }
        $ret_open = $this->newopen($userInfo);
        if (!$ret_open) {
            $arr = ['res_code' => '1001', 'res_msg' => '开户失败'];
            echo json_encode($arr);
            exit;
        }

        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_open];
        echo json_encode($arr);
        exit;
    }

    public function actionNewbankwx()
    {
        $userId = $this->post('user_id');
        $come_from = $this->post('come_from', 6);
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
            echo json_encode($arr);
            exit;
        }
        //判断存管密码
        $is_password = (new Payaccount())->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if(empty($is_password)){
            $arr = ['res_code' => '1002', 'res_msg' => '请设置密码'];
            echo json_encode($arr);
            exit;
        }
        $ret_open = $this->newbank($userInfo, $come_from);
        if (!$ret_open) {
            $arr = ['res_code' => '1001', 'res_msg' => '绑卡请求失败'];
            echo json_encode($arr);
            exit;
        }

        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_open];
        echo json_encode($arr);
        exit;
    }

    public function actionSetpwd()
    {
        $userId = $this->post('user_id');
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
            'retUrl' => Yii::$app->request->hostInfo . '/new/depositorynew?type=2&user_id=' . $userInfo->user_id,
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

    public function actionAuthorize()
    {
        $userId = $this->post('user_id');
        $isRepay = $this->post('is_repay', 1); //调用 1借款调用 2还款调用
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
        $retUrl = Yii::$app->request->hostInfo . '/new/depositorynew/loading?type=' . 6 . '&user_id=' . $userInfo->user_id;
        if ($isRepay == 2) {
            $retUrl = Yii::$app->request->hostInfo . '/new/depositorynew/waiting?user_id=' . $userInfo->user_id;
        }
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
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&from=auth',
             'retUrl' => $retUrl,//返回交易页面链接
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getauthfoureinonenotify',//后台响应链接
        ];
        $ret_set = $apiDep->cgauth($params);
        $ret_set = json_decode($ret_set,true);
        if (!empty($ret_set) && $ret_set['res_code'] != 0) {
            $arr = ['res_code' => '1001', 'res_msg' => '授权失败'];
            echo json_encode($arr);
            exit;
        }
        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $ret_set['res_data']];
        echo json_encode($arr);
        exit;
    }

    public function actionLoading()
    {
        $userId = $this->get('user_id');
        $type = $this->get('type');
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            exit("获取用户信息失败");
        }
        $this->layout = 'depos/index';
        $this->getView()->title = '等待中';
        return $this->render('loading', [
                    'userId' => $userId,
                    'csrf' => $this->getCsrf(),
                    'type' => $type, //1：开户 2：设置密码 4：还款授权 5：消费授权 6:绑卡 7:绑卡且跳转至银行卡列表页
                        ]
        );
    }

    public function actionGetresult()
    {
        $userId = $this->post('user_id');
        $step = $this->post('type'); //1：开户 2：设置密码 4：还款授权 5：消费授权 6：绑卡 7:绑卡且跳转至银行卡列表页 8:绑卡且跳回列表页 9:设置密码且跳转至银行卡列表页 10：未知跳转 11：解卡跳回银行卡列表页 12:四合一授权跳回借款首页
        $userInfo = (new User())->getUserinfoByUserId($userId);
        if (!$userInfo) {
            $arr = ['res_code' => '1001', 'res_msg' => '用户信息获取失败'];
            echo json_encode($arr);
            exit;
        }
        $type = $step;
        if (in_array($step, [6,7,8,11])) { //6：绑卡 7:绑卡且跳转至银行卡列表页  8:绑卡且跳回列表页
            $type = 1;
        }
        if(in_array($step,[9])){
            $type = 2;
        }
        if(in_array($step, [12])){
            $type = 6;
        }
        $result = (new Payaccount())->getPaysuccessByUserId($userInfo->user_id, 2, $type);
        if (empty($result)) {
            $arr = ['res_code' => '1002', 'res_msg' => '未成功'];
            echo json_encode($arr);
            exit;
        }
        if ( in_array($step, [6,7,8]) ) {
            if (empty($result->card)) {
                $arr = ['res_code' => '1002', 'res_msg' => '未成功'];
                echo json_encode($arr);
                exit;
            }
        }
        if( $step == 11 ){
             $default_bank = User_bank::find()->where(['user_id'=>$userId,'default_bank'=>1])->one();   
             if (!empty($result->card) || !empty($default_bank)) {
                $arr = ['res_code' => '1002', 'res_msg' => '未成功'];
                echo json_encode($arr);
                exit;
            }
        }
        $arr = ['res_code' => '0000', 'res_msg' => '成功'];
        echo json_encode($arr);
        exit;
    }

    public function getCsrf()
    {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    //还款_中转页
    public function actionWaiting()
    {
        $this->layout = 'depos/index';
        $type = $this->get('type');
        $userId = $this->get('user_id');
        $userObj = (new User())->getUserinfoByUserId($userId);
        if (empty($userObj)) {
            exit("获取用户信息失败");
        }
        $userLoanId = (new User_loan())->getHaveinLoan($userId);
        if (empty($userLoanId)) {
            exit("获取借款信息错误");
        }
        $this->getView()->title = '等待中';
        return $this->render('waiting', [
                'type' => $type,
                'user_id' => $userId,
                'csrf' => $this->getCsrf(),
                'loan_id' => $userLoanId
            ]
        );
    }

    //还款_授权选择页
    public function actionChoice()
    {
        $this->layout = 'depos/choice';
        $userId = $this->get('user_id','');
        $from = $this->get('from','weixin');
        $userObj = (new User())->getUserinfoByUserId($userId);
        if (!$userObj) {
            exit("获取用户信息失败");
        }
        $auth_result = 0;
        $isAuth = (new Payaccount())->getPaysuccessByUserId($userObj->user_id, 2, 6);
        if (!empty($isAuth)) {
            $authTimeOut = $isAuth->isTimeOut();
            if ($authTimeOut) {
                if($from == 'weixin'){
                    return $this->redirect('/borrow/billlist/index');
                }
                $auth_result = 1;
            }
        }
        $o_pay_account_auth_error = (new PayAccountError())->getError($userObj->user_id,3,2);
        $auth_error = 0;
        if(!empty($o_pay_account_auth_error)){
            if($o_pay_account_auth_error->status == 0){
                $o_pay_account_auth_error->updateStatusSuccess();
                $auth_error = 1;
            }
        }
        $this->getView()->title = '操作授权';
        return $this->render('choice', [
                'userId' => $userId,
                'csrf' => $this->getCsrf(),
                'auth_result' => $auth_result,
                'from' => $from,
                'auth_error' => $auth_error,
            ]
        );
    }

    public function actionIswindow()
    {
        $userId = $this->get('user_id');
        $userObj = (new User())->getUserinfoByUserId($userId);
        if (empty($userObj)) {
            $arr = ['res_code' => '0000', 'res_msg' => '成功', 'status' => 2];
            echo json_encode($arr);
            exit;
        }
        $payAccountModel = new Payaccount();
        $isFundAuth = $payAccountModel->getPaysuccessByUserId($userObj->user_id, 2, 5);
        if (empty($isFundAuth)) {
            $arr = ['res_code' => '0000', 'res_msg' => '成功', 'status' => 1];
            echo json_encode($arr);
            exit;
        }
        $arr = ['res_code' => '0000', 'res_msg' => '成功', 'status' => 0];
        echo json_encode($arr);
        exit;
    }

    /**
     * 页面跳转存管开户
     * @param $userInfo
     * @return bool
     */
    private function newopen($userInfo)
    {
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002', //交易渠道
            'from' => '1',
            'idType' => '01', //01-身份证
            'idNo' => $userInfo->identity, //证件号码
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'acctUse' => '00000',
            'smsFlag' => '0', //0不需要; 1需要
            'identity' => '2', //1：出借角色2：借款角色3：代偿角色
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getopennotify',
            'retUrl' => Yii::$app->request->hostInfo . '/new/depositorynew/loading?type=1&user_id=' . $userInfo->user_id,
            'isPage' => '1',
            'isUrl' => '1',
        ];
        $ret_open = $apiDep->khym($params);
        $payAccount = new Payaccount();
        $condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 1,
            'activate_result' => 0,
        ];
        if (!$ret_open) {
            Logger::dayLog('depository', '开户失败', 'user_id->' . $userInfo->user_id);
            return false;
        }
        $addRes = $payAccount->add_list($condition);
        if (!$addRes) {
            Logger::dayLog('depository', 'pay_account表操作失败', 'user_id->' . $userInfo->user_id);
            return false;
        }

        return $ret_open;
    }

    private function newbank($userInfo, $come_from) {
        $apiDep = new Apidepository();
        $payAccountModel = new Payaccount();
        $payAccountObj = $payAccountModel->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!empty($payAccountObj) && !empty($payAccountObj->card)) {
            return FALSE;
        }
        $postData = [
            'idType' => '01', //证件类型
            'idNo' => $userInfo->identity, //证件号码
            'name' => $userInfo->realname, //姓名
            'accountId' => $payAccountObj->accountId, //电子账号
            'userIP' => Common::get_client_ip(), //客户IP
            'retUrl' => Yii::$app->request->hostInfo . '/new/depositorynew/loading?type=' . $come_from . '&user_id=' . $userInfo->user_id, //前台跳转链接
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&from=weixin',
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getbindbanknotify', //后台通知链接
            'isUrl' => '1', //选择获得url地址
            'from' => '1', //来源
            'channel' => '000002', //交易渠道
            'order_id' => date('YmdHis') . $userInfo->user_id . rand(1000, 9999), //订单号
        ];
        $ret_open = $apiDep->bindpage($postData);
        return $ret_open;
    }

}

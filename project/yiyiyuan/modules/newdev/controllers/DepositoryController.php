<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Bank;
use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Keywords;
use app\models\news\Cg_remit;
use app\models\news\Common as Common2;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Sms_depository;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_loan_flows;
use app\models\news\BehaviorRecord;
use app\models\news\White_list;
use app\models\news\Bankbill;
use app\models\news\User_label;
use app\models\news\User_remit_list;
use app\models\news\Loan_renew_user;
use app\models\news\No_repeat;
use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\Payaccount;
use app\commonapi\Apidepository;
use Yii;

class DepositoryController extends NewdevController {

    public function actionOpen(){
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $bank_id  = $this->post('bank_id');
        $business = $this->post('business');
        $bankInfo = User_bank::findOne($bank_id);
        if(!$bankInfo || !$business){
            $resultArr = array('ret' => '99','msg'=>'获取银行卡信息或借款信息错误');
            echo json_encode($resultArr);exit;
        }
        $resultArr = [
            'ret' => 0,
            'mark' => 1,
            'isOpen' => 1,
            'isCard' => 1,
            'isAuth' => 1,
            'isPass' => 1,
            'goLoan' => 2,//默认2不直接借款，1借款
        ];


        $payAccount = new Payaccount();
        //判断用户是否存管开户
        $isOpen = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);

        if(empty($isOpen)){
            $resultArr['isOpen'] = 0;//未开户
        }
        //判断用户是否存管绑卡
        $isOpen = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(empty($isOpen) || empty($isOpen->card)){
            $resultArr['isCard'] = 0;//未绑卡
        }
        //判断用户是否存管设置密码
        $isPass = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if(empty($isPass)){
            $resultArr['isPass'] = 0;//未设置密码
        }

        echo json_encode($resultArr);exit;
    }

    /**
     * 用户是否开户，如果没有，开户，
     * 用户是否设置密码，如果没有，设置密码
     * 用户是否授权，如果没有，授权
     */
    public function actionIsopen(){
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $bank_id  = $this->post('bank_id');
        $business = $this->post('business');
        $bankInfo = User_bank::findOne($bank_id);
        if(!$bankInfo || !$business){
            $resultArr = array('ret' => '96','msg'=>'获取银行卡信息或借款信息错误');
            echo json_encode($resultArr);exit;
        }

        //判断用户是否存管开户
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);

        //短信开户
        if(empty($isAccount)){
            $resultArr = array('ret' => '6','msg'=>'您在江西银行未进行开户');
            echo json_encode($resultArr);exit;
        }

        //判断用户是否存管设置密码
        $isPass = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if(!$isPass){
            $setpwd = $this->pwdset($business);
            if(!$setpwd){
                $resultArr = array('ret' => '2','msg'=>'设置密码失败');
                echo json_encode($resultArr);exit;
            }
            $resultArr = array('ret' => '3','data' =>$setpwd, 'msg'=>'前往银行设置密码');
            echo json_encode($resultArr);exit;

        }
        //判断用户是否存管授权
        $isAuth = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 3);
        if(!$isAuth){
            $setAuth = $this->auth($userInfo, $business);
            if(!$setAuth){
                $resultArr = array('ret' => '4','msg'=>'授权失败');
                echo json_encode($resultArr);exit;
            }
            $resultArr = array('ret' => '5','data' =>$setAuth, 'msg'=>'前往银行授权');
            echo json_encode($resultArr);exit;
        }
        $resultArr = array('ret' => '0','msg'=>'已开户和授权');
        echo json_encode($resultArr);exit;
        //判断已开户用户的绑卡关系
//        if($isAccount->card == $bankInfo->card){
//            $resultArr = array('ret' => '0','msg'=>'已开户,卡片与绑卡时为同一张卡');
//            echo json_encode($resultArr);exit;
//        }else{
//            $resultArr = array('ret' => '2','msg'=>'已开户,卡片与绑卡时不是同一张卡');
//            echo json_encode($resultArr);exit;
//        }
    }

    /**
     * 获取存管开户验证码
     * @return string
     */
    public function actionGetdcode() {
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);

        $mobile = $this->post('mobile');
        if(!$mobile){
            $resultArr = array('ret' => '1','msg'=>"获取失败,请稍后再试");
            echo json_encode($resultArr);
            exit;
        }
        //短信次数
        $smsCount = (new Sms_depository())->getSmsCount($mobile,1);
        if($smsCount >= 6){
            $resultArr = array('ret' => '1','msg'=>"您今天获取验证码的次数过多，请明天再试");
            echo json_encode($resultArr);
            exit;
        }
        $params['srvTxCode'] = 'cardBindPlus';
        $codeRes = $this->getDcode($mobile);
        if(!$codeRes){
            $resultArr = array('ret' => '1','msg'=>"获取失败,请稍后再试");
            echo json_encode($resultArr);
            exit;
        }
        (new Sms_depository())->addList(['recive_mobile'=>$mobile,'sms_type'=>1]);
        $resultArr = array('ret' => '0','msg'=>"获取成功",'data'=>$codeRes);
        echo json_encode($resultArr);
        exit;
    }

    /**
     * 存管开户
     */
    public function actionOpenaccount(){
        $postData = $this->post();
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        if(empty($postData) || !$postData['bank_id'] || !$postData['mobile'] || !$postData['srvAuthCode']){
            $resultArr = array('ret' => '1','msg'=>"参数不能为空");
            echo json_encode($resultArr);exit;
        }
        if(!$postData['code']){
            $resultArr = array('ret' => '2','msg'=>"请输入验证码");
            echo json_encode($resultArr);exit;
        }
        $bankInfo = User_bank::findOne($postData['bank_id']);
        if(!$bankInfo){
            $resultArr = array('ret' => '3','msg'=>"银行卡信息错误");
            echo json_encode($resultArr);exit;
        }
        //判断用户是否存管开户
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if($isAccount){
            $resultArr = array('ret' => '0','msg'=>"开户成功");echo json_encode($resultArr);exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $bankInfo->card,
            'acctUse' => '00000',
            'lastSrvAuthCode' => $postData['srvAuthCode'],
            'smsCode' => $postData['code'],
            'from' => '1',
        ];
        $ret_open = $apiDep->openplus($params);
        $payAccount = new Payaccount();
        $condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 1,
        ];
        if(!$ret_open){
            //判断用户是否开户成功
            $acc = $payAccount->getPaysuccessByUserId($userInfo->user_id);
            if($acc){
                $resultArr = array('ret' => '0','msg'=>"用户已开户成功");
                echo json_encode($resultArr);exit;
            }
            $condition['activate_result'] = 0;//失败
            $addRes = $payAccount->add_list($condition);
            if(!$addRes){
                $resultArr = array('ret' => '5','msg'=>"网络错误");
                echo json_encode($resultArr);exit;
            }
            $resultArr = array('ret' => '4','msg'=>"开户失败请重试");
            echo json_encode($resultArr);exit;
        }
        //开户成功
        $condition['activate_result'] = 1;
        $condition['accountId'] = $ret_open["accountId"];
        $condition['card'] = (string) $bankInfo->id;
        $addRes = $payAccount->add_list($condition);
        if(!$addRes){
            $resultArr = array('ret' => '5','msg'=>"网络错误");
            echo json_encode($resultArr);exit;
        }
        $userBankModel = new User_bank();
        $userBankModel->updateDefaultBank($userInfo->user_id, $bankInfo->id);
        $resultArr = array('ret' => '0','msg'=>"开户成功");
        echo json_encode($resultArr);exit;
    }

    /**
     * 设置密码
     * @return string
     */
    public function actionSetpwd(){
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $bank_id  = $this->post('bank_id');
        $bankInfo = User_bank::findOne($bank_id);
        if(!$bankInfo){
            $resultArr = array('ret' => '96','msg'=>'获取银行卡信息或借款信息错误');
            echo json_encode($resultArr);exit;
        }

        //判断用户是否存管开户
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(empty($isAccount)){
            $resultArr = array('ret' => '2','msg'=>'您在江西银行未进行开户');
            echo json_encode($resultArr);exit;
        }
        $isPwd = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if($isPwd){
            $resultArr = array('ret' => '4', 'msg'=>'已经设置过密码');
            echo json_encode($resultArr);exit;
        }
        //设置密码
        $setpwd = $this->pwdset();
        if(!$setpwd){
            $resultArr = array('ret' => '1','msg'=>'设置密码失败');
            echo json_encode($resultArr);exit;
        }
        $resultArr = array('ret' => '0','data' =>$setpwd, 'msg'=>'前往银行设置密码');
        echo json_encode($resultArr);exit;
    }

    /**
     * 设置密码
     * @return string
     */
    public function actionSetpwdres(){
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        //判断用户是否存管开户
        $payAccount = new Payaccount();
        $isPwd = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if(!$isPwd){
            $resultArr = array('ret' => '1', 'msg'=>'设置密码未成功');
            echo json_encode($resultArr);exit;
        }
        $resultArr = array('ret' => '0', 'msg'=>'设置密码成功');
        echo json_encode($resultArr);exit;
    }

    /**
     * 获取验证码
     * @param $mobile 手机号
     * @param $type   1:开户 2：绑卡 3:授权
     * @param $accountid
     * @param $card
     * @return mixed
     */
    private function getDcode($mobile, $type = 1, $accountid = '', $card = ''){
         if($type == 1){
             $params['srvTxCode'] = 'accountOpenPlus';
         }elseif($type == 2){
             $params['srvTxCode'] = 'cardBindPlus';
         }else{
             $params['srvTxCode'] = 'termsAuth';
             $params['reqType'] = '2';
             $params['accountId'] = $accountid;
             $params['cardNo'] = $card;
         }
         $params['from'] = 1;
         $params['channel'] = '000002';
         $params['mobile'] = $mobile;
         $apiDep = new Apidepository();
         return $apiDep->sendmsg($params);
     }

    /**
     * 解绑卡
     * @return mixed
     */
    public function actionOverbind(){
        $bank_id = $this->post("bank_id");
        $bankInfo = User_bank::findOne($bank_id);
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(!$bankInfo || !$isAccount){
            $resultArr = array('ret' => '1','msg'=>'获取用户信息错误');
            echo json_encode($resultArr);exit;
        }
        if($isAccount->card == ''){//已经解绑
            $resultArr = array('ret' => '0','msg'=>'解绑成功');
            echo json_encode($resultArr);exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',
            'from' => 1,
            'accountId' => $isAccount->accountId,//存管平台分配的账号
            'idType' => '01',//01-身份证（18位）20-组织机构代码25-企业社会信用代码
            'idNo' => $userInfo->identity,//证件号码
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $isAccount->card,//银行卡号	A	19	M	绑定银行卡号
        ];
        if(!$apiDep->overbind($params)){
            $resultArr = array('ret' => '2','msg'=>'解绑失败');
            echo json_encode($resultArr);exit;
        }
        $up_res = $isAccount->update_list(['card' => '']);
        if(!$up_res){
            $resultArr = array('ret' => '3','msg'=>'解绑数据更新失败');
            echo json_encode($resultArr);exit;
        }
        $resultArr = array('ret' => '0','msg'=>'解绑成功');
        echo json_encode($resultArr);exit;
    }

    /**
     * 设置密码
     * @param $business_type
     * @return bool|mixed
     */
    private function pwdset(){
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(!$isAccount){
            return false;
        }
        //如果没有开户记录，添加开户记录
        $isPassword = $payAccount->getPaystatusByUserId($userInfo->user_id, 2, 2);
        if(!$isPassword){
            $add_condition = [
                "user_id" => $userInfo->user_id,
                'type' => 2,
                'step' => 2,
                'accountId' => $isAccount->accountId,
            ];
            $add_res = $payAccount->add_list($add_condition);
            if(!$add_res){
                return false;
            }
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
            'retUrl' => Yii::$app->request->hostInfo.'/new/depository/passwordback',
            'notifyUrl' => Yii::$app->request->hostInfo.'/new/getsetpassnotify',
        ];
        $ret_set = $apiDep->pwdset($params);
        if(!$ret_set){
            return false;
        }
        return $ret_set;
    }

    /**
     * 提现
     * @return mixed
     */
    public function actionGetmoney(){
        $loan_id = $this->post('loan_id');
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $loanInfo = User_loan::findOne($loan_id);
        $userRemit = User_remit_list::find()->where(['loan_id'=>$loan_id])->one();
        $settle_amount = $loanInfo->getActualAmount($loanInfo->is_calculation, $loanInfo->amount, $loanInfo->withdraw_fee);
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(!$isAccount){
            $resultArr = array('ret' => '3','msg'=>"您未开户");
            echo json_encode($resultArr);exit;
        }
        $isPassword = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if(!$isPassword){
            $resultArr = array('ret' => '4','msg'=>"您未设置密码");
            echo json_encode($resultArr);exit;
        }
        $rem_res = $userRemit->update_remit(['remit_status'=>"DOREMIT"]);
        if(!$rem_res){
            $resultArr = array('ret' => '5','msg'=>"出款数据更新失败");
            echo json_encode($resultArr);exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',
            'from' => 1,
            'order_no' => $userRemit->order_id,
            'accountId' => $isAccount->accountId,
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $isAccount->card,
            'txAmount' => (string) round($settle_amount,2),
            'txFee' => $loanInfo->is_calculation == 1 ?  (string) round($loanInfo->withdraw_fee,2) : '0',
            'retUrl' => Yii::$app->request->hostInfo.'/new/loan',
            'notifyUrl' => Yii::$app->request->hostInfo.'/new/getmoneynotify',
            'forgotPwdUrl' => Yii::$app->request->hostInfo.'/new/forgot?userid='.$userInfo->user_id.'&from=weixin',
            'acqRes' => $userRemit->order_id,
        ];
        Logger::errorLog(print_r($params, true), 'moneyout_post', 'depository');
        $ret_set = $apiDep->moneyout($params);
        if(!$ret_set){
            $resultArr = array('ret' => '2','data'=>"提现失败");
            echo json_encode($resultArr);exit;
        }
        $resultArr = array('ret' => $ret_set['res_code'],'data'=>$ret_set['res_data']);
        echo json_encode($resultArr);exit;
    }

    /**
     * 判断用户是否设置密码成功
     * @param $user_id
     * @return bool
     */
    public function isPassword($user_id){
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($user_id, 2, 2);
        if(empty($isAccount)){
            return false;
        }
        return true;
    }

    public function actionPasswordback(){
        $this->getView()->title = "操作成功";
        return $this->render('passwordback',[
            'csrf'=>$this->getCsrf(),
        ]);
    }

    public function actionAuthback(){
        $getData = $this->get();
        Logger::errorLog(print_r($getData, true), 'getdata', 'depository');
        $this->getView()->title = "操作成功";
        return $this->render('authback',
            [
                'business' => $getData['business'],
            ]
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
     * 免短信存管开户
     * @param $userInfo
     * @param $bankInfo
     * @return array
     */
    private function freeopen($userInfo, $bankInfo){
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',//交易渠道
            'idType' => '01',//01-身份证
            'idNo' => $userInfo->identity,//证件号码
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $bankInfo->card,
            'acctUse' => '00000',
            'from' => '1',
        ];
        $ret_open = $apiDep->freeopen($params);
        $payAccount = new Payaccount();
        $condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 1,
        ];
        if(!$ret_open){
            $condition['activate_result'] = 0;//失败
            $addRes = $payAccount->add_list($condition);
            if(!$addRes){
                $resultArr = array('ret' => '5','msg'=>"网络错误");
                return $resultArr;
            }
            $resultArr = array('ret' => '1','msg'=>"开户失败请重试");
            return $resultArr;
        }
        //开户成功
        $condition['activate_result'] = 1;
        $condition['accountId'] = $ret_open["accountId"];
        $condition['card'] = (string) $bankInfo->id;
        $addRes = $payAccount->add_list($condition);
        if(!$addRes){
            $resultArr = array('ret' => '5','msg'=>"网络错误");
            return $resultArr;
        }
        $userBankModel = new User_bank();
        $userBankModel->updateDefaultBank($userInfo->user_id, $bankInfo->id);
        $resultArr = array('ret' => '0','msg'=>"开户成功");
        return $resultArr;
    }

    /**
     * 授权
     * @return array
     */
    public function actionAuth(){
        $postData = $this->post();
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);

        $ret_open = $this->auth($userInfo, $postData['business']);
        if(!$ret_open){
            $resultArr = array('ret' => '4','msg'=>"授权失败");
            echo json_encode($resultArr);exit;
        }
        $resultArr = array('ret' => '0','data'=>$ret_open,'msg'=>"操作成功");
        echo json_encode($resultArr);exit;
    }

    private function auth($userInfo, $business){
        //判断用户是否存管开户
        $payAccount = new Payaccount();
        $account = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(!$account){
            $resultArr = array('ret' => '99','msg'=>"请先开户");
            echo json_encode($resultArr);exit;
        }
        $ispass = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if(!$ispass){
            $resultArr = array('ret' => '98','msg'=>"请先设置密码");
            echo json_encode($resultArr);exit;
        }
        $payAccount = new Payaccount();
        $condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 3,
            'activate_result' => 0,
            'accountId' => $account->accountId,
            'sign' => 1,
            //'card' => $account->card,
        ];
        $addRes = $payAccount->add_list($condition);
        if(!$addRes){
            $resultArr = array('ret' => '98','msg'=>"数据更新失败");
            echo json_encode($resultArr);exit;
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',//交易渠道
            'accountId' => $account->accountId,
            'from' => 1,
            'orderId' => date('YmdHis') . rand(1000, 9999),
            'agreeWithdraw' => '1',//开通预约取现功能标志
            'autoBid' => '1',//开通自动投标功能标志
            'autoTransfer' => '1',//开通自动债转功能标志
            'directConsume' => '1',//开通无密消费功能标识
            'forgotPwdUrl' => Yii::$app->request->hostInfo.'/new/forgot?userid='.$userInfo->user_id.'&from=weixin',
            'transactionUrl' => Yii::$app->request->hostInfo.'/new/depository/authback?business='.$business,
            'notifyUrl' => Yii::$app->request->hostInfo.'/new/getauthnotify',
        ];
        $ret_open = $apiDep->auth($params);
        return $ret_open;
    }


    /**
     * 开放平台提现
     * @return mixed
     */
    public function actionGetmoneyopen(){
        $loan_id = $this->get('loan_id');
        $user = $this->getUser();
        $userInfo = User::findOne($user->user_id);
        $loanInfo = User_loan::findOne($loan_id);
        $settle_amount = $loanInfo->getActualAmount($loanInfo->is_calculation, $loanInfo->amount, $loanInfo->withdraw_fee);
        $cgRemitModel = new Cg_remit();
        $cgRemit = $cgRemitModel->getByLoanId($loan_id);
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if(!$isAccount){
            $resultArr = array('ret' => '3','msg'=>"您未开户");
            echo json_encode($resultArr);exit;
        }
        $isPassword = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if(!$isPassword){
            $resultArr = array('ret' => '4','msg'=>"您未设置密码");
            echo json_encode($resultArr);exit;
        }
        if ($cgRemit->remit_status != 'WILLREMIT') {
            $resultArr = array('ret' => '5','msg'=>"您正在提现中");
            echo json_encode($resultArr);exit;
        }
        $remitting = $cgRemit->doremit();
        if (!$remitting) {
            $resultArr = array('ret' => '6','msg'=>"提现数据更新失败");
            echo json_encode($resultArr);exit;
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
            'mobile'=> $userInfo->mobile,
            'withdraw_money'=> (string) round($settle_amount,2),
            'withdraw_fee'=> $loanInfo->is_calculation == 1 ?  (string) round($loanInfo->withdraw_fee,2) : '0',
            'forgot_pwdurl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&from=weixin',
            'ret_url'=> Yii::$app->request->hostInfo.'/new/loan/showloan?loan_id='.$loan_id,
//            'isUrl' => 1,
        ];
        $params['isUrl'] = 1;
        Logger::errorLog(print_r($params, true), 'moneyoutopen_post', 'depository');
        $ret_set = $apiDep->moneyoutopen($params);
        if(!$ret_set || $ret_set['rsp_code'] != 0){
            $cgRemit->willRemit();
            $resultArr = array('ret' => '2','data'=>"提现失败");
            echo json_encode($resultArr);exit;
        }

        return $this->redirect($ret_set['rsp_msg']);
    }
}

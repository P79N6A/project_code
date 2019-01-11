<?php
namespace app\modules\borrow\controllers;

use app\common\ApiClientCrypt;
use app\common\Logger;
use app\models\dev\ActivityLoanRepay;
use \app\models\news\Coupon_list;
use app\models\news\ScanTimes;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_loan;
use Yii;

class PurchasecardsactivityController extends BorrowController
{
    public $enableCsrfValidation = false;

    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @return string
     * 活动页面
     */
    public function actionIndex()
    {
        $this->layout = "purchasecardsactivity";
        $uid = $this->get('user_id', '');
        //获取用户
        if (empty($uid)) {
            $user = $this->getUser();
            if (!empty($user)) {
                $uid = $user->user_id;
            }
        } else {
            $userModel = new User();
            $user = $userModel->getUserinfoByUserId($uid);
            Yii::$app->newDev->login($user, 1);
        }
        //判断app还是H5
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $isapp = 1;  //app端
        } else {
            $isapp = 2;  //h5端
        }
        //银行卡列表
        $getUserBankData = [];
        $type = '';
        $isAlert = 1;
        if ($user) {
            $userBankModel = new User_bank(); //银行卡
            $getUserBankData = $userBankModel->getBankArr($user->user_id);
            //截取最后四位数
            foreach ($getUserBankData as $k => $v) {
                $getUserBankData[$k]['card'] = substr($v['card'], -4);
            }
            //查询异步结果
            $activityLoanRepay = new ActivityLoanRepay();
            $getLoanRepay = $activityLoanRepay->getData($user->user_id);
            if ($getLoanRepay) {
                $type = $getLoanRepay->status;
                if ($getLoanRepay->is_alert == 1 && $type!=-1 && $type!=0) {
                    $data = ['is_alert' => 2];
                    $save = $getLoanRepay->update_batch($data);
                    if (!$save) {
                        Logger::log('purchasecardsacyivity', '弹层修改失败--' . $user->user_id);
                    }
                    $isAlert = 1;
                } else {
                    $isAlert = 2;
                }
            }
            //重复支付限制
            if($type ==0) {
                $limit = $activityLoanRepay->fourlimit($user->user_id);
                if ($limit){
                    $type = -1;
                }
            }
        }
        return $this->render('index', [
            'type' => $type,
            'user_id' => $uid,
            'banklist' => $getUserBankData,
            'is_app' => $isapp,
            'is_alert' => $isAlert,
        ]);
    }

    /**
     * 判断
     */
    public function actionJudge()
    {
        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        $user = $this->getUser();
        if (empty($user_id)) {
            $data = ['code' => 1, 'msg' => '您未登录,请登录后参加', 'data' => []];
            return json_encode($data);
        }
        $activityLoanRepayModel = new ActivityLoanRepay(); //活动订单表
        $userCreditModel = new User_credit();  //审核表
        $userBankModel = new User_bank(); //银行卡
        $userLoanModel = new User_loan(); //借款表
        //查询该用户是否参加过本次活动
        $getActivityLoanRepayData = $activityLoanRepayModel->getUserData($user_id);
        if ($getActivityLoanRepayData) {
            $data = ['code' => 2, 'msg' => '乐享大礼包每人只能购买一次', 'data' => []];
            return json_encode($data);
        }
        //查询判断该用户借款是否未通过和购买该礼包资格
        $getUserCreditData = $userCreditModel->checkYyyUserCredit($user_id);
//        Logger::log('purchasecardsactivity','评测状态-'.$getUserCreditData['user_credit_status']);
        if ($getUserCreditData && $getUserCreditData['user_credit_status'] == 2) {
            $data = ['code' => 3, 'msg' => '您暂无资格购买此礼包', 'data' => []];
            return json_encode($data);
        }
        //评测是否失效
        $invalid = $userCreditModel->Invalid($user_id);
        $userLoan = $userLoanModel->getHaveinLoan($user_id);
        if ($getUserCreditData['user_credit_status'] == 1 || $getUserCreditData['user_credit_status'] == 3 ||($invalid==6 && !$userLoan)) {
            $data = ['code' => 4, 'msg' => '购买礼包请先获取购买资格', 'data' => []];
            return json_encode($data);
        }
        //选择银行卡
        $getUserBankData = $userBankModel->getBankArr($user_id);
        if (empty($getUserBankData)) {
            $data = ['code' => 5, 'msg' => '请前往个人中心绑定银行卡', 'data' => []];
        } else {
            $data = ['code' => 0, 'msg' => '成功', 'data' => []];
        }
        return json_encode($data);
    }

    /**
     * @return string
     * 选择银行卡购卡
     */
    public function actionPaybankcard()
    {
        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        $userCreditModel = new User_credit();  //审核表
        //查询判断该用户借款是否未通过和购买该礼包资格
        $getUserCreditData = $userCreditModel->checkYyyUserCredit($user_id);
        //评测通过并且借款未驳回或者未借款--才可发起借款
        $userLoanModel = new User_loan(); //借款表
        $activityLoanRepayModel = new ActivityLoanRepay(); //活动订单表
        $limit = $activityLoanRepayModel->fourlimit($user_id);
        if($limit){
            $array = ['code' => 8, 'msg' => '订单正在支付中,请稍等', 'data' => []];
            return json_encode($array);
        }
        $userLoan = $userLoanModel->getHaveinLoan($user_id);
        if ($getUserCreditData && ($getUserCreditData['user_credit_status'] == 4 || $getUserCreditData['user_credit_status'] == 5 || $userLoan)) {
            $bank_id = $this->post('bank_id');
            $user = $this->getUser();
            $user_id = $user->user_id;
            $bankModel = new User_bank(); //银行卡表
            $bank = $bankModel->isUserCard($user->user_id, $bank_id);
            $orderid = date('YmdHis') . rand(1000, 9999);
            $platform = 2;
            $condition = [
                'user_id' => $user_id,
                'order_pay_no' => $orderid,
                'bank_id' => $bank_id,
                'money' => 5,
                'platform' => $platform,
            ];
            $ret = $activityLoanRepayModel->addLoan($condition, 1);
            if (!$ret) {
                $array = ['code' => 6, 'msg' => '购买优惠券记录生成失败', 'data' => []];
                return json_encode($array);
            }
            $card_type = ($bank->type == 0) ? 1 : 2;
            $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
            $business_code = "YYYKJYHQ";
            $userip = \app\models\news\Common::get_client_ip();
            $postData = array(
                'orderid' => $orderid, // 请求唯一号
                'identityid' => (string)$user_id, // 用户标识
                'bankname' => $bank->bank_name, //银行名称
                'bankcode' => $bank->bank_abbr, //银行编码
                'card_type' => $card_type, // 卡类型
                'cardno' => $bank->card, // 银行卡号
                'idcard' => $user->identity, // 身份证号
                'username' => $user->realname, // 姓名
                'phone' => $phone, // 预留手机号
                'productcatalog' => '7', // 商品类别码
                'productname' => '购买优惠券', // 商品名称
                'productdesc' => '购买优惠券', // 商品描述
                'amount' => 5*100, // 交易金额
                'orderexpdate' => 60,
                'business_code' => $business_code,
                'userip' => $userip,
                'callbackurl' => Yii::$app->params['avtivity_loanRepay_notify_url'], // 异步回调地址
            );
            $openApi = new ApiClientCrypt;
            Logger::errorLog(print_r($postData, true), 'purchasecardsactivity');
            $res = $openApi->sent('payroute/pay', $postData, 2);
            $result = $openApi->parseResponse($res);
            Logger::errorLog(print_r($result, true), 'purchasecardsactivity');
            if ($result['res_code'] == 0 && !empty($result['res_data']['url'])) {
                $array = ['code' => 0, 'msg' => '请求成功', 'url' => $result['res_data']['url']];
                return json_encode($array);
            } else {
                $array = ['code' => 7, 'msg' => '请求支付失败', 'data' => []];
                return json_encode($array);
            }
        }else{
            $data = ['code' => 3, 'msg' => '您暂无资格购买此礼包', 'data' => [$getUserCreditData]];
            return json_encode($data);
        }
    }
}
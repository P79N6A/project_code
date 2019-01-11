<?php

namespace app\modules\api\controllers\controllers312;

use app\commonapi\Apibaidu;
use app\commonapi\Apidepository;
use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\ErrorCode;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Address;
use app\models\news\AddressLoan;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Fraudmetrix_return_info;
use app\models\news\GoodsOrder;
use app\models\news\Insurance;
use app\models\news\No_repeat;
use app\models\news\Payaccount;
use app\models\news\Push_yxl;
use app\models\news\Term;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_label;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_loan_flows;
use app\models\news\UserCreditList;
use app\models\news\White_list;
use app\models\service\GoodsService;
use app\modules\api\common\ApiController;
use Yii;

class UserloanController extends ApiController
{

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $amount = Yii::$app->request->post('amount');
        $days = Yii::$app->request->post('days');
        $gps = Yii::$app->request->post('_gps');
        $bank_id = Yii::$app->request->post('bank_id');
        $coupon_id = Yii::$app->request->post('coupon_id');
        $coupon_val = Yii::$app->request->post('coupon_val');
        $source = Yii::$app->request->post('source');
        $uuid = Yii::$app->request->post('uuid');
        $address = Yii::$app->request->post('address');
        $term = Yii::$app->request->post('term');
        $goods_id = '20171116151601';
        $business_type = empty(Yii::$app->request->post('business_type')) ? 1 : Yii::$app->request->post('business_type');
        $desc = Yii::$app->request->post('desc', '个人或家庭消费');
        if (empty($version) || empty($user_id) || empty($amount) || empty($days) || empty($bank_id) || empty($term) || empty($goods_id) || empty($desc)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $user = User::findOne($user_id);
        if (empty($user)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }

        $bankObj = User_bank::findOne($bank_id);
        if (empty($bankObj)) {
            $array = $this->returnBack('10043');
            echo $array;
            exit;
        }

        //检测是否允许借款
        $loanCode = $this->checkCanLoan($user);
        if ($loanCode != '0000') {
            $array = $this->returnBack($loanCode);
            echo $array;
            exit;
        }

        //监测是否有可用评测记录
        $is_credit = (new User_credit())->checkCanCredit($user);
        if(empty($is_credit)){
            exit($this->returnBack('10233'));
        }

        //监测数据是否合法
        $code = $this->checkLoanField($user, $amount, $days, $bankObj, $coupon_id, $coupon_val, $business_type, $source, $term);
        if ($code != '0000') {
            $array = $this->returnBack($code);
            echo $array;
            exit;
        }

        //判断用户有没有开户、绑卡、设置密码
        $isCungan = (new Payaccount())->isCunguan($user->user_id);
        if ($isCungan['isOpen'] != 1 || $isCungan['isCard'] != 1 || $isCungan['isPass'] != 1) {
            $array = $this->returnBack('10210');
            echo $array;
            exit;
        }

        if ($isCungan['isAuth'] != 1) {//四合一授权
            $array = $this->returnBack('10210');
            echo $array;
            exit;
        }

        //用户信用
        $status = 5;
        $prome_status = 0;

        //借款决策
//        $loan_no_keys = $user->user_id . "_loan_no";
//        $loan_no = Yii::$app->redis->get($loan_no_keys);
//        if (empty($loan_no)) {
//            $status = 3;
//            $prome_status = 1;
//        }
        $UserCreditRes=(new User_credit())->checkYyyUserCredit($user_id);
        if ($UserCreditRes['user_credit_status']==3) {
            exit($this->returnBack('10116'));
        }
        //评测驳回
        if($UserCreditRes['user_credit_status'] == 2 && !empty($UserCreditRes['invalid_time'])){
            $borrowing = (new User_loan())->getBorrowingByTime($user->user_id,$UserCreditRes['invalid_time']);
            if(!$borrowing){
                exit($this->returnBack('10121'));
            }
        }
        if ($UserCreditRes['user_credit_status'] == 4) {
//            exit($this->returnBack('10121'));//请先去购卡
            $status = 6;
            $prome_status = 5;
            $is_pay=0;//没购卡
            $oUserCredit=User_credit::find()->where(['user_id' => $user_id])->one();
            $interest=isset($oUserCredit->interest_rate) ? ($oUserCredit->interest_rate)/100 : 0.00098;
        }else if($UserCreditRes['user_credit_status']==5){
            if($UserCreditRes['order_amount'] != $amount){
                exit($this->returnBack('10117'));
            }
            $status = 6;
            $prome_status = 5;
            $is_pay=1;//已购卡
            $oUserCredit=User_credit::find()->where(['user_id' => $user_id])->one();
            $interest=isset($oUserCredit->interest_rate) ? ($oUserCredit->interest_rate)/100 : 0.00098;
        }
        $source=$oUserCredit->source;
        $inspectOpen=Keywords::inspectOpen();
        $loan_no = (new User_loan())->getLoanNo($user->user_id);

        $transaction = Yii::$app->db->beginTransaction();
        $loaninfo = $this->addLoan($user, $amount, $days, $bankObj, $is_pay, $interest, $coupon_id, $coupon_val, $business_type, $source, $uuid, $term, $goods_id, $desc, $status, $prome_status, $loan_no, $inspectOpen);
        if ($loaninfo['rsp_code'] == '0000') {
            //添加核保
            $insuranceModel = new Insurance();
            $InsuranceRes=$insuranceModel->addInsurance($loaninfo['data']);
            //添加GPS信息
            $this->addAddress($user_id, $source, $loan_no, $gps, $address);

            //更新评测表
            $oUserCredit=User_credit::find()->where(['user_id'=>$user_id])->one();
            $credit_array = [
                'loan_id' => $loaninfo['data']['loan_id'],
                'invalid_time' => date('Y-m-d H:i:s')
            ];
            $oUserCredit->updateUserCredit($credit_array);
            //同步credit记录至list表
            (new UserCreditList())->synchro($oUserCredit->req_id);
            if($inspectOpen==2){
               $ex_stattus=(new User_loan())->getcreditUserloan($user->user_id);
               if($ex_stattus){
                   //添加pushyxl
                   $nowTime = date('Y-m-d H:i:s');
                   $psuhyxl_condition = [
                       'user_id' => $user->user_id,
                       'loan_id' => $loaninfo['data']->loan_id,
                       'loan_status' => 3,
                       'type' => 1,
                       'notify_status' => 0,
                   ];
                   $pushYxlModel=new Push_yxl();
                   $result =$pushYxlModel->saveYxlInfo($psuhyxl_condition);
                   //推送order
                   $PushOrderRes=$pushYxlModel->postSignal($loaninfo['data'],$user,$oUserCredit);
               }
            }else{
               //添加pushyxl
               $nowTime = date('Y-m-d H:i:s');
               $psuhyxl_condition = [
                   'user_id' => $user->user_id,
                   'loan_id' => $loaninfo['data']->loan_id,
                   'loan_status' => 3,
                   'type' => 1,
                   'notify_status' => 0,
               ];
               $pushYxlModel=new Push_yxl();
               $result =$pushYxlModel->saveYxlInfo($psuhyxl_condition);
               //推送order
               $PushOrderRes=$pushYxlModel->postSignal($loaninfo['data'],$user,$oUserCredit);
            }

            $transaction->commit();
            $array['loan_id'] = $loaninfo['data']->loan_id;
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        } else {
            $transaction->rollBack();
            $array = $this->returnBack($loaninfo['rsp_code']);
            echo $array;
            exit;
        }
    }

    /**
     * 添加GPS信息
     * @param $userObj  用户对象
     * @return string
     */
    private function addAddress($userId, $source, $loan_no, $gps, $address)
    {
        if (empty($gps) || $gps == '0.00,0.00') {
            Logger::dayLog('api/userloan', 'gps为空', $gps, $address, $userId, $source, $loan_no);
            return false;
        }
        $array = explode(',', $gps);
        $longitude = !empty($array[0]) ? $array[0] : '';
        $latitude = !empty($array[1]) ? $array[1] : '';
        if (empty($latitude) || empty($longitude)) {
            Logger::dayLog('api/userloan', 'latitude或longitude为空', $gps, $userId, $source, $loan_no);
            return false;
        }
        if (empty($address) || $address == 'empty') {
            $address = '';
            $addressResult = (new Apibaidu())->sendReverse($latitude . ',' . $longitude);
            if (!empty($addressResult)) {
                $address = $addressResult['formatted_address'];
            }
            if (empty($address)) {
                Logger::dayLog('api/userloan', 'baidu接口获取地址失败', $gps, $userId, $source, $loan_no, $addressResult);
            }
        }
        $come_from = $source == 4 ? 2 : 1;//1：ios  2：安卓
        $addressModel = new Address();
        $addressData = [
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'come_from' => $come_from,
        ];
        $addressResult = $addressModel->save_address($addressData);
        if (empty($addressResult)) {
            Logger::dayLog('api/userloan', 'GPS记录失败', $userId, $latitude, $longitude, $address, $come_from, $addressResult);
            return false;
        }
        $addressLoanModel = new AddressLoan();
        $result = $addressLoanModel->getRecordByLoanNo($loan_no);
        if (!empty($result)) {
            return false;
        }
        $data = [
            'loan_no' => $loan_no,
            'address_id' => $addressModel->id,
            'user_id' => $userId
        ];
        $addressLoanResult = $addressLoanModel->addRecord($data);
        if (empty($addressLoanResult)) {
            Logger::dayLog('api/userloan', '借款gps记录表操作失败', $data, $addressLoanResult);
            return false;
        }
        return true;
    }

    /**
     * 检测是否允许借款
     * @param $userObj  用户对象
     * @return string
     */
    private function checkCanLoan($userObj)
    {
        if (!is_object($userObj) || empty($userObj)) {
            return '10001';
        }

        //用户状态判断
        if ($userObj->status == 5) {
            return '10097';
        }

        //连点
        $norepet = (new No_repeat())->norepeat($userObj->user_id, $type = 2);
        if (!$norepet) {
            return '99991';
        }
        $loan_info = new User_loan();
        //判断是否存在借款
        $loan = $loan_info->getHaveinLoan($userObj->user_id,[1, 4, 5, 6, 9, 10]);
        if ($loan !== 0) {
            return '10050';
        }

        //判断是否存在驳回订单
        $judgment = $loan_info->LoanJudgment($userObj->user_id);
        if (!$judgment) {
            return '10098';
        }

        //判断7-14产品中是否有进行中的借款
        if (!empty($userObj->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $userObj->identity]);
            if (!$canLoan) {
                return '99990';
            }
        }
        
        //判断先花商城中订单及借款状况
        $shop_res = (new User_credit())->getshopOrder($userObj);
        if(!$shop_res){
             return '10246';
        }
        
        return '0000';
    }

    /**
     * 监测借款数据是否合法
     * @param $user 用户对象
     * @param $amount   借款金额
     * @param $days 借款天数
     * @param $bank 银行卡对象
     * @param $coupon_id    优惠券id
     * @param int $coupon_val 优惠券金额
     * @param $business_type    借口类型
     * @return string
     */
    private function checkLoanField($userObj, $amount, $days, $bankObj, $coupon_id, $coupon_val = 0, $business_type, $source, $term)
    {
        if (!is_object($userObj) || empty($userObj)) {
            return '10001';
        }
        if (!is_object($bankObj) || empty($bankObj)) {
            return '10043';
        }
        if ($term == 1) {
            //最大额度限制
            //$max_amount = $userObj->getUserLoanAmount($userObj, $type = 1);
            $max_amount = 3000;
            //担保借款最大额度限制
            if ($business_type == 4) {
//                $max_amount = 2500;
            }
        } else {
            //最大额度限制
            $max_amount = (new Term())->getTremAmountMax($userObj->user_id, 1);
            //担保借款最大额度限制
            if ($business_type == 4) {
                $max_amount = (new Term())->getTremAmountMax($userObj->user_id, 4);
            }
        }
        if (intval($amount) < 500 || intval($amount) > $max_amount || intval($amount) % 500 != 0) {
            return '10048';
        }
        $can_max_days = (new User_loan())->getMaxLoanDays($userObj->user_id); //可借天数
        $can_days = $can_max_days[0];
        $max_days = Keywords::getMaxDays();
        if (intval($days) < $can_days || intval($days) > $max_days || intval($days) % 7 != 0) {
            return '10048';
        }

        if ($userObj->status != 3) {
            return '10023';
        }
        if (in_array($source, [1, 2, 3, 4]) && ($userObj->extend->company == '')) {
            return '10047';
        }
        if ($userObj->pic_identity == '' || ($userObj->pic_identity != '' && $userObj->status == '4')) {
            return '10047';
        }
        if ($bankObj->user_id != $userObj->user_id) {
            return '10044';
        }
        $coupon = '';
        if (!empty($coupon_id)) {
            $coupon = Coupon_list::findOne($coupon_id);
        }
        if (!empty($coupon)) {
            if (($coupon->mobile != $userObj->mobile) || $coupon->status != 1 || ($coupon->val != $coupon_val)) {
                return '10049';
            }
        }
        $isOpen = (new Payaccount())->getPaysuccessByUserId($userObj->user_id, 2, 1);
        $isPassword = (new Payaccount())->getPaysuccessByUserId($userObj->user_id, 2, 2);
        if (empty($isOpen) || empty($isPassword)) {
            return '10210';
        }
        if ($isOpen->card != $bankObj->id) {
            return '10211';
        }
        return '0000';
    }

    /**
     * 生产借款
     * @param $amount
     * @param $days
     * @param $bankObj
     * @param $coupon_id
     * @param $coupon_val
     * @param $business_type 借款类型：1信用 2担保 （注：不与user_loan字段business_type同含义）
     * @param int $source
     * @param $uuid
     * @param $term
     * @param $goods_id
     * @param $desc
     * @return array
     */
    private function addLoan($userObj, $amount, $days, $bankObj,$is_pay, $interest, $coupon_id, $coupon_val, $business_type, $source = 3, $uuid, $term, $goods_id, $desc = '个人或家庭消费', $status, $prome_status, $loan_no, $inspectOpen)
    {
        if (!is_object($userObj) || empty($userObj)) {
            return ['rsp_code' => '10001'];
        }
        if (!is_object($bankObj) || empty($bankObj)) {
            return ['rsp_code' => '10043'];
        }
        //分期开关判断
        $userTerm = (new Term())->getTremByUserId($userObj->user_id);
        if (empty($userTerm) && $term > 1) {
            return ['rsp_code' => '10200'];
        }
        if (!empty($userTerm)) {
            if ($business_type == 4 && $userTerm->db_canterm == 0 && $term > 1) {
                return ['rsp_code' => '10200'];
            } elseif ($business_type == 1 && $userTerm->xy_canterm == 0 && $term > 1) {
                return ['rsp_code' => '10200'];
            }
        }

        $source = ($source == 3) ? 2 : $source;
        $feeOpen = Keywords::feeOpen();
        $type = 2;
        if ($feeOpen == 2) {
            $ex_status=(new User_loan())->getcreditUserloan($userObj->user_id);
            if($ex_status){
                $type = 2;
            }else{
                $type = 3;
            }
        }
        $ip = Common::get_client_ip();
        if ($term > 1) {
            $business_type = ($business_type == 4) ? 6 : 5; //5信用分期 6担保分期
            $coupon_id = 0;
            $coupon_val = 0;
        } else {
            $business_type = ($business_type == 4) ? 4 : 1; //1信用 4担保
        }

        $loanModel = new User_loan();
        //计算用户手续费、利率
        $loanfee = $loanModel->loan_Fee_rate_new($amount,$interest,$days,$userObj->user_id, $term);
        $interest_fee = $loanfee['interest_fee']; //利息
        $withdraw_fee = $loanfee['withdraw_fee']; //服务费
        $fee = $loanfee['fee'] * 100;

        //是否为系统指定后置用户
        $charge = (new User_label())->isChargeUser($userObj->mobile);
        if ($charge === false) {
            $charge = 1;
        } else {
            $charge = 0;
        }
        $condition = array(
            'user_id' => $userObj->user_id,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'current_amount' => $amount,
            'days' => $days,
            'type' => $type,
            'status' => $status,
            'prome_status' => $prome_status,
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
            'desc' => $desc,
            'bank_id' => $bankObj->id,
            'source' => !empty($source) ? (int)$source : 2,
            'is_calculation' => $charge,
            'business_type' => $business_type,
            'loan_no' => $loan_no,
        );
        
        //白名单
        $whiteModel = new White_list();
        $white = $whiteModel->isWhiteList($userObj->user_id);
        if ($white) {
            $condition['final_score'] = -1;
        }

        //优惠卷金额
        if (!empty($coupon_id)) {
            if ($interest_fee > $coupon_val) {
                $condition['coupon_amount'] = $coupon_val;
            } else {
                $condition['coupon_amount'] = $interest_fee;
            }
        }

        $condition['withdraw_time'] = date('Y-m-d H:i:s');
        $ret = $loanModel->addUserLoan($condition, $business_type);
        Logger::dayLog('app/userloan', '添加userloan', $condition, $ret); //@todo 监测使用，后期请删除
        Yii::$app->redis->del($userObj->user_id . "_loan_no");
        if (empty($ret) || $condition['status'] == 3) {
            return ['rsp_code' => '10051'];
        }
        $loan = $loanModel;
//        if (!$white) {
//            $frModel = Fraudmetrix_return_info::find()->where(['loan_id' => $loan_no])->one();
//            if (!empty($frModel)) {
//                $loan->refresh();
//                $frModel->savefinal_score($loan, $frModel);
//            }
//        }
        //记录优惠券使用情况
        if (!empty($coupon_id)) {
            $couponUseModel = new Coupon_use();
            $couponUseModel->addCouponUse($userObj, $coupon_id, $loan->loan_id);
        }
        if (in_array($loan->status, [5, 6])) {
            $success_num = (new User())->isRepeatUser($loan->user_id);
            $loanextendModel = new User_loan_extend();
            $extend = array(
                'user_id' => $loan->user_id,
                'loan_id' => $loan->loan_id,
                'outmoney' => 0,
                'payment_channel' => 0,
                'userIp' => $ip,
                'extend_type' => '1',
                'success_num' => $success_num,
                'uuid' => $uuid
            );

            if ($is_pay==1) {
                $extend['status'] = 'AUTHED';
            }else{
                if($inspectOpen==2){
                    $ex_res=(new User_loan())->getcreditUserloan($loan->user_id);
                    if($ex_res){
                        $extend['status'] = 'TB-SUCCESS';
                    }else{
                        $extend['status'] = 'AUTHED';
                    }
                }else{
                    $extend['status'] = 'TB-SUCCESS';
                }
            }
            $extendId = $loanextendModel->addList($extend);
            if (empty($extendId)) {
                Logger::dayLog('app/userloan', '添加userloanextend失败', 'loan_id：' . $loan->loan_id, $extend);
                return ['rsp_code' => '10051'];
            }
        }
        if ($term > 1) {
            $goodsOrderModel = new GoodsOrder();
            $goodsService = new GoodsService();
            $order_id = $goodsService->createOrderId($loan->loan_id, $userObj->identity);
            if (!$order_id) {
                return ['rsp_code' => '10201'];
            }
            $order_amount = $loanModel->getOrderAmount($charge, $amount, $withdraw_fee, $interest_fee);
            $goodsOrder = [
                'order_id' => $order_id,
                'goods_id' => $goods_id,
                'loan_id' => $loan->loan_id,
                'user_id' => $loan->user_id,
                'number' => $term,
                'fee' => $fee,
                'order_amount' => $order_amount,
            ];
            $goodsOrderInfo = $goodsOrderModel->addGoodsOrder($goodsOrder);
            if (!$goodsOrderInfo) {
                return ['rsp_code' => '10202'];
            }
        }
        return ['rsp_code' => '0000', 'data' => $loan];
    }
}

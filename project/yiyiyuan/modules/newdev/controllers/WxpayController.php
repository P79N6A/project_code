<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\commonapi\Wxpay;
use app\models\news\BillRepay;
use app\models\news\Coupon_list;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use app\models\news\OverdueLoan;
use app\models\news\RepayCouponUse;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use Yii;

class WxpayController extends NewdevController {

    public function actionSubmitorderinfo() {
        $postData = $this->post();
        Logger::dayLog('Wxpay', "post", $postData);
        $orderid = date('YmdHis') . rand(1000, 9999);
        $loan_id = intval($postData['loan_id']);
        $coupon_id = isset($postData['coupon_id']) ? intval($postData['coupon_id']) : 0;
        if($coupon_id){
            $coupon_list_one =(new Coupon_list())->getCouponById($coupon_id);
            if($coupon_list_one){
                $coupon_amount=$coupon_list_one->val;
                $repay_coupon_use_info=(new RepayCouponUse())->getByDiscountId($coupon_id);
                if(!empty($repay_coupon_use_info)){
                    exit(json_encode(['status' => '1111', 'msg' => '该优惠券已使用或者使用中']));
                }
            }else{
                exit(json_encode(['status' => '1112', 'msg' => '该优惠券已使用或者使用中']));
            }
        }
        if ($loan_id <= 0) {
            exit(json_encode(['status' => '1006', 'msg' => '借款信息错误']));
        }
        $loaninfo = User_loan::findOne($loan_id);
        
         //是否可以还款校验
        $chk_repay = (new Loan_repay())->check_repay($loaninfo);
        if (!$chk_repay) {
            exit(json_encode(['status' => '1007', 'msg' => '借款信息不存在']));
        }
        //获取应还款的金额
        $huankuan_money_check = (new User_loan())->getRepaymentAmount($loaninfo);
        $huankuan_money = isset($postData['money']) ? $postData['money'] : $huankuan_money_check;
        $total_fee = intval($huankuan_money * 100);
        if ($total_fee <= 0) {
            exit(json_encode(['status' => '1008', 'msg' => '还款金额不正确']));
        }
        //判断是否逾期
        if (in_array($loaninfo['business_type'], [5, 6])) {
            //判断是否已逾期
            $overdueLoan = (new GoodsBill())->find()->where(['loan_id' => $postData['loan_id'], 'bill_status' => 12])->one();
            if (!empty($overdueLoan)) {
                $type = 2; //代表逾期
            } else {
                $type = 1;  //未逾期
            }
        } else {
            $type = (in_array($loaninfo->status, [12, 13])) ? 2 : 1;
        }
        $type = 2;
        $addres = $this->addRepay($loan_id, $loaninfo->user_id, $orderid, $huankuan_money, $type);

        if (!$addres) {
            exit(json_encode(['status' => '1009', 'msg' => '还款记录创建失败']));
        }
        
        $orderid = Loan_repay::findOne($addres);
        if (empty($orderid->repay_id) || !isset($orderid->repay_id)) {
            exit(json_encode(['status' => '1010', 'msg' => '请求还款失败']));
        }
        if($coupon_id){
            //添加使用优惠券
            $coupon_use_condition=array(
                'user_id'=>$loaninfo->user_id,
                'discount_id'=>$coupon_id,
                'repay_id'=>$addres,
                'repay_amount'=>$huankuan_money,
                'repay_status'=>0,//初始
                'coupon_amount'=>$coupon_amount,
                'loan_id'=>$loan_id,
            );
            $repay_coupon_use=new RepayCouponUse();
            $res=$repay_coupon_use->addRecord($coupon_use_condition);
        }
        $orderid = $orderid->repay_id;
        $wxPay = new Wxpay($type);
        $wxPayUrl = $wxPay->getWxpayUrl($orderid, $huankuan_money);
        exit(json_encode(['status' => 0, 'url' => $wxPayUrl]));
    }

    //微信提交时，在还款表中添加一条记录
    private function addRepay($loan_id, $user_id, $orderid, $total_fee, $type) {
        if ($type == 2) {
            $platform = 7;
        } else {
            $platform = 4;
        }
        $money = floatval($total_fee);
        $loan_repay = new Loan_repay();
        $condition = array(
            'repay_id' => '',
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'money' => $money,
            'platform' => $platform, //微信支付
            'source' => 1, //公众号
        );
        $ret = $loan_repay->addRepay($condition);
        return $ret;
    }

    /**
     * 开放平台微信支付
     * @return \yii\web\Response
     */
    public function actionWxpaynew()
    {
        $post_data = $this->post();
        if (empty($post_data['loan_id']) || $post_data['loan_id'] <= 0) {
            exit(json_encode(['status' => '1006', 'msg' => '借款信息错误']));
        }
        $loan_id = $post_data['loan_id'];
        $loaninfo = User_loan::findOne($loan_id);
        if (empty($loaninfo)) {
            exit(json_encode(['status' => '1007', 'msg' => '借款信息不存在']));
        }
        //获取用户id
        if (empty($loaninfo->user_id)) {
            exit(json_encode(['status' => '1008', 'msg' => '获取user_id失败']));
        }
        //获取用户还款金额
        if (empty($post_data['money']) || !is_numeric($post_data['money'])) {
            exit(json_encode(['status' => '1009', 'msg' => '还款金额信息错误']));
        }
        //判断借款状态
        $repay_satus = [9, 12, 13];
        if (!in_array($loaninfo->status, $repay_satus)) {
            exit(json_encode(['status' => '1010', 'msg' => '借款状态错误']));
        }
        //判断还款金额是否为0
        if ($post_data['money'] <= 0) {
            exit(json_encode(['status' => '1011', 'msg' => '还款金额不能小于0']));
        }
        $coupon_id = isset($postData['coupon_id']) ? intval($postData['coupon_id']) : 0;
        if($coupon_id){
            $coupon_list_one =(new Coupon_list())->getCouponById($coupon_id);
            if($coupon_list_one){
                $coupon_amount=$coupon_list_one->val;
                $repay_coupon_use_info=(new RepayCouponUse())->getByDiscountId($coupon_id);
                if(!empty($repay_coupon_use_info)){
                    exit(json_encode(['status' => '1111', 'msg' => '该优惠券已使用或者使用中']));
                }
            }else{
                exit(json_encode(['status' => '1112', 'msg' => '该优惠券已使用或者使用中']));
            }
        }
        //是否是分期     是否可以还款校验
        if (in_array($loaninfo['business_type'], [5, 6])) {
            $chk_repay = (new BillRepay())->check_repay($loaninfo);
        } else {
            $chk_repay = (new Loan_repay())->check_repay($loaninfo);
        }
        if (!$chk_repay) {
            return $this->redirect('/new/repay/error');
        }
        $user = User::findOne($loaninfo['user_id']);
        $user_id = $user->user_id;
        $money = isset($post_data['money']) ? floatval($post_data['money']) * 100 : '';
        $bank = User_bank::find()->where(['user_id' => $user_id])->one();
        $card_id = $bank->id;
        //是否是分期
        if (in_array($loaninfo['business_type'], [5, 6])) {
            //看是否已逾期
            $isOverdue = GoodsBill::find()->where(['loan_id' => $loan_id, 'bill_status' => 12])->one();
            $loan_repay = new BillRepay();
            //查询！=8分期账单
            $bill = (new GoodsBill())->getLatelyPhase($loan_id);
            $condition = [
                'bank_id' => $card_id,
                'user_id' => $user_id,
                'loan_id' => $loan_id,
                'bill_id' => $bill['bill_id'],
                'left_money' => 0,
                'status' => 0,
                'actual_money' => isset($post_data['money']) ? floatval($post_data['money']) : '',
                'platform' => !empty($isOverdue) ? 8 : 5,
                'source' => 1,
            ];
            Logger::errorLog(print_r($condition, true), 'fenqi_huankuan_qingqiu_wx');
            $ret = $loan_repay->saveRepayInfo($condition);
            if (!$ret) {
                return $this->redirect('/new/repay/error');
            }
            $repay_order = BillRepay::findOne($ret);
            if (empty($repay_order->repay_id) || !isset($repay_order->repay_id)) {
                return $this->redirect('/new/repay/error');
            }
        } else {
            $isOverdue = OverdueLoan::find()->where(['loan_id'=>$loaninfo->loan_id])->one();
            $loan_repay = new Loan_repay();
            $condition = [
                'repay_id' => '',
                'user_id' => $user_id,
                'loan_id' => $loan_id,
                'bank_id' => $card_id,
                'money' => isset($post_data['money']) ? floatval($post_data['money']) : '',
                'platform' => !empty($isOverdue) ? 8 : 5,
            ];
            Logger::errorLog(print_r($condition, true), 'huankuan_qingqiu_wx');
            $ret = $loan_repay->save_repay($condition);
            if (!$ret) {
                return $this->redirect('/new/repay/error');
            }
            $repay_order = Loan_repay::findOne($ret);
            if (empty($repay_order->repay_id) || !isset($repay_order->repay_id)) {
                return $this->redirect('/new/repay/error');
            }
        }
        $orderid = $repay_order->repay_id;
        $card_type = ($bank->type == 0) ? 1 : 2;
        $phone = isset($bank->bank_mobile) ? $bank->bank_mobile : $user->mobile;
        $business_code = $isOverdue ? "WXPAYTJYX" : "WXPAY";
        $postData = array(
            'orderid' => $orderid, // 请求唯一号
            'identityid' => (string) $user_id, // 用户标识
            'bankname' => $bank->bank_name, //银行名称
            'bankcode' => $bank->bank_abbr, //银行编码
            'card_type' => $card_type, // 卡类型
            'cardno' => $bank->card, // 银行卡号
            'idcard' => $user->identity, // 身份证号
            'username' => $user->realname, // 姓名
            'phone' => $phone, // 预留手机号
            'productcatalog' => '7', // 商品类别码
            'productname' => '购买电子产品', // 商品名称
            'productdesc' => '购买电子产品', // 商品描述
            'amount' => $money, // 交易金额
            'orderexpdate' => 60,
            'business_code' => $business_code,
            'userip' => $_SERVER["REMOTE_ADDR"], //ip
            'callbackurl' => Yii::$app->params['newdev_notify_url'], // 异步回调地址
        );
        Logger::errorLog(print_r($postData, true), 'openpay');
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('payment/pay', $postData, 2);
        Logger::errorLog(print_r($res, true), 'huankuan_wxpay_url');
        $res = json_decode($res,true);
        if($res['res_code'] != '0000'){
            exit(json_encode(['status' => $res['res_data'], 'msg' => $res['res_data']]));
        }
        if($coupon_id){
            //添加使用优惠券
            $coupon_use_condition=array(
                'user_id'=>$user_id,
                'discount_id'=>$coupon_id,
                'repay_id'=>$loan_repay->id,
                'repay_amount'=>isset($post_data['money']) ? floatval($post_data['money']) : '',
                'repay_status'=>0,//还款中
                'coupon_amount'=>$coupon_amount,
                'loan_id'=>$loan_id,
            );
            $repay_coupon_use=new RepayCouponUse();
            $res=$repay_coupon_use->addRecord($coupon_use_condition);
        }
        $wxPayUrl = $res['res_data'];
        exit(json_encode(['status' => 0, 'url' => $wxPayUrl['url']]));
    }
}

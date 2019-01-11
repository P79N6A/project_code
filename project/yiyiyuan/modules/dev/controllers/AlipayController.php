<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\models\news\Loan_repay;
use app\models\dev\User_loan;
use app\models\news\User_loan as User_loan2;
use app\models\dev\User;
use app\commonapi\Logger;
use app\models\dev\ApiSms;
use app\commonapi\Alipay;
use Yii;

class AlipayController extends SubController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $postData = Yii::$app->request->post();
        Logger::dayLog('alipay', $postData);
        if (empty($postData['loan_id']) || $postData['loan_id'] <= 0) {
            exit('借款信息错误');
        }
        $loaninfo = User_loan::findOne($postData['loan_id']);
        if (empty($loaninfo)) {
            exit('借款信息不存在');
        }
        //获取用户id
        if (empty($loaninfo->user_id)) {
            exit('获取user_id失败');
        }
        //获取用户还款金额
        if (empty($postData['money']) || !is_numeric($postData['money'])) {
            exit('还款金额信息错误');
        }
        //获取来源$postData['source']
        if (empty($postData['source'])) {
            exit('来源信息错误');
        }
        //判断借款状态
        $repay_satus = [9, 12, 13];
        if (!in_array($loaninfo->status, $repay_satus)) {
            exit('借款状态错误');
        }

        //生成还款订单并保存
        $loanRepayModel = new Loan_repay();
        $condition = array(
            'repay_id' => date('YmdHis') . rand(1000, 9999),
            'user_id' => $loaninfo->user_id,
            'loan_id' => $postData['loan_id'],
            'money' => $postData['money'],
            'platform' => 5, //支付宝支付
            'source' => $postData['source'], //还款来源（5 android；6 IOS）
        );
        $repay_res = $loanRepayModel->save_repay($condition);
        if (!$repay_res) {
            exit(json_encode(['status' => '1009', 'msg' => '还款记录创建失败']));
        }

        $aliPay = new Alipay();
        $aliPayURL = $aliPay->getAlipayUrl($condition['repay_id'], $condition['money']);

        $this->layout = 'alipay';
        $this->getView()->title = '支付中';
        return $this->render('index', [
                    'aliPayURL' => $aliPayURL
        ]);
    }

    //接收异步通知
    public function actionNotify() {
        $postData = Yii::$app->request->post();

        if (empty($postData)) {
            exit();
        }
        $msg_arr = json_decode($postData['msg'], true);

        //校验还款状态
        $loan_repay = Loan_repay::find()->where(['repay_id' => $postData['merchantOutOrderNo']])->one();
        if (empty($loan_repay) || in_array($loan_repay->status, [1])) {
            $this->sendSuccess('还款状态错误' . $postData['merchantOutOrderNo']);
        }

        //校验还款金额
        $total_fee = isset($msg_arr['payMoney']) ? $msg_arr['payMoney'] : 0;
        //校验还款金额和返回金额是否一致
        if (intval($loan_repay->money) < intval($msg_arr['payMoney'])) {
            $this->sendSuccess('还款金额错误' . $postData['merchantOutOrderNo']);
        }

        //校验借款信息
        $loan_id = $loan_repay->loan_id;
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo) || $loaninfo->status == 8) {
            $this->sendSuccess('借款信息错误' . $postData['merchantOutOrderNo']);
        }

        //校验用户信息
        $userModel = new User();
        $userinfo = $userModel->find()->select(['user_id', 'mobile'])->where(['user_id' => $loaninfo['user_id']])->one();
        if (empty($userinfo)) {
            $this->sendSuccess('用户信息错误' . $postData['merchantOutOrderNo']);
        }

        //获取应还款的金额
        $newLoaninfo = User_loan2::findOne($loaninfo->loan_id);
        $huankuan_money = $newLoaninfo->getRepaymentAmount($newLoaninfo);

        $sms = new ApiSms();
        //回调返回还款失败
        if (!$postData['payResult']) {
            $res = $sms->sendRepaymentFailedSms($userinfo['mobile'], $huankuan_money);
            $this->sendSuccess('还款失败->' . $postData['merchantOutOrderNo']);
        }
        //记录接口返回成功的数据
        Logger::errorLog(print_r($postData, true), 'notifysuccess', 'alipay_notify');

        $times = date('Y-m-d H:i:s');
        if ($huankuan_money <= 0) {
            $res = $sms->sendRepaymentFailedSms($userinfo['mobile'], $huankuan_money);
            $this->sendSuccess('应还款金额错误->' . $postData['merchantOutOrderNo']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        //修改还款信息
        $repayRes = $this->updateRepay($loan_repay, $msg_arr['tradeNo'], $total_fee, $times);
        if (!$repayRes) {
            $transaction->rollBack();
            //发送还款失败通知
            $res = $sms->sendRepaymentFailedSms($userinfo['mobile'], $huankuan_money);
            $this->sendSuccess('修改还款信息失败->' . $postData['merchantOutOrderNo']);
        }
        //全额还款(应还款金额=实际还款金额) 修改借款状态为已完成
        Logger::dayLog('Wxpay', "huankuan_money", $huankuan_money);
        Logger::dayLog('Wxpay', "payMoney", $msg_arr['payMoney']);
        Logger::dayLog('Wxpay', "in_payMoney", intval($msg_arr['payMoney']));
        Logger::dayLog('Wxpay', "in_huankuan_money", intval($huankuan_money));
        if ($huankuan_money <= $msg_arr['payMoney']) {
            $status = 8;
            $loanres = $loaninfo->changeStatus($status);
            $loanresult = $loaninfo->updateUserLoan(['repay_type' => 2, 'repay_time' => $times]);
            if ($loanres == false || $loanresult == false) {
                $transaction->rollBack();
                $res = $sms->sendRepaymentFailedSms($userinfo['mobile'], $huankuan_money);
                $this->sendSuccess('修改借款状态失败->' . $postData['merchantOutOrderNo']);
            }
            $userinfo->inputWhite($userinfo['user_id']);
            //用户加入白名单
        }
        $transaction->commit();
        
        if($huankuan_money <= $msg_arr['payMoney']){
            $res = $sms->sendRepaymentAllSms($userinfo['mobile']);
        } else {
            $res = $sms->sendRepaymentPortionSms($userinfo['mobile'], $total_fee, $huankuan_money - $total_fee);
        }
        $this->sendSuccess('');
    }

    private function sendSuccess($info) {
        if (!empty($info)) {
            Logger::errorLog(print_r($info, true), 'notifyfaild', 'alipay_notify');
        }
        echo 'success';
        exit();
    }

    //修改还款信息
    private function updateRepay($repay, $transaction_id, $total_fee, $times) {
        $params['status'] = 1;
        $params['actual_money'] = round($total_fee, 2);
        $params['paybill'] = $transaction_id;
        $params['repay_time'] = $times;
        return $repay->update_repay($params);
    }

}

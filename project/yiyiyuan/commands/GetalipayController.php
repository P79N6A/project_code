<?php

/**
 * 查询支付宝支付结果
 *
 *   linux : /data/wwwroot/yiyiyuan/yii automation sent
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii automation sent
 *
 */

namespace app\commands;

use app\models\dev\Loan_repay;
use app\models\news\User_loan;
use app\models\dev\User;
use app\models\dev\ApiSms;
use app\commonapi\Alipay;
use app\commonapi\Logger;
use yii\console\Controller;
use Yii;

class GetalipayController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
//        $starttime = date("Y-m-d H:i:s", strtotime("-20 minute"));
//        $endtime = date("Y-m-d H:i:s", strtotime("-10 minute"));
        $starttime = '2017-08-24 01:00:00';
        $endtime = '2017-09-18 23:59:00';

        $where = [
            'AND',
            ["status" => 0],
            ['platform' => [4, 5, 7, 8]],
            ['between', 'createtime', $starttime, $endtime]
        ];
        $total = Loan_repay::find()->where($where)->count();
        $limit = 1000;
        $page = ceil($total / $limit);
        $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$page}次\n");
        if ($total <= 0) {
            exit;
        }
        Logger::dayLog('loanrepay', 'send', $starttime . ' to ' . $endtime, ' 获取user_repay条数', $total);
        for ($i = 0; $i < $page; $i++) {
            //查询aplipay结果
            $loan_repay = Loan_repay::find()->where($where)->offset($i * $limit)->limit($limit)->all();
            Logger::dayLog('loanrepay', 'send', $starttime . ' to ' . $endtime, ' 获取user_repay条数', $total);
            if (!empty($loan_repay)) {
                foreach ($loan_repay as $key => $val) {

                    $loan_id = $val->loan_id;
                    $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
                    $chase_amount = $loaninfo->getChaseamount($loaninfo->user_id);
                    $is_yq = ($chase_amount > 0) ? TRUE : FALSE;
                    $aliPay = new Alipay($is_yq);
                    if ($val['platform'] == 4 || $val['platform'] == 7) {
                        $platform = "wx";
                    } else {
                        $platform = "al";
                    }
                    $result = $aliPay->getPayRes($val['repay_id'], $platform);

                    $res = json_decode($result, true);
                    if (!$result || empty($res['payResult'])) {
                        continue;
                    }
                    $this->updateLoanrepay($res);
                }
            }
        }
    }

    /**
     * 更新还款记录
     */
    private function updateLoanrepay($repay) {
        //校验还款状态
        $loan_repay = Loan_repay::find()->where(['repay_id' => $repay['merchantOutOrderNo']])->one();
        if (empty($loan_repay) || in_array($loan_repay->status, [1])) {
            Logger::errorLog(print_r('还款状态错误' . $repay['merchantOutOrderNo'], true), 'notifyfaild', 'alipay_notify');
            return;
        }

        //校验还款金额
        $total_fee = isset($repay['orderMoney']) ? $repay['orderMoney'] : 0;
        //校验还款金额和返回金额是否一致
        if (intval($loan_repay->money) < intval($repay['orderMoney'])) {
            Logger::errorLog(print_r('还款金额错误' . $repay['merchantOutOrderNo'], true), 'notifyfaild', 'alipay_notify');
            return;
        }

        //校验借款信息
        $loan_id = $loan_repay->loan_id;
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            Logger::errorLog(print_r('借款信息错误' . $repay['merchantOutOrderNo'], true), 'notifyfaild', 'alipay_notify');
            return;
        }

        //校验用户信息
        $userModel = new User();
        $userinfo = $userModel->find()->select(['user_id', 'mobile'])->where(['user_id' => $loaninfo['user_id']])->one();

        if (empty($userinfo)) {
            Logger::errorLog(print_r('用户信息错误' . $repay['merchantOutOrderNo'], true), 'notifyfaild', 'alipay_notify');
            return;
        }

        //获取应还款的金额
        $newLoaninfo = \app\models\news\User_loan::findOne($loaninfo->loan_id);
        $huankuan_money = $newLoaninfo->getRepaymentAmount($loaninfo);

        $sms = new ApiSms();

        $times = date('Y-m-d H:i:s');
        if ($huankuan_money <= 0) {
            $res = $sms->sendRepaymentFailedSms($userinfo['mobile'], $huankuan_money);
            Logger::errorLog(print_r('应还款金额错误->' . $repay['merchantOutOrderNo'], true), 'notifyfaild', 'alipay_notify');
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        //修改还款信息
        $repayRes = $this->updateRepay($loan_repay, $repay['tradeNo'], $total_fee, $times);
        if (!$repayRes) {
            $transaction->rollBack();
            //发送还款失败通知
            $res = $sms->sendRepaymentFailedSms($userinfo['mobile'], $huankuan_money);
            Logger::errorLog(print_r('修改还款信息失败->' . $repay['merchantOutOrderNo'], true), 'notifyfaild', 'alipay_notify');
            return;
        }
        //全额还款(应还款金额=实际还款金额) 修改借款状态为已完成
        if ($huankuan_money <= $repay['orderMoney']) {
            $status = 8;
            $loanres = $loaninfo->changeStatus($status);
            $loanresult = $loaninfo->update_userLoan(['repay_type' => 2, 'repay_time' => $times]);
            if ($loanres == false || $loanresult == false) {
                $res = $sms->sendRepaymentFailedSms($userinfo['mobile'], $huankuan_money);
                Logger::errorLog(print_r('修改借款状态失败->' . $repay['merchantOutOrderNo'], true), 'notifyfaild', 'alipay_notify');
                $transaction->rollBack();
            }
            //用户加入白名单
            $userinfo->inputWhite($userinfo['user_id']);
        }
        $transaction->commit();
        if ($huankuan_money <= $repay['orderMoney']) {
            $res = $sms->sendRepaymentAllSms($userinfo['mobile']);
        } else {
            $res = $sms->sendRepaymentPortionSms($userinfo['mobile'], $total_fee, $huankuan_money - $total_fee);
        }
        //记录接口返回成功的数据
        Logger::errorLog(print_r($repay, true), 'notifysuccess', 'alipay_notify');
    }

    //修改还款信息
    private function updateRepay($repay, $transaction_id, $total_fee, $times) {
        $params['status'] = 1;
        $params['actual_money'] = round($total_fee, 2);
        $params['paybill'] = $transaction_id;
        $params['repay_time'] = $times;
        return $repay->updateRepay($params);
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

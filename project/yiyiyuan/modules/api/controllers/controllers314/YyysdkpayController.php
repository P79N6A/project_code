<?php

namespace app\modules\api\controllers\controllers314;

use app\commonapi\Wechatpay;
use app\models\news\Loan_renew_user;
use app\models\news\Loan_repay;
use app\models\news\Renewal_payment_record;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

/**
 * 微信公众号支付
 */
class YyysdkpayController extends ApiController {

    public $enableCsrfValidation = false;
    private $paytype = 'sdk';

    /**
     * 提交订单信息
     */
    public function actionIndex() {
        $res = $this->returnBack('99999','','微信支付维护中，请选择银行卡还款');
        echo $res;
        exit;
        
        $version = Yii::$app->request->post('version');
        $orderid = '';
        $loan_id = intval(Yii::$app->request->post('loan_id'));
        $money = floatval(Yii::$app->request->post('money'));
        $source = Yii::$app->request->post('source', 5);
        $type = Yii::$app->request->post('type', 1);
        if (empty($version) || empty($loan_id)) {
            $res = $this->returnBack('99994');
            echo $res;
            exit;
        }
        if ($type == 1 && empty($money)) {
            $res = $this->returnBack('99994');
            echo $res;
            exit;
        }

        if ($loan_id <= 0) {
            $res = $this->returnBack('10081');
            echo $res;
            exit;
        }

        $loaninfo = User_loan::findOne($loan_id);
        if (empty($loaninfo) || $loaninfo->status == 8) {
            $res = $this->returnBack('10082');
            echo $res;
            exit;
        }

        if ($type == 1) {
            $result = $this->addRepay($loaninfo->loan_id, $loaninfo->user_id, $orderid, $money, $source);
            $total_fee = 100 * $money;
            if (!$result) {
                $res = $this->returnBack('10085');
                echo $res;
                exit;
            } else {
                $params['total_fee'] = $total_fee;
                $params['out_trade_no'] = $orderid;
                $params['mch_create_ip'] = Yii::$app->request->userIP;
                $params['body'] = '购买电子产品';
                $service = new Wechatpay(SYSTEM_ENV == 'prod' ? 'Config_pro' : 'Config_test', $this->paytype);
                $res = $service->submitOrderInfo($params);
                $array['token_id'] = 0;
                if ($res['status'] == 0) {
                    $array['token_id'] = $res['token_id'];
                    $res = $this->returnBack('0000',$array);
                    echo $res;
                    exit;
                } else {
                    $res = $this->returnBack($res['status'],'',$array['rsp_msg']);
                    echo $res;
                    exit;
                }
            }
        } else {
            $this->paytype = 'renewal';
            $renewUserModel = new Loan_renew_user();
            if (!$renewUserModel->chooseRenewUser($loaninfo)) {
                $array = $this->returnBack('10090');
                echo $array;
                exit;
            }
            $money = $loaninfo->getRenewalMoney($loan_id);
            $result = $this->addRenewal($loaninfo, $orderid, $money, $source);
            if (!$result) {
                $array = $this->returnBack('10085');
                echo $array;
                exit;
            }
        }
        $array = $this->pay($money, $orderid);
        echo $array;
        exit;
    }

    private function pay($money, $orderid) {
        $total_fee = intval($money * 100);
        $params['total_fee'] = $total_fee;
        $params['out_trade_no'] = $orderid;
        $params['mch_create_ip'] = Yii::$app->request->userIP;
        $params['body'] = '购买电子产品';
        $service = new Wechatpay(SYSTEM_ENV=='prod' ? 'Config_pro' : 'Config_test', $this->paytype);
        $res = $service->submitOrderInfo($params);
        if ($res['status'] == 0) {
            $array = $this->returnBack('0000', $res['token_id']);
            return $array;
        } else {
            $array['rsp_code'] = $res['status'];
            $array['rsp_msg'] = $res['msg'];
            $array = $this->returnBack( $res['status'], '', $res['msg']);
            return $array;
        }
    }

    /*
     * 微信提交时，在还款表中添加一条记录
     */

    private function addRepay($loan_id, $user_id, $orderid, $total_fee, $source) {
        $user = User::find()->where(['user_id' => $user_id])->one();
        $user_id = $user['user_id'];
        $money = floatval($total_fee);
        $loan_repay = new Loan_repay();
        $condition = array(
            'repay_id' => $orderid,
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'money' => $money,
            'platform' => 4, //微信支付
            'source' => $source, //app
        );
        $ret = $loan_repay->save_repay($condition);
        return $ret;
    }

    /*
     * 微信提交时，在还款表中添加一条记录
     */
    private function addRenewal($loan, $orderid, $total_fee, $source) {
        $money = floatval($total_fee);
        $loan_renewal = new Renewal_payment_record();
        $data = [
            'loan_id' => $loan->loan_id,
            'order_id' => $orderid,
            'parent_loan_id' => $loan->parent_loan_id ? $loan->parent_loan_id : 0,
            'user_id' => $loan->user_id,
            'bank_id' => NULL,
            'platform' => 4,
            'source' => $source,
            'money' => $money,
        ];
        $ret = $loan_renewal->save_batch($data);
        return $ret;
    }

    /**
     * 查询订单
     */
    public function actionQueryorder() {
        $version = Yii::$app->request->post('version');
        $out_trade_no = intval(Yii::$app->request->post('out_trade_no'));
        $transaction_id = intval(Yii::$app->request->post('transaction_id'));

        if (empty($version) || empty($out_trade_no) || empty($transaction_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $params = [
            'out_trade_no' => $out_trade_no,
            'transaction_id' => $transaction_id,
        ];
        $service = new Wechatpay(SYSTEM_ENV=='prod' ? 'Config_pro' : 'Config_test', $this->paytype);
        $res = $service->queryOrder($params);
        if ($res['status'] == 0) {
            $array = $this->returnBack('0000');
        } else {
            $array = $this->returnBack($res['status'], '', $res['msg']);
        }
        exit($array);
    }
}

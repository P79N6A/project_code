<?php

namespace app\modules\newdev\controllers;

use Yii;
use app\commonapi\Wechatpay;
use app\models\news\User_loan;
use app\models\news\Loan_repay;
use app\models\news\User;

/**
 * 微信公众号支付
 */
class YyygzhpayController extends NewdevController {

    private $paytype = 'gzh';

    /**
     * 提交订单信息
     */
    public function actionSubmitorderinfo() {
        $psstArr   = $this->post();
        $orderid   = date('YmdHis') . rand(1000, 9999);
        $loan_id   = intval($psstArr['loan_id']);
        if($loan_id <=0){
            exit(json_encode(['status' => '1006', 'msg' => '借款信息错误']));
        }
        
        $loaninfo = User_loan::findOne($loan_id);
        if(empty($loaninfo)){
            exit(json_encode(['status' => '1007', 'msg' => '借款信息不存在']));
        }
        
        //获取用户Openid
        $userInfo = User::findOne($loaninfo->user_id);
        $openid = $userInfo->openid;
        if(empty($openid)){
            exit(json_encode(['status' => '1010', 'msg' => '获取openid失败']));
        }
        
        //获取应还款的金额
        $huankuan_money_check = $loaninfo->getRepaymentAmount($loaninfo);
        $huankuan_money = isset($psstArr['money']) ? $psstArr['money'] : $huankuan_money_check;
        $total_fee = intval($huankuan_money * 100);
        if($total_fee <= 0){
            exit(json_encode(['status' => '1008', 'msg' => '还款金额不正确']));
        }
        $addres    = $this->addRepay($loan_id, $loaninfo->user_id , $orderid, $huankuan_money);
        if (!$addres) {
            exit(json_encode(['status' => '1009', 'msg' => '还款记录创建失败']));
        } else {
            $orderid = Loan_repay::findOne($addres);
            $orderid = $orderid->repay_id;
            $params['total_fee']     = $total_fee;
            $params['out_trade_no']  = $orderid;
            $params['mch_create_ip'] = Yii::$app->request->userIP;
            $params['sub_openid']    = SYSTEM_ENV == 'prod' ? $openid : $openid;
            $params['body']          = '购买电子产品';
            $service                 = new Wechatpay(SYSTEM_ENV == 'prod' ? 'Config_pro':'Config_test', $this->paytype);
            $res                     = $service->submitOrderInfo($params);
            exit(json_encode($res));
        }
    }

    /**
     * 微信提交时，在还款表中添加一条记录
     * @param $loan_id 借款id
     * @param $user_id 用户id
     * @param $orderid 订单编号
     * @param $total_fee 还款金额
     * @return bool
     */
    private function addRepay($loan_id , $user_id, $orderid, $total_fee) {
        $user       = User::find()->where(['user_id' => $user_id])->one();
        $user_id    = $user['user_id'];
        $money      = floatval($total_fee);
        $loan_repay = new Loan_repay();
        $condition  = array(
            'repay_id' => '',
            'user_id'  => $user_id,
            'loan_id'  => $loan_id,
            'money'    => $money,
            'platform' => 4,   //微信支付
            'source'   => 1,   //公众号
        );
        $ret = $loan_repay->save_repay($condition);
        return $ret;
    }

}

?>
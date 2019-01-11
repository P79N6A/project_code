<?php

namespace app\modules\newdev\controllers;

use app\commonapi\ApiSign;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\Exchange;
use app\models\news\GoodsLoan;
use app\models\news\Payaccount;
use app\models\news\PayAccountError;
use app\models\news\Push_not_withdrawals;
use app\models\news\Push_yxl;
use app\models\news\SmsSend;
use app\models\news\UmengSend;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_remit_list;
use app\models\news\YiLoanNotify;
use app\models\service\UserloanService;
use app\models\dev\Activity_newyear;
use app\models\news\User_loan_flows;
use Yii;

class NotifydebtController extends NewdevController {

    public $enableCsrfValidation = false;

    const FREE_WITHDRAW_TYPE = 1; //免密提现
    const WITHDRAW_TYPE = 2; //提现
    const REMIT_TYPE = 3; //放款
    const END_CLAIM_TYPE = 4; //结束债权
    const RENEW_TYPE = 18; //展期债权起息日到期日
    const REJECT_TYPE = 36;

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $type = $this->get('type', 0);
        if (empty($type)) {
            exit;
        }
//      $postData = '{"loan_id":223728750,"request_no":"201810090627247237","res_status":6,"error_code":"txcgfail001","remit_time":"2018-12-21 00:00:00","error_msg":"存管卡不支持提现，导致借款驳回"}';
       
        $postData = $this->post('data');
        $sign = $this->post('_sign');
        Logger::dayLog('debt', $type, $postData, $sign);
        if (empty($postData) || empty($sign)) {
            Logger::dayLog('debt', $type, '参数错误');
            exit('error1');
        }
        $apiSignModel = new ApiSign();
        $verify = $apiSignModel->verifyData($postData, $sign);
        if (!$verify) {
            Logger::dayLog('debt', $type, '验签失败');
            exit('sign error');
        }
        $result = json_decode($postData, true);
        if (!$result || empty($result)) {
            Logger::dayLog('debt', $type, '参数json解析错误');
            exit('error empty1');
        }

        switch ($type) {
            case 1 :
                $this->handwithdraw($result);
                break;
            case 2 :
                $this->handwithdraw($result);
                break;
            case 3 :
                $this->debtresult($result);
                break;
            case 4 :
                $this->loanrepay($result);
                break;
            case 18:
                $this->renewloan($result);
                break;
            case 36:
                $this->loanreject($result);
                break;
            default :
                exit;
        }
    }

    /**
     * 授权展期起息日记录
     * @param type $result
     */
    public function renewloan($result) {
        if (!is_array($result) || !isset($result['loan_id']) || !isset($result['res_status'])) {
            Logger::dayLog('debt/notifyError', '参数为空renewloan');
        }
        $loan = (new User_loan())->getLoanById($result['loan_id']);
        if (empty($loan)) {
            echo 'FAIL';
            exit;
        }
        if (isset($result['begin_date']) && isset($result['end_date'])) {
            $end_date = date('Y-m-d', strtotime($result['begin_date']) + ($loan->days * 86400));
            $rsp = $loan->update_userLoan(['start_date' => $result['begin_date'], 'end_date' => $end_date]);
            if (!$rsp) {
                Logger::dayLog('debt/notifyError', $result['loan_id'], 'renewloan', $rsp);
                echo 'FAIL';
                exit;
            }
            $ex_rsp = (new Exchange())->add_list(['loan_id' => $loan->loan_id, 'exchange' => 0, 'type' => 1]);
            echo 'SUCCESS';
            exit;
        }
        echo 'FAIL';
        exit;
    }

    /**
     * 接收债匹结果结果
     */
    public function debtresult($result) {
        if (!is_array($result) || !isset($result['loan_id']) || !isset($result['res_status'])) {
            Logger::errorLog(print_r([0 => "参数为空3"], true), 'NotifyError', 'debt');
        }
        $remit_time = '';
        if (isset($result['remit_time'])) {
            $remit_time = $result['remit_time'];
        }
        $res = $this->todo($result['loan_id'], $result['res_status'], $remit_time);
        Logger::errorLog(print_r([$result['loan_id'] => $res], true), 'NotifyError', 'debt');
        if (!$res) {
            echo 'FAIL';
            exit;
        }
        echo "SUCCESS";
        exit;
    }

    /**
     * 手动提现结果接收
     * @param type $result
     */
    public function handwithdraw($result) {
        if ($result['res_status'] == 6) {//出款成功
            $status = 'SUCCESS';
        } else if ($result['res_status'] == 11) {
            $status = 'FAIL';
        } else if ($result['res_status'] == 12) {
            $status = 'WILLREMIT';
        } else {
            exit;
        }
        $cgModel = new Cg_remit();
        $cgRemit = $cgModel->getByLoanId($result['loan_id']);
        if (empty($cgRemit) || $cgRemit->remit_status != 'DOREMIT') {
            Logger::errorLog(print_r([$result['loan_id'] => "存管出款子表=》不存在或者状态不为DOREMIT"], true), 'GetmoneyNotifyErrorNew', 'debt');
            exit;
        }
        if ($status == 'FAIL') {
            if(!empty($result['error_code'])){
                $res_json=[
                    'error_code'=>$result['error_code'],
                    'error_msg'=>$result['error_msg'],
                ];
                $res_json=json_encode($res_json,true);
                $err_condition=[
                    'user_id'=>$cgRemit->user_id,
                    'type'=>6,
                    'res_code'=>$result['error_code'],
                    'res_json'=>$res_json,
                    'res_msg'=>$result['error_msg'],
                    'status'=>0,
                ];
                $res=(new PayAccountError())->save_error($err_condition);
            }
            $fail = $cgRemit->outMoneyFail('1', 'outmoneyfail');
            if (!$fail) {
                Logger::errorLog(print_r([$result['loan_id'] => "存管出款子表=》FAIL状态修改失败"], true), 'GetmoneyNotifyErrorNew', 'debt');
                exit;
            }
            $this->saveNewSmsSend(3, $cgRemit);
        } elseif ($status == 'SUCCESS') {
            if(empty($result['error_code'])){
                $res_json=[
                    'error_code'=>'00000000',
                    'error_msg'=>'交易成功',
                ];
                $res_json=json_encode($res_json,true);
                $err_condition=[
                    'user_id'=>$cgRemit->user_id,
                    'type'=>6,
                    'res_code'=>'00000000',
                    'res_json'=>$res_json,
                    'res_msg'=>'交易成功',
                    'status'=>0,
                ];
                $res=(new PayAccountError())->save_error($err_condition);
            }
            $success = $cgRemit->outMoneySuccess();
            if (!$success) {
                Logger::errorLog(print_r([$result['loan_id'] => "存管出款子表=》SUCCESS状态修改失败"], true), 'GetmoneyNotifyErrorNew', 'debt');
                exit;
            }
            $this->saveNewSmsSend(2, $cgRemit);
        } elseif ($status == 'WILLREMIT') {
            $success = $cgRemit->willRemit();
            if (!$success) {
                Logger::errorLog(print_r([$result['loan_id'] => "存管出款子表=》WILLREMIT状态修改失败"], true), 'GetmoneyNotifyErrorNew', 'debt');
                exit;
            }
        } else {
            exit;
        }

        $loan_notify = new YiLoanNotify();
        $loan_notify->saveNotifyRecord($cgRemit->remitlist);

        //出款成功加入分期中间表
        $goods_loan = new GoodsLoan();
        $goods_loan->addSuccessGoodsLoan($cgRemit->remitlist);
        echo 'SUCCESS';
        exit;
    }

    //刚兑结果异步通知
    public function actionExchangenotify() {
        $postData = $this->post('data');
        $postSign = $this->post('_sign');
        Logger::errorLog(print_r([$postData, $postSign], true), 'Notifydebt_exchangenotify', 'debt');
        if (empty($postData) || empty($postSign)) {
            $data_msg = ['rsp_code' => '99990', 'rsp_msg' => '参数错误'];
            Logger::errorLog(print_r(['Notifydebt_exchangenotify' => $data_msg], true), 'NotifyError', 'debt');
            return json_encode($data_msg);
        }
        $signRes = (new ApiSign)->verifyData($postData, $postSign);
        if (!$signRes) {
            $data_msg = ['rsp_code' => '99991', 'rsp_msg' => '签名无效'];
            Logger::errorLog(print_r(['Notifydebt_exchangenotify' => $data_msg], true), 'NotifyError', 'debt');
            return json_encode($data_msg);
        }
        $data = json_decode($postData, TRUE);
        if (empty($data)) {
            $data_msg = ['rsp_code' => '99992', 'rsp_msg' => 'data数据不能为空'];
            Logger::errorLog(print_r(['Notifydebt_exchangenotify' => $data_msg], true), 'NotifyError', 'debt');
            return json_encode($data_msg);
        }
        foreach ($data as $item) {
            if (empty($item) || empty($item['loan_id'])) {
                continue;
            }
            $exchange = (new Exchange)->getByLoanId($item['loan_id']);
            if (empty($exchange)) {
                Logger::dayLog('claim/inputnotify', $item['loan_id'] . '刚兑记录为空');
            }
            $result = $exchange->update_list(['exchange' => 1, 'exchange_date' => date('Y-m-d H:i:s')]);
            if (!$result) {
                Logger::dayLog('claim/inputnotify', $item['loan_id'] . '刚兑记录更新失败');
            }
        }
        $data_msg = ['rsp_code' => '0000', 'rsp_msg' => 'OK'];
        return json_encode($data_msg);
    }

    private function todo($loan_id, $status, $remit_time) {
        $loanModel = new User_loan();
        $loanInfo = $loanModel->getLoanById($loan_id);
        if (!$loanInfo) {
            Logger::dayLog('debt/NotifyError', $loan_id, 'empty_loan');
            return false;
        }
        $userModel = new User();
        $userInfo = $userModel->getUserinfoByUserId($loanInfo->user_id);
        if (!$userInfo) {
            Logger::dayLog('debt/NotifyError', $loan_id, 'empty_user');
            return false;
        }
        $payAccount = new Payaccount();
        $authRes = $payAccount->chkAccountAndAuth($userInfo->user_id);
        if (!$authRes) {
            Logger::dayLog('debt/NotifyError', $loan_id, 'empty_payAccount');
            return false;
        }
        $cgRemitModel = new Cg_remit();
        $cgRemit = $cgRemitModel->getByLoanId($loan_id);
        if (empty($cgRemit) || $cgRemit->remit_status != 'WAITREMIT') {
            $remit_status = empty($cgRemit) ? 'EMPTY' : $cgRemit->remit_status;
            Logger::dayLog('debt/NotifyError', $loan_id, 'error_status' . $remit_status);
            if (!empty($cgRemit) && $cgRemit->remit_status == 'WILLREMIT') {
                return true;
            }
            return false;
        }
        //债匹成功
        if ($status === 6) {
            $willRemit = $cgRemit->willRemit($remit_time);
            if (!$willRemit) {
                Logger::errorLog(print_r(["loanExtendError" => $loan_id], true), 'NotifyError', 'debt');
                return false;
            }
            $loanInfo->refresh();
            if( in_array($loanInfo->business_type, [5, 6, 11])){ //分期 修改goods_bill的起息日 结束日
                $allgoodsbills = $loanInfo->goodsbills;
                $start_time = $loanInfo->start_date;
                $days = 30;
                $end_time = date('Y-m-d 00:00:00', strtotime($start_time . "+" . $days . " days"));
                foreach ($allgoodsbills as $k => $val){
                    $timeUpdateRes = $val->updatetime($start_time,$end_time);
                    if(!$timeUpdateRes){
                        Logger::errorLog( 'goodsbill子订单表起息日 结束日时间修改错误', 'NotifyError', 'debt');
                        return false;
                    }
                    $start_time = $end_time;
                    $end_time = date('Y-m-d 00:00:00', strtotime($start_time . "+" . ($days) . " days"));
                }
                
            }
            //添加推送智融钥匙表放款时间
            $this->addYxl($loanInfo, 2, 6);
            $this->saveNewSmsSend(1, $cgRemit);
            (new UmengSend())->saveUmengSend($loanInfo, 3);
            return true;
        } else if ($status == 11) {
            //债匹失败，借款驳回
            $extend = $loanInfo->loanextend;
            if (!empty($extend)) {
                $service = new UserloanService();
                $service->tbReject($loan_id);
                $remit = User_remit_list::find()->where(['loan_id' => $loan_id, 'remit_status' => 'DOREMIT'])->orderBy('id')->one();
                $remit->updateRemit(['remit_status' => 'REJECT']);
            }
            return true;
        }
    }

    //债匹还款通知
    public function loanrepay($item) {
        if (empty($item) || empty($item['loan_id']) || empty($item['res_status']) || $item['res_status'] != 6) {
            return false;
        }

        $exchange = (new Exchange)->getByLoanId($item['loan_id']);
        if (empty($exchange)) {
            Logger::dayLog('claim/inputnotify', $item['loan_id'] . '刚兑记录为空');
        }
        $result = $exchange->update_list(['exchange' => 1, 'exchange_date' => date('Y-m-d H:i:s')]);
        if (!$result) {
            Logger::dayLog('claim/inputnotify', $item['loan_id'] . '刚兑记录更新失败');
        }

//        $loan = User_loan::findOne($item['loan_id']);
//        $extend = $loan->loanextend;
//        if (!empty($extend) && $extend->status == 'FAIL') {
//            $service = new UserloanService();
//            $a = $service->tbReject($item['loan_id']);
//            $remit = User_remit_list::find()->where(['remit_status' => 'FAIL', 'loan_id' => $item['loan_id']])->orderBy('id')->one();
//            $result = $remit->updateRemit(['remit_status' => 'REJECT']);
//            $this->saveSmsSend($loan);
//        }

        $loan = User_loan::findOne($item['loan_id']);
        $cgRemit = $loan->cgRemit;
        $loanExtend = $loan->loanextend;
        $remitList = $loan->remit;
        if (!$loan || !$cgRemit || !$loanExtend || !$remitList) {
            Logger::dayLog('claim/inputnotify', $item['loan_id'] . '信息不存在');
            return false;
        }

        if ($cgRemit->remit_status == 'FAIL') {//cg_remit的状态是FAIL，
            //直接驳回借款
            $service = new UserloanService();
            $a = $service->tbReject($item['loan_id']);
            $remit = User_remit_list::find()->where(['remit_status' => 'FAIL', 'loan_id' => $item['loan_id']])->orderBy('id')->one();
            $result = $remit->updateRemit(['remit_status' => 'REJECT']);
            $this->saveSmsSend($loan);
        } elseif ($cgRemit->remit_status == 'WILLREMIT') { //cg_remit的状态是WILLREMIT,
            $ret = $this->changeByWillRemit($loan, $cgRemit, $loanExtend, $remitList);
            if (!$ret) {
                Logger::dayLog('claim/inputnotify', $item['loan_id'] . 'cg_remit状态为WILLREMIT时,更新数据失败');
                return false;
            }
            $oPushNotWithdrawals=(new Push_not_withdrawals())->getByLoanId($item['loan_id']);
            if(!empty($oPushNotWithdrawals)){
                $res_json=[
                    'error_code'=>'fivedayover',
                    'error_msg'=>'5天未提现引导',
                ];
                $res_json=json_encode($res_json,true);
                $err_condition=[
                    'user_id'=>$cgRemit->user_id,
                    'type'=>6,
                    'res_code'=>'fivedayover',
                    'res_json'=>$res_json,
                    'res_msg'=>'5天未提现引导',
                    'status'=>0,
                ];
                $res=(new PayAccountError())->save_error($err_condition);
            }
        }
        echo 'SUCCESS';
        exit;
    }

    //七天未完成债匹借款驳回
    public function loanreject($item) {
        if (empty($item) || empty($item['loan_id'])) {
            return false;
        }
        
        $loan = User_loan::findOne($item['loan_id']);
        if(!$loan){
            Logger::dayLog('claim/inputnotify', $item['loan_id'] . '七天未完成驳回借款信息不存在');
            return false;
        }
        $cgRemit = $loan->cgRemit;
        $loanExtend = $loan->loanextend;
        $remitList = $loan->remit;
        if (!$cgRemit || !$loanExtend || !$remitList) {
            Logger::dayLog('claim/inputnotify', $item['loan_id'] . '七天未完成驳回借款附属表信息不存在');
            return false;
        }
        
        if ($cgRemit->remit_status == 'WAITREMIT') {
            $ret = $this->changeByWillRemit($loan, $cgRemit, $loanExtend, $remitList,7);
            if (!$ret) {
                Logger::dayLog('claim/inputnotify', $item['loan_id'] . '七天未完成驳回借款cg_remit状态为reject时,更新数据失败');
                return false;
            }
        }
        echo 'SUCCESS';
        exit;
    }

    private function saveSmsSend($userLoan) {

        $sms_type = 9;
        $content = '尊敬的用户，您的提现操作由于银行卡问题失败了，请您前往APP绑定新的银行卡后解除当前存管卡，再次发起借款。';
        $mobile = $userLoan->user->mobile;

        $addData['mobile'] = $mobile;
        $addData['content'] = $content;
        $addData['sms_type'] = $sms_type;
        $addData['status'] = 0;
        $addData['channel'] = Yii::$app->params['sms_channel'];
        $addData['send_time'] = date('Y-m-d H:i:s');
        $sms_model = new SmsSend();
        $res = $sms_model->addSmsSend($addData);
        return $res;
    }

    /**
     * 发送短信 1：债匹成功 2：提现成功 3：提现失败
     * @param $type
     * @param $cgRemit
     * @return bool
     */
    private function saveNewSmsSend($type, $cgRemit) {
        $userLoan = $cgRemit->userloan;
        if (!$userLoan) {
            return false;
        }
        $period_num = 1;
        if(in_array($userLoan->business_type,[5,6,11])){
            $period_num = count($userLoan->goodsbills);
        }
        $amount = number_format($userLoan->amount, 2);
        $getAmount = number_format($cgRemit->settle_amount, 2);
        $repayAmount = $userLoan->getRepaymentAmount($userLoan);
        $end_date = date('Y-m-d', strtotime('-1 days', strtotime($userLoan->end_date)));
        $card = $userLoan->bank->card;
        $card_no = substr($card, -4);
        if ($type == 1) {
            $sms_type = 5;
//            $content = '尊敬的用户，您在先花一亿元有一笔' . $amount . '元借款已通过审核，现已到达您的存管账户，请在2小时内前往APP进行提现操作，不提现也视为借款成功哦。';
            $content = '尊敬的用户，您在先花一亿元有一笔'.$amount.'元借款已通过审核，分'.$period_num.'期，现已到达您的存管账户，请在2小时内前往APP进行提现操作，不提现也视为借款成功哦。';
        } elseif ($type == 2) {
            $sms_type = 6;
            $content = '尊敬的用户，您已成功从存管账户提现' . $getAmount . '元到尾号为' . $card_no . '的银行卡。分' . $period_num . '期，应还金额' . $repayAmount . '元。';
        } elseif ($type == 3) {
            $sms_type = 7;
            $content = '尊敬的用户，您的提现操作由于银行卡问题失败了，请您前往APP绑定新的银行卡后解除当前存管卡，再次发起借款。';
//            $content = '尊敬的用户，您的提现操作失败了，请您前往APP内再次进行提现操作。';
        }

        $mobile = $userLoan->user->mobile;
        $addData['mobile'] = $mobile;
        $addData['content'] = $content;
        $addData['sms_type'] = $sms_type;
        $addData['status'] = 0;
        $addData['channel'] = Yii::$app->params['sms_channel'];
        $addData['send_time'] = date('Y-m-d H:i:s');
        $sms_model = new SmsSend();
        $res = $sms_model->addSmsSend($addData);
        return $res;
    }

    /**
     * cg_remit改为NOREMTI,user_remit_list改为SUCCESS,user_loan_extend改为SUCCESS,借款改为结清状态8
     * @param $loan
     * @param $cgRemit
     * @param $loanExtend
     * @param $remitList
     * @return bool
     */
    private function changeByWillRemit($loan, $cgRemit, $loanExtend, $remitList,$loan_status = 7) {
        //开启事务
        $transaction = Yii::$app->db->beginTransaction();
        //借款改为结清状态8
        $time = date('Y-m-d H:i:s');
        $change_ret = $loan->changeStatus($loan_status);
        if (!$change_ret) {
            $transaction->rollBack();
            Logger::dayLog('claim/inputnotify', $loan['loan_id'] . '更新借款状态为7失败');
            return false;
        }
//        $loan_condition = [
//            'repay_time' => $time,
//        ];
//        $loan_ret  = $loan->update_userLoan($loan_condition);
//        if(!$loan_ret){
//            $transaction->rollBack();
//            Logger::dayLog('claim/inputnotify', $loan['loan_id'] . '更新借款状态为8失败');
//            return false;
//        }
        //cg_remit改为NOREMIT,
        $cg_ret = $cgRemit->noRemit();
        if (!$cg_ret) {
            $transaction->rollBack();
            Logger::dayLog('claim/inputnotify', $loan['loan_id'] . 'cg_remit改为NOREMIT失败');
            return false;
        }
        //user_remit_list改为REJECT
        $remit_list_ret = $remitList->saveReject();
        if (!$remit_list_ret) {
            $transaction->rollBack();
            Logger::dayLog('claim/inputnotify', $loan['loan_id'] . 'user_remit_list改为REJECT失败');
            return false;
        }
        //user_loan_extend改为REJECT
        $loan_extend_ret = $loanExtend->saveReject();
        if (!$loan_extend_ret) {
            $transaction->rollBack();
            Logger::dayLog('claim/inputnotify', $loan['loan_id'] . 'user_loan_extend改为REJECT失败');
            return false;
        }
        
        //分期借款改为11驳回
        if( in_array($loan->business_type, [5, 6, 11] )){
            $result_goods_bill = (new User_loan()) -> rejectGoodsBill($loan);
            if(!$result_goods_bill){
                $transaction->rollBack();
                Logger::dayLog('claim/inputnotify', $loan['loan_id'] . '分期子订单goods_bill改为11失败');
                return false;
            }
        }
        
        $transaction->commit();
        return true;
    }

    private function addYxl($userLoan, $type, $status) {

        $pushYxlModel = new Push_yxl();
        $res = $pushYxlModel->getYxlInfo($userLoan->loan_id, $type, $status);
        if ($userLoan->settle_type == 2 || !empty($res)) {
            return false;
        }
        $condition = [
            'user_id' => $userLoan->user_id,
            'loan_id' => $userLoan->loan_id,
            'loan_status' => $status,
            'type' => $type,
        ];
        return $pushYxlModel->saveYxlInfo($condition);
    }

}

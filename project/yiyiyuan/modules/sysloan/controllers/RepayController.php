<?php

namespace app\modules\sysloan\controllers;

use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Bill_repay;
use app\models\news\Insure;
use app\models\news\Loan_repay;
use app\models\news\Manager_logs;
use app\models\news\Renewal_payment_record;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\sysloan\common\ApiController;
use Yii;

class RepayController extends ApiController {
    public $enableCsrfValidation = false;

    /**
     * @name 获取还款列表
     * @param int $loan_id 借款ID
     * @return json
     */
    public function actionRepaylist() {

        $required   = ['loan_id'];  //必传参数
//
        $httpParams = $this->post();  //获取参数
//
        $verify     = $this->BeforeVerify($required, $httpParams);
//        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
//
        $result_log = Common::saveLog('RepayController', 'actionRepaylist', $ip, 'sysloan', $httpParams['loan_id']);
        /*         * *************记录访问日志end**************** */

        $loanInfo = User_loan::find()->where(['loan_id' => $httpParams['loan_id']])->one();
        
        $parentloanInfo = User_loan::find()->where(['parent_loan_id' => $loanInfo->parent_loan_id])->indexBy('loan_id')->all();
        $loanids  = array_keys($parentloanInfo);


        if (isset($httpParams['offline']) && $httpParams['offline'] == 1) {
            $loanInfo = (new Loan_repay)->getOfflineRepayByLoanId($httpParams['loan_id']);  //获取线下成功还款
        } else {
            $loanInfo = (new Loan_repay)->getRepayByLoanId($loanids);  //获取所有成功还款
        }
        $insInfo = Insure::find()->where(['loan_id' => $loanids, 'type' => 3, 'status' => 1])->all();
        $loanInfo = array_merge($loanInfo, $insInfo);
		
        $renewalInfo = Renewal_payment_record::find()->where(['loan_id' => $loanids, 'status' => 1])->all();
        $loanInfo = array_merge($loanInfo, $renewalInfo);
        if (empty($loanInfo)) {
            $array = $this->errorreback('60012');
            exit(json_encode($array));
        }
        $array = $this->result('0000', $loanInfo);
        exit(json_encode($array));
    }

    public function actionGetrepayamount() {
        $required   = ['loan_id'];  //必传参数
//
        $httpParams = $this->post();  //获取参数
//
        $verify     = $this->BeforeVerify($required, $httpParams);

        $loanInfo = User_loan::find()->where(['loan_id' => $httpParams['loan_id']])->one();

        $needAmount = (new User_loan)->getRepaymentAmount($loanInfo);  //应还款

        $alreadyAmount = $loanInfo->getRepayAmount(2);

        $renewalNum    = (new User_loan())->getRenewalNum($httpParams['loan_id']);

        $array                   = $this->errorreback('0000');
        $array['need_amount']    = $needAmount;
        $array['already_amount'] = $alreadyAmount;
        $array['renewal']        = $renewalNum;
        exit(json_encode($array));
    }

    public function actionRepaybeforelist() {
        $required = ['repay_id'];  //必传参数

        $httpParams = $this->post();  //获取参数

        $verify = $this->BeforeVerify($required, $httpParams);

        $httpParams['repay_id'] = json_decode($httpParams['repay_id']);

        $loanInfo = (new Loan_repay)->getBeforeRepay($httpParams['repay_id']);  //获取所有成功还款
        $insInfo  = Insure::find()->where(['order_id' => $httpParams['repay_id'], 'type' => 3, 'status' => 1])->all();
        $loanInfo = array_merge($loanInfo, $insInfo);
        if (empty($loanInfo)) {
            $array = $this->errorreback('60012');
            exit(json_encode($array));
        }
        $array = $this->beforeResult('0000', $loanInfo);
        exit(json_encode($array));
    }

    private function beforeResult($code, $object) {
        $array               = $this->errorreback($code);
        $array['repay_list'] = [];
        if (empty($object)) {
            return $array;
        }
        foreach ($object as $key => $val) {
            if (isset($val['repay_id'])) {
                $data[$key]['amount']     = isset($val['actual_money']) ? $val['actual_money'] : '';
                $data[$key]['repay_time'] = isset($val['repay_time']) ? $val['repay_time'] : '';
                $data[$key]['repay_id']   = isset($val['repay_id']) ? $val['repay_id'] : '';
                $data[$key]['repay_type'] = empty($val['pic_repay1']) ? 1 : 2;
                $data[$key]['is_renewal'] = 0;
            } else {  //展期还款
                $data[$key]['amount']     = isset($val['money']) ? $val['money'] : '';
                $data[$key]['repay_time'] = isset($val['last_modify_time']) ? $val['last_modify_time'] : '';
                $data[$key]['repay_id']   = isset($val['order_id']) ? $val['order_id'] : '';
                $data[$key]['repay_type'] = 1;
                $data[$key]['is_renewal'] = 1;
            }
            $data[$key]['id']           = $val['id'];
            $data[$key]['order_number'] = isset($val['paybill']) ? $val['paybill'] : '';
            $data[$key]['plat_from']    = isset($val['platform']) ? $val['platform'] : '';
            $data[$key]['pic_repay1']   = isset($val['pic_repay1']) ? $val['pic_repay1'] : '';
            $data[$key]['pic_repay2']   = isset($val['pic_repay2']) ? $val['pic_repay2'] : '';
            $data[$key]['pic_repay3']   = isset($val['pic_repay3']) ? $val['pic_repay3'] : '';
            $data[$key]['remark']       = isset($val['repay_mark']) ? $val['repay_mark'] : '';
            $data[$key]['bank_name']    = isset($val->bank) ? $val->bank->bank_name : '';
            $data[$key]['card_number']  = isset($val->bank) ? $val->bank->card : '';
            $data[$key]['repay_status'] = $val['status'];
            $data[$key]['mobile']       = isset($val->user->mobile) ? $val->user->mobile : '';
            $data[$key]['realname']     = isset($val->user->realname) ? $val->user->realname : '';
            $data[$key]['loanamount']   = isset($val->loan->amount) ? $val->loan->amount : '';
            $data[$key]['end_date']     = isset($val->loan->end_date) ? $val->loan->end_date : '';
        }
        $array['repay_list'] = $data;
        return $array;
    }

    /**
     * @param string $repay_id 还款订单号
     * @return json
     */
    public function actionRepayone() {
        $required   = [];  //必传参数
        $httpParams = $this->post();  //获取参数

        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('RepayController', 'actionRepayone', $ip, 'sysloan', $httpParams);
        /*         * *************记录访问日志end**************** */

        $errorNum = $this->createOneWhere($httpParams);
        if (!$errorNum) {
            $array = $this->errorreback('99994');
            exit(json_encode($array));
        }
        $loanInfo = (new Loan_repay)->getRepayByConditions($httpParams);
        if (empty($loanInfo)) {
            $array = $this->errorreback('60012');
            exit(json_encode($array));
        }
        $array = $this->resultOne('0000', $loanInfo);
        exit(json_encode($array));
    }

    private function createOneWhere($where = []) {
        $searchWhere = ['repay_id', 'loan_id', 'mobile', 'realname', 'identity'];  //规定的搜索条件
        $errorNum    = 0;
        foreach ($searchWhere as $v) {
            if (!isset($where[$v]) || empty($where[$v])) {
                $errorNum ++;
            }
        }

        if ($errorNum >= 5) {
            return false;
        }
        return true;
    }

    private function resultOne($code, $object) {
        $array               = $this->errorreback($code);
        $array['repay_list'] = [];
        if (empty($object)) {
            return $array;
        }
        foreach ($object as $key => $val) {
            $data[$key]['loan_id']    = $this->getPrefixByDays($val) . $val['loan_id'];
            $data[$key]['repay_id']   = $val['repay_id'];
            $data[$key]['status']     = $val['status'];
            $data[$key]['paybill']    = $val['paybill'];
            $data[$key]['bank_id']    = $val['bank_id'];
            $data[$key]['money']      = $val['money'];
            $data[$key]['platform']   = $val['platform'];
            $data[$key]['source']     = $val['source'];
            $data[$key]['repay_time'] = $val['repay_time'];
            $data[$key]['mobile']     = isset($val->user->mobile) ? $val->user->mobile : '';
            $data[$key]['realname']   = isset($val->user->realname) ? $val->user->realname : '';
            $data[$key]['end_date']   = isset($val->loan->end_date) ? $val->loan->end_date : '';
            $data[$key]['amount']     = isset($val->loan->amount) ? $val->loan->amount : '';
        }
        $array['repay_list'] = $data;
        return $array;
    }

    /**
     * 上传还款凭证
     * @param type $code
     * @param type $object
     * @return array
     */
    public function actionUploadpic() {
//        $required   = ['loan_id', 'pic_repay1', 'admin_id', 'admin_name', 'platform'];  //必传参数
        $required   = ['loan_id', 'pic_repay1', 'admin_id', 'admin_name', 'platform','paybill'];  //必传参数
        $httpParams = $this->post();  //获取参数

        $verify = $this->BeforeVerify($required, $httpParams);

        $loaninfo = (new User_loan)->getById($httpParams['loan_id']);
        if (empty($loaninfo)) {
            $array = $this->errorreback('10052');
            exit(json_encode($array));
        }

        if (!in_array($loaninfo->status, [9, 10, 12, 13])) {
            $array = $this->errorreback('10023');
            exit(json_encode($array));
        }
        $user       = (new User)->getById($loaninfo['user_id']);
        $user_id    = $user['user_id'];
        /*         * *************记录访问日志beigin******************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('RepayController', 'actionUploadpic', $ip, 'sysloan', $user_id);
        /*         * *************记录访问日志end******************* */

        $transaction        = Yii::$app->db->beginTransaction();
        $loan_repay         = new Loan_repay();
        $data['repay_id']   = date('Ymdhis') . rand(1000, 9999);
        $data['user_id']    = $user_id;
        $data['loan_id']    = $httpParams['loan_id'];
        $data['pic_repay1'] = $httpParams['pic_repay1'];
        $data['platform']   = $httpParams['platform'];
        $data['paybill']   = $httpParams['paybill'];
        if (isset($httpParams['reason'])) {
            $data['repay_mark'] = $httpParams['reason'] ? $httpParams['reason'] : '';
        }
        if (isset($httpParams['pic_repay2'])) {
            $data['pic_repay2'] = $httpParams['pic_repay2'];
        }
        if (isset($httpParams['pic_repay3'])) {
            $data['pic_repay3'] = $httpParams['pic_repay3'];
        }
        $data['status'] = 3;
        $data['source'] = 3;
        $model          = new Loan_repay();
        $loan_result    = $model->addRepay($data);
        if (!$loan_result) {
            Logger::dayLog('sysloanUploadpic', $loan_result, $model->getErrors(), $data);
            $transaction->rollBack();
            $array = $this->errorreback('99999');
            exit(json_encode($array));
        }

        $admin     = $httpParams['admin_id'];
        $condition = array(
            'admin_name'     => $httpParams['admin_name'],
            'admin_id'       => $admin,
            'operation_type' => 6,
            'log_id'         => $loan_result,
        );
        if (isset($httpParams['reason'])) {
            $condition['reason'] = $httpParams['reason'] ? $httpParams['reason'] : '';
        }
        $result_log = (new Manager_logs)->updateManagerlogs($condition);
        if (!$result_log) {
            $transaction->rollBack();
            $array = $this->errorreback('60007');
            exit(json_encode($array));
        }
        $transaction->commit();
        $array = $this->errorreback('0000');
        exit(json_encode($array));
    }

    private function result($code, $object) {
        $array               = $this->errorreback($code);
        $array['repay_list'] = [];
        if (empty($object)) {
            return $array;
        }
        foreach ($object as $key => $val) { //正常还款
            if (isset($val['repay_id'])) {
                $data[$key]['amount']     = isset($val['actual_money']) ? $val['actual_money'] : '';
                $data[$key]['repay_time'] = isset($val['repay_time']) ? $val['repay_time'] : '';
                $data[$key]['repay_id']   = isset($val['repay_id']) ? $val['repay_id'] : '';
                $data[$key]['repay_type'] = empty($val['pic_repay1']) ? 1 : 2;
                $data[$key]['is_renewal'] = 0;
            } else {  //展期还款
                $data[$key]['amount']     = isset($val['money']) ? $val['money'] : '';
                $data[$key]['repay_time'] = isset($val['last_modify_time']) ? $val['last_modify_time'] : '';
                $data[$key]['repay_id']   = isset($val['order_id']) ? $val['order_id'] : '';
                $data[$key]['repay_type'] = 1;
                $data[$key]['is_renewal'] = 1;
            }
            $data[$key]['id']           = $val['id'];
            $data[$key]['order_number'] = isset($val['paybill']) ? $val['paybill'] : '';
            $data[$key]['plat_from']    = isset($val['platform']) ? $val['platform'] : '';
            $data[$key]['pic_repay1']   = isset($val['pic_repay1']) ? $val['pic_repay1'] : '';
            $data[$key]['pic_repay2']   = isset($val['pic_repay2']) ? $val['pic_repay2'] : '';
            $data[$key]['pic_repay3']   = isset($val['pic_repay3']) ? $val['pic_repay3'] : '';
            $data[$key]['remark']       = isset($val['repay_mark']) ? $val['repay_mark'] : '';
            $data[$key]['bank_name']    = isset($val->bank) ? $val->bank->bank_name : '';
            $data[$key]['card_number']  = isset($val->bank) ? $val->bank->card : '';
            $data[$key]['repay_status'] = $val['status'];
        }
        $array['repay_list'] = $data;
        return $array;
    }

    /*
     * 还款驳回
     */

    public function actionRepayreject() {
        $required   = ['id', 'admin_name', 'admin_id'];  //必传参数
        $httpParams = $this->post();  //获取参数

        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin******************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('RepayController', 'actionRepayreject', $ip, 'sysloan', $httpParams);
        /*         * *************记录访问日志end******************* */

        $loan_repay = Loan_repay::findOne($httpParams['id']);
        if (empty($loan_repay)) {
            $array = $this->errorreback('60012');
            exit(json_encode($array));
        }
        //成功不能驳回
        if ($loan_repay->status != 3) {
            $array = $this->errorreback('60014');
            exit(json_encode($array));
        }
        $transaction          = Yii::$app->db->beginTransaction();
        //还款状态改为0
        $conditions['status'] = 4;
        $saveRes              = $loan_repay->updateRepay($conditions);
        if (!$saveRes) {
            $transaction->rollBack();
            $array = $this->errorreback('60013');
            exit(json_encode($array));
        }
		(new Loan_repay())->updateFailSubsidiary($loan_repay['id']);
        //判断借款状态
        $loan_id  = $loan_repay->loan_id;
        $loanInfo = User_loan::findOne($loan_id);
        if ($loanInfo->status == 8) {
            $transaction->commit();
            $array = $this->errorreback('0000');
            exit(json_encode($array));
        }
        //判断借款状态应该为几
        $time = date('Y-m-d H:i:s');
        if ($loanInfo['end_date'] <= $time) {
            $sum    = Loan_repay::find()->where(['loan_id' => $loan_id, 'status' => 1])->sum('actual_money');
            $status = $sum > 0 ? 13 : 12;
        } else {
            $status = 9;
        }
        //如果当前借款状态和应改为的状态相等 则直接返回
        if ($loanInfo->status == $status) {
            $transaction->commit();
            $array = $this->errorreback('0000');
            exit(json_encode($array));
        }


        $statusRes = $loanInfo->changeStatus($status, $httpParams['admin_id']);
        if (!$statusRes) {
            $transaction->rollBack();
            $array = $this->errorreback('60002');
            exit(json_encode($array));
        }
        $condition  = array(
            'admin_id'       => $httpParams['admin_id'],
            'admin_name'     => $httpParams['admin_name'],
            'operation_type' => 2,
            'log_id'         => $loanInfo->loan_id,
        );
        $result_log = (new Manager_logs)->updateManagerlogs($condition);
        if (!$result_log) {
            $transaction->rollBack();
            $array = $this->errorreback('60007');
            exit(json_encode($array));
        }
        $transaction->commit();
        $array = $this->errorreback('0000');
        exit(json_encode($array));
    }

    /*
     * 时时获取账单状态和还款信息
     */

    public function actionNowstatus() {
        $required   = ['loan_id'];  //必传参数
        $httpParams = $this->post();  //获取参数

        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('LoanController', 'actionLoaninfo', $ip, 'sysloan', $httpParams['loan_id']);
        /*         * *************记录访问日志end**************** */

        $loanInfo = (new User_loan())->getById($httpParams['loan_id']);
        if (empty($loanInfo)) {
            $array = $this->errorreback('10048');
            exit(json_encode($array));
        }
        $loanInfo->loan_id      = $this->getPrefixByDays($loanInfo) . $loanInfo->loan_id;
        $loanInfo->chase_amount = $loanInfo->getChaseamount($httpParams['loan_id']);  //分期后 重置逾期金额
        $chase_amount           = $loanInfo->chase_amount > 0 ? $loanInfo->chase_amount : 0;

        //还款信息
        $repayInfo         = Loan_repay::find()->where(['loan_id' => $httpParams['loan_id'], 'status' => 1])->all();
        $array             = $this->resultTwo('0000', $repayInfo,$loanInfo);
        $array['loaninfo'] = $loanInfo->toArray();
        if (!empty($array['repay_list'])) {
            $maxRepayTime = max(array_column($array['repay_list'], 'repay_time'));
            if ($maxRepayTime) {
                $array['loaninfo']['repay_time'] = $maxRepayTime;
            }
        }
        exit(json_encode($array));
    }

    private function resultTwo($code, $object,$loaninfo='') {
        $array               = $this->errorreback($code);
        $array['repay_list'] = [];
        if (empty($object)) {
            return $array;
        }
        foreach ($object as $key => $val) {
            $data[$key]['loan_id']    = $this->getPrefixByDays($loaninfo) . $val['loan_id'];
            $data[$key]['user_id']    = $val['user_id'];
            $data[$key]['repay_id']   = $val['repay_id'];
            $data[$key]['amount']     = $val['actual_money'];
            $data[$key]['pic_repay1'] = $val['pic_repay1'];
            $data[$key]['pic_repay2'] = $val['pic_repay2'];
            $data[$key]['pic_repay3'] = $val['pic_repay3'];
            $data[$key]['status']     = $val['status'];
            $data[$key]['platform']   = $val['platform'];
            $data[$key]['source']     = $val['source'];
            $data[$key]['repay_time'] = $val['repay_time'];
            $data[$key]['mobile']     = isset($val->user->mobile) ? $val->user->mobile : '';
            $data[$key]['realname']   = isset($val->user->realname) ? $val->user->realname : '';
            $data[$key]['identity']   = isset($val->user->identity) ? $val->user->identity : '';
        }
        $array['repay_list'] = $data;
        return $array;
    }

    /* 获取分期成功还款
     * @author wangqin
     * @date 2017-11-23 16:00:00
     */

    public function actionRepaystages() {
        $required   = ['loan_id'];  //必传参数
        $httpParams = $this->post();  //获取参数

        $verify     = $this->BeforeVerify($required, $httpParams);
//        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('RepayController', 'actionRepaystages', $ip, 'sysloan', $httpParams['loan_id']); //写进记录
//        /*         * *************记录访问日志end**************** */

        $loanInfo = (new Bill_repay)->getBillRepayByLoanId($httpParams['loan_id']);  //获取所有成功还款

        if (empty($loanInfo)) {
            $array = $this->errorreback('60012');
            exit(json_encode($array));
        }
        $array = $this->result('0000', $loanInfo);
        exit(json_encode($array));
    }

    /* 获取账单状态和还款信息
     * @author wangqin
     * @date 2017-11-24 16:00:00
     */

    public function actionNowstatusstages() {
        $required   = ['loan_id'];  //必传参数
        $bill_id    = ['bill_id'];
        $httpParams = $this->post();  //获取参数

        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('LoanController', 'actionNowstatusstages', $ip, 'sysloan', $httpParams['loan_id']);
        /*         * *************记录访问日志end**************** */

        $loanInfo = (new User_loan())->getById($httpParams['loan_id']); //借款的状态
        if (empty($loanInfo)) {
            $array = $this->errorreback('10048');
            exit(json_encode($array));
        }
        $loanInfo->loan_id = $this->getPrefixByDays($loanInfo) . $loanInfo->loan_id; //就是1_loanId
        $chase_amount      = $loanInfo->chase_amount > 0 ? $loanInfo->chase_amount : 0; //逾期费用 如果有逾期费用
        //分期还款的信息
        $repayInfo         = (new Bill_repay)->getBillRepayByLoanId($httpParams['loan_id'], $httpParams['bill_id']);
        $array             = $this->resultTwo('0000', $repayInfo,$loanInfo);
        $array['loaninfo'] = $loanInfo->toArray();
        exit(json_encode($array));
    }

}

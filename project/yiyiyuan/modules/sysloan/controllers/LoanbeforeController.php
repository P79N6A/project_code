<?php

namespace app\modules\sysloan\controllers;

//use app\models\news\OverdueLoan;


use app\commonapi\Common;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use app\models\news\Manager_logs;
use app\models\news\OverdueLoan;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\sysloan\common\ApiController;
use Yii;

class LoanbeforeController extends ApiController {
    public $enableCsrfValidation = false;

    public function actionLoanlist() {

        $required              = ['loan_id'];  //必传参数
        $httpParams            = $this->post();  //获取参数
        $verify                = $this->BeforeVerify($required, $httpParams);
        $httpParams['loan_id'] = str_replace('1_', '', $httpParams['loan_id']);
        $httpParams['loan_id'] = str_replace('3_', '', $httpParams['loan_id']);
        $loanIds               = json_decode($httpParams['loan_id'], true);
        $loanInfo              = (new User_loan)->getLoanBeforeList($loanIds);
        $array                 = $this->result('0000', $loanInfo);
        exit(json_encode($array));
    }

    //根据手机号获取loan_id
    public function actionGetloanidbymobile() {
        $required   = ['mobile'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip                   = Common::get_client_ip();
        $result_log           = Common::saveLog('LoanController', 'getloanidbymobile', $ip, 'sysloan', $httpParams['mobile']);
        /*         * *************记录访问日志end**************** */
        $userinfo             = (new User())->find()->where(['mobile' => $httpParams['mobile']])->one();  //2499136
        $loans                = $userinfo->allloan;
        $array                = $this->errorreback('0000');
        $array['res']         = array_column($loans, 'loan_id');
        exit(json_encode($array));
    }

    //根据loan_id获取renewal loan信息
    public function actionGetrenewalloaninfo() {
        $required              = ['loan_id'];  //必传参数
        $httpParams            = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip                    = Common::get_client_ip();
        $result_log            = Common::saveLog('LoanController', 'getloanidbymobile', $ip, 'sysloan', $httpParams['loan_id']);
        /*         * *************记录访问日志end**************** */
        $select                = 'amount,is_calculation,chase_amount,interest_fee as fee,withdraw_fee as servic_fee,days,end_date';
        $loaninfo              = (new OverdueLoan())->find()->select($select)->where(['loan_id' => $httpParams['loan_id']])->asArray()->one();  //2499136
        $array                 = $this->errorreback('0000');
        $array['res']          = $loaninfo;
        exit(json_encode($array));
    }

    /*
     * 获取账单逾期金额
     *
     */

    public function actionLoaninfo() {
        $required   = ['loan_id'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('LoanController', 'actionLoaninfo', $ip, 'sysloan', $httpParams['loan_id']);
        /*         * *************记录访问日志end**************** */

        $overdueInfo = (new OverdueLoan())->getLoaninfo(['=', 'loan_id', $httpParams['loan_id']]);
        if (empty($overdueInfo)) {
            $overdueInfo = (new OverdueLoan())->getLoaninfo(['=', 'bill_id', $httpParams['loan_id']]);
            if (empty($overdueInfo)) {
                $array = $this->errorreback('10048');
                exit(json_encode($array));
            } else {
                $array         = $this->errorreback('0000');
                $chase_amount  = $overdueInfo->chase_amount > 0 ? $overdueInfo->chase_amount : 0;
                $array['info'] = ['chase_amount' => $chase_amount];
                exit(json_encode($array));
            }
        }
    }

    /*
     * 获取账单逾期金额
     *
     */

    public function actionLoanchaseamount() {
        $required   = ['loan_id'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('LoanController', 'actionLoanchaseamount', $ip, 'sysloan', $httpParams['loan_id']);
        /*         * *************记录访问日志end**************** */
        $loanIds    = json_decode($httpParams['loan_id'], true);
        if (!is_array($loanIds)) {
            $array = $this->errorreback('99994');
            exit(json_encode($array));
        }
        $loanInfo = (new OverdueLoan())->find()->where(['loan_id' => $loanIds])->all();
        if (empty($loanInfo)) {
            $array = $this->errorreback('10048');
            exit(json_encode($array));
        }
        $array = $this->resultamount('0000', $loanInfo);
        exit(json_encode($array));
    }

    private function resultamount($code, $object) {
        $array              = $this->errorreback($code);
        $array['loan_list'] = [];
        if (empty($object)) {
            return $array;
        }
        foreach ($object as $key => $val) {
            $data[$key]['loan_id']      = $this->getPrefixByDays($val) . $val['loan_id'];
            $data[$key]['bill_id']      = $val['bill_id'];
            $data[$key]['chase_amount'] = $val['chase_amount'];
        }
        $array['loan_list'] = $data;
        return $array;
    }

    /**
     * 修改借款状态
     * @param type $code
     * @param type $object
     * @return array
     */
    public function actionChangeloanstatus() {
        $required   = ['loan_id', 'status', 'admin_id', 'realname'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        /*         * *************记录访问日志beigin************* */
        $ip         = Common::get_client_ip();
        $result_log = Common::saveLog('LoanController', 'actionChangeloanstatus', $ip, 'sysloan', $httpParams['loan_id']);
        /*         * *************记录访问日志end**************** */

        $loanInfo = (new User_loan)->getById($httpParams['loan_id']);
        if (empty($loanInfo)) {
            $array = $this->errorreback('10048');
            exit(json_encode($array));
        }
        $transaction = Yii::$app->db->beginTransaction();

        if ($httpParams['status'] == 8) { //结清
            //将逾期表状态改为结清
            if (in_array($loanInfo->business_type, [1, 4])) {
                //查询逾期表中是否存在
                $overdueInfos = (new OverdueLoan())->getLoaninfo(['=', 'loan_id', $loanInfo->loan_id]);
                if (!empty($overdueInfos)) {
                    $res = $overdueInfos->clearOverdueLoan();
                }
            }
            if ($loanInfo->status == 8) { //已结清，不做任何操作
                $array = $this->errorreback('60011');
                exit(json_encode($array));
            }
            //应还金额
            if ($loanInfo->is_calculation == 1) {
                $amount = intval(($loanInfo['amount'] + $loanInfo['interest_fee']) * 10000);
            } else {
                $amount = intval(($loanInfo['amount'] + $loanInfo['interest_fee'] + $loanInfo['withdraw_fee']) * 10000);
            }
            //已还金额
            $getAmount     = $loanInfo->getRepayAmount(2);
            $getAmount     = empty($getAmount) ? 0 : $getAmount;
            $alreadyAmount = intval($getAmount * 10000);
            if ($amount > $alreadyAmount) {  //应还金额大于已还金额 不能结清
                $array = $this->errorreback('60003');
                exit(json_encode($array));
            }
            //如果修改装填是结清（status = 8）那么直接加入白名单
            $userInfo = User::findOne($loanInfo['user_id']);
            if (!empty($userInfo) && $userInfo->status == 3) {
                $userModel = new User();
                $whiteRes  = $userModel->inputWhite($loanInfo['user_id']);
                if (!$whiteRes) {
                    $transaction->rollBack();
                    $array = $this->errorreback('60015');
                    exit(json_encode($array));
                }
            }
        }


        //查询用户最后一次还款时间
        $repayInfo = Loan_repay::find()->where(['loan_id' => $loanInfo->loan_id, 'status' => 1])->orderBy('id desc')->one();
        $repayTime = '';
        if (!empty($repayInfo)) {
            $repayTime            = $repayInfo->repay_time;
            $loanInfo->repay_time = $repayInfo->repay_time;
            $loanSave             = $loanInfo->save();
            if (!$loanSave) {
                $transaction->rollBack();
                $array = $this->errorreback('60002');
                exit(json_encode($array));
            }
        }

        $statusRes = $loanInfo->changeStatus($httpParams['status'], $httpParams['admin_id']);
        if (!$statusRes) {
            $transaction->rollBack();
            $array = $this->errorreback('60002');
            exit(json_encode($array));
        }
        $condition  = array(
            'admin_id'       => $httpParams['admin_id'],
            'admin_name'     => $httpParams['realname'],
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
        $array         = $this->errorreback('0000');
        $array['data'] = ['repay_time' => $repayTime];
        exit(json_encode($array));
    }

    /**
     * 修改分期订单的状态
     */
    public function actionChangebillstatus() {
        $required   = ['loan_id', 'bill_id', 'status', 'admin_id', 'realname'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
//        $httpParams = [
//            'loan_id'  => '223726592',
//            'bill_id'  => json_encode(["W201711280243221333", "W201711280243221332"]),
//            'status'   => 8,
//            'admin_id' => 1,
//            'realname' => 'admin',
//        ];
        if (empty($httpParams['bill_id'])) {      //缺少订单id参数
            exit(json_encode($this->errorreback('60016')));
        }
        $bill_ids = json_decode($httpParams['bill_id'], true);

        //查询最早一起没结清的借款
        $where     = [
            'and',
                ['loan_id' => $httpParams['loan_id']],
                ['!=', 'bill_status', 8],
        ];
        $billsInfo = GoodsBill::find()->where($where)->select('bill_id')->orderBy('id')->asArray()->one();
        if (!empty($billsInfo) && !in_array($billsInfo['bill_id'], $bill_ids)) {
            $array = $this->errorreback('60020');
            exit(json_encode($array));
        }

        $loanInfo = (new User_loan)->getById($httpParams['loan_id']);
        if (empty($loanInfo)) {
            $array = $this->errorreback('10048');
            exit(json_encode($array));
        }


        //找出符合条件的bill逾期本金+利息
        $bill_model  = new GoodsBill();
        $bill_info   = $bill_model->getBillAmount($httpParams['loan_id'], $bill_ids);
        $bill_amount = $bill_info['principal'] + $bill_info['interest'];
        $getAmount   = $bill_info['repay_amount'];
        if ($getAmount < $bill_amount) {   //借款金额大于还款金额不能结清
            $array = $this->errorreback('60003');
            exit(json_encode($array));
        }

        GoodsBill::updateAll(['bill_status' => 8], ['bill_id' => $bill_ids]); //修改分期订单（goods_bill）状态
        OverdueLoan::updateAll(['loan_status' => 8], ['bill_id' => $bill_ids]);  //修改分期账单(overdue_loan)状态
        //如果是全部分期都结清那么修改订单表的状态
        $overdue_count = $bill_model->getTotalNum(['loan_id' => $httpParams['loan_id'], 'bill_status' => 12]);
        if ($overdue_count == 0) {     //所有订单已结清则更给user_loan的状态
            $res = $loanInfo->changeStatus(8);
            if (!$res) {
                $array = $this->errorreback('60019');
                exit(json_encode($array));
            }
        }
        $array = $this->errorreback('0000');
        exit(json_encode($array));
    }

    private function result($code, $object) {
        $array              = $this->errorreback($code);
        $array['loan_list'] = [];
        if (empty($object)) {
            return $array;
        }
        foreach ($object as $key => $val) {
            $flows                       = OverdueLoan::find()->where(['loan_id' => $val['loan_id'], 'loan_status' => 12])->one();
            $is_overdue                  = empty($flows) ? 1 : 2;
            $data[$key]['loan_id']       = $this->getPrefixByDays($val) . $val['loan_id'];
            $data[$key]['business_type'] = $val['business_type'];
            $data[$key]['amount']        = $val['amount'];
            $data[$key]['days']          = $val['days'];
            $data[$key]['realname']      = $val->user['realname'];
            $data[$key]['mobile']        = $val->user['mobile'];
            $data[$key]['desc']          = $val['desc'];
            $data[$key]['amount']        = $val['amount'];
            $data[$key]['loan_time']     = $val['create_time'];
            $data[$key]['end_date']      = $val['end_date'];
            $data[$key]['repay_time']    = $val['repay_time'];
            $data[$key]['repay_amount']  = isset($val['repay_amount']) && $val['repay_amount'] > 0 ? $val['repay_amount'] : 0;
            $data[$key]['is_overdue']    = $is_overdue;
        }
        $array['loan_list'] = $data;
        return $array;
    }

    /**
     * 为贷后提供账单查询接口
     */
    public function actionLoaninfos() {
        $required              = ['loan_id'];  //必传参数
        $httpParams            = $this->post();  //获取参数
        $verify                = $this->BeforeVerify($required, $httpParams);
        $httpParams['loan_id'] = str_replace('1_', '', $httpParams['loan_id']);
        $httpParams['loan_id'] = str_replace('3_', '', $httpParams['loan_id']);
        $loanIds               = json_decode($httpParams['loan_id'], true);
        //测试数据
//        $loanIds = [223726968,223726969,223726970];
        $loanInfo              = (new User_loan)->getLoanBeforeList($loanIds);
        if (empty($loanInfo)) {
            exit(json_encode('账单不存在'));
        }
        foreach ($loanInfo as $k => $val) {
            $data[$k]['source']           = $val['source'];
            $data[$k]['user_id']          = $val['user_id'];
            $data[$k]['business_type']    = $val['business_type'];
            $data[$k]['amount']           = $val['amount'] ? $val['amount'] : 0;
            $data[$k]['days']             = $val['days'];
            $data[$k]['desc']             = $val['desc'] ? $val['desc'] : '';
            $data[$k]['start_date']       = $val['start_date'] ? $val['start_date'] : '0000-00-00 00:00:00';
            $data[$k]['end_date']         = $val['end_date'] ? $val['end_date'] : '0000-00-00 00:00:00';
            $data[$k]['chase_amount']     = $this->getLoanChaseamount($val['loan_id'], $val['business_type']);
            $data[$k]['status']           = $val['status'];
            $data[$k]['repay_time']       = $val['repay_time'];
            $data[$k]['is_calculation']   = $val['is_calculation'];
            $data[$k]['create_time']      = date("Y-m-d H:i:s", time());
            $data[$k]['last_modify_time'] = date("Y-m-d H:i:s", time());
            $data[$k]['fee']              = $val['interest_fee'] > 0 ? $val['interest_fee'] : 0;
            $data[$k]['servic_fee']       = $val['withdraw_fee'] > 0 ? $val['withdraw_fee'] : 0;
            $data[$k]['bank_id']          = isset($val->bank->id) ? $val->bank->id : 0;
            $data[$k]['username']         = isset($val->user->realname) ? $val->user->realname : '';
            $data[$k]['mobile']           = isset($val->user->mobile) ? $val->user->mobile : '';
            $data[$k]['identity']         = isset($val->user->identity) ? $val->user->identity : '';
            $data[$k]['prome_score']      = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_score : 0;
            $data[$k]['prome_subject']    = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_subject : '';
            $data[$k]['remit_time']       = isset($val->remit->last_modify_time) ? $val->remit->last_modify_time : '0000-00-00 00:00:00';
            $data[$k]['is_more']          = $val['is_push'] == 1 ? 2 : 1;
            $data[$k]['loan_time']        = $val['create_time'] ? $val['create_time'] : '0000-00-00 00:00:00';
            $data[$k]['loan_repay']       = $this->getLoanRepay($val['loan_id'], $val['business_type']);
			$data[$k]['product_source']   = $this ->getProductsource($val);
			$data[$k]['loan_id']          = $data[$k]['product_source'].'_'.$val['loan_id'];
            $data[$k]['parent_loan_id']   = $val['parent_loan_id'] ? $data[$k]['product_source'].'_'.$val['parent_loan_id'] : '';
			
        }
        $array['rsp_code']      = '0000';
        $array['loan_info'] = $data;
        exit(json_encode($array));
    }

    /**
     * 还款
     * @param type $loan_id
     */
    private function getLoanRepay($loan_id, $business_type) {
        $info = Loan_repay::find()->where(['loan_id' => $loan_id, 'status' => 1])->all();
		$loanInfo = User_loan::findOne($loan_id);
		$days = $loanInfo['days'];
        $data = [];
        if (!empty($info)) {
			$product_source = $this ->getProductsource($loanInfo);
            foreach ($info as $k => $v) {
                $data[$k]['repay_id']       = $v['repay_id'];
                $data[$k]['loan_id']        = $v['loan_id'];
                $data[$k]['user_id']        = $v['user_id'];
                $data[$k]['amount']         = $v['actual_money'] > 0 ? $v['actual_money'] : 0;
                $data[$k]['pic_repay1']     = $v['pic_repay1'] ? $v['pic_repay1'] : '';
                $data[$k]['pic_repay2']     = $v['pic_repay2'] ? $v['pic_repay2'] : '';
                $data[$k]['pic_repay3']     = $v['pic_repay3'] ? $v['pic_repay3'] : '';
                $data[$k]['platform']       = $v['platform'];
                $data[$k]['source']         = $v['source'];
                $data[$k]['status']         = $v['status'];
                $data[$k]['realname']       = isset($v->user->realname) ? $v->user->realname : '';
                $data[$k]['mobile']         = isset($v->user->mobile) ? $v->user->mobile : '';
                $data[$k]['identity']       = isset($v->user->identity) ? $v->user->identity : '';
                $data[$k]['repay_time']     = $v['repay_time'] ? $v['repay_time'] : '0000-00-00 00:00:00';
                $data[$k]['product_source'] = $product_source;
            }
        }

        return json_encode($data);
    }

    /**
     * 查询子订单
     */
    private function getLoanBill($loan_id) {
        $info = GoodsBill::find()->where(['loan_id' => $loan_id])->all();
        $data = [];
        if (!empty($info)) {
            foreach ($info as $k => $v) {
                $data[$k]['bill_id']        = $v['bill_id'];
                $data[$k]['order_id']       = $v['order_id'];
                $data[$k]['goods_id']       = $v['goods_id'];
                $data[$k]['loan_id']        = $v['loan_id'];
                $data[$k]['user_id']        = $v['user_id'];
                $data[$k]['phase']          = $v['phase'];
                $data[$k]['fee']            = $v['fee'];
                $data[$k]['number']         = $v['number'];
                $data[$k]['goods_amount']   = $v['goods_amount'];
                $data[$k]['current_amount'] = $v['current_amount'];
                $data[$k]['actual_amount']  = $v['actual_amount'];
                $data[$k]['repay_amount']   = $v['repay_amount'];
                $data[$k]['principal']      = $v['principal'];
                $data[$k]['over_principal'] = $v['over_principal'];
                $data[$k]['interest']       = $v['interest'];
                $data[$k]['over_interest']  = $v['over_interest'];
                $data[$k]['over_late_fee']  = $v['over_late_fee'];
                $data[$k]['start_time']     = $v['start_time'];
                $data[$k]['end_time']       = $v['end_time'];
                $data[$k]['days']           = $v['days'];
                $data[$k]['bill_status']    = $v['bill_status'];
                $data[$k]['remit_status']   = $v['remit_status'];
                $data[$k]['repay_time']     = $v['repay_time'];
                $data[$k]['create_time']    = $v['create_time'];
                $data[$k]['product_source'] = 3;
                $data[$k]['chase_amount']   = $this->getLoanBillChamount($v['bill_id']);
            }
        }
        return json_encode($data);
    }

    /**
     * 查询子账单逾期金额
     */
    private function getLoanBillChamount($bill_id) {
        if (empty($bill_id)) {
            return 0;
        }
        $where = [
            'and',
                ['bill_id' => $bill_id],
                ['in', 'loan_status', [11, 12, 13]]
        ];
        $res   = (new OverdueLoan())->find()->where($where)->one();
        return $res ? $res['chase_amount'] : 0;
    }

    private function getLoanChaseamount($loan_id, $business_type) {
        if (empty($loan_id) || empty($business_type)) {
            return false;
        }
        if (in_array($business_type, [1, 4])) {
            $where = ['loan_id' => $loan_id];
        } else {
            $where = [
                'and',
                    ['loan_id' => $loan_id],
                    ['in', 'loan_status', [11, 12, 13]]
            ];
        }
        $res = (new OverdueLoan())->find()->select('sum(chase_amount) as chase_amount')->where($where)->one();
        return $res['chase_amount'] ? $res['chase_amount'] : 0;
    }

    //逾前决策需要的数据
    public function actionBeforpolicy() {
        $required   = ['loan_id'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify     = $this->BeforeVerify($required, $httpParams);
        $loanIds    = json_decode($httpParams['loan_id'], true);
        $ovderLoan  = OverdueLoan::find()->select('amount,user_id,loan_id,days,end_date,business_type')->where(['in', 'loan_id', $loanIds])->asArray()->all();
        if (!empty($ovderLoan)) {
            $array['rsp_code']      = '0000';
            $array['loan_info'] = $ovderLoan;
            exit(json_encode($array));
        } else {
            $array['rsp_code']      = '0001';
            $array['loan_info'] = '';
            exit(json_encode($array));
        }
    }

}

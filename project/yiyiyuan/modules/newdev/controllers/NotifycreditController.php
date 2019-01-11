<?php

namespace app\modules\newdev\controllers;

use app\common\ApiSign;
use app\commonapi\ApiSms;
use app\commonapi\ApiSmsShop;
use app\commonapi\ErrorCode;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Do_ious;
use app\models\news\Push_yxl;
use app\models\news\Setting;
use app\models\news\TemQuota;
use app\models\news\User;
use app\models\news\User_credit;
use app\models\news\UserCreditList;
use app\models\news\WarnMessageList;
use Yii;

class NotifycreditController extends NewdevController {

    public $enableCsrfValidation = FALSE;

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $post_data = $this->post();
        //全额度
//      $post_data['data'] = '{"strategy_req_id":"1994","req_id":"1994","loan_id":"0","user_id":"79770552","res_status":"approval","result_subject":"","credit_subject":"{\\"user_id\\":\\"79770552\\",\\"loan_total\\":\\"-1\\",\\"success_num\\":\\"0\\",\\"tianqi_score_v2\\":\\"615\\",\\"is_black_tq\\":\\"0\\",\\"PROME_V4_SCORE\\":\\"0\\",\\"result_status\\":\\"3\\",\\"RESULT\\":\\"3\\",\\"ious_status\\":\\"0\\",\\"ious_days\\":\\"0\\",\\"AMOUNT\\":\\"1000\\",\\"AMOUNT_SC\\":\\"1000\\",\\"DAYS\\":\\"56\\",\\"DAYS_SC\\":\\"56\\",\\"CRAD_RATE\\":\\"0.18\\",\\"CRAD_RATE_SC\\":\\"0.18\\",\\"INTEREST_RATE\\":\\"0.00098\\",\\"INTEREST_RATE_SC\\":\\"0.00098\\",\\"CARD_MONEY\\":\\"180\\",\\"CARD_MONEY_SC\\":\\"180\\",\\"mid_fm_seven_d\\":\\"1\\",\\"mph_fm_seven_d\\":\\"1\\",\\"mid_fm_one_m\\":\\"1\\",\\"mph_fm_one_m\\":\\"1\\",\\"Strategy_RESULT\\":\\"0\\",\\"class\\":\\"999\\",\\"revise_calss\\":\\"999\\"}"}';
        //只有亿元额度
//       $post_data['data'] = '{"strategy_req_id":"2216","req_id":"2216","loan_id":"0","user_id":"79770552","res_status":"approval","result_subject":"","credit_subject":"{\\"user_id\\":\\"79770552\\",\\"loan_total\\":\\"-1\\",\\"success_num\\":\\"0\\",\\"tianqi_score_v2\\":\\"615\\",\\"is_black_tq\\":\\"0\\",\\"PROME_V4_SCORE\\":\\"0\\",\\"result_status\\":\\"3\\",\\"RESULT\\":\\"3\\",\\"ious_status\\":\\"0\\",\\"ious_days\\":\\"0\\",\\"AMOUNT\\":\\"1001\\",\\"AMOUNT_SC\\":\\"0\\",\\"DAYS\\":\\"56\\",\\"DAYS_SC\\":\\"0\\",\\"CRAD_RATE\\":\\"0.18\\",\\"CRAD_RATE_SC\\":\\"0\\",\\"INTEREST_RATE\\":\\"0.00098\\",\\"INTEREST_RATE_SC\\":\\"0\\",\\"CARD_MONEY\\":\\"180\\",\\"CARD_MONEY_SC\\":\\"0\\",\\"mid_fm_seven_d\\":\\"1\\",\\"mph_fm_seven_d\\":\\"1\\",\\"mid_fm_one_m\\":\\"1\\",\\"mph_fm_one_m\\":\\"1\\",\\"Strategy_RESULT\\":\\"0\\",\\"class\\":\\"999\\",\\"revise_calss\\":\\"999\\",\"period\":\"2\"}"}';
        //只有商城额度呀 
//     $post_data['data'] = '{"strategy_req_id":"1992","req_id":"1992","loan_id":"0","user_id":"79770552","res_status":"approval","result_subject":"","credit_subject":"{\\"user_id\\":\\"79770552\\",\\"loan_total\\":\\"-1\\",\\"success_num\\":\\"0\\",\\"tianqi_score_v2\\":\\"615\\",\\"is_black_tq\\":\\"0\\",\\"PROME_V4_SCORE\\":\\"0\\",\\"result_status\\":\\"3\\",\\"RESULT\\":\\"3\\",\\"ious_status\\":\\"0\\",\\"ious_days\\":\\"0\\",\\"AMOUNT\\":\\"0\\",\\"AMOUNT_SC\\":\\"1000\\",\\"DAYS\\":\\"0\\",\\"DAYS_SC\\":\\"56\\",\\"CRAD_RATE\\":\\"0\\",\\"CRAD_RATE_SC\\":\\"0.18\\",\\"INTEREST_RATE\\":\\"0\\",\\"INTEREST_RATE_SC\\":\\"0.00098\\",\\"CARD_MONEY\\":\\"0\\",\\"CARD_MONEY_SC\\":\\"180\\",\\"mid_fm_seven_d\\":\\"1\\",\\"mph_fm_seven_d\\":\\"1\\",\\"mid_fm_one_m\\":\\"1\\",\\"mph_fm_one_m\\":\\"1\\",\\"Strategy_RESULT\\":\\"0\\",\\"class\\":\\"999\\",\\"revise_calss\\":\\"999\\"}"}';
        //都没有额度
//     $post_data['data'] = '{"strategy_req_id":"1650","req_id":"1650","loan_id":"0","user_id":"79770459","res_status":"bohui","result_subject":"","credit_subject":"{\\"user_id\\":\\"79770442\\",\\"loan_total\\":\\"-1\\",\\"success_num\\":\\"0\\",\\"tianqi_score_v2\\":\\"615\\",\\"is_black_tq\\":\\"0\\",\\"PROME_V4_SCORE\\":\\"0\\",\\"result_status\\":\\"3\\",\\"RESULT\\":\\"3\\",\\"ious_status\\":\\"0\\",\\"ious_days\\":\\"0\\",\\"AMOUNT\\":\\"0\\",\\"AMOUNT_SC\\":\\"0\\",\\"DAYS\\":\\"0\\",\\"DAYS_SC\\":\\"0\\",\\"CRAD_RATE\\":\\"0\\",\\"CRAD_RATE_SC\\":\\"0\\",\\"INTEREST_RATE\\":\\"0\\",\\"INTEREST_RATE_SC\\":\\"0\\",\\"CARD_MONEY\\":\\"0\\",\\"CARD_MONEY_SC\\":\\"0\\",\\"mid_fm_seven_d\\":\\"1\\",\\"mph_fm_seven_d\\":\\"1\\",\\"mid_fm_one_m\\":\\"1\\",\\"mph_fm_one_m\\":\\"1\\",\\"Strategy_RESULT\\":\\"0\\",\\"class\\":\\"999\\",\\"revise_calss\\":\\"999\\"}"}';
//        $post_data['_sign'] = '4381883a94f71097b5ee17473f02cc09pZgNly+MQF8skdl8ZZBjlyHw5Ncpm+TkxPZt57vrBYguJx/bkD4Tyn6OUl5fk6k/iH5uxHsW/tM2EVjjMGknxA==';

        $is_verify = (new ApiSign())->verifyData($post_data['data'], $post_data['_sign']);
        if (empty($is_verify)) {
            Logger::dayLog('notify/credit', '验签失败', $is_verify, $post_data);
            exit(json_encode((new ErrorCode())->getErrorArr('99998')));
        }
        $result = json_decode($post_data['data'], TRUE);
        if (empty($result)) {
            Logger::dayLog('notify/credit', '数据异常', $result, $post_data);
            exit(json_encode((new ErrorCode())->getErrorArr('99993')));
        }
        $credit_subject = json_decode($result['credit_subject'], TRUE);
        if (empty($credit_subject)) {
            Logger::dayLog('notify/credit', '数据异常', $credit_subject, $post_data);
            exit(json_encode((new ErrorCode())->getErrorArr('99993')));
        }
        Logger::dayLog('notify/credit', '异步回调数据', $post_data, $result, $credit_subject);
        $o_user_credit = (new User_credit())->getByReqId($result['strategy_req_id']);
        if (empty($o_user_credit) || $o_user_credit->status != 1) {
            Logger::dayLog('notify/credit', '无可用测评记录', $o_user_credit, $post_data);
            exit(json_encode((new ErrorCode())->getErrorArr('10011')));
        }
        if (empty($o_user_credit->user)) {
            Logger::dayLog('notify/credit', 'yi_user数据不完整', $post_data);
            exit(json_encode((new ErrorCode())->getErrorArr('99993')));
        }
        $dataFalseOrTrue = $this->getDataRes($credit_subject); //检测分期期数与分期与否的数据数据
        if (!$dataFalseOrTrue) {
            Logger::dayLog('notify/credit', '评测数据异常', $credit_subject);
            exit(json_encode((new ErrorCode())->getErrorArr('99993')));
        }
        if(empty($credit_subject['installment_result'])){
            $credit_subject['installment_result'] = 3;
        }
        //单期，分期期数初始化为1，天数验证
        if ($credit_subject['installment_result'] == 3) {
            if (intval($credit_subject['DAYS']) % 7 != 0) {
                $result['res_status'] = 'reject';
                Logger::dayLog('notify/credit', '单期，天数不符合规则，逻辑驳回', $credit_subject);
            }
            $credit_subject['period'] = 1;
        }
        //不符合分期规则，改为评测驳回  installment_result:0驳回 1分期 3单期
        if ($credit_subject['installment_result'] == 1 && in_array($result['res_status'], ['approval', 'manual'])) { //分期
            if (empty($credit_subject['period'])) {
                $credit_subject['period'] = 1;
            }
            if (!empty($credit_subject['DAYS']) && $credit_subject['DAYS'] != 30) {
                $credit_subject['DAYS'] = 30;
            }
            $period_result = (new User_credit())->getPeriodReject($credit_subject);
            if (!$period_result) {
                $result['res_status'] = 'reject';
                Logger::dayLog('notify/credit', '数据与分期规则冲突，评测改为驳回', $credit_subject);
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        $invalid_time = date('Y-m-d H:i:s', strtotime("+24 hour"));
        $credit_data = [
            'score' => rand(80, 90),
            'status' => 2,
            'res_status' => 2, //默认不可借
            'amount' => empty($credit_subject['AMOUNT']) ? 0 : $credit_subject['AMOUNT'],
            'shop_amount' => empty($credit_subject['AMOUNT_SC']) ? 0 : $credit_subject['AMOUNT_SC'],
            'days' => empty($credit_subject['DAYS']) ? 0 : $credit_subject['DAYS'],
            'shop_days' => empty($credit_subject['DAYS_SC']) ? 0 : $credit_subject['DAYS_SC'],
            'interest_rate' => empty($credit_subject['INTEREST_RATE']) ? 0 : bcmul($credit_subject['INTEREST_RATE'], 100, 4),
            'shop_interest_rate' => empty($credit_subject['INTEREST_RATE_SC']) ? 0 : bcmul($credit_subject['INTEREST_RATE_SC'], 100, 4),
            'crad_mondy' => empty($credit_subject['CARD_MONEY']) ? 0 : $credit_subject['CARD_MONEY'],
            'crad_rate' => empty($credit_subject['CRAD_RATE']) ? 0 : $credit_subject['CRAD_RATE'],
            'shop_crad_rate' => empty($credit_subject['CRAD_RATE_SC']) ? 0 : $credit_subject['CRAD_RATE_SC'],
            'invalid_time' => $invalid_time,
            'res_info' => $post_data['data'],
            'installment_result' => $credit_subject['installment_result'],
            'period' => empty($credit_subject['period']) ? 1 : $credit_subject['period']
        ];

        //通过测评
        $tem_quota_result = TRUE;
        if ($result['res_status'] == 'approval') {
            $credit_data['res_status'] = 1;
        }
        if (!empty($credit_subject['AMOUNT']) && !empty($credit_subject['CARD_MONEY'])) {
            $m_tem_quota = new TemQuota();
            $o_tem_quota = $m_tem_quota->getByUserId($o_user_credit->user->user_id);
            if (empty($o_tem_quota)) {
                $data = [
                    'user_id' => $o_user_credit->user->user_id,
                    'quota' => $credit_subject['AMOUNT'],
                    'days' => $credit_subject['DAYS'],
                ];
                $tem_quota_result = $m_tem_quota->addTemQuota($data);
            } else {
                $data = [
                    'quota' => $credit_subject['AMOUNT'],
                    'days' => $credit_subject['DAYS'],
                ];
                $tem_quota_result = $o_tem_quota->updateTemQuota($data);
            }
        }
        //人工
        if ($result['res_status'] == 'manual') {
            $credit_data['status'] = 3;
            $credit_data['res_status'] = 0;
        }

        //合规入场，评测默认支付
        $inspect = Keywords::inspectOpen();
        if ($inspect == 2) {
            $credit_data['pay_status'] = 1;
            $credit_data['type'] = 3;
        }

        $creditResult = $o_user_credit->updateUserCredit($credit_data);

        //白条资格
        $do_ious_result = TRUE;
        if (isset($credit_subject['ious_status']) && isset($credit_subject['ious_days'])) {
            $m_do_ious = new Do_ious();
            $o_do_ious = $m_do_ious->getByUserId($o_user_credit->user->user_id);
            $ious_days = $credit_subject['ious_days'];
            if ($credit_subject['ious_status'] == 1 && $credit_subject['ious_days'] < 7) {
                $ious_days = 7;
            }
            $do_ious_arr = [
                'ious_status' => $credit_subject['ious_status'],
                'ious_days' => $ious_days
            ];
            if (!empty($o_do_ious)) {
                $do_ious_result = $o_do_ious->updateRecord($do_ious_arr);
            } else {
                $do_ious_arr['user_id'] = $o_user_credit->user->user_id;
                $do_ious_result = $m_do_ious->addRecord($do_ious_arr);
            }
        }

        //记录同步至历史记录表
        $list_result = (new UserCreditList())->synchro($result['strategy_req_id']);

        if (empty($creditResult) || empty($tem_quota_result) || empty($do_ious_result) || empty($list_result)) {
            $transaction->rollBack();
            Logger::dayLog('notify/credit', '更新失败', $creditResult, $tem_quota_result, $do_ious_result, $list_result, $post_data);
            exit(json_encode((new ErrorCode())->getErrorArr('99992')));
        }
        $transaction->commit();
        //同步智融钥匙 (如果有亿元的额度)
        $is_tuisong = $this->is_tuisong($o_user_credit);
        Logger::dayLog('notify/is_tuisong', '有信令是否推送', '推送状态：' . $is_tuisong);
        if ($credit_data['status'] != 3 && $is_tuisong) {//人工或者亿元额度为0且是通过
            $crad_mondy = $credit_subject['CARD_MONEY'];
            $oUser = User::find()->where(['user_id' => $o_user_credit->user_id])->one();
            $nowTime = date('Y-m-d H:i:s');
            $psuhyxl_condition = [
                'user_id' => $o_user_credit->user_id,
                'loan_id' => $o_user_credit->req_id,
                'loan_status' => 3,
                'type' => 3,
                'notify_status' => 0,
            ];
            $pushYxlModel = new Push_yxl();
            $pushyxlresult = $pushYxlModel->saveYxlInfo($psuhyxl_condition);
            //推送评测状态+推送order
            $res = (new User_credit())->postCreditStatus($o_user_credit, $oUser, $crad_mondy);
            if ($res) {
                $pushYxlModel->updateSuccess();
            } else {
                $pushYxlModel->updateError();
            }
        }


        //亿元发送短信
        if ($result['res_status'] == 'approval' && !empty($o_user_credit->user) && isset($credit_subject['AMOUNT']) && !empty($credit_subject['AMOUNT']) && $credit_subject['AMOUNT'] != 0) {
            (new ApiSms())->sendCreditSuccessSma($o_user_credit->user->mobile, $credit_subject['AMOUNT']);
        }
        //商城发送短信
        $shop_setting = new Setting();
        $shop_switch_result = $shop_setting->getShop();
        if ($shop_switch_result && ($shop_switch_result->status == 0)) {
            if ($result['res_status'] == 'approval' && !empty($o_user_credit->user) && isset($credit_subject['AMOUNT_SC']) && !empty($credit_subject['AMOUNT_SC']) && $credit_subject['AMOUNT_SC'] != 0) {
                (new ApiSmsShop())->sendApplySuccess($o_user_credit->user->mobile, $credit_subject['AMOUNT_SC'], 44);
                (new WarnMessageList())->saveWarnMessage($o_user_credit, 1, 9, $credit_subject['AMOUNT_SC']);
            }
        }

        exit('SUCCESS');
    }

    //亿元额度为0且是通过不推送
    private function is_tuisong($o_user_credit) {
        if ((empty($o_user_credit['amount']) || $o_user_credit['amount'] == 0) && in_array($o_user_credit['source'], [1, 3, 4]) && $o_user_credit['status'] == 2 && $o_user_credit['res_status'] == 1) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 判断分期数据是否正常
     * @param type $credit_subject
     * @return boolean
     */
    private function getDataRes($credit_subject) {
        if (!in_array($credit_subject['installment_result'], [0, 1, 3])) {
            return FALSE;
        }
        //installment_result 1分期 3单期
//        if( ($credit_subject['installment_result'] == 3 ) && ($credit_subject['period']> 1) ){
//            return false;
//        }
        if (($credit_subject['installment_result'] == 1) && (empty($credit_subject['period']) || in_array($credit_subject['period'], [0]))) {
            return FALSE;
        }
        return TRUE;
    }


}

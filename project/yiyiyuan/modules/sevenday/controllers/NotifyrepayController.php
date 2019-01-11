<?php

namespace app\modules\sevenday\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\day\Loan_repay_guide;
use app\models\day\Overdue_loan_guide;
use app\models\day\Renewal_payment_record_guide;
use app\models\day\User_loan_guide;
use app\models\news\Renewal_payment_record;
use app\models\onlyread\User_loan;
use Yii;
use yii\web\Controller;

class NotifyrepayController extends Controller {

    public $enableCsrfValidation = false;
    private $notify_url;
    private $error_url;

    public function behaviors() {
        return [];
    }

    public function init() {
        $this->notify_url = [
            '1' => '/day/loan?repay=success',
        ];
        $this->error_url = [
            '1' => '/day/repay?repay=error',
        ];
    }

    /**
     * 获取还款的同步路径
     * @param type $loan_repay 还款记录
     * @param type $status 还款状态  1 成功，2失败
     * @param type $source 还款来源 目前只针对我们自己的app
     */
    private function getUrl($loan_repay, $status, $source) {
        switch ($loan_repay->source) {
            case 1:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '&repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '&repay_id=' . $loan_repay->repay_id;
                }
                if (!empty($source)) {
                    $url .= '&source=' . $source;
                }
                break;
        }
        return $url;
    }

    //在线还款服务器异步通知地址
    public function actionIndex() {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        $parr = $openApi->parseReturnData($data);
        $isPost = Yii::$app->request->isPost;
        $source = Yii::$app->request->get('source', '');
//        $parr = [
//            'res_code' => 0,
//            'res_data' => [
//                'pay_type' => 114,
//                'status' => '2',
//                'orderid' => 'Y08031734014',
//                'yborderid' => '201803141630047489779367132',
//                'amount' => 50000,
//                'res_code' => '',
//                'res_msg' => '',
//                'app_id' => '2810335722015',
//            ]
//        ];
//        $isPost = true;

        Logger::dayLog('sevenday/notifyrepay', $parr);
        if (!isset($parr['res_data']['orderid']) || empty($parr['res_data']['orderid'])) {
            return FALSE;
        }

        $o_loan_repay_guide = (new Loan_repay_guide())->getRepayByRepayId($parr['res_data']['orderid']);

        if (empty($o_loan_repay_guide)) {
            return FALSE;
        }
        if ($isPost) {
            $this->postNotify($o_loan_repay_guide, $parr);
        } else {
            return $this->getNotify($o_loan_repay_guide, $parr, $source);
        }
    }

    private function getNotify($o_loan_repay_guide, $parr, $source) {
        if ($o_loan_repay_guide->status == 0) {
            if (isset($parr['res_data']['status']) && $parr['res_data']['status'] == '11') {
                $conditon = ['status' => 4];
            } else {
                $conditon = ['status' => -1];
            }
            $get_up_result = $o_loan_repay_guide->updateRecord($conditon);
            if (!$get_up_result) {
                Logger::dayLog('sevenday/notifyrepay', 'get_update_faile' . $parr['res_data']['orderid'], $conditon);
            }
        }
        if ($parr['res_code'] == 0) {
            if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                $url = $this->getUrl($o_loan_repay_guide, 1, $source);
                Logger::dayLog('sevenday/notifyrepay', 'get_url_1', $url);
                return $this->redirect($url);
            } else {
                $url = $this->getUrl($o_loan_repay_guide, 2, $source);
                Logger::dayLog('sevenday/notifyrepay', 'get_url_2', $url);
                return $this->redirect($url);
            }
        } else {
            $url = $this->getUrl($o_loan_repay_guide, 2, $source);
            return $this->redirect($url);
        }
    }

    private function postNotify($o_loan_repay_guide, $parr) {
        if (empty($o_loan_repay_guide) || empty($parr)) {
            exit;
        }
        $loan_id = $o_loan_repay_guide->loan_id;
        $o_user_loan_guide = (new User_loan_guide())->getById($loan_id);
        $userinfo = $o_user_loan_guide->user;
        $amount = isset($parr['res_data']['amount']) ? $parr['res_data']['amount'] / 100 : 0;
        if (bccomp($o_loan_repay_guide->money, $amount, 3) != 0) {
            echo 'FAIL';
        }
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {//成功处理
            $this->repaySuccess($o_loan_repay_guide, $o_user_loan_guide, $amount, $parr, $userinfo);
        } else if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 11) {//失败处理
            $this->repayFail($o_loan_repay_guide, $parr, $userinfo, $o_user_loan_guide, $amount);
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

    //分期还款失败更新分期还款表
    private function stagesRepayFail($loan_repay, $parr, $userinfo, $loaninfo, $amount) {
        $conditon = [
            'status' => 4,
            'paybill' => isset($parr['res_data']['yborderid']) ? $parr['res_data']['yborderid'] : '',
        ];
        $up_result = $loan_repay->update_repay($conditon);
        if ($up_result) {
            echo 'SUCCESS';
        } else {
            Logger::dayLog('sevenday/notifyrepay', 'post_update_faile', $parr['res_data']['orderid']);
        }
        exit;
    }

    //不分期还款失败更新还款表
    private function repayFail($loan_repay, $parr, $userinfo, $loaninfo, $amount) {
        $conditon = [
            'status' => 4,
            'paybill' => isset($parr['res_data']['yborderid']) ? $parr['res_data']['yborderid'] : '',
        ];
        $up_result = $loan_repay->updateRecord($conditon);
        if ($up_result) {
            echo 'SUCCESS';
        } else {
            Logger::dayLog('sevenday/notifyrepay', 'post_update_faile', $parr['res_data']['orderid']);
        }
        exit;
    }

    //不分期还款成功 更新还款记录
    private function updateRepayFail($loan_repay, $parr, $amount) {
        $times = date('Y-m-d H:i:s');
        $repay_condition = [
            'status' => 2,
        ];
        $ret = $loan_repay->update_repay($repay_condition);
        if (!$ret) {
            Logger::dayLog('sevenday/notifyrepay', $loan_repay->repay_id, $parr['res_data']['status'], '更新还款订单失败');
        }
        return $ret;
    }

    //分期还款成功 更新分期还款记录
    private function updateRepay($loan_repay, $parr, $amount) {
        $times = date('Y-m-d H:i:s');
        $repay_condition = [
            'status' => 1,
            'platform' => $this->__repaymentChannel($parr['res_data']['pay_type']),
            'actual_money' => bcdiv(bcmul($amount, 100, 2), 100, 2), //接受金额精度计算
            'paybill' => $parr['res_data']['yborderid'],
//            'left_money'   => $leftAmount,
            'repay_time' => $times,
        ];
        $ret = $loan_repay->updateRecord($repay_condition);
        if (!$ret) {
            Logger::dayLog('sevenday/notifyrepay', $loan_repay->repay_id, $parr['res_data']['status'], '更新还款订单失败');
        }
        return $ret;
    }

    //结清user_loan
    private function clearUserLoan($loaninfo, $parr) {
        //还款结清
        $times = date('Y-m-d H:i:s');
        $change_ret = $loaninfo->updateRecord(['status' => 8]);
        if (!$change_ret) {
            Logger::dayLog('sevenday/notifyrepay', $loaninfo->loan_id, $parr['res_data']['status'], '更新借款状态为8失败');
            return false;
        }
        $loan_condition = [
            'repay_type' => 2,
            'repay_time' => $times,
        ];
        $loan_ret = $loaninfo->updateRecord($loan_condition);
        if (!$loan_ret) {
            Logger::dayLog('sevenday/notifyrepay', $loaninfo->loan_id, $parr['res_data']['status'], '更新借款信息失败');
            return false;
        }
        $where = [
            "AND",
            ['loan_id' => $loaninfo->loan_id],
            ['!=', 'loan_status', 8],
        ];
        $overdusLoan = (new Overdue_loan_guide())->find()->where($where)->one();
        if (!empty($overdusLoan)) {
            $overdusLoan->clearOverdueLoan();
            if (!$overdusLoan) {
                Logger::dayLog('sevenday/notifyrepay', $overdusLoan, '更新逾期账单结清状态失败');
                exit;
            }
        }
        return $change_ret && $loan_ret;
    }

    //还款成功回调方法
    private function repaySuccess($loan_repay, $loaninfo, $amount, $parr, $userinfo) {
        if (!empty($loan_repay) && !in_array($loan_repay->status, [1, 4])) {
            $ret = $this->updateRepay($loan_repay, $parr, $amount);
            if (!$ret) {
                exit;
            }
            $huankuan_money = $loaninfo->getRepayment($loaninfo);
            if (bccomp($huankuan_money, 0, 2) == 1) {
                echo 'SUCCESS';
                exit;
            }
            //还款结清
            $res = $this->clearUserLoan($loaninfo, $parr);
            if (!$res) {
                exit;
            }
            echo 'SUCCESS';
            exit;
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

    /**
     * 判断还款渠道
     * @param $pay_type
     * @return int
     */
    private function __repaymentChannel($pay_type) {
        $pay_type_arr = Keywords::getRepaymentChannel();
        if (!empty($pay_type_arr[$pay_type])) {
            return $pay_type_arr[$pay_type];
        }
        return 2;
    }

    public function actionRenew() {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        $isPost = Yii::$app->request->isPost;
        if ($isPost) {
            $nofify_type = 'post';
        } else {
            $nofify_type = 'get';
        }
        $parr = $openApi->parseReturnData($data);
//        $nofify_type = 'post';
//        $parr = [
//            'res_code' => 0,
//            'res_data' => [
//                'pay_type' => 172,
//                'status' => 2,
//                'orderid' => '201808132007272833',
//                'yborderid' => '20180813200732010431196478996690',
//                'amount' => 1,
//                'res_code' => '',
//                'res_msg' => '',
//                'app_id' => '2810335722015',
//            ],
//        ];
        Logger::dayLog('day/renew', $nofify_type, $parr);
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
//                    return $this->redirect('/dev/loan');
                    $renewal_info = Renewal_payment_record_guide::find()->where(['order_id' => $parr['res_data']['orderid']])->one();
                    if (!empty($renewal_info)) {//先判断状态是不是0 0的情况下做修改
                        if ($renewal_info->status == 0) {
                            $data = ['status' => -1];
                            $res = $renewal_info->update_batch($data);
                            Logger::dayLog('day/renew', $nofify_type, $renewal_info->loan_id, '续期同步状态修改' . $res);
                        }
                        $source = $renewal_info['source'];
                    } else {
                        $source = 1;
                    }
                    return $this->redirect('/day/loan?repay=success');
                } else {
                    return $this->redirect('/day/loan?repay=error');
                }
            } else {
                return $this->redirect('/day/loan?repay=error');
            }
        }
        $repay = Renewal_payment_record_guide::find()->where(['order_id' => $parr['res_data']['orderid']])->one();
        Logger::dayLog('day/renew', $nofify_type, $parr['res_data']['orderid'], 'Renewal_payment_record', $repay);
        if (empty($repay)) {
            exit;
        }
        $loan_id = $repay->loan_id;
        $loaninfo = User_loan_guide::findOne($loan_id);
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {
            if (empty($repay->paybill) && !in_array($repay->status, ['1', '4'])) {
                //出款渠道
                $platform = $this->__repaymentChannel($parr['res_data']['pay_type']);
                $actual_money = round($parr['res_data']['amount'] / 100, 2);
                $paybill = $parr['res_data']['yborderid'];

                $ret = $repay->saveSuccess($platform, $actual_money, $paybill);
                Logger::dayLog('notify/renewal_repay', $nofify_type, $repay->loan_id, $repay->create_time, '新建', $repay->id . $ret);
                if (!$ret) {
                    exit;
                }
                $res = $loaninfo->createRenewLoan($repay->create_time, $repay->id);
                Logger::dayLog('notify/renewal_repay', $nofify_type, $repay->loan_id, '新建借款期记录' . $res);
                if ($res) {
                    echo 'SUCCESS';
                    exit;
                } else {
                    exit;
                }
            } else {
                echo 'SUCCESS';
                exit;
            }
        } elseif ($parr['res_code'] == 0 && $parr['res_data']['status'] == 11) {//失败处理
            $result = $repay->saveFail();
            if ($result) {
                echo 'SUCCESS';
            }
            exit;
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

}

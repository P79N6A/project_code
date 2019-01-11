<?php

namespace app\modules\dev\controllers;

use app\commands\SubController;
use app\common\ApiClientCrypt;
use app\common\yeepay\QuickYeepay;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\Keywords;
use app\models\dev\Account;
use app\models\dev\Card_bin;
use app\models\dev\Coupon_list;
use app\models\dev\Coupon_use;
use app\models\dev\Financial_information;
use app\models\dev\Frozen_log;
use app\models\dev\Guarantee_card_order;
use app\models\dev\Guarantee_reback;
use app\models\dev\Invest_detail;
use app\models\dev\Loan_repay;
use app\models\dev\Renewal_payment_record;
use app\models\dev\Sms;
use app\models\dev\User;
use app\models\dev\User_amount_list;
use app\models\dev\User_bank;
use app\models\dev\User_bincard_list;
use app\models\dev\User_guarantee_loan;
use app\models\dev\User_loan;
use app\models\dev\User_loan_flows;
use app\models\dev\User_remit_list;
use app\models\dev\User_temporary_quota;
use app\models\dev\ApiSms;
use Exception;
use Yii;

class NotifyController extends SubController {

    public $enableCsrfValidation = false;
    private $quickYeepay;

    public function init() {
        $this->quickYeepay = new QuickYeepay();
    }

    /**
     * 待还款金额
     * @param type $loan_id
     * @return type
     */
    public function Amount($loaninfo) {//$loaninfo
        $loan_id         = $loaninfo['loan_id'];
        $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
        $loan_coupon     = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
        if ($loan_id <= 38841) {
            if (!empty($loaninfo['chase_amount'])) {
                $loaninfo['huankuan_amount'] = $loaninfo['chase_amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'];
            } else {
                if ($loaninfo['current_amount'] < $loaninfo['amount']) {
                    $loaninfo['huankuan_amount'] = $loaninfo['current_amount'] + $loaninfo['interest_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'];
                } else {
                    $loaninfo['huankuan_amount'] = $loaninfo['amount'] + $loaninfo['interest_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'];
                }
            }
        } else {
            if (!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) {
                if (!empty($loaninfo['chase_amount'])) {
                    $loaninfo['huankuan_amount'] = $loaninfo['chase_amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                } else {
                    if ($loaninfo['current_amount'] < $loaninfo['amount']) {
                        $loaninfo['huankuan_amount'] = $loaninfo['current_amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    } else {
                        $loaninfo['huankuan_amount'] = $loaninfo['amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    }
                }
            } else {
                if (!empty($loaninfo['chase_amount'])) {
                    $loaninfo['huankuan_amount'] = $loaninfo['chase_amount'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                } else {
                    if ($loaninfo['current_amount'] < $loaninfo['amount']) {
                        $loaninfo['huankuan_amount'] = $loaninfo['current_amount'] + $loaninfo['interest_fee'] + $loaninfo['withdraw_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    } else {
                        $loaninfo['huankuan_amount'] = $loaninfo['amount'] + $loaninfo['interest_fee'] + $loaninfo['withdraw_fee'] + $loaninfo['collection_amount'] - $loaninfo['like_amount'] - $loaninfo['coupon_amount'];
                    }
                }
            }
        }
        $repay_sql    = "SELECT SUM(actual_money) AS actual_money FROM " . Loan_repay::tableName() . " WHERE `loan_id` = '$loan_id' AND `status` = '1'";
        $actual_money = Yii::$app->db->createCommand($repay_sql)->queryOne();
        if ($actual_money['actual_money'] != 0) {
            $loaninfo['huankuan_amount'] = round($loaninfo['huankuan_amount'], 2) - round($actual_money['actual_money'], 2);
        }
        return $loaninfo;
    }

//在线还款服务器异步通知地址
    public function actionNotifybackurl() {
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
        $source = Yii::$app->request->get('source', '');
        $parr   = $openApi->parseReturnData($data);
        Logger::errorLog(print_r($parr, true), 'repay_yibao');
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                    if (empty($source)) {
                        return $this->redirect('/new/repay/verify?repay_id=' . $parr['res_data']['orderid']);
                    } else {
                        return $this->redirect('/new/repay/verify?source=' . $source . '&repay_id=' . $parr['res_data']['orderid']);
                    }
                } else {
                    if (empty($source)) {
                        return $this->redirect('/new/repay/error');
                    } else {
                        return $this->redirect('/new/repay/error?source=' . $source);
                    }
                }
            } else {
                if (empty($source)) {
                    return $this->redirect('/new/repay/error');
                } else {
                    return $this->redirect('/new/repay/error?source=' . $source);
                }
            }
        }
        $loan_repay = Loan_repay::find()->where(['repay_id' => $parr['res_data']['orderid']])->one();

        if (!empty($loan_repay)) {

            $loan_id     = $loan_repay->loan_id;
            $newLoanInfo = \app\models\news\User_loan::find()->where(['loan_id' => $loan_id])->one();
            if (!empty($newLoanInfo)) {
                $chaseAmount = $newLoanInfo->getChaseamount($loan_id);
            }
            $loaninfo               = User_loan::find()->where(['loan_id' => $loan_id])->one();
            $loaninfo->chase_amount = isset($chaseAmount) && $chaseAmount > 0 ? $chaseAmount : $loaninfo->chase_amount;
            $userinfo               = User::find()->select(array('realname', 'user_id', 'mobile'))->where(['user_id' => $loaninfo['user_id']])->one();
            $amount                 = isset($parr['res_data']['amount']) ? $parr['res_data']['amount'] / 100 : 0;
        }

        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {
            if (!empty($loan_repay) && empty($loan_repay->paybill)) {
                $transaction                  = Yii::$app->db->beginTransaction();
                $times                        = date('Y-m-d H:i:s');
                $loan_repay->status           = 1;
                /*
                  if ($parr['res_data']['pay_type'] == 101) {
                  $loan_repay->platform = 3;
                  }
                 */
                //出款渠道
                $loan_repay->platform         = $this->__repaymentChannel($parr['res_data']['pay_type']);
                $loan_repay->actual_money     = round($parr['res_data']['amount'] / 100, 2);
                $loan_repay->paybill          = $parr['res_data']['yborderid'];
                $loan_repay->last_modify_time = $times;
                $loan_repay->repay_time       = $times;
                $ret                          = $loan_repay->save();
                //发送还款成功通知


                if ($ret) {
                    $huankuan_money = $loaninfo->getRepaymentAmount($loaninfo->loan_id, $loaninfo->status, $loaninfo->chase_amount, $loaninfo->collection_amount, $loaninfo->like_amount, $loaninfo->amount, $loaninfo->current_amount, $loaninfo->interest_fee, $loaninfo->coupon_amount, $loaninfo->withdraw_fee);
                    if ($huankuan_money <= 0) {
                        $status                     = 8;
                        $loaninfo->status           = $status;
                        $loaninfo->settle_type      = $loaninfo->settle_type == 3 ? 1 : $loaninfo->settle_type;
                        $loaninfo->repay_type       = 2;
                        $loaninfo->last_modify_time = $times;
                        $loaninfo->repay_time       = $times;
                        $loaninfo->save();

                        $owhere   = [
                            "AND",
                            ['loan_id' => $loan_id],
                            ['!=', 'loan_status', 8],
                        ];
                        $overinfo = \app\models\news\OverdueLoan::find()->where($owhere)->one();
                        if (!empty($overinfo)) {
                            $overinfo->clearOverdueLoan();
                        }
                        User::inputWhite($loaninfo->user_id);

//查询借款人姓名
                        $realname = $userinfo['realname'];

                        $sql_flow  = "INSERT INTO  " . User_loan_flows::tableName() . "  (`loan_id`, `admin_id`, `loan_status`, `create_time`) VALUES ('$loan_id', '-1', '$status', '$times')";
                        $ret_flows = Yii::$app->db->createCommand($sql_flow)->execute();
                        if ($ret_flows) {
                            $transaction->commit();
                            if ($loan_repay->source != 4) {
                                $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 1);
                            }
                            echo 'SUCCESS';
                            exit;
                        } else {
                            $transaction->rollBack();
                            echo 'SUCCESS';
                            exit;
                        }
                    } else {
                        $transaction->commit();
                        if ($loan_repay->source != 4) {
                            $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 1);
                        }
                        echo 'SUCCESS';
                        exit;
                    }
                } else {
                    $transaction->rollBack();
                    echo 'SUCCESS';
                    exit;
                }
            } else {
                echo 'SUCCESS';
                exit;
            }
        } else {
            if ($loan_repay->source != 4) {
                $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 2);
            }
            echo 'SUCCESS';
            exit;
        }
    }

    //在线还款服务器异步通知地址
    public function actionRenewalnotifybackurl() {
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
//                'pay_type' => 102,
//                'status' => 2,
//                'orderid' => '201703161518156430',
//                'yborderid' => '411703165626327751',
//                'amount' => 1,
//                'error_code' => '',
//                'error_msg' => '',
//                'app_id' => '2810335722015'
//            ],
//        ];
        Logger::errorLog(print_r($parr, true), 'renewal_repay_yibao');
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
//                    return $this->redirect('/dev/loan');
                    return $this->redirect('/dev/renewal/renewalnotifysuccess?order_id=' . $parr['res_data']['orderid']);
                } else {
                    return $this->redirect('/dev/repay/error');
                }
            } else {
                return $this->redirect('/dev/repay/error');
            }
        }
        $repay = Renewal_payment_record::find()->where(['order_id' => $parr['res_data']['orderid']])->one();

        if (!empty($repay)) {
            $loan_id  = $repay->loan_id;
            $loaninfo = User_loan::findOne($loan_id);
        }
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {
            if (!empty($repay) && empty($repay->paybill)) {
                $transaction             = Yii::$app->db->beginTransaction();
                $times                   = date('Y-m-d H:i:s');
                $repay->status           = 1;
                /*
                  if ($parr['res_data']['pay_type'] == 101) {
                  $repay->platform = 3;
                  }
                 */
                //出款渠道
                $repay->platform         = $this->__repaymentChannel($parr['res_data']['pay_type']);
                $repay->actual_money     = round($parr['res_data']['amount'] / 100, 2);
                $repay->paybill          = $parr['res_data']['yborderid'];
                $repay->last_modify_time = $times;
                $ret                     = $repay->save();
                if ($ret) {
                    $res = $loaninfo->createRenewLoan();
                    if ($res) {
                        $transaction->commit();
                        echo 'success';
                        exit;
                    } else {
                        if ($loaninfo->status != 9) {
                            $transaction->commit();
                            echo 'success';
                            exit;
                        }
                        $transaction->rollBack();
                        exit;
                    }
                } else {
                    $transaction->rollBack();
                    exit;
                }
            } else {
                echo 'SUCCESS';
                exit;
            }
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

    /**
     * 借款在线还款结果短信通知用户
     * @param type $mobile 接收短信的手机号
     * @param type $loan 借款
     * @param type $type 1、支付成功，2、支付失败
     */
    private function sendSms($mobile, $loaninfo, $amount, $type = 2) {
        $newLoaninfo    = \app\models\news\User_loan::findOne($loaninfo->loan_id);
        $huankuan_money = $newLoaninfo->getRepaymentAmount($loaninfo, 2);
        Logger::dayLog('repay_notify', 'huankuan_money_' . $loaninfo->loan_id, $huankuan_money);
        switch ($type) {
            case 1:
                if ($huankuan_money > 0) {
                    $res = (new ApiSms())->sendRepaymentPortionSms($mobile, $amount, $huankuan_money);
                } else {
                    $res = (new ApiSms())->sendRepaymentAllSms($mobile);
                }
                break;
            case 2:
                $res = (new ApiSms())->sendRepaymentFailedSms($mobile, $huankuan_money);
                break;
        }
    }

//出款异步通知地址
    public function actionRemitbackurl() {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        $parr = $openApi->parseReturnData($data);
        Logger::errorLog(print_r($parr, true), 'remit_backurl');
        if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 6) {
            //出款成功
            $status = 'SUCCESS';
        } elseif ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 11) {
            //出款成功
            $status = 'FAIL';
        } else {
            exit;
        }

        //订单号
        $req_id          = $parr['res_data']['req_id'];
        //出款请求号
//        $client_id = $parr['res_data']['client_id'];
        $user_remit_list = User_remit_list::find()->where(['order_id' => $req_id])->one();
        if ($user_remit_list->remit_status != 'SUCCESS') {
            $user_remit_list->remit_status     = $status;
            $user_remit_list->last_modify_time = date('Y-m-d H:i:s');

            $transaction = Yii::$app->db->beginTransaction();
            if ($user_remit_list->save()) {
                if ($user_remit_list->type == 2) {
                    $ret = $this->setGuaranteeRebackStatus($user_remit_list->loan_id, $status);
                    if ($ret) {
                        $transaction->commit();
                        echo 'SUCCESS';
                        exit;
                    } else {
                        $transaction->rollBack();
                        echo 'SUCCESS';
                        exit;
                    }
                } else {
                    $transaction->commit();
                    echo 'SUCCESS';
                    exit;
                }
            } else {
                $transaction->rollBack();
                echo 'SUCCESS';
                exit;
            }
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

    //修改退卡记录表的退款状态
    private function setGuaranteeRebackStatus($id, $status) {
        $now_time = date('Y-m-d H:i:s');
        $sql      = "update " . Guarantee_reback::tableName() . " set reback_status='$status',last_modify_time='$now_time' where id=" . $id;
        $ret      = Yii::$app->db->createCommand($sql)->execute();
        return $ret;
    }

//在线还款服务器异步通知地址
    public function actionNotifyurl() {
        if (isset($_GET['result_pay'])) {
            Logger::errorLog(print_r($_GET, true), 'Notifyurl_data_get');
            $parr = $_GET;
            unset($parr['s']);
        } else {
            $data_url = file_get_contents("php://input");
            Logger::errorLog(print_r($data_url, true), 'Notifyurl_data_post');
            parse_str($data_url, $parr);
        }

        Logger::errorLog(print_r($parr, true), 'Notifyurl');
        $md5_key = Yii::$app->params['xianhua_key'];
        $md      = Http::createMd5($parr, $md5_key, 1);
        if (isset($parr['sign']) && $md == $parr['sign']) {
            if ($parr['result_pay'] == 'SUCCESS') {
                $loan_repay = Loan_repay::find()->where(['repay_id' => $parr['no_order']])->one();
                if (!empty($loan_repay) && empty($loan_repay->paybill)) {
                    $transaction                  = Yii::$app->db->beginTransaction();
                    $loan_id                      = $loan_repay->loan_id;
                    $times                        = date('Y-m-d H:i:s');
                    $loan_repay->status           = 1;
                    $loan_repay->actual_money     = $parr['money_order'];
                    $loan_repay->paybill          = $parr['oid_paybill'];
                    $loan_repay->last_modify_time = $times;
                    $ret                          = $loan_repay->save();
                    if ($ret) {
                        $loaninfo  = User_loan::find()->where(['loan_id' => $loan_id])->one();
                        $money_act = $this->Amount($loaninfo);
                        if ($money_act['huankuan_amount'] <= 0) {
                            $status                     = 8;
                            $loaninfo->status           = $status;
                            $loaninfo->repay_type       = 2;
                            $loaninfo->last_modify_time = $times;
                            $loaninfo->repay_time       = $times;
                            $loaninfo->save();
                            if ($loaninfo->business_type == 3) {
                                $gua_loan          = User_guarantee_loan::find()->where(['loan_id' => $loan_id])->one();
                                $gua_loan->status  = 8;
                                $gua_loan->version += 1;
                                $gua_loan->save();
                                if ($gua_loan->guater->status == 7) {
                                    $gua_all = User_guarantee_loan::find()->where(['user_guarantee_id' => $gua_loan->user_guarantee_id, 'status' => array(12, 13)])->count();
                                    if ($gua_all == 0) {
                                        $gua_loan->guater->status = 3;
                                        $gua_loan->guater->save();
//                                        print_r($gua_loan->guater);
                                        $flozen                   = new Frozen_log();
                                        $flozen->user_id          = $gua_loan->user_guarantee_id;
                                        $flozen->admin_id         = -1;
                                        $flozen->type             = 2;
                                        $flozen->create_time      = $times;
                                        $flozen->save();
                                    }
                                }
                            }
//查询借款人姓名
                            $userinfo = User::find()->select(array('realname', 'user_id'))->where(['user_id' => $loaninfo['user_id']])->one();
                            $realname = $userinfo['realname'];
                            if ($loaninfo->business_type == 1 || $loaninfo->business_type == 3) {
//借款人账户当前借款和总借款减
                                if ($loaninfo->credit_amount > 0) {
                                    $loan_acc = "update " . Account::tableName() . " set current_amount=current_amount+" . $loaninfo->credit_amount . ",current_loan=current_loan-" . $loaninfo->amount . ",total_loan=total_loan-" . $loaninfo->amount . ",version=version+1 where user_id=" . $loaninfo->user_id;
                                } else {
                                    $loan_acc = "update " . Account::tableName() . " set current_loan=current_loan-" . $loaninfo->amount . ",total_loan=total_loan-" . $loaninfo->amount . ",version=version+1 where user_id=" . $loaninfo->user_id;
                                }
                                $ret_loan_acc = Yii::$app->db->createCommand($loan_acc)->execute();

//该笔借款已经完成，把投资人的收益返还至投资人的账户
                                $nowtime    = date('Y' . '年' . 'm' . '月' . 'd' . '日' . ' H:i');
                                $sql        = "select l.days,i.amount,i.yield,i.user_id,i.status,i.invest_id,i.create_time,u.openid from yi_user_invest as i,yi_user_loan as l,yi_user as u where l.loan_id='$loan_id' and i.loan_id='$loan_id' and i.user_id=u.user_id";
                                $investlist = Yii::$app->db->createCommand($sql)->queryAll();
//                                print_r($investlist);
                                foreach ($investlist as $key => $value) {
//修改投资记录的状态
                                    $sql_invest    = "update yi_user_invest set status = 3,version=(version+1) where invest_id=" . $value['invest_id'];
                                    Yii::$app->db->createCommand($sql_invest)->execute();
//计算投资人的收益
                                    $profit        = $loaninfo->business_type == 3 ? ($value['amount'] * 0.01) : (($value['amount'] * ($value['yield'] / 100) / 365) * $value['days']);
                                    $income_amount = sprintf("%.2f", $profit);
//更新投资人的额度和收益
                                    $sql_profit    = "update yi_account set current_invest=current_invest-" . $value['amount'] . ",total_invest=total_invest-" . $value['amount'] . ",current_amount=current_amount+" . $value['amount'] . ",total_income=(total_income+" . $income_amount . "),version=(version+1) where user_id=" . $value['user_id'];
                                    Yii::$app->db->createCommand($sql_profit)->execute();

//保存一条投资收益明细
                                    $invest_detail              = new Invest_detail();
                                    $invest_detail->user_id     = $value['user_id'];
                                    $invest_detail->invest_id   = $value['invest_id'];
                                    $invest_detail->income      = $income_amount;
                                    $invest_detail->create_time = $times;

                                    $invest_detail->save();

//添加一条资金交易流水信息表
                                    $financial_information                   = new Financial_information();
                                    $financial_information->version          = 1;
                                    $financial_information->user_id          = $value['user_id'];
                                    $financial_information->trade_type       = 'DUE';
                                    $financial_information->funds_direction  = 'INCR';
                                    $financial_information->trade_amount     = $value['amount'];
                                    $financial_information->trade_share      = $value['amount'];
                                    $financial_information->last_modify_time = $times;
                                    $financial_information->create_time      = $times;

                                    $financial_information->save();

//向投资人推送模板消息
                                    $invest_time = date('m' . '月' . 'd' . '日', strtotime($value['create_time']));
                                    $template_id = Yii::$app->params['profit_template_id'];
                                    if (!empty($value['openid'])) {
                                        $openid = $value['openid'];
                                        $url    = Yii::$app->request->hostInfo . "/dev/account/investdetail?user_id=" . $value['user_id'] . "&invest_id=" . $value['invest_id'];

                                        $data           = '{
                                                                                                       "touser":"' . $openid . '",
                                                                                                       "template_id":"' . $template_id . '",
                                                                                                       "url":"' . $url . '",
                                                                                                       "topcolor":"#FF0000",
                                                                                                       "data":{
                                                                                                               "first": {
                                                                                                                   "value":"恭喜您在' . $invest_time . '投资' . $realname . '的收益已经到账啦！",
                                                                                                                   "color":"#173177"
                                                                                                               },
                                                                                                               "income_amount":{
                                                                                                                   "value":"' . $income_amount . '元",
                                                                                                                   "color":"#173177"
                                                                                                               },
                                                                                                               "income_time": {
                                                                                                                   "value":"' . $nowtime . '",
                                                                                                                   "color":"#173177"
                                                                                                               },
                                                                                                               "remark":{
                                                                                                                   "value":"快去投资更多靠谱的小伙伴吧！！首次借款还有更多惊喜呦！>>点击查看",
                                                                                                                   "color":"#173177"
                                                                                                               }
                                                                                                       }
                                                                                                  }';
                                        $resulttemplate = $this->sendTemplatetouser($data);
                                        Logger::errorLog(print_r($resulttemplate, true), 'sendtemplatetouserbyshouyi');
                                    }
                                }

//如果第一次在正常时间内还款，则提升与借款额度相同的信用额度，后面的所有借款在正常时间内还款，都提升借款额度50%的信用值
//首先判断是否是第一次在还款期内成功还款
//判断此次还款是否逾期,如果未逾期则提额，否则不提额
                                if (empty($loaninfo['chase_amount'])) {
                                    $loaninfonotthis = User_loan::find()->where(['user_id' => $loaninfo['user_id']])->andWhere(['status' => 8])->andWhere("loan_id != $loan_id and business_type !=2 and chase_amount is NULL")->one();
                                    if (empty($loaninfonotthis)) {
//是第一次，则提升与借款额度相同的信用额度
                                        if ($loaninfo['current_amount'] < $loaninfo['amount']) {
                                            $up_amount = floor($loaninfo['current_amount']);
                                        } else {
                                            $up_amount = floor($loaninfo['amount']);
                                        }
//提升额度
                                        $sql_up = "update " . Account::tableName() . " set remain_amount=(remain_amount-" . $up_amount . "),amount=(amount+" . $up_amount . "),current_amount=(current_amount+" . $up_amount . ") where user_id=" . $loaninfo['user_id'];
                                        Yii::$app->db->createCommand($sql_up)->execute();

//记录提额的日志
                                        $amount_date = array(
                                            'type'    => 7,
                                            'user_id' => $loaninfo['user_id'],
                                            'amount'  => $up_amount
                                        );
                                        $user_amount = new User_amount_list();
                                        $user_amount->CreateAmount($amount_date);
                                    }
                                }
                            } else if ($loaninfo->business_type == 2) {
//担保卡借款提前还款
//1.把提前还的额度加到用户可用担保额度里，且返回至担保卡购买记录剩余额度
                                $userId      = $userinfo['user_id'];
                                $repayAmount = round($loaninfo['current_amount'] / 0.99);
                                $sql_account = "update " . Account::tableName() . " set real_guarantee_amount=(real_guarantee_amount+$repayAmount) where user_id=" . $userId;
                                Logger::errorLog($sql_account, 'cardpayonline');
                                $ret_account = Yii::$app->db->createCommand($sql_account)->execute();
//修改购买记录里剩余的担保额度=========================
                                $cardList    = Guarantee_card_order::find()->where("user_id=$userId and status=1 and remain_amount < total_amount")->orderBy('create_time desc')->all();
                                Logger::errorLog(print_r($cardList, true), 'cardpayonline');
                                $allAmount   = $repayAmount;
                                $countRet    = 1;
                                if (!empty($cardList)) {
                                    while ($allAmount > 0) {
                                        if (!$countRet) {
                                            break;
                                        }
                                        foreach ($cardList as $val) {
                                            if (($val->remain_amount + $allAmount) < $val->total_amount) {
                                                $cardsql = "update " . Guarantee_card_order::tableName() . " set remain_amount=remain_amount+$allAmount where id=" . $val->id;
                                                Logger::errorLog($cardsql, 'cardpayonline');
                                                $cardret = Yii::$app->db->createCommand($cardsql)->execute();
                                                if ($cardret >= 0) {
                                                    $countRet = 1;
                                                } else {
                                                    $countRet = 0;
                                                }
                                                $allAmount = 0;
                                                break;
                                            } else {
                                                $cardsql = "update " . Guarantee_card_order::tableName() . " set remain_amount=total_amount where id=" . $val->id;
                                                Logger::errorLog($cardsql . "======2", 'cardpayonline');
                                                $cardret = Yii::$app->db->createCommand($cardsql)->execute();
                                                if (!$cardret) {
                                                    $countRet = 0;
                                                    break;
                                                }
                                                $allAmount = $allAmount - ($val->total_amount - $val->remain_amount);
                                            }
                                        }
                                    }
                                }
                            }

                            $sql_flow  = "INSERT INTO  " . User_loan_flows::tableName() . "  (`loan_id`, `admin_id`, `loan_status`, `create_time`) VALUES ('$loan_id', '-1', '$status', '$times')";
                            $ret_flows = Yii::$app->db->createCommand($sql_flow)->execute();
                            if ($ret_flows) {
                                $transaction->commit();
                                $arr = array(
                                    'rsp_code' => '0000',
                                    'rsp_msg'  => '交易成功',
                                );
                                print_r(json_encode($arr));
                            } else {
                                $transaction->rollBack();
                            }
                        } else {
                            $transaction->commit();
                            $arr = array(
                                'rsp_code' => '0000',
                                'rsp_msg'  => '交易成功',
                            );
                            print_r(json_encode($arr));
                        }
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $arr = array(
                        'rsp_code' => '0000',
                        'rsp_msg'  => '交易成功',
                    );
                    print_r(json_encode($arr));
                }
            }
        }
    }

    public function actionCallbackurl() {
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
        Logger::errorLog(print_r($parr, true), 'Bank_yibao');
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                    return $this->redirect('/dev/bank/success?repay_id=' . $parr['res_data']['amount']);
                } else {
                    return $this->redirect('/dev/bank/error');
                }
            } else {
                return $this->redirect('/dev/repay/error');
            }
        }
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {
            $usercard = User_bincard_list::find()->where(['biancard_id' => $parr['res_data']['orderid']])->one();
            if (!empty($usercard) && $usercard->status == 0) {
                $transaction            = Yii::$app->db->beginTransaction();
                $times                  = date('Y-m-d H:i:s');
                $usercard->status       = 1;
                $usercard->paybill      = $parr['res_data']['yborderid'];
                $usercard->actual_money = round($parr['res_data']['amount'] / 100, 2);
                /*
                  if ($parr['res_data']['pay_type'] == 101) {
                  $usercard->platform = 3;
                  }
                 */
                //出款渠道
                $usercard->platform     = $this->__repaymentChannel($parr['res_data']['pay_type']);
                $ret                    = $usercard->save();
                if ($ret) {
                    $sql                        = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . strlen($usercard->card) . " AND prefix_value=left(" . $usercard->card . ",prefix_length) order by prefix_length desc";
                    $cardbin                    = Yii::$app->db->createCommand($sql)->queryOne();
                    $userbank                   = new User_bank();
                    $userbank->user_id          = $usercard->user_id;
                    $userbank->type             = $cardbin['card_type'];
                    $userbank->bank_abbr        = $cardbin['bank_abbr'];
                    $userbank->bank_name        = $cardbin['bank_name'];
                    $userbank->card             = $usercard->card;
                    $userbank->bank_mobile      = $usercard->bank_mobile;
                    $userbank->status           = 1;
                    $userbank->create_time      = $times;
                    $userbank->last_modify_time = $times;
                    $ret_userbank               = $userbank->save();
                    if ($ret_userbank) {
                        //绑卡人账户额度提升500
                        $account                   = Account::find()->where(['user_id' => $usercard->user_id])->one();
                        $account['remain_amount']  -= 500;
                        $account['amount']         += 500;
                        $account['current_amount'] += 500;
                        $account['version']        += 1;
                        $account['total_income']   += round($parr['res_data']['amount'] / 100, 2);
                        try {
                            $account->save();
                            $amount_date = array(
                                'type'    => 13,
                                'user_id' => $usercard->user_id,
                                'amount'  => 500
                            );
                            $user_amount = new User_amount_list();
                            $user_amount->CreateAmount($amount_date);
                            $transaction->commit();
                            echo 'SUCCESS';
                            exit;
                        } catch (Exception $ex) {
                            $transaction->rollBack();
                        }
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $transaction->rollBack();
                }
            } else {
                echo 'SUCCESS';
                exit;
            }
        }
    }

//购买担保卡服务器异步通知地址
    public function actionGuaranteebackurl() {
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
        Logger::errorLog(print_r($parr, true), 'guarantee_yibao');
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                    return $this->redirect('/dev/guarantee/success?repay_id=' . $parr['res_data']['amount']);
                } else {
                    return $this->redirect('/dev/guarantee/error');
                }
            } else {
                return $this->redirect('/dev/guarantee/error');
            }
        }
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {
            $usercard = Guarantee_card_order::find()->where(['order_id' => $parr['res_data']['orderid']])->one();
            if (!empty($usercard) && ($usercard->status != 1)) {
                $transaction            = Yii::$app->db->beginTransaction();
                $times                  = date('Y-m-d H:i:s');
                $usercard->status       = 1;
                $usercard->pay_time     = $times;
                $usercard->paybill      = $parr['res_data']['yborderid'];
                $usercard->actual_money = round($parr['res_data']['amount'] / 100, 2);
                /*
                  if ($parr['res_data']['pay_type'] == 101) {
                  $usercard->platform = 3;
                  }
                 */
                //出款渠道
                $usercard->platform     = $this->__repaymentChannel($parr['res_data']['pay_type']);
                if ($usercard->save()) {
                    $account                        = Account::find()->where(['user_id' => $usercard->user_id])->one();
                    $account->recharge_amount       = $account->recharge_amount + round($parr['res_data']['amount'] / 100, 2);
                    $account->guarantee_amount      = $account->guarantee_amount + ($parr['res_data']['amount'] / 100 * 0.99);
                    $account->real_guarantee_amount = $account->real_guarantee_amount + round($parr['res_data']['amount'] / 100, 2);
                    if ($account->save()) {
                        $transaction->commit();
                        echo 'SUCCESS';
                        exit;
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $transaction->rollBack();
                }
            } else {
                echo 'SUCCESS';
                exit;
            }
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

//绑卡服务器异步通知地址
    public function actionBanknotify() {
        if (isset($_GET['result_pay'])) {
            Logger::errorLog(print_r($_GET, true), 'bank_Notifyurl');
            $parr = $_GET;
            unset($parr['s']);
        } else {
            $data_url = file_get_contents("php://input");
            Logger::errorLog(print_r($data_url, true), 'bank_Notifyurl');
            parse_str($data_url, $parr);
        }
        Logger::errorLog(print_r($parr, true), 'BankNotify');
        $md5_key = Yii::$app->params['xianhua_key'];
        $md      = Http::createMd5($parr, $md5_key, 1);
        if (isset($parr['sign']) && $md == $parr['sign']) {
            if ($parr['result_pay'] == 'SUCCESS') {
                $usercard = User_bincard_list::find()->where(['biancard_id' => $parr['no_order']])->one();
                if (!empty($usercard) && empty($usercard->paybill)) {
                    $transaction            = Yii::$app->db->beginTransaction();
                    $times                  = date('Y-m-d H:i:s');
                    $usercard->status       = 1;
                    $usercard->paybill      = $parr['oid_paybill'];
                    $usercard->actual_money = $parr['money_order'];
                    $ret                    = $usercard->save();
                    if ($ret) {
                        $sql                        = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . strlen($usercard->card) . " AND prefix_value=left(" . $usercard->card . ",prefix_length) order by prefix_length desc";
                        $cardbin                    = Yii::$app->db->createCommand($sql)->queryOne();
                        $userbank                   = new User_bank();
                        $userbank->user_id          = $usercard->user_id;
                        $userbank->type             = $cardbin['card_type'];
                        $userbank->bank_abbr        = $cardbin['bank_abbr'];
                        $userbank->bank_name        = $cardbin['bank_name'];
                        $userbank->card             = $usercard->card;
                        $userbank->bank_mobile      = $usercard->bank_mobile;
                        $userbank->status           = 1;
                        $userbank->create_time      = $times;
                        $userbank->last_modify_time = $times;
//                        if ($userbank->type == 1) {
//                            $userbank->validate = $usercard->validate;
//                            $userbank->cvv2 = $usercard->cvv2;
//                        }
                        $ret_userbank               = $userbank->save();
                        if ($ret_userbank) {
//绑卡人账户额度提升500
                            $account                   = Account::find()->where(['user_id' => $usercard->user_id])->one();
                            $account['remain_amount']  -= 500;
                            $account['amount']         += 500;
                            $account['current_amount'] += 500;
                            $account['version']        += 1;
                            $account['total_income']   += $parr['money_order'];
                            try {
                                $account->save();
                                $amount_date = array(
                                    'type'    => 13,
                                    'user_id' => $usercard->user_id,
                                    'amount'  => 500
                                );
                                $user_amount = new User_amount_list();
                                $user_amount->CreateAmount($amount_date);
                                $transaction->commit();
                                $arr         = array(
                                    'rsp_code' => '0000',
                                    'rsp_msg'  => '交易成功',
                                );
                                print_r(json_encode($arr));
                                exit;
                            } catch (Exception $ex) {
                                $transaction->rollBack();
                            }
                        } else {
                            $transaction->rollBack();
                        }
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $arr = array(
                        'rsp_code' => '0000',
                        'rsp_msg'  => '交易成功',
                    );
                    print_r(json_encode($arr));
                    exit;
                }
            }
        }
    }

//购买担保卡服务器异步通知地址
    public function actionGuaranteenotify() {
        if (isset($_GET['result_pay'])) {
            Logger::errorLog(print_r($_GET, true), 'guarantee_Notifyurl');
            $parr = $_GET;
            unset($parr['s']);
        } else {
            $data_url = file_get_contents("php://input");
            Logger::errorLog(print_r($data_url, true), 'guarantee_Notifyurl');
            parse_str($data_url, $parr);
        }
        Logger::errorLog(print_r($parr, true), 'GuaranteeNotify');
        $md5_key = Yii::$app->params['xianhua_key'];
        $md      = Http::createMd5($parr, $md5_key, 1);
        if (isset($parr['sign']) && $md == $parr['sign']) {
            if ($parr['result_pay'] == 'SUCCESS') {
                $usercard = Guarantee_card_order::find()->where(['order_id' => $parr['no_order']])->one();
                if ((!empty($usercard) && empty($usercard->paybill)) || ($usercard->status != 1)) {
                    $transaction            = Yii::$app->db->beginTransaction();
                    $times                  = date('Y-m-d H:i:s');
                    $usercard->status       = 1;
                    $usercard->pay_time     = $times;
                    $usercard->paybill      = $parr['oid_paybill'];
                    $usercard->actual_money = $parr['money_order'];
                    if ($usercard->save()) {
                        $account                        = Account::find()->where(['user_id' => $usercard->user_id])->one();
                        $account->recharge_amount       = $account->recharge_amount + $parr['money_order'];
                        $account->guarantee_amount      = $account->guarantee_amount + ($parr['money_order'] * 0.99);
                        $account->real_guarantee_amount = $account->real_guarantee_amount + $parr['money_order'];
                        if ($account->save()) {
                            $transaction->commit();
                            $arr = array(
                                'rsp_code' => '0000',
                                'rsp_msg'  => '交易成功',
                            );
                            print_r(json_encode($arr));
                            exit;
                        } else {
                            $transaction->rollBack();
                        }
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $arr = array(
                        'rsp_code' => '0000',
                        'rsp_msg'  => '交易成功',
                    );
                    print_r(json_encode($arr));
                    exit;
                }
            }
        }
    }

    /**
     * 购买担保卡绑定银行卡异步处理
     */
    public function actionAddguabackurl() {
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
        Logger::errorLog(print_r($parr, true), 'Bank_guarantee_yibao');
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                    return $this->redirect('/dev/guarantee/success?repay_id=' . $parr['res_data']['amount']);
                } else {
                    return $this->redirect('/dev/guarantee/error');
                }
            } else {
                return $this->redirect('/dev/repay/error');
            }
        }
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {
            $guarantee = Guarantee_card_order::find()->where(['order_id' => $parr['res_data']['orderid']])->one();
            if (!empty($guarantee) && ($guarantee->status != 1)) {
                $transaction             = Yii::$app->db->beginTransaction();
                $times                   = date('Y-m-d H:i:s');
                $guarantee->status       = 1;
                $guarantee->pay_time     = $times;
                $guarantee->paybill      = $parr['res_data']['yborderid'];
                $guarantee->actual_money = round($parr['res_data']['amount'] / 100, 2);
                /*
                  if ($parr['res_data']['pay_type'] == 101) {
                  $guarantee->platform = 3;
                  }
                 */
                //出款渠道
                $guarantee->platform     = $this->__repaymentChannel($parr['res_data']['pay_type']);

                $account                        = Account::find()->where(['user_id' => $guarantee->user_id])->one();
                $account->recharge_amount       = $account->recharge_amount + round($parr['res_data']['amount'] / 100, 2);
                $account->guarantee_amount      = $account->guarantee_amount + ($parr['res_data']['amount'] / 100 * 0.99);
                $account->real_guarantee_amount = $account->real_guarantee_amount + round($parr['res_data']['amount'] / 100, 2);
                $account->save();
                if ($guarantee->save()) {
                    $transaction->commit();
                    echo 'SUCCESS';
                    exit;
                } else {
                    $transaction->rollBack();
                }
            } else {
                echo 'SUCCESS';
                exit;
            }
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

    /**
     * 购买担保卡绑定银行卡异步处理
     */
    public function actionAddguanotify() {
        if (isset($_GET['result_pay'])) {
            Logger::errorLog(print_r($_GET, true), 'addgua_Notifyurl');
            $parr = $_GET;
            unset($parr['s']);
        } else {
            $data_url = file_get_contents("php://input");
            Logger::errorLog(print_r($data_url, true), 'addgua_Notifyurl');
            parse_str($data_url, $parr);
        }
        Logger::errorLog(print_r($parr, true), 'AddGuaNotify');

        $md5_key = Yii::$app->params['xianhua_key'];
        $md      = Http::createMd5($parr, $md5_key, 1);
        if (isset($parr['sign']) && $md == $parr['sign']) {
            if ($parr['result_pay'] == 'SUCCESS') {
                $guarantee = Guarantee_card_order::find()->where(['order_id' => $parr['no_order']])->one();
                if (empty($guarantee->paybill) || ($guarantee->status != 1)) {
                    $transaction                    = Yii::$app->db->beginTransaction();
                    $times                          = date('Y-m-d H:i:s');
                    $guarantee->status              = 1;
                    $guarantee->pay_time            = $times;
                    $guarantee->paybill             = $parr['oid_paybill'];
                    $guarantee->actual_money        = $parr['money_order'];
                    $account                        = Account::find()->where(['user_id' => $guarantee->user_id])->one();
                    $account->recharge_amount       = $account->recharge_amount + $parr['money_order'];
                    $account->guarantee_amount      = $account->guarantee_amount + ($parr['money_order'] * 0.99);
                    $account->real_guarantee_amount = $account->real_guarantee_amount + $parr['money_order'];
                    $account->save();
                    if ($guarantee->save()) {
                        $transaction->commit();
                        $arr = array(
                            'rsp_code' => '0000',
                            'rsp_msg'  => '交易成功',
                        );
                        print_r(json_encode($arr));
                        exit;
                    } else {
                        $transaction->rollBack();
                    }
                } else {
                    $arr = array(
                        'rsp_code' => '0000',
                        'rsp_msg'  => '交易成功',
                    );
                    print_r(json_encode($arr));
                    exit;
                }
            }
        }
    }

    /**
     * 判断还款渠道
     * @param $pay_type
     * @return int
     */
    private function __repaymentChannel($pay_type) {
        $pay_type_arr = Keywords::getRepaymentChannel();
        Logger::errorLog(print_r($pay_type_arr, true), 'repay_memt_channel');
        if (!empty($pay_type_arr[$pay_type])) {
            return $pay_type_arr[$pay_type];
        }
        return 2;
    }

}

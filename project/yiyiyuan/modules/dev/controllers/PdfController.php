<?php

namespace app\modules\dev\controllers;

use app\commonapi\Common;
use app\models\dev\Rate;
use app\models\dev\User;
use app\models\dev\User_bank;
use app\models\dev\User_loan;
use app\models\dev\Userwx;
use Yii;
use yii\web\Controller;
use app\commonapi\ApiSign;
use app\commonapi\Http;
use app\commonapi\Logger;

class PdfController extends Controller {

    public $layout = 'pdf';
    public $enableCsrfValidation = false;

    public function actionXhbtopdf() {
        $this->getView()->title = "先花一亿元先花宝投资协议";
        $user_id = $_GET['user_id'];
        $userinfo = User::find()->select(array('realname', 'identity'))->where(['user_id' => $user_id])->one();
        return $this->render('xhbtopdf', [
                    'userinfo' => $userinfo
        ]);
    }

    public function actionInvesttopdf() {
        $this->getView()->title = "先花一亿元信用咨询及居间服务协议(三方)";
        $invest_id = intval($_GET['invest_id']);
        $loan_id = intval($_GET['loan_id']);
        $this->layout = 'agreement';
        //借款方信息
        $loan = User_loan::findOne($loan_id);
        $user = $loan->user;
        $userwx = $user->userwx;
        $bank = $loan->bank;
        ////////////////////////////////////
        $rate = new Rate();
        $day_rate = $rate->getRateByDay($loan['days']);
        //////////////////////////////////////
        //还款日期
        $huankuandate = date('Y-m-d', (time() + $loan['days'] * 24 * 3600));
        $sql_invest = "select u.realname,u.identity,i.amount from yi_user_invest as i,yi_user as u where i.invest_id=" . $invest_id . " and i.user_id=u.user_id";
        $userinfo = Yii::$app->db->createCommand($sql_invest)->queryOne();
        //投资金额
        $invest_amount = number_format($userinfo['amount'], 2, '.', '');
        $daxie_invest_amount = Common::get_amount($invest_amount);
        //到期应收本息
        $investprofit = number_format((($userinfo['amount'] * (Yii::$app->params['rate'] / 100) / 365) * $loan['days'] + $userinfo['amount']), 2, '.', '');
        //到期应收金额
        $endamount = number_format(($userinfo['amount'] * (1 + $day_rate * $loan['days'])), 2, '.', '');
        $daxie_investprofit = Common::get_amount($investprofit);
        $daxie_investprofit_num = Common::get_amount_num($investprofit);
        $daxie_invest_amount_num = Common::get_amount_num($invest_amount);
        return $this->render('investtopdf', [
                    'user' => $user,
                    'loaninfo' => $loan,
                    'userwx' => $userwx,
                    'bank' => $bank,
                    'userinfo' => $userinfo,
                    'invest_amount' => $invest_amount,
                    'huankuandate' => $huankuandate,
                    'investprofit' => $investprofit,
                    'endamount' => $endamount,
                    'daxie_invest_amount' => $daxie_invest_amount,
                    'daxie_investprofit' => $daxie_investprofit,
                    'daxie_invest_amount_num' => $daxie_invest_amount_num,
                    'daxie_investprofit_num' => $daxie_investprofit_num,
        ]);
    }

    public function actionSetcontract() {
        $this->getView()->title = '先花一亿元居间服务及借款协议（三方）';
        $loan_id = Yii::$app->request->get('loan_id');
        $this->layout = 'agreement';
        $data = [
            [
                'loan_id' => $loan_id
            ]
        ];
        $signData = (new ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        //线上开放平台
        $url = "http://10.139.35.146:8085/api/match/queryinvest";
        //测试开放平台
//        $url = "http://182.92.80.211:8009/api/match/queryinvest";
        $result = Http::interface_post($url, $signData);
        if ($result) {
            $investdata = json_decode($result, true);
            $datas = json_decode($investdata['data'], true);
            if ($datas && isset($datas['resData']) && $datas['resData']) {
//                $contributordata = json_decode($investdata['data'], true);
                $contributorarr = $datas['resData'][$loan_id];
            } else {
                Logger::errorLog($loan_id, 'makecon', 'crontab');
                exit;
            }
        } else {
            Logger::errorLog("$loan_id 生成失败" . "---" . print_r($result, true), 'contract', 'crontab');
            $contributorarr = [];
        }
        //$sql_loan = "select l.loan_id,l.days,l.end_date,u.realname,u.identity,w.nickname,b.* from ".User_loan::tableName()." as l,".User::tableName()." as u,".Userwx::tableName()." as w,".User_bank::tableName()." as b where l.user_id=u.user_id and u.user_id=b.user_id and u.openid=w.openid and l.loan_id=$loan_id";
        $sql_loan = "select l.*,u.realname,u.identity,w.nickname,b.bank_name,b.card from " . User_loan::tableName() . " as l left join " . User_bank::tableName() . " as b on b.id=l.bank_id left join " . User::tableName() . " as u on b.user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where l.loan_id=$loan_id";
        $loaninfo = Yii::$app->db->createCommand($sql_loan)->queryOne();
        //借款本金
        $loan_amount = $loaninfo['current_amount'];
        $endamount = (new User_loan)->getRepaymentAmount($loan_id, $loaninfo['status'], $loaninfo['chase_amount'], $loaninfo['collection_amount'], $loaninfo['like_amount'], $loaninfo['amount'], $loaninfo['current_amount'], $loaninfo['interest_fee'], $loaninfo['coupon_amount'], $loaninfo['withdraw_fee']);
        $loan_amount = number_format($loan_amount, 2, '.', '');
        $daxie_loan_amount = Common::get_amount($loan_amount);
        $daxie_endamount = Common::get_amount($endamount);
        $daxie_loan_amount_num = Common::get_amount_num($loan_amount);
        $daxie_endamount_num = Common::get_amount_num($endamount);
        $huankuandate = date('Y-m-d', (strtotime($loaninfo['withdraw_time']) + $loaninfo['days'] * 24 * 3600));
        return $this->render('setcontract', [
                    'loaninfo' => $loaninfo,
                    'daxie_loan_amount' => $daxie_loan_amount,
                    'daxie_endamount' => $daxie_endamount,
                    'daxie_loan_amount_num' => $daxie_loan_amount_num,
                    'daxie_endamount_num' => $daxie_endamount_num,
                    'huankuandate' => $huankuandate,
                    'contributorarr' => $contributorarr
        ]);
    }

    public function actionSetloancontract() {        
        $this->layout = 'contract';
        $loan_id = intval($_GET['loan_id']);
        
        $loaninfo = \app\models\news\User_loan::find()->joinWith('bank', TRUE, 'LEFT JOIN')->joinWith('user', TRUE, 'LEFT JOIN')->where([\app\models\news\User_loan::tableName().'.loan_id'=>$loan_id])->one();
        
//        
//        echo $sql_loan = "select l.*,u.realname,u.identity,b.* from " . User_loan::tableName() . " as l left join " . User_bank::tableName() . " as b on b.id=l.bank_id left join " . User::tableName() . " as u on b.user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where l.loan_id=$loan_id";
//        $loaninfo = Yii::$app->db->createCommand($sql_loan)->queryOne();
        //借款本金
        $loan_amount = $loaninfo['current_amount'];
        //还款金额
        $endamount = (new User_loan)->getRepaymentAmount($loan_id, $loaninfo['status'], $loaninfo['chase_amount'], $loaninfo['collection_amount'], $loaninfo['like_amount'], $loaninfo['amount'], $loaninfo['current_amount'], $loaninfo['interest_fee'], $loaninfo['coupon_amount'], $loaninfo['withdraw_fee']);
        $loan_amount = number_format($loan_amount, 2, '.', '');
        $daxie_loan_amount = Common::get_amount($loan_amount);
        $daxie_endamount = Common::get_amount($endamount);
        $daxie_loan_amount_num = Common::get_amount_num($loan_amount);
        $daxie_endamount_num = Common::get_amount_num($endamount);
        $huankuandate = date('Y-m-d', (strtotime($loaninfo['withdraw_time']) + $loaninfo['days'] * 24 * 3600));
        return $this->render('setloancontract', [
                    'loaninfo' => $loaninfo,
                    'daxie_loan_amount' => $daxie_loan_amount,
                    'daxie_endamount' => $daxie_endamount,
                    'daxie_loan_amount_num' => $daxie_loan_amount_num,
                    'daxie_endamount_num' => $daxie_endamount_num,
                    'huankuandate' => $huankuandate,
        ]);
    }

    public function actionJiufu($loan_id) {
        $this->getView()->title = "融资文件";
        $this->layout = 'agreement';
        $loan = User_loan::findOne($loan_id);
        $repay_amount = $loan->totalAmount($loan->is_calculation);
        $daxie_amount = Common::get_amount($loan->amount);
        $daxie_repay_amount = Common::get_amount($repay_amount);
        $user = User::findOne($loan->user_id);
        $bank = $loan->bank;
        $loan_num = 'XHJK' . $loan->loan_id;
        $jf_num = 'XHXW' . $loan->loan_id;
        return $this->render('createjiufu', [
                    'user' => $user,
                    'bank' => $bank,
                    'loan_num' => $loan_num,
                    'jf_num' => $jf_num,
                    'loan' => $loan,
                    'repay_amount' => $repay_amount,
                    'daxie_amount' => $daxie_amount,
                    'daxie_repay_amount' => $daxie_repay_amount,
        ]);
    }

    /**
     * 调用地方：定时任务 Pdfgenerationprotocol->makeAgreement
     */
    public function actionAgreement() {
        $this->getView()->title = "融资文件";
        $this->layout = 'agreement';
        $loan_id = $_GET['loan_id'];
        $loan = User_loan::findOne($loan_id);
        $repay_amount = $loan->totalAmount($loan->is_calculation);
        $daxie_amount = Common::get_amount($loan->amount);
        $daxie_repay_amount = Common::get_amount($repay_amount);
        $user = User::findOne($loan->user_id);
        $bank = $loan->bank;
        if (empty($user) || empty($bank)) {
            Logger::dayLog('agreement', 'loan_' . $loan->user_id);
        }
        $loan_num = 'XHJK' . $loan->loan_id;
        $jf_num = 'XHXW' . $loan->loan_id;
        return $this->render('agreement', [
                    'user' => $user,
                    'bank' => $bank,
                    'loan_num' => $loan_num,
                    'jf_num' => $jf_num,
                    'loan' => $loan,
                    'repay_amount' => $repay_amount,
                    'daxie_amount' => $daxie_amount,
                    'daxie_repay_amount' => $daxie_repay_amount,
        ]);
    }

    /**
     * 调用地方: 定时任务 Pdfgenerationprotocol->makeContract
     */
    public function actionSetcontractaccredit() {
        $this->getView()->title = '先花一亿元居间服务及借款协议（三方）';
        $loan_id = trim(Yii::$app->request->get('loan_id'));
        $fund_id = trim(Yii::$app->request->get('fund_id'));  //资金方
        $this->layout = 'agreement';
        $data = [
            [
                'loan_id' => $loan_id
            ]
        ];
        $signData = (new ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        //线上开放平台
        //$url = "http://10.139.35.146:8085/api/match/queryinvest";
        //测试开放平台
        $url = "http://182.92.80.211:8009/api/match/queryinvest";
        $result = Http::interface_post($url, $signData);
        if ($result) {
            $investdata = json_decode($result, true);
            if ($investdata['data']) {
                $contributordata = json_decode($investdata['data'], true);
                $contributorarr = !empty($contributordata['resData']) ? $contributordata['resData'][$loan_id] : [];
            } else {
                Logger::errorLog("$loan_id 生成失败" . "---" . print_r($result, true), 'contract', 'crontab');
                $contributorarr = [];
            }
        } else {
            Logger::errorLog("$loan_id 生成失败" . "---" . print_r($result, true), 'contract', 'crontab');
            Logger::dayLog('setcontractaccredit', 'loan_' . $loan_id . "\n");
            $contributorarr = [];
        }
        //$sql_loan = "select l.loan_id,l.days,l.end_date,u.realname,u.identity,w.nickname,b.* from ".User_loan::tableName()." as l,".User::tableName()." as u,".Userwx::tableName()." as w,".User_bank::tableName()." as b where l.user_id=u.user_id and u.user_id=b.user_id and u.openid=w.openid and l.loan_id=$loan_id";
        $sql_loan = "select l.*,u.realname,u.identity,w.nickname,b.bank_name,b.card from " . User_loan::tableName() . " as l left join " . User_bank::tableName() . " as b on b.id=l.bank_id left join " . User::tableName() . " as u on b.user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where l.loan_id=$loan_id";
        $loaninfo = Yii::$app->db->createCommand($sql_loan)->queryOne();
        //借款本金
        $loan_amount = $loaninfo['current_amount'];
        $endamount = (new User_loan)->getRepaymentAmount($loan_id, $loaninfo['status'], $loaninfo['chase_amount'], $loaninfo['collection_amount'], $loaninfo['like_amount'], $loaninfo['amount'], $loaninfo['current_amount'], $loaninfo['interest_fee'], $loaninfo['coupon_amount'], $loaninfo['withdraw_fee']);
        $loan = User_loan::findOne($loan_id);
        $endamount = $loan->totalAmount($loan->is_calculation);
        $loan_amount = number_format($loan_amount, 2, '.', '');
        $daxie_loan_amount = Common::get_amount($loan_amount);
        $daxie_endamount = Common::get_amount($endamount);
        $daxie_loan_amount_num = Common::get_amount_num($loan_amount);
        $daxie_endamount_num = Common::get_amount_num($endamount);
        $huankuandate = date('Y-m-d', (strtotime($loaninfo['withdraw_time']) + $loaninfo['days'] * 24 * 3600));
        //资金方
        $fund_data = array('2' => '玖富', '3' => '连交所');
        $fund_str = !empty($fund_data[$fund_id]) ? $fund_data[$fund_id] : "";
        return $this->render('setcontractaccredit', [
                    'loaninfo' => $loaninfo,
                    'daxie_loan_amount' => $daxie_loan_amount,
                    'daxie_endamount' => $daxie_endamount,
                    'daxie_loan_amount_num' => $daxie_loan_amount_num,
                    'daxie_endamount_num' => $daxie_endamount_num,
                    'huankuandate' => $huankuandate,
                    'contributorarr' => $contributorarr,
                    'fund' => $fund_str,
        ]);
    }

}

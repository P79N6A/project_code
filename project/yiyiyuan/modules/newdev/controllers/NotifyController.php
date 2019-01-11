<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\ApiSms;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\BillRepay;
use app\models\news\BillRepayDetail;
use app\models\news\Coupon_list;
use app\models\news\GoodsBill;
use app\models\news\Loan_mapping;
use app\models\news\Loan_repay;
use app\models\news\OverdueLoan;
use app\models\news\Renewal_payment_record;
use app\models\news\Renew_amount;
use app\models\news\RepayCouponUse;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\WarnMessageList;
use app\models\dev\Activity_newyear;
use app\commonapi\ApiSmsShop;
use app\models\service\StageService;
use Yii;

class NotifyController extends NewdevController {

    public $enableCsrfValidation = false;
    private $quickYeepay;
    private $notify_url;
    private $error_url;

//    public function beforeAction() {
    public function behaviors() {
        return [];
    }

    public function init() {
//        parent::init();
        //3:易宝投资通,2:易宝一键支付,6:连连支付（一亿元）,7:融宝快捷（花生米富）,8:宝付代扣,9:宝付认证支付（一亿元）,10:连连认证支付（花生米富）,11:易宝代扣,12:融宝快捷（一亿元）,13:融宝快捷（米富）,14:融宝快捷（一亿元）,15:融宝快捷（米富）,16:融宝(逾期),17:宝付（逾期）
        $this->notify_url = [
            '1'  => '/new/repay/verify',
            '2'  => '/new/repay/verify',
            '3'  => '/new/repay/verify',
            '4'  => '/new/repay/verify',
            '5'  => '/new/repay/verify',
            '6'  => '/new/repay/verify',
            '7'  => '/new/repay/verify',
            '8'  => 'https://m.rong360.com/center',
            '10' => '/channelapi/repay/success',
            '11' => '/channelapi/repay/success',
            '12' => '/channelapi/repay/success',
            '13' => '/channelapi/repay/success',
            '14' => '/channelapi/repay/success',
        ];
        $this->error_url  = [
            '1'  => '/new/repay/error',
            '2'  => '/new/repay/errorapp',
            '3'  => '/new/repay/error',
            '4'  => '/new/repay/error',
            '5'  => '/new/repay/errorapp',
            '6'  => '/new/repay/errorapp',
            '7'  => '/new/repay/error',
            '8'  => 'https://m.rong360.com/center',
            '10' => '/channelapi/repay/error',
            '11' => '/channelapi/repay/error',
            '12' => '/channelapi/repay/error',
            '13' => '/channelapi/repay/error',
            '14' => '/channelapi/repay/error',
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
            case 2:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                if (!empty($source)) {
                    $url .= '&source=' . $source;
                }
                break;
            case 3:
            case 4:
            case 5:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                if (!empty($source)) {
                    $url .= '&source=' . $source;
                }
                break;
            case 6:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                if (!empty($source)) {
                    $url .= '&source=' . $source;
                }
                break;
            case 7:
                $mapping_info = Loan_mapping::find()->where(['loan_id' => $loan_repay->loan_id])->one();
                if ($status == 1) {
                    if (!empty($mapping_info)) {
                        $url = $mapping_info['callbackurl'] . "&status=1";
                    } else {
                        $url = $this->success_url . "?repay_id=" . $loan_repay['repay_id'];
                    }
                    break;
                } else {
                    if (!empty($mapping_info)) {
                        $url = $mapping_info['callbackurl'] . "&status=0";
                    }
                    break;
                }
            case 8:
                $url          = $this->notify_url[$loan_repay->source];
                break;
            case 9:
                $mapping_info = Loan_mapping::find()->where(['loan_id' => $loan_repay->loan_id])->one();
                if (!$mapping_info) {
                    Logger::errorLog(print_r(array($loan_repay['repay_id'] . "：mappinf_info is empty1"), true), 'notify_err', 'jiedianqian');
                    exit("网络错误!");
                }
                $callbacksrt = $mapping_info->callbackurl;
                $callbackArr = explode('^', $callbacksrt);
                if (!$callbackArr || !is_array($callbackArr) || !isset($callbackArr[0]) || !isset($callbackArr[1])) {
                    Logger::errorLog(print_r(array($loan_repay['repay_id'] . ": mappinf_info is empty2"), true), 'notify_err', 'jiedianqian');
                    exit("网络错误!");
                }
                $seccess_url = $callbackArr[0];
                $error_url   = $callbackArr[1];
                if ($status == 1) {
                    $url = $seccess_url;
                } else {
                    $url = $error_url;
                }
                break;
            case 10:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                break;
            case 11:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                break;
            case 12:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                break;
            case 13:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                break;
            case 14:
                if ($status == 1) {
                    $url = $this->notify_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                } else {
                    $url = $this->error_url[$loan_repay->source] . '?repay_id=' . $loan_repay->repay_id;
                }
                break;
        }
        return $url;
    }

    //在线还款服务器异步通知地址
    public function actionIndex() {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = $this->get('res_data');
        } else {
            $data = $this->post('res_data');
        }
        $parr   = $openApi->parseReturnData($data);
        $isPost = Yii::$app->request->isPost;
        $source = Yii::$app->request->get('source', '');
//        $parr   = [
//            'res_code' => 0,
//            'res_data' => [
//                'pay_type'  => 114,
//                'status'    => '11',
//                'orderid'   => 'Y1224142143223728239',
//                'yborderid' => '201803141630047489779367132',
//                'amount'    => 100,
//                'res_code'  => '',
//                'res_msg'   => '',
//                'app_id'    => '2810335722015',
//            ]
//        ];
//        $isPost = 1 ;
//        $source=1;
        Logger::dayLog('new_notify', $parr);
        if (!isset($parr['res_data']['orderid']) || empty($parr['res_data']['orderid'])) {
            return FALSE;
        }

        $loan_repay = (new Loan_repay())->getRepayByOrderId($parr['res_data']['orderid']);
        if (empty($loan_repay)) {
            return FALSE;
        }
        if ($isPost) {
            $this->postNotify($loan_repay, $parr);
        } else {
            return $this->getNotify($loan_repay, $parr, $source);
        }
    }

    private function getNotify($loan_repay, $parr, $source) {
        if ($loan_repay->status == 0) {
            if (isset($parr['res_data']['status']) && $parr['res_data']['status'] == '11') {
                $conditon = ['status' => 4];
                $couponConditon = ['repay_status' => 4];
            } else {
                $conditon = ['status' => -1];
                $couponConditon = ['repay_status' => -1];
            }
            $get_up_result = $loan_repay->update_repay($conditon);
            if (!$get_up_result) {
                Logger::dayLog('new_notify', 'get_update_faile' . $parr['res_data']['orderid'], $conditon);
            }
            //优惠卷
            $repayCouponUseObj = (new RepayCouponUse())->getByRepayId($loan_repay->id);
            if(!empty($repayCouponUseObj) && $repayCouponUseObj->repay_status == 0){
                $repayCouponUseResult = $repayCouponUseObj->updateRecord($couponConditon);
                if(empty($repayCouponUseResult)){
                    Logger::dayLog('new_notify', 'repay_coupon_use_update_faile' . $parr['res_data']['orderid'], $couponConditon);
                }
            }
            // 支付请求后，同步返回结果时， 锁定到支付中
            (new StageService())->lockToRepaying($loan_repay->id);
        }
        if ($parr['res_code'] == 0) {
            if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                $url = $this->getUrl($loan_repay, 1, $source);
                Logger::dayLog('new_notify', 'get_url_1', $url);
                return $this->redirect($url);
            } else {
                $url = $this->getUrl($loan_repay, 2, $source);
                Logger::dayLog('new_notify', 'get_url_2', $url);
                return $this->redirect($url);
            }
        } else {
            $url = $this->getUrl($loan_repay, 2, $source);
            return $this->redirect($url);
        }
    }

    private function postNotify($loan_repay, $parr) {
        if (empty($loan_repay || empty($parr))) {
            exit;
        }
        $loan_id  = $loan_repay->loan_id;
        $loaninfo = User_loan::findOne($loan_id);
        $userinfo = $loaninfo->user;
        $amount   = isset($parr['res_data']['amount']) ? $parr['res_data']['amount'] / 100 : 0;
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {//成功处理
            if (in_array($loaninfo->business_type, [5, 6, 11])) {  //分期还款
                $this->stagesRepaySuccess($loan_repay, $loaninfo, $amount, $parr, $userinfo);
            } else {   //不分期还款
                $this->repaySuccess($loan_repay, $loaninfo, $amount, $parr, $userinfo);
            }
        } else if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 11) {//失败处理
            if (in_array($loaninfo->business_type, [5, 6, 11])) {   //分期还款
                $this->stagesRepayFail($loan_repay, $parr, $userinfo, $loaninfo, $amount);
            } else { //不分期还款
                $this->repayFail($loan_repay, $parr, $userinfo, $loaninfo, $amount);
            }
        } else {
            if ($loan_repay->source != 4) {
                $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 2);
            }
            echo 'SUCCESS';
            exit;
        }
    }

    //分期还款失败更新分期还款表
    private function stagesRepayFail($loan_repay, $parr, $userinfo, $loaninfo, $amount) {
        $conditon  = [
            'status'  => 4,
            'paybill' => isset($parr['res_data']['yborderid']) ? $parr['res_data']['yborderid'] : '',
        ];
        $up_result = $loan_repay->update_repay($conditon);
        //发送还款失败通知
        if ($loan_repay->source != 4 && $up_result) {
            //发送短信
            $this->sendSms($userinfo->mobile, $loaninfo, $amount, $type = 2, $leftAmount = 0);
        }
        //更改失败处理
        $res=(new StageService())->toFail($loan_repay->id);
        if ($up_result && $res) {
            echo 'SUCCESS';
        } else {
            Logger::dayLog('new_notify', 'post_update_faile', $parr['res_data']['orderid']);
        }
        exit;
    }

    //不分期还款失败更新还款表
    private function repayFail($loan_repay, $parr, $userinfo, $loaninfo, $amount) {
        $conditon  = [
            'status'  => 4,
            'paybill' => isset($parr['res_data']['yborderid']) ? $parr['res_data']['yborderid'] : '',
        ];
        $up_result = $loan_repay->update_repay($conditon);
        //优惠卷
        $repayCouponUseObj = (new RepayCouponUse())->getByRepayId($loan_repay->id);
        if(!empty($repayCouponUseObj)){
            $repayCouponUseResult = $repayCouponUseObj->updateRecord(['repay_status'=>4]);
            if(empty($repayCouponUseResult)){
                Logger::dayLog('new_notify', '优惠卷记录更新失败' . $loan_repay->repay_id, ['repay_status'=>4]);
                $up_result = false;
            }
        }
        //发送还款成功通知  @TODO 已做修改
        if ($loan_repay->source != 4 && $up_result) {
            $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 2);
        }
        if ($up_result) {
            echo 'SUCCESS';
        } else {
            Logger::dayLog('new_notify', 'post_update_faile', $parr['res_data']['orderid']);
        }

        exit;
    }

    //不分期还款成功 更新还款记录
    private function updateRepayFail($loan_repay, $parr, $amount) {
        $times           = date('Y-m-d H:i:s');
        $repay_condition = [
            'status' => 2,
        ];
        $ret             = $loan_repay->update_repay($repay_condition);
        if (!$ret) {
            Logger::dayLog('new_notify', $loan_repay->repay_id, $parr['res_data']['status'], '更新还款订单失败');
        }
        return $ret;
    }

    //分期还款成功 更新分期还款记录
    private function updateRepay($loan_repay, $parr, $amount) {
        $times           = date('Y-m-d H:i:s');
        $repay_condition = [
            'status'       => 1,
            'platform'     => $this->__repaymentChannel($parr['res_data']['pay_type']),
            'actual_money' => bcdiv(bcmul($amount,100,2),100,2),//接受金额精度计算
            'paybill'      => $parr['res_data']['yborderid'],
//            'left_money'   => $leftAmount,
            'repay_time'   => $times,
        ];
        $ret             = $loan_repay->update_repay($repay_condition);
        if (!$ret) {
            Logger::dayLog('new_notify', $loan_repay->repay_id, $parr['res_data']['status'], '更新还款订单失败');
        }
        //优惠卷
        $repayCouponUseObj = (new RepayCouponUse())->getByRepayId($loan_repay->id);
        if(!empty($repayCouponUseObj)){
            $repayCouponUseResult = $repayCouponUseObj->updateRecord(['repay_status'=>1]);
            if(empty($repayCouponUseResult)){
                Logger::dayLog('new_notify', '优惠卷记录更新失败' . $loan_repay->repay_id, ['repay_status'=>1]);
                $ret = false;
            }
            $couponListObj = (new Coupon_list())->getById($repayCouponUseObj->discount_id);
            if(!empty($couponListObj)){
                $couponListResult = $couponListObj->_saveCouplist(['use_time'=>date('Y-m-d H:i:s')]);
                if(empty($couponListResult)){
                    Logger::dayLog('new_notify', '优惠卷使用记录更新失败' . $loan_repay->repay_id, ['status'=>1]);
                }
            }
        }
        return $ret;
    }

    //分期还款成功
    private function stagesRepaySuccess($loan_repay, $loaninfo, $amount, $parr, $userinfo) {
        if (empty($loan_repay) || !empty($loan_repay->paybill) || $loan_repay->status == 1 || $loan_repay->status == 4) {
            exit('SUCCESS');
        }
        $ret= $this->updateRepay($loan_repay, $parr, $amount);
        if (!$ret) {
            exit();
        }
        $platform = $this->__repaymentChannel($parr['res_data']['pay_type']);
        //支付请求异步结果回来后更新成功状态
        $res=(new StageService())->toSuccess($loan_repay->id,$loan_repay);
        //是否要结清userloan
        $where=[
            'AND',
            ['loan_id'=>$loaninfo->loan_id],
            ['<>','bill_status',8],
        ];
        $oGoodsBill=GoodsBill::find()->where($where)->one();
        if(empty($oGoodsBill)){//结清
            $overres=$loaninfo->saveInstallmentRepayNormal();
        }
        if($res){
            //发送短信
            $this->sendSms($userinfo->mobile, $loaninfo, $amount, $type = 1,$leftAmount = 0);
            exit('SUCCESS');
        }
        exit('FAIL');
    }

    //结清user_loan 并加入白名单
    private function clearUserLoan($loaninfo, $parr) {
        //还款结清
        $times      = date('Y-m-d H:i:s');
        $change_ret = $loaninfo->changeStatus(8);
        if (!$change_ret) {
            Logger::dayLog('new_notify', $loaninfo->loan_id, $parr['res_data']['status'], '更新借款状态为8失败');
            return false;
        }
        $loan_condition = [
            'repay_type' => 2,
            'repay_time' => $times,
        ];
        $loan_ret       = $loaninfo->update_userLoan($loan_condition);
        if (!$loan_ret) {
            Logger::dayLog('new_notify', $loaninfo->loan_id, $parr['res_data']['status'], '更新借款信息失败');
            return false;
        }

        if (in_array($loaninfo->business_type, [1, 4, 9, 10])) {
            $where       = [
                "AND",
                ['loan_id' => $loaninfo->loan_id],
                ['!=', 'loan_status', 8],
            ];
            $overdusLoan = OverdueLoan::find()->where($where)->one();
            if (!empty($overdusLoan)) {
                $overdusLoan->clearOverdueLoan();
                if (!$overdusLoan) {
                    Logger::dayLog('new_notify', $overdusLoan, '更新逾期账单结清状态失败');
                    exit;
                }
            }
        }


        $userModel = new User();
        $userModel->inputWhite($loaninfo->user_id);
        return $change_ret && $loan_ret;
    }

    //不分期还款成功回调方法
    private function repaySuccess($loan_repay, $loaninfo, $amount, $parr, $userinfo) {
        if (!empty($loan_repay) && !in_array($loan_repay->status, [1, 4])) {
            $ret = $this->updateRepay($loan_repay, $parr, $amount);
            if (!$ret) {
                exit;
            }
            $huankuan_money = $loaninfo->getRepaymentAmount($loaninfo);
            if ($loaninfo->type != 3 && bccomp($huankuan_money, 0, 2) == 1) {
                    //发送还款成功通知
                    if ($loan_repay->source != 4) {
                        $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 1);
                    }
                    echo 'SUCCESS';
                    exit;
            }
            if ($loaninfo->type == 3 && bccomp($huankuan_money, 0, 2) == 1) {
                $newFee = $this->getNewInterfee($loaninfo, $loan_repay);
                if(bccomp($huankuan_money, $newFee, 2) != 0){
                    //发送还款成功通知
                    if ($loan_repay->source != 4) {
                        $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 1);
                    }
                    echo 'SUCCESS';
                    exit;
                }else{
                    $res = $loaninfo->updateInterestFee(strtotime($loan_repay->createtime));
                    $loaninfo->refresh();
                }
            }

            //还款结清
            $res = $this->clearUserLoan($loaninfo, $parr);
            //放款成功增加三次抽奖
            $start_time = date("Y-m-d 00:00:00", time());
            $end_time = date("Y-m-d H:i:s", time());
            if($start_time >="2018-04-12 00:00:00" && $end_time< "2018-04-27 00:00:00"){
                $activ_info = Activity_newyear::find()->where(['user_id' => $loaninfo['user_id'],'type' => 5])->one();
                $model = new Activity_newyear();
                if(empty($activ_info)){
                    $model->addNewyearinfo($loaninfo['user_id'], "invite_num", 5,3);
                } else {
                    $activ_info->updateNum("invite_num", 5,3);
                }
            }
            if (!$res) {
                exit;
            }
            //发送还款成功通知
            if ($loan_repay->source != 4) {
                $this->sendSms($userinfo['mobile'], $loaninfo, $amount, 1);
            }
            echo 'SUCCESS';
            exit;
        } else {
            echo 'SUCCESS';
            exit;
        }
    }

    private function getNewInterfee($loanInfo, $repayInfo){
        $days = ceil((time() - strtotime($repayInfo->createtime)) / 24 / 3600);
        $interest_fee = $days*0.00049*$loanInfo->amount;
        return $interest_fee;
    }

    //续期还款服务器异步通知地址
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
//                'pay_type' => 172,
//                'status' => 2,
//                'orderid' => 'Y1224124200223728230',
//                'yborderid' => '20180813200732010431196478996690',
//                'amount' => 1,
//                'res_code' => '',
//                'res_msg' => '',
//                'app_id' => '2810335722015',
//            ],
//        ];
        Logger::dayLog('notify/renewal_repay',$nofify_type,$parr);
        if ($nofify_type == 'get') {
            if ($parr['res_code'] == 0) {
                if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                    $renewal_info = Renewal_payment_record::find()->where(['order_id' => $parr['res_data']['orderid']])->one();
                    if (!empty($renewal_info)) {//先判断状态是不是0 0的情况下做修改
                        if ($renewal_info->status == 0) {
                            $data = ['status' => -1];
                            $res  = $renewal_info->update_batch($data);
                            Logger::dayLog('notify/renewal_repay',$nofify_type,$renewal_info->loan_id, '续期同步状态修改' . $res);
                        }
                        $source = $renewal_info['source'];
                    } else {
                        $source = 1;
                    }
                    return $this->redirect('/new/repay/verify?source=' . $source);
                } else {
                    return $this->redirect('/new/repay/errorapp');
                }
            } else {
                return $this->redirect('/new/repay/errorapp');
            }
        }
        $repay = Renewal_payment_record::find()->where(['order_id' => $parr['res_data']['orderid']])->one();
        Logger::dayLog('notify/renewal_repay',$nofify_type,$parr['res_data']['orderid'],'Renewal_payment_record',$repay);
        if (empty($repay)) {
            exit;
        }
        $loan_id  = $repay->loan_id;
        $loaninfo = User_loan::findOne($loan_id);
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {
            if (empty($repay->paybill) && !in_array($repay->status, ['1', '4'])) {
                //出款渠道
                $platform     = $this->__repaymentChannel($parr['res_data']['pay_type']);
                $actual_money = round($parr['res_data']['amount'] / 100, 2);
                $paybill      = $parr['res_data']['yborderid'];
                
                $ret = $repay->saveSuccess($platform, $actual_money, $paybill);
                Logger::dayLog('notify/renewal_repay',$nofify_type,$repay->loan_id, '续期记录' . $ret);
                if (!$ret) {
                    exit;
                }
                $res = $loaninfo->createRenewLoan($repay->create_time,$repay->id);
                Logger::dayLog('notify/renewal_repay',$nofify_type,$repay->loan_id, '新建借款期记录' . $res);
                if ($res) {
                    //先花商城续期成功短信通知 $loaninfo->business_type==9 
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
                if($loaninfo->business_type==9){//商城借款
                    $o_renew_amount = (new Renew_amount())->getRenew($loan_id,$now_date = '',$type=2);
                    $date = '';
                    $oUser = User::findOne($loaninfo->user_id);
                    if(!empty($o_renew_amount) && !empty($oUser)){
                        if($o_renew_amount->end_time > date('Y-m-d H:i:i',time())){
                            $date = $this->getDate(strtotime($o_renew_amount->end_time));
                        }
                    }
                    (new ApiSmsShop())->sendXuqiFail($oUser->mobile, $date, 47); //短信
                    if(!empty($date)){
                        $res1 = (new WarnMessageList())->saveWarnMessage($loaninfo,1,15,$date); //app push
                         Logger::dayLog('notify/renewal_repay','111',$res1);
                    }else{
                        $res2 =(new WarnMessageList())->saveWarnMessage($loaninfo,1,16);//app push
                         Logger::dayLog('notify/renewal_repay','222',$res2);
                    }
                }
                
                echo 'SUCCESS';
            }
            exit;
        } else {
            echo 'SUCCESS';
            exit;
        }
    }
    public function getDate($endTime){
        $nowTime = strtotime(date('Y-m-d H:i:s'));
        if ($nowTime > $endTime) {
            return NULL;
        }
        //计算天数
        $timediff = $endTime - $nowTime;
        $days = intval($timediff / 86400);
        //计算小时数
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        //计算分钟数
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        //计算秒数
        $secs = $remain % 60;
        $res = str_pad($days, 2, "0", STR_PAD_LEFT).'天'.str_pad($hours, 2, "0", STR_PAD_LEFT).':'.str_pad($mins, 2, "0", STR_PAD_LEFT).':'.str_pad($secs, 2, "0", STR_PAD_LEFT);
//        $res = array("day" => str_pad($days, 2, "0", STR_PAD_LEFT),"hour" => str_pad($hours, 2, "0", STR_PAD_LEFT), "min" => str_pad($mins, 2, "0", STR_PAD_LEFT), "sec" => str_pad($secs, 2, "0", STR_PAD_LEFT));
        return $res;
    }

    /**
     * 借款在线还款结果短信通知用户
     * @param type $mobile 接收短信的手机号
     * @param type $loan 借款
     * @param type $type 1、支付成功，2、支付失败
     */
    private function sendSms($mobile, $loaninfo, $amount, $type = 2, $leftAmount = 0) {
        $phase=1;
        $totalphase=1;
        $newLoaninfo    = User_loan::findOne($loaninfo->loan_id);
        if(in_array($newLoaninfo->business_type, [5, 6, 11])){
            $BillRepay_Arr=BillRepay::find()->where(['loan_id'=>$loaninfo->loan_id,'status'=>6])->asArray()->all();
            $GoodsBill_Arr=GoodsBill::find()->where(['loan_id'=>$loaninfo->loan_id])->all();
            $phase=count($BillRepay_Arr);
            $totalphase=count($GoodsBill_Arr);
        }
        $huankuan_money = $newLoaninfo->getRepaymentAmount($loaninfo, 2);
        Logger::dayLog('repay_notify', 'huankuan_money', $huankuan_money);
        $apiSms         = new ApiSms();
        switch ($type) {
            case 1:
                if (bccomp($huankuan_money, 0, 2) > 0) {
                    if( in_array($newLoaninfo->business_type, [5, 6, 11])){//分期部分还款
                        $res = $apiSms->sendSmsByRepaymentTerms($mobile, $amount, $phase ,$totalphase);
                    }else{//单期部分还款
                        $res = $apiSms->sendSmsByRepaymentPortion($mobile, $amount, $huankuan_money);
                    }
                } else {
                    $res = $apiSms->sendSmsByRepaymentAll($mobile);
                }
                break;
            case 2:
                if (bccomp($huankuan_money, 0, 2) > 0) {
                    if(in_array($newLoaninfo->business_type, [5, 6, 11])){
                        $userloanModel = new User_loan();
                        $waitPayData = $userloanModel->shouldPayAmount($loaninfo); //本金、最后还款日、利息
                        $management_amount = (new OverdueLoan())->getoverAmount($loaninfo); //贷后管理费
                        $huankuan_money= $waitPayData['bj_amount']+$waitPayData['overdue_bjamount']+ $waitPayData['interest_amount'] + $management_amount;//本期应还总金额
                    }
                    $res = $apiSms->sendSmsByRepaymentFailedNew($mobile, $huankuan_money);
                }
                break;
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

}

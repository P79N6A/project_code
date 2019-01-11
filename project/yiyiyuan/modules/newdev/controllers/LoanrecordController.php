<?php
/**
 * 借款相关记录与详情
 * Created by PhpStorm.
 * User: wangyongqiang
 * Date: 2017/4/26
 * Time: 15:56
 */
namespace app\modules\newdev\controllers;


use app\models\dev\User_loan_flows;
use app\models\news\RemitSuccessList;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Loan_repay;
use app\models\news\Renewal_payment_record;
use app\models\news\Renew_amount;
use app\models\service\UserloanService;
use Yii;

class LoanrecordController extends NewdevController
{
    private $__header_home = "/new/loan";  //借款首页
    public $layout = 'loanmain';

    /**
     * 借款记录
     */
    public function actionLoanrecord()
    {
        $this->getView()->title = "借款记录";
        $this->layout = 'loanlist';
        $userinfo = $this->getUser();
        //判断用户的性别
        $card_length = strlen($userinfo['identity']);
        $sex = $card_length == 15 ? substr($userinfo['identity'], 14) : substr($userinfo['identity'], 16, 1);
        // $loanlist  = array(
        //     'credit' => $this->creditRecord(), //信用借款记录
        //     'secured' => $this->securedLoanRecord(), //担保借款记录
        // );
        $loanlist  = $this->creditRecord();
        return $this->render('loanlist', ['loanlist' => $loanlist, 'sex' => $sex]);
    }
    /**
     * 所有借款记录
     */
    private function creditRecord()
    {
        $userinfo = $this->getUser();
        //取出用户所有借款订单
        $where = [
            'AND',
//            ['>=', 'create_time', '2017-01-01'],
            ['user_id' => $userinfo['user_id']],
            ['business_type' => [1,4,5,6]]
        ];
        $loanlist = User_loan::find()->where($where)->orderBy('create_time desc')->all();
        if (!empty($loanlist)){
            $userLoanModel = new User_loan();
            foreach($loanlist as $key=>$value){
                //9已出款；12还款异常(未还款逾期)；13 还款异常(部分还款 逾期)；
                $loan_status = array(9, 12, 13);
                if (in_array($value['status'], $loan_status)){
                    $loanlist[$key]['shareurl'] = $this->__loanCoupon($value['business_type'], $value['loan_id']);
                }else{
                    $loanlist[$key]['shareurl'] = '';
                }
                //判断借款初次发生时间
                if ($value['loan_id'] != $value['parent_loan_id'] && !empty($value['parent_loan_id)'])) {
                    $loanlist[$key]['create_time'] = $value['start_date'];
                }
                //借款状态
                $loanStatue = $userLoanModel->getLoanStatusView($value);
                $loanlist[$key]['status'] = $loanStatue['status'];
            }
        }
        return $loanlist;

    }

    /**
     * 担保借款记录
     */
    private function securedLoanRecord()
    {
        $userinfo = $this->getUser();
        //取出用户所有担保借款订单
        $where = [
            'AND',
//            ['>=', 'create_time', '2017-01-01'],
            ['user_id' => $userinfo['user_id']],
            ['business_type' => [4,6]]
        ];
        $loanlist = User_loan::find()->where($where)->orderBy('create_time desc')->all();
        if (!empty($loanlist)){
            $userLoanModel = new User_loan();
            foreach($loanlist as $key=>$value){
                //9已出款；12还款异常(未还款逾期)；13 还款异常(部分还款 逾期)；
                $loan_status = array(9, 12, 13);
                if (in_array($value['status'], $loan_status)){
                    $loanlist[$key]['shareurl'] = $this->__loanCoupon($value['business_type'], $value['loan_id']);
                }else{
                    $loanlist[$key]['shareurl'] = '';
                }
                //判断借款初次发生时间
                if ($value['loan_id'] != $value['parent_loan_id'] && !empty($value['parent_loan_id)'])) {
                    $loanlist[$key]['create_time'] = $value['start_date'];
                }
                //借款状态
                $loanStatue = $userLoanModel->getLoanStatusView($value);
                $loanlist[$key]['status'] = $loanStatue['status'];
            }
        }
        return $loanlist;
        //return $this->render('loanlist', ['loanlist' => $loanlist, 'sex' => $sex]);
    }

    /**
     * 借款详情
     */
    public function actionCreditdetails()
    {
        $this->layout = "new/loanrecord";
        $this->getView()->title = "借款详情";
        $loan_id = $this->get('loan_id',0);
        $loaninfo = User_loan::findOne($loan_id);
        $loaninfo->chase_amount = $loaninfo->getChaseamount($loan_id);
        if(empty($loaninfo)){//loaninfo为空走首页,lml17/8/31
            return $this->redirect("/new/loan/");
        }
        $loan_coupon = '';
        if (!empty($loaninfo) && !empty($loaninfo->couponUse)){
            $loan_coupon = $loaninfo->couponUse;
        }
        $already_amount = $loaninfo->getRepayAmount(2);
        $loaninfo['huankuan_amount'] = $already_amount === NULL ? 0 : $already_amount;
        //还款时间
        $userloanService = new UserloanService();
        $repay = $userloanService->getHuankuanTime($loaninfo);

        //查询用户还款信息
        $repayinfo = Loan_repay::find()->select(array('createtime'))->where(['loan_id' => $loan_id])->orderBy('createtime')->one();
        //服务费
        $service_amount = number_format($loaninfo->withdraw_fee,2);

        //续期金额
        $renewModel = new Renew_amount();
        $renew_amount = 0;
        $renewAmountObj = $renewModel->getRenewOne($loaninfo->loan_id);
        if(!empty($renewAmountObj)){
            $renew_amount = $renewAmountObj->renew_fee;
        }

        //逾期罚息金额
        $overdue_amount = (new User_loan())->getOverdueAmount($loaninfo->loan_id);
        if(in_array($loaninfo['status'], [5,6,9,11,12,13]) || ($loaninfo['status'] == 3 && $loaninfo['prome_status'] == 1)){
            return $this->redirect("/new/loan/showloan?loan_id=" . $loaninfo['loan_id']);
        }
        //settle_type:0：初始状态；1：还款结清；2：续期结清；3：续期中
        if($loaninfo['status'] == 3 || $loaninfo['status'] == 7){
            $loan_flows = User_loan_flows::find()->where(['loan_id'=>$loan_id])->one();
            $data_set = array(
                'loan_coupon' => $loan_coupon,
                'loaninfo' => $loaninfo,
                'repayinfo' => $repayinfo,
                'business_type' => $loaninfo['business_type'],
                'service_amount' => $service_amount,
                'loan_flows' =>$loan_flows,
                'overdue_amount'=> $overdue_amount
            );
            return $this->render('succfail', $data_set);
        }
        if ($loaninfo['settle_type'] == 2) {
            $repay_time = Renewal_payment_record::find()->select(array('last_modify_time'))->where(['loan_id' => $loan_id, 'status' => 1])->one();
            $data_set = array(
                'loan_coupon' => $loan_coupon,
                'loaninfo' => $loaninfo,
                'repayinfo' => $repayinfo,
                'business_type' => $loaninfo['business_type'],
                'service_amount' => $service_amount,
                'repay_time' => $repay_time['last_modify_time'],
                'renew_amount'=> $renew_amount,
                'overdue_amount'=> $overdue_amount
            );
            return $this->render('succrenewal', $data_set);
        }

        //保单号
        $insuranceOrder = (new RemitSuccessList())->getInsureNumberByLoanId($loaninfo['loan_id']);
        $data_set = array(
            'loan_coupon' => $loan_coupon,
            'loaninfo' => $loaninfo,
            'repayinfo' => $repayinfo,
            'business_type' => $loaninfo['business_type'],
            'service_amount' => $service_amount,
            'overdue_amount'=> $overdue_amount,
            'repay_time'=> $repay['huankuantime'],
            'insurance_order'=> $insuranceOrder,
        );
        return $this->render('succend', $data_set);
    }

    /**
     * 借款对应的优惠券
     * @param $business_type
     * @param $loan_id
     * @return bool|string
     */
    private function __loanCoupon($business_type, $loan_id)
    {
        if (empty($business_type) || empty($loan_id)) return false;
        $time = time();
        //判断用户优惠券
        $coupon_list_info = new Coupon_list();
        $loan_coupon = $coupon_list_info->getLoanCoupon($loan_id);
        //val:面值：0表示全免
        //status:2表示已使用
        if (!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) {
            $shareurl = "/new/loan/succ?l=" . $loan_id;
        } else {
            //business_type:1:好友;2:担保;3:担保人
            if ( $business_type == 1) {
                $shareurl = "/new/share/likestat?t=" . $time . "&d=" . $loan_id . "&s=" . md5($time . $loan_id);
            } else {
                $shareurl = "/new/loan/succ?l=" . $loan_id;
            }
        }
        return $shareurl;
    }

}
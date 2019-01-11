<?php

namespace app\modules\borrow\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\models\news\BehaviorRecord;
use app\models\news\Insure;
use app\models\news\Loan_repay;
use app\models\news\OverdueLoan;
use app\models\news\Renew_amount;
use app\models\news\Renewal_payment_record;
use app\models\news\RenewalInspect;
use app\models\news\User_loan;
use app\models\news\GoodsBill;
use Yii;

class BilllistController extends BorrowController {

    public $layout = 'bill/bill';


    public function actionIndex() {
        $this->get();
        $this->getView()->title = "账单";
        $userInfo = $this->getUser();

        $loan_hgamount=0;
        $loan_expect_day=0;
        $loan_count=0;//代还款笔数
        $expect_num=0;//逾期笔数
        $loan_last_day=0;//最后借款还款日
        $loan_time_desc='';
        $loan_id=0;
        $business_type=0;
        $loan_expect_status=1;//正常
        $user_id=$userInfo->user_id;
        $loan_hgamount_total = 0;
        //查询亿元借款账单
        $oUserLoan = (new User_loan())->getLoan($user_id,[8,9,11,12,13]);
        if($oUserLoan && (($oUserLoan->loanextend && $oUserLoan->loanextend->status == 'SUCCESS') || $oUserLoan->settle_type==3) && $oUserLoan->status != 8){
            //获取应还款金额
            $loan_id=$oUserLoan->loan_id;
            $business_type = $oUserLoan->business_type;
            $loan_count=$loan_count+1;
            $loan_hgamount_total = (new User_loan())->getRepaymentAmount($oUserLoan); 
//            $loan_hgamount=(new User_loan())->getRepaymentAmount($oUserLoan);
//            
//            if($loan_hgamount<0){
//                $loan_hgamount=0;
//            }
            $loan_last_day=date('Y/m/d',strtotime($oUserLoan->end_date)-24*3600);
            if($oUserLoan->status==11){//还款确认中
                $loan_status=2;
                if(time()>strtotime($oUserLoan->end_date)){
                    $loan_expect_day=(new User_loan())->getOverdueDays($oUserLoan);
                    $loan_expect_status=2;
                    $expect_num=$expect_num+1;
                }
                $oLoanRepay=Loan_repay::find()->where(['status'=>[-1,3],'loan_id'=>$loan_id])->orderBy('id desc')->one();
                if(!empty($oLoanRepay)){
                    if($oLoanRepay->status==3){//线下还款
                        $loan_time_desc='预计24小时内确认完成';
                    }else{
                        $loan_time_desc='预计2小时内确认完成';
                    }
                }
            }else{
                $loan_status=1;//立即还款
                $oLoanRepay=Loan_repay::find()->where(['status'=>[-1,0],'loan_id'=>$loan_id])->orderBy('id desc')->one();
                if(!empty($oLoanRepay)){
                    $loan_status=2;
                    if($oLoanRepay->status==0 && time()>(strtotime($oLoanRepay->createtime)+240)){
                        $loan_status=1;
                    }
                }
        
                if(in_array($oUserLoan->status,[12,13])){//已逾期
                    $loan_expect_day=(new User_loan())->getOverdueDays($oUserLoan);
                    $loan_expect_status=2;
                    $expect_num=$expect_num+1;
                }else if($oUserLoan->status==9){//status==9
                    if($oUserLoan->settle_type==3){//已续期
                        $loan_status=4;
                    }
                    if( in_array($oUserLoan->business_type, [5, 6, 11]) ){
                        //分期是否逾期 
                        $isorOverdueLoan = (new User_loan())->isorOverdueLoan($oUserLoan,2);
                        if($isorOverdueLoan){
                            $loan_expect_day=(new GoodsBill())->getFqOverdueDays($oUserLoan);//逾期天数（兼容分期）
                            $loan_expect_status=2;
                            $expect_num=$expect_num+1;
                        }
                    }
                }
                //续期中
                $Renewal_payment_record=Renewal_payment_record::find()->where(['loan_id'=>$loan_id,'status'=>-1])->one();
                if($Renewal_payment_record){//续期中
                    $loan_status=3;
                    $loan_time_desc='预计10分钟内确认完成';
                }
            }
            $syLoanTime = strtotime($oUserLoan->end_date) - time();
            $repayIngInfo = Loan_repay::find()->where(['loan_id'=>$oUserLoan->loan_id,'status'=>-1])->one();
            $data =  ( new User_loan() )->getBillDetailData($oUserLoan);
            $loan_last_day = $data['last_day'];//最后还款日(兼容分期)
            $loan_hgamount = ($data['amount']<0) ? 0.00 : $data['amount'];//待还款金额(兼容分期)
        }
        //查询智融钥匙是否有白条账单
        $ious_hgamount=0;
        $ious_expect_day=0;
        $ious_last_day=0;
        $ious_url='';
        $ious_expect_status=1;
        $ious_time_desc='';
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $userInfo->mobile]);
        if(!empty($iousResult)){
            $ious_status=1;
            $loan_count=$loan_count+1;
            $yqIousTime = time()-strtotime($iousResult['end_time']);
            if($yqIousTime>0){
                $ious_expect_day=ceil($yqIousTime/24/3600);
            }else{
                $ious_expect_day=0;
            }
            $ious_last_day = date('Y/m/d',strtotime($iousResult['end_time'])-24*3600);
            $ious_hgamount=$iousResult['chase_amount'];
            if($ious_hgamount<0){
                $ious_hgamount=0;
            }
            if($iousResult['status']==11){//待还款确认
                $ious_status=2;
                if(isset($iousResult['is_repay_status'])){
                    if($iousResult['is_repay_status']==1){//线下
                        $ious_time_desc='预计24小时内确认完成';
                    }
                    if($iousResult['is_repay_status']==2){
                        $ious_time_desc='预计2小时内确认完成';
                    }
                }
            }
            $is_overdue=$iousResult['is_overdue'];

            if($is_overdue==1){//已逾期
                $ious_expect_status=2;
                $expect_num=$expect_num+1;
            }
            $mobile=$userInfo->mobile;
            $url=urlencode('/dev/iousdetails/index?ious_id='.$iousResult['ious_id']);
            $youxinDomain = Yii::$app->params['youxin_url'];
            $ious_url=$youxinDomain.'dev/iousdetails/index?ious_id='.$iousResult['ious_id'].'&userToken='.$mobile.'&url='.$url;
        }
//        $loan_hgamount_total = (new User_loan())->getRepaymentAmount($oUserLoan);
        $total_amount=number_format($loan_hgamount_total+$ious_hgamount,2);
        $loan_dialog_res=$this->GetLoanDialogStatus($oUserLoan,$iousResult,$loan_hgamount);//弹窗规则
        if(!empty($loan_dialog_res['type'])){
            $type=$loan_dialog_res['type'];
            $is_Behavior= BehaviorRecord::find()->where(['user_id' => $userInfo->user_id, 'loan_id'=>$loan_dialog_res['repay_id'],'type' => $type])->count();
            if($is_Behavior<1){
                $BehaviorModel = new BehaviorRecord();
                $BehaviorModel->addList(['user_id' => $userInfo->user_id,'loan_id'=>$loan_dialog_res['repay_id'],'type' => $type]);
            }else{
                $loan_dialog_res=[
                    'loan_dialog_status'=>0,
                    'dialog_desc'=>'',
                ];//如果存在就置空该弹窗
            }
        }

        if(Keywords::renewalInspectOpen() == 2){
            $inspect_ing = (new RenewalInspect())->getByUserIdAndStatus($user_id);
            if(!empty($inspect_ing)){
                $loan_status = 3;
            }
            $o_renewal_inspect = (new RenewalInspect())->getByStatus($user_id,2,0);
            if(!empty($o_renewal_inspect)){
                $o_renewal_inspect->updateIsShow();
                $loan_dialog_res=[
                    'loan_dialog_status'=>6,
                    'dialog_desc'=>'对不起，由于您的资质不足，续期失败'
                ];
            }
        }
        
        
        
        $jsinfo = $this->getWxParam();
        return $this->render('index_list', [
            'user_id'=>$user_id,
            'ious_id'=>empty($iousResult['ious_id']) ? 0 : $iousResult['ious_id'],
            'loan_id'=>$loan_id,
            'business_type'=>$business_type,
            'loan_status'=>empty($loan_status) ? 0 : $loan_status,
            'ious_status'=>empty($ious_status) ? 0 : $ious_status,
            'loan_expect_status'=>$loan_expect_status,//1：正常 2：已逾期
            'ious_expect_status'=>$ious_expect_status,//1：正常 2：已逾期
            'total_amount' => $total_amount,//还款总金额
            'loan_count' => $loan_count,//待还款总笔数
            'expect_num' => $expect_num,//逾期总笔数
            'loan_expect_day'=>$loan_expect_day,//借款逾期天数
            'ious_expect_day'=>$ious_expect_day,//白条逾期天数
            'loan_amount'=>number_format($loan_hgamount,2),//借款应还金额
            'ious_amount'=>number_format($ious_hgamount,2),//白条应还金额
            'loan_last_day'=>$loan_last_day,//借款最后还款日
            'ious_last_day'=>$ious_last_day,//白条最后还款日
            'loan_dialog_status'=>$loan_dialog_res['loan_dialog_status'],
            'dialog_desc'=>$loan_dialog_res['dialog_desc'],
            'ious_url'=>$ious_url,//白条跳转地址
            'loan_time_desc'=>$loan_time_desc,
            'ious_time_desc'=>$ious_time_desc,
        ]);
    }

    public function actionDetail(){
        $this->get();
        $this->getView()->title = "账单详情";
        $loan_id = Yii::$app->request->get('loan_id');
        if (empty($loan_id)) {
            return $this->redirect('/borrow/loan');
        }
        $oUserLoan = User_loan::find()->where(['loan_id'=>$loan_id])->one();
        if (empty($oUserLoan)) {
            return $this->redirect('/borrow/loan');
        }
        $user_id=$oUserLoan->user_id;
        $data =  ( new User_loan() )->getBillDetailData($oUserLoan);
        $is_renew_amout = (new Renew_amount())->entry($oUserLoan->loan_id);

        return $this->render('index_detail', [
            'user_id'=>$user_id,
            'loan_id'=>$loan_id,
            'amount'=> empty($data['amount'])? 0.00 : sprintf('%.2f', $data['amount']),
            'principal'=>empty($data['principal'])? 0.00 : sprintf('%.2f', $data['principal']),
            'loan_status'=>$data['loan_status'],
            'day'=>$data['day'],
            'renewal_day'=>$data['renewal_day'],
            'interest_amount'=> empty($data['interest_amount'])? 0.00:sprintf('%.2f', $data['interest_amount']),
            'management_amount'=> empty($data['management_amount'])?0.00: sprintf('%.2f', $data['management_amount']),
//            'is_renewal_able'=>$data['is_renewal_able'],
            'last_day'=>$data['last_day'],
            'is_renew_amout'=>$is_renew_amout,
            'period_num'=>$data['period_num'],
            'loan_type'=>$data['loan_type'],
            'overdue_bjamount'=>empty($data['overdue_bjamount'])?0.00: sprintf('%.2f', $data['overdue_bjamount']),
            'pay_goods_bill_id'=> empty($data['pay_goods_bill_id'])? '' : implode(",", $data['pay_goods_bill_id']),
            'csrf' => $this->getCsrf()
        ]);

    }

    public function actionDetailterm(){
        $loan_id = Yii::$app->request->get('loan_id',"");
        $this->getView()->title = "账单详情";
        if (empty($loan_id)) {
            return $this->redirect('/borrow/loan');
        }
        //查询是否有待还账单
        $yetBillList = (New GoodsBill())->getNotYetBillList($loan_id);//待还账单
        $mustBillList = (New GoodsBill())->getNotYetBillList($loan_id,2);//必还账单
        $allBillList = (New GoodsBill())->getNotYetBillList($loan_id,3);//所有账单
//        if(!$yetBillList || !$allBillList || !$mustBillList || empty($mustBillList) || empty($yetBillList) || empty($allBillList)){
        if(!$yetBillList || !$allBillList || empty($yetBillList) || empty($allBillList)){
            return $this->redirect('/borrow/loan');
        }
        $mustIds = array_column($mustBillList,'id');
        //组装账单信息
        $yetBillListNew = $this->getBillListNew($yetBillList,$mustIds);
        $oUserLoan = User_loan::findOne($loan_id);
        $resArr['allrepay'] = (New User_loan()) ->getRepaymentAmount($oUserLoan);//还款金额（兼容分期）
        $resArr['days'] = $yetBillList['0']['days'];//天数
        $resArr['terms'] = $yetBillList['0']['number'];//期数
        $resArr['yetbilllist'] = $yetBillListNew;
        return $this->render("detailterm",[
            'billlistInfo' => $resArr,
            'userId' => $oUserLoan->user_id,
            'loanId' => $loan_id,
        ]);

    }
    /*
     * 组装账单必还字段 repay_type 1为必还，2为可还
     */
    public function getBillListNew($yetBillList,$mustIds){
        $phaseArr = [1=>"首期应还金额",2=>"第二期应还金额",3=>"第三期应还金额",4=>"第四期应还金额",5=>"第五期应还金额",6=>"第六期应还金额",7=>"第七期应还金额",8=>"第八期应还金额",9=>"第九期应还金额"];
//        if(empty($yetBillList) || empty($mustIds) || !is_array($yetBillList) || !is_array($mustIds)){
        if(empty($yetBillList)  || !is_array($yetBillList)){
            return false;
        }
        foreach ($yetBillList as &$v){
            if(in_array($v['id'],$mustIds)){
                $v['repay_type'] = 1;
            }else{
                $v['repay_type'] = 2;
            }
            $v['bill_id'] = $v['id'];
            $v['end_time'] = date('Y-m-d',strtotime($v['end_time']));
            if(isset($phaseArr[$v['phase']])){
                $v['phase'] = $phaseArr[$v['phase']];
            }
        }
        return $yetBillList;
    }

    //合规下展期
    public function actionAjaxRenew(){
        if($this->isPost()){
            if(Keywords::renewalInspectOpen() != 2){
                exit(json_encode($this->reback('10241'), JSON_UNESCAPED_UNICODE));
            }
            $loan_id = $this->post('loan_id');
            if(empty($loan_id)){
                exit(json_encode($this->reback('99994'), JSON_UNESCAPED_UNICODE));
            }
            $o_user_loan = (new User_loan())->getById($loan_id);
            if (empty($o_user_loan)) {
                exit(json_encode($this->reback('10052'), JSON_UNESCAPED_UNICODE));
            }
            $tiem = date('Y-m-d H:i:s');
            $time_in = date("Y-m-d H:i:s", strtotime("-5 day", strtotime($o_user_loan->end_date)));
            $over_time_in = date("Y-m-d H:i:s", strtotime("+3 day", strtotime($o_user_loan->end_date)));
            if ($tiem < $time_in || $tiem > $over_time_in) {
                exit(json_encode($this->reback('10242'), JSON_UNESCAPED_UNICODE));
            }
            $o_renewal_inspect = (new RenewalInspect())->getByLoanId($loan_id);
            if (!empty($o_renewal_inspect)) {
                exit(json_encode($this->reback('10243'), JSON_UNESCAPED_UNICODE));
            }
            $condition = [
                'loan_id' => $loan_id,
                'user_id' => $o_user_loan->user_id,
                'status' => 0,
                'is_show_status' => 0
            ];
            $result = (new RenewalInspect())->addRecord($condition);
            if (!empty($result)) {
                exit(json_encode($this->reback('0000'), JSON_UNESCAPED_UNICODE));
            }
            exit(json_encode($this->reback('10244'), JSON_UNESCAPED_UNICODE));
        }else{
            exit(json_encode($this->reback('99997'), JSON_UNESCAPED_UNICODE));
        }
    }

    private function GetLoanDialogStatus($oUserLoan,$iousResult,$loan_hgamount){
        if(empty($oUserLoan) && empty($iousResult)){
            $res=[
                'loan_dialog_status'=>0,//不弹窗
                'type'=>'',
                'dialog_desc'=>'',
                'repay_id'=>'',
            ];
            return $res;
        }
        if(!empty($oUserLoan)){
            $loan_id=$oUserLoan->loan_id;
        }
        if(!empty($oUserLoan) && $oUserLoan->status==8){
            $repay_amount= Loan_repay::find()->where(['loan_id'=>$loan_id])->sum('actual_money');
            if(!empty($iousResult)){//status 9 11 12
                $res=[
                    'loan_dialog_status'=>5,//白条未还清且亿元已还清
                    'type'=>5,
                    'dialog_desc'=>'恭喜你，借款账单还款成功当前已还'.sprintf("%.2f",$repay_amount).'元，借款账单已结清！',
                    'repay_id'=>$loan_id,
                ];
                return  $res;
            }

            $res=[
                'loan_dialog_status'=>4,//还款成功(全部)
                'type'=>4,
                'dialog_desc'=>'恭喜你，借款账单还款成功当前已还'.sprintf("%.2f",$repay_amount).'元，借款账单已结清！',
                'repay_id'=>$loan_id,
            ];
            return  $res;
        }else{
            if(empty($oUserLoan)){
                $res=[
                    'loan_dialog_status'=>0,//不弹窗
                    'type'=>'',
                    'dialog_desc'=>'',
                    'repay_id'=>'',
                ];
                return $res;
            }
            $oLoanRepay= Loan_repay::find()->where(['loan_id'=>$loan_id])->all();
            if(empty($oLoanRepay)){
                if($oUserLoan->settle_type==3){
                    $res=[
                        'loan_dialog_status'=>2,//续期成功
                        'type'=>3,
                        'dialog_desc'=>'恭喜你，成功续期'.$oUserLoan->days.'天最后还款日变更为'.date('Y-m-d',strtotime($oUserLoan->end_date)-24*3600),
                        'repay_id'=>$loan_id,
                        ];
                    return $res;
                }
                $res=[
                    'loan_dialog_status'=>0,//不弹窗
                    'dialog_desc'=>'',
                    'repay_id'=>'',
                ];
                return $res;
            }else{
                $oLoanRpayStatusOne= Loan_repay::find()->where(['loan_id'=>$loan_id,'status'=>1])->orderBy('id desc')->one();
                $repay_amount= Loan_repay::find()->where(['loan_id'=>$loan_id])->sum('actual_money');
                if($oLoanRpayStatusOne){
                    $res=[
                        'loan_dialog_status'=>3,//还款成功(部分)
                        'type'=>6,
//                        'dialog_desc'=>'恭喜你，借款账单还款成功！当前已还'.sprintf("%.2f",$repay_amount).'元，剩余应还'.$loan_hgamount.'元！',
                        'dialog_desc'=>'恭喜您，成功还款'.sprintf("%.2f",$repay_amount).'元！',
                        'repay_id'=>$oLoanRpayStatusOne->id,
                    ];
                    return $res;
                }
                $oLoanRpayStatusFour= Loan_repay::find()->where(['loan_id'=>$loan_id,'status'=>4])->orderBy('id desc')->one();
                if($oLoanRpayStatusFour){
                    $res=[
                        'loan_dialog_status'=>1,//还款失败
                        'type'=>2,
                        'dialog_desc'=>'对不起，系统未收到你的还款款项请重新还款如有疑问,请联系客服',
                        'repay_id'=>$oLoanRpayStatusFour->id,
                    ];
                    return  $res;
                }

            }
        }

        $res=[
            'loan_dialog_status'=>0,//不弹窗
            'type'=>'',
            'dialog_desc'=>'',
            'repay_id'=>'',
        ];
        return $res;

    }
}

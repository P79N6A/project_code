<?php

namespace app\modules\api\controllers\controllers311;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\models\news\BehaviorRecord;
use app\models\news\Insure;
use app\models\news\Loan_repay;
use app\models\news\Renewal_payment_record;
use app\models\news\RenewalInspect;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class BilllistController extends ApiController
{

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        if (empty($version) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $userInfo = User::findOne($user_id);
        if (empty($userInfo)) {
            exit($this->returnBack('10001'));
        }

        $loan_hgamount=0;
        $loan_expect_day=0;
        $loan_count=0;//代还款笔数
        $expect_num=0;//逾期笔数
        $loan_last_day=0;//最后借款还款日
        $loan_time_desc='';
        $loan_id=0;
        $business_type = 0;
        $loan_expect_status=1;//正常
        //查询亿元借款账单
        $oUserLoan = (new User_loan())->getLoan($user_id,[8,9,11,12,13]);
        if($oUserLoan && (($oUserLoan->loanextend && $oUserLoan->loanextend->status == 'SUCCESS') || $oUserLoan->settle_type==3) && $oUserLoan->status != 8){
            //获取应还款金额
            $loan_id=$oUserLoan->loan_id;
            $business_type = $oUserLoan->business_type;
            $loan_count=$loan_count+1;
            $loan_hgamount=(new User_loan())->getRepaymentAmount($oUserLoan);
            if($loan_hgamount<0){
                $loan_hgamount=0;
            }
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
                }else{//status==9
                    if($oUserLoan->settle_type==3){//已续期
                        $loan_status=4;
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
        }
        //查询智融钥匙是否有白条账单
        $ious_hgamount=0;
        $ious_expect_day=0;
        $ious_last_day=0;
        $ious_url='';
        $ious_expect_status=1;
        $ious_time_desc='';
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $userInfo->mobile]);
        if (!empty($iousResult)) {
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
        $total_amount=$loan_hgamount+$ious_hgamount;
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
        $array = [
            'ious_id'=>empty($iousResult['ious_id']) ? 0 : $iousResult['ious_id'],
            'loan_id'=>$loan_id,
            'loan_status'=>empty($loan_status) ? 0 : $loan_status,
            'ious_status'=>empty($ious_status) ? 0 : $ious_status,
            'loan_expect_status'=>$loan_expect_status,//1：正常 2：已逾期 3续期中
            'ious_expect_status'=>$ious_expect_status,//1：正常 2：已逾期
            'total_hgamount' => $total_amount,//还款总金额
            'loan_count' => $loan_count,//待还款总笔数
            'expect_num' => $expect_num,//逾期总笔数
            'loan_expect_day'=>$loan_expect_day,//借款逾期天数
            'ious_expect_day'=>$ious_expect_day,//白条逾期天数
            'loan_amount'=>$loan_hgamount,//借款应还金额
            'ious_amount'=>$ious_hgamount,//白条应还金额
            'loan_last_day'=>$loan_last_day,//借款最后还款日
            'loan_dialog_status'=>$loan_dialog_res['loan_dialog_status'],
            'dialog_desc'=>$loan_dialog_res['dialog_desc'],
            'ious_last_day'=>$ious_last_day,//白条最后还款日
            'ious_url'=>$ious_url,//白条跳转地址
            'loan_time_desc'=>$loan_time_desc,
            'ious_time_desc'=>$ious_time_desc,
            'business_type'=>$business_type,//账单标题
        ];

        exit($this->returnBack('0000', $array));

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
                        'dialog_desc'=>'恭喜你，借款账单还款成功！当前已还'.sprintf("%.2f",$repay_amount).'元，剩余应还'.$loan_hgamount.'元！',
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

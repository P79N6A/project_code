<?php

namespace app\modules\api\controllers\controllers314;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\models\news\Juxinli;
use app\models\news\PayAccountError;
use app\models\news\User;
use app\models\news\User_credit;
use app\models\news\User_loan;
use app\models\news\User_rate;
use app\models\service\UserloanService;
use app\modules\api\common\ApiController;
use Yii;
use yii\helpers\ArrayHelper;

class UserloanlimitController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $source = Yii::$app->request->post('source');//5s是安卓
        if (empty($version) || empty($source)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $loan_amounts=Keywords::getMaxCreditAmounts();
       if(empty($user_id)){
           $array = $this->returnBack('0000',['loan_amounts'=>$loan_amounts]);
           echo $array;
           exit;
       }

        $userModel = new User();
        $userinfo = $userModel->getUserinfoByUserId($user_id);
        if (empty($userinfo)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
        $userLoanService = new UserloanService();
        //判断一亿元产品中是否有进行中的借款
        $reject_data = array('is_reject' => 0, 'guide_url' => '', 'reject_data' => array());
        $loan_id = 0;
        $business_type = 0;
        $haveinLoanId = (new User_loan())->getHaveinLoan($userinfo->user_id,[1, 4, 5, 6]);
        if (!empty($haveinLoanId)) {
            $loan_id = $haveinLoanId;
            $loanInfo = $userLoanService->getLoanByLoanId($loan_id);
            $business_type = $loanInfo->business_type;
        }
//        Logger::dayLog('api/loan/userloan','借款数据api',$loan_id);
        //查询是否有驳回评测
        $reject_credit = (new User_credit())->getCreditReject($userinfo->user_id,$source);
        if(!empty($reject_credit)){
            $reject_data = $reject_credit;
        }

        //查询是否有驳回借款
        $bank_id='';
        $reject_loan = (new User_loan())->rejectLoanInfo($userinfo->user_id);
        if (!empty($reject_loan)) {
            $business_type = $reject_loan->business_type;
            $bank_id=$reject_loan->bank_id;
        }
        //查询是否有5天未提现引导
        $reject_credit = (new User_credit())->getCreditFiveOverReject($userinfo->user_id);
        if(!empty($reject_credit)){
            $reject_data = $reject_credit;
        }
        //检测亿元评测
        $user_credit=(new User_credit())->checkYyyUserCredit($user_id);

        $time_diff=strtotime($user_credit['invalid_time'])-time();
        if ($time_diff>0){
            $syhour=ceil($time_diff/3600);
        }else{
            $syhour=0;
        }
        //账单数量
        $bill_num=0;
        //智融钥匙待支付的账单
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $userinfo->mobile]);
        if(!empty($iousResult)){
                $bill_num=$bill_num+1;
            $usercredit_status=9;//白条未还清
        }else{
            $usercredit_status=$this->AppUserCreditStatus($user_credit,$userinfo);//检测评测状态
        }
        if($user_credit['order_amount']>0){
            $loan_amounts=ceil($user_credit['order_amount']);
        }
        //查询提现弹窗
        $dialogalert_data=$this->DialogAlert($userinfo,$iousResult,$reject_loan);
        //聚信立时间
        $junxinli=Juxinli::find()->where(['user_id' => $user_id, 'type' => 1])->orderBy('create_time desc')->one();
        if(!empty($junxinli)){
            $auth_time=$junxinli->last_modify_time;
        }else{
            $auth_time=$userinfo->create_time;
        }
        
        $array = $this->reback($bank_id,$dialogalert_data, $loan_amounts,$loan_id,$user_credit,$usercredit_status,$syhour,$business_type,$bill_num,$reject_data,$auth_time,$user_id,$source,$userinfo);
        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;
    }

    private function reback($bank_id, $dialogalert_data, $loan_amounts, $loan_id,$user_credit,$usercredit_status,$syhour,$business_type,$bill_num,$reject_data,$auth_time,$user_id,$source,$userinfo) {

        $btn_status = 0;
        $ios_down_url = '';
        $android_down_url = '';
        $redict_activation_num = 0;
        $loan_day='';
        $evaluation_activation_info['yxl_authentication_url'] = '';
        $evaluation_activation_info['channel'] = 0;
        $evaluation_activation_info['youxin_down_url'] = '';
        $credit_is_show=Keywords::getIsCreditShow();
        $direct_activation_url = ''; //直接激活h5地址
        $oUserCredit=(new User_credit())->getUserCreditByUserId($user_id);
        //审批通过且额度已激活
        if($usercredit_status==2){
            $loan_day=$oUserCredit->days;
        }
        //3审批通过且额度待激活
        if($usercredit_status==3){
            $apiHttp = new Apihttp();
            $payResult = $apiHttp->getYxlpayBycredit(['req_id' => $oUserCredit->req_id, 'source' => 1]);
            $btn_status = (new UserloanService()) -> getBtnStatusByCredit($payResult);
            //测评激活(user_id分桶：0 1 2 3 4下载智融app ,5 6 7 8 9直接跳转智融H5)
            $evaluation_activation_info = (new User())->getEvaluationChannel($user_id, $userinfo->mobile);
            if ($btn_status) {
                $num = Yii::$app->redis->get($oUserCredit->req_id);
                $redict_activation_num = empty($num) ? 0 : $num;
                $direct_activation_url = Yii::$app->request->hostInfo . '/borrow/creditactivation/activating?req_id=' . $oUserCredit->req_id;
            }
            //请求智融钥匙接口获取安卓apk下载地址、ios App Store地址
            $downResult = $apiHttp->getYxldownurl([]);
            $ios_down_url = $downResult['ios_url'];
            $android_down_url = $downResult['android_url'];
            $loan_day=$oUserCredit->days;
        }
        
        if(in_array($usercredit_status, [1,4,5,6,10])){
            $loan_amounts = Keywords::getMaxCreditAmounts();
        }
        $array['loan_id'] = empty($loan_id) ? 0 : $loan_id;
        $array['business_type'] = empty($business_type) ? 0 : $business_type;
        $array['auth_time'] = $auth_time;
        $array['bill_num'] = $bill_num;
        $array['is_reject'] = $reject_data['is_reject']; //是否显示驳回
        $array['usercredit_status'] = $usercredit_status;//评测状态
        $array['reject_data'] = $reject_data['reject_data']; //驳回
        $array['guide_url'] = $reject_data['guide_url']; //导流url
        $array['goods_url'] = 'http://sc.9fbank.com/m/views/index.html?channel=6020'; //商城地址
        $array['goods_open'] = 2; //列表开关1开启2关闭.
        $array['loan_amounts'] = $loan_amounts;
        $array['loan_day'] = $loan_day;
        $array['loan_period'] = (!empty($oUserCredit) && !empty($oUserCredit->period))?$oUserCredit->period:1;
        $array['invalid_time'] = $user_credit['invalid_time'];
        $array['syhour'] =empty($syhour) ? 0 : $syhour;
        $array['loan_reason_list'] = Keywords::getDesc();
        $url = Yii::$app->params['app_url'] . '/new/';
        $array['insurance_url'] = $url.'buy?user_id='.$user_id.'&source='.$source; //保险地址
        $array['insurance_url'] = '';
        $array['activation_btn_status'] = $btn_status;
        $array['youxin_down_url'] = $evaluation_activation_info['youxin_down_url'];
        $array['evaluation_activation_channel'] = $evaluation_activation_info['channel'];
        $array['yxl_authentication_url'] = $evaluation_activation_info['yxl_authentication_url'];
        $array['redict_activation_num'] = $redict_activation_num;
        $array[ 'direct_activation_url'] = $direct_activation_url;
        $array['ios_down_url']=$ios_down_url;
        $array['android_down_url']=$android_down_url;
        $array['credit_is_show'] = $credit_is_show;
        $array['bank_id'] =$bank_id;
        $array[ 'dialog_status'] = $dialogalert_data['dialog_status'];
        $array['dialog_desc']=$dialogalert_data['dialog_desc'];
        $array['dialog_desc_color']=$dialogalert_data['dialog_desc_color'];
        $array['dialog_url'] = $dialogalert_data['dialog_url'];
        return $array;
    }

    private function AppUserCreditStatus($user_credit,$userinfo){
        $oCredit = (new User_credit())->getUserCreditByUserId($userinfo->user_id);
        $rejectCredit = (new User_credit())->getCreditRejectReturn($oCredit); //true：驳回 false:不是驳回
        $SelectioStatus=(new User_loan())->getSelectionStatusNew($userinfo->user_id);
        if($user_credit['user_credit_status']==1){
                return $usercredit_status=0;//0初始未评测
        }elseif($user_credit['user_credit_status']==2){
            $creditLastTime=$user_credit['invalid_time'];//评测时间
            if($userinfo->status==5){
                return $usercredit_status=4;//审批未通过且是黑名单(无按钮)
            }
            
            $UserCreditByTimeRes=(new User_loan())->getUserCreditByTime($userinfo->user_id,$creditLastTime);
            
            if($SelectioStatus && $UserCreditByTimeRes){ 
                return $usercredit_status=10; //审批未通过，且已完善过资料后仍有待完善资料 （重新获取 完善资料）
            }else if($SelectioStatus && !$UserCreditByTimeRes){
                return $usercredit_status=6; //审批未通过，且未完善过资料后仍有待完善资料 （ 完善资料）
            }else if(!$SelectioStatus && $UserCreditByTimeRes){
                return $usercredit_status=5; //审批未通过，且已完善过资料后无待完善资料 （ 重新获取）
            }else{
                return $usercredit_status=4;//审批未通过且（无可完善资料且在24有效期内）(无按钮)
            }
            
        }else if($user_credit['user_credit_status']==3){
            $SelectioStatus=(new User_loan())->getSelectionStatusNew($userinfo->user_id);
            if($SelectioStatus){
                return $usercredit_status=7;//额度获取中(加快审核按钮)
            }else{
                return $usercredit_status=8;//额度获取中(无加快审核按钮)
            }

        }else if($user_credit['user_credit_status']==4){
            return $usercredit_status=3;//审批通过且额度待激活
        }else if($user_credit['user_credit_status']==5){
            return $usercredit_status=2;//审批通过且额度已激活
        }else if( $rejectCredit && $oCredit['invalid_time']<date('Y-m-d H:i:s',time()) ){ //驳回且已失效
            $usercredit_status = 5;  //5.审批未通过，已失效，无可完善资料,不是黑名单用户：重新获取额度
            if( $SelectioStatus){
                $usercredit_status = 10; // 6.审批未通过，已失效，有可完善资料,不是黑名单用户：重新获取额度 完善资料
            }
            return $usercredit_status;
        }else if($user_credit['user_credit_status']==6){
            return $usercredit_status=1;//额度已失效
        }
    }

    private function DialogAlert($userinfo,$iousResult,$reject_loan){
        //弹窗
        $dialog_status=0;
        $dialog_desc='';
        $dialog_url='';
        $dialog_desc_color='';
        $data=[
            'dialog_status'=>$dialog_status,
            'dialog_desc'=>$dialog_desc,
            'dialog_url'=>$dialog_url,
            'dialog_desc_color'=>$dialog_desc_color,
        ];
        $oPayAccount= PayAccountError::find()->where(['user_id'=>$userinfo->user_id,'type'=>6,'status'=>0])->one();
        if(!empty($oPayAccount)){
            if($oPayAccount->res_code=='00000000'){
                if (!empty($iousResult)){
                    $dialog_status=2;//提现成功（立即支付）
                    $dialog_desc='恭喜您，提现成功!'."<br/>".'立即支付测评账单，可获得专项优惠！';
                    $url=urlencode('/dev/iousdetails/index?ious_id='.$iousResult['ious_id']);
                    $mobile=$userinfo->mobile;
                    $dialog_url=Yii::$app->params['youxin_url'].'dev/iousdetails/index?ious_id='.$iousResult['ious_id'].'&userToken='.$mobile.'&url='.$url;
                    $oPayAccount->updateStatusSuccess();
                }else{
                    $dialog_status=1;// 提现成功（查看账单）
                    $dialog_desc='恭喜您，提现成功!';
                    $oPayAccount->updateStatusSuccess();
                }
            }elseif(!empty($reject_loan) && ($oPayAccount->res_code=='CI68' || $oPayAccount->res_code=='CI73')){
                $dialog_status=3;//提现失败（立即更换)
                $dialog_desc='很抱歉，由于您的存管卡暂不支持提现，导致借款驳回，请更换存管卡后重新发起借款';
                $oPayAccount->updateStatusSuccess();
            }elseif($oPayAccount->res_code=='txcgfail001'){
                $dialog_status=4;//提现失败（联系客服)
                $dialog_desc='由于当前银行卡无法解绑，暂无法更换卡片，请联系客服解决';
                $dialog_url = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId='.$userinfo->user_id; //客服链接
                $oPayAccount->updateStatusSuccess();
            }else{
                $dialog_status=5;//提现失败（再次提现）
                $dialog_desc='失败原因：网络加载延迟，请重新发起提现';
                $dialog_desc_color='网络加载延迟';
                $oPayAccount->updateStatusSuccess();
            }
            $data=[
                'dialog_status'=>$dialog_status,
                'dialog_desc'=>$dialog_desc,
                'dialog_url'=>$dialog_url,
                'dialog_desc_color'=>$dialog_desc_color,
            ];
            return $data;
        }
        return $data;
    }

}

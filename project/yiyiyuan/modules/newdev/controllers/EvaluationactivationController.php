<?php
namespace app\modules\newdev\controllers;

use app\commonapi\Logger;
use app\models\news\User_extend;
use app\models\news\User_loan;
use app\models\news\User;
use app\commonapi\Common;
use app\models\service\UserloanService;
use app\commonapi\Apihttp;
use Yii;



class EvaluationactivationController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }
    
    /**
     * 
     */
    public function actionIndex(){

        $this->getView()->title = '激活额度';
        $mobile = empty($this->get('mobile')) ? '' : $this->get('mobile');
        $userInfo = (new User())->getUserinfoByMobile($mobile);
        $encodeUserId = empty($userInfo) ? '' : $userInfo->user_id;

        return $this->render('evaluadown',[
            'mobile' => $mobile,
            'encodeUserId' => $encodeUserId,
        ]);
    }
    
    /**
     * 测评按钮点击时的ajax请求
     */
    public function actionClickstatus(){
        $loan_id = $this->get('loan_id');
        $err_code = '0001';
        $back_msg = '';
        $click_status = 0;
         if (empty($loan_id))  {
             //exit('借款不存在');
            return  json_encode(['back_code'=>$err_code,'back_msg'=>'借款不存在','click_status'=>$click_status]);
         }
         //查询借款人的信息
        $loaninfo = User_loan::find()->joinWith('user', true, 'LEFT JOIN')->where([User_loan::tableName() . '.loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            //exit('借款不存在');
           return  json_encode(['back_code'=>$err_code,'back_msg'=>'借款不存在','click_status'=>$click_status]);
        }
        if (empty($loaninfo->user)) {
            //exit('用户不存在');
            return  json_encode(['back_code'=>$err_code,'back_msg'=>'用户不存在','click_status'=>$click_status]);
        }
        $click_status = $this->getZryspayresult($loan_id);
        $err_code = '0000';
        return  json_encode(['back_code'=>$err_code,'back_msg'=>$back_msg,'click_status'=>$click_status]);
    }
    
    /**
     * 请求智融钥匙接口获取测评支付记录
     */
    private function getZryspayresult($loan_id){
         $refresh_status = 0;
         $apiHttp = new Apihttp();
         $payResult = $apiHttp->getYxlpayresult(['loan_id' => $loan_id,'source'=>1]);
         $btn_status = (new UserloanService()) -> getBtnStatus($payResult);
         return $btn_status;
    }
    
}
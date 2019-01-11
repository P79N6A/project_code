<?php
namespace app\modules\newdev\controllers;


use app\models\service\UserloanService;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\User_loan;
use app\models\news\User;
use app\commonapi\Apihttp;
use Yii;


class DirectactivationController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }
    
    /**
     * 直接激活-激活中
     * @return type
     */
    public function actionActivating(){
        $this->getView()->title = '激活额度';
        $loan_id = $this->get('loan_id');
        if (empty($loan_id))  {
            exit('借款不存在');
         }
         //查询借款人的信息
        $loaninfo = User_loan::find()->joinWith('user', true, 'LEFT JOIN')->where([User_loan::tableName() . '.loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            exit('借款不存在');
        }
        if (empty($loaninfo->user)) {
            exit('用户不存在');
        }
      // Yii::$app->redis->del($loan_id);die;

        $encodeUserId = empty($loaninfo->user->user_id) ? '' : $loaninfo->user->user_id;
        $redict_activation_num = empty(Yii::$app->redis->get($loan_id)) ? 0 : Yii::$app->redis->get($loan_id);
        $redict_activation_num = $redict_activation_num + 1;
        Yii::$app->redis->setex($loan_id,86400,$redict_activation_num);
        $refresh_status = 0;
        if($redict_activation_num >= 3){
            //智融接口返回测评支付结果
          $refresh_status = $this->getZryspayresult($loan_id);
            if( $refresh_status == 0 ){ //智融认证未成功支付时才会驳回
                //借款驳回
                $oService = new UserloanService();
                $reject_result = $oService->tbReject($loan_id);
                Logger::dayLog('weixin/directactivation/rejectloan', '直接激活三次，借款驳回',$loaninfo->user->user_id, $redict_activation_num, $reject_result); //@todo 监测使用，后期请删除
            }

            
        }

        return $this->render('activating',[
            'activation_num' => $redict_activation_num,
            'loan_id' => $loan_id,
            'encodeUserId' => $encodeUserId,
            'refresh_status' => $refresh_status,
        ]);
    }
    
    private function getZryspayresult($loan_id){
         $refresh_status = 0;
         $apiHttp = new Apihttp();
         $payResult = $apiHttp->getYxlpayresult(['loan_id' => $loan_id,'source'=>1]);
         $btn_status = (new UserloanService()) -> getBtnStatus($payResult);
         if($btn_status == 1 &&  $payResult['res_code'] == '0000' && !empty($payResult['res_data']) && $payResult['res_data']['status'] == 1){
             $refresh_status = 1;
         }
         return $refresh_status;
    }
    
    
        /**
     * 直接激活-激活结果（失败）
     * @return type
     */
    public function actionActivationresult(){
         $this->getView()->title = '激活额度'; 
         $activation_num = $this->get('activation_num');
         $loan_id = $this->get('loan_id');
         if (empty($loan_id))  {
            exit('借款不存在');
         }
         //查询借款人的信息
        $loaninfo = User_loan::find()->joinWith('user', true, 'LEFT JOIN')->where([User_loan::tableName() . '.loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            exit('借款不存在');
        }
        if (empty($loaninfo->user)) {
            exit('用户不存在');
        }
        $mobile = empty($loaninfo->user->mobile) ? '' : $loaninfo->user->mobile;
        $encodeUserId = empty($loaninfo->user->user_id) ? '' : $loaninfo->user->user_id;
         $btn_status = 0;
         $try_activation_url = '';
         $evaluation_url = '';
         
        $info = (new User())-> getEvaluationChannel($loaninfo->user->user_id,$loaninfo->user->mobile);
        if($info['channel'] == 1){
            $evaluation_activation_url = $info['youxin_down_url'];
        }else{
             $evaluation_activation_url = $info['yxl_authentication_url'];
        }
         if($activation_num < 3){
             $btn_status = 1;
             $try_activation_url = '/new/loan/showloan?loan_id='.$loan_id;
             $evaluation_url = $evaluation_activation_url;
         }
         
         return $this->render('activationresult',[
            'btn_status' => $btn_status,
            'try_activation_url' => $try_activation_url,
            'evaluation_activation_url' => $evaluation_url,
            'encodeUserId' => $encodeUserId,
         ]);
    }
    
}
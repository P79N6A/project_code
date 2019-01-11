<?php
namespace app\modules\borrow\controllers;

use app\models\service\UserloanService;
use app\commonapi\Logger;
use app\models\news\User_extend;
use app\models\news\User_loan;
use app\models\news\User_credit;
use app\models\news\User;
use app\commonapi\Common;
use app\commonapi\Apihttp;
use Yii;


class CreditactivationController extends BorrowController
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
        $this->layout = 'activing';
        $this->getView()->title = '借款审核';
        $req_id = $this->get('req_id');
        if (empty($req_id))  {
            exit('额度不存在'); //评测不存在
        }
        //查询评测的信息
        $creditinfo =  User_credit::find()->where(['req_id'=>$req_id])->one();
        if (empty($creditinfo)) {
            exit('额度不存在');
        }
        if (empty($creditinfo->user_id)) {
            exit('用户不存在');
        }
//       Yii::$app->redis->del($req_id);
        $encodeUserId = empty($creditinfo->user_id) ? '' : $creditinfo->user_id;
        $redict_activation_num = empty(Yii::$app->redis->get($req_id)) ? 0 : Yii::$app->redis->get($req_id);
        $redict_activation_num = $redict_activation_num + 1;
        Yii::$app->redis->setex($req_id,86400,$redict_activation_num);
        $refresh_status = 0;
        if($redict_activation_num >= 3){
            $refresh_status = $this->getZrysData($req_id);//智融接口返回测评支付结果
            if( $refresh_status == 0 ){ //智融认证未成功支付时才会驳回
                //评测驳回
                $time = date('Y-m-d H:i:s');  
                $condition = [
                    'status' => 2,
                    'res_status' => 2,
                    'invalid_time' => $time,
                ];
                $transaction = Yii::$app->db->beginTransaction();
                $reject_result = $creditinfo->updateUserCredit($condition);
                if (empty($reject_result)) {
                    $transaction->rollBack();
                    Logger::dayLog('weixin/creditactivation/activating', '直接激活三次，测评驳回',$creditinfo->user_id, $redict_activation_num, $reject_result);
                }
                $transaction->commit();

                //通知智融钥匙，把order失效掉
                $credit_soure = $creditinfo->source ==3 ? 1 : $creditinfo->source;
                $this->getZrysres($req_id,$credit_soure);
          }   
        }
        return $this->render('activating',[
            'activation_num' => $redict_activation_num,
            'req_id' => $req_id,
            'encodeUserId' => $encodeUserId,
            'refresh_status' => $refresh_status,
        ]);
    }
    private function getZrysres($req_id,$source){
         $contacts = [
                    'req_id' => $req_id,
                    'source' => $source,
                    'status' => 2,
                    ];
        $api = new Apihttp();
        $result = $api->postSignal($contacts,4);
        if(!empty($result['rsp_code']) && $result['rsp_code'] == '0000'){
            return true;
        }
        Logger::dayLog('signal/signalpush', '有信令推送失败', 'req_id：' . $req_id, $contacts, $result);
        return false;
        
    }

    private function getZrysData($req_id){
         $apiHttp = new Apihttp();
         $refresh_status = 0;
         $payResult = $apiHttp->getYxlpayBycredit(['req_id' => $req_id,'source'=>1]);
         $btn_status = (new UserloanService()) -> getBtnStatusByCredit($payResult);
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
         $this->layout = 'activition';
         $this->getView()->title = '借款审核'; 
         $activation_num = $this->get('activation_num');
         $req_id = $this->get('req_id');
         if (empty($req_id)){
            exit('额度不存在');
         }
         //查询评测的信息
         $creditinfo = User_credit::find()->where(['req_id'=>$req_id])->one();
         if (empty($creditinfo)) {
            exit('额度不存在');
         }
         if (empty($creditinfo->user_id)) {
            exit('用户不存在');
         }
        $oUser = User::findOne($creditinfo->user_id);
        $mobile = empty($oUser->mobile) ? '' : $oUser->mobile;
        $btn_status = 0;
        $try_activation_url = '';
        $evaluation_url = '';
        $info = (new User())-> getEvaluationChannel($creditinfo->user_id,$mobile);
        if($info['channel'] == 1){
            $evaluation_activation_url = $info['youxin_down_url'];
        }else{
             $evaluation_activation_url = $info['yxl_authentication_url'];
        }
        if($activation_num < 3){
            $btn_status = 1;
            $try_activation_url = '/borrow/loan?req_id='.$req_id;
            $evaluation_url = $evaluation_activation_url;
        }
        $apiHttp = new Apihttp();
        $downResult = $apiHttp->getYxldownurl([]);
        $android_down_url = $downResult['android_url'];
        return $this->render('activationresult',[
            'mobile' => $mobile,
            'btn_status' => $btn_status,
            'try_activation_url' => $try_activation_url,
            'evaluation_activation_url' => $evaluation_url,
            'android_down_url' => $android_down_url,
            'encodeUserId' => $creditinfo->user_id,
        ]);
    }
    
    
    /**
     * 测评激活-智融app下载页
     */
    public function actionEvaluadown(){
        $this->layout = 'activition';
        $this->getView()->title = '借款审核';
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
        $req_id = $this->get('req_id');
        $back_msg = '';
        $click_status = 0;
        if (empty($req_id))  {
            return  json_encode(['back_code'=>'0001','back_msg'=>'测评不存在','click_status'=>$click_status]);
         }
         $creditinfo = User_credit::find()->where(['req_id'=>$req_id])->one();
         if (empty($creditinfo)) {
             return  json_encode(['back_code'=>'0001','back_msg'=>'测评不存在','click_status'=>$click_status]);
         }
         if (empty($creditinfo->user_id)) {
            return  json_encode(['back_code'=>'0001','back_msg'=>'用户不存在','click_status'=>$click_status]);
         }
        $apiHttp = new Apihttp();
        $payResult = $apiHttp->getYxlpayBycredit(['req_id' => $req_id,'source'=>1]);
        $click_status = (new UserloanService()) -> getBtnStatusByCredit($payResult);
        $err_code = '0000';
        return  json_encode(['back_code'=>$err_code,'back_msg'=>$back_msg,'click_status'=>$click_status]);
    }
    
 
    
}
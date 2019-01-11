<?php

namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Common;
use app\models\news\Juxinli;
use app\models\news\User;
use app\models\news\User_credit;
use app\models\news\User_extend;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\UserCreditList;
use app\modules\api\common\ApiController;
use Yii;

class EvaluationController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $uuid = Yii::$app->request->post('_uuid');
        $user_id = Yii::$app->request->post('user_id');
        $deviceType = Yii::$app->request->post('source');
        $deviceTokens = Yii::$app->request->post('device_tokens');
        if (empty($user_id) || empty($deviceType) || empty($uuid)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $user_extend = User_extend::find()->where(['user_id' => $user_id])->one();
        if(empty($user_extend) || empty($user_extend->company)){
//            请前往个人中心完善身份信息
            $array = $this->returnBack('10239');
            echo $array;
            exit;
        }
        $oUser=(new User())->getUserinfoByUserId($user_id);
        $deviceIp = Common::get_client_ip();
        $oUserCredit = (new User_credit())->getUserCreditByUserId($user_id);
        $loan_id='';
        $user_yyy_credit=(new User_credit())->checkYyyUserCredit($user_id);
        //判断是否允许评测@todo 代码REVIEW
        $CreditTime=$oUserCredit['last_modify_time'];
        $user_credit_status=$user_yyy_credit['user_credit_status'];
        $loan_id=empty($oUserCredit->loan_id) ? '': $oUserCredit->loan_id;

        if($user_credit_status==1){
            $repeatNum = (new User_loan())->isRepeatUser($user_id);
            if ($repeatNum == 0) {
                $oUserRejectLoan = (new User_loan())->getLastRejectLoan($user_id);
                if(!empty($oUserRejectLoan)){
                    $CreditTime=$oUserRejectLoan->last_modify_time;//如果他是借款被驳回时间
                }
            }
        }
        if($user_credit_status==3){
            $array = $this->returnBack('10226');
            echo $array;
            exit;
        }
        
        //判断商城订单
        if($user_credit_status == 6  ){
           $shop_res = (new User_credit())->getshopOrder($oUser);
           if(!$shop_res){
                $array = $this->returnBack('10246');
                echo $array;
                exit; 
           }
        }
        
        if(!empty($CreditTime) && $user_credit_status!=6){
            $fillIn = (new User_credit())->chkCreditByMaterial($user_id,$CreditTime);
            $result = (new User_credit())->chkCredit($fillIn,$user_id,$loan_id,$user_credit_status);//@todo 没失效没借款是不是要评测
            if ($result === false) {
                $array = $this->returnBack('10231');
                echo $array;
                exit;
            }
        }

        //判断存在未完成的借款&&借款不是'INIT', 'TB-AUTHED', 'TB-SUCCESS'
        $userLoanId = (new User_loan())->getHaveinLoan($user_id,$business_type = [1,4,5,6,9,10]);
        if (!empty($userLoanId)) {
            $oExtend = (new User_loan_extend())->checkUserLoanExtend($userLoanId);
            if (!empty($oExtend) && !in_array($oExtend->status, ['INIT', 'TB-AUTHED', 'TB-SUCCESS'])) {
                $array = $this->returnBack('10050');
                echo $array;
                exit;
            }
        }
        $jxl_result = (new Juxinli())->isAuthYunyingshang($user_id);
        if (!$jxl_result) {
            $array = $this->returnBack('10228');
            echo $array;
            exit;
        }
        $oJuXinLi = (new Juxinli())->getJuxinliByUserId($user_id);
        $result = $this->postCredit($oJuXinLi->requestid, $oUserCredit, $uuid, $user_id, $deviceTokens, $deviceType, $deviceIp);
        if ($result === false) {
            $array = $this->returnBack('10229');
            echo $array;
            exit;
        }
        $array = $this->returnBack('0000');
        echo $array;
        exit;
    }

    private function postCredit($reqId, $oUserCredit, $uuid, $user_id, $deviceTokens, $deviceType, $deviceIp) {
        $policyApi = new Apihttp();
        $postData = [
            'aid' => 1,
            'req_id' => $reqId,
            'user_id' => $user_id,
            'callbackurl' => Yii::$app->request->hostInfo . '/new/notifycredit'
        ];

        $result = $policyApi->postCredit($postData);
        $result = json_decode($result, true);
        Logger::dayLog('app/evaluation', '请求评测', $postData, $result);
        if ($result['res_code'] === 0 && !empty($result['res_data']['strategy_req_id'])) {

            //评测过
            $creditArray = [
                'loan_id'=>'',
                'req_id' => $result['res_data']['strategy_req_id'],
                'uuid' => $uuid,
                'status' => 1,
                'pay_status' => 0,
                'source'=>1,
                'device_tokens' => $deviceTokens,
                'device_type' => $deviceType,
                'device_ip' => $deviceIp,
            ];
            //从未评测过
            if (empty($oUserCredit)) {
                $creditArray['user_id'] = $user_id;
                $creditResult = (new User_credit())->addUserCredit($creditArray);
            } else {
                $creditResult = $oUserCredit->updateInit($creditArray);
            }
            //记录同步至历史记录表
            $list_result = (new UserCreditList())->synchro($result['res_data']['strategy_req_id']);
            if (empty($creditResult)) {
                Logger::dayLog('app/evaluation', '评测表记录失败', $result['res_data']['strategy_req_id'], $creditResult);
            }
            return $creditResult;
        }
        return false;
    }

}

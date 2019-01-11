<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\common\ErrorCode;
use app\common\PLogger;
use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Juxinli;
use app\models\news\User;

use app\models\news\User_credit;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\UserCreditList;
use Yii;

class GetusercreditController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        $openApi = new ApiClientCrypt;
        $data = $this->post('data');
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('getusercredit', $parr);
        $deviceIp = $parr['res_data']['device_ip'];
        $deviceType =$parr['res_data']['device_type'];
        $deviceTokens = $parr['res_data']['device_tokens'];
        $uuid =$parr['res_data']['uuid'];
        $mobile = $parr['res_data']['mobile'];
        $oUser=(new User)->getUserinfoByMobile($mobile);
        if($oUser->status==5){
            $return_infos = $this->returnback('0002','黑名单用户');
            echo $return_infos;
            exit;
        }
        $oJuXinLi = (new Juxinli())->getJuxinliByUserId($oUser->user_id);
        $haveinLoanId = (new User_loan())->getHaveinLoan($oUser->user_id,$business_type = [1,4,5,6,9,10]);
        $oUserCredit = (new User_credit())->getUserCreditByUserId($oUser->user_id);
        if (!empty($haveinLoanId)) {
                $return_infos = $this->returnback('0003','有进行中的借款');
                echo $return_infos;
                exit;
        }

        $shop_res = (new User_credit())->getshopOrder($oUser);
        if(!$shop_res){
            $return_infos = $this->returnback('0004','您已有一笔商城订单，暂不可发起');
            echo $return_infos;
            exit;
        }

        //检测亿元评测
        $user_credit=(new User_credit())->checkYyyUserCredit($oUser->user_id);
        if(in_array($user_credit['user_credit_status'], [1,6])){//亿元未评测或者失效
            $result = $this->postCredit($oJuXinLi->requestid, $user_credit['user_credit_status'], $oUserCredit,$uuid, $oUser->user_id, $deviceTokens, $deviceType, $deviceIp);
            if (empty($result)) {
                $return_infos = $this->returnback('0001','评测失败');
                echo $return_infos;
                exit;
            }else{
                $oUserCredit = (new User_credit())->getUserCreditByUserId($oUser->user_id);
                $return_infos['rsp_code'] = '0000';
                $return_infos['rsp_msg'] ='成功';
                $return_infos['req_id']=$oUserCredit['req_id'];
                echo json_encode($return_infos);
                exit;
            }
        }else{

            if(!empty($oUserCredit) && $oUserCredit->status == 2 && $oUserCredit->res_status == 1 && $oUserCredit->pay_status == 0){//4:已测评可借未购买
                $yyyCredit=(new User_credit())->getYyyCredit($oUserCredit);
                if($yyyCredit){
                    $return_infos['rsp_code'] = '0010';
                    $return_infos['rsp_msg'] ='成功';
                    $return_infos['req_id']=$oUserCredit['req_id'];
                    $return_infos['invalid_time'] =$oUserCredit->invalid_time;
                    echo json_encode($return_infos);
                    exit;
                }
            }
            if($user_credit['user_credit_status']==2){
                $return_infos['req_id']=$oUserCredit['req_id'];
                $return_infos['invalid_time'] =$oUserCredit->invalid_time;
            }
            $return_infos['rsp_code'] = '0001';
            $return_infos['rsp_msg'] ='评测失败';
            echo json_encode($return_infos);
            exit;
        }

    }
    /*
     * 智融钥匙评测
     * */
    private function postCredit($reqId, $UserCreditStatus, $oUserCredit, $uuid, $user_id, $deviceTokens, $deviceType, $deviceIp) {
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
                'source'=>2,
                'device_tokens' => $deviceTokens,
                'device_type' => $deviceType,
                'device_ip' => $deviceIp,
            ];
            //从未评测过
            if ($UserCreditStatus==1) {
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

    protected function returnback($rsp_code, $error_msg = '', $array = [])
    {
        $codeArray['rsp_code'] = $rsp_code;
        $codeArray['rsp_msg'] = !empty($error_msg) ? $error_msg : (new ErrorCode())->geterrorcode($rsp_code);
        if (!empty($array)) {
            $codeArray = array_merge($codeArray, $array);
        }
        return json_encode($codeArray, JSON_UNESCAPED_UNICODE);
    }
}

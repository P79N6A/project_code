<?php

namespace app\modules\newdev\controllers;

use app\models\news\Payaccount;
use app\commonapi\Logger;
use app\common\ApiClientCrypt;
use app\models\news\PayAccountError;

class GetnewsetpassnotifyController extends NewdevController{
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /*
     * 接收存管设置密码结果
     */
    public function actionIndex() {
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'newPasswordNotify', 'debt');
        if(!is_array($postData) || !isset($postData['data'])){
            exit('error1');
        }
        $result = json_decode($postData['data'], true);
        Logger::errorLog(print_r($result, true), 'newPasswordNotify', 'debt');
        if(!$result || $result['res_code'] != 0){
            exit('error2');
        }
        $res_arr = json_decode($result['res_data'], true);
        // echo "<pre>";
        // print_r($res_arr);die;
        if(!is_array($res_arr) || !isset($res_arr['retCode']) || !isset($res_arr['accountId'])){
            exit('error3');
        }

        $accountId = $res_arr['accountId'];
        $res = $res_arr['retCode'];

        //设置密码成功，把用户设置密码结果改为成功
        $isPassword = Payaccount::find()->where(['accountId' => $accountId, 'type' => 2, 'step' => 2])->orderBy('activate_time desc')->one();
        $payAccountErrorInfo = array(
                'user_id' => isset($isPassword->user_id) ? $isPassword->user_id : "",
                'type' => 2,
                'res_code' => $res,
                'res_json' => addslashes($result['res_data']),
                'res_msg' => $res_arr['retMsg'],
                'status' => 0,
            );
        $payaccountinfo = (New PayAccountError())->save_error($payAccountErrorInfo);
        if(!$payaccountinfo){Logger::errorLog(print_r($payaccountinfo, true), 'Authfoureinonenotify', 'debt');}
        if(!$isPassword){
            exit('Payaccount error');
        }
        if($isPassword->activate_result == 1 ){
            return "SUCCESS";
        }
        //响应超时应答码
        $timeOutCodes = [
            'CT9903',
            'CT990300',
            'CE999999',
        ];
        if($res == '00000000'){
            $pass_res = $isPassword->update_list(['activate_result'=>1]);
        }elseif(!in_array($res, $timeOutCodes)){
            $pass_res = $isPassword->update_list(['activate_result'=>2]);
        }

        if(!$pass_res){
            exit('error');
        }
        return "SUCCESS";
    }

    public function actionResetpwd(){
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'PasswordResetNotify', 'debt');
        return "SUCCESS";
    }

}

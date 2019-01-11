<?php

namespace app\modules\newdev\controllers;

use app\models\news\Payaccount;
use app\commonapi\Logger;
use app\common\ApiClientCrypt;
use app\models\news\User_bank;

class GetauthorizenotifyController extends NewdevController{
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /*
     * 接收存管授权结果
     */
    public function actionIndex() {
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'AuthorizeNotify', 'debt');
        if(!is_array($postData) || !isset($postData['data'])){
            exit('error1');
        }
        $result = json_decode($postData['data'], true);
        Logger::errorLog(print_r($result, true), 'AuthorizeNotify', 'debt');
        if(!$result || $result['res_code'] != 0){
            exit('error2');
        }
        $res_data = $result['res_data'];
        $res_arr = json_decode($res_data, true);
        if(!is_array($res_arr) || !isset($res_arr['retCode']) || !isset($res_arr['accountId'])){
            exit('error3');
        }

        $accountId = $res_arr['accountId'];
        $res = $res_arr['retCode'];
        $type = $res_arr['type'];
        $step = 5;
        if($type == 2){//1缴费授权；2还款授权 =》 4还款授权  5缴费授权
            $step = 4;
        }
        $isOpen = Payaccount::find()->where(['accountId' => $accountId, 'type' => 2, 'step' => $step])->orderBy('activate_time desc')->one();
        if(!$isOpen){
            exit('Payaccount error');
        }
        if($isOpen->activate_result == 1 ){
            return "SUCCESS";
        }
        //响应超时应答码
        $timeOutCodes = [
            'CT9903',
            'CT990300',
            'CE999999',
        ];
        if($res == '00000000'){
            $condition['activate_result'] = 1;
            $condition['activate_time'] = date('Y-m-d H:i:s');
            $condition['accountId'] = $accountId;
            $open_res = $isOpen->update_list($condition);
            if(!$open_res){
                $pass_res = false;
            }else{
                $pass_res = true;
            }
        }elseif(!in_array($res, $timeOutCodes)){
            $pass_res = $isOpen->update_list(['activate_result'=>2]);
        }

        if(!$pass_res){
            exit('error');
        }
        return "SUCCESS";
    }

}

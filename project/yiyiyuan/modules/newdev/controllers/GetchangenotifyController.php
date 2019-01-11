<?php

namespace app\modules\newdev\controllers;

use app\models\news\User_remit_list;
use app\commonapi\Logger;

class GetchangenotifyController extends NewdevController{
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /*
     * 接收切换通道成功通知，user_remit_list.remit_status CHANGELOCK->INIT
     */
    public function actionIndex() {
        $postData = $this->post('data');
        Logger::errorLog(print_r($postData, true), 'ChangeNotifyError', 'debt');
        if(!$postData){
            Logger::errorLog(print_r([0=>"参数为空1"], true), 'ChangeNotifyError', 'debt');
            exit('error empty1');
        }
        $result = json_decode($postData, true);
        if(!$result || empty($result)){
            Logger::errorLog(print_r([0=>"参数为空2"], true), 'ChangeNotifyError', 'debt');
            exit('error empty1');
        }

//        $result = [
//            1 => [
//                'loan_id' => '18554773',
//                'res_code' => 0,
//            ],
//        ];

        foreach ($result as $k => $arr){
            if(empty($arr) || !isset($arr['loan_id']) || !isset($arr['res_code'])){
                Logger::errorLog(print_r([0=>"参数为空3"], true), 'ChangeNotifyError', 'debt');
                continue;
            }
            $res = $this->todo($arr['loan_id'], $arr['res_code']);
            Logger::errorLog(print_r([$arr['loan_id']=>$res], true), 'ChangeNotifyError', 'debt');
        }
        echo "SUCCESS";
    }

    private function todo($loan_id, $status){
        if($status == 0){
            $userRemit = User_remit_list::find()->where(["loan_id"=>$loan_id])->one();
            if(!$userRemit){
                Logger::errorLog(print_r([$loan_id=>"出款记录不存在"], true), 'ChangeNotifyError', 'debt');
                return false;
            }
            $remig_res = $userRemit->update_remit(["remit_status"=>"INIT"]);
            if(!$remig_res){
                Logger::errorLog(print_r([$loan_id=>"出款状态更新失败"], true), 'ChangeNotifyError', 'debt');
                return false;
            }
            return true;
        }elseif ($status == 1){
            Logger::errorLog(print_r([$loan_id=>"切换通道失败"], true), 'ChangeNotifyError', 'debt');
            return true;
        }
    }

}

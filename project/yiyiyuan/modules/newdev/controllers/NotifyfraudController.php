<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Logger;
use app\models\dev\AntiFraud;
use Yii;

class NotifyfraudController extends NewdevController{
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    public function actionIndex(){
        $data = $this->post();
        Logger::errorLog(print_r($data, true), 'NotifyFraud', 'fraud');
        $res = json_decode($data['data'],true);
        if(!is_array($res) || !$res['loan_id'] || !$res['res_status'] || !$res['req_id'] || !isset($res['result_subject'])){
            echo "ERROR";
            exit;
        }
        if($res['res_status'] == 'approval'){ //安全
            $resultStatus = 2;
        }elseif ($res['res_status'] == 'manual'){//人工
            $resultStatus = 3;
        }elseif ($res['res_status'] == 'reject'){//欺诈
            $resultStatus = 1;
        }else{
            exit;
        }
        $fraud = (new AntiFraud())->getFraudById($res['req_id']);
        if($fraud->model_status != 7){
            Logger::errorLog(print_r($fraud->loan_id.'状态错误', true), 'NotifyFraud', 'fraud');
            echo 'ERROR_STATUS';
            exit;
        }
        $fraudInfo = $fraud->updateFraudResult($resultStatus, $res['result_subject']);
        if(!$fraudInfo){
            Logger::errorLog(print_r($fraud->loan_id.'更新状态错误', true), 'NotifyFraud', 'fraud');
            echo "UPDATE_STATUS_ERROR";
            exit;
        }
        echo "SUCCESS";
    }

}

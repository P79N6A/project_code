<?php

namespace app\commands;

use app\commonapi\Logger;
use app\models\news\Bankbill;
use app\models\news\Bankreport;
use app\models\news\User_loan;
use yii\console\Controller;
use Yii;

class BankreportController extends Controller {
    /**
     * 
     */
    public function actionIndex(){

        $Bankreport = new Bankreport();

        //分页查询
    	$limit = 500;
    	$time = date("2017-08-24 00:00:00");
    	$where = [
    		'AND',
           	[ "<", Bankbill::tableName().".create_time",$time],
            [
                'OR',
                ["=",User_loan::tableName().".status",8],
                ["=",User_loan::tableName().".status",9],
                ["=",User_loan::tableName().".status",11],
                ["=",User_loan::tableName().".status",12],
                ["=",User_loan::tableName().".status",13],
            ]
    	];
        $bank_info = Bankbill::find()->joinWith('loan', true, 'LEFT JOIN')->where($where);
        $total = $bank_info->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $repay_info = $bank_info->offset($i * $limit)->limit($limit)->orderby(' create_time desc ')->all();
            if (!empty($repay_info)){
                foreach($repay_info as $key => $value){

                    //判断储蓄卡信息是否已添加
                    $report_deposit=$Bankreport->find()->where(['loan_id'=>$value['loan_id'],'bin_type'=>2])->one();
                     if($value && !$report_deposit && $value['deposit_url']){
                        //解析json文件
                        $deposit_url_json = $this->analBankInfo($value['deposit_url']);
                        //添加入库
                        $add_deposit_url_status = $Bankreport->addList($deposit_url_json, $value['loan_id'], 2);
                        //记录添加失败日志
                        if($add_deposit_url_status == false){
                            Logger::errorLog('deposit_url : '.$value['deposit_url'] .' ，loan_id : '.$value['loan_id'].'， create_time : '.$value['create_time'].'，nowtime :'.date('Y-m-d H:i:s') , 'Bankreport_error' , 'Bankreport');
                        }
                    }
 
                    //判断信用卡信息是否已添加
                    $report_credit=$Bankreport->find()->where(['loan_id'=>$value['loan_id'],'bin_type'=>1])->one();
                    if ($value && !$report_credit && $value['credit_url']) {
                        //解析json文件
                        $credit_url_json = $this->analBankInfo($value['credit_url']);
                        //添加入库
                        $add_credit_url_status = $Bankreport->addList($credit_url_json, $value['loan_id'], 1);
                        //记录添加失败日志
                        if ($add_credit_url_status == false) {
                            Logger::errorLog('credit_url : ' . $value['credit_url'] . ' ，loan_id : ' . $value['loan_id'] . '， create_time : ' . $value['create_time'] . '，nowtime :' . date('Y-m-d H:i:s'), 'Bankreport_error', 'Bankreport');
                        }
                    }

                }
            }
        }
    }

    //解析json文件
    public function analBankInfo($data){
        $bank_info_json = @file_get_contents($data);

        if(empty($bank_info_json)){
            Logger::errorLog(print_r(array("json文件为空"), true), 'Bankreport_json', 'Bankreport');
        }
        return json_decode( $bank_info_json , true);
    }

    public function Deposit_url($value){

    }
}
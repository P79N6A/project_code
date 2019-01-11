<?php


namespace app\commands;

use app\models\news\User_remit_list;
use app\models\news\User_loan_extend;
use app\models\news\FundRecord;
use app\commonapi\Logger;
use Yii;
use yii\console\Controller;

/**
 * Class FundController
 * @package app\commands
 * 更改资方状态;
 */
//win下地址 D:\phpStudy\php\php-5.6.27-nts\php.exe  D:\work\yiyiyuan\yii fund；

class FundController extends Controller {



    public function actionIndex($fund){
        $date = date("Y-m-d H:i:s");
        $begin_time = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $end_time = date('Y-m-d 23:59:59', strtotime('-1 day'));
        $fund_recod = FundRecord::find()->andWhere(['between','create_time',$begin_time,$end_time])->andFilterWhere(["fund_status"=>'INIT',"fund"=>$fund])->all();
        if(!empty($fund_recod)){
            foreach($fund_recod as $key=>$val){
                $loan_ids[] = $val['loan_id'];
            }
        }else{
            echo "Please check the data";
        }
            //将资方表状态改为LOCK锁定
        if(!empty($loan_ids)){
            $fund_status = FundRecord::updateAll(["fund_status"=>'LOCK'],['loan_id'=>$loan_ids]);
        }else{
            echo "Please check the data";
        }
        if(!empty($loan_ids)){
            foreach($loan_ids as $val){
                $transaction=\Yii::$app->db->beginTransaction();//开启事物;
                //将一条数据状态变为DOING处理中
                $data = FundRecord::find()->where(['loan_id' => $val])->one();
                $data ->fund_status = 'DOING';
                if(!$data->save()){
                    $transaction->rollBack();
                    continue;
                }
                //更改user_loan_extend表fund为传入的参数
                $data1 = User_loan_extend::find()->where(['loan_id' => $val])->one();
                if(!$data1){
                    $transaction->rollBack();
                    continue;
                }
                $data1 ->fund = $fund;
                $data1 ->last_modify_time = $date;
                if(!$data1->save()){
                    $transaction->rollBack();
                    continue;
                }
                //查出user_remit_list表中最大的一条数据将fund改为传入的参数
                $remit_list = User_remit_list::find()->andFilterWhere(["loan_id"=>$val])->orderBy("create_time desc")->one();
                $data = User_remit_list::find()->where(['loan_id' => $val])->one();
                if(!$data){
                    $transaction->rollBack();
                    continue;
                }
                $data ->fund = $fund;
                $data ->last_modify_time = $date;
                if(!$data->save()){
                    $transaction->rollBack();
                    continue;
                }
                //将本条数据状态改为success//操作结束
                $data = FundRecord::find()->where(['loan_id' => $val])->one();
                $data ->fund_status = 'SUCCESS';
                if(!$data->save()){
                    $transaction->rollBack();
                    continue;
                }

                //将最后修改时间变换;
                $data = FundRecord::find()->where(['loan_id' => $val])->one();
                $data ->last_time = $date;
                if(!$data->save()){
                    $transaction->rollBack();
                    continue;
                }
                $transaction->commit();//事物提交

            }
        }else{
            echo "Please check the data";
        }





    }
    // 保存日志
    private function log($message) {
        echo $message . "\n";
    }
}

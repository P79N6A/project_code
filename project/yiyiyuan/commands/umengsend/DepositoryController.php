<?php

/**
 * 提醒用户提现个推提醒添加
 */

namespace app\commands\umengsend;

use app\models\news\Cg_remit;
use app\models\news\Insure;
use app\models\news\SmsSend;
use app\models\news\UmengSend;
use app\commands\BaseController;
use app\models\news\User_loan;
use app\commonapi\Logger;
use Yii;

set_time_limit(0);
ini_set('memory_limit', '-1');

class DepositoryController extends BaseController {
    
    public function addDepositoryInfo($time,$type){
        $limit = 500;
        $time_start = date("Y-m-d H:i:s", strtotime("-".$time." hours"));
        $time_end = date("Y-m-d H:i:s", strtotime("-".$time." hours 10 minute"));
        $where = [
            'AND',
            ["BETWEEN", Cg_remit::tableName() . ".last_modify_time", $time_start,$time_end],
            [Cg_remit::tableName() . '.remit_status' => 'WILLREMIT'],
            [User_loan::tableName() . '.source' => [2,4]],
        ];
        $cgRemit = Cg_remit::find()->joinWith('userloan', true, 'LEFT JOIN')->where($where);
        $total = $cgRemit->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $cg_info = $cgRemit->offset($i * $limit)->limit($limit)->all();
            if (!empty($cg_info)) {
                $res = $this->addDep($cg_info,$time,$type);
                echo "success:" . $res;
            }
        }
    }

    /**
     * 添加提醒
     * @param $insure_info
     * @param $time
     * @param $time_type
     * @return int
     */
    private function addDep($cg_info,$time,$type) {
        $success = 0;
        if($type == 1){
            $umengModel = new UmengSend();
            foreach ($cg_info as $k => $v) {
                $res = $umengModel->saveUmengSend($v->userloan,4,$time);
                if($res){
                    $success++;
                }else{
                    Logger::dayLog('Umengsend', '添加提醒失败：', $v->loan_id);
                }
            }
        }elseif ($type == 2){
            foreach ($cg_info as $k => $v) {
                $sms_type = 5;
                $content = '您有一笔借款已到达您的账户超过'.$time.'小时了，赶紧提现吧！';

                $mobile = $v->user->mobile;
                $addData['mobile'] = $mobile;
                $addData['content'] = $content;
                $addData['sms_type'] = $sms_type;
                $addData['status'] = 0;
                $addData['channel'] = Yii::$app->params['sms_channel'];
                $addData['send_time'] = date('Y-m-d H:i:s');
                $sms_model = new SmsSend();
                $res = $sms_model->addSmsSend($addData);
                if($res){
                    $success++;
                }else{
                    Logger::dayLog('Umengsend', '添加提醒失败：', $v->loan_id);
                }
            }
        }
        return $success;
    }
}

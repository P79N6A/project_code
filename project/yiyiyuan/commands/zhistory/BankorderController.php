<?php

namespace app\commands;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\User;
use app\models\news\Bankbill;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * 获取银行卡账单
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii setloanstatus > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe E:\www\yiyiyuan\yii setloanstatus
 */

class BankorderController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $start_time = date('Y-m-d H:i:00',  strtotime('- 20 minutes'));
        $end_time = date('Y-m-d H:i:00',  strtotime('- 10 minutes'));
        $where = [
            'AND',
            ["status" => "WAIT"],
            ['>=','last_modify_time',$start_time],
            ['<=','last_modify_time',$end_time],
        ];
        $total = Bankbill::find()->where($where)->count();
        $limit = 1000;
        $pages = ceil($total / $limit);

        $this->log("共{$total}条银行卡初始状态数据:每次处理{$limit},需要要处理{$pages}次\n");

        //把查询出的数据改为中间状态:DOING
        for ($i = 0; $i < $pages; $i++) {
            $Bankbill = Bankbill::find()->where($where)->limit($limit)->all();
            if(empty($Bankbill)){
                Logger::errorLog(print_r(array("initBankbill查询结果为空"), true), 'bankOrder_error', 'bankOrder');
                $this->log("没有获取到Bankbill状态为init的数据");
                continue;
            }

            $loan_ids = ArrayHelper::getColumn($Bankbill, 'loan_id');
            $bill_nums = Bankbill::updateAll(['status'=>'LOCK', 'last_modify_time'=>date('Y-m-d H:i:s')],['loan_id'=>$loan_ids]);
            Logger::dayLog('bankbill', 'send', $start_time . ' to ' . $end_time , 'bankbill进行获取账单状态锁定LOCK', $bill_nums);

            foreach ($Bankbill as $key => $value) {
                $condition_doing = [];
                $condition_doing['status'] = 'DOING';
                $value->updateBankbill($condition_doing);
                //请求接口获取银行卡账单，不管获取成功与否都把状态改为:FINISHED
                $this->upBankbill($value);
            }
        }

    }

    private function upBankbill($value){
            $condition = [];
            $user = User::findOne($value->user_id);
            if(empty($user)){
                Logger::errorLog(print_r(array($value->id . "查询userinfo失败，user_id： ".$value->user_id), true), 'bankOrder_error', 'bankOrder');
                $condition['status'] = "FINISHED";
                $res = $value->updateBankbill($condition);
                return false;
            }
            $apihttp = new Apihttp;
            //获取储蓄卡账单
            if(!empty($value->deposit_card)){
                $data = [
                    [
                        'phone' => $user->mobile,
                        'name' => $user->realname,
                        'idcard' => $user->identity,
                        'card' => $value->deposit_card
                    ]
                ];
                $result = $apihttp->bankOrder($data);
                if($result['res_code'] == '0000'){
                    $msgarr = json_decode($result['res_msg'],true);
                    //银行卡账单
                    if($msgarr && is_array($msgarr['PayConsumptionDer'])){
                        $condition['deposit_url'] = $msgarr['PayConsumptionDer'][0]['url'];
                        $condition['deposit_mofify_time'] = date("Y-m-d H:i:s");
                    }
                    //小额信贷
//                    if($msgarr && is_array($msgarr['AccountChangeDer'])){
//                        $condition['loan_detail_url'] = $msgarr['AccountChangeDer'][0]['url'];
//                    }
                }
            }
            //获取信用卡账单
            if(!empty($value->credit_card)){
                $data = [
                    [
                        'phone' => $user->mobile,
                        'name' => $user->realname,
                        'idcard' => $user->identity,
                        'card' => $value->credit_card
                    ]
                ];
                $result = $apihttp->bankOrder($data);
                if($result['res_code'] == '0000'){
                    $msgarr = json_decode($result['res_msg'],true);
                    //银行卡账单
                    if($msgarr && is_array($msgarr['PayConsumptionDer'])){
                        $condition['credit_url'] = $msgarr['PayConsumptionDer'][0]['url'];
                        $condition['credit_mofify_time'] = date("Y-m-d H:i:s");
                    }
                    //小额信贷
//                    if($msgarr && is_array($msgarr['AccountChangeDer'])){
//                        $condition['loan_detail_url'] = $msgarr['AccountChangeDer'][0]['url'];
//                    }
                }
            }
            $condition['status'] = "FINISHED";
            $res = $value->updateBankbill($condition);
            if(!$res){
                Logger::errorLog(print_r(array("bankbill_id: ".$value->id . "失败"), true), 'bankOrder_error', 'bankOrder');
            }
            Logger::errorLog(print_r(array("bankbill_id: ".$value->id . "成功"), true), 'bankOrder_success', 'bankOrder');
            return true;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

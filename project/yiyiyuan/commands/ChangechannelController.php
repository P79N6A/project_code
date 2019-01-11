<?php

namespace app\commands;

use app\models\news\Payaccount;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\dev\User_loan_flows;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\User_remit_list;
use Yii;
use yii\console\Controller;

/**
 * 银行存管，借款已经打到用户的电子账户2小时，用户未设置密码提现，或者提现未成功，切换出款通道为112，刚对记录表改为体制外，出款记录改为changelock
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii loandefaulttiming > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe d:\www\yiyiyuan\yii loandefaulttiming
 */
/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class ChangechannelController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $error_num = 0;
        $success_num = 0;
        $limit = 500;
        $front_2_time = date("Y-m-d H:i:00", (time() - (60 * 60 * 2)));  //前推2小时
        $front_2_10_time = date("Y-m-d H:i:00", strtotime($front_2_time) - 60 * 10); //在前推10分钟
        $where = [
            'AND',
            [User_loan_extend::tableName() . ".status" => 'DOREMIT'],
            [User_loan::tableName() . ".business_type" => [1, 4, 5, 6]],
            [User_loan_extend::tableName() . ".payment_channel" => 7],
            [User_remit_list::tableName() . ".remit_status" => 'INIT'],
            [">=", User_loan_extend::tableName() . ".last_modify_time", $front_2_10_time],
            ["<", User_loan_extend::tableName() . ".last_modify_time", $front_2_time]
        ];
        $total = User_loan::find()
                ->leftJoin(User_remit_list::tableName(), User_remit_list::tableName() . ".loan_id = " . User_loan::tableName() . ".loan_id")
                ->joinWith("loanextend", 'TRUE', 'LEFT JOIN')
                ->where($where)
                ->count();
        Logger::errorLog(print_r(array("一共查出$total 条数据"), true), 'changechanne_conut', 'debt');
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $user_loan = User_loan::find()
                            ->leftJoin(User_remit_list::tableName(), User_remit_list::tableName() . ".loan_id = " . User_loan::tableName() . ".loan_id")
                            ->joinWith("loanextend", 'TRUE', 'LEFT JOIN')
                            ->where($where)
                            ->offset($i * $limit)->limit($limit)->all();
            if (!empty($user_loan)) {
                foreach ($user_loan as $key => $value) {
                    $payAccountModel = new Payaccount();
                    $isPwd = $payAccountModel->getPaysuccessByUserId($value->user_id, 2, 2);
                    $loan_extend = User_loan_extend::find()->where(['loan_id' => $value->loan_id])->one();
                    $transaction = Yii::$app->db->beginTransaction();
                    if (!$isPwd) {
                        $ex_res = $loan_extend->change_channel_noget($loan_extend);
                    } else {
                        $ex_res = $loan_extend->change_channel_lock($loan_extend);
                    }
                    if (!$ex_res) {
                        $transaction->rollBack();
                        $error_num++;
                        Logger::errorLog(print_r(array("$value->loan_id 借款状态更新失败"), true), 'err', 'debt');
                        continue;
                    }
                    $transaction->commit();
                    $success_num++;
                    Logger::errorLog(print_r(array("$value->loan_id 借款状态更新成功"), true), 'succ', 'debt');
                }
            }
        }
        if ($error_num > 0) {
            Logger::errorLog(print_r(array("$error_num 条借款状态更新失败"), true), 'changechanne_err', 'debt');
        }
        Logger::errorLog(print_r(array("$success_num 条借款状态更新成功"), true), 'changechanne_succ', 'debt');
    }

}

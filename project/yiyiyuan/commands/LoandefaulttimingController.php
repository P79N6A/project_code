<?php

namespace app\commands;

use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_loan_extend;
use app\models\dev\User_loan_flows;
use app\commonapi\Http;
use app\commonapi\Logger;
use Yii;
use yii\console\Controller;

/**
 * 借款筹款大于24小时，定时更改借款状态
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

class LoandefaulttimingController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $error_num = 0;
        $limit = 500;
        $front_24_time = date("Y-m-d H:i:00", (time() - (60 * 60 * 24)));  //前推24小时
        $front_24_10_time = date("Y-m-d H:i:00", strtotime($front_24_time) - 60 * 10); //在前推10分钟
        $where = [
            'AND',
            [User_loan::tableName() . ".source" => 5],
            [User_loan::tableName() . ".status" => [6, -4]],
            [User_loan::tableName() . ".business_type" => [1,4]],
            [User_loan_flows::tableName() . ".loan_status" => 6],
            ['!=', User::tableName() . ".status", 3],
            [">=", User_loan_flows::tableName() . ".create_time", $front_24_10_time],
            ["<", User_loan_flows::tableName() . ".create_time", $front_24_time]
        ];
        $total = User_loan::find()
                ->leftJoin(User_loan_flows::tableName(), User_loan_flows::tableName() . ".loan_id = " . User_loan::tableName() . ".loan_id")
                ->leftJoin(User::tableName(), User::tableName() . '.user_id = ' . User_loan::tableName() . '.user_id')
                ->where($where)
                ->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $user_loan = User_loan::find()
                            ->leftJoin(User_loan_flows::tableName(), User_loan_flows::tableName() . ".loan_id = " . User_loan::tableName() . ".loan_id")
                            ->leftJoin(User::tableName(), User::tableName() . '.user_id = ' . User_loan::tableName() . '.user_id')
                            ->where($where)
                            ->offset($i * $limit)->limit($limit)->all();
            if (!empty($user_loan)) {
                foreach ($user_loan as $key => $value) {
                    $transaction = Yii::$app->db->beginTransaction();
                    //操作user_extend表
                    $loan_extend = User_loan_extend::find()->where(['loan_id'=>$value->loan_id])->one();
                    $loan_extend->extend_type = 4;
                    $loan_extend->status = 'REJECT';
                    $extend_save = $loan_extend->save();
                    if ($value->changeStatus(4) && $extend_save) {
                        $value->status = -4;
                        $value->save();
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        $error_num++;
                        Logger::errorLog(print_r(array("$value->loan_id 借款状态更新失败"), true), 'setLoanStatus', 'crontab');
                        continue;
                    }
                }
            }
        }
        User_loan::updateAll(['status' => 4], ['status' => -4, 'business_type' => [1,4]]);
        if ($error_num > 0) {
            Logger::errorLog(print_r(array("$error_num 条借款状态更新失败"), true), 'setLoanStatusNum', 'crontab');
        }
    }

}

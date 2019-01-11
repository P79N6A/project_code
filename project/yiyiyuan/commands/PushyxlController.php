<?php
namespace app\commands;

use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\Push_yxl;
use app\models\news\UmengSend;
use Yii;
use yii\console\Controller;

/*
 * 审核通过两小时内未支付
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii loandefaulttiming > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe d:\www\yiyiyuan\yii loandefaulttiming
 */
set_time_limit(0);
ini_set('memory_limit', '-1');

class PushyxlController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $error_num = 0;
        $success_num = 0;
        $limit = 500;
        $front_2_time = date("Y-m-d H:i:00", (time() - (60 * 60 * 2)));  //前推2小时
        $front_2_10_time = date("Y-m-d H:i:00", strtotime($front_2_time) - 60 * 10); //在前推10分钟
        $where = [
            'AND',
            [User_loan_extend::tableName() . ".status" => 'TB-SUCCESS'],
            [Push_yxl::tableName() . ".type" => 1],
            [Push_yxl::tableName() . ".notify_status" => 1],
            [Push_yxl::tableName() . ".loan_status" => 3],
            [">=", Push_yxl::tableName() . ".notify_time", $front_2_10_time],
            ["<", Push_yxl::tableName() . ".notify_time", $front_2_time]
        ];
        $total = Push_yxl::find()
                ->leftJoin(User_loan_extend::tableName(), User_loan_extend::tableName() . ".loan_id = " . Push_yxl::tableName() . ".loan_id")
                ->where($where)
                ->count();
//        print_r($total);die;
        Logger::errorLog(print_r(array("一共查出$total 条数据"), true), 'pushyxl', 'commands');
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $push_yxl = Push_yxl::find()
                        ->leftJoin(User_loan_extend::tableName(), User_loan_extend::tableName() . ".loan_id = " . Push_yxl::tableName() . ".loan_id")
                        ->where($where)
                        ->offset($i * $limit)->limit($limit)->all();
            if (!empty($push_yxl)) {
                foreach ($push_yxl as $key => $value) {
                    $loan_extend = User_loan_extend::find()->where(['loan_id' => $value->loan_id])->one();
                    //添加提现提醒umeng_send
                    $umengSend = (new UmengSend())->saveUmengSend($loan_extend->loan, 1);
                    if(!$umengSend){
                        $error_num++;
                        Logger::errorLog(print_r(array("$value->loan_id 推送添加失败"), true), 'pushyxl', 'commands');
                        continue;
                    }
                    $success_num++;
                }
            }
        }
        if ($error_num > 0) {
            Logger::errorLog(print_r(array("$error_num 条推送添加失败"), true), 'pushyxl', 'commands');
        }
    }

}

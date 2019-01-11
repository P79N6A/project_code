<?php

/**
 * 优惠券发放
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii income > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use app\models\dev\Coupon_apply;
use app\models\dev\Coupon_list;
use app\models\dev\User;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendcoupontonewController extends Controller {

    // 命令行入口文件
    public function actionIndex() {

        $sucess = 0;
        $start_time = date('Y-m-d 00:00:00', strtotime('-1 days'));
        $end_time = date('Y-m-d 00:00:00');
        $total = User::find()->where(['>=', 'create_time', $start_time])->andFilterWhere(['<', 'create_time', $end_time])->count();
        $pages = ceil($total / 1000);
        for ($i = 0; $i < $pages; $i++) {
            $users = User::find()->where(['>=', 'create_time', $start_time])->andFilterWhere(['<', 'create_time', $end_time])->offset($i * 1000)->limit(1000)->all();
            foreach ($users as $key => $val) {
                $coupon = Coupon_list::find()->where(['mobile' => $val->mobile])->one();
                if (!empty($coupon)) {
                    continue;
                } else {
                    $couponModel = new Coupon_apply();
                    $res = $couponModel->sendcoupon($val->user_id, '新手专享', 1, 30, 88, $number = 10000);
                    if ($res) {
                        $sucess++;
                    }
                }
            }
        }
        $this->log("\n处理结果:success{$sucess}个用户发送成功");
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

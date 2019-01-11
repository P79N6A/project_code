<?php

/**
 * 每日白名单用户提额
 * linux : /data/wwwroot/yiyiyuan/yii setwhitequota
 */

namespace app\commands;

use app\models\dev\User_quota;
use app\models\dev\User_quota_record;
use app\models\dev\White_list;
use yii\console\Controller;

class SetwhitequotaController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $endtime = date('Y-m-d 23:59:59', strtotime('-1 days'));
        $starttime = date("Y-m-d 00:00:00", strtotime("-1 days"));
        $condition = ['between', 'last_modify_time', $starttime, $endtime]; //between 包含边界值
        $total = White_list::find()->where($condition)->count();
        $limit = 1000;
        $page = ceil($total / $limit);
        for ($i = 0; $i < $page; $i++) {
            $white = White_list::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
            if (!empty($white)) {
                foreach ($white as $key => $val) {
                    $quotaModel = new User_quota;
                    $quotaModel->setUserQuota($val->user_id, '借款达标,系统提额');
                }
            } else {
                break;
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

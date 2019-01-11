<?php

namespace app\commands;

use Yii;
use app\commonapi\Logger;
use yii\console\Controller;
use app\models\news\User;
use app\models\news\User_label;

set_time_limit(0);
ini_set('memory_limit', '-1');

class LabeladdController extends Controller {

    public function actionIndex(){

        $start_time = date("2017-10-21 00:00:00");
        $end_time   = date("2017-10-23 00:00:00");
        $time = date("Y-m-d H:i:s");
        if($time < $start_time || $time > $end_time){
            exit();
        }
        $limit = 500; //每次循环处理数量
        $verify_time_start = date("Y-m-d H:i:00",strtotime("-5 minute"));
        $verify_time_end   = date("Y-m-d H:i:00");
        $whereconfig = [
            'AND',
            ['status' => 3],
            ['BETWEEN', 'verify_time', $verify_time_start, $verify_time_end],
        ];
        $total = User::find()->where($whereconfig)->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $userLoan = User::find()->where($whereconfig)->offset($i * $limit)->limit($limit)->all();
            if (empty($userLoan)) {
                break;
            }
            foreach ($userLoan as $key => $val){
                $data = [
                    'mobile' => $val->mobile,
                    'label' => 'charge',
                ];
                $td_info = User_label::find()->where($data)->one();
                if(!empty($td_info)){
                    continue;
                }
                $save = (new User_label())->addLabel($data);
                if (!$save) {
                    Logger::dayLog('addLabel', 'error：保存用户失败:mobile', $val->mobile, date('Y-m-d H:i:s') . PHP_EOL);
                }
            }
        }
    }
}

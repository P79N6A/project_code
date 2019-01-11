<?php

namespace app\commands;

use app\models\dev\Statistics_type;
use app\models\own\StatisticsType;
use yii\console\Controller;

/**
 * 每天凌晨1点执行一次  同步Statistics_type数据到线下库
 * windows D:phpStudy\php56n\php.exe D:WWW\yyymobile\yii statisticstype index
 */
class StatisticstypeController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $time = time();
        $statrTime = date("Y-m-d 00:00:00", strtotime('-1 days'));
        $endTime = date("Y-m-d 00:00:00",$time);
        $where = [
            'AND',
            ['>=' , 'create_time',$statrTime],
            ['<' , 'create_time',$endTime],
        ];
        $info = Statistics_type::find()
            ->where($where)
            ->asArray()
            ->all();
        
        if(!empty($info)){
            foreach($info as $k => $v){
                unset($v['id']);
                $model = new StatisticsType();
                $model ->createData($v);
            }
        }
    }
}
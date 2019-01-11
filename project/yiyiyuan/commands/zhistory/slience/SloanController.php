<?php

namespace app\commands\slience;

use Yii;
use yii\console\Controller;
use app\models\slience\Sloan;
use app\commonapi\Logger;
/**
 * 计算比率, 生成暗续
 *   linux : /data/wwwroot/yiyiyuan/yii slience/sloan/importday
 *   window : d:\xampp\php\php.exe d:\www\yiyiyuan\yii slience/sloan/importday
 */
set_time_limit(0);
ini_set('memory_limit', '-1');
class SloanController extends Controller {
    /**
     * 某一天
     * 命令 slience/sloan/status
     * @param  string $theday 某一天 2017-07-07
     * @return
     */
    public function actionImportday($theday = null) {
        //1. 默认昨天 
        if (empty($theday)) {
            $theday = date('Y-m-d', time() - 86400);
        }

        $oSloan = new Sloan;
        $info = $oSloan -> importSliences($theday);
        if(empty($info)){
            Logger::dayLog('slience/importday', $theday, 'no rows' );
            exit;
        }
        Logger::dayLog('slience/importday', $theday, $info);
        var_export($info);
    }
    /**
     * 批量处理 slience/sloan/importdays "2017-01-01" "2017-02-01"  1
     * @return [type] [description]
     */
    public function actionImportdays($start_date, $end_date, $days) {
        $start_date = date('Y-m-d', strtotime($start_date));
        $end_date = date('Y-m-d', strtotime($end_date));

        for($theday = $start_date; $theday<$end_date;){
            echo $theday . "\n";
            $oSloan = new Sloan;
            $info = $oSloan -> importSlience($theday, $days);
            if(empty($info)){
                Logger::dayLog('slience/importdays', $theday, 'no rows' );
            }
            Logger::dayLog('slience/importdays', $theday, $info);
            var_export($info);

            $theday = date('Y-m-d', strtotime($theday) + 86400);
        }
    }    
}

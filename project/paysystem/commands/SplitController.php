<?php

/**
 *  财务对账完成---》拆账任务
 * 定时采集数据    拆账
D:\phpStudy\php55n\php.exe D:\paysystem\yii split runQuerys
 */
// #查询
// */10 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii split runQuerys 1>/dev/null 2>&1
//D:/phpStudy/PHPTutorial/php/php-5.6.27-nts/php.exe  D:/workspace/paysystem/yii split newQueryss
namespace app\commands;

use app\common\Logger;
use app\modules\balance\common\SplitCommon;
use app\modules\balance\common\SplitUnder;
use app\modules\balance\common\SplitNew;
use app\modules\balance\common\SplitNews;

/**s
 * 数聚魔盒报告查询
 */
class SplitController extends BaseController {

    /**
     * 对账完成的数据---拆账
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oS = new SplitCommon();
        $data = $oS->runAll();
        Logger::dayLog('command', 'SplotReport/runSplitQuerys', $data);
        return json_encode($data);
    }


    /**
     * 拆分线下还款的财务数据
     * 每五分钟执行一次
     * under 线下
     */
    public function runUnders($start_time = null, $end_time = null) {
        $time = time();
        if (empty($end_time)) {
            $end_time = date('Y-m-d 23:59:59', $time - (24*3600*2));
        }
        if (empty($start_time)) {
            // 当前之前的 30天
            $start_time = date('Y-m-d 00:00:00', $time - (24*3600*2));
        }
        $start_time = date('Y-m-d 00:00:00', strtotime($start_time));
        $end_time = date('Y-m-d 23:59:59', strtotime($end_time));
        $oS = new SplitUnder();
        $data = $oS->runAll($start_time,$end_time);
        Logger::dayLog('command/runUnders', 'runSplitUnders', $data);
        return json_encode($data);
    }

    /**
     *  查询所有的放款记录，然后进行回款拆账    这个可以在生产上跑 但是循环相加总数有问题
     * @return string
     */
    public function newQuerys($start_time = null, $end_time = null) {
        $time = time();
        if (empty($end_time)) {
            $end_time = date('Y-m-d 00:00:00',$time);
        }
        if (empty($start_time)) {
            // 当前之前的 30天
            $start_time = date('Y-m-d 23:59:59', $time - (24*3600*30));
        }
        $start_time = date('Y-m-d 00:00:00', strtotime($start_time));
        $end_time = date('Y-m-d 23:59:59', strtotime($end_time));
//        var_dump($end_time,$start_time);die;
        $oS = new SplitNew();
        $data = $oS->runAll($start_time,$end_time);
        Logger::dayLog('command', 'SplotReport/newQuerys', $data);
        return json_encode($data);
    }


    /**
     *  查询所有的放款记录，然后进行回款拆账  只能在本地跑  直接查整个还款表开始  数据无误
     * @return string
     */
    public function newQueryss($start_time = null, $end_time = null) {
        $time = time();
        if (empty($end_time)) {
            $end_time = date('Y-m-d 00:00:00',$time);
        }
        if (empty($start_time)) {
            // 当前之前的 30天
            $start_time = date('Y-m-d 23:59:59', $time - (24*3600*30));
        }
        $start_time = date('Y-m-d 00:00:00', strtotime($start_time));
        $end_time = date('Y-m-d 23:59:59', strtotime($end_time));
//        var_dump($end_time,$start_time);die;
        $oS = new SplitNew();
        $data = $oS->getRepayInfos($start_time,$end_time);
        Logger::dayLog('command', 'SplotReport/newQuerys', $data);
        return json_encode($data);
    }



    /**
     * 七天乐手动拆账
     *  查询所有的放款记录，然后进行回款拆账  只能在本地跑  直接查整个还款表开始  数据无误
     * @return string
     */
    public function newQuerys7($start_time = null, $end_time = null) {
        $time = time();
        if (empty($end_time)) {
            $end_time = date('Y-m-d 00:00:00',$time);
        }
        if (empty($start_time)) {
            // 当前之前的 30天
            $start_time = date('Y-m-d 23:59:59', $time - (24*3600*30));
        }
        $start_time = date('Y-m-d 00:00:00', strtotime($start_time));
        $end_time = date('Y-m-d 23:59:59', strtotime($end_time));
//        var_dump($end_time,$start_time);die;
        $oS = new SplitNews();
        $data = $oS->getRepayInfos($start_time,$end_time);
        Logger::dayLog('command', 'SplotReport/newQuerys', $data);
        return json_encode($data);
    }

}
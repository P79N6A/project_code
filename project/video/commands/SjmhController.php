<?php

/**
 * 数聚魔盒报告查询
 *
 *
 * 定时采集数据地址
    D:\phpStudy\php55n\php.exe D:\open\yii sjmh runQuerys
    定时通知地址
    D:\phpStudy\php55n\php.exe D:\open\yii sjmh runNotify
 */
// #查询
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii sjmh runQuerys 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\sjmh\SjmhCollection;
use app\modules\api\common\sjmh\SNotify;
/**
 * 数聚魔盒报告查询
 */
class SjmhController extends BaseController {

    /**
     * 数据魔盒  采集数据
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oS = new SjmhCollection();
        $data = $oS->runAll();
        Logger::dayLog('command', 'SjmhReport/runCollectionQuerys', $data);
        return json_encode($data);
    }

    /**
     * 数据魔盒数据  通知
     * 每五分钟执行一次
     */
    public function runNotify() {
        $oS = new SNotify;
        $data = $oS->runAll();
        Logger::dayLog('command', 'SjmhReport/runNotifyQuerys', $data);
        return json_encode($data);
    }

}
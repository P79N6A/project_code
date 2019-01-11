<?php

/**
 * 数聚魔盒报告查询
 * windows d:\xampp\php\php.exe D:\www\open\yii remit []
 */
// #查询
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii sjt runQuerys 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\sjt\ReportSjt;
/**
 * 数聚魔盒报告查询
 */
class SjtController extends BaseController {

    /**
     * 查询
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oM = new ReportSjt;
        $data = $oM->runQuery();
        Logger::dayLog('command', 'reportSjt', $data);
        return json_encode($data);
    }

}

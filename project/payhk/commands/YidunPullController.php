<?php

// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii operator runnotify 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\yidun\ClientYd;
use Yii;

/**
 * 上数拉取任务
 */
class YidunPullController extends BaseController {

    /**
     * 拉取
     * 每10秒执行一次 最多10条
     */

    public function runPull() {
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $ydObj = new ClientYd($env);
        $subData =  date("Y-m-d",strtotime(" -1 day "));
        $dataList = $ydObj->timerPulldata($subData,30);
        return $dataList;
    }

}

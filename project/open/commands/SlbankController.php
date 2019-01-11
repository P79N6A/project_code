<?php
/**
 * 数立银行流水定时任务
 * @author 孙瑞
 * @crontab采集地址 ==> *\/5 7-22 * * * /usr/local/php-5.4.40/bin/php /data/wwwroot/open/yii slbank runQuerys 1>/dev/null 2>&1
 * @crontab通知地址 ==> *\/5 7-22 * * * /usr/local/php-5.4.40/bin/php /data/wwwroot/open/yii slbank runNotify 1>/dev/null 2>&1
 */
namespace app\commands;

use app\common\Logger;
use app\modules\api\common\slbank\SlbankService;
use app\modules\api\common\slbank\SlbankNotify;

class SlbankController extends BaseController {

    /**
     * 数立银行流水定时采集任务
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oS = new SlbankService();
        $data = $oS->runAll();
        Logger::dayLog('slbank/Collect','采集成功'.$data.'条');
        return json_encode($data);
    }

    /**
     * 数立银行流水定时通知任务
     * 每五分钟执行一次
     */
    public function runNotify() {
        $oS = new SlbankNotify();
        $data = $oS->runAll();
        Logger::dayLog('slbank/Notify','通知成功'.$data.'条');
        return json_encode($data);
    }

}
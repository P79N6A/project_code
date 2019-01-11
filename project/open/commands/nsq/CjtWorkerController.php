<?php

namespace app\commands\nsq;

use app\common\Logger;
use app\modules\api\common\changjie\CjRemit;

///usr/local/php-5.4.40/bin/php /data/wwwroot/open/yii nsq/cjt-worker start
/**
 * 出款任务相关功能
 */
class CjtWorkerController extends BaseWorkerController {
    protected $topic = "cjremit";
    protected $channel = "cjchannel";

    public function customer($msg) {
        $jsonData = $msg->getPayload();
        $data = json_decode($jsonData, true);
        $oM = new CjRemit;
        Logger::dayLog('command/cjtremitnsq', $data);
        $resData = $oM->runOneRemit($data);
        echo json_encode($resData);
    }
}

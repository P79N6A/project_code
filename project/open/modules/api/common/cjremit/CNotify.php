<?php

/**
 * 计划任务处理:畅捷出款流程
 * @author zhangfei
 */

namespace app\modules\api\common\cjremit;

use app\common\Logger;
use app\models\cjt\ClientNotify;
use app\models\cjt\CjtRemit;
set_time_limit(0);
class CNotify extends \app\modules\api\common\remit\CNotify {
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new CjtRemit;
        $this->oClientNotify = new ClientNotify;
        $this->logname = 'cjt/notify';
        $this->channelId = 130;
    }
}
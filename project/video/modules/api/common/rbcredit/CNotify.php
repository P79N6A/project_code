<?php

/**
 * 计划任务处理:出款流程
 * 这个是出款的逻辑类,相当于控制器功能
 */

namespace app\modules\api\common\rbcredit;

use app\common\Logger;
use app\models\rbcredit\ClientNotify;
use app\models\rbcredit\RbCreditRemit;
set_time_limit(0);
class CNotify extends \app\modules\api\common\remit\CNotify {
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new RbCreditRemit;
        $this->oClientNotify = new ClientNotify;
        $this->logname = 'rbcredit/notify';
        $this->channelId = 12;
    }
}
<?php

/**
 * 计划任务处理:连连出款流程
 */

namespace app\modules\api\common\llpay;
use app\models\lian\LLClientNotify;
use app\models\lian\LLRemit;
set_time_limit(0);
class CNotify extends \app\modules\api\common\remit\CNotify {
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new LLremit();
        $this->oClientNotify = new LLClientNotify();
        $this->logname = 'llpay/notify';
        $this->channelId = 9;
    }
}
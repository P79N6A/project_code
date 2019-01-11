<?php

/**
 * 计划任务处理:易极付出款流程
 */

namespace app\modules\api\common\yjf;
use app\models\yjf\YjfClientNotify;
use app\models\yjf\YjfRemit;
set_time_limit(0);
class CNotify extends \app\modules\api\common\remit\CNotify {
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new YjfRemit();
        $this->oClientNotify = new YjfClientNotify();
        $this->logname = 'yjf/notify';
        $this->channelId = 9;
    }
}
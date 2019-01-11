<?php

/**
 * 计划任务处理:宝付出款流程
 * 这个是中信出款的逻辑类,相当于控制器功能
 * @author lijin
 */

namespace app\modules\api\common\baofoo;

use app\common\Logger;
use app\models\baofoo\ClientNotify;
use app\models\baofoo\BfRemit;
set_time_limit(0);
class CNotify extends \app\modules\api\common\remit\CNotify {
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new BfRemit;
        $this->oClientNotify = new ClientNotify;
        $this->logname = 'baofoo/notify';
        $this->channelId = 8;
    }
}
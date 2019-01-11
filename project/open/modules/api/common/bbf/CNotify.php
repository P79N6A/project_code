<?php

/**
 * 计划任务处理:宝付出款流程
 * 这个是中信出款的逻辑类,相当于控制器功能
 * @author lijin
 */

namespace app\modules\api\common\bbf;

use app\common\Logger;
use app\models\bbf\ClientNotify;
use app\models\bbf\BbfRemit;
set_time_limit(0);
class CNotify extends \app\modules\api\common\remit\CNotify {
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new BbfRemit;
        $this->oClientNotify = new ClientNotify;
        $this->logname = 'bbf/notify';
        //$this->channelId = 20;
    }
}
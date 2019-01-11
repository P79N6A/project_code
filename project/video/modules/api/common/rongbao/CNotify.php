<?php

/**
 * 计划任务处理:融宝出款流程
 * 这个是中信出款的逻辑类,相当于控制器功能
 * @author lijin
 */

namespace app\modules\api\common\rongbao;

use app\common\Logger;
use app\models\rongbao\ClientNotify;
use app\models\rongbao\Remit;
use yii\helpers\ArrayHelper;
set_time_limit(0);
class CNotify extends \app\modules\api\common\remit\CNotify {
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new Remit;
        $this->oClientNotify = new ClientNotify;
        $this->logname = 'rongbao/notify';
        $this->channelId = 6;
    }
}
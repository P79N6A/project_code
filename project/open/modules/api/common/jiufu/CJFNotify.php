<?php
/**
 * 控制器功能
 * 计划任务处理: 通知客户端逻辑类
 * @author lijin
 */
namespace app\modules\api\common\jiufu;
use app\models\jiufu\JFClientNotify;
use app\models\jiufu\JFRemit;
use app\modules\api\common\remit\CNotify;

class CJFNotify extends CNotify {
	/**
	 * 初始化接口
	 */
	public function __construct() {
		$this->oRemit = new JFRemit;
		$this->oClientNotify = new JFClientNotify;
		$this->logname = '9f';
		$this->channelId = 3;
	}
}
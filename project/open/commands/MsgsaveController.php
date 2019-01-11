<?php
/**
 * 同步一亿元采集的历史短信数据
 * 定时任务模块
 * @author 孙瑞
 * @crontab定时地址 ==> *\/5 7-22 * * * /usr/local/php-5.4.40/bin/php /data/wwwroot/open/yii msgsave runQuerys 1>/dev/null 2>&1
 */
namespace app\commands;
use Yii;
use app\common\Logger;
use app\modules\api\common\msgsave\MsgSaveService;
use app\models\yiyiyuan\YiMsgList;

class MsgsaveController extends BaseController {
	public $step = 500;

	public function runQuerys() {
		Logger::dayLog('msgsave','query: 开始时间: '.date('Y-m-d H:i:s'));
		$filepath = Yii::$app->basePath . '/log/runId.txt';
		$startId = file_get_contents($filepath);
		$maxId = (new YiMsgList())->getMaxId();
		$stopId = $startId + $this->step;
		$stopId = $stopId >= $maxId ? $maxId : $stopId;
		file_put_contents($filepath, $stopId);
        $data = (new MsgSaveService())->runAll($startId, $stopId);
		Logger::dayLog('msgsave','query: 结束时间: '.date('Y-m-d H:i:s'));
        return $data;
	}
}
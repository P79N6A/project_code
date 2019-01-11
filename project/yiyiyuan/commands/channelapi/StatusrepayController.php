<?php
namespace app\commands\channelapi;

/**
 *   还款状态查询修改通知主表状态（新增至消息发送表） 每五分钟执行一次 每次同步状态200条
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii channelapi/statusrepay
 *   windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii channelapi/statusrepay
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\GuideNotify;
use app\models\news\GuideNotifyList;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class StatusrepayController extends BaseController
{
    public function actionIndex()
    {
        $time = time();
        $stime = date("Y-m-d", $time - 24 * 3600 * 2); //两天内

        $res = (new GuideNotify())->listInitialNotify($stime, $type = 3, $limit = 200);
        if (empty($res)) {
            exit();
        }
        foreach ($res as $key => $val) {
            $this->getLoanStatus($val);
        }
    }

    private function getLoanStatus($guideNotifyObj)
    {
        if (empty($guideNotifyObj)) {
            return false;
        }
        $repayStatus = isset($guideNotifyObj->repay->status) ? $guideNotifyObj->repay->status : 0;
        if ($repayStatus == 1) {
            $this->setGuideStatus($guideNotifyObj, $repayStatus);
        }
        if ($repayStatus == 4) {
            //失败，映射通知表状态为2
            $this->setGuideStatus($guideNotifyObj, $repayStatus = 2);
        }
    }

    private function setGuideStatus($guideNotifyObj, $status)
    {
        $result = $guideNotifyObj->updateNoticeStatus($status);
        $data = [
            'gid' => $guideNotifyObj->id,
            'result_status' => $status,
        ];
        $info = (new GuideNotifyList())->add($data);
        if (!$result || !$info) {
            Logger::dayLog('channel_script/statusverify', '还款状态更新', $guideNotifyObj, $status, $result, $info);
        }
    }
}

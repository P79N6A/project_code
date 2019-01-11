<?php
namespace app\commands\channelapi;

/**
 *   出款状态查询新增通知主表记录（新增至消息发送表） 每五分钟执行一次 每次同步状态200条
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii channelapi/statusremit
 *   windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii channelapi/statusremit
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\GuideNotify;
use app\models\news\GuideNotifyList;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class StatusremitController extends BaseController
{
    public function actionIndex()
    {
        $time = time();
        $stime = date("Y-m-d", $time - 24 * 3600 * 2); //两天内

        $res = (new GuideNotify())->listInitialNotify($stime, $type = 2, $limit = 200);
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
        $loanextend_status = isset($guideNotifyObj->loanextend->status) ? $guideNotifyObj->loanextend->status : '';
        //放款成功
        if ($loanextend_status == 'SUCCESS') {
            $this->setGuideStatus($guideNotifyObj, $status = 1);
        }
        //放款失败
        if ($loanextend_status == "REJECT") {
            $this->setGuideStatus($guideNotifyObj, $status = 2);
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
            Logger::dayLog('channel_script/statusverify', '出款状态更新', $guideNotifyObj, $status, $result, $info);
        }
    }
}

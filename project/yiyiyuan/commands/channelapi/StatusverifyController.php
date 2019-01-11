<?php
namespace app\commands\channelapi;

/**
 *   审核状态查询修改通知主表状态（新增至消息发送表） 每五分钟执行一次 每次同步状态200条
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii channelapi/statusverify
 *   windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii channelapi/statusverify
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\dev\Payaccount;
use app\models\news\GuideNotify;
use app\models\news\GuideNotifyList;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class StatusverifyController extends BaseController
{
    public function actionIndex()
    {
        $time = time();
        $stime = date("Y-m-d", $time - 24 * 3600 * 2); //两天内

        $res = (new GuideNotify())->listInitialNotify($stime, $type = 1, $limit = 200);
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
        $loan_status = isset($guideNotifyObj->userloan->status) ? $guideNotifyObj->userloan->status : 0;
        //审核成功
        $audited_loan_status = [6, 9];
        if (in_array($loan_status, $audited_loan_status)) {
            $this->setGuideStatus($guideNotifyObj, $status = 1);
        }
        //审核失败
        $audit_failure_loan_status = [7];
        if (in_array($loan_status, $audit_failure_loan_status)) {
            $this->setGuideStatus($guideNotifyObj, $status = 2);
        }
    }

    private function setGuideStatus($guideNotifyObj, $status)
    {
        $result = $guideNotifyObj->updateNoticeStatus($status);
        //审核通过添加放款记录
        if ($status == 1) {
            $remitData = [
                'type' => 2,
                'pid' => $guideNotifyObj->pid,
                'url' => $guideNotifyObj->url
            ];
            (new GuideNotify())->add($remitData);
        }
        //添加通知发送表
        $data = [
            'gid' => $guideNotifyObj->id,
            'result_status' => $status,
        ];
        $info = (new GuideNotifyList())->add($data);
        if (!$result || !$info) {
            Logger::dayLog('channel_script/statusverify', '审核状态更新', $guideNotifyObj, $status, $result, $info);
        }
    }
}

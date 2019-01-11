<?php
namespace app\commands\channelapi;

/**
 *   发送通知 每五分钟执行一次 每次发送200条
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii channelapi/sendnotify sendverify或sendremit或sendrepay
 *   windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii channelapi/sendnotify sendverify或sendremit或sendrepay
 */

use app\commands\BaseController;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\GuideNotifyList;
use app\models\news\Loan_repay;
use app\models\news\User_loan;
use app\modules\channelapi\common\ApiController;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendnotifyController extends BaseController
{
    public function sendverify()
    {
        $this->listNotify($type = 1);
    }

    public function sendremit()
    {
        $this->listNotify($type = 2);
    }

    public function sendrepay()
    {
        $this->listNotify($type = 3);
    }

    private function listNotify($type)
    {
        $time = time();
        $stime = date("Y-m-d", $time - 24 * 3600 * 2); //两天内
        $res = (new GuideNotifyList())->listNotify($stime, $type, $limit = 200);
        if (empty($res)) {
            exit();
        }
        $ids = ArrayHelper::getColumn($res, 'id');
        GuideNotifyList::updateAll(['notice_status' => 2], ['notice_status' => [1, 5], 'id' => $ids]);
        switch ($type) {
            case 1:
                $this->verify($res);
                break;
            case 2:
                $this->remit($res);
                break;
            case 3:
                $this->repay($res);
                break;
        }
    }

    private function verify($verifyData)
    {
        foreach ($verifyData as $item) {
            if (empty($item) || !isset($item->guide) || empty($item->guide) || empty($item->guide->url)) {
                continue;
            }
            $item->refresh();
            $status = $this->getStatus($item);
            if (empty($status)) {
                continue;
            }
            $data = [
                'contractId' => (int)$item->guide->pid, // 借款订单号
                'status' => $status, // 1:审核失败,2:审核成功,3:出款失败,4:出款成功
                'from' => 'yiyiyuan_api',
                'remark' => '',//预留
                'repayPlan' => ''
            ];
            $data['sign'] = ApiController::buildSign($data);
            $sendRes = Http::interface_post_form_urlencoded($item->guide->url, $data);
            $result = json_decode($sendRes, true);
            if ((isset($result['rsp_msg']) && ($result['rsp_msg'] == 'success' || $result['rsp_msg'] == 'SUCCESS')) || (isset($result['rsp_code']) && $result['rsp_code'] == '0000')) {
                $updateRes = $item->updateSuccess();
            } else {
                $updateRes = $item->updateError();
                Logger::dayLog('channel_script/sendverify', '通知状态返回记录ID:' . $item->id, $sendRes, $result);
            }

            if (!$updateRes) {
                Logger::dayLog('channel_script/sendverify', '通知状态更新失败', $item, $result);
            }
        }
    }

    private function remit($remitData)
    {
        foreach ($remitData as $item) {
            if (empty($item) || !isset($item->guide) || empty($item->guide) || empty($item->guide->pid) || empty($item->guide->url)) {
                continue;
            }
            $item->refresh();
            $status = $this->getStatus($item);
            if (empty($status)) {
                continue;
            }
            $data = [
                'contractId' => (int)$item->guide->pid, // 借款订单号
                'status' => $status, // 1:审核失败,2:审核成功,3:出款失败,4:出款成功
                'from' => 'yiyiyuan_api',
                'remark' => '',//预留
                'repayPlan' => ''
            ];
            if ($data['status'] == 4) { //出款成功需要传还款计划
                $loanInfo = User_loan::findOne($item->guide->pid);
                if (empty($loanInfo)) {
                    continue;
                }
                $repay_amount = (new User_loan())->getRepaymentAmount($loanInfo); //应还款金额
                $repayPlan = [
                    'amount' => $repay_amount, //应还款金额
                    'periodNo' => 1, //期数
                    'dueTime' => $loanInfo->end_date, //到期还款时间
                    'payType' => 1, //支持还款类型
                    'canRepayTime' => '',//预留
                    'startTime' => $loanInfo->start_date,
                    'interestFee' => $loanInfo->interest_fee,
                    'withdrawFee' => $loanInfo->withdraw_fee,
                    'actualAmount' => (new User_loan())->getActualAmount($loanInfo->is_calculation, $loanInfo->amount, $loanInfo->withdraw_fee),
                ];
                $data['repayPlan'] = json_encode($repayPlan);
            }
            $data['sign'] = ApiController::buildSign($data);
            $sendRes = Http::interface_post_form_urlencoded($item->guide->url, $data);
            $result = json_decode($sendRes, true);
            if ((isset($result['rsp_msg']) && ($result['rsp_msg'] == 'success' || $result['rsp_msg'] == 'SUCCESS')) || (isset($result['rsp_code']) && $result['rsp_code'] == '0000')) {
                $updateRes = $item->updateSuccess();
            } else {
                $updateRes = $item->updateError();
                Logger::dayLog('channel_script/sendremit', '通知状态返回记录ID:' . $item->id, $sendRes, $result);
            }

            if (!$updateRes) {
                Logger::dayLog('channel_script/sendremit', '通知状态更新失败', $item, $result);
            }
        }
    }

    private function repay($repayData)
    {
        foreach ($repayData as $item) {
            if (empty($item) || !isset($item->guide) || empty($item->guide) || empty($item->guide->pid) || empty($item->guide->url)) {
                continue;
            }
            $item->refresh();
            $repayInfo = Loan_repay::findOne($item->guide->pid);
            if (empty($repayInfo))
                continue;
            $data = [
                'orderid' => $repayInfo->loan_id,
                'repay_money' => $repayInfo->actual_money > 0 ? $repayInfo->actual_money : 0, //还款金额
                'repay_date' => $repayInfo->createtime, //还款时间
                'payorderid' => $item->guide->rid, //还款倒流的订单ID
                'repay_result' => $item->guide->status, //推送的结果 成功or失败
                'from' => 'yiyiyuan_api',
                'paybill' => $repayInfo->paybill,
                'repay_time' => $repayInfo->repay_time
            ];
            $data['sign'] = ApiController::buildSign($data);
            $sendRes = Http::interface_post_form_urlencoded($item->guide->url, $data);
            $result = json_decode($sendRes, true);
            if ((isset($result['rsp_msg']) && ($result['rsp_msg'] == 'success' || $result['rsp_msg'] == 'SUCCESS')) || (isset($result['rsp_code']) && $result['rsp_code'] == '0000')) {
                $updateRes = $item->updateSuccess();
            } else {
                $updateRes = $item->updateError();
                Logger::dayLog('channel_script/sendrepay', '通知状态返回记录ID:' . $item->id, $sendRes, $result);
            }

            if (!$updateRes) {
                Logger::dayLog('channel_script/sendrepay', '通知状态更新失败', $item, $result);
            }
        }
    }

    // 1:审核失败,2:审核成功,3:出款失败,4:出款成功
    private function getStatus($val)
    {
        $array = [
            1 => [1 => 2, 2 => 1],
            2 => [1 => 4, 2 => 3]
        ];
        if (isset($array[$val->guide->type][$val->guide->status])) {
            return $array[$val->guide->type][$val->guide->status];
        }
        return 0;
    }
}

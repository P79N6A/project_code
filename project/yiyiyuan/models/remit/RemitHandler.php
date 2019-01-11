<?php
/**
 * 出款处理模型
 */
namespace app\models\remit;

use app\commonapi\Logger;
use app\models\news\CommonNotify;
use app\models\news\Money_limit;
use app\models\news\User_remit_list;
use yii\helpers\ArrayHelper;

class RemitHandler {
    /**
     * 出款运行
     * @param int $channel
     * @return []
     */
    public function runByChannel($channel) {
        //1. 查询要处理的订单
        $channel = intval($channel);
        if (!$this->validChannel($channel)) {
            Logger::dayLog("autoremit", $channel, "此通道不支持");
            return $this->resp("NOCHANNEL", "此通道不支持");
        }

        if ($this->isMaxMoney($channel)) {
            Logger::dayLog("autoremit", "达日通道最大金额上限");
            return $this->resp("CHANNEL_MAX_MONEY", "达日最大金额上限");
        }

        if ($this->isMaxPushMoney()) {
            Logger::dayLog("autoremit", "达日最大金额上限");
            return $this->resp("ALL_MAX_MONEY", "达日最大金额上限");
        }

        $oRemitList = new User_remit_list;
        $remitData = $oRemitList->getInitData($channel, 200);
        if (!$remitData) {
            Logger::dayLog("autoremit", "无数据");
            return $this->resp("NODATA", "无数据处理");
        }

        //2 悲观锁定状态
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemitList->lockRemits($ids);
        if (!$ups) {
            Logger::dayLog("autoremit", "锁定失败");
            return $this->resp("LOCKFAIL", "锁定失败");
        }

        //3 计算处理总数
        $initRet = ['total' => count($ids), 'success' => 0];

        //4 逐条处理
        foreach ($remitData as $oRemit) {
            $result = $this->doRemit($oRemit);
            if (!$result) {
                Logger::dayLog("autoremit", $oRemit->id, "处理失败");
                continue;
            }

            $initRet['success']++;
        }
        return $this->resp("0000", $initRet);
    }
    /**
     * 执行一条出款纪录
     * @param int $id User_remit_list 的id
     * @return bool
     */
    public function runById($id) {
        //1 获取一条纪录
        $id = intval($id);
        if (!$id) {
            return $this->resp('ERROR', "ID必须是数字");
        }
        $oRemit = User_remit_list::findOne($id);
        if (!empty($oRemit)) {
            return $this->resp('ERROR', "无法找到对应的纪录");
        }
        if ($oRemit->status != 'INIT') {
            return $this->resp('ERROR', "状态不合法");
        }
        if (!in_array($oRemit->fund, array(1,2))) {
            return $this->resp('ERROR', "通道不支持");
        }

        //3. 处理单条纪录
        $result = $this->doRemit($oRemit);
        if (!$result) {
            return $this->resp('ERROR', $oRemit->id . "处理失败");
        }

        return $this->resp('0000', 'ok');
    }
    /**
     * 处理一条出款纪录
     * @param  obj $oRemit
     * @return  bool
     */
    private function doRemit($oRemit) {
        //1. 锁定状态
        $result = $oRemit->lock();
        if (!$result) {
            Logger::dayLog('autoremit', $oRemit->id, "无法锁定,可能已被其它进程处理");
            return false;
        }

        //2. 根据通道获取处理模型
        $payModel = $this->getPayApi($oRemit['payment_channel']);
        if (empty($payModel)) {
            Logger::dayLog('autoremit', $oRemit->id, "没有找到处理模型");
            return false;
        }

        //3. 调用对就处理模型
        $res = $payModel->pay($oRemit);
        $res_code = ArrayHelper::getValue($res, 'res_code');
        $res_msg = ArrayHelper::getValue($res, 'res_msg');

        //4. 处理流程
        if ($res_code == '0000') {
            $result = $oRemit->saveDoRemit();
        } else {
            $fail_codes = $payModel->getFails();
            if (in_array($res_code, $fail_codes)) {
                // 明确错误时
                $result = $oRemit->savePayFail($res_code, $res_msg);
            } else {
                // 不明确错误挂起
                $result = true;
            }
        }

        //5. 加入到通知表中
        $r = $this->addNotify($oRemit);
        return $result;
    }
    /**
     * 添加出款推送消息
     * @param obj $oRemit user_remit_list
     */
    private function addNotify($oRemit) {
        if (in_array($oRemit['remit_status'], ['DOREMIT'])) {
            $oNotify = new CommonNotify;
            $result = $oNotify->saveNotify($oRemit['loan_id'], 1);
            if (!$result) {
                Logger::dayLog('autoremit', 'addNotify', $oNotify->errors);
                return false;
            }
        }
        return true;
    }
    /**
     * 检查channel是否支持
     * @param  int $channel
     * @return bool
     */
    private function validChannel($channel) {
        $channels = [
            User_remit_list::CN_SINA,
            User_remit_list::CN_BF_PEANUT,
            User_remit_list::CN_RB_PEANUT,
            User_remit_list::CN_CHANGJIE,
            User_remit_list::CN_RB_YYY,
        ];
        return in_array($channel, $channels);
    }
    /**
     * 获取处理模块
     * @param  int $channel
     * @return obj | null 处理类
     */
    private function getPayApi($channel) {
        $channel = intval($channel);
        if (!$this->validChannel($channel)) {
            return null;
        }

        switch ($channel) {
        //融宝
        case User_remit_list::CN_RB_PEANUT:
        case User_remit_list::CN_RB_YYY:
            $pay = new PayRb;
            break;
            
        // 宝付
        case User_remit_list::CN_BF_PEANUT:
            $pay = new PayBf;
            break;

        // 新浪
        case User_remit_list::CN_SINA:
            $pay = new PaySina;
            break;

        // c畅捷
        case User_remit_list::CN_CHANGJIE:
            $pay = new PayCj();
            break;

        default:
            $pay = null;
            break;
        }
        return $pay;
    }
    /**
     * 响应
     * @param  string $rsp_code 响应码
     * @param  string $rsp_msg 响应原因
     * @return []
     */
    private function resp($rsp_code, $rsp_msg) {
        return [
            'rsp_code' => $rsp_code,
            'rsp_msg' => $rsp_msg,
        ];
    }

    /**
     * 查询出款表中某个通道的当天已成功出款总额
     * @param  [type]  $channel_id [description]
     * @return boolean             [description]
     */
    private function isMaxMoney($channel_id) {
        $oRemitList = new User_remit_list;
        $todaySuccessAmount = $oRemitList->todaySuccessMoney($channel_id);

        $oMoneyLimit = new Money_limit;
        $todayMaxAmount = $oMoneyLimit->todayMaxMoney($channel_id);

        if (empty($todayMaxAmount) || empty($todaySuccessAmount)) {
            return false;
        }

        if ((bccomp(floatval($todaySuccessAmount), floatval($todayMaxAmount), 2) == 1)) {
            Logger::dayLog("autoremit", $todaySuccessAmount, $todayMaxAmount, "当日通道出款金额超限");
            return true;
        }

        return false;
    }

    /**
     * 查询当日提交给出款通道的总金额
     * @return boolean             [description]
     */
    private function isMaxPushMoney() {
        $oRemitList = new User_remit_list;
        $todaySuccessAmount = $oRemitList->todayPushMoney();

        $oMoneyLimit = new Money_limit;
        $todayMaxAmount = $oMoneyLimit->todayMaxMoney(0,1);

        if (empty($todayMaxAmount) || empty($todaySuccessAmount)) {
            return false;
        }

        if ((bccomp(floatval($todaySuccessAmount), floatval($todayMaxAmount), 2) == 1)) {
            Logger::dayLog("autoremit", $todaySuccessAmount, $todayMaxAmount, "当日总出款金额超限");
            return true;
        }

        return false;
    }
}

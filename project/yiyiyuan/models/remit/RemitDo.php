<?php

/**
 * 出款处理模型
 */

namespace app\models\remit;

use app\commonapi\Logger;
use app\models\news\User_remit_list;
use yii\helpers\ArrayHelper;

class RemitDo {

    /**
     * 出款运行
     * @param int $channel
     * @return []
     */
    public function run($fund, $channel) {
        // 1.获得相应的处理类
        $oRemitHander = (new RemitFactory)->get($fund, $channel);
        if (!$oRemitHander) {
            Logger::dayLog("NOHANDER", "{$fund}, {$channel} 没有找到对应的处理类");
            return $this->resp("NOHANDER", "{$fund}, {$channel} 没有找到对应的处理类");
        }

        //2. 根据通道获取处理模型
        $hitResult = (new FundoutMoneyLimit)->hitRule($fund, $channel);
        if ($hitResult) {
            Logger::dayLog('RemitDo', $fund, $channel, "资方，通道出款出款相关规则");
            return $this->resp("MaxMoney", "资方，通道出款超限");
        }

        $oRemitList = new User_remit_list;
        $remitData = $oRemitList->getInitByFund($fund, $channel);
        if (!$remitData) {
            Logger::dayLog("autoremit", "无数据");
            return $this->resp("NODATA", "无数据处理");
        }

        //3 悲观锁定状态
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemitList->lockRemits($ids);
        if (!$ups) {
            Logger::dayLog("autoremit", "锁定失败");
            return $this->resp("LOCKFAIL", "锁定失败");
        }

        //4 计算处理总数
        $initRet = ['total' => count($ids), 'success' => 0];

        //5 逐条处理
        foreach ($remitData as $oRemit) {
            $result = $this->doRemit($oRemitHander, $oRemit);
            if (!$result) {
                Logger::dayLog("autoremit", $oRemit->id, "处理失败");
                continue;
            }

            $initRet['success'] ++;
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
            return $this->resp('ERROR1', "ID必须是数字");
        }
        $oRemit = User_remit_list::findOne($id);
        if (empty($oRemit)) {
            return $this->resp('ERRO2R', "无法找到对应的纪录");
        }
        if ($oRemit->remit_status != 'INIT') {
            return $this->resp('ERR3OR', "状态不合法");
        }
        $fund = $oRemit->fund;
        $channel = $oRemit->payment_channel;

        // 获得相应的处理类
        $oRemitHander = RemitFactory::get($oRemit->loanExtend->fund, $oRemit->loanExtend->payment_channel);
        if (!$oRemitHander) {
            Logger::dayLog("NOHANDER", "{$oRemit->loanExtend->fund}, {$oRemit->loanExtend->payment_channel} 没有找到对应的处理类");
            return $this->resp("NOHANDER", "{$oRemit->loanExtend->fund}, {$oRemit->loanExtend->payment_channel} 没有找到对应的处理类");
        }
        //3. 处理单条纪录
        $result = $this->doRemit($oRemitHander, $oRemit);
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
    protected function doRemit($oRemitHander, $oRemit) {
        if (!$oRemitHander) {
            Logger::dayLog('autoremit', "没有处理类");
            return false;
        }
        $isLock = $oRemit->lock();
        if (!$isLock) {
            Logger::dayLog('autoremit', $oRemit->id, "无法锁定,可能已被其它进程处理");
            return false;
        }
        $res = $oRemitHander->pay($oRemit);
        // @todo 挪处理
        $res_code = ArrayHelper::getValue($res, 'res_code');
        $res_msg = ArrayHelper::getValue($res, 'res_msg');
        $status = ArrayHelper::getValue($res, 'status');
        $result = FALSE;
        switch ($status) {
            //处理中，成功，失败，业务中间状态
            //成功的时候对fund=10做判断
            case 'DOREMIT':
                $result = $oRemit->saveDoRemit();
                break;
            case 'FAIL':
                if ($oRemit->fund == 1) {
                    $result = $oRemit->changeFund($res_code, $res_msg);
                } elseif ($oRemit->fund == 5) {
                    $result = $oRemit->changeFund($res_code, $res_msg);
                } elseif ($oRemit->fund == 6) {
                    $result = $oRemit->changeFund($res_code, $res_msg);
                } else {
                    $result = $oRemit->savePayFail($res_code, $res_msg);
                }
                break;
            case 'SUCCESS':
                if ($oRemit->fund == 10) {
                    $result = $oRemit->savePaySuccess();
                }
                break;
            case 'PREREMIT'://如果需要特殊处理的资方可能有此状态
                $result = $oRemit->savePreRemit();
                break;
            default :
                $result = FALSE;
        }
        return $result;
    }

    /**
     * 添加出款推送消息
     * @param obj $oRemit user_remit_list
     */
    protected function addNotify($oRemit) {
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

}

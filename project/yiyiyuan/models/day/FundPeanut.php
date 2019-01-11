<?php

/**
 * 出款处理模型
 */

namespace app\models\day;

use app\commonapi\Logger;
use app\models\news\CommonNotify;
use app\models\remit\PayBf;
use app\models\remit\PayCj;
use yii\helpers\ArrayHelper;

class FundPeanut {

    /**
     * 出款运行
     * @param int $channel
     * @return []
     */
    public function run($fund, $channel) {

        $oRemitList = new User_remit_list_guide();

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
            $result = $this->doRemit($oRemit);
            if (!$result) {
                Logger::dayLog("autoremit", $oRemit->id, "处理失败");
                continue;
            }

            $initRet['success'] ++;
        }
        return $this->resp("0000", $initRet);
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
                Logger::dayLog('autoremit', $oRemit->id, '提交结果不明');
            }
        }
        return $result;
    }

    /**
     * 处理一条出款纪录
     * @param  obj $oRemit
     * @return  bool
     */
    public function pay($oRemit) {
        //1. 根据通道获取处理模型
        $payModel = $this->getPayApi($oRemit['payment_channel']);
        if (empty($payModel)) {
            Logger::dayLog('autoremit', $oRemit->id, "没有找到处理模型");
            return false;
        }

        //3. 调用对就处理模型
        $res = $payModel->pay($oRemit);
        $res_code = ArrayHelper::getValue($res, 'res_code');
        $res_msg = ArrayHelper::getValue($res, 'res_msg');
        //返回[status,res_code,res_msg]
        //4. 处理流程
        if ($res_code == '0000') {
            $status = 'DOREMIT';
//            $result = $oRemit->saveDoRemit();
        } else {
            $fail_codes = $payModel->getFails();
            if (in_array($res_code, $fail_codes)) {
                // 明确错误时
                $status = 'FAIL';
//                $result = $oRemit->savePayFail($res_code, $res_msg);
            } else {
                // 不明确错误挂起
                $status = NULL;
            }
        }
        $array = ['status' => $status, 'res_code' => $res_code, 'res_msg' => $res_msg];
        Logger::dayLog('fundpeanut', $oRemit->id, $array);
        //5. 加入到通知表中
        $r = (new CommonNotify)->addNotify($oRemit, $status);
        return $array;
    }

    /**
     * 获取处理模块
     * @param  int $channel
     * @return obj | null 处理类
     */
    private function getPayApi($channel) {
        $channel = intval($channel);

        switch ($channel) {
            //融宝
            case User_remit_list_guide::CN_RB_PEANUT:
            case User_remit_list_guide::CN_RB_YYY:
            case User_remit_list_guide::CN_RB_DAY:
            case User_remit_list_guide::CN_RB_PXHT:
                $pay = new PayRb();
                break;

            // 宝付
            case User_remit_list_guide::CN_BF_PEANUT:
                $pay = new PayBf;
                break;

            // c畅捷
            case User_remit_list_guide::CN_CHANGJIE:
                $pay = new PayCj();
                break;

            default:
                $pay = null;
                break;
        }
        return $pay;
    }

    public function isSupport($oLoan) {
        return true;
    }

    public function hitRule() {
        
    }

    public function getFails() {
        
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

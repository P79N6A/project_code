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

class FundPeanut implements CapitalInterface {

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
            case User_remit_list::CN_RB_PEANUT:
            case User_remit_list::CN_RB_YYY:
            case User_remit_list::CN_RB_PINGXIANG:
            case User_remit_list::CN_RB_YGY:
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
            case User_remit_list::CN_CJ_PINGXIANG:
                $pay = new PayCj();
                break;

            default:
                $pay = null;
                break;
        }
        return $pay;
    }

    public function isSupport($oLoan) {
        if (!empty($oLoan->remit)) {
            $fundIds = array_map(function($record) {
                return $record->attributes['fund'];
            }, $oLoan->remit);
            if (in_array(CapitalInterface::PEANUT, $fundIds)) {
                return false;
            }
        }
        return true;
    }

    public function hitRule() {
        
    }

    public function getFails() {
        
    }

}

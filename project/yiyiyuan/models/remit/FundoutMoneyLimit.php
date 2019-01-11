<?php

/**
 * 出款处理模型
 */

namespace app\models\remit;

use app\commonapi\Logger;
use app\models\news\Money_limit;
use app\models\news\User_remit_list;

class FundoutMoneyLimit {

    /**
     * 是否命中对应资方出款规则
     * @param type $fund
     * @param type $channel
     * @return bool true:命中规则  false:未命中，可以出款
     */
    public function hitRule($fund, $channel) {
        $result = TRUE;
        switch ($fund) {
            //花生米富
            case User_remit_list::FUND_PEANUT:
                $result = $this->mifuRule($channel, $fund);
                break;
            //花生米富
            case User_remit_list::FUND_QITA:
                $result = $this->mifuRule($channel, $fund);
                break;

            // 玖富
            case User_remit_list::FUND_JF:
                $result = FALSE; //@TODO 限制出款1000W左右，或者数据库配置
                break;
            // 联交所
            case User_remit_list::FUND_LIANJIAO:
                $result = FALSE;
                break;
            // 金联储
            case User_remit_list::FUND_JINLIAN:
                break;
            // 小诺
            case User_remit_list::FUND_XIAONUO:
                $model = new FundXiaonuo();
                $result = $model->hitRule();
                break;
            // 微神马
            case User_remit_list::FUND_WEISM:
                $model = new FundWeism();
                $result = $model->hitRule();
                break;
            // 存管
            case User_remit_list::FUND_CUNGUAN:
                $result = false;
                break;

            default:
                $result = TRUE;
                break;
        }
        return $result;
    }

    /**
     * 查询出款表中某个资方对应通道的当天已成功出款总额
     * @param  [type]  $channel_id [description]
     * @return boolean             [description]
     */
    private function mifuRule($channel_id) {
        $oRemitList = new User_remit_list;
        $todaySuccessAmount = $oRemitList->todaySuccessMoney($channel_id, User_remit_list::FUND_QITA);
        $oMoneyLimit = new Money_limit;
        $todayMaxAmount = $oMoneyLimit->todayMaxMoney($channel_id, User_remit_list::FUND_QITA);

        if (empty($todayMaxAmount) || empty($todaySuccessAmount)) {
            return false;
        }
        if ((bccomp(floatval($todaySuccessAmount), floatval($todayMaxAmount), 2) == 1)) {
            Logger::dayLog("fundrule/mifu", $todaySuccessAmount, $todayMaxAmount, "当日通道出款金额超限");
            return true;
        }

        return false;
    }

}

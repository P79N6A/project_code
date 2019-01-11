<?php

/**
 * 出款处理模型
 */

namespace app\models\remit;

use app\models\news\User_remit_list;

class RemitFactory {

    /**
     * 获取处理模块
     * @param  int $channel
     * @return obj | null 处理类
     */
    public function get($fund, $channel) {
        $fund = intval($fund);
        $channel = intval($channel);
        switch ($fund) {
            //花生米富
            case User_remit_list::FUND_PEANUT:
                if ($this->validChannel($channel)) {
                    $pay = new FundPeanut;
                } else {
                    $pay = null;
                }
                break;
            //其他
            case User_remit_list::FUND_QITA:
                if ($this->validChannel($channel)) {
                    $pay = new FundPeanut;
                } else {
                    $pay = null;
                }
                break;

            // 玖富
            case User_remit_list::FUND_JF:
                $pay = new FundJf;
                break;
            // 联交所
            case User_remit_list::FUND_LIANJIAO:
                if ($this->validLianChannel($channel)) {
                    $pay = new FundPeanut;
                } else {
                    $pay = null;
                }
                break;
            // 金联储
            case User_remit_list::FUND_JINLIAN:
                $pay = new FundJinlian;
                break;
            // 小诺
            case User_remit_list::FUND_XIAONUO:
                $pay = new FundXiaonuo;
                break;
            // 微神马
            case User_remit_list::FUND_WEISM:
                $pay = new FundWeism;
                break;
            // 存管
            case User_remit_list::FUND_CUNGUAN:
                $pay = new FundCunguan;
                break;
            default:
                $pay = null;
                break;
        }
        return $pay;
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
            User_remit_list::CN_RB_PINGXIANG,
            User_remit_list::CN_RB_YGY,
            User_remit_list::CN_CJ_PINGXIANG,
        ];
        return in_array($channel, $channels);
    }

    /**
     * 检查联交所channel是否支持
     * @param  int $channel
     * @return bool
     */
    private function validLianChannel($channel) {
        $channels = [
            User_remit_list::CN_RB_YYY,
        ];
        return in_array($channel, $channels);
    }

}

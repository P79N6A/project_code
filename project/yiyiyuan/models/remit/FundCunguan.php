<?php

/**
 * 存管出款
 */

namespace app\models\remit;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\CommonNotify;
use app\models\news\Payaccount;
use app\models\news\User_bank;
use Yii;

class FundCunguan implements CapitalInterface {

    /**
     * 调用接口
     * @param $oRemit
     * @return array
     */
    public function pay($oRemit) {
        $payAccount = (new Payaccount())->chkAccountAndAuth($oRemit->user_id);
        if (!$payAccount) {
            Logger::errorLog(print_r(["errorid" => $oRemit->loan->loan_id . '未开户或者未授权'], true), 'dopremiterror', 'debt');
            return ['status' => NULL, 'res_code' => '13001', 'res_msg' => '未开户或者未授权'];
        }
        //插入子订单表
        $params = [
            'remit_id' => $oRemit->id,
            'order_id' => $oRemit->order_id,
            'loan_id' => $oRemit->loan_id,
            'real_amount' => $oRemit->real_amount,
            'settle_amount' => $oRemit->settle_amount,
            'remit_status' => 'INIT',
            'bank_id' => $oRemit->bank_id,
            'user_id' => $oRemit->user_id,
        ];
        $ret = (new Cg_remit())->addCg($params);
        if ($ret) {
            $status = 'DOREMIT';
            $array = ['status' => $status, 'res_code' => '0000', 'res_msg' => '添加子订单表成功'];
        } else {
            $status = NULL;
            $array = ['status' => $status, 'res_code' => '0000', 'res_msg' => '添加子订单表失败'];
        }
        Logger::dayLog('fundcunguan', $oRemit->id, $array);
        return $array;
    }

    /**
     * 明确错误码
     * @return array
     */
    public function getFails() {
        return [];
    }

    public function getDoreimt($code) {
        $codes = [
            'CE999028',
            'CT9903',
            'CT990300',
            'CE999999',
            '510000',
            'responsefail'
        ];
        return in_array($code, $codes);
    }

    /**
     * 主动提现错误码
     * @param $code 错误码
     * @return array
     */
    public function getRemitFails($code) {
        $codes = [];
        return in_array($code, $codes);
    }

    /**
     * 提现通知错误码
     * @return array
     */
    public function getNotifyFails($code) {
        $codes = [
            'T9060752', //已冲正/撤销
        ];
        return in_array($code, $codes);
    }

    public function hitRule() {
        // TODO: Implement hitRule() method.
    }

    public function isSupport($oLoan) {
        $loan = $oLoan->loan;
        if (!$loan) {
            return false;
        }
        if ($loan->business_type == 10) {
            return false;
        }
        if (!empty($oLoan->remit)) {
            $fundIds = array_map(function($record) {
                return $record->attributes['fund'];
            }, $oLoan->remit);
            if (in_array(CapitalInterface::CUNGUAN, $fundIds)) {
                return false;
            }
        }
        $oPayaccount = new \app\models\news\Payaccount;
        $isOpen = $oPayaccount->isOutByCunguan($loan);
        return $isOpen;
    }

}

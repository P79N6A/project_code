<?php

/**
 *   存管提现出款
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii remit/remit runByChannel
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii remit/remit runByChannel 1 #1新浪; 2:
 */

namespace app\commands\remit;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\CommonNotify;
use app\models\news\GoodsLoan;
use app\models\news\Payaccount;
use app\models\news\Recall;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_loan_flows;
use app\models\news\User_remit_list;
use app\commands\BaseController;
use app\models\remit\FundCunguan;
use yii\helpers\ArrayHelper;
use Yii;

class DepremitController extends BaseController {

    public function actionIndex() {
        $cGList = new Cg_remit();
        $remitData = $cGList->getWillremitData(200);
        if (empty($remitData)) {
            exit;
        }

        $ids = ArrayHelper::getColumn($remitData, 'id');
        $cGList->updateAllLockremit($ids);

        foreach ($remitData as $oRemit) {
            $result = $this->doRemit($oRemit);
            if (!$result || $result['rep_code'] != '0') {
                Logger::dayLog("depautoremit", $oRemit->id, "处理失败");
                continue;
            }
        }
    }

    private function doRemit($oRemit) {
        $lockRes = $oRemit->lockRemit();
        if (!$lockRes) {
            Logger::errorLog(print_r(["errorid" => $oRemit->loan_id . '出款锁定失败'], true), 'dopremiterror', 'debt');
            return ['rep_code' => '1', 'rep_msg' => '出款锁定失败'];
        }
        $userModel = new User();
        $userInfo = $userModel->getUserinfoByUserId($oRemit->user_id);
        if (!$userInfo) {
            Logger::errorLog(print_r(["errorid" => $oRemit->loan_id . '用户不存在'], true), 'dopremiterror', 'debt');
            return ['rep_code' => '1', 'rep_msg' => '用户不存在'];
        }
        $loanModel = new User_loan();
        $loanInfo = $loanModel->getLoanById($oRemit->loan_id);
        if (!$loanInfo) {
            Logger::errorLog(print_r(["errorid" => $oRemit->loan_id . '借款不存在'], true), 'dopremiterror', 'debt');
            return ['rep_code' => '1', 'rep_msg' => '借款不存在'];
        }
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        $isAuth = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 3);
        if (!$isAccount || !$isAuth) {
            Logger::errorLog(print_r(["errorid" => $oRemit->loan_id . '未开户或者未授权'], true), 'dopremiterror', 'debt');
            return ['rep_code' => '1', 'rep_msg' => '未开户或者未授权'];
        }
        $settle_amount = $oRemit->settle_amount;
        $getMoneyRet = $this->getMoney($isAccount, $userInfo, $settle_amount, $loanInfo, $isAuth, $oRemit);
        Logger::errorLog(print_r([$userInfo->mobile => $getMoneyRet], true), 'Notify-getmoney', 'debt');
        if ($getMoneyRet && $getMoneyRet['retCode'] == '00000000') {
            $ret = $oRemit->outMoneySuccess();
            $status = 'SUCCESS';
        } elseif ((new FundCunguan())->getDoreimt($getMoneyRet['retCode'])) {
            $ret = $oRemit->doremit();
            $status = 'DOREMIT';
        } else{
            //$ret = $oRemit->outMoneyFail($getMoneyRet['retCode'],$getMoneyRet['retMsg']);
            //$status = 'FAIL';
            $ret = $oRemit->doremit();
            $status = 'DOREMIT';
        }
//        else {
//            $ret = NULL;
//            $status = NULL;
//        }
        Logger::dayLog('fundcunguan_cg', $oRemit->id, $ret);
        //5. 加入到通知表中
        $r = (new CommonNotify)->addNotify($oRemit, $status);

        //出款成功加入分期中间表
        $goods_loan = new GoodsLoan();
        $goods_loan->addSuccessGoodsLoan($oRemit->remitlist);
        return ['status' => $status, 'rep_code' => 0];
    }

    /**
     * 调用存管免密提现接口
     * @param $isAccount
     * @param $userInfo
     * @param $settle_amount
     * @param $loanInfo
     * @param $isAuth
     * @param $oRemit
     * @return bool
     */
    private function getMoney($isAccount, $userInfo, $settle_amount, $loanInfo, $isAuth, $oRemit) {
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002', //交易渠道
            'accountId' => $isAccount->accountId, //存管平台分配的账号
            'idType' => '01', //01-身份证
            'idNo' => $userInfo->identity, //证件号码
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $isAccount->bank->card, //银行卡号	A	19	M	绑定银行卡号
            'txAmount' => (string) round($settle_amount, 2),
            'txFee' => $loanInfo->is_calculation == 1 ? (string) round($loanInfo->withdraw_fee, 2) : '0',
            'contOrderId' => $isAuth->orderId, //预约提现签约订单号
            'from' => 1,
            'acqRes' => $oRemit->order_id
        ];
        Logger::errorLog(print_r($params, true), 'getmoney', 'debt');
        $ret = $apiDep->agreemoneyout($params);
        return $ret;
    }

}

<?php

/**
 * 
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii remit/remit runByChannel
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii remit/remit runByChannel 1 #1新浪; 2:
 */

namespace app\commands\day;

use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\day\FundPeanut;
use app\models\day\User_loan_guide;
use app\models\day\User_remit_list_guide;
use app\models\remit\RemitDo;
use yii\helpers\ArrayHelper;

class RemitController extends BaseController {

    public $limit = 500;

    public function runLoanToRemit() {

        $max_money = 500000;
        $money = (new User_remit_list_guide())->getSuccessData();
        if (bcsub($max_money, $money) <= 500) {
            echo '出款超限';
            exit;
        }
        $oUserGuideModel = new User_loan_guide();
        $initData = $oUserGuideModel->getInitData($this->limit);
        if (empty($initData)) {
            exit;
        }

        $loan_ids = ArrayHelper::getColumn($initData, 'loan_id');
        $oUserGuideModel->lockBatch($loan_ids);
        $initRet = ['total' => count($initData), 'success' => 0];
        foreach ($initData as $key => $val) {
            $lock_result = $val->lock();
            if (!$lock_result) {
                Logger::dayLog('dayremit', $val->loan_id, 'lockfaile');
                continue;
            }
            $oRemitModel = new User_remit_list_guide();
            $is_out = $oRemitModel->getDoingData($val->loan_id);
            if ($is_out) {
                $result = $oRemitModel->inData($val);
                if ($result) {
                    $chstatus = $val->repayStatus();
                    $initRet['success'] ++;
                }
            }
        }
        print_r($initRet);
    }

    /**
     * 出款运行
     * @param int $channel
     * @return str
     */
    public function runByFundChannel($fund, $channel) {
        $remitDo = new FundPeanut();
        $initRet = $remitDo->run($fund, $channel);
//        $initRet = (new RemitHandler)->runByChannel($channel);
        print_r($initRet);
    }

    /**
     * 执行一条出款纪录
     * @param int $id User_remit_list 的id
     * @return bool
     */
    public function runById($id) {
        $remitDo = new RemitDo();
        $initRet = $remitDo->runById($id);
        print_r($initRet);
    }

}

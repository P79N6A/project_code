<?php

namespace app\commands\remit;

use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\Cm_loans;
use app\models\news\Exchange;
use yii\helpers\ArrayHelper;

/**
 * 存管债权推送
 */

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class CginputclaimController extends BaseController {

    // 命令行入口文件
    public function actionIndex() {
        $cgModel = new Cg_remit();
        $loan_cg_remit = $cgModel->getInitData(200);
        if (empty($loan_cg_remit)) {
            exit;
        }
        $ids = ArrayHelper::getColumn($loan_cg_remit, 'id');
        $cgModel->updateAllLock($ids);
        foreach ($loan_cg_remit as $key => $value) {
            $lock_res = $value->lock();
            if (!$lock_res) {
                Logger::errorLog("loan_id：" . $value->loan_id . '---锁定失败', 'cgerr', 'cgsendloanclaim');
                continue;
            }
            //$res = $loanModel->sendClaim($value->loan_id);
            $cmModel = new Cm_loans();
            $data = [
                'loan_id' => $value->loan_id,
                'status' => 0,
                'type' => 2,
            ];
            $res = $cmModel->addCm($data);
            if (!$res) {
                Logger::errorLog("loan_id：" . $value->loan_id . '---插入临时表失败', 'cgerr', 'cgsendloanclaim');
                continue;
            }
            $cre_res = $this->creatExchange($value->loan_id);
            if (!$cre_res) {
                Logger::errorLog("loan_id：" . $value->loan_id . '---生成刚兑记录', 'cgerr', 'cgsendloanclaim');
                continue;
            }
            $wait_res = $value->waitRemit();
            if (!$wait_res) {
                Logger::errorLog("loan_id：" . $value->loan_id . '---更改waitremit失败', 'cgerr', 'cgsendloanclaim');
                continue;
            }
        }
    }

    /**
     * 生成纲对记录表
     * @param $loan_id
     * @return bool
     */
    private function creatExchange($loan_id) {
        $exchange = new Exchange();
        $condition = [
            'loan_id' => $loan_id,
            'exchange' => 0,
            'type' => 1,
        ];
        $ex_ret = $exchange->add_list($condition);
        if (!$ex_ret) {
            return false;
        }
        return true;
    }

}

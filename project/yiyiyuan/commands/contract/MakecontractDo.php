<?php

/**
 * 合同生成处理模型
 */

namespace app\commands\contract;

use app\models\news\Loan_contract;
use app\models\news\User_loan;
use Yii;
use yii\helpers\ArrayHelper;

class MakecontractDo {

    public function run($fund) {
        $fund = intval($fund);
        $data = (new Loan_contract())->getInitByFund($fund, 1000);

        $ids = ArrayHelper::getColumn($data, 'id');
        Loan_contract::updateAll(['status' => 'LOCK', 'last_modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);

        $count = count($data);
        $success = 0;
        $error = 0;
        foreach ($data as $k => $v) {
            $result = $this->makeContract($v);
            if ($result) {
                $res = $v->saveSuccess();
                $success++;
            } else {
                $res = $v->saveFail();
                $error++;
            }
        }
        return "all: " . $count . " success: " . $success . " error: " . $error;
    }
    /**
     * 单条合同
     * @param  [type] $loan_id [description]
     * @return [type]          [description]
     */
    public function runById($loan_id) {
        $contact = (new Loan_contract)->getByLoanId($loan_id);
        $result = $this->makeContract($contact);
        if (!$contact) {
            return false;
        }

        if ($result) {
            $res = $contact->saveSuccess();
            return true;
        } else {
            $res = $contact->saveFail();
            return false;
        }
    }
    
        /**
     * 单条合同
     * @param  [type] $loan_id [description]
     * @return [type]          [description]
     */
    public function wsmrunById($loan_id, $path) {
        $loan_id = intval($loan_id);
        $loanInfo = User_loan::findOne($loan_id);
        $fund = $loanInfo->loanextend->fund;

        $make = $this->factory($fund);
        if (!$make) {
            return false;
        }

        $tpl = $this->getTemplate($fund);

        return $make->make($loan_id, $tpl, $path);
    }

    /**
     * 生成合同
     * @param $contract
     * @return bool
     */
    private function makeContract($contract) {
        $loan_id = intval($contract->loan_id);
        $loanInfo = User_loan::findOne($loan_id);
        $fund = $loanInfo->loanextend->fund;

        $make = $this->factory($fund);
        if (!$make) {
            return false;
        }

        $tpl = $this->getTemplate($fund);

        return $make->make($loan_id, $tpl, $contract['path']);
    }
    /**
     * 获取合同的模板
     * @param  [type] $fund [description]
     * @return [type]       [description]
     */
    private function getTemplate($fund) {
        $path = Yii::$app->basePath . "/modules/newdev/views/pdf/";
        $tpls = [
            //花生米富
            '1' => $path . 'peanut.php',
            //微神马
            '6' => $path . 'wsm.php',
        ];
        return isset($tpls[$fund]) ? $tpls[$fund] : '';
    }

    private function factory($fund) {
        switch ($fund) {
        //花生米富
        case 1:
            $make = new PeanutContract;
            break;
        /*
        // 玖富
        case 2:
        $Make = new JfContract;
        break;
        // 联交所
        case 3:
        $Make = new LianjiaoContract;
        break;
        // 金联储
        case 4:
        $Make = new JinlianContract;
        break;
        // 小诺
        case 5:
        $Make = new XiaonuoContract;
        break;
        // 微神马
         */
        case 6:
        $make = new WeismContract;
        break;
        default:
            $make = null;
            break;
        }
        return $make;
    }
}

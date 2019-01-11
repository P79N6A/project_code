<?php

namespace app\commands\contract;

use app\commonapi\Logger;
use app\models\news\Loan_contract;
use app\models\news\User_loan;
use app\models\news\User_loan_flows;
use Yii;

set_time_limit(0);
ini_set('memory_limit', '-1');

class MakecontractController extends \app\commands\BaseController {

    /**
     * 获取需要生成合同的借款并存储数据
     * @return bool
     */
    public function getContract() {
        //查询2017年1月1日以后的成功的出款
        $begin_time = "2017-10-17 00:00:00";
        $now_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($begin_time)));
        $condition = [
            'AND',
            ['=', User_loan_flows::tableName() . '.loan_status', 9],
        ];
        do {
            $condition[2] = ['>=', User_loan_flows::tableName() . '.create_time', $begin_time];
            $condition[3] = ['<', User_loan_flows::tableName() . '.create_time', $end_time];
            //获取前一天出款的借款
            $total = User_loan_flows::find()->where($condition)->count();

            //每100条处理一次
            $limit = 100;
            $pages = ceil($total / $limit);

            Logger::dayLog('getContract', "\n共获取" . $total . "条数据每次处理" . $limit . "需要要处理" . $pages . "次\n");

            $success = 0;
            $error = 0;
            for ($i = 0; $i < $pages; $i++) {
                $loan_list = User_loan_flows::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
                //如果没有出款，则直接结束
                if (empty($loan_list)) {
                    break;
                }

                foreach ($loan_list as $key => $value) {
                    $loan = User_loan::findOne($value->loan_id);
                    if (!$loan) {
                        $error++;
                        continue;
                    }
                    if ($loan->status == 7) {
                        $error++;
                        continue;
                    }
                    if ($loan && in_array($loan->business_type, [1, 4, 5, 6])) {
                        $result = $this->saveContract($loan->loan_id); //存储数据
                        if (!$result) {
                            $error++;
                        } else {
                            $success++;
                        }
                    }
                }
            }
            echo "begin: " . $begin_time . "end: " . $end_time . " all: " . $total . ",success :" . $success . ",error :" . $error . "\n\r";
            $begin_time = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($begin_time)));
            $end_time = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($end_time)));
        } while ($end_time <= $now_time);
    }

    /**
     * 通过资方生成合同
     * @param $fund
     * @return bool
     */
    public function makeContractByFund($fund) {
        $makeDo = new MakecontractDo();
        $ret = $makeDo->run($fund);
        print_r($ret);
    }

    /**
     * 执行一条合同生成
     * @param $loan_id int
     * @return bool
     */
    public function makeContractById($loan_id) {
        $makeDo = new MakecontractDo();
        $ret = $makeDo->runById($loan_id);
        var_dump($ret);
    }

    private function saveContract($loan_id) {
        $loanInfo = User_loan::findOne($loan_id);
        if ($loanInfo->create_time) {
            $year = date('Y', strtotime($loanInfo->create_time));
            $month = date('m', strtotime($loanInfo->create_time));
            $day = date('d', strtotime($loanInfo->create_time));
        } else {
            $year = date('Y');
            $month = date('m');
            $day = date('d');
        }
        $contract = 'loan_' . $loanInfo->loan_no;
        $rootdir = dirname(Yii::$app->basePath);
        if (!$loanInfo->loanextend) {
            return false;
        }
        $filepath = $rootdir . '/share/pdf/' . $loanInfo->loanextend->fund . '/' . $year . '/' . $month . '/' . $day . '/' . $contract . '.pdf';
        $data = [
            'loan_id' => $loan_id,
            'fund' => $loanInfo->loanextend->fund,
            'path' => $filepath,
            'type' => 'INIT',
        ];
        $loanContractModel = new Loan_contract();
        return $loanContractModel->saveData($data);
    }

}

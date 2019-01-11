<?php

namespace app\commands;

use Yii;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\User_loan;
use app\models\news\Loan_repay;
use app\commonapi\Http;
use yii\console\Controller;

/**
 * 财务账目统计 MoneystatisticsController.php  定时任务
 */
//避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class MoneystatisticsController extends Controller {

//    public $modefy_start_date = "2017-09-01 00:00:00";
//    public $modefy_end_date = "2017-09-02 23:59:59";
    public $modefy_start_date;
    public $modefy_end_date;
    public $platform = [13, 15];

    // 命令行入口文件
    public function actionIndex($time = 9) {
        if ($time == 1) {
            $this->modefy_start_date = "2017-08-01 00:00:00";
            $this->modefy_end_date = "2017-08-02 23:59:59";
        } elseif ($time == 2) {
            $this->modefy_start_date = "2017-08-03 00:00:00";
            $this->modefy_end_date = "2017-08-08 23:59:59";
        } elseif ($time == 3) {
            $this->modefy_start_date = "2017-08-09 00:00:00";
            $this->modefy_end_date = "2017-08-10 23:59:59";
        } elseif ($time == 4) {
            $this->modefy_start_date = "2017-08-01 00:00:00";
            $this->modefy_end_date = "2017-08-10 23:59:59";
        } elseif ($time == 5) {
            $this->modefy_start_date = "2017-08-11 00:00:00";
            $this->modefy_end_date = "2017-08-20 23:59:59";
        } elseif ($time == 6) {
            $this->modefy_start_date = "2017-08-21 00:00:00";
            $this->modefy_end_date = "2017-08-31 23:59:59";
        } else {
            $this->modefy_start_date = "2017-08-01 00:00:00";
            $this->modefy_end_date = "2017-08-31 23:59:59";
        }
        //loan_id  17116588
        $limit = 500;
        foreach ($this->platform as $k => $v) {
            $cost[$v] = 0;              //成本
            $interest[$v] = 0;          //利息
            $penalty_interest[$v] = 0;  //罚息
        }
        $where_config = [
            'AND',
            ["status" => 1],
            ["platform" => $this->platform],
            ['BETWEEN', 'last_modify_time', $this->modefy_start_date, $this->modefy_end_date],
        ];

//        $list = \app\models\news\Loan_repay::find()->select('loan_id')->distinct()->where(['AND',['>=','last_modify_time','2017-07-01'],['<','last_modify_time','2017-08-01']])->all();
        $user_repay_sql = Loan_repay::find()->select('loan_id')->distinct()->where($where_config);
        $total = $user_repay_sql->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $repay_info = $user_repay_sql->offset($i * $limit)->limit($limit)->all();
            $num = $limit * $i;
            $num_end = $num + $limit;
            $this->log("共{$total}条数据:处理第{$num}条——第{$num_end}条\n");
            if (!empty($repay_info)) {
                foreach ($repay_info as $key => $value) {
                    //查询repay信息的loan信息
                    $loaninfo = User_loan::findOne($value['loan_id']);
                    $loaninfo->chase_amount = $loaninfo->getChaseamount($loaninfo->loan_id);
                    $repay_all_info = Loan_repay::find()->where(['loan_id' => $value['loan_id'], 'status' => 1])->asArray()->all();
                    if (count($repay_all_info) > 1) {
                        usort($repay_all_info, function($a, $b) {
                            if (strtotime($a['createtime']) == strtotime($b['createtime']))
                                return 0;
                            return (strtotime($a['createtime']) > strtotime($b['createtime'])) ? -1 : 1;
                        });
                    }
                    $cost_loan = bcsub($loaninfo['amount'], $loaninfo['withdraw_fee'], 4);
                    $interest_loan = bcadd($loaninfo['interest_fee'], $loaninfo['withdraw_fee'], 4);
                    $penalty_interest_loan = empty($loaninfo['chase_amount']) ? 0 : bcsub($loaninfo['chase_amount'], bcsub($cost_loan, $interest_loan, 4), 4);
                    $loan_info_static = $this->setRepayInfo($repay_all_info, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan);

                    foreach ($this->platform as $k => $v) {
                        $cost[$v] = bcadd($cost[$v], $loan_info_static[3][$v], 4);
                        $interest[$v] = bcadd($interest[$v], $loan_info_static[4][$v], 4);
                        $penalty_interest[$v] = bcadd($penalty_interest[$v], $loan_info_static[5][$v], 4);
                    }
                }
            }
        }
        $date_time = date("Y-m-d H:i:s");
        foreach ($this->platform as $k => $v) {
            Logger::dayLog('Monrystatistics', "渠道{$this->platform[$k]},成本{$cost[$v]},利息{$interest[$v]},逾期罚息{$penalty_interest[$v]}");
            $this->log("渠道{$this->platform[$k]},成本{$cost[$v]},利息{$interest[$v]},逾期罚息{$penalty_interest[$v]}");
        }
    }

    private function setRepayInfo($repay_all_info, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan) {
        if (empty($repay_all_info) || !is_array($repay_all_info) || empty($loaninfo)) {
            return FALSE;
        }
        foreach ($this->platform as $k => $v) {
            $cost_new[$v] = 0;
            $interest_new[$v] = 0;
            $penalty_interest_new[$v] = 0;
        }
        foreach ($repay_all_info as $v) {
            if ((strtotime($v['last_modify_time']) < strtotime($this->modefy_start_date))) {//统计日期前的比较
                $befor_info_arr = $this->beforeTimeStatistics($v, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan);
                $cost_loan = $befor_info_arr[0];
                $interest_loan = $befor_info_arr[1];
                $penalty_interest_loan = $befor_info_arr[2];
                $b[] = $befor_info_arr;
            } elseif ((strtotime($v['last_modify_time']) >= strtotime($this->modefy_start_date)) && strtotime($v['last_modify_time']) < strtotime($this->modefy_end_date)) {//统计日期前的比较
                $in_info_arr = $this->inTimeStatistics($v, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new);
                $cost_loan = $in_info_arr[0];
                $interest_loan = $in_info_arr[1];
                $penalty_interest_loan = $in_info_arr[2];
                $cost_new = $in_info_arr[3];
                $interest_new = $in_info_arr[4];
                $penalty_interest_new = $in_info_arr[5];
            }
        }
        return [$cost_loan, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
    }

    /*
     * 订单在指定日期前的还款计算
     */

    private function beforeTimeStatistics($repayinfo, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan) {
        if ($cost_loan >= $repayinfo['actual_money']) {//还款小于等于本金
            $cost_loan = bcsub($cost_loan, $repayinfo['actual_money'], 4);
            return [$cost_loan, $interest_loan, $penalty_interest_loan];
        } else {
            if (($cost_loan < $repayinfo['actual_money']) && (bcsub($repayinfo['actual_money'], $cost_loan, 4) <= $interest_loan)) {//还款大于本金并且小于等于利息
                return [0, bcsub($interest_loan, bcsub($repayinfo['actual_money'], $cost_loan, 4), 4), $penalty_interest_loan];
            } else {//还款大于本金并且大于利息计入罚息，如果超出则归为0
                $penalty_interest_loan = bcsub($penalty_interest_loan, bcsub(bcsub($repayinfo['actual_money'], $cost_loan, 4), $interest_loan, 4), 4);
                return [0, 0, $penalty_interest_loan];
            }
        }
    }

    /*
     * 订单在指定日期间的还款计算
     */

    private function inTimeStatistics($repayinfo, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new) {

        if ($cost_loan >= $repayinfo['actual_money']) {//还款小于等于本金
            $cost_loan = bcsub($cost_loan, $repayinfo['actual_money'], 4);
            if (in_array($repayinfo['platform'], $this->platform)) {
                $cost_new[$repayinfo['platform']] = bcadd($cost_new[$repayinfo['platform']], $repayinfo['actual_money'], 4);
            }
            return [$cost_loan, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
        } else {
            if (($cost_loan < $repayinfo['actual_money']) && (bcsub($repayinfo['actual_money'], $cost_loan, 4) <= $interest_loan)) {//还款大于本金并且小于等于利息
                $interest_loan = bcsub($interest_loan, bcsub($repayinfo['actual_money'], $cost_loan, 4), 4);
                if (in_array($repayinfo['platform'], $this->platform)) {
                    $cost_new[$repayinfo['platform']] = bcadd($cost_new[$repayinfo['platform']], $cost_loan, 4);
                    $interest_new[$repayinfo['platform']] = bcadd($interest_new[$repayinfo['platform']], bcsub($repayinfo['actual_money'], $cost_loan, 4), 4);
                }

                return [0, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
            } else {//还款大于本金并且大于利息计入罚息，如果超出则归为0
//                $penalty_interest_loan = $penalty_interest_loan - ($repayinfo['actual_money'] - $cost_loan - $interest_loan);
                //原利息未清，还款大于剩余原利息，在统计通道内
                //本金归0
                //利息归0
                //罚息==
                $repayinfo['actual_money'] = bcsub(bcsub($repayinfo['actual_money'], $cost_loan, 4), $interest_loan, 4);
                if (in_array($repayinfo['platform'], $this->platform)) {
                    $cost_new[$repayinfo['platform']] = bcadd($cost_new[$repayinfo['platform']], $cost_loan, 4);
                    $interest_new[$repayinfo['platform']] = bcadd($interest_new[$repayinfo['platform']], $interest_loan, 4);
                    $penalty_interest_new[$repayinfo['platform']] = bcadd($penalty_interest_new[$repayinfo['platform']], $repayinfo['actual_money'], 4);
                }
                $penalty_interest_loan = bcsub($penalty_interest_loan, $repayinfo['actual_money'], 4);

                if ($penalty_interest_loan < 0) {
                    $penalty_interest_loan = 0;
                }
                return [0, 0, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
//                if ($interest_loan != 0 && $repayinfo['actual_money'] >= $interest_loan && in_array($repayinfo['platform'], $this->platform)) {
//                    $repayinfo['actual_money'] = $repayinfo['actual_money'] - $interest_loan;
//                    $interest_new[$repayinfo['platform']] += $interest_loan;
//                    $interest_loan = 0; //计算结束归0
//                    $penalty_interest_loan = $penalty_interest_loan - $repayinfo['actual_money'];
//                    $penalty_interest_loan_tmp = $penalty_interest_loan;
//                    if ($penalty_interest_loan < 0) {
//                        $penalty_interest_loan = 0;
//                    }
//                    $penalty_interest_new[$repayinfo['platform']] += abs($penalty_interest_loan_tmp);
//                    $cost_new[$repayinfo['platform']] += $cost_loan;
//                } elseif ($interest_loan != 0 && $repayinfo['actual_money'] < $interest_loan && in_array($repayinfo['platform'], $this->platform)) {
//                    //原利息未清，还款小于剩余原利息，在统计通道内
//                    $interest_loan = $interest_loan - $repayinfo['actual_money'];
//                } elseif ($interest_loan == 0 && in_array($repayinfo['platform'], $this->platform)) {
//                    //原利息已结清，在通道内
//                    $penalty_interest_new[$repayinfo['platform']] += $repayinfo['actual_money'];
//                } elseif ($interest_loan != 0 && $repayinfo['actual_money'] >= $interest_loan) {
//                    //原利息未清，还款大于剩余原利息，不在统计通道内
//                    $penalty_interest_loan = $penalty_interest_loan - ($repayinfo['actual_money'] - $interest_loan);
//                    if ($penalty_interest_loan < 0) {
//                        $penalty_interest_loan = 0;
//                    }
//                    $interest_loan = 0;
//                } elseif ($interest_loan != 0 && $repayinfo['actual_money'] < $interest_loan) {
//                    //原利息未清，还款小于剩余原利息，不在统计通道内
//                    $interest_loan = $interest_loan - $repayinfo['actual_money'];
//                }
//                return [0, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

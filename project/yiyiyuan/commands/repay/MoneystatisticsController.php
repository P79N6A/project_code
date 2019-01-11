<?php

namespace app\commands\repay;

use app\commonapi\Logger;
use app\models\onlyread\Loan_repay;
use app\models\onlyread\User_loan;
use app\models\onlyread\User_remit_list;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

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
    public $platform = 17;

    // 命令行入口文件
    public function actionIndex($start_time = '2017-08-01', $end_time = "2017-12-30", $pl = 25) {
        $this->modefy_start_date = $start_time;
        $this->modefy_end_date = $end_time;
        $this->platform = $pl;
        //loan_id  17116588
        $limit = 500;
        $benjin['xianhua'] = 0;              //成本
        $lixi['xianhua'] = 0;          //利息
        $faxi['xianhua'] = 0;  //罚息
        $benjin['xiaoxiao'] = 0;              //成本
        $lixi['xiaoxiao'] = 0;          //利息
        $faxi['xiaoxiao'] = 0;  //罚息
        $where_config = [
            'AND',
            [Loan_repay::tableName() . ".status" => 1],
            [Loan_repay::tableName() . ".platform" => $this->platform],
            ['BETWEEN', Loan_repay::tableName() . '.last_modify_time', $this->modefy_start_date, $this->modefy_end_date],
            [User_loan::tableName() . ".status" => [4, 8, 9, 11, 12, 13]],
        ];

        $par_loan = User_loan::find()->select('parent_loan_id')->distinct()->joinWith('repay', TRUE, 'LEFT JOIN')->where($where_config);
        echo $total = $par_loan->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $repay_info = $par_loan->offset($i * $limit)->limit($limit)->orderBy(Loan_repay::tableName() . '.last_modify_time ASC')->all();
            $num = $limit * $i;
            $num_end = $num + $limit;
            $this->log("共{$total}条数据:处理第{$num}条——第{$num_end}条\n");
            if (!empty($repay_info)) {
                foreach ($repay_info as $key => $value) {
                    //查询repay信息的loan信息
                    $loan_all = User_loan::find()->where(['parent_loan_id' => $value['parent_loan_id']])->orderBy('loan_id desc')->all();
                    $loaninfo = end($loan_all);
                    $loaninfo->chase_amount = $loaninfo->getChaseamount($loaninfo->loan_id);
                    $loan_ids = ArrayHelper::getColumn($loan_all, 'loan_id');

                    $repay_all_info = Loan_repay::find()->where(['loan_id' => $loan_ids, 'status' => 1])->asArray()->all();
                    $outmoney = User_remit_list::find()->where(['loan_id' => $value['parent_loan_id'], 'remit_status' => 'SUCCESS'])->one();
                    $channelId = in_array($outmoney->fund, [1, 10]) ? 'xiaoxiao' : 'xianhua';
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
                    $benjin[$channelId] = bcadd($benjin[$channelId], $loan_info_static[3], 4);
                    $lixi[$channelId] = bcadd($lixi[$channelId], $loan_info_static[4], 4);
                    $faxi[$channelId] = bcadd($faxi[$channelId], $loan_info_static[5], 4);
                }
            }
        }
        Logger::dayLog('Monrystatistics', $this->modefy_start_date, $this->modefy_end_date, "小小渠道{$this->platform},成本{$benjin['xiaoxiao']},利息{$lixi['xiaoxiao']},逾期罚息{$faxi['xiaoxiao']}");
        Logger::dayLog('Monrystatistics', $this->modefy_start_date, $this->modefy_end_date, "先花渠道{$this->platform},成本{$benjin['xianhua']},利息{$lixi['xianhua']},逾期罚息{$faxi['xianhua']}");
        $this->log("小小渠道{$this->platform},成本{$benjin['xiaoxiao']},利息{$lixi['xiaoxiao']},逾期罚息{$faxi['xiaoxiao']}");
        $this->log("先花渠道{$this->platform},成本{$benjin['xianhua']},利息{$lixi['xianhua']},逾期罚息{$faxi['xianhua']}");
    }

    private function setRepayInfo($repay_all_info, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan) {
        if (empty($repay_all_info) || !is_array($repay_all_info) || empty($loaninfo)) {
            return FALSE;
        }
        $benjin = 0;              //成本
        $lixi = 0;          //利息
        $faxi = 0;  //罚息
        foreach ($repay_all_info as $v) {
            if ((strtotime($v['last_modify_time']) < strtotime($this->modefy_start_date))) {//统计日期前的比较
                $befor_info_arr = $this->beforeTimeStatistics($v, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan);
                $cost_loan = $befor_info_arr[0];
                $interest_loan = $befor_info_arr[1];
                $penalty_interest_loan = $befor_info_arr[2];
            } elseif ((strtotime($v['last_modify_time']) >= strtotime($this->modefy_start_date)) && strtotime($v['last_modify_time']) < strtotime($this->modefy_end_date)) {//统计日期前的比较
                $in_info_arr = $this->inTimeStatistics($v, $loaninfo, $cost_loan, $interest_loan, $penalty_interest_loan, $benjin, $lixi, $faxi);
                $cost_loan = $in_info_arr[0];
                $interest_loan = $in_info_arr[1];
                $penalty_interest_loan = $in_info_arr[2];
                $benjin = $in_info_arr[3];
                $lixi = $in_info_arr[4];
                $faxi = $in_info_arr[5];
            }
        }
        return [$cost_loan, $interest_loan, $penalty_interest_loan, $benjin, $lixi, $faxi];
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
            if ($repayinfo['platform'] == $this->platform) {
                $cost_new = bcadd($cost_new, $repayinfo['actual_money'], 4);
            }
            return [$cost_loan, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
        } else {
            if (($cost_loan < $repayinfo['actual_money']) && (bcsub($repayinfo['actual_money'], $cost_loan, 4) <= $interest_loan)) {//还款大于本金并且小于等于利息
                $interest_loan = bcsub($interest_loan, bcsub($repayinfo['actual_money'], $cost_loan, 4), 4);
                if ($repayinfo['platform'] == $this->platform) {
                    $cost_new = bcadd($cost_new, $cost_loan, 4);
                    $interest_new = bcadd($interest_new, bcsub($repayinfo['actual_money'], $cost_loan, 4), 4);
                }
                return [0, $interest_loan, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
            } else {
                $repayinfo['actual_money'] = bcsub(bcsub($repayinfo['actual_money'], $cost_loan, 4), $interest_loan, 4);
                if ($repayinfo['platform'] == $this->platform) {
                    $cost_new = bcadd($cost_new, $cost_loan, 4);
                    $interest_new = bcadd($interest_new, $interest_loan, 4);
                    $penalty_interest_new = bcadd($penalty_interest_new, $repayinfo['actual_money'], 4);
                }
                $penalty_interest_loan = bcsub($penalty_interest_loan, $repayinfo['actual_money'], 4);

                if ($penalty_interest_loan < 0) {
                    $penalty_interest_loan = 0;
                }
                return [0, 0, $penalty_interest_loan, $cost_new, $interest_new, $penalty_interest_new];
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

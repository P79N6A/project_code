<?php

/**
 *  推送体制内的还款时间（前五天）
 *  linux : /data/wwwroot/yiyiyuan/yii claim/sendloanrepay
 *  window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii claim/sendloanrepay
 */

namespace app\commands\claim;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Cm_loans;
use app\models\news\Exchange;
use app\models\news\RepayTime;
use app\models\news\User_loan;
use app\commands\BaseController;
use yii\helpers\ArrayHelper;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendloanrepayController extends BaseController {

    private $limit = 100;
    private $repayTimeModel = null;

    public function actionIndex() {
        $this->repayTimeModel = new RepayTime();
        $successNum = 0;
        $countNum = 0;
        $start_date = date('Y-m-d 00:00:00', strtotime('-5 days'));
        $end_date = date('Y-m-d 00:00:00');
//        $start_date = '2017-11-01 00:00:00';
//        $end_date = date('Y-m-d 00:00:00');
        $where = [
            'AND',
            ['>=', User_loan::tableName() . '.repay_time', $start_date],
            ['<', User_loan::tableName() . '.repay_time', $end_date],
            [User_loan::tableName() . '.status' => 8],
            [User_loan::tableName() . '.settle_type' => [0, 1, 3]],
            [RepayTime::tableName() . '.status' => null]
        ];
        $sql = User_loan::find()->joinWith('repayTime', 'TRUE', 'LEFT JOIN')->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $data = $sql->limit($this->limit)->all();
            if (empty($data)) {
                break;
            }
            $count = count($data);
            $countNum += $count;
            $lists = $this->doArray($data);
            $result = $this->doSend($lists);
            if ($result) {
                $successNum += $result;
            }
        }
        Logger::dayLog('claim/sendloanrepay', date('Y-m-d H:i:s'), '需推送总数：' . $countNum, '成功：' . $successNum);
        exit('success:' . $successNum . ';count:' . $countNum);
    }

    private function addRepayTime($ids, $count) {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        $array = [];
        $date = date('Y-m-d H:i:s');
        foreach ($ids as $id) {
            $array[] = [
                0 => $id, //loan_id
                1 => 1, //status
                2 => $date, //last_modify_time
                3 => $date, //create_time
                4 => 0, //version
            ];
        }
        $transaction = Yii::$app->db->beginTransaction();
        $num = $this->repayTimeModel->batchAddRecord($array);
        if ($num != $count) {
            $transaction->rollBack();
            Logger::dayLog('claim/sendloanrepay', '批量添加repay_time表失败', $ids);
            return false;
        }
        $transaction->commit();
    }

    private function doArray($userLoanData) {
        $list = [];
        $i = 0;
        if (empty($userLoanData)) {
            return $list;
        }
        foreach ($userLoanData as $key => $value) {
            if (empty($value) || !is_object($value)) {
                break;
            }
            $loans = User_loan::find()->where(['parent_loan_id' => $value->parent_loan_id])->all();
            foreach ($loans as $val) {
                $isOverdue = $val->repay_time > $val->end_date ? 2 : 1;
                $list[$i]['loan_id'] = $val->loan_id;
                $list[$i]['repay_time'] = $val->repay_time; //还款时间
                $list[$i]['start_date'] = $val->start_date; //计息时间
                $list[$i]['end_date'] = $val->end_date; //到期时间
                $list[$i]['duration'] = $val->days; //借款天数
                $list[$i]['is_overdue'] = $isOverdue; //是否逾期
                $list[$i]['source'] = 1; //来源      
                $i++;
            }
        }
        return $list;
    }

    private function doSend($lists) {
        if (empty($lists) || !is_array($lists)) {
            return 0;
        }
        $apiDep = new Apidepository();
        $ret = $apiDep->postRepayTime($lists);
        if (empty($ret)) {
            return 0;
        }
        $ids = ArrayHelper::getColumn($lists, 'loan_id');
        if (!empty($ret['fail_loan'])) {
            Logger::dayLog('claim/sendloanrepay', date('Y-m-d H:i:s'), '失败loan_id：', $ret['fail_loan']);
            $ids = array_diff($ids, $ret['fail_loan']);
        }
        $repayTimeCount = count($ids);
        $result = $this->addRepayTime($ids, $repayTimeCount);
        if ($result === false) {
            Logger::dayLog('claim/sendloanrepay', '批量添加repay_time表失败', $ids);
        }
        return isset($ret['success_num']) ? $ret['success_num'] : 0;
    }

}

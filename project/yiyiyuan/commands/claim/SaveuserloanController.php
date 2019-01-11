<?php
namespace app\commands\claim;

use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\PushUserLoan;
use app\models\news\User_loan;

class SaveuserloanController extends BaseController {
    private $limit = 500;
    private $debug = false;

    //记录在贷记录
    public function actionInloan() {
        $this->doInLoan();
    }

    //记录结清记录
    public function actionRepayment() {
        $this->doRepayment();
    }

    private function doInLoan() {
        $countNum = 0;
        $successNum = 0;
        $start_date = date('Y-m-d 00:00:00', strtotime('-1 days'));
        $end_date = date('Y-m-d 00:00:00');
        $where = [
            'AND',
            ['>=', User_loan::tableName() . '.last_modify_time', $start_date],
            ['<', User_loan::tableName() . '.last_modify_time', $end_date],
            [User_loan::tableName() . '.status' => [9, 11, 12, 13]],
            [User_loan::tableName() . '.settle_type' => 0],
            [PushUserLoan::tableName() . '.id' => NULL]
        ];
        $sql = (new User_loan())->find()->joinWith('pushuserloan', 'TRUE', 'LEFT JOIN')->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            if ($this->debug && $i > 0) {
                break;
            }
            $o_user_loan = $sql->limit($this->limit)->all();
            if (empty($o_user_loan)) {
                break;
            }
            $countNum += count($o_user_loan);
            $result = $this->doInsertBatch($o_user_loan);
            if (!$result) {
                continue;
            }
            $successNum += $result;
        }
        Logger::dayLog('script/claim/saveuserloan', date('Y-m-d H:i:s'), '在贷需处理总数：' . $countNum, '成功：' . $successNum);
        exit('count:' . $countNum . ';success:' . $successNum);
    }

    private function doInsertBatch($o_user_loan) {
        if (empty($o_user_loan)) {
            return false;
        }
        $list = [];
        $time = date('Y-m-d H:i:s');
        foreach ($o_user_loan as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $list[$key] = [
                'user_id' => $value->user_id,
                'loan_id' => $value->parent_loan_id,
                'amount' => $value->amount,
                'status' => 1,
                'send_status' => 0,
                'last_modify_time' => $time,
                'create_time' => $time,
            ];
        }
        try {
            $result = (new PushUserLoan())->insertBatch($list);
        } catch (\Exception $e) {
            Logger::dayLog('script/claim/saveuserloan', '在贷记录_保存记录失败' . $e->getMessage(), $list);
            $result = 0;
        }
        if ($result > 0 && count($o_user_loan) == $result) {
            return $result;
        }
        return $result;
    }

    private function doRepayment() {
        $countNum = 0;
        $successNum = 0;
        $start_date = date('Y-m-d 00:00:00', strtotime('-1 days'));
        $end_date = date('Y-m-d 00:00:00');
        $where = [
            'AND',
            ['>=', User_loan::tableName() . '.last_modify_time', $start_date],
            ['<', User_loan::tableName() . '.last_modify_time', $end_date],
            [User_loan::tableName() . '.status' => 8],
            [User_loan::tableName() . '.settle_type' => [0, 1, 3]]
        ];
        $sql = (new User_loan())->find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            if ($this->debug && $i > 0) {
                break;
            }
            $o_user_loan = $sql->offset($i * $this->limit)->limit($this->limit)->all();
            if (empty($o_user_loan)) {
                break;
            }
            $countNum += count($o_user_loan);
            foreach ($o_user_loan as $item) {
                $result = $this->doRepaymentSave($item);
                if (!$result) {
                    continue;
                }
                $successNum++;
            }
        }
        Logger::dayLog('script/claim/saveuserloan', date('Y-m-d H:i:s'), '结清需处理总数：' . $countNum, '成功：' . $successNum);
        exit('count:' . $countNum . ';success:' . $successNum);
    }

    private function doRepaymentSave($o_user_loan) {
        if (empty($o_user_loan) || !is_object($o_user_loan)) {
            return false;
        }
        $m_push_user_loan = new PushUserLoan();
        $o_push_user_loan = $m_push_user_loan->getByLoanId($o_user_loan->parent_loan_id);
        if (empty($o_push_user_loan)) {
            $data = [
                'user_id' => $o_user_loan->user_id,
                'loan_id' => $o_user_loan->parent_loan_id,
                'amount' => $o_user_loan->amount,
                'status' => 2,
                'send_status' => 0
            ];
            $result = $m_push_user_loan->addRecord($data);
            if (empty($result)) {
                Logger::dayLog('script/claim/saveuserloan', '结清记录_保存记录失败' . $o_user_loan->loan_id);
                return false;
            }
            return true;
        } else {
            if ($o_push_user_loan->status == 1) {
                $data = [
                    'status' => 2,
                    'send_status' => 0
                ];
                $result = $o_push_user_loan->updateRecord($data);
                if (empty($result)) {
                    Logger::dayLog('script/claim/saveuserloan', '结清记录_更新记录失败' . $o_user_loan->loan_id);
                    return false;
                }
                return true;
            }
            Logger::dayLog('script/claim/saveuserloan', '结清记录_无操作' . $o_user_loan->loan_id);
            return false;
        }
    }
}

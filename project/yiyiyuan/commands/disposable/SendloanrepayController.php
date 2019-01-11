<?php
/**
 *  推送体制内的还款时间
 *  linux : /data/wwwroot/yiyiyuan/yii disposable/sendloanrepay
 *  window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii disposable/sendloanrepay
 */
namespace app\commands\disposable;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Cm_loans;
use app\models\news\Loans;
use app\models\news\User_loan;
use app\commands\BaseController;
use yii\helpers\ArrayHelper;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendloanrepayController extends BaseController
{
    private $limit = 200;
    private $cmLoansModel = null;

    public function actionIndex()
    {
        $this->cmLoansModel = new Cm_loans();
        $countNum = 0;
        $successNum = 0;
        $where = [
            'AND',
            [Cm_loans::tableName() . '.id' => NULL],
            ['>', Loans::tableName() . '.loan_id', 150000],
            [User_loan::tableName() . '.status' => 8],
            [User_loan::tableName() . '.settle_type' => 0]
        ];
        $sql = Loans::find()->joinWith('userloan', 'TRUE', 'LEFT JOIN')->joinWith('cmloans', 'TRUE', 'LEFT JOIN')->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $data = $sql->limit($this->limit)->all();
            if (empty($data)) {
                break;
            }
            $count = count($data);
            $countNum += $count;
            $ids = ArrayHelper::getColumn($data, 'loan_id');
            $result = $this->addCmLoans($ids, $count);
            if ($result === false) {
                break;
            }
            $lists = $this->doArray($data);
            $result = $this->doSend($lists, $data);
            if ($result) {
                $successNum += $result;
            }
        }
        Logger::dayLog('disposable/sendloanrepay', date('Y-m-d H:i:s'), '需推送总数：' . $countNum, '成功：' . $successNum);
        exit('success:' . $successNum . ';count:' . $countNum);
    }

    private function doArray($userLoanData)
    {
        $list = [];
        if (empty($userLoanData)) {
            return $list;
        }
        foreach ($userLoanData as $key => $value) {
            if (empty($value) || !is_object($value)) {
                break;
            }
            $isOverdue = $value->userloan->repay_time > $value->userloan->end_date ? 2 : 1;
            $list[$key]['loan_id'] = $value->loan_id;
            $list[$key]['repay_time'] = $value->userloan->repay_time;//还款时间
            $list[$key]['start_date'] = $value->userloan->start_date;//计息时间
            $list[$key]['end_date'] = $value->userloan->end_date;//到期时间
            $list[$key]['duration'] = $value->userloan->days;//借款天数
            $list[$key]['is_overdue'] = $isOverdue;//是否逾期
            $list[$key]['source'] = 1;//来源
        }
        return $list;
    }

    private function doSend($lists)
    {
        if (empty($lists) || !is_array($lists)) {
            return 0;
        }
        $apiDep = new Apidepository();
        $ret = $apiDep->postRepayTime($lists);
        if (empty($ret)) {
            return 0;
        }
        if (!empty($ret['fail_loan'])) {
            Logger::dayLog('disposable/sendloanrepay', date('Y-m-d H:i:s'), '失败loan_id：', $ret['fail_loan']);
        }
        return isset($ret['success_num']) ? $ret['success_num'] : 0;
    }

    private function addCmLoans($ids, $count)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        $array = [];
        $date = date('Y-m-d H:i:s');
        foreach ($ids as $id) {
            $array[] = [
                0 => $id,//loan_id
                1 => 2,//status
                2 => $date,//create_time
                3 => 2,//type
                4 => 0,//version
                5 => $date,//last_modify_time
            ];
        }
        $transaction = Yii::$app->db->beginTransaction();
        $num = $this->cmLoansModel->batchAddCmLoans($array);
        if ($num != $count) {
            $transaction->rollBack();
            Logger::dayLog('disposable/sendloanrepay', '批量添加cm_loans表失败', $ids);
            return false;
        }
        $transaction->commit();
    }
}

<?php

namespace app\commands\disposable;

use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Renew_amount;
use app\models\news\RenewalInspect;
use app\models\news\User_loan;
use app\commands\BaseController;
use yii\helpers\ArrayHelper;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RenewalinspectController extends BaseController
{
    private $limit = 200;

    public function actionIndex()
    {
        $successNum = 0;
        $date = date('Y-m-d H:i:00', strtotime("-5 minute"));
        $where = [
            'AND',
            ['status' => 0],
            ['<', 'create_time', $date]
        ];
        $sql = (new RenewalInspect())->find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $data = $sql->limit($this->limit)->all();
            if (empty($data)) {
                break;
            }
            $ids = ArrayHelper::getColumn($data, 'id');
            (new RenewalInspect())->batchLock($ids);
            foreach ($data as $item) {
                $result = $this->doRenewal($item);
                if ($result) {
                    $successNum++;
                }
            }

        }
        Logger::dayLog('script/disposable/renewal_inspect', '需推送总数：' . $total, '成功：' . $successNum);
        exit('count:' . $total . ';success:' . $successNum);
    }

    private function doRenewal($o_renewal_inspect)
    {
        if (empty($o_renewal_inspect)) {
            return FALSE;
        }
        $lock_res = $o_renewal_inspect->updateLock();
        if (!$lock_res) {
            Logger::dayLog('script/disposable/renewal_inspect', '加锁失败', $o_renewal_inspect->id);
            return FALSE;
        }
        if (Keywords::renewalInspectOpen() != 2) {
            Logger::dayLog('script/disposable/renewal_inspect', '合规展期开关关闭', $o_renewal_inspect->id);
            $this->doFail($o_renewal_inspect);
            return FALSE;
        }
        $o_renew_amount = (new Renew_amount())->getRenewOne($o_renewal_inspect->loan_id);
        if (empty($o_renew_amount) || $o_renew_amount->mark == 3) {
            Logger::dayLog('script/disposable/renewal_inspect', '合规展期无资格', $o_renewal_inspect->id);
            $this->doFail($o_renewal_inspect);
            return FALSE;
        }
        $o_user_loan = (new User_loan())->getById($o_renewal_inspect->loan_id);
        $tiem = date('Y-m-d H:i:s');
        $time_in = date("Y-m-d H:i:s", strtotime("-5 day", strtotime($o_user_loan->end_date)));
        $over_time_in = date("Y-m-d H:i:s", strtotime("+3 day", strtotime($o_user_loan->end_date)));
        if ($o_renew_amount->mark == 1 && ($tiem < $time_in || $tiem > $over_time_in)) {
            Logger::dayLog('script/disposable/renewal_inspect', '合规展期时间不符合', $o_renewal_inspect->id);
            $this->doFail($o_renewal_inspect);
            return FALSE;
        }
        if ($o_user_loan->status == 8 || in_array($o_renewal_inspect->status, [1, 2])) {
            Logger::dayLog('script/disposable/renewal_inspect', '展期失败，数据不正确', $o_renewal_inspect->id, $o_user_loan->status, $o_renewal_inspect->status);
            $this->doFail($o_renewal_inspect);
            return FALSE;
        }
        $transaction = Yii::$app->db->beginTransaction();
        $result = (new RenewalInspect())->createInspectloan($o_user_loan, $o_renew_amount, $o_renewal_inspect);
        if ($result) {
            $transaction->commit();
            return TRUE;
        }
        $transaction->rollBack();
        return FALSE;
    }

    private function doFail($o_renewal_inspect)
    {
        $o_renewal_inspect->refresh();
        $o_renewal_inspect->updateFail();
    }
}

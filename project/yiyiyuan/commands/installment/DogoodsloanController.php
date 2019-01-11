<?php
namespace app\commands\installment;

/**
 * 同步借款成功&失败数据至goodsloan
 * linux : sudo -u www /data/wwwroot/yiyiyuan/yii installment/dogoodsloan
 * windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii installment/dogoodsloan
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\GoodsLoan;
use app\models\news\RemitSuccessList;
use app\models\news\User_loan;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class DogoodsloanController extends BaseController
{
    private $failLimit = 100;

    public function actionDofail()
    {
        $failNum = 0;
        //处理失败数据
        $start_date = date('Y-m-d H:i:00', strtotime('-5 minutes'));
        $end_date = date('Y-m-d H:i:00');
        $where = [
            'AND',
            ['>=', User_loan::tableName() . '.last_modify_time', $start_date],
            ['<', User_loan::tableName() . '.last_modify_time', $end_date],
            [User_loan::tableName() . '.business_type' => [5, 6]],
            [User_loan::tableName() . '.status' => '7']
        ];
        $sql = User_loan::find()->where($where);
        $failTotal = $sql->count();
        $pages = ceil($failTotal / $this->failLimit);

        for ($i = 0; $i < $pages; $i++) {
            $userLoan = $sql->offset($i * $this->failLimit)->limit($this->failLimit)->all();
            if (empty($userLoan)) {
                break;
            }
            foreach ($userLoan as $key => $value) {
                $result = $this->addFailGoodsLoan($value->loan_id);
                if (!$result) {
                    continue;
                }
                $failNum++;
            }
        }
        Logger::dayLog('installment/dogoodsloan', date('Y-m-d H:i:s'), '失败/驳回需处理总数：' . $failTotal, '失败/驳回：' . $failNum);
    }

    //新增失败记录
    private function addFailGoodsLoan($loanId)
    {
        $result = (new GoodsLoan())->addGoodsLoan($loanId, 2);
        if (!$result) {
            Logger::dayLog('installment/dogoodsloan', '新增goodsloan表失败', $loanId, 2);
            return false;
        }
        return true;
    }
}

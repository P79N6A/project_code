<?php
namespace app\commands\installment;

/**
 * 同步分期账单表记录
 * linux : sudo -u www /data/wwwroot/yiyiyuan/yii installment/dogoodsbill
 * windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii installment/dogoodsbill
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\GoodsLoan;
use app\models\news\GoodsOrder;
use app\models\service\GoodsService;
use yii\helpers\ArrayHelper;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class DogoodsbillController extends BaseController
{
    private $limit = 200;

    public function actionIndex()
    {
        $successNum = 0;

        $goodsLoanModel = new GoodsLoan();
        $goodsLoan = $goodsLoanModel->listInit($this->limit);

        $ids = ArrayHelper::getColumn($goodsLoan, 'id');
        $goodsLoanModel->updateAllLock($ids);

        foreach ($goodsLoan as $key => $value) {
            $result = $this->doDistribute($value);
            if (!$result) {
                continue;
            }
            $successNum++;
        }
        Logger::dayLog('installment/dogoodsbill', date('Y-m-d H:i:s'), '成功需处理总数：' . $this->limit, '成功：' . $successNum);
    }

    //状态分发
    private function doDistribute($goodsLoanObj)
    {
        $info = false;
        if ($goodsLoanObj->loan_status == '1') {
            $info = $this->doSuccess($goodsLoanObj);
        } elseif ($goodsLoanObj->loan_status == '2') {
            $info = $this->doFail($goodsLoanObj);
        }
        return $info;
    }

    //处理成功
    private function doSuccess($goodsLoanObj)
    {
        $goodsOrderObj = (new GoodsOrder())->getLoanByLoanId($goodsLoanObj->loan_id);
        if (empty($goodsOrderObj)) {
            return false;
        }
        $lockInfo = $goodsOrderObj->updateLock();
        if (!$lockInfo) {
            Logger::dayLog('installment/dogoodsbill', '更新LOCK失败', $goodsOrderObj->id);
            return false;
        }
        $goodsService = new GoodsService();
        $successInfo = $goodsService->addGoodsBill($goodsOrderObj);
        if (!$successInfo) {
            Logger::dayLog('installment/dogoodsbill', '新增goodsBill失败', $goodsOrderObj->id);
            return false;
        }
        $loanSuccessInfo = $goodsLoanObj->updateSuccess();
        if (!$loanSuccessInfo) {
            Logger::dayLog('installment/dogoodsbill', '更新goods_loan表success状态失败', $goodsOrderObj->id);
            return false;
        }
        return true;
    }

    //处理失败
    private function doFail($goodsLoanObj)
    {
        $goodsOrderObj = (new GoodsOrder())->getLoanByLoanId($goodsLoanObj->loan_id);
        if (empty($goodsOrderObj)) {
            return false;
        }
        $lockInfo = $goodsOrderObj->updateLock();
        if (!$lockInfo) {
            Logger::dayLog('installment/dogoodsbill', '更新LOCK失败', $goodsOrderObj->id);
            return false;
        }
        $failInfo = $goodsOrderObj->updateFail();
        if (!$failInfo) {
            Logger::dayLog('installment/dogoodsbill', '更新goods_order表fail状态失败', $goodsOrderObj->id);
            return false;
        }
        $loanFailInfo = $goodsLoanObj->updateSuccess();
        if (!$loanFailInfo) {
            Logger::dayLog('installment/dogoodsbill', '更新goods_loan表success状态失败', $goodsOrderObj->id);
            return false;
        }
        return true;
    }
}

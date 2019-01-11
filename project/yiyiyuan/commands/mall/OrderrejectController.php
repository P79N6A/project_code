<?php
namespace app\commands\mall;

/**
 * 驳回订单
 * linux : sudo -u www /data/wwwroot/yiyiyuan/yii mall/orderreject
 * windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii mall/orderreject
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\Goods_order_terms;
use yii\helpers\ArrayHelper;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class OrderrejectController extends BaseController
{
    private $limit = 200;

    public function actionIndex()
    {
        $successNum = 0;
        $goodsOrderModel = new Goods_order_terms();
        $datas = $goodsOrderModel->getInitData($this->limit);
        $ids = ArrayHelper::getColumn($datas, 'id');
        $goodsOrderModel->updateAllLock($ids);

        foreach ($datas as $key => $value) {
            $resultLock = $value->lock();
            if (!$resultLock) {
                Logger::dayLog('mall/orderrejectErr', date('Y-m-d H:i:s'), '锁定失败：' . $value->id);
                continue;
            }
            $resultReject = $value->reject();
            if (!$resultReject) {
                Logger::dayLog('mall/orderrejectErr', date('Y-m-d H:i:s'), '驳回失败：' . $value->id);
                continue;
            }
            $successNum++;
        }
        echo date('Y-m-d H:i:s'). '成功需处理总数：' . count($datas), '成功：' . $successNum;
    }
}

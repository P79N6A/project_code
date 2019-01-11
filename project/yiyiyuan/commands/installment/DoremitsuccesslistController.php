<?php
namespace app\commands\installment;

/**
 * 同步借款成功数据至remit_success_list表
 * linux : sudo -u www /data/wwwroot/yiyiyuan/yii installment/doremitsuccesslist
 * windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii installment/doremitsuccesslist
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\RemitSuccessList;
use app\models\news\User_remit_list;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class DoremitsuccesslistController extends BaseController
{
    private $limit = 100;

    public function actionIndex()
    {
        $successNum = 0;
        //处理成功数据
        $start_date = date('Y-m-d H:i:00', strtotime('-5 minutes'));
        $end_date = date('Y-m-d H:i:00');
        $where = [
            'AND',
            ['>=', User_remit_list::tableName() . '.last_modify_time', $start_date],
            ['<', User_remit_list::tableName() . '.last_modify_time', $end_date],
            [User_remit_list::tableName() . '.remit_status' => 'SUCCESS']
        ];
        $sql = User_remit_list::find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);

        for ($i = 0; $i < $pages; $i++) {
            $remitList = $sql->offset($i * $this->limit)->limit($this->limit)->all();
            if (empty($remitList)) {
                break;
            }
            foreach ($remitList as $key => $value) {
                $result = $this->addRemitSuccessList($value);
                if (!$result) {
                    continue;
                }
                $successNum++;
            }
        }
        Logger::dayLog('installment/doremitsuccesslist', date('Y-m-d H:i:s'), '成功需处理总数：' . $total, '成功：' . $successNum);
        exit('success:' . $successNum . ';count:' . $total);
    }

    //新增记录
    private function addRemitSuccessList($userRemitListObj)
    {
        if (!is_object($userRemitListObj) || empty($userRemitListObj)) {
            return false;
        }
        $condition = [
            'loan_id' => $userRemitListObj->loan_id,
            'user_id' => $userRemitListObj->loan->user_id,
            'goods_order_id' => '0',
            'business_type' => $userRemitListObj->loan->business_type,
            'remit_type' => 1,
            'settle_amount' => $userRemitListObj->settle_amount,
        ];
        if (in_array($userRemitListObj->loan->business_type, [5, 6])) {
            $condition['goods_order_id'] = $userRemitListObj->goodsOrder->id;
        }
        $info = (new RemitSuccessList())->addRemitSuccessList($condition);
        if (!$info) {
            Logger::dayLog('installment/doremitsuccesslist', '新增remit_success_list表失败', $userRemitListObj->loan_id, $condition);
            return false;
        }
        return true;
    }
}

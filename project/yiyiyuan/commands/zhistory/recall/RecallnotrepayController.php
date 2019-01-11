<?php
namespace app\commands\recall;

/**
 *   召回短信 到期前三天上午发送 每天早晨10点执行一次 每次循环500条
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii recall/recallnotrepay
 *   windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii recall/recallnotrepay
 */

use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\Recall;
use app\models\news\User_loan;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RecallnotrepayController extends BaseController
{
    private $limit = 500;

    public function actionIndex()
    {
        $configPath = __DIR__ . "/config.php";
        if (!file_exists($configPath)) {
            throw new Exception($configPath . "配置文件不存在", 6000);
        }
        $config = include($configPath);
        $config = $this->getOpenSources($config);
        if (empty($config)) {
            return 0;
        }
        $log_time = date("Y-m-d H:i:00", time());
        $sources = ArrayHelper::getColumn($config, 'loan_source');
        $time = date("Y-m-d 00:00:00", strtotime("+3 day"));
        $where = [
            'AND',
            [User_loan::tableName() . '.end_date' => $time],
            [User_loan::tableName() . '.status' => 9],
            ['IN', User_loan::tableName() . '.source', $sources]
        ];

        $sql = User_loan::find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        $successNum = 0;
        for ($i = 0; $i < $pages; $i++) {
            $items = $sql->offset($i * $this->limit)->limit($this->limit)->all();
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (empty($item->user)) {
                        continue;
                    }
                    $info = $this->setRecall($item);
                    if ($info) {
                        $successNum++;
                    }
                }
            }
        }
        Logger::dayLog('recall/recall', 'recallrepay', $log_time, '查询总数：' . $total, '添加召回短信表成功总数：' . $successNum);
    }

    //添加召回记录
    private function setRecall($loanInfo)
    {
        $info = (new Recall())->getRecallByUserId($loanInfo->user_id, $loanInfo->loan_id, 5);
        if (empty($info)) {
            $recallObj = new Recall();
            $user = $loanInfo->user;
            $condition = [
                'user_id' => $loanInfo->user_id,
                'loan_id' => $loanInfo->loan_id,
                'recive_mobile' => $user->mobile,
                'source' => (int)$user->come_from,
                'sms_type' => 5
            ];
            $result = $recallObj->addRecall($condition);
            if ($result) {
                return true;
            } else {
                Logger::dayLog('recall/recallnotrepay_error', '添加召回短信表失败', 'userId:' . $loanInfo->user_id, 'loan_id:' . $loanInfo->loan_id);
                return false;
            }
        }
    }

    //过滤召回未开启渠道
    private function getOpenSources($sources)
    {
        $list = [];
        foreach ($sources as $item) {
            if ($item['is_send_recall'] == 1 && $item['is_send_new'] == 1) {
                $list[] = $item;
            }
        }
        return $list;
    }
}
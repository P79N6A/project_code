<?php
namespace app\commands\recall;

/**
 *   召回短信 放款后发送 每十分钟执行一次 每次500条
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii recall/recallremit
 *   windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii recall/recallremit
 */

use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\Recall;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RecallremitController extends BaseController
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
        $sources = ArrayHelper::getColumn($config, 'loan_source');
        $extend_start_time = date("Y-m-d H:i:00", strtotime("-10 minutes"));
        $loan_start_time = date("Y-m-d H:i:00", strtotime("-1 day"));
        $end_time = date("Y-m-d H:i:00", time());
        $where = [
            'AND',
            ['between', User_loan::tableName() . '.create_time', $loan_start_time, $end_time],
            [User_loan::tableName() . '.status' => 9],
            ['IN', User_loan::tableName() . '.source', $sources],
            ['between', User_loan_extend::tableName() . '.last_modify_time', $extend_start_time, $end_time],
            [User_loan_extend::tableName() . '.status' => 'SUCCESS'],
        ];
        $result = User_loan::find()->joinWith('loanextend', true, 'LEFT JOIN')->where($where)->limit($this->limit)->all();
        if (!empty($result)) {
            $num = count($result);
            $successNum = 0;
            foreach ($result as $item) {
                if (!isset($item->user) || empty($item->user)) {
                    continue;
                }
                $info = $this->setRecall($item);
                if ($info) {
                    $successNum++;
                }
            }
            Logger::dayLog('recall/recall','recallremit', $end_time, '查询总数：' . $num, '添加召回短信表成功总数：' . $successNum);
        }
    }

    //添加召回记录
    private function setRecall($loanInfo)
    {
        $user = $loanInfo->user;
        $info = (new Recall())->getRecallByUserId($loanInfo->user_id, $loanInfo->loan_id, 1);
        if (empty($info)) {
            $recallObj = new Recall();
            $condition = [
                'user_id' => $loanInfo->user_id,
                'loan_id' => $loanInfo->loan_id,
                'recive_mobile' => $user->mobile,
                'source' => (int)$user->come_from,
                'sms_type' => 1
            ];
            $result = $recallObj->addRecall($condition);
            if ($result) {
                return true;
            } else {
                Logger::dayLog('recall/recallremit_error', '添加召回短信表失败', 'userId:' . $loanInfo->user_id, 'loan_id:' . $loanInfo->loan_id);
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
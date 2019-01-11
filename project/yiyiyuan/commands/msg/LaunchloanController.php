<?php
namespace app\commands\msg;

/**
 *   完善资料发起借款
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii msg/firstlogon/launchloan
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii msg/firstlogon/hours 1
 */
use app\commonapi\Logger;
use app\models\news\Juxinli;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\WarnMessageList;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

//避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class LaunchloanController extends Controller
{
    private $limit = 200;

    public function actionHours($timeTpye = 1)
    {
        $this->handle($timeTpye);
    }

    private function handle($timeTpye = 1)
    {
        switch ($timeTpye) {
            case 1://1天
                $startTime = date("Y-m-d H:i:00", strtotime('-24 hours'));
                $endTime = date("Y-m-d H:i:00", strtotime('-23 hours -30 minutes'));
                $text = '一天';
                break;
            default:
                exit('parameter error');
                break;
        }
        $countNum = 0;
        $successNum = 0;
        $where = [
            'AND',
            ['between', Juxinli::tableName() . '.create_time', $startTime, $endTime],
            [Juxinli::tableName() . '.process_code' => '10008'],
            [Juxinli::tableName() . '.type' => 1],
            [User_loan::tableName() . '.loan_id' => NULL]
        ];
        $sql = User::find()->where($where)->joinWith('juxinlijoin', true, 'LEFT JOIN')->joinWith('loan', true, 'LEFT JOIN');
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $userList = $sql->offset($i * $this->limit)->limit($this->limit)->all();
            if (empty($userList)) {
                break;
            }
            $countNum += count($userList);
            $num = (new WarnMessageList)->todo($userList, $timeTpye, $type = 2);
            $successNum += (int)$num;
        }
        Logger::dayLog('script/msg/launchloan', $text . '成功需处理总数：' . $countNum, '成功：' . $successNum);
        exit('count:' . $countNum . ';success:' . $successNum);
    }
}
<?php
namespace app\commands\msg;

/**
 *   资料未填写推送
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii msg/firstlogon/semih
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii msg/firstlogon/hours 1
 */
use app\commonapi\Logger;
use app\models\news\Juxinli;
use app\models\news\User;
use app\models\news\WarnMessageList;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

//避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class FirstlogonController extends Controller
{
    private $limit = 200;

    public function actionSemih()
    {
        $this->handle($timeType = 1);
    }

    public function actionHours($timeType = 2)
    {
        $this->handle($timeType);
    }

    private function handle($timeType = 1)
    {
        switch ($timeType) {
            case 1://半小时
                $startTime = date("Y-m-d H:i:00", strtotime('-60 minutes'));
                $endTime = date("Y-m-d H:i:00", strtotime('-30 minutes'));
                $text = '半小时';
                break;
            case 2://1天
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
            ['between', User::tableName() . '.create_time', $startTime, $endTime],
            [
                'OR',
                [Juxinli::tableName() . '.id' => null],
                [
                    'AND',
                    [Juxinli::tableName() . '.type' => 1],
                    ['!=', Juxinli::tableName() . '.process_code', '10008']
                ]
            ],
        ];
        $sql = User::find()->where($where)->where($where)->joinWith('juxinlijoin', true, 'LEFT JOIN');
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $userList = $sql->offset($i * $this->limit)->limit($this->limit)->all();
            if (empty($userList)) {
                break;
            }
            $countNum += count($userList);
            $num = (new WarnMessageList)->todo($userList, $timeType, $type = 1);
            $successNum += (int)$num;
        }
        Logger::dayLog('script/msg/firstlogon', $text . '成功需处理总数：' . $countNum, '成功：' . $successNum);
        exit('count:' . $countNum . ';success:' . $successNum);
    }
}
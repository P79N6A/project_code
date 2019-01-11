<?php
namespace app\commands\recall;

/**
 *   发送召回短信 每十分钟执行一次 每次500条
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii recall/sendrecall
 *   windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii recall/sendrecall
 */

use app\commands\BaseController;
//use app\models\dev\ApiSms;
use app\commonapi\ApiSms;
use app\models\news\Recall;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendrecallController extends BaseController
{
    private $limit = 500;

    public function actionIndex()
    {
        $start_time = date("Y-m-d H:i:00", strtotime("-1 day"));
        $where = [
            'AND',
            ['status' => 0],
            ['>', 'create_time', $start_time]
        ];
        $result = Recall::find()->where($where)->limit($this->limit)->all();
        if (!empty($result)) {
            $ids = ArrayHelper::getColumn($result, 'id');
            Recall::updateAll(['status' => 3], ['status' => 0, 'id' => $ids]);
            foreach ($result as $item) {
                $this->sendSms($item);
            }
        }
    }

    //发送短信
    private function sendSms($recall)
    {
        $content = $this->getMessage($recall);
        if (empty($content)) {
            return false;
        }
        //通道如果选择2，加【先花一亿元】
        $ret = (new ApiSms())->choiceChannel($recall->recive_mobile, $content, 42, '', 3);
        if ($ret) {
            $this->setRecallStatus($recall, $status = 1);
        } else {
            $this->setRecallStatus($recall, $status = 2);
        }
    }

    //回写召回短信发送状态
    private function setRecallStatus($recall, $status)
    {
        $condition = [
            'status' => $status,
            'send_time' => date('Y-m-d H:i:s')
        ];
        $recall->updateRecall($condition);
    }

    //获取message文本
    private function getMessage($recall)
    {
        $message = '';
        switch ($recall->sms_type) {
            case 1:
                $message = '500不够花？先花一亿元2分钟申请，1分钟到账，提额2000，速来！http://t.cn/RNyfZco';
                break;
            case 2:
                $message = '先花一亿元免息券，可提额2000，速领，让你不差钱有面儿！http://t.cn/RNyIu7Q';
                break;
            case 3:
                $message = '点我，立马提额2000，并送免息券，速点！http://t.cn/RNyIk06';
                break;
            case 4:
                $message = '点我，立马提额2000，并送免息券，速点！http://t.cn/RNyIk06';
                break;
            case 5:
                $message = '还清借款，马上下款2500元，速来先花一亿元app！http://t.cn/Rp5Y0oT';
                break;
        }
        return $message;
    }
}
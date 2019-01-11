<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3
 * Time: 9:22
 */
declare(strict_types=1);

namespace app\commands\kafka;
use app\common\kafka\Consumer;
use app\common\Logger;
use yii\console\Controller;
use Yii;
use Workerman\Worker;
use app\common\kafka\LoadConsumer;
if (!class_exists('Worker')) {
    require Yii::$app->basePath.'/common/workerman/Autoloader.php';
}

#require Yii::$app->basePath."/common/kafka/Consumer.php";
//require Yii::$app->basePath."/common/kafka/HConsumer.php";
/*
use app\common\kafka\KafkaConsumer;

*/

//date_default_timezone_set('PRC');

class BaseConsumerController extends Controller
{
    //protected $broker = "10.253.40.217:9092";
    protected $group_id=null; //test
    protected $topic;  //
    protected $worker_port;   //端口设置21000-22000

    public function actionIndex(){
        $this->group_id = $this->group_id ? $this->group_id : $this->topic;
        if ( empty($this->group_id) || empty($this->topic)){
            $this->log("error", "主题或组不能为空！");
            return false;
        }
        $obj = $this;

        $consumer = function($task) use ($obj){
            $oConsumer = new Consumer();
            $oConsumer->setGroupId($obj->group_id);
            $oConsumer->setTopic($obj->topic);
            $oConsumer->setCallBack([$this, "consumer"]);
            $oConsumer->receive();
            exit;
        };
        $this->runKafka($consumer);
    }
    private function runKafka($consumer)
    {
        global $argv;
        array_splice($argv,0,1);
        try {
            $workerman_log = Yii::$app->basePath . '/log/workerman';
            $this->createdir($workerman_log);

            Worker::$stdoutFile = "{$workerman_log}/kafka_stdout.log";
            //Worker::$stdoutFile = $workerman_log.'.log';
            Worker::$pidFile = "{$workerman_log}/{$this->group_id}_{$this->topic}.pid";
            Worker::$statusDir = $workerman_log;
            $ip_port = 'text://0.0.0.0:'.$this->worker_port;
            $task = new Worker($ip_port);
            $task->count = 3;
            $task->name = $argv[0];

            $task->onWorkerStart = $consumer;
            Worker::runAll();
        }catch (\Exception $e) {
            exit(250);
        }
    }

    /**
     * 日志
     * @param $sub_dir  类型
     * @param $message  内容
     */
    private function log($sub_dir, $message){
        Logger::dayLog("kafka/{$this->topic_name}/consumer/{$sub_dir}", $message);
    }

    private function createdir($dir)
    {
        //if(!is_dir($dir))return false;
        if(file_exists($dir))return true;
        $dir	= str_replace("\\","/",$dir);
        substr($dir,-1)=="/"?$dir=substr($dir,0,-1):"";
        $dir_arr	= explode("/",$dir);
        $str = '';
        foreach($dir_arr as $k=>$a){
            $str	= $str.$a."/";
            if(!$str)continue;
            //echo $str."<br>";
            if(!file_exists($str))mkdir($str,0755);
        }
        return true;
    }
}
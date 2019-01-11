<?php
namespace app\commands\nsq;
if (!class_exists('Worker')) {
    require __DIR__ .'/../../common/workerman/Autoloader.php'; 
}
if (!class_exists('Nsqlookupd')) {
    require __DIR__ .'/../../common/nsqphp/bootstrap.php';
}

use Yii;
use Workerman\Worker;
use Workerman\Lib\Timer;
use nsqphp\Logger\Stderr;
use nsqphp\Lookup\Nsqlookupd;
use nsqphp\Message\Message;
use nsqphp\nsqphp;
use yii\console\Controller;
class BaseWorkerController extends Controller {
    protected $topic;
    protected $channel; 

    public function actionIndex(){
        $this->runNsq();
    }

    private function getConfig(){
        $is_prod = SYSTEM_PROD;
        $env = $is_prod ? 'nsqProd' : 'nsqDev';
        $configPath = Yii::$app->basePath . '/config/'.$env.'.php';

        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    public function runNsq(){
        global $argv;
        array_splice($argv,0,1);
        try {
            $config = $this->getConfig();
            $config['topic'] = $this->topic;
            $config['channel'] = $this->channel;
            Worker::$stdoutFile = Yii::$app->basePath . '/log/nsq_stdout.log';
            $task = new Worker('text://0.0.0.0:2119'); 
            $task->count = 5;
            $task->name = $argv[0];
            $obj = $this;
            $task->onWorkerStart = function($task) use ($obj,$config)
            {      
                // $time_interval = 2.5;
                // Timer::add($time_interval, function()
                // {
                //     echo "task run\n";
                // });
                $logger = new Stderr;
                $lookup = new Nsqlookupd($config['lookup']);
                $nsq = new nsqphp($lookup);
                $nsq->subscribe($config['topic'], $config['channel'], function($msg) use ($obj) {
                    call_user_func([$obj,'customer'],$msg);
                });
                $nsq->run();
				// sleep(10);
            };
            Worker::runAll();
        }catch (\Exception $e) {
            exit(250);
        }
    }
    /*
    private function addNsq($data){
        if(empty($data)){
            return false;
        }
        $nsq = new nsqphp;
        $nsq->publishTo($this->hosts);
        $data = json_encode($data);
        $message = new Message($data);
        $res = $nsq->publish($this->topic,$message);
        return $res;
    }
    */
}

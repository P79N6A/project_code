<?php
namespace app\common;
require_once __DIR__ .'/nsqphp/bootstrap.php';
use nsqphp\Message\Message;
use nsqphp\nsqphp;
use Yii;
class NsqProducer
{   
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

    public function addNsq($topic, $data){
        if(empty($data)){
            return false;
        }
        $config = $this->getConfig();
        $nsq = new nsqphp;
        $nsq->publishTo($config['nsq']);
        $data = json_encode($data);
        $message = new Message($data);
        $res = $nsq->publish($topic,$message);
        return $res;
    }
}

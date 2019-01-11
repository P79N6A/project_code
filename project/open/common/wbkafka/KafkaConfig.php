<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3
 * Time: 17:22
 */
namespace app\common\wbkafka;
use Yii;

class KafkaConfig
{
    public function getConfig(){
        $is_prod = SYSTEM_PROD;
        $env = $is_prod ? 'kafkaProd' : 'kafkaDev';
        $configPath = Yii::$app->basePath . '/config/'.$env.'.php';

        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }
}
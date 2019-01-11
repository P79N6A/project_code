<?php
/**
 * kafka生产者
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/4
 * Time: 20:19
 * php7 /data/wwwroot/open/yii kafka/producer
 * 手册网页：https://arnaud-lb.github.io/php-rdkafka/phpdoc/index.html
 */
namespace app\common\kafka;

use app\common\kafka\KafkaConfig;
use yii\helpers\ArrayHelper;
use app\common\Logger;


class Producer
{
    private $topic = null;
    private $topic_name = null;
    /**
     * 主题入口
     * @param $topic_name  主题
     * @return bool
     */
    public function open($topic_name){
        //1. get broker
        $broker = $this->getBroker();
        if(!$broker){
            $this->log("error", "broker is empty");
            return false;
        }
        try{
            $producer = new \RdKafka\Producer();
            $producer ->addBrokers($broker);

            $cf = new \RdKafka\TopicConf();
            $cf->set('request.required.acks', 1);
            $topic = $producer->newTopic($topic_name, $cf);

            $this->topic = $topic;
            $this->topic_name = $topic_name;

            $result = true;
        }catch(\Exception $e){
            $this->log("error", $e);
            $result = false;
        }
        return $result;
    }


    /**
     *发送信息
     * @param $message  信息内容
     * @return bool
     */
    public function sent($message)
    {
        if (empty($message) ){
            $this->log("error", "message is empty");
            return false;
        }

        if(!$this->topic){
            $this->log("error", "topic或信息不能为空!");
            return false;
        }

        //发送消息
        $message = json_encode(
            ['message' => $message]
        );
        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $message, null);
        return true;
    }


    public function close(){
        $this->topic = null;
    }

    /*
     * 获取配置文件中的kafka服务器
     */
    private function getBroker()
    {
        $oKafkaConfig = new KafkaConfig();
        $get_config = $oKafkaConfig->getConfig();
        $broker = ArrayHelper::getValue($get_config, 'broker', "");
        return $broker;
    }

    /**
     * 日志
     * @param $sub_dir  类型
     * @param $message  同容
     */
    private function log($sub_dir, $message){
        Logger::dayLog("kafka/{$this->topic_name}/producer/{$sub_dir}", $message);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/4
 * Time: 20:19
 */
namespace app\common\wbkafka;

use app\common\kafka\KafkaConfig;
use yii\helpers\ArrayHelper;
use app\common\Logger;


class Producer
{
    private $topic = null;
    private $topic_name = null;

    /**
     * open topic
     * @param $topic
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
     * @param $message
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

    private function getBroker()
    {
        $oKafkaConfig = new KafkaConfig();
        $get_config = $oKafkaConfig->getConfig();
        $broker = ArrayHelper::getValue($get_config, 'broker', "");
        return $broker;
    }
    private function log($sub_dir, $message){
        Logger::dayLog("kafka/{$this->topic_name}/producer/{$sub_dir}", $message);
    }
}
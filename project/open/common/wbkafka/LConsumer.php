<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/5
 * Time: 9:55
 */

class LConsumer
{
    private $group_id;
    private $topic;
    private $partition;

    public function setGroupId($group_id){
        $this->group_id = $group_id;
    }
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }
    public function setPartition($partition)
    {
        $this->partition = $partition;
    }

    public function receive($obj)
    {
        $group_id = $this->group_id;
        $topic = $this->topic;
        $oKafkaConfig = new \app\common\kafka\KafkaConfig();
        $get_config = $oKafkaConfig->getConfig();
        $broker = \yii\helpers\ArrayHelper::getValue($get_config, "broker");
        if (empty($broker) || empty($group_id) || empty($topic)){
            echo "主题或组或kafka服务不能为空！";
            return false;
        }

        $conf = new RdKafka\Conf();
        $conf->setDrMsgCb(function ($kafka, $message) use($topic) {
            $file_name = "kafka".DIRECTORY_SEPARATOR."consumer".DIRECTORY_SEPARATOR.$topic."success";
            \app\common\Logger::dayLog($file_name, "success", json_encode($message));
            var_dump($message);
        });
        $conf->setErrorCb(function ($kafka, $err, $reason) use($topic) {
            $file_name = "kafka".DIRECTORY_SEPARATOR."consumer".DIRECTORY_SEPARATOR.$topic."error";
            \app\common\Logger::dayLog($file_name, "error", json_encode(["err"=>$err, "reason"=>$reason]));
        });
        //设置消费组
        $conf->set('group.id', $group_id);
        $rk = new RdKafka\Consumer($conf);
        $rk->addBrokers($broker);
        $topicConf = new RdKafka\TopicConf();
        $topicConf->set('request.required.acks', 1);
        //在interval.ms的时间内自动提交确认、建议不要启动
        $topicConf->set('auto.commit.enable', 0);
        $topicConf->set('auto.commit.interval.ms', 100);
        // 设置offset的存储为file
        //$topicConf->set('offset.store.method', 'file');
        // 设置offset的存储为broker
        $topicConf->set('offset.store.method', 'broker');
        //$topicConf->set('offset.store.path', __DIR__);
        //smallest：简单理解为从头开始消费，其实等价于上面的 earliest
        //largest：简单理解为从最新的开始消费，其实等价于上面的 latest
        $topicConf->set('auto.offset.reset', 'largest');
        $topic = $rk->newTopic($topic, $topicConf);
        // 参数1消费分区0
        // RD_KAFKA_OFFSET_BEGINNING 重头开始消费
        // RD_KAFKA_OFFSET_STORED 最后一条消费的offset记录开始消费
        // RD_KAFKA_OFFSET_END 最后一条消费
        $topic->consumeStart($this->partition, RD_KAFKA_OFFSET_STORED);

        //日志名
        $file_name = "kafka".DIRECTORY_SEPARATOR."consumer".DIRECTORY_SEPARATOR."error";
        for($i=0; $i<100; $i++){
            //参数1表示消费分区，这里是分区0
            //参数2表示同步阻塞多久
            $message = $topic->consume(0, 12 * 1000);
            if (empty($message)) {
                continue;
            }
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = \yii\helpers\ArrayHelper::getValue($message, "payload");
                    $payload = \yii\helpers\ArrayHelper::getValue(json_decode($payload, true), "message");
                    call_user_func([$obj, 'customer'], $payload);
                    //var_dump($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    \app\common\Logger::dayLog($file_name, "message", "No more messages; will wait for more\n");
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    \app\common\Logger::dayLog($file_name, "message", "Timed out\n");
                    break;
                default:
                    \app\common\Logger::dayLog($file_name, "message", json_encode([$message->errstr(), $message->err]));
                    //throw new \Exception();
                    break;
            }
        }
    }
}
<?php
/**
 * 生产者
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/5
 * Time: 14:49
 *
 * php7 /data/wwwroot/open/yii kafka/consumer start
 * 手册网页：https://arnaud-lb.github.io/php-rdkafka/phpdoc/index.html
 */

namespace app\common\kafka;

use app\common\kafka\KafkaConfig;
use yii\helpers\ArrayHelper;
use app\common\Logger;

class Consumer
{
    private $group_id;
    private $topic;
    private $topic_name;
    private $partition;
    private $callback;

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
    public function setCallBack($callback){
        $this->callback = $callback;
    }

    /**
     * 接收信息方法
     * @return bool
     */
    public function receive()
    {
        if ( empty($this->group_id) || empty( $this->topic)){
            $this->log("error","主题或组或kafka服务不能为空！" );
            return false;
        }

        $conf = $this->getConf();
        $consumer = new \RdKafka\KafkaConsumer($conf);
        // Subscribe to topic 'test'
        //主题
        $consumer->subscribe([$this->topic]);
        //日志名
        for($i=0; $i<100; $i++){
            $message = $consumer->consume( 10000 );
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = ArrayHelper::getValue($message, "payload");
                    $message_arr = json_decode($payload, true);
                    $message = ArrayHelper::getValue($message_arr, "message");
                    $this->log("message", $payload);
                    //call_user_func([$obj, 'customer'], $payload);
                    if($this->callback){
                        call_user_func($this->callback, $payload);
                    }
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    //$this->log("message", "No more messages; will wait for more\n");
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    //$this->log("message", "Timed out");
                    break;
                default:
                    $this->log("message", json_encode([$message->errstr(), $message->err]));
                    break;
            }
        }
    }

    /**
     * kafka消费配置
     * @return null|\RdKafka\Conf
     */
    private function getConf(){
        $conf = new \RdKafka\Conf();
        $cobj = $this;
        $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) use ($cobj) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $kafka->assign($partitions);
                    break;
                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $kafka->assign(NULL);
                    break;
                default:
                    $cobj->log("error", "setRebalanceCb",  json_encode($err) );
            }
        });

        $broker = $this->getBroker();
        if(!$broker){
            return null;
        }
        //主题
        $conf->set('group.id', $this->group_id);
        //kafka服务器，可以是多个例（"ip,ip,ip"）
        $conf->set('metadata.broker.list', $broker);
        $topicConf = new \RdKafka\TopicConf();

        $topicConf->set('auto.offset.reset', 'smallest');

        $topicConf->set('offset.store.method', 'broker');
        $topicConf->set('auto.commit.interval.ms', 100);
        $topicConf->set('request.required.acks', 1);
        $conf->setDefaultTopicConf($topicConf);
        return $conf;

    }

    /**
     * 获取配置文件中的kafka服务器
     * @return mixed
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
     * @param $message  内容
     */
    private function log($sub_dir, $message){
        Logger::dayLog("kafka/{$this->topic_name}/consumer/{$sub_dir}", $message);
    }
}
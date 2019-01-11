<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3
 * Time: 9:44
 */
declare(strict_types=1);
namespace app\common\wbkafka;


use app\common\Logger;
use Yii;
use Kafka\Config;
use Kafka\Producer;
use Kafka\ProducerConfig;
use yii\helpers\ArrayHelper;

require_once Yii::$app->basePath."/common/kafka/vendor/autoload.php";

//date_default_timezone_set('PRC');

class KafkaProducer
{
    //protected $broker = "10.253.40.217:9092";
    //protected $broker = "127.0.0.1:9092";
    //protected $topics ="test";

    /**
     *
     * @param $message
     * @param $topic
     * @return bool
     */
    public function producer($message, $topic)
    {
        $oKafkaConfig = new KafkaConfig();
        $get_config = $oKafkaConfig->getConfig();
        $broker = ArrayHelper::getValue($get_config, 'broker', "");

        if (empty($message) || empty($topic) || empty($broker)){
            echo "信息或主题不能为空！";
            return false;
        }
        $message = json_encode(
            ['message' => $message]
        );

        $config = ProducerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList($broker);
        $config->setBrokerVersion('1.0.0');
        $config->setRequiredAck(1);
        $config->setIsAsyn(false);
        $config->setProduceInterval(500);
        $use_message = [
            'topic' => $topic,
            'value' => $message,
            //'key' => '',
        ];
        $producer = new Producer(
            function() use($use_message) {
                return [
                    $use_message,
                ];
            }
        );
        $obj = $this;
        $producer->success(function($result) use($topics, $obj) {
            $call_data = json_encode(["result" => $result, "topics"=>$topics]);
            call_user_func([$obj, 'success'], $call_data);
        });
        $producer->error(function($errorCode) use($topics, $obj) {
            $call_data = json_encode(["result" => $errorCode, "topics"=>$topics]);
            call_user_func([$obj, 'error'], $call_data);

        });
        $producer->send(true);
    }

    /**
     * 成功接收的数据
     * @param $result
     * @return bool
     */
    public function success($result)
    {
        $json_data = json_decode($result, true);
        if (is_null($json_data)){
            return false;
        }
        $topics = ArrayHelper::getValue($json_data, "topics");
        $result = ArrayHelper::getValue($json_data, "result");
        $file_name = "kafka".DIRECTORY_SEPARATOR."topic".DIRECTORY_SEPARATOR.$topics."success";
        Logger::dayLog($file_name, "message", json_encode($result));
    }

    /**
     * 失败接收的数据
     * @param $result
     * @return bool
     */
    public function error($result)
    {
        $json_data = json_decode($result, true);
        if (is_null($json_data)){
            return false;
        }
        $topics = ArrayHelper::getValue($json_data, "topics");
        $result = ArrayHelper::getValue($json_data, "result");
        $file_name = "kafka".DIRECTORY_SEPARATOR."topic".DIRECTORY_SEPARATOR.$topics."error";
        Logger::dayLog($file_name, "message", json_encode($result));
    }
}
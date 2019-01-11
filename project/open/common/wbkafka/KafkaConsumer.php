<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/4
 * Time: 12:00
 */
namespace app\common\wbkafka;

use Yii;
use app\common\kafka\KafkaConfig;
use Kafka\Consumer;
use Kafka\ConsumerConfig;
use yii\helpers\ArrayHelper;

require_once Yii::$app->basePath . "/common/kafka/vendor/autoload.php";


class KafkaConsumer
{
    /**
     * @param $obj   对象
     * @param $group_id   组
     * @param $topic  主题
     * @return bool
     */
    public function consumer($obj, $group_id, $topic)
    {
        $oKafkaConfig = new KafkaConfig();
        $get_config = $oKafkaConfig->getConfig();
        $broker = ArrayHelper::getValue($get_config, "broker");
        if (empty($broker) || empty($group_id) || empty($topic)){
            echo "主题或组或kafka服务不能为空！";
            return false;
        }

        $config = ConsumerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(100);
        $config->setMetadataBrokerList($broker);
        $config->setGroupId($group_id);
        $config->setBrokerVersion('1.0.0');
        $config->setTopics([$topic]);
        $config->setOffsetReset('latest');
        $config->setRequiredAck(1);

        $config->set('offset.store.method', 'broker');
        //$config->set('offset.store.method', 'broker');


        $consumer = new Consumer();

        //$consumer->setLogger($logger);
        $consumer->start(function ($topic, $part, $message) use($obj): void {
            //var_dump($message);
            $message = ArrayHelper::getValue($message, "message", "");
            $value = ArrayHelper::getValue($message, "value");
            call_user_func([$obj, 'customer'], $value);
        });
    }
}
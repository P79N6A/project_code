<?php
/**
 * 测试例子
 * php7 /data/wwwroot/open/yii kafkaconsumer
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/30
 * Time: 16:44
 *
 *  sudo -u www /usr/local/bin/php /data/wwwroot/open/yii kafka/consumer start
 *  sudo -u www /usr/local/bin/php /data/wwwroot/open/yii kafka/consumer reload
 *  sudo -u www /usr/local/bin/php /data/wwwroot/open/yii kafka/consumer stop
 *  sudo -u www /usr/local/bin/php /data/wwwroot/open/yii kafka/consumer status  查看
 */

namespace app\commands\kafka;



use app\common\Logger;

class ConsumerController extends BaseConsumerController
{

    //protected $group_id = "test";
    protected $topic = "open_test";
    protected $worker_port = 21000; //端口设置21000-22000,端口必须唯一


    public function consumer($message)
    {
        var_dump($message);
        Logger::dayLog("kafka/".$this->topic, 'message', $message);
    }
}

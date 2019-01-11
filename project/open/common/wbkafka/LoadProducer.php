<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/5
 * Time: 9:45
 */
namespace app\common\wbkafka;
use Yii;
require Yii::$app->basePath."/common/kafka/Producer.php";

class LoadProducer
{
    public function producer($message, $topic)
    {
        $oProducer = new \Producer();
        $oProducer->sent($message, $topic);
    }
}
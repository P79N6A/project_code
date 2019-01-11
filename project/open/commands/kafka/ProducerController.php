<?php
/**
 * 客户端
 * php7 /data/wwwroot/open/yii kafkaproducer
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/30
 * Time: 16:44
 */
namespace app\commands\kafka;

//
//class ProducerController extends BaseProducerController
//{
//    protected $topic ="test";
//
//    public function actionIndex()
//    {
//
//        $message = [
//            "message"=>"aaaaa".mt_rand(),
//        ];
//        $this->producer(json_encode($message));
//    }
//}

use app\common\kafka\LoadProducer;
use app\common\kafka\Producer;
use Yii;
use yii\console\Controller;

class ProducerController extends Controller
{
    public function actionIndex()
    {
        $oLoadProducer = new Producer();
        $oLoadProducer -> open("open_test");
        for ($i=0; $i<5; $i++){
            $a = rand(1000,9999)."A";
            $result = $oLoadProducer->sent( $a);
        }

    }
}

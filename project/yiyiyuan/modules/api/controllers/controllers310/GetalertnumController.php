<?php

namespace app\modules\api\controllers\controllers310;

use app\modules\api\common\ApiController;
use app\commonapi\Keywords;
use Yii;

class GetalertnumController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $loan_id = Yii::$app->request->post('loan_id');
        $buyInsurance_info = Keywords::buyInsurance();
        $alertNum = $buyInsurance_info['alertNum'];

        if (empty($version) || empty($loan_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $key = 'alertNum'.$loan_id;
        $redisNum = Yii::$app->redis->get($key);
        $isAlert = $alertNum-$redisNum <= 0 ? '2' : '1';//1:弹窗，2：不弹窗
        if($isAlert == '2'){
            $array['is_alert'] = $isAlert;
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        }

        $res = Yii::$app->redis->setex($key, 86400, $redisNum+1);
        $array['is_alert'] = $isAlert;
        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;
    }
}

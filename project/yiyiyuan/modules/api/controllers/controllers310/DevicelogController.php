<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Logger;
use app\modules\api\common\ApiController;
use Yii;

class DevicelogController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $uuid = Yii::$app->request->post('uuid');
        $deviceType = Yii::$app->request->post('device_type');
        $deviceSys = Yii::$app->request->post('device_sys');
        $startTime = Yii::$app->request->post('start_time');
        $endTime = Yii::$app->request->post('end_time');
        if (empty($version) || empty($uuid) || empty($startTime)) {
            exit($this->returnBack('99994'));
        }
        $data = [
            'uuid' => $uuid,
            'device_type' => $deviceType,
            'device_sys' => $deviceSys
        ];
        //上次结束时间
        if (!empty($endTime)) {
            $data['time'] = $endTime;
            $data['type'] = 2;
            $this->writeLog($data);
        }
        //本次开始时间
        if (!empty($startTime)) {
            $data['time'] = $startTime;
            $data['type'] = 1;
            $this->writeLog($data);
        }
        exit($this->returnBack('0000'));
    }

    private function writeLog($data)
    {
        Logger::dayLog('device_log', json_encode($data));
    }
}

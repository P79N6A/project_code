<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Statistics;

class StController extends NewdevController {

    public $enableCsrfValidation = false;
    public function behaviors()
    {
        return [];
    }
    public function actionStatisticssave() {
        $info = $_SERVER;
        $ip = Common::get_client_ip();
        $ip_explode = explode(',',$ip);
        $ip = $ip_explode[0];
        $model = new Statistics();
        $type = isset($_GET['type']) ? intval($_GET['type']) : 0;
        $model->user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $model->loan_id = isset($_GET['loan_id']) ? intval($_GET['loan_id']) : 0;
        $model->from = 'sms_api';
        $model->remoteip = isset($ip) ? $ip : 0;
        $agent = isset($info['HTTP_USER_AGENT'])?mb_substr($info['HTTP_USER_AGENT'], 0, 256, 'utf-8'):'';
        $model->user_agent = $agent;
        $model->create_time = date('Y-m-d H:i:s');
        $model->type = $type;
        Logger::dayLog('statistics',$model);
        $result = $model->save();
        Logger::dayLog('statistics',$result);
    }
}

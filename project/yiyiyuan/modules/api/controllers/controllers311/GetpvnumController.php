<?php

namespace app\modules\api\controllers\controllers311;

use app\commonapi\Common;
use app\models\news\Statistics;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class GetpvnumController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $loan_id = Yii::$app->request->post('loan_id');
        $from = Yii::$app->request->post('from');
        $type = Yii::$app->request->post('type');

        if (empty($version) || empty($type) || empty($user_id) || empty($from)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $array_from = [
            '1'=>'ios',
            '2'=>'andraoid',
        ];
        $array_type = [
            '1' => 150,
            '2' => 151,
        ];
        if (!array_key_exists($from, $array_from)) {
            $array = $this->returnBack('99997');
            echo $array;
            exit;
        }
        if (!array_key_exists($type, $array_type)) {
            $array = $this->returnBack('99997');
            echo $array;
            exit;
        }
        /*         * *************记录访问日志end******************* */

        $fuser = User::findOne($user_id);
        if (empty($fuser)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
        $info = $_SERVER;
        //print_r($info);exit;
        $model = new Statistics();
        $model->user_id = $user_id;
        $model->loan_id = isset($loan_id) && !empty($loan_id) ? intval($loan_id) : 0;
        $model->from = $array_from[$from];
        $model->remoteip = Common::get_client_ip();
        $model->user_agent = $info['HTTP_USER_AGENT'];
        $model->create_time = date('Y-m-d H:i:s');
        $model->type = $array_type[$type];

        $model->save();
        $array = $this->returnBack('0000');
        echo $array;
        exit;
    }
}

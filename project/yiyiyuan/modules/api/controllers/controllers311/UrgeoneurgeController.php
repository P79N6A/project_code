<?php

namespace app\modules\api\controllers\controllers311;

use app\models\news\ScanTimes;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class UrgeoneurgeController extends ApiController
{

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        if (empty($version) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $userInfo = User::findOne($user_id);
        if (empty($userInfo)) {
            exit($this->returnBack('10001'));
        }
        $sacnTimesModel = new ScanTimes();
        $oScanTimes= ScanTimes::find()->where(['mobile' => $userInfo->mobile, 'type' => 25])->orderBy('id desc')->one();
        if(!empty($oScanTimes)){
            $time_diff=time()-strtotime($oScanTimes->create_time);
            if($time_diff<24*3600){
                exit($this->returnBack('10230'));
            }else{
                $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => 25]);
            }
        }else{
            $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => 25]);
        }

        exit($this->returnBack('0000'));

    }

}

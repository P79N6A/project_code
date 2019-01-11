<?php
namespace app\modules\api\controllers\controllers311;

use app\models\news\ScanTimes;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class SyncyxController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $userId = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type', 23);//23信息同步弹窗（默认）  24信用卡跳过

        if (empty($version) || empty($userId) || empty($type)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $user = new User();
        $userInfo = $user->getUserinfoByUserId($userId);
        if (empty($userInfo)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
        $sacnTimesModel = new ScanTimes();
        $sacnTimesModel->save_scan(['mobile' => $userInfo->mobile, 'type' => $type]);
        $array = $this->returnBack('0000');
        echo $array;
        exit;
    }
}

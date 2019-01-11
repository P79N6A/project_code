<?php

namespace app\modules\api\controllers\controllers314;

use app\common\ApiClientCrypt;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\Renewal_payment_record;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class ScantimesController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type', 1);

        if (empty($version) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $scanModel = new \app\models\dev\Scan_times();
        $user = User::findOne($user_id);
        $scanModel->getScanCount($user->mobile, 16);
        $array = $this->returnBack('0000');
        echo $array;
        exit;
    }

}

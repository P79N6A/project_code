<?php

namespace app\modules\api\controllers\controllers314;

use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class InsuranceinfoController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $insurance_order = Yii::$app->request->post('insurance_order');
        if (empty($version) || empty($insurance_order)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $list = $this->gettbinfo($insurance_order);
        $array = $this->returnBack('0000', $list);
        echo $array;
        exit;
    }

    private function gettbinfo($insurance_order) {
        $list = [];
        $insure = Insurance::find()->where(['insurance_order'=>$insurance_order])->one();
        if (empty($insure)) {
            $list['amount'] = '';
            $list['time'] = '';
            $list['status'] = '';
            $list['order_id'] = '';
        }else{
            $list['amount'] = sprintf('%.2f', $insure->money);
            $list['time'] = $insure->create_time;
            $list['status'] = $insure->status;
            $list['order_id'] = !empty($insure->insurance_order) ? $insure->insurance_order : '';
        }
        return $list;
    }
}

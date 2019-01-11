<?php

namespace app\modules\api\controllers\controllers311;

use app\models\news\Insurance;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class InsurancelistController extends ApiController
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

        $user = User::findOne($user_id);
        if (empty($user)) {
            $array = $this->returnBack('99997');
            echo $array;
            exit;
        }

        $list = $this->gettblist($user);
        if (!empty($list)) {
            $array['list'] = $list;
        } else {
            $array['list'] = array();
        }
        $array['list'] = $list;
        $array = $this->returnBack('0000');
        echo $array;
        exit;
    }

    private function gettblist($user)
    {
        $where = [
            'AND',
            ['user_id' => $user->user_id],
            ['status' => 1],
            ['NOT', ['insurance_order' => NULL]]
        ];
        $loan = Insurance::find()->where($where)->orderBy('create_time desc')->all();
        $list = [];
        if (empty($loan)) {
            return $list;
        }
        foreach ($loan as $key => $val) {
            $list[$key]['amount'] = sprintf('%.2f', $val->money);
            $list[$key]['time'] = $val->create_time;
            $list[$key]['status'] = $val->status;
            $list[$key]['order_id'] = !empty($val->insurance_order) ? $val->insurance_order : '';
        }
        return $list;
    }

}

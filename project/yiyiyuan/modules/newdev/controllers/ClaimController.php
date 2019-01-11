<?php

/**
 * 借款相关记录与详情
 * Created by PhpStorm.
 * User: wangyongqiang
 * Date: 2017/4/26
 * Time: 15:56
 */

namespace app\modules\newdev\controllers;

use app\commonapi\ApiSign;
use app\commonapi\Logger;
use app\models\news\Difference_amount;

class ClaimController extends NewdevController {

    private $data;

    public function beforeAction($action) {
        //parent::beforeAction($action);
        $this->verifyData();
        return true;
    }

    private function verifyData() {
        $data = json_encode(['amount' => 1000]);
        $_sign = $this->post('_sign');
        $this->data = $this->post('data');
        $apiSignModel = new ApiSign();
        $verify = $apiSignModel->verifyData($this->data, $_sign);
        if (!$verify) {
            return $this->showMessage('10000', '验签错误', 'JSON');
        }
        return $verify;
    }

    /**
     * 借款记录
     */
    public function actionThirdamount() {
        //1、验证
        $this->verifyData();
        $condition = json_decode($this->data, true);
        Logger::dayLog('claim', $condition);

        //2、每天一次
        $model = new Difference_amount();
        $day_count = $model->verifyDayRecord();
        if ($day_count) {
            return FALSE;
        }
        //3、保存
        $parm = [
            'loan_amount' => $condition['amount'],
            'invest_amount' => 0,
            'loan_expire_amount' => 0
        ];
        $result = $model->addRecord($parm);
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}

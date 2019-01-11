<?php

namespace app\modules\sysloanguide\controllers;

use app\commonapi\Logger;
use app\models\day\User_loan_guide;
use app\models\news\Renew_amount;
use app\modules\sysloan\common\ApiController;

class RenewalController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $required = ['loan_id', 'renew', 'renew_fee', 'start_time', 'end_time'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify = $this->BeforeVerify($required, $httpParams);
        Logger::errorLog(print_r(array($httpParams), true), 'renewal_request', 'Renewal');
        $loan = User_loan_guide::find()->where(['loan_id' => $httpParams['loan_id']])->one();
        if (empty($loan)) {
            $array = ['rsp_code' => '10001', 'rsp_desc' => $this->errorback('10001')];
            return json_encode($array);
        }
        if (!in_array($loan->business_type, [7])) {
            $array = ['rsp_code' => '10001', 'rsp_desc' => $this->errorback('10004')];
            return json_encode($array);
        }
        $renew_model = new \app\models\day\Renew_amount_guide();
        $renew = $renew_model->getRenew($loan->loan_id);
        if (!empty($renew) && $renew->mark == 1) {
            $array = ['rsp_code' => '10003', 'rsp_desc' => $this->errorback('10003')];
            return json_encode($array);
        }
        $mark = 1;
        $res = $renew_model->addFirstRecord($loan, $httpParams['renew_fee'], $mark, 2, $httpParams['end_time']);
        if ($res) {
            $array = ['rsp_code' => '0000', 'rsp_desc' => $this->errorback('0000')];
        } else {
            $array = ['rsp_code' => '10002', 'rsp_desc' => $this->errorback('10002')];
        }
        return json_encode($array);
    }

    /**
     * @abstract 错误提示信息
     *
     * */
    public function errorback($error_code) {
        $array_error_code = array(
            '0000' => '添加展期信息成功',
            '10001' => '未找到该借款信息',
            '10002' => '添加展期信息失败',
            '10003' => '该借款已经可以展期',
            '10004' => '分期借款不可以展期',
        );
        return $array_error_code[$error_code];
    }

}

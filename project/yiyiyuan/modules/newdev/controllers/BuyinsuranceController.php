<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\news\User_loan;
use app\modules\newdev\controllers\NewdevController;
use Yii;

class BuyinsuranceController extends NewdevController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $loan_id = Yii::$app->request->post('loan_id');
        $source = Yii::$app->request->post('source', 1);
        $is_chk = Yii::$app->request->post('is_chk');
        if (empty($loan_id) || empty($source) || empty($is_chk)) {
            echo json_encode(['code' => '10001', 'msg' => '系统错误']);
            exit;
        }
        //判断借款是否存在
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            echo json_encode(['code' => '10002', 'msg' => '借款数据错误']);
            exit;
        }
        //判断核保信息是否存在
        $insureInfo = (new Insurance())->getDateByLoanId($loan_id);
        if (empty($insureInfo)) {
            echo json_encode(['code' => '10003', 'msg' => '数据错误']);
            exit;
        }
        $in_result = (new Insure())->find()->where(['loan_id' => $loan_id, 'status' => [0, -1, 1]])->one();
        if ($in_result) {
            echo json_encode(['code' => '10004', 'msg' => '保险购买中，请勿重复购买']);
            exit;
        }
        //勾选不勾选
        $up_res = $insureInfo->updateData(['is_chk' => $is_chk]);
        if (!$up_res) {
            echo json_encode(['code' => '10001', 'msg' => '系统错误']);
            exit;
        }
        if ($is_chk == 2) {
            echo json_encode(['code' => '0000', 'msg' => '', 'url' => '/new/loan']);
            exit;
        }
        $order_id = date('YmdHis') . $insureInfo->loan_id;
        //投保支付
        $url = $this->policypay($insureInfo, $order_id);
        if (!$url) {
            echo json_encode(['code' => '10005', 'msg' => '请求错误']);
            exit;
        }
        //添加支付记录
        $order_id = $this->addInsurance($insureInfo, $order_id, $source);
        if (!$order_id) {
            echo json_encode(['code' => '10001', 'msg' => '系统错误']);
            exit;
        }
        echo json_encode(['code' => '0000', 'msg' => '', 'url' => $url]);
        exit;
    }

    public function actionGetalertnum(){
        $loan_id = $this->post('loan_id');
        $buyInsurance_info = Keywords::buyInsurance();
        $alertNum = $buyInsurance_info['alertNum'];
        if(empty($loan_id)){
            echo json_encode(['code' => '10001', 'msg' => '系统错误']);
            exit;
        }

        $key = 'alertNum'.$loan_id;
        $redisNum = Yii::$app->redis->get($key);
        $isAlert = $alertNum-$redisNum <= 0 ? '2' : '1';//1:弹窗，2：不弹窗
        if($isAlert == '2'){
            echo json_encode(['code' => '0000', 'msg' => '成功', 'isAlert' => $isAlert]);
            exit;
        }

        $res = Yii::$app->redis->setex($key, 86400, $redisNum+1);
        echo json_encode(['code' => '0000', 'msg' => '成功', 'isAlert' => $isAlert]);
        exit;
    }

    private function policypay($insureInfo, $order_id) {
        $contacts = [
            'req_id' => $insureInfo->req_id, //请求序号
            'client_id' => $order_id,
            'callbackurl' => Yii::$app->params['policypay_notify_url'], //回调地址
        ];
        $api = new Apihttp();
        $result = $api->policypay($contacts);
        if ($result['res_code'] != 0) {
            Logger::dayLog('installment/policypay', '投保支付失败', 'insure ID：' . $insureInfo->id, $contacts, $result);
            return false;
        }
        if (isset($result['res_data']) && isset($result['res_data']['url'])) {
            $redirect_url = (string) $result['res_data']['url'];
            if (empty($redirect_url)) {
                return false;
            }
            return $redirect_url;
        } else {
            return false;
        }
    }

    private function addInsurance($insureInfo, $order_id, $source) {
        $data = [
            'req_id' => $insureInfo->req_id,
            'loan_id' => $insureInfo->loan_id,
            'user_id' => $insureInfo->user_id,
            'order_id' => $order_id,
            'money' => $insureInfo->money,
            'source' => $source,
            'version' => 0,
        ];
        $res = (new Insure())->saveData($data);
        if (!$res) {
            return false;
        }
        return $data['order_id'];
    }

    private function errorreback($code) {
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = $this->geterrorcode($code);
        return $array;
    }

    private function reback($code, $url) {
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = $this->geterrorcode($code);
        $array['redirect_url'] = $url;
        return $array;
    }

}

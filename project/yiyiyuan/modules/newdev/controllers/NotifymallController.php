<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Crypt3Des;
use app\commonapi\Logger;
use app\models\news\MallOrder;
use app\models\news\MallOrderPay;
use Yii;

class NotifymallController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        if (isset($_GET['res_data'])) {
            $data = $this->get('res_data');
        } else {
            $data = $this->post('res_data');
        }
        $api = new ApiClientCrypt();
        $result = Crypt3Des::decrypt($data, $api->getKey());
        $parr = json_decode($result, true);
        if (!is_array($parr)) {
            $return = [
                'res_code' => '200',
                'res_data' => '解析失败',
            ];
            return Crypt3Des::encrypt(json_encode($return), $api->getKey());
        }
        Logger::dayLog('mallPay_notify', $parr);
//        $parr = array (
//            'pay_type' => 123,
//            'status' => 2,
//            'orderid' => 'M01271104347489764',
//            'yborderid' => '20180127110441010431140590257714',
//            'amount' => 1,
//            'res_code' => '',
//            'res_msg' => '',
//            'app_id' => '2810335722015',
//            '_sign' => '7b1616c21974b0c5ffac401b8bfe141a',
//        );
        $MallOrderPay = (new MallOrderPay())->find()->where(['req_id'=>$parr['orderid']])->one();
        if (empty($MallOrderPay)) {
            return false;
        }
        $isPost = Yii::$app->request->isPost;
        if ($isPost) {
            $this->postNotify($MallOrderPay, $parr);
        } else {
           return $this->getNotify($MallOrderPay, $parr);
        }
    }

    private function getNotify($MallOrderPay, $parr)
    {
        if ($MallOrderPay->status == 0) {
            $conditon = ['status' => -1];
            $get_up_result = $MallOrderPay->updateData($conditon);
            $mallOrderModel = (new MallOrder())->getGoodsOrderByOrderId($MallOrderPay->m_id);
            $mallOrderModel->doing();
            if (!$get_up_result) {
                Logger::dayLog('MallPay_notify', 'get_update_faile' . $parr['orderid'], $conditon);
            }
        }
        return $this->redirect('/mall/store');
    }

    private function postNotify($MallOrderPay, $parr)
    {
        if (empty($MallOrderPay || empty($parr))) {
            exit;
        }
        if ($MallOrderPay->status == 1 || $MallOrderPay->status == 4) {
            echo 'SUCCESS';
            exit;
        }

        if ($parr['res_code'] == 0 && $parr['status'] == 2) {//成功处理
            $data = [
                'status' => 1,
                'actual_money' =>  $parr['amount'],
                'repay_time' => date('Y-m-d H:i:s'),
            ];
            $MallOrderPay->updateData($data);
            $mallOrderModel = (new MallOrder())->getGoodsOrderByOrderId($MallOrderPay->m_id);
            $res = $mallOrderModel->success();

        } else if ($parr['res_code'] == 0 && $parr['status'] == 11) {//失败处理
            $data = [
                'status' => 4,
                'repay_time'=>date('Y-m-d H:i:s'),
            ];
            $res = $MallOrderPay->updateData($data);
        }
        if ($res === true) {
            echo 'SUCCESS';
            exit();
        } elseif ($res === false) {
            Logger::dayLog('MallPay_notify', '保单状态更新失败：' . $parr['orderid'], $parr);
            exit();
        }
        Logger::dayLog('MallPay_notify', '未定义状态ID：' . $parr['orderid'], $parr);
    }

}

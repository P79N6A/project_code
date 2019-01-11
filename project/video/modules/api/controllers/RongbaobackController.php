<?php

/**
 * 已经挪致 /controllers/ 这个已经不用了. 稍后删除
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\rongbao\Remit;
use app\models\YpQuickOrder;
use app\models\YpTztOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\rongbao\RbApi;
use app\modules\api\common\rongbao\RemitStatus;
use Yii;

class RongbaobackController extends ApiController {

    /**
     * 融宝代付
     */
    private $oRbApi;

    /**
     * 易宝投资通
     */
    private $yeepay;

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }

    public function actionIndex() {
        
    }

    public function actionRongbao($id='prod4') {
        echo $id;exit;
        $oRbApi = new RbApi($id);
        $params = Yii::$app->request->post();
        Logger::dayLog('rongbao/notify', $this->post());
        if (!isset($params['data'])) {
            return false;
        }
//        $params = [
//            'data' => 'APxDamhu7JiGLqzIVP/wWkV1uoqtaD/obmo5BWOoSgewMkQyK+h3P+R+ip9wbhIJIQ7BK5/mBEXw1ZcLAGHMARsQOEO23FHJ/InmJmiwOpU8VesirnP9sK/cSzPW/rYgxNviT5zK164Zhv9aVh4j3tYcBm379WFvFpxchRD51YpRfMXoLedjwaILp8trySyMtcNAQUbuyU8fFyH7I0drF90BhlkjeeP+/D9C5b7yVZzHILuKUNNpc8Dho6T5m4DcQ1EAyHCj2W60nBGlilbIhMcgaJKxL0rv5r+ZXdayNH+t56xQGTyHtyo0qyEkFVHweOfcfqpID7A/9IJJOQGlOw==',
//            'merchant_id' => '100000000000147',
//            'encryptkey' => 'd9CRgUnZ+h0lCJ/P+qqQNuVXkV0gMFsqcpEYAqpMC6k7H2CneBYxi65HahlYSICfwv5dwpF6EZWMO8v9OMXSyesUBajPOuRAq9lneQLonLAMdFqsF7c6E/gMh+CeGh7smCAuq3iyIaZjDhk5GOHJRt6h9Sd0LH/SMRIInc68592O0I+ILjjIa7Ph5IynS+PZK67gMWbwetmljt9WngKEicm5MfSLLP/+eQx+MxvQ8frqXbvygs05d6AOncSmjwoD0MYdoUzFgL/Ufz6WrxhrpEYw8Bi2RxRMl9KtaTM0JTcRfQzlDoOBSi35IDamoSrCYEIomoZdNJOLjR3hiJz6iA==',
//        ];
        $result = $oRbApi->decResult($params);
        $res = json_decode($result, TRUE);
        Logger::dayLog('rongbao/notify', $res);

        $re_sign = $res['sign'];
        unset($res['sign']);
        $sign = $oRbApi->buildSign($res);
        if ($sign != $re_sign) {
            return FALSE;
        }
        //5.1 解析状态响应码
        $oRemitStatus = new RemitStatus;
        $result = $oRemitStatus->parseQueryNotityStatus($res['data']);

        if (!$result) {
            return false;
        }

        $data = explode(',', $res['data']);

        $oRemit = Remit::findOne($data[2]);
        if(!$oRemit){
            Logger::dayLog('rongbao/notify', 'not found', 'order_no', $res['order_no'], 'id', $data[2]);
            exit;
        }
        if (in_array($oRemit->remit_status, [Remit::STATUS_FAILURE, Remit::STATUS_SUCCESS])) {
            echo 'SUCCESS';
            exit;
        }
        $transaction = Yii::$app->db->beginTransaction();
        //5.2 保存查询表中
        $result = $oRemit->saveRspStatus($oRemitStatus->remit_status, $oRemitStatus->rsp_status, $oRemitStatus->rsp_status_text, '',3);
        if (!$result) {
            Logger::dayLog('rongbao/notify', 'error', $oRemit->id, $oRemit->errors);
            $transaction->rollBack();
            return FALSE;
        }

        //6 加入到通知列表中
        $RbRemitModel = new \app\modules\api\common\rongbao\Rbremit();
        $result = $RbRemitModel->InputNotify($oRemit);
        if (!$result) {
            $transaction->rollBack();
            return FALSE;
        }
        $transaction->commit();
        echo 'SUCCESS';
        exit;
    }

    /**
     * 投资通异步回调接口:只有异步，前台是自己的，不在这儿
     */
    public function actionTztcallurl() {
        Logger::dayLog(
                'yeepaycallback/tzt', 'GET', $this->get(), 'POST', $this->post()
        );

        //1 数据获取
        $data = $this->getParam('data');
        $encryptkey = $this->getParam('encryptkey');
        /* if (!(defined('SYSTEM_LOCAL') && SYSTEM_LOCAL)) {
          $data       = 'ppqNoZ9YXgUYF9/AhMaDx+s3G0SM8cqV7lZ1b2iWu0+cScluR3T8UpEdRm5hauB/KdTzxolEeK3OjJDmJXaVKHcTwxToCwaU3KegWmiNPZQ55FcCXmf0tWoQVm1fHaCcCIVFTLoN0OHvbUOSI4vrVxEuQ+FLYBMIt+sMTgJPfCz4xGKaYLwwnfAohKNRxBUJ8MJM8C1b8YIZzj35zQYevSsIowdLFy6ApPKKUTmgnMbr7k1a8eaQQQ4rH3GKB6eUVldpq2ho6cbpEwrvNDvQZKj6t7h7SVAt31mcvdXBq+UTtkBzMG1ZVL6n2Ftsah12q4x++5srlkjoxGZyXZrtHHTs3tDCCO/QGr46pBBR1kFOOO1Eon4wos9HEVCWtxp9LEwUwYRHEhKV5y3ZIDzZqG1V/5yM2QTEawvL1BSX2CWXDf1tXVNT4dL/3JF50gvxp+FLQkVIHajyFmW+671R4ky4Sf5RKzqIJoV1y+15yuXk/MeNa83y6RI+1QT+fqok02jplNy7TY6wqc7pqHvs6BtxPOHtHx4hFlcizXkNP3UyfJGhIkxu2f1oiADLHfV/5qS6Re6X1WM54cQ/GDk7S9aNXmW/OmuIFBgeE9Tb7aFwr6PI76hjifEIz/dTxHGFICsRwT+sSel067gNb/qb3OEz7TV63ia1WyZwsrq9nA4=';
          $encryptkey = 'hRNdmKgocGjAktsCKCr/ZL47zIQLToWQX97dNjLXjfDx03IAHF2ksBIVv47dcuxeaDBNByk+4Ld5yctS3kC1apLKqID+eGyYhlu8QgQ5emFDJK42ElusDg2neLuNa58aCdOys9uIOFq3fRmSmFniyBBMn7WijUVfNe4VQS1Drag=';
          } */

        //2 解析数据
        $yeepayData = $this->yeepay->callback($data, $encryptkey);

        //3 无响应时不处理
        if (empty($yeepayData)) {
            Logger::dayLog(
                    'yeepaycallback/tzt', '响应的数据为空或无法解析', 'data', $data, 'encryptkey', $encryptkey
            );
            exit;
        }
        if (!is_array($yeepayData) || $yeepayData['error_code']) {
            Logger::dayLog(
                    'yeepaycallback/tzt', '响应的数据不合法', 'yeepaydata', $yeepayData
            );
            exit;
        }

        //5 根据易宝返回的订单号检查本数据库是否存在
        $orderModel = YpTztOrder::model()->getByAidOrderId($yeepayData['orderid']);
        if (empty($orderModel)) {
            Logger::dayLog(
                    'yeepaycallback/tzt', '数据库中无orderid', $yeepayData
            );
            exit;
        }

        //6  检测本地已经是支付成功状态了，则没必要再处理一次
        if ($orderModel->pay_status != YpTztOrder::STATUS_PAYOK) {
            // 获取异步状态对应关系
            // 易宝状态有   0:支付失败; 1:成功; 2:已撤消;
            $orderModel->pay_status = $orderModel->asyncStatus($yeepayData['status']);
            if (YpTztOrder::STATUS_PAYFAIL == $orderModel->pay_status) {
                $orderModel->error_code = intval($yeepayData['errorcode']);
                $orderModel->error_msg = isset($yeepayData['errormsg']) ? $yeepayData['errormsg'] : '';
            }
            $orderModel->closetime = time();
            $orderModel->yborderid = (string) $yeepayData['yborderid'];
            $dbres = $orderModel->save();

            // 纪录数据库错误日志
            if (!$dbres) {
                $errors = $orderModel->errors;
                Logger::dayLog(
                        'yeepaytzt/error', 'actionInvokebindbankcard', '保存到db失败', '保存数据', $orderModel->attributes, '错误原因', $errors
                );
                exit;
            }

            //保存到总订单表状态
            $r = $orderModel->upPayorderStatus();
        }

        //7 通知客户端
        $result = $orderModel->clientNotify();
        if ($result) {
            echo 'SUCCESS';
            exit;
        }
    }

    /**
     * 一健支付回调:
     * 只有支付成功易宝才会回调: 切记切记. 一键支付文档里有写,这与投资通是不同的
     * post 易宝后台异步回调
     * get  用户手动点击回调
     */
    public function actionQuickcallurl() {
        //1 数据获取
        $isPost = Yii::$app->request->isPost; //OST表示易宝后台异步调用
        $data = $this->getParam('data');
        $encryptkey = $this->getParam('encryptkey');

        //2 解析数据
        $yeepayData = $this->yeepayQuick->callback($data, $encryptkey);

        Logger::dayLog(
                'yeepaycallback/wrap', 'ispost', $isPost, 'data', $yeepayData
        );

        // 无响应时不处理
        if (empty($yeepayData)) {
            Logger::dayLog(
                    'yeepaycallback/wrap', '响应的数据为空或无法解析', 'data', $data, 'encryptkey', $encryptkey
            );
            exit;
        }
        if (!is_array($yeepayData) || $yeepayData['error_code']) {
            Logger::dayLog(
                    'yeepaycallback/wrap', '响应的数据不合法', 'yeepaydata', $yeepayData
            );
            exit;
        }

        //4 根据易宝返回的订单号检查本数据库是否存在
        $orderModel = YpQuickOrder::model()->getByAidOrderId($yeepayData['orderid']);
        if (empty($orderModel)) {
            Logger::dayLog(
                    'yeepaycallback/quick', '数据库中无orderid', $yeepayData['orderid']
            );
            exit;
        }

        //5  若状态已经更新成功了，则无需要再更新
        if ($orderModel->pay_status != YpQuickOrder::STATUS_PAYOK) {

            //获取状态
            if ($yeepayData['status'] == 1) {
                $pay_status = YpQuickOrder::STATUS_PAYOK;
            } else {
                $pay_status = YpQuickOrder::STATUS_PAYFAIL; // 这种应该不存在，因为只有支付成功才会回调
            }

            //5 保存易宝返回的支付状态到数据库
            $orderModel->pay_status = $pay_status;
            $orderModel->yborderid = (string) $yeepayData['yborderid'];
            $orderModel->bankcardtype = $yeepayData['cardtype'];
            $orderModel->bankcode = $yeepayData['bankcode'];
            $ret = $orderModel->save();

            if (!$ret) {
                Logger::dayLog(
                        'yeepaycallback/quick', '保存数据', $yeepayData, '保存失败', $orderModel->errors
                );
                exit;
            }

            //6 保存到总订单表状态
            $r = $orderModel->upPayorderStatus();
            if (!$r) {
                Logger::dayLog(
                        'yeepaycallback/quick', '保存总订单表失败', $orderModel->errors
                );
                exit;
            }
        }

        // end update status
        if ($isPost) {
            $result = $orderModel->clientNotify();
            if ($result) {
                echo 'SUCCESS';
                exit;
            }
        } else {
            $url = $orderModel->clientBackurl();
            if ($url) {
                header("Location:{$url}");
            }
        }
    }

}

<?php

/**
 * 易宝一键支付回调接口 内部错误码范围2800-2899
 * 易宝投资通回调接口 内部错误码范围2900-2999
 */

namespace app\controllers;

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

    public function actionRongbao($env = 'prod105') {
        Logger::dayLog('rongbao/notify', $env, $this->post());
        $oRbApi = new RbApi($env);
        $params = Yii::$app->request->post();
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
        Logger::dayLog('rongbao/notify', $env, $res);

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
        $client_id = $data[count($data) - 3];
        if(!$client_id ){
            Logger::dayLog('rongbao/notify', $client_id. 'client not found',$data);
            exit;
        }
        $oRemit = Remit::findOne(['client_id'=>$client_id]);
        //$oRemit = Remit::findOne($data[2]);
        if (!$oRemit) {
            Logger::dayLog('rongbao/notify', 'not found', 'order_no', $res['order_no'], 'id', $data[2]);
            exit;
        }
        if (in_array($oRemit->remit_status, [Remit::STATUS_FAILURE, Remit::STATUS_SUCCESS])) {
            echo 'SUCCESS';
            exit;
        }
        $transaction = Yii::$app->db->beginTransaction();
        //5.2 保存查询表中
        $result = $oRemit->saveRspStatus($oRemitStatus->remit_status, $oRemitStatus->rsp_status, $oRemitStatus->rsp_status_text, '', 3);
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
}

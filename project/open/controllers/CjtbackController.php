<?php

/**
 * 畅捷代付异步回调地址
 */

namespace app\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\models\cjt\CjtRemit;
use app\models\cjt\ClientNotify;
use app\modules\api\common\changjie\CjRemit;
use app\modules\api\common\changjie\CjtApi;
use Yii;

class CjtbackController extends ApiController {

    private static $backCode = [
        '0000',//成功
        '2013',//失败-收款行未开通业务
        '3999',//失败-其他错误
    ];
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['notify'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    public function actionNotify() {
        $getData = file_get_contents("php://input");
        Logger::dayLog('cjtback/notify', $getData);
        if(empty($getData)) return false;

        $xml = simplexml_load_string($getData);
        $result = json_decode(json_encode($xml),TRUE);
        if(!isset($result['BODY']['RET_CODE']) || !isset($result['BODY']['TRX_REQ_SN']) || !isset($result['BODY']['AMOUNT'])){
            return false;
        }
        $resCode = $result['BODY']['RET_CODE'];
        $resOrder = $result['BODY']['TRX_REQ_SN'];
        $resMoney = $result['BODY']['AMOUNT'];
        $resMoney = $resMoney / 100;//单位分转成元
        $resText = isset($result['BODY']['ERR_MSG'])?$result['BODY']['ERR_MSG']:'';
        

        if(!in_array($resCode,self::$backCode)){//失败
            Logger::dayLog('cjtback/notify', 'order', $resOrder, 'resCode', $resCode);
            return false;
        }

        $orderInfo = CjtRemit::findOne(array('client_id'=>$resOrder));
        if (!$orderInfo) {
            Logger::dayLog('cjtback/notify', 'not found', 'no_order', $resOrder, 'resCode', $resCode);
            return false;
        }

        if($orderInfo->settle_amount != $resMoney){
            Logger::dayLog('cjtback/notify', '订单金额与支付返回金额不同', '订单金额',$orderInfo->settle_amount, '支付金额', $resMoney);
            return false;
        }

        $retCode = '';
        $channelId = $orderInfo->channel_id;
        $cjRemitobj = new CjRemit;
        $cjApiObj = new CjtApi('prod'.$channelId);
        $trx_code = CjRemit::CJ_NOTIFY_CODE;//代付
        $bodyInfo = [];

        if (in_array($orderInfo->remit_status, [CjtRemit::STATUS_FAILURE, CjtRemit::STATUS_SUCCESS])) {
            $retCode = '0000';//成功
            $resultXml = $cjApiObj->getXmlParam($bodyInfo,$trx_code,$orderInfo->client_id,$retCode);
            echo $resultXml;exit;
        }
        
        if($resCode == '0000'){
            $remitStatus = CjtRemit::STATUS_SUCCESS;
        }else{
            $remitStatus = CjtRemit::STATUS_FAILURE;
        }
        $saveRes = $orderInfo->saveRspStatus($remitStatus, $resCode, $resText, '', '', 3);
        if (!$saveRes) {
            Logger::dayLog('cjtback/notify', 'error', $orderInfo->id, $orderInfo->errors);
            return false;
        }

        $resultXml = $cjApiObj->getXmlParam($bodyInfo,$trx_code,$orderInfo->client_id,$resCode);
        Logger::dayLog('cjtback/notify', 'return', $resultXml);
        // 加入到通知列表中
        $orderInfo->refresh();
        $notifyRes = $cjRemitobj->InputNotify($orderInfo);
        
        echo $resultXml;exit;

    }
}

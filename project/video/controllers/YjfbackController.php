<?php

/**
 * 易极付出款异步回调地址
 */

namespace app\controllers;

use app\common\Logger;
use app\models\yjf\YjfRemit;
use app\modules\api\common\ApiController;
use app\modules\api\common\yjf\Yjfpay;
use Yii;

class YjfbackController extends ApiController {

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['yjfnotify'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    public function actionYjfnotify($env = 'prod4') {
        //1 数据获取
        $postdata = Yii::$app->request->post();
        Logger::dayLog('yjf', 'yjfnotify',$env, $postdata);
         // 无响应时不处理
        if (empty($postdata)) {
            exit;
        }
        $result = (new Yjfpay($env))->verify($postdata);
        if(empty($result)){
            Logger::dayLog('yjf','yjfnotify', '验签失败',$postdata);
            exit;
        }
        //异步回调参数数组
        $no_order = isset($postdata['merchOrderNo'])?$postdata['merchOrderNo']:'';
        $serviceStatus = isset($postdata['serviceStatus'])?$postdata['serviceStatus']:'';
        $resultMessage = isset($postdata['resultMessage'])?$postdata['resultMessage']:'';
        $transAmount = isset($postdata['transAmount'])?$postdata['transAmount']:'';
        //查询是否存在该订单
        if(empty($no_order)){
            return false;
        }
        $oRemit = YjfRemit::findOne(array('req_id'=>$no_order));
        if (!$oRemit) {
            Logger::dayLog('yjf','yjfnotify', 'not found', 'no_order', $no_order, 'req_id', $no_order);
            exit;
        }
        if ($oRemit->settle_amount!=$transAmount) {
            Logger::dayLog('yjf','yjf/backpay', '订单回执金额与订单金额不同', $transAmount,$oRemit->settle_amount);
            exit;
        }
        //判断是否是终态
        if (in_array($oRemit->remit_status, [YjfRemit::STATUS_FAILURE, YjfRemit::STATUS_SUCCESS])) {
            //6 异步回调成功返回状态码
            echo 'success';exit;
        }
        //判断异步返回结果
        if($serviceStatus == 'REMITTANCE_SUCCESS'){
            $remit_status = YjfRemit::STATUS_SUCCESS;
        }else if($serviceStatus == 'REMITTANCE_DEALING'){
            $remit_status = YjfRemit::STATUS_DOING;
        }else if($serviceStatus == 'REMITTANCE_FAIL'){
            $remit_status = YjfRemit::STATUS_FAILURE;
        }
        Logger::dayLog('yjf','serviceStatus', $serviceStatus);
        Logger::dayLog('yjf','remit_status', $remit_status);
        //保存查询表中
        $result = $oRemit->saveRspStatus($remit_status, $serviceStatus, $resultMessage,'','',3);
        Logger::dayLog('yjf','saveRspStatus', $result);
        if (!$result) {
            Logger::dayLog('yjf','saveRspStatus', 'error', $oRemit->id, $oRemit->errors);
            return FALSE;
        }

        //加入到通知列表中
        $result = (new YjfRemit())->InputNotify($oRemit);
        Logger::dayLog('yjf','InputNotify', $result);
        if (!$result) {
            return FALSE;
        }
        // 异步回调成功返回状态码
        echo 'success';exit;
    }
}

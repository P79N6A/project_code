<?php

/**
 * 连连代付异步回调地址
 */

namespace app\controllers;

use app\common\Logger;
use app\models\lian\LLRemit;
use app\modules\api\common\ApiController;
use app\modules\api\common\llpay\LLpay;
use Yii;

class LianbackController extends ApiController {

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['liannotify'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    public function actionLiannotify($env = 'prod4') {
        $str = file_get_contents("php://input");
        Logger::dayLog('lianback/liannotify', $env, $str);
        if(empty($str)) return false;
        $ollApi = new LLpay($env);
        //@todo Something 传参
        $result = $ollApi->verifyNotify($str);
        if(!$result){
            Logger::dayLog('lianback/notify', 'verifyNotify', '验签失败', $str);
            exit;
        }
        //异步回调参数数组
        $res = $ollApi->getNotifyResp();
        $no_order = $res['no_order'];//订单号
        $result_pay = $res['result_pay'];//支付结果，SUCCESS：为支付成功
        $info_order = $res['info_order'];//订单描述，如果支付结果失败，则记录失败原因
        $money_order = $res['money_order'];//支付金额
        //查询是否存在该订单
        //@todo Something判断$no_order是否为空
        if(empty($no_order)){
            return false;
        }
        $oRemit = LLRemit::findOne(array('req_id'=>$no_order));
        if (!$oRemit) {
            Logger::dayLog('lianback/notify', 'not found', 'no_order', $no_order, 'req_id', $no_order);
            exit;
        }
        if($oRemit->settle_amount!=$money_order){
            Logger::dayLog('lianback/notify', '订单金额与支付返回金额不同', '订单金额',$oRemit->settle_amount, '支付金额', $money_order);
            exit;
        }
        //判断是否是终态
        if (in_array($oRemit->remit_status, [LLRemit::STATUS_FAILURE, LLRemit::STATUS_SUCCESS])) {
            //6 异步回调成功返回状态码
            return json_encode(['ret_code' => '0000', 'ret_msg' => '交易成功',],JSON_UNESCAPED_UNICODE);
        }
        $rsp_status = $result_pay;
        //判断异步返回结果
        if($result_pay == 'SUCCESS'){
            $remit_status = LLRemit::STATUS_SUCCESS;
            $rsp_status_text = "出款成功";
        }else{
            $remit_status = LLRemit::STATUS_FAILURE;
            $rsp_status_text = $info_order;
        }
        //保存查询表中
        $result = $oRemit->saveRspStatus($remit_status, $rsp_status, $rsp_status_text, '','','', 3);
        if (!$result) {
            Logger::dayLog('lianback/liannotify', 'error', $oRemit->id, $oRemit->errors);
            return FALSE;
        }
//@todo Something 通知客户端 根据通知结果 判断插入的通知信息状态
        //加入到通知列表中
        $result = (new LLRemit())->InputNotify($oRemit);
        if (!$result) {
            return FALSE;
        }
        
        // 异步回调成功返回状态码
        return json_encode([
            'ret_code' => '0000',
            'ret_msg' => '交易成功',
        ], JSON_UNESCAPED_UNICODE);
    }
}

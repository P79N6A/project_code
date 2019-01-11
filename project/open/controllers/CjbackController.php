<?php

/**
 * 畅捷代付异步回调地址
 */

namespace app\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\models\cjt\CjtRemit;
use app\models\cjt\ClientNotify;
use app\modules\api\common\cjremit\CjRemit;
use app\modules\api\common\cjremit\CjtApi;
use Yii;
use yii\helpers\ArrayHelper;

class CjbackController extends ApiController {
    const WITHDRAWAL_SUCCESS = 'WITHDRAWAL_SUCCESS'; //成功
    const WITHDRAWAL_FAIL = 'WITHDRAWAL_FAIL'; //失败
    const RETURN_TICKET = 'RETURN_TICKET'; //失败-提现退票
    private static $backCode = [
        self::WITHDRAWAL_SUCCESS,//成功
        self::WITHDRAWAL_FAIL,//失败-
        self::RETURN_TICKET,//失败-提现退票
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

    /**
     * 按aid取不同的配置
     * @param  int  $channel_id 用于区分不同的商编
     * @return RbApi
     */
    private function getApi($channel_id) {
        static $map = [];
        $is_prod = SYSTEM_PROD;
        $is_prod = true;
        $env = $is_prod ? 'prod' . $channel_id : 'dev';
        if (!isset($map[$channel_id])) {
            $map[$channel_id] = new CjtApi($env);
        }
        return $map[$channel_id];
    }
    public function actionNotify() {
        $postData = $this->post();

        Logger::dayLog('cjremit/cjtback','notify', $postData);
        if(empty($postData)) return false;

        $sign = ArrayHelper::getValue($postData,'sign','');     //签名

        $fail_reason = ArrayHelper::getValue($postData,'fail_reason','');    //失败原因
        $gmt_withdrawal = ArrayHelper::getValue($postData,'gmt_withdrawal','');    //提现时间 格式：yyyyMMddHHmmss
        if(!empty($gmt_withdrawal)){
            $gmt_withdrawal = date('Y-m-d H:i:s', strtotime($gmt_withdrawal));
        }
        $inner_trade_no = ArrayHelper::getValue($postData,'inner_trade_no','');       //第三方返回的提现订单号
//        $notify_type = ArrayHelper::getValue($postData,'notify_type','');     //通知类型  trade_status_sync
        $outer_trade_no = ArrayHelper::getValue($postData,'outer_trade_no','');       //提现订单号---咱们传过去的
        $return_code = ArrayHelper::getValue($postData,'return_code','');    //返回码
        $withdrawal_amount = ArrayHelper::getValue($postData,'withdrawal_amount','');    //提现金额
        $withdrawal_status = ArrayHelper::getValue($postData,'withdrawal_status','');    //提现状态

        // 1。验签
//        $channel_id = $this->get('xhh_code_id','174');
//        $oCjtApi = $this->getApi($channel_id); //因为使用的公钥都是同一个  随便写一个通道id就行
//        $result = $oCjtApi->singVerification($postData,$sign);
//        if(!$result){
//            Logger::dayLog('cjremit/cjtback','error','singError验签失败',$postData);
//            echo 'false';die;   //验签失败
//        }
        //2。根据订单号查询信息--判断状态
        $orderInfo = CjtRemit::findOne(array('client_id'=>$outer_trade_no));
        if(!is_object($orderInfo)){
            Logger::dayLog('cjremit/cjtback','error', '查询不到相对应的数据','订单号'.$outer_trade_no);
            return false;
        }
        if($orderInfo->remit_status == CjtRemit::STATUS_SUCCESS || $orderInfo->remit_status == CjtRemit::STATUS_FAILURE){
            Logger::dayLog('cjremit/cjtback','error', '该订单状态已经为最终状态','订单号'.$outer_trade_no);
            echo 'success';die;
        }

        //3。判断金额
        if($orderInfo->settle_amount != $withdrawal_amount){
            Logger::dayLog('cjremit/cjtback','error','订单金额与支付返回金额不同','订单号'.$outer_trade_no,'订单金额',$orderInfo->settle_amount, '支付金额', $withdrawal_amount);
            return false;
        }
        if(!in_array($withdrawal_status,self::$backCode)){//失败
            Logger::dayLog('cjremit/cjtback','error','状态错误', '订单号:'.$inner_trade_no , '状态:'. $withdrawal_status);
            return false;
        }

        //4。判断状态，并通知
        if($withdrawal_status == self::WITHDRAWAL_SUCCESS){     //成功
            $saveRes = $orderInfo->saveRspStatus(CjtRemit::STATUS_SUCCESS, $return_code, $fail_reason, $gmt_withdrawal, $inner_trade_no, 3);
            if (!$saveRes) {
                Logger::dayLog('cjtback/notify', 'error', $orderInfo->id, $orderInfo->errors);
                return false;
            }
            $res = $this->addNotify($orderInfo);
            if(!$res){
                return false;
            }
            echo 'success';die;
        }
        if($withdrawal_status == self::WITHDRAWAL_FAIL || $withdrawal_status == self::RETURN_TICKET){     //失败
            $saveRes = $orderInfo->saveRspStatus(CjtRemit::STATUS_FAILURE, $return_code, $fail_reason, $gmt_withdrawal, $inner_trade_no, 3);
            if (!$saveRes) {
                Logger::dayLog('cjtback/notify', 'error', $orderInfo->id, $orderInfo->errors);
                return false;
            }
            $res = $this->addNotify($orderInfo);
            if(!$res){
                return false;
            }
            echo 'success';die;
        }
        Logger::dayLog('cjremit/cjtback','error','未知错误', '订单号:'.$inner_trade_no );
        return false;

    }

    /**
     *  封装一下通知
     * @param $orderInfo
     * @return bool
     */
    public function addNotify($orderInfo){
        $cjRemitobj = new CjRemit;
        $notifyRes = $cjRemitobj->InputNotify($orderInfo);
        if($notifyRes){
            Logger::dayLog('cjremit/cjtback','notifyError', '通知失败，订单号：'.$orderInfo->client_id);
            return false;
        }
        return true;
    }
}

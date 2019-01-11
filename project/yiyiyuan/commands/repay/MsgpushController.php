<?php

namespace app\commands\repay;

use app\commonapi\Logger;
use app\commonapi\sms\CSms;
use app\models\news\SmsSend;
use yii\helpers\ArrayHelper;

/**
 * 出款发短信通知
 */

/**
 *   linux : /data/wwwroot/yiyiyuan/yii remit/msgpush runNotify
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii repay/msgpush runNotify
 */
class MsgpushController extends \app\commands\BaseController {

    /**
     * 出款通知
     *
     * @return str
     */
    public function runNotify() {
        $initRet = $this->_runNotify();
        print_r($initRet);
    }

    /**
     * 出款通知
     * @return []
     */
    private function _runNotify() {
        //1. 查询要处理的通知
        $initRet = ['total' => 0, 'success' => 0];
        $smsModel = new SmsSend();
        $notifys = $smsModel->getInitData(1000);
        if (!$notifys) {
            Logger::dayLog("repay/msgpush", "无数据");
            return $initRet;
        }
        //2 悲观锁定状态
        $ids = ArrayHelper::getColumn($notifys, 'id');
        $ups = $smsModel->lockNotifys($ids);
        if (!$ups) {
            Logger::dayLog("repay/msgpush", "锁定失败");
            return $initRet;
        }
        //3 计算处理总数
        $initRet['total'] = count($ids);

        //4批量处理
        $arrData = [];
        foreach ($notifys as $oNotify){
            $arrData[] = [
                'phone' => $oNotify->mobile,
                'content' => $oNotify->content,
                'channel_code' => $oNotify->channel,
            ];
        }
        $smsApi = new CSms();
        $ret =$smsApi->sendMarketingSms($arrData);
        if($ret['rsp_code'] == '0000'){
            $smsModel->successs($ids);
            $initRet['success'] = count($ids);
        }else{
            $smsModel->fails($ids);
        }

        return $initRet;
    }

}

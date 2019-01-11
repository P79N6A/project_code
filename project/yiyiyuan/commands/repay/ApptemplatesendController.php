<?php

namespace app\commands\repay;

use app\commonapi\Logger;
use app\commonapi\sms\CSms;
use app\models\news\UmengSend;
use yii\helpers\ArrayHelper;

/**
 * app个推
 */

/**
 *   linux : /data/wwwroot/yiyiyuan/yii repay/apptemplatesend
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii  repay/apptemplatesend
 */
class ApptemplatesendController extends \app\commands\BaseController
{

    public function actionIndex()
    {
        $error_num = 0;
        $success_num = 0;
        $umengModel = new UmengSend();
        $notifys = $umengModel->getInitData(1000);
        if (!$notifys) {
            Logger::dayLog("repay/appPush", "无数据");
            echo 'NO DATA';exit();
        }
        //2 悲观锁定状态
        $ids = ArrayHelper::getColumn($notifys, 'id');
        $ups = $umengModel->lockNotifys($ids);
        if (!$ups) {
            Logger::dayLog("repay/appPush", "锁定失败");
            echo 'LOCK REEOR';exit();
        }
        //3 计算处理总数
        $total_num = count($ids);

        //4批量处理
        foreach ($notifys as $key => $val) {
            //发送微信模板信息1:IOS 2:安卓
            $device_type = $val->device_type;
            $content = [];
            if ($device_type == 2) {
                $content['display_type'] = 'notification';
                $content['body']['ticker'] = $val->title;
                $content['body']['title'] = $val->title;
                $content['body']['text'] = $val->content;
                $content['body']['after_open'] = 'go_app';
            } elseif ($device_type == 1) {
                $content['aps']['alert'] = $val->content;
            }
            $ncontent = json_encode($content, JSON_UNESCAPED_UNICODE);
            $device_tokens = $val->device_token;
            if(!$device_tokens){
                continue;
            }
            $order_id = $val->id;
            $ret = $this->sendAppTemplate($device_type, $device_tokens, $ncontent, $order_id);
            if (!$ret) {
                $val->saveFail();
                $error_num++;
                Logger::dayLog("repay/appPush", "app推送失败", $val);
            }else{
                $val->saveSuccess();
                $success_num++;
            }
        }

        echo "all: " . $total_num . " success_num: " . $success_num . " error_num: " . $error_num;
    }

    /**
     * 发送app消息推送
     * @param $device_type
     * @param $device_tokens
     * @param $content
     * @param $order_id
     * @return bool
     */
    private function sendAPPTemplate($device_type, $device_tokens, $content, $order_id)
    {
        $data['biz_code']      = 1;
        $data['device_type']   = $device_type == 2 ? 'android' : 'ios';
        $data['type']          = 'unicast';
        $data['device_tokens'] = $device_tokens;
        $data['payload']       = $content;
        $data['biz_msg_id']    = $order_id;
        Logger::dayLog("repay/appPushMsg", "app推送参数", $data);
        $amppush = new CSms();
        $result  = $amppush->unicast($data);
        if ($result['rsp_code'] == '0000') {
            return true;
        } else {
            return false;
        }

    }

}


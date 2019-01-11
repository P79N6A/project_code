<?php

namespace app\commands;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\Sms;
use app\models\dev\User;
use app\models\dev\User_extend;
use yii\console\Controller;

/**
 * 借款筹款大于6小时，定时更改借款状态
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii setloanstatus > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe E:\www\yiyiyuan\yii setloanstatus
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class CallbacksmsController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $total = User_extend::find()->where(['is_callback' => 1])->count();
        $limit = 10000;
        $pages = ceil($total / $limit);
        $error_num = 0;
        for ($i = 0; $i < $pages; $i++) {
            $allextend = User_extend::find()->where(['is_callback' => 1])->limit($limit)->all();
            if (!empty($allextend)) {
                foreach ($allextend as $key => $value) {
                    //2. 先花宝 投资总额度 减少 
                    $user = User::findOne($value->user_id);
                    if (!empty($user)) {
                        $this->sendSms($user->mobile);
                        $result = $allextend[$key]->updateRecord(['is_callback' => 0, 'user_id' => $value->user_id]);
                        if (!$result) {
                            $error_num++;
                        }
                    } else {
                        $allextend[$key]->delete();
                    }
                }
            } else {
                break;
            }
        }
        if ($error_num > 0) {
            Logger::errorLog(print_r(array($error_num . "条召回短信发送失败"), true), 'callback_error', 'crontab');
        }
        if ($total - $error_num > 0) {
            Logger::errorLog(print_r(array($total - $error_num . "条召回短信已经发送成功"), true), 'callback', 'crontab');
        }
    }

    /**
     * 借款在线还款结果短信通知用户
     * @param type $mobile 接收短信的手机号
     * @param type $loan 借款
     * @param type $type 1、支付成功，2、支付失败
     */
    private function sendSms($mobile) {

        $sms = new Sms();
        $content = '尊敬的用户您的借款已经通过初步借款审核，请下载app http://t.cn/RtSvMgW 查看借款进度，及时完成借款。感谢您使用先花一亿元，有问题请咨询微信人工客服：先花一亿元(xianhuayyy)，都来先花花，一起有钱花。';
        $sms->content = $content;
        $sms->recive_mobile = $mobile;

        $sendRet = Http::sendByMobile($mobile, $content);
        if ($sendRet) {
            //将发送的验证码保存在redis里
            $sms->create_time = date('Y-m-d H:i:s', time());
            $sms->sms_type = 30;
            $sms->save();
        }
    }

//    private function 
    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

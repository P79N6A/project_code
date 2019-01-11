<?php

namespace app\commands\msg;

use app\commonapi\ApiSms;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\sms\CSms;
use app\models\news\Accesstoken;
use app\models\news\WarnMessageList;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

require(dirname(dirname(dirname(__FILE__))) . '/' . 'notification/android/AndroidUnicast.php');
require(dirname(dirname(dirname(__FILE__))) . '/' . 'notification/android/AndroidListcast.php');
require(dirname(dirname(dirname(__FILE__))) . '/' . 'notification/ios/IOSUnicast.php');
require(dirname(dirname(dirname(__FILE__))) . '/' . 'notification/ios/IOSListcast.php');

/**
 * 放款成功短信和push通知  WarnmessageController.php  定时任务
 */
//避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendmessageController extends Controller {

    private $android_appkey = "562de3dd67e58ed70f0003b7";
    private $android_appMasterSecret = "ztxpdi71xf5m2eu4bgnpdio3revsmvbh";
    private $iso_appkey = "5670e38267e58e8d7d001669";
    private $iso_appMasterSecret = "6g0n49fd4wmmqmldhxb6vs366stzy4bc";
    private $down_app = 'http://t.cn/R4K2tn5 ';

    // 命令行入口文件
    public function actionIndex() {
        //出款五分钟
        $limit = 500;
        $warn = WarnMessageList::find()->where(['status' => 0]);

        $total = $warn->count();
        $pages = ceil($total / $limit);
        $succ = 0;
        $error = 0;
        for ($i = 0; $i < $pages; $i++) {
            $warnlist = $warn->limit($limit)->all();
            if (!empty($warnlist)) {
                $ids = ArrayHelper::getColumn($warnlist, 'id');
                $lock = (new WarnMessageList())->changeLock($ids);
                $res = $this->send($warnlist);
                $succ += $res['succ'];
                $error += $res['error'];
                $this->log("\n all:{$total},SUCCESS:{$res['succ']},EROOR:{$res['error']},pages:{$i}\n");
            }
        }
        Logger::dayLog('sendtotal', 'SUCCESS:' . $succ . ',FAIL:' . $error);
    }

    private function send($warnlist) {
        $succ = 0;
        $error = 0;
        foreach ($warnlist as $key => $val) {
            $val->changeOneLock();
            switch ($val->channel) {
                case 1:
                    $result = $this->sendsmspush($val);
                    if ($result) {
                        $val->changeSuccess();
                        $succ++;
                    } else {
                        $val->changeFail();
                        $error++;
                    }
                    break;
                case 2:
                    $result = $this->sendapppush($val);
                    if ($result) {
                        $val->changeSuccess();
                        $succ++;
                    } else {
                        $val->changeFail();
                        $error++;
                    }
                    break;
                case 3:
                    $result = $this->sendwxpush($val);
                    if ($result) {
                        $val->changeSuccess();
                        $succ++;
                    } else {
                        $val->changeFail();
                        $error++;
                    }
                    break;
                case 4:
                    $result = $this->sendsmspush($val);
                    $result1 = $this->sendapppush($val);
                    if ($result && $result1) {
                        $val->changeSuccess();
                        $succ++;
                    } else {
                        $val->changeFail();
                        $error++;
                    }
                    break;
                case 5:
                    $result = $this->sendsmspush($val);
                    $result1 = $this->sendwxpush($val);
                    if ($result && $result1) {
                        $val->changeSuccess();
                        $succ++;
                    } else {
                        $val->changeFail();
                        $error++;
                    }
                    break;
                case 6:
                    $result = $this->sendapppush($val);
                    $result1 = $this->sendwxpush($val);
                    if ($result && $result1) {
                        $val->changeSuccess();
                        $succ++;
                    } else {
                        $val->changeFail();
                        $error++;
                    }
                    break;
                case 7:
                    $result = $this->sendsmspush($val);
//                    $result = 1;
                    $result1 = $this->sendapppush($val);
//                    $result1 = 1;
                    $result2 = $this->sendwxpush($val);
                    if ($result && $result1 && $result2) {
                        $val->changeSuccess();
                        $succ++;
                    } else {
                        $val->changeFail();
                        $error++;
                    }
                    break;
            }
        }
        return ['succ' => $succ, 'error' => $error];
    }

    private function sendsmspush($message) {
        $model = new ApiSms();
        $result = $model->choiceChannel($message->user->mobile, $message->contact, $message->type, '', 3);
        if (!$result) {
            return FALSE;
        }
        return TRUE;
    }

    private function sendapppush($message) {
        $user = $message->user;
        $password = $user->password;
        if (empty($password)) {
            return TRUE;
        }
        $nowtime = date('His');
        $result = $this->sendAPPTemplate($message, $password->device_type, $password->device_tokens, $message->id . $nowtime);
        return TRUE;
    }

    private function sendwxpush($message) {
        $user = $message->user;
        if (!empty($user->openid)) {
            $this->sendTemplete($user->openid, $message->contact);
            return TRUE;
        }
        return FALSE;
    }

    private function sendTemplete($openid, $content) {
        //推送客服消息给借款人
        $data = '{
				    		"touser":"' . $openid . '",
				    		"msgtype":"text",
				   	 		"text":
				    		{
				         		"content":"' . $content . '"
				    		}
								}';
        $resultno = $this->sendSmsBykefus($data);
        if ($resultno) {
            $result = json_decode($resultno);
            Logger::dayLog('wxsms', $result);
        }
    }

    public function sendSmsBykefus($data) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccessToken();
        $menu = Http::dataPost($data, $url);
        return $menu;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

    /**
     * 发送app消息推送
     * @param $device_type
     * @param $device_tokens
     * @param $content
     * @param $order_id
     * @return bool
     */
    private function sendAPPTemplate($message, $device_type, $device_tokens, $order_id) {
        $content = [];
        if ($device_type == 'android') {
            $content['display_type'] = 'notification';
            $content['body']['ticker'] = $message->title;
            $content['body']['title'] = $message->title;
            $content['body']['text'] = $message->contact;
            if ($message->back_action == 3 && !empty($message->back_url)) {
                $content['body']['after_open'] = 'go_custom';
                $array = [
                    'type' => 1,
                    'warn_id' => $message->id,
                    'url' => $message->back_url
                ];
                $content['body']['custom'] = $array;
            } elseif ($message->back_action == 2 && !empty($message->relation_id)) {
                $content['body']['after_open'] = 'go_custom';
                $array = [
                    'type' => 2,
                    'warn_id' => $message->id,
                    'msg_type' => 1,
                    'msg_id' => $message->relation_id
                ];
                $content['body']['custom'] = $array;
            } else {
                $content['body']['after_open'] = 'go_custom';
                $array = [
                    'type' => 3,
                    'warn_id' => $message->id
                ];
                $content['body']['custom'] = $array;
            }
        } elseif ($device_type == 'ios') {
            $content['aps']['alert'] = $message->contact;
            if ($message->back_action == 3 && !empty($message->back_url)) {
                $array = [
                    'type' => 1,
                    'warn_id' => $message->id,
                    'url' => $message->back_url
                ];
                $content['url'] = json_encode($array ,JSON_UNESCAPED_UNICODE);
            } elseif ($message->back_action == 2 && !empty($message->relation_id)) {
                $array = [
                    'type' => 2,
                    'warn_id' => $message->id,
                    'msg_id' => $message->relation_id
                ];
                $content['url'] = json_encode($array ,JSON_UNESCAPED_UNICODE);
            }else{
                $array = [
                    'type' => 3,
                    'warn_id' => $message->id,
                ];
                $content['url'] = json_encode($array ,JSON_UNESCAPED_UNICODE);
            }
        } else {
            return TRUE;
        }
        $ncontent = json_encode($content, JSON_UNESCAPED_UNICODE);
        $device_tokens = $device_tokens;
        if (!$device_tokens) {
            return FALSE;
        }
        $data['biz_code'] = 1;
        $data['device_type'] = $device_type;
        $data['type'] = 'unicast';
        $data['device_tokens'] = $device_tokens;
        $data['payload'] = $ncontent;
        $data['biz_msg_id'] = $order_id;
        $amppush = new CSms();
        $result = $amppush->unicast($data);
        Logger::dayLog("appPushMsg", "app推送参数", $data, $result);
        if (isset($result['rsp_code']) && $result['rsp_code'] == '0000') {
            return true;
        } else {
            return false;
        }
    }

    //获取access_token值
    public function getAccessToken() {
        $appId = Yii::$app->params['AppID']; //，需要在微信公众平台申请自定义菜单后会得到
        $appSecret = Yii::$app->params['AppSecret']; //需要在微信公众平台申请自定义菜单后会得到
        //先查询对应的数据表是否有token值
        $access_token = Accesstoken::find()->where(['type' => 1])->one();
        if (isset($access_token->access_token)) {
            //判断当前时间和数据库中时间
            $time = time();
            $gettokentime = $access_token->time;
            if (($time - $gettokentime) > 7000) {
                //重新获取token值然后替换以前的token值
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
                $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
                $resultArr = json_decode($data, true); //转为数组
                $accessToken = $resultArr["access_token"]; //获取access_token
                //替换以前的token值
                $sql = "update yi_access_token set access_token = '$accessToken',time=$time where type=1";
                $result = Yii::$app->db->createCommand($sql)->execute();

                return $accessToken;
            } else {
                return $access_token->access_token;
            }
        } else {
            //获取token值并把token值保存在数据表中
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
            $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
            $resultArr = json_decode($data, true); //转为数组
            $accessToken = $resultArr["access_token"]; //获取access_token

            $time = time();
            $sql = "insert into " . Accesstoken::tableName() . "(access_token,time) value('$accessToken','$time')";
            $result = Yii::$app->db->createCommand($sql)->execute();

            return $accessToken;
        }
    }

}

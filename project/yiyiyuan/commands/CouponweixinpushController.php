<?php

/**
 * 微信消息发放
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii income > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\Accesstoken;
use app\models\news\Mobile;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class CouponweixinpushController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $start_time = date('Y-m-d H:i:s');
        $condition = [
            'AND',
            [Mobile::tableName() . ".type" => '57'],
            ["<=", Mobile::tableName() . ".send_time", $start_time],
            [Mobile::tableName() . ".status" => '0'],
        ];
        $total = Mobile::find()->where($condition)->count();
        $limit = 100;
        $pages = ceil($total / $limit);
        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("\ntotal:{$total}条数据:每次处理limit:{$limit},需要要处理page:{$pages}次\n");
//        $template_id = 'zhjk_RQFiOixjCKFlHB_wBDekfJyUb8VnKOPRge0uXA';//测试魔板ID
        $template_id = 'BA_ZknRPh5noI3PTayFwr6N38iSAQlEx4ghGlAuG6EM'; //正式魔板ID
        for ($i = 0; $i < $pages; $i++) {
            $Mobiles = Mobile::find()
                    ->joinWith('user', true, 'LEFT JOIN')
                    ->where($condition)
                    ->limit($limit)
                    ->all();
            if (empty($Mobiles)) {
                break;
            }
            $ids = ArrayHelper::getColumn($Mobiles, 'id');
            $lock = (new Mobile())->lockAll($ids);
            foreach ($Mobiles as $key => $value) {
                //锁字段
                $value->lock();
                if (empty($value->user->openid)) {
                    continue;
                }
                $result = $this->sendWeixinTemplate($value->user->openid, $template_id);
                if ($result) {
                    $value->success();
                } else {
                    $value->fail();
                }
            }
        }
    }

    //微信模板推送
    private function sendWeixinTemplate($openid, $template_id) {
//        http://mp.yaoyuefu.com/borrow/index?utm_source=tmpMsg&utm_campaign=time_test&utm_content=100920
        $htime = date('mdH');
        $url = Yii::$app->params['app_url'] . "/borrow/index?utm_source=tmpMsg&utm_campaign=time_test&utm_content=" . $htime;

        $nowtime = date('Y' . '年' . 'm' . '月' . 'd' . '日',time()+86400);
        $data = '{
                                               "touser":"' . $openid . '",
                                               "template_id":"' . $template_id . '",
                                               "url":"' . $url . '",
                                               "topcolor":"#FF0000",
                                               "data":{
                                                                "first": {
                                                                          "value":"您已成功获得一亿元500元额度，将在24小时后到期，请您尽快使用。若您保持良好信用，额度将会稳步提升。",
                                                                          "color":"#173177"
                                                                         },
                                                                  "keyword1":{
                                                                           "value":"500-1000元",
                                                                           "color":"#173177"
                                                                  		 },
                                                                  "keyword2": {
                                                                            "value":"' . $nowtime . '",
                                                                            "color":"#173177"
                                                                          },
                                                                  "remark":{
                                                                             "value":"点击此卡片立即查看额度。",
                                                                              "color":"#173177"
                                                                             }
                                                         }
                                     }';
        //print_r($data);exit;
        $resulttemplate = $this->sendTemplatetouser($data);
        Logger::errorLog(print_r($resulttemplate, true), 'couponusendtemplatetouser');

        return true;
    }

    private function sendTemplatetouser($data) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->getAccessToken();
        $result = Http::dataPost($data, $url);
        return $result;
    }

    private function getAccessToken() {
        $appId = \Yii::$app->params['AppID']; //，需要在微信公众平台申请自定义菜单后会得到
        $appSecret = \Yii::$app->params['AppSecret']; //需要在微信公众平台申请自定义菜单后会得到
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
                if (isset($resultArr["access_token"])) {
                    $accessToken = $resultArr["access_token"]; //获取access_token
                    //替换以前的token值
                    $sql = "update yi_access_token set access_token = '$accessToken',time=$time where type=1";
                    $result = Yii::$app->db->createCommand($sql)->execute();

                    return $accessToken;
                } else {
                    return $access_token->access_token;
                }
            } else {
                return $access_token->access_token;
            }
        } else {
            //获取token值并把token值保存在数据表中
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
            $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
            $resultArr = json_decode($data, true); //转为数组
            if (isset($resultArr["access_token"])) {
                $accessToken = $resultArr["access_token"]; //获取access_token

                $time = time();
                $sql = "insert into " . Accesstoken::tableName() . "(access_token,time) value('$accessToken','$time')";
                $result = Yii::$app->db->createCommand($sql)->execute();
                return $accessToken;
            } else {
                return false;
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

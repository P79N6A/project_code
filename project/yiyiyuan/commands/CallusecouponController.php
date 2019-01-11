<?php

/**
 * 优惠券发放
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
use app\models\dev\Coupon_list;
use app\models\dev\Sms;
use app\models\dev\User;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class CallusecouponController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $time = date('2016-10-14 00:00:00');
        $days = [3, 7, 15, 30];
        $continue_sms = array(
            '3' => '您的账户中还有一张借款优惠券未使用，登陆后马上发起借款30分钟内审核通过到账，还能免息借款，都来先花花，一起有钱花。快来下载先花一亿元app吧http://t.cn/R4K2tn5',
            '7' => '您的账户有一张借款免息券未使用，登陆app发起借款，急速通过还免息，都来先花花，一起有钱花。快来下载先花一亿元app吧http://t.cn/R4K2tn5',
            '15' => '工资不够花？急速借款就来先花一亿元，30分钟下款，还有免息借款优惠券，都来先花花，一起有钱花。快来下载先花一亿元app吧http://t.cn/R4K2tn5',
            '30' => '您的借款优惠券还有12小时就过期作废了，现在马上发起借款享28天免息借款，都来先花花，一起有钱花。快来下载先花一亿元app吧http://t.cn/R4K2tn5',
        );
        $continue_weixin = array(
            '3' => "您的账户中还有一张借款优惠券未使用，登陆后马上发起借款30分钟内审核通过到账，还能免息借款。\n补贴类型：借款免息券\n补贴金额：全免\n都来先花花，一起有钱花，点击<a href='http://mp.yaoyuefu.com/dev/loan'>马上发起借款</a>",
            '7' => "最近手头有点紧？花二哥帮你来解决，1万元随借随还，新用户30秒审核通过，30分钟批款。\n补贴类型：借款免息券\n补贴金额：全免\n都来先花花，一起有钱花，点击<a href='http://mp.yaoyuefu.com/dev/loan'>马上发起借款</a>",
            '15' => "工资不够花？急速借款就来先花一亿元，30分钟下款，还有免息借款优惠券。\n补贴类型：借款免息券\n补贴金额：全免\n都来先花花，一起有钱花，点击<a href='http://mp.yaoyuefu.com/dev/loan'>马上发起借款</a>",
            '30' => "您的借款优惠券还有12小时就过期作废了，现在马上发起借款享28天免息借款。\n补贴类型：借款免息券\n补贴金额：全免\n都来先花花，一起有钱花，点击<a href='http://mp.yaoyuefu.com/dev/loan'>马上发起借款</a>",
        );
        foreach ($days as $val) {
            $start_time = date('Y-m-d 00:00:00', strtotime('-' . $val . ' days'));
            $end_time = date('Y-m-d 00:00:00', strtotime($start_time) + 24 * 3600);
            if (strtotime($start_time) < strtotime($time)) {
                continue;
            }
            $total = User::find()->joinWith('couponlist', TRUE, 'LEFT JOIN')->where(['>=', User::tableName() . '.create_time', $start_time])->andFilterWhere(['<', User::tableName() . '.create_time', $end_time])->andWhere([Coupon_list::tableName() . '.status' => 1])->count();
            $pages = ceil($total / 1000);
            for ($i = 0; $i < $pages; $i++) {
                $users = User::find()->joinWith('couponlist', TRUE, 'LEFT JOIN')->where(['>=', User::tableName() . '.create_time', $start_time])->andFilterWhere(['<', User::tableName() . '.create_time', $end_time])->andWhere([Coupon_list::tableName() . '.status' => 1])->offset($i * 1000)->limit(1000)->all();
                if (!empty($users)) {
                    foreach ($users as $key => $vals) {
                        if (empty($vals->openid)) {
                            $this->sendSms($vals->mobile, $continue_sms[$val], $val);
                        } else {
                            $this->sendTemplete($vals->openid, $continue_weixin[$val]);
                        }
                    }
                }
            }
        }

//        $this->log("\n处理结果:success{$sucess}个用户发送成功");
    }

    /**
     * 借款在线还款结果短信通知用户
     * @param type $mobile 接收短信的手机号
     * @param type $loan 借款
     * @param type $type 1、支付成功，2、支付失败
     */
    private function sendSms($mobile, $content, $days = 3) {
        $sms = new Sms();
//        $content = '尊敬的用户您的借款已经通过初步借款审核，请下载app http://t.cn/RtSvMgW 查看借款进度，及时完成借款。感谢您使用先花一亿元，有问题请咨询微信人工客服：先花一亿元(xianhuayyy)，都来先花花，一起有钱花。';
        $sms->content = $content;
        $sms->recive_mobile = $mobile;

        $sendRet = Http::sendByMobile($mobile, $content);
        switch ($days) {
            case 3:
                $sms_type = 31;
                break;
            case 7:$sms_type = 32;
                break;
            case 15:$sms_type = 33;
                break;
            case 30:$sms_type = 34;
                break;
            default :$sms_type = 31;
        }
        if ($sendRet) {
            //将发送的验证码保存在redis里
            $sms->create_time = date('Y-m-d H:i:s', time());
            $sms->sms_type = $sms_type;
            $sms->save();
        }
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
            Logger::errorLog(print_r($result, true), 'callusecoupon', 'crontab');
        }
    }

    public function sendSmsBykefus($data) {
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccessToken();
        $menu = Http::dataPost($data, $url);
        return $menu;
    }

    //获取access_token值
    public function getAccessToken() {
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

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

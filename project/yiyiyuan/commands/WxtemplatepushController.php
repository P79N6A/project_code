<?php

namespace app\commands;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\Accesstoken;
use app\models\news\WxTemplateList;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class WxtemplatepushController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $time_now = date('Y-m-d H:i:s');
        $condition = [
            'AND',
            ["<=", "send_time", $time_now],
            ["status" => '0']
        ];
        $total = WxTemplateList::find()->where($condition)->count();
        $limit = 200;
        $pages = ceil($total / $limit);
        $this->log( "\n". date('Y-m-d H:i:s') . "......................");
        $this->log("\ntotal:{$total}条数据:每次处理limit:{$limit},需要要处理page:{$pages}次\n");

        for ($i = 0; $i < $pages; $i++) {
            $lists = WxTemplateList::find()
                ->where($condition)
                ->limit($limit)
                ->all();
            if (empty($lists)) {
                break;
            }
            $ids = ArrayHelper::getColumn($lists, 'id');
            $lock = (new WxTemplateList())->lockAll($ids);
            foreach ($lists as $key => $value) {
                //锁字段
                $value->lock();
                if(empty($value->openid)){
                    continue;
                }
                $result = $this->send($value);
                if($result){
                    $value->updateList(['status' => 2]);
                }else{
                    $value->updateList(['status' => 3]);
                }
            }
        }
    }

    private function send($wxTemplateList){
        $template_obj = $wxTemplateList->template;
        $data = json_decode($template_obj->data,true);
        $msg = [
            'touser' => $wxTemplateList->openid,
            'template_id' => $template_obj->template_id,
            'url' => $template_obj->url,
            'data' => $data
        ];

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->getAccessToken();
        $result = Http::interface_post_json($url,$msg);

        Logger::dayLog('wx_template_push/',$wxTemplateList->id,$result);

        $ret = @json_decode($result,true);
        if($ret && isset($ret['errcode']) && $ret['errcode']==0){
            return true;
        }
        return false;
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

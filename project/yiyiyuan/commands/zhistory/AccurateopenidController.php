<?php
/**
 *
 * 精准营销定时--openid存在微信推送
 *    d:\xampp\php\php.exe d:\www\yiyiyuan\yii longnoloan
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/14
 * Time: 10:38
 */
namespace app\commands;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\Accesstoken;
use app\models\dev\Coupon_apply;
use app\models\news\AccurateOpenId;
use app\models\news\User;
use yii\console\Controller;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');
class AccurateopenidController extends Controller
{
    private $down_app = 'http://t.cn/R4K2tn5 ';
    private $accurate_open_id_object;

    public function actionIndex()
    {
        $start = time();
        $this->logicOpenId();
        $end = time() - $start;
        Logger::errorLog(print_r(array('total'=>$end), true), 'Incompletedatauser', 'precision');
    }
    private function logicOpenId()
    {
        $start_time = date("Y-m-d 00:00:00", time());
        $end_time = date("Y-m-d H:i:s", time());
        $whereconfig = [
            'AND',
            ['is_coupon' => 0],
            ['between', 'create_time', $start_time, $end_time],
        ];
        $sql = AccurateOpenId::find()->where($whereconfig);
        $total = $sql->count();
        $limit = 500;
        $pages = ceil($total / $limit);
        for($i=0; $i<$pages;$i++){
            $open_id_data = $sql->limit($limit)->asArray()->select('id, user_id, openid, sms_type')->all();
            if (!empty($open_id_data)){
                $user_id_string = Common::ArrayToString($open_id_data, 'user_id');
                AccurateOpenId::updateAll(['is_coupon' => -1], ['is_coupon' => 0, 'user_id' => explode(',', $user_id_string)]);
                foreach($open_id_data as $value){
                    $user_info = $this->getUserInfo($value['user_id']);
                    if (empty($user_info))
                        continue;
                    $message_coupon_info = $this->formatMessage($value['sms_type'], $user_info);
                    $template_id = "x0pduEg_cw7NxmbQbR3WnOIZ1br3IdBFZOcSo0rDg6Q";
                    if (!empty($message_coupon_info['coupon_list'])){
                        $template_id = "ze-03NH4AESs3I91b5C8SfFxuJKGwPytEnRWoitIV5A";
                    }
                    $ret_weixin = $this->sendWeixinMsg($value['openid'], $message_coupon_info['sms_message'],$template_id);
                    //$ret_weixin = $this->semdWeixinNotice($value['openid'], $message_coupon_info['sms_message']);
                    //$ret_weixin = $this->semdWeixinNotice("oLbbGs8QHNS-Pyah5-Vqpseuxltc", $message_coupon_info['sms_message']);
                    $ccurate_open_id_object = new AccurateOpenId();
                    if ($ret_weixin) {
                        $ccurate_open_id_object->updateIsCoupon($value['id'], 1);
                        if (!empty($message_coupon_info['coupon_list'])) {
                            $coupon_title = $message_coupon_info['coupon_list']['money'] . "元优惠券";
                            $coupon_apply = new Coupon_apply();
                            $coupon_apply->sendcouponactivity($value['user_id'], $coupon_title, 2, $message_coupon_info['coupon_list']['days'], $message_coupon_info['coupon_list']['money']);

                        }
                    }
                }
            }
        }
    }

    /**
     * 获取用户信息
     * @param $user_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    private function getUserInfo($user_id)
    {
        if (empty($user_id)) return false;
        $user_info = User::find()->where(['user_id'=>$user_id])->one();
        if (!empty($user_id)){
            return $user_info;
        }
        return false;
    }

    /**
     * 短信内容格式
     * @param $code
     * @param $user_info
     * @return array
     */
    private function formatMessage($code, $user_info)
    {
        $mobile = empty($user_info->mobile)? "" : substr_replace($user_info->mobile,'****',3,4);
        $amount = $user_info->getUserLoanAmount($user_info);
        $realname = empty($user_info->realname) ? "" : $user_info->realname;
        $msg_code_data = [];
        $curr_time = date("H:i", time());
        switch($code) {
            //1.未完成资料
            case 1://30分钟
                //$msg = "【先花一亿元】亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！提交时如仍有疑问，您可联系客服处理";
                $msg = [
                    'first'=>"亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可联系客服处理",
                ];
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 2://1天
                //$msg = "【先花一亿元】亲，您的1500元贷款额度已到账，补充资料即可领取，立即领取：{$this->down_app}提交时如仍有疑问，您可通过APP联系客服处理，退订回T";
                $msg = [
                    'first'=>"亲，您的1500元贷款额度已到账，补充资料即可领取",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理",
                ];

                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 3://3天
                //$msg = "【先花一亿元】亲，您的5元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，您的5元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现。",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和5元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>5, 'days'=>3]];
                break;
            case 4://7天
                //$msg  = "【先花一亿元】亲，您的10元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，您的10元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现。",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和10元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>10, 'days'=>3]];
                break;
            case 5:///14天
                //$msg  = "【先花一亿元】亲，您的20元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，您的20元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现。",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和20元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>20, 'days'=>3]];
                break;
            case 6://30天
                //$msg  = "【先花一亿元】亲，您的30元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，您的30元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现。 ",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和30元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];

                $msg_code_data = ['sms_message'=>$msg,'coupon_list'=>['money'=>30, 'days'=>3]];
                break;
            //未完成首次借款（驳回用户召回）
            case 7://1天
                //$msg = "【先花一亿元】亲，您有一笔1500元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，您有一笔1500元的贷款额度可以提现，当前有效，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 8:// //3天
                //$msg = "【先花一亿元】亲，30元免息已收到，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，30元免息已收到，领取1500元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和30元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 9://5天
                //$msg = "【先花一亿元】尊敬的{$user_info->realname}，您的1500元现金和30元免息券尚未领取，赶紧领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"您的1500元现金和30元免息券尚未领取，赶紧领取！ ",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和30元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 10://14天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，老用户60元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取。 ",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和60元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 11://30天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，老用户80元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和80元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 12://90天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = [
                    'first'=>"亲，老用户100元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>"1500元信用额度和100元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>100, 'days'=>7]];
                break;
            //长时间未借款
            case 13://1天
                //$msg = "【先花一亿元】亲，您有一笔{$user_info->account->amount}元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app }退订回T";
                $msg = [
                    'first'=>"您有一笔".$amount."元的贷款额度可以提现，当前有效，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>$amount,
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg];
                break;
            case 14:// //3天
                //$msg = "【先花一亿元】亲，30元免息已收到，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = [
                    'first'=>"亲，30元免息已收到，领取".$amount."元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>$amount."元信用额度和30元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 15://5天
                //$msg = "【先花一亿元】尊敬的{$user_info->realname}，您的{$user_info->account->amount}元现金和30元免息券尚未领取，赶紧领取：{$this->down_app }退订回T";
                $msg = [
                    'first'=>"亲，30元免息已收到，领取".$amount."元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>$amount."元信用额度和30元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 16://14天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = [
                    'first'=>"亲，老用户60元免息券已到账 ，领取".$amount."元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>$amount."元信用额度和60元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];

                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 17://30天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = [
                    'first'=>"亲，老用户80元免息券已到账 ，领取".$amount."元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>$amount."元信用额度和80元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];

                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 18://90天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = [
                    'first'=>"亲，老用户100元免息券已到账 ，领取".$amount."元的贷款额度即可免息，赶紧领取。",
                    'keynote1'=>$mobile,
                    'keynote2'=>$amount."元信用额度和100元免息券",
                    'keynote3'=>$curr_time,
                    'remark'=>"提交时如仍有疑问，您可通过APP联系客服处理。",
                ];
                $msg_code_data = ['sms_message'=>$msg, 'coupon_list'=>['money'=>100, 'days'=>7]];
                break;
        }
        return $msg_code_data;
    }

    private function sendWeixinMsg($openid, $content, $template_id)
    {
        //$content = json_encode($content);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->getAccessToken();
        $data = '{
           "touser":"'.$openid.'",
           "template_id":"'.$template_id.'",
           "url":"http://t.cn/R4K2tn5",           
           "data":{
                "first":{
                    "value":"'.$content["first"].'",
                    "color":"#173177"
                },
                "keyword1":{
                            "value":"'.$content["keynote1"].'",
                            "color":"#173177"
                 },
                 "keyword2": {
                    "value":"'.$content["keynote2"].'",
                     "color":"#173177"
                 },
                 "keyword3": {
                    "value":"'.$content["keynote3"].'",
                    "color":"#173177"
                  },
                  "remark":{
	                    "value":"'.$content["remark"].'",
	                    "color":"#173177"
	               }
                }
       }';
        $result = Http::dataPost($data, $url);
        $result = json_decode($result, true);
        if (!empty($result) && $result['errcode'] ==0)
            return  true;
        return false;
    }

    /**
     * 发送微信
     * @param $openid
     * @param $content
     * @return bool
     */
    private function semdWeixinNotice($openid, $content)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->getAccessToken();
        $data = '{
            "touser":"' . $openid . '",
            "msgtype":"text",
            "text":
                {
                    "content":"' . $content . '"
                }
            }';
        $result = Http::dataPost($data, $url);
        $result = json_decode($result, true);
        if (!empty($result) && $result['errcode'] ==0)
            return  true;
        return false;
    }
    //获取access_token值
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

    private function getWeixinModel($template_id)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=" . $this->getAccessToken();

        //$result = Http::dataPost($data, $url);
        $result = Http::getCurl($url);

        //$result = json_decode($result, true);
        file_put_contents("D:/aa.txt", $result);
        var_dump($result);exit;
        if (!empty($result) && $result['errcode'] ==0)
            return  true;
        return false;
    }
}
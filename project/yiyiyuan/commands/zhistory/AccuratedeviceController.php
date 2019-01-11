<?php
/**
 *
 * 精准营销定时--Device存在时发送
 *    d:\xampp\php\php.exe d:\www\yiyiyuan\yii accuratedevice
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/14
 * Time: 10:38
 */
namespace app\commands;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\dev\Coupon_apply;
use app\models\news\AccurateDevice;
use app\models\news\User;
use yii\console\Controller;
use Yii;
require(dirname(dirname(__FILE__)) . '/' . 'notification/android/AndroidUnicast.php');
require(dirname(dirname(__FILE__)) . '/' . 'notification/android/AndroidListcast.php');
require(dirname(dirname(__FILE__)) . '/' . 'notification/ios/IOSUnicast.php');
require(dirname(dirname(__FILE__)) . '/' . 'notification/ios/IOSListcast.php');
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');
class AccuratedeviceController extends Controller
{
    private $android_appkey           = "562de3dd67e58ed70f0003b7";
    private $android_appMasterSecret     = "ztxpdi71xf5m2eu4bgnpdio3revsmvbh";

    private $iso_appkey = "5670e38267e58e8d7d001669";
    private $iso_appMasterSecret = "6g0n49fd4wmmqmldhxb6vs366stzy4bc";

    private $down_app = 'http://t.cn/R4K2tn5 ';

    /**
     *
     * //6d77310c04d8ee21c21a49105f4699dd3b1ec37566e257bcdd1da73565cb1262
        //$this->youmengIosPush(['device_tokens'=>"54aa609a8e770221f9c1c3da8a8dadb8a41cbb61c105b7e38df01bccf25c3eb6", 'alert'=>"alertcontent"]);
        //$this->youmengIOSListcast(['device_tokens'=>"54aa609a8e770221f9c1c3da8a8dadb8a41cbb61c105b7e38df01bccf25c3eb6", 'alert'=>"alertcontent"]);
        //$this->pushQuery("us09373149818485022401");
        //exit;

        //$push_data = [
        'device_tokens'=>"AoaayDMVqmMVITRzh5WyH0WhgbwhMU00OMc928QkSoyb,AoaayDMVqmMVITRzh5WyH0WhgbwhMU00OMc928QkSoyb",
        'ticker'=>"【先花一亿元】",
        'title'=>"【先花一亿元】title",
        'text'=>"先花一亿元text",
        ];
        //$push_ret = $this->youmengAndroidPush($push_data);
        //$push_ret = $this->youmengAndroidListcast($push_data);
        //$this->pushQuery("us09373149818485022401");
        //exit;
     * 
     */
    public function actionIndex()
    {
        $start = time();
        $this->logicDevice();
        $end = time() - $start;
        Logger::errorLog(print_r(array('total'=>$end), true), 'Accuratedevice', 'precision');
    }
    private function logicDevice()
    {
        $start_time = date("Y-m-d 00:00:00", time());
        $end_time = date("Y-m-d H:i:s", time());
        $whereconfig = [
            'AND',
            ['is_coupon' => 0],
            ['between', 'create_time', $start_time, $end_time],
        ];
        $sql = AccurateDevice::find()->where($whereconfig);
        $total = $sql->count();
        $limit = 200;
        $pages = ceil($total / $limit);
        for($i=0; $i<$pages;$i++){
            $accurate_device = $sql->limit($limit)->asArray()->select('id,user_id, device_tokens, device_type, sms_type')->all();
            if (!empty($accurate_device)){
                $user_id_string = Common::ArrayToString($accurate_device, 'user_id');
                AccurateDevice::updateAll(['is_coupon' => -1], ['is_coupon' => 0, 'user_id' => explode(',', $user_id_string)]);
                foreach($accurate_device as $value){
                    $user_info = $this->getUserInfo($value['user_id']);
                    if (empty($user_info))
                        continue;
                    $message_coupon_info = $this->formatMessage($value['sms_type'], $user_info);
                    $title_ticker = "提额通知";
                    $description = empty( $message_coupon_info['description']) ? "" : $message_coupon_info['description'];
                    if (!empty($message_coupon_info['coupon_list'])){
                        $title_ticker = "免息券领取通知";
                    }
                    if ($value['device_type'] == 'ios'){
                        $push_ret = $this->youmengIosPush(['device_tokens'=>$value['device_tokens'], 'alert'=>$message_coupon_info['sms_message']], $description);
                    }else{
                        $push_data = [
                            'device_tokens'=>$value['device_tokens'],
                            'ticker'=>$title_ticker,
                            'title'=> $title_ticker,
                            'text'=>$message_coupon_info['sms_message'],
                        ];
                        $push_ret = $this->youmengAndroidPush($push_data, $description);
                    }
                    $accurate_device = new AccurateDevice();
                    if (!empty($push_ret['ret']) && strtolower($push_ret['ret']) == 'success'){
                        $this->updateMsgId($value['id'], $push_ret['data']['msg_id']);
                        $accurate_device->updateIsCoupon($value['id'], 1);
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
        $amount = $user_info->getUserLoanAmount($user_info);
        $msg_code_data = [];
        switch($code) {
            //1.未完成资料
            case 1://30分钟
                //$msg = "【先花一亿元】亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！提交时如仍有疑问，您可联系客服处理";
                $msg = "亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成资料30分钟'];
                break;
            case 2://1天
                //$msg = "【先花一亿元】亲，您的1500元贷款额度已到账，补充资料即可领取，立即领取：{$this->down_app}提交时如仍有疑问，您可通过APP联系客服处理，退订回T";
                $msg = "亲，您在资料提交过程中遇到问题了吗？提交完就能得到1500元借款额度了哦！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成资料1天'];
                break;
            case 3://3天
                //$msg = "【先花一亿元】亲，您的5元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "亲，您的5元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成资料3天', 'coupon_list'=>['money'=>5, 'days'=>3]];
                break;
            case 4://7天
                //$msg  = "【先花一亿元】亲，您的10元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "亲，您的10元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成资料7天','coupon_list'=>['money'=>10, 'days'=>3]];
                break;
            case 5:///14天
                //$msg  = "【先花一亿元】亲，您的20元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "亲，您的20元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'准营销 未完成资料14天','coupon_list'=>['money'=>20, 'days'=>3]];
                break;
            case 6://30天
                //$msg  = "【先花一亿元】亲，您的30元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现，立即领取：{$this->down_app}退订回T";
                $msg = "亲，您的30元免息券和1500元贷款额度已到账，有效期3天，补充资料即可提现！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成资料30天','coupon_list'=>['money'=>30, 'days'=>3]];
                break;
            //未完成首次借款（驳回用户召回）
            case 7://1天
                //$msg = "【先花一亿元】亲，您有一笔1500元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app}退订回T";
                $msg = " 亲，您有一笔1500元的贷款额度可以提现，当前有效，赶紧领取。";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成首次借款1天'];
                break;
            case 8:// //3天
                //$msg = "【先花一亿元】亲，30元免息已收到，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "亲，30元免息已收到，领取1500元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成首次借款3天', 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 9://5天
                //$msg = "【先花一亿元】尊敬的{$user_info->realname}，您的1500元现金和30元免息券尚未领取，赶紧领取：{$this->down_app}退订回T";
                $msg = "您的1500元现金和30元免息券尚未领取，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成首次借款5天'];
                break;
            case 10://14天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "老用户60元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成首次借款14天', 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 11://30天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "老用户80元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'准营销 未完成首次借款30天', 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 12://90天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取：{$this->down_app}退订回T";
                $msg = "老用户100元免息券已到账 ，领取1500元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 未完成首次借款90天', 'coupon_list'=>['money'=>100, 'days'=>7]];
                break;
            //长时间未借款
            case 13://1天
                //$msg = "【先花一亿元】亲，您有一笔{$user_info->account->amount}元的贷款额度可以提现，当前有效，赶紧领取：{$this->down_app }退订回T";
                $msg = "亲，您有一笔{$amount}元的贷款额度可以提现，当前有效，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 长时间未借款1天'];
                break;
            case 14:// //3天
                //$msg = "【先花一亿元】亲，30元免息已收到，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "亲，30元免息已收到，领取{$amount}元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg,  'description'=>'精准营销 长时间未借款3天', 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 15://5天
                //$msg = "【先花一亿元】尊敬的{$user_info->realname}，您的{$user_info->account->amount}元现金和30元免息券尚未领取，赶紧领取：{$this->down_app }退订回T";
                $msg = "亲，30元免息已收到，领取{$amount}元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 长时间未借款5天', 'coupon_list'=>['money'=>30, 'days'=>7]];
                break;
            case 16://14天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户60元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "老用户60元免息券已到账 ，领取{$amount}元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 长时间未借款14天', 'coupon_list'=>['money'=>60, 'days'=>7]];
                break;
            case 17://30天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户80元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "老用户80元免息券已到账 ，领取{$amount}元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 长时间未借款30天', 'coupon_list'=>['money'=>80, 'days'=>7]];
                break;
            case 18://90天
                //$msg  = "【先花一亿元】尊敬的{$user_info->realname}，老用户100元免息券已到账 ，领取{$user_info->account->amount}元的贷款额度即可免息，赶紧领取：{$this->down_app }退订回T";
                $msg = "老用户100元免息券已到账 ，领取{$amount}元的贷款额度即可免息，赶紧领取！";
                $msg_code_data = ['sms_message'=>$msg, 'description'=>'精准营销 长时间未借款90天', 'coupon_list'=>['money'=>100, 'days'=>7]];
                break;
        }
        return $msg_code_data;
    }

    /**
     * 友盟安卓推送单播
     * ['device_tokens'=>'AgnHfYWQPYyAkK7dtXMKCb', 'ticker'=>'ticker', 'title'=>'title', 'text'=>'test']
     * @param array $push_data ['device_tokens', 'ticker', 'title', 'text']
     * @param $description
     * @return mixed
     * @throws \Exception
     */
    private function youmengAndroidPush($push_data, $description)
    {
        if (empty($push_data) || empty($description)) return false;
        if (empty($push_data['device_tokens']) || empty($push_data['ticker']) || empty($push_data['title']) || empty($push_data['text'])){
            return false;
        }
        $unicast = new \AndroidUnicast();
        $unicast->setAppMasterSecret($this->android_appMasterSecret);
        $unicast->setPredefinedKeyValue("appkey",           $this->android_appkey);
        $unicast->setPredefinedKeyValue("timestamp",        strval(time()));
        // Set your device tokens here
        $unicast->setPredefinedKeyValue("device_tokens",    $push_data['device_tokens']);
        $unicast->setPredefinedKeyValue("ticker",           $push_data['ticker']);
        $unicast->setPredefinedKeyValue("title",            $push_data['title']);
        $unicast->setPredefinedKeyValue("text",             $push_data['text']);
        $unicast->setPredefinedKeyValue("after_open",       "go_app");
        // Set 'production_mode' to 'false' if it's a test device.
        // For how to register a test device, please see the developer doc.
        $unicast->setPredefinedKeyValue("production_mode", "false");
        $unicast->setPredefinedKeyValue("description", $description);
        // Set extra fields可选 用户自定义key-value。只对"通知"
        //$unicast->setExtraField("jump_position", "2");
        //{"ret":"SUCCESS","data":{"task_id":"us50340149811974667301"}}
        $ret_message = $unicast->send();
        //var_dump($ret_message);
        //$ret_message = '{"ret":"SUCCESS","data":{"task_id":"us50340149811974667301"}}';
        if (empty($ret_message)){
            return [];
        }
        return json_decode($ret_message, true);
    }

    /**
     * 友盟IOS推送单播
     * ['device_tokens'=>'d86dcca0af75e1d2379d6c07d048595424943121e48d190361d442de196bc849', 'alert'=>'test']
     * @param array $push_data ['device_tokens', 'alert']
     * @param $description
     * @return bool
     * @throws \Exception
     */
    private function youmengIosPush($push_data, $description)
    {
        if (empty($push_data) || empty($description)) return false;
        if (empty( $push_data['device_tokens'])){
            return false;
        }
        $unicast = new \IOSUnicast();
        $unicast->setAppMasterSecret($this->iso_appMasterSecret);
        $unicast->setPredefinedKeyValue("appkey",           $this->iso_appkey);
        $unicast->setPredefinedKeyValue("timestamp",        strval(time()));
        // Set your device tokens here
        $unicast->setPredefinedKeyValue("device_tokens",    $push_data['device_tokens']);
        $unicast->setPredefinedKeyValue("alert", $push_data['alert']);
        $unicast->setPredefinedKeyValue("badge", 0);
        $unicast->setPredefinedKeyValue("sound", "chime");
        // Set 'production_mode' to 'true' if your app is under production mode
        $unicast->setPredefinedKeyValue("production_mode", "false");
        $unicast->setPredefinedKeyValue("description", $description);
        // Set customized fields
        //$unicast->setCustomizedField("test", "helloworld");
        $ret_message = $unicast->send();
        if (empty($ret_message)){
            return [];
        }
        //var_dump($ret_message);exit;
        //$ret_message = '{"ret":"SUCCESS","data":{"task_id":"us02516149811937385601"}}';
        return json_decode($ret_message, true);
    }

    /**
     * 查询发送结果
     * @param $task_id
     */
    private function pushQuery($task_id)
    {
        $url = 'http://msg.umeng.com/api/status';
        $data = [
            'appkey' => $this->iso_appkey = "5670e38267e58e8d7d001669",
            'timestamp' => strval(time()),
            'task_id' => $task_id,
        ];

        $postBody = json_encode($data);

        $sign = md5("POST" . $url . $postBody . $this->iso_appMasterSecret);
        $url = $url . "?sign=" . $sign;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody );
        $result = curl_exec($ch);
        var_dump($result);

    }

    /**
     * 更新msg_id到yi_accurate_device表中
     * @param $id
     * @param $msg_id
     * @return bool
     */
    private function updateMsgId($id, $msg_id)
    {
        if (empty($id) || empty($msg_id)) return false;
        $device_info =AccurateDevice::find()->where(['id'=>$id])->one();
        if (empty($device_info)) return false;
        $device_info->msg_id = $msg_id;
        return $device_info->save();
    }


}
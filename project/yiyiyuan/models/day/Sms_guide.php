<?php

namespace app\models\day;

use app\commonapi\Common;
use app\commonapi\Logger;
use app\commonapi\sms\SmsApi;
use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_sms_guide".
 *
 * @property string $id
 * @property string $mobile
 * @property string $code
 * @property string $content
 * @property integer $sms_type
 * @property integer $status
 * @property integer $channel
 * @property integer $type
 * @property string $send_time
 * @property string $create_time
 */
class Sms_guide extends BaseModel {

    private $platform_host = "http://msg.xianhuahua.com/push-platform/";
    private $platform_host_test = "http://47.93.121.86:8090/push-platform";

//    private $platform_host_test = "http://121.69.71.58:10024/push-platform";

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_sms';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['sms_type', 'status', 'channel', 'type'], 'integer'],
            [['send_time', 'create_time'], 'safe'],
            [['mobile'], 'string', 'max' => 12],
            [['code'], 'string', 'max' => 10],
            [['content'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'code' => 'Code',
            'content' => 'Content',
            'sms_type' => 'Sms Type',
            'status' => 'Status',
            'channel' => 'Channel',
            'type' => 'Type',
            'send_time' => 'Send Time',
            'create_time' => 'Create Time',
        ];
    }

    private function getHost() {
        $is_prod = SYSTEM_ENV == 'prod' ? true : false;
        $platform_host = $is_prod ? $this->platform_host : $this->platform_host_test;
        return $platform_host;
    }

    /**
     * 统计当天短信发送次数
     * @param str $mobile 手机号码
     * @param int $type 验证码发送类型 1注册 2登录
     * @return int 发送次数
     */
    public function getSmsCount($mobile, $type = 1) {
        if (empty($mobile)) {
            return null;
        }
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $sms_count = self::find()->where(["mobile" => $mobile, 'sms_type' => $type])->andWhere(['>=', 'create_time', $begintime])->andWhere(['<=', 'create_time', $endtime])->count();
        return $sms_count;
    }

    /**
     * 7天乐绑卡短信
     * @param $mobile
     * @param $type
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 13:43
     */
    public function sendSevendayCard($mobile, $type) {
        if (empty($mobile)) {
            return false;
        }
        $code = $this->createCode($mobile, 'sevenday_getcode_bank_');
        $content = '【7天乐】' . $code . '（七天乐手机验证码，请完成绑卡）为了保护您的账号安全，验证码短信请勿转发其他人。';
        return $this->choiceChannel($mobile, $content, $type, $code, 3);
    }

    /**
     * 7天乐运营商认证
     * @param $mobile
     * @param $type
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 13:43
     */
    public function sendSevendayMobile($mobile, $type) {
        if (empty($mobile)) {
            return false;
        }
        $code = $this->createCode($mobile, 'sevenday_getcode_mobile_');
        $content = '【7天乐】' . $code . '（七天乐手机验证码，请完成运营商认证）为了保护您的账号安全，验证码短信请勿转发其他人。';
        return $this->choiceChannel($mobile, $content, $type, $code, 3);
    }

    /**
     * 7天乐登录注册短信
     * @param $mobile
     * @param $type
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 13:48
     */
    public function sendSevendayReg($mobile, $type) {
        if (empty($mobile)) {
            return false;
        }
        $code = $this->createCode($mobile, 'sevenday_getcode_register_');
        $content = '【7天乐】' . $code . '（七天乐手机验证码，请完成注册/登录）为了保护您的账号安全，验证码短信请勿转发其他人。';
        return $this->choiceChannel($mobile, $content, $type, $code, 3);
    }

    /**
     * 7天乐出款成功
     * @param $mobile
     * @param $type
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 13:48
     */
    public function sendSevendayOutmoney($mobile, $money, $date, $outmoney, $allmoney, $type) {
        if (empty($mobile)) {
            return false;
        }
        $code = $this->createCode($mobile, 'sevenday_getcode_register_');
        $content = "尊敬的用户，您在七天乐有一笔{$money}元借款已放款至您绑定的银行卡中，您的借款次日起正式生效，实际出款金额为{$outmoney}元，最后还款日为{$date}，应还金额{$allmoney}元，请留意银行短信通知";
        return $this->choiceChannel($mobile, $content, $type, '', 3);
    }

    /**
     * 7天乐还款成功
     * @param $mobile
     * @param $type
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 13:48
     */
    public function sendSevendayRepay($mobile, $money, $type) {
        if (empty($mobile)) {
            return false;
        }
        if ($type == 5) {
            $content = "尊敬的用户，您在七天乐有一笔" . round($money, 2) . "元借款已还款成功，限时免审核放款通道已开启http://t.cn/E7LKjbN，期待再次为您服务。";
        } else {
            $content = "尊敬的用户，您在七天乐有一笔" . round($money, 2) . "元还款失败，请立即前往http://t.cn/E7LKjbN 重新还款，避免逾期对您的信用造成不良记录.";
        }
        return $this->choiceChannel($mobile, $content, $type, '', 3);
    }

    /**
     * 还款失败
     * @param $mobile
     * @param $repay
     * @param $need
     * @return mixed
     * @author 王新龙
     * @date 2018/8/3 18:22
     */
    public function sendSmsByRepaymentPortion($mobile, $repay, $need) {
        $content = addslashes('尊敬的用户您已成功还 款' . sprintf('%.2f', $repay) . '元。仍需还 款' . sprintf('%.2f', $need) . '元。全额还 款后即可发起下一笔借款。');
        return $this->saveSmsSend($mobile, $content, 2);
    }

    /**
     * 还款结清
     * @param $mobile
     * @return mixed
     * @author 王新龙
     * @date 2018/8/3 18:22
     */
    public function sendSmsByRepaymentAll($mobile) {
        $content = addslashes('尊敬的用户您此次借款已还清。感谢您使用先花一亿元进行借款。');
        return $this->saveSmsSend($mobile, $content, 1);
    }

    /**
     * 还款失败
     * @param $mobile
     * @param $need
     * @return mixed
     * @author 王新龙
     * @date 2018/8/3 18:22
     */
    public function sendSmsByRepaymentFailed($mobile, $need) {
        $content = '尊敬的用户您刚刚的还 款操作失败了，建议您选择其他还 款方式，当前仍需还 款' . sprintf('%.2f', $need) . '元，有任何疑问请咨询先花一亿元微信客服，按时还 款助您成长。';
        return $this->saveSmsSend($mobile, $content, 3);
    }

    /**
     * 逾前提醒
     * @param $mobile
     * @param $type
     * @return bool
     * @author 代威群
     * @date 2018/8/3 13:48
     */
    public function sendRepay($mobile, $realname, $money, $date, $end_date) {
        if (empty($mobile)) {
            return false;
        }
        $end_date = date("Y-m-d", strtotime($end_date) - 1);
        $now = date('Y-m-d');
        if ($now == $end_date) {
            $content = $realname . "先生/女士，今天是您在七天乐借款的最后还款日，还款金额" . sprintf('%.2f', $money) . "元，错过最后还款日还款将产生贷后服务费，同时会影响您的信用记录，请您按时前往 http://t.cn/Ev3RTSd 进行还款。请注意：七天乐从未授权任何第三方向你收取任何费用！";
        } else {
            $content = $realname . "先生/女士，距您于" . date('Y-m-d', strtotime($date)) . "产生的借款还有一天到最后还款日，还款金额" . sprintf('%.2f', $money) . "元，错过最后还款日还款将产生贷后服务费，同时会影响您的信用记录，请您按时前往 http://t.cn/Ev3RTSd 进行还款。请注意：七天乐从未授权任何第三方向你收取任何费用！";
        }
        return $this->choiceChannel($mobile, $content, 4, '', 3);
    }

    /**
     * 生成验证码并存储到redis
     * @param $mobile 手机号
     * @param $type 对应sms表sms_type字段
     * @return int 本次短信发送code
     */
    private function createCode($mobile, $type) {
        $key = $type . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        if (!empty($code_byredis)) {
            $code = $code_byredis;
        } else {
            $code = rand(1000, 9999);
        }
        Yii::$app->redis->setex($key, 1800, $code);
        return $code;
    }

    /**
     * 短信通道选择，1：创蓝 2：云信
     * @param string $mobile 手机号
     * @param string $content 发送内容
     * @param int $type 短信发送类型 对应sms表sms_type字段
     * @param string $code 短信验证码
     * @param int $sendchannel 所选通道 1:创蓝 others：云信
     * @return boolean
     */
    public function choiceChannel($mobile, $content, $type, $code, $sendchannel) {
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'code' => $code];
        if (empty($code) || strlen($code) < 4) {
            $res = $this->sendMarketingSms([['phone' => $mobile, 'content' => $content, 'channel_code' => $sendchannel]]);
            $this->saveSms($mobile, $content, $type, 2, $sendchannel, 1, $code);
        } else {
            $res = $this->sendIndustrySms($mobile, $content, $sendchannel);
            $this->saveSms($mobile, $content, $type, 2, $sendchannel, 1, $code);
        }
        return $res;
    }

    /**
     * Undocumented function
     * 批量发送营销类短信-New
     * @return void
     */
    public function sendMarketingSms($postData) {
        if (empty($postData) || !is_array($postData))
            return false;
        $smsList = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $oApi = new SmsApi;
        $data = [
            'business_code' => 11, //7天乐
            'channel_code' => $postData[0]['channel_code'],
            'smsList' => $smsList, //json
        ];
        $host = $this->getHost();
        $url = $host . '/api/sms/v2/send_marketing_sms';
        $result = $oApi->send($url, $data);
        Logger::dayLog('psms', $data, $result);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

    /**
     * 发送触发类短信-new
     * @param $phone    手机号
     * @param $content  短信内容
     * @param $channelCode  通道
     * @return array|bool|mixed
     */
    public function sendIndustrySms($mobile, $content, $channelCode) {
        if (empty($mobile) || empty($content) || empty($channelCode)) {
            return false;
        }
        $host = $this->getHost();
        $url = $host . '/api/sms/v2/send_industry_sms';
        $data = [
            'phone' => $mobile,
            'content' => $content,
            'channel_code' => $channelCode,
            'aid' => 99, //一亿元
            'ip' => Common::get_client_ip(),
        ];
        $oApi = new SmsApi;
        $result = $oApi->send($url, $data);
        Logger::dayLog('psms', $data, $result, $url);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

    /**
     * 发送短信记录存储
     * @param array $data 数据集合
     * @return boolean
     */
    private function saveSms($mobile, $content, $sms_type, $status = 0, $channel = 3, $type = 1, $code = '') {
        $postData = [
            'mobile' => $mobile,
            'content' => $content,
            'code' => (string) $code,
            'sms_type' => $sms_type,
            'status' => $status,
            'channel' => $channel,
            'type' => $type,
            'send_time' => date('Y-m-d H:i:s'),
            'create_time' => date('Y-m-d H:i:s')
        ];
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

}

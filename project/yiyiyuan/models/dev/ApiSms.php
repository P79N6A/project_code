<?php

namespace app\models\dev;

use app\commonapi\Apihttp;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\sms\CSms;
use app\commonapi\ApiSms as ApiSms2;
use Yii;

/**
 * 统一对外开放接口
 */
class ApiSms {

    /**
     * 短信发送
     */
    public function sendOpenAccSms($mobile) {

        if (empty($mobile)) {
            return false;
        }
        $content = '尊敬的用户，您的借款已经通过审核，需要在2小时内登录先花一亿元APP领取借款，APP下载请点 http://t.cn/R4K2tn5，如有疑问请咨询先花一亿元微信客服';
        $type = 29;

        $apihttp = new Apihttp;
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'channel_type' => 3];
        // $sendRet = $apihttp->sendSmsByChuanglan($data); //调用路由，后续可改为调用短信路由接口
        $apiModel = new ApiSms2();
        $sendRet = $apiModel->choiceChannel($mobile, $content, $type, '', 3);

        if ($sendRet['rsp_code'] == '0000') {
            $ret = $this->saveSms($data);
            return true;
        } else {
            //暂留
            return false;
        }
    }

    /**
     * H5 流程审核通过，通知用户进行活体
     */
    public function sendCallUserHuotiSms($mobile) {

        if (empty($mobile)) {
            return false;
        }
        $content = '您的借款已经通过审核，请下载先花一亿元app http://t.cn/R4K2tn5，领取，请在24小时内领取，否则借款将被取消。';
        $type = 37;

        $apihttp = new Apihttp;
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'channel_type' => 3];
        //$sendRet = $apihttp->sendSmsByChuanglan($data); //调用路由，后续可改为调用短信路由接口
        $apiModel = new ApiSms2();
        $sendRet = $apiModel->choiceChannel($mobile, $content, $type, '', 3);
        if ($sendRet['rsp_code'] == '0000') {
            $ret = $this->saveSms($data);
            return true;
        } else {
            //暂留
            return false;
        }
    }

    /**
     * 短信发送
     * @param int $type [1,2]
     */
    public function sendReg($mobile, $type) {

        if (empty($mobile)) {
            return false;
        }
        $code = $this->createCode($mobile, 'getcode_register_');
        if (!$code) {
            return FALSE;
        }
        $content = '【先花一亿元】' . $code . '（先花一亿元手机验证码，请完成认证）为了保护您的账号安全，验证码短信请勿转发其他人。';
        $apiModel = new ApiSms2();
        return $apiModel->choiceChannel($mobile, $content, $type, $code, 3);  //选择发送通道（1 创蓝 ， 2 云信）
    }

    /**
     * 绑卡短信发送
     * @param int $type 
     */
    public function sendBindCard($mobile, $type) {

        if (empty($mobile)) {
            return false;
        }
        $code = $this->createCode($mobile, 'getcode_bank_');
        $content = '【先花一亿元】' . $code . '（先花一亿元手机验证码，请完成绑定银行卡）为了保护您的账号安全，验证码短信请勿转发其他人';
        $apiModel = new ApiSms2();
        return $apiModel->choiceChannel($mobile, $content, $type, $code, 3);  //选择发送通道（1 创蓝 ， 2 云信）
    }

    /**
     * 获取优惠券短信发送
     * @param int $type [1,2]
     */
    public function sendCoupon($mobile, $type) {

        if (empty($mobile)) {
            return false;
        }
        $content = '先花一亿元送您的借款优惠券已经放到了您的账户中，登陆 http://t.cn/R4K2tn5，即可使用';
        $apihttp = new ApiSms2();
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'channel_type' => 3];
        $sendRet = $apihttp->choiceChannel($mobile, $content, $type, '', 3);
        if ($sendRet['rsp_code'] == '0000') {
            return true;
        } else {
            //暂留
            return false;
        }
    }

    /**
     * 微信支付短信发送
     * @param $mobile
     * @param $content
     * @return boolean
     */
    public function sendWechatpaySms($mobile, $content) {

        if (empty($mobile) || empty($content)) {
            return false;
        }
        Http::sendByMobile($mobile, $content);
        return true;
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
            return FALSE;
        } else {
            $code = rand(1000, 9999);
        }
        Yii::$app->redis->setex($key, 60, $code);
        return $code;
    }

    /**
     * 发送短信记录存储
     * @param array $data 数据集合
     * @return boolean
     */
    private function saveSms($data) {

        $sms = new Sms();
        $sms->content = $data['content'];
        $sms->recive_mobile = $data['mobile'];
        $sms->create_time = date('Y-m-d H:i:s', time());
        $sms->sms_type = $data['sms_type'];
        $sms->code = isset($data['code']) ? $data['code'] : '';
        $sms->send_mobile = '';
        $sms->save();

        return true;
    }

    /**
     * 创蓝短信发送私有方法
     * @param string $mobile 手机号
     * @param string $content 短信内容，不要标签，内部已集成
     * @param int $type 短信发送类型 对应sms表sms_type字段
     * @param string $code 短信验证码
     * @return boolean
     */
    private function sendSmsByChuanglan($mobile, $content, $type, $code) {
        $apihttp = new Apihttp;
        $data = [
            'mobile' => $mobile,
            'content' => "【先花一亿元】" . $content,
            'sms_type' => $type,
            'code' => $code,
            'channel_type' => empty($code) ? 1 : 2,
        ];

        $sendRet = $apihttp->sendSmsByChuanglan($data); //调用路由，后续可改为调用短信路由接口
        if ($sendRet['res_code'] == '0000') {
            $ret = $this->saveSms($data);
            return true;
        } else {
            //暂留
            return false;
        }
    }

    /**
     * 云信短信平台
     * @param string $mobile 手机号
     * @param string $content 短信内容
     * @param int $type 短信发送类型 对应sms表sms_type字段
     * @param string $code 短信验证码
     * @return boolean
     */
    private function sendSmsByYunxin($mobile, $content, $type, $code) {
        $sendRet = Http::sendByMobile($mobile, $content);
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'code' => $code];
        if ($sendRet) {
            $ret = $this->saveSms($data);
            return true;
        } else {
            //暂留
            return false;
        }
    }

    /**
     * 借款还清短信
     * @param string $mobile
     * @return boolean
     */
    public function sendRepaymentAllSms($mobile) {
        $content = addslashes('尊敬的用户您此次借款已还清。感谢您使用先花一亿元进行借款。');
        $apiModel = new ApiSms2();
        return $apiModel->choiceChannel($mobile, $content, 37, '1', 3);
    }

    /**
     * 部分还款短信
     * @param string $mobile 手机号码
     * @param float $repay 还款金额
     * @param float $need 剩余还款金额
     * @return boolean
     */
    public function sendRepaymentPortionSms($mobile, $repay, $need) {
        $content = addslashes('尊敬的用户您已成功还 款' . sprintf('%.2f', $repay) . '元。仍需还 款' . sprintf('%.2f', $need) . '元。全额还 款后即可发起下一笔借款。');
        $apiModel = new ApiSms2();
        return $apiModel->choiceChannel($mobile, $content, 38, '1', 3);
    }

    /**
     * 还款失败
     * @param string $mobile
     * @param float $need 剩余还款金额
     * @return boolean
     */
    public function sendRepaymentFailedSms($mobile, $need) {
        $content = '尊敬的用户您刚刚的还 款操作失败了，建议您选择其他还 款方式，当前仍需还 款' . sprintf('%.2f', $need) . '元，有任何疑问请咨询先花一亿元微信客服，按时还 款助您成长。';
        $apiModel = new ApiSms2();
        return $apiModel->choiceChannel($mobile, $content, 39, '1', 3);
    }

    /**
     * 发送短信
     * @param $sms_type [短信类型] 0->其他，1->注册/登录，2->充值验证码，3->绑卡验证码，4->密码短信
     * @param $mess [是否群发] 0->否，1->是
     */
    private function sendSmsByXinda($mobile, $code, $content, $sms_type, $mess = 0) {
        $content = $this->addSmsSign($content, 3); //给内容添加签名
        $arrData = [
            'phone' => $mobile,
            'msgContent' => $content,
            'msgSign' => 2,
            'sendChannel' => 3,
            'ip' => ''
        ];
        $result = $this->send($arrData, $mess);
        if (!$result) {
            Logger::daylog('sms', 'error', 'phone', $mobile, 'smscode', $code, 'sms_type', $sms_type);
        } else {
            $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $sms_type, 'code' => $code];
            $ret = $this->saveSms($data);
        }
        return $result;
    }

    /**
     * @param $postData
     * @param $type [发送类别] 0->单发，1->群发
     */
    public function send($arrData, $type) {
        if (empty($arrData)) {
            return false;
        }
        //发送短信渠道：1是创蓝，2是云信[默认]，3是信达优创
        $objCSms = new CSms();
        if ($type == 0) {
            $result = $objCSms->sendAuth($arrData);
        } else {
            $result = $objCSms->sendMarketing($arrData);
        }
        if ($result['rsp_code'] == '0000') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 短信内容加签名
     * @param $content 传递过来的短信内容
     * @param $sendChannel 1是创蓝，2是云信[默认]，3是信达优创
     */
    private function addSmsSign($content, $sendChannel = 3) {
        switch ($sendChannel) {
            case 1:
                $content = '【先花一亿元】' . $content;
                break;
            case 2:
                $content = $content . '【先花一亿元】';
                break;
            default:
                $content = '【先花一亿元】' . $content;
        }
        return $content;
    }

}

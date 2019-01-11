<?php

namespace app\commonapi;

use app\models\news\Sms;
use app\models\news\SmsSend;
use app\commonapi\sms\CSms;
use app\models\news\User_loan;
use Yii;

class ApiSms {

    /**
     * 短信发送
     * @param $mobile
     * @return bool
     */
    public function sendOpenAccSms($mobile) {
        if (empty($mobile)) {
            return false;
        }
        $content = '尊敬的用户，您的借款已经通过审核，需要在2小时内登录先花一亿元APP领取借款，APP下载请点 http://t.cn/R4K2tn5，如有疑问请咨询先花一亿元微信客服';
        $type = 29;

        $apihttp = new Apihttp;
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'channel_type' => 3];
        //$sendRet = $apihttp->sendSmsByChuanglan($data); //调用路由，后续可改为调用短信路由接口
        $sendRet = $this->choiceChannel($mobile, $content, $type, '', 3);
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
     * @param $mobile
     * @return bool
     */
    public function sendCallUserHuotiSms($mobile) {

        if (empty($mobile)) {
            return false;
        }
        $content = '您的借款已经通过审核，请下载先花一亿元app http://t.cn/R4K2tn5，领取，请在24小时内领取，否则借款将被取消。';
        $type = 37;

        $apihttp = new Apihttp;
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'channel_type' => 3];
        // $sendRet = $apihttp->sendSmsByChuanglan($data); //调用路由，后续可改为调用短信路由接口
        $sendRet = $this->choiceChannel($mobile, $content, $type, '', 3);
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
     * @param $mobile
     * @param $type  发送通道1 创蓝 2 云信
     * @return bool
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
        return $this->choiceChannel($mobile, $content, $type, $code, 3);
    }

    /**
     * 定时警觉短信发送
     * @param $mobile
     * @param $type  发送通道1 创蓝 2 云信
     * @return bool
     */
    public function sendMonitor($mobile, $type, $loan_id) {

        if (empty($mobile)) {
            return false;
        }
        $content = '【一亿元债匹36小时没接收到债匹结束结果通知的报警】（执行结果:' . $loan_id . '）发出[严重]报警，请及时处理！【监控系统】。';
        return $this->choiceChannel($mobile, $content, $type, '', 3);
    }

    /**
     * 绑卡短信发送
     * @param $mobile
     * @param $type 选择发送通道（1 创蓝 ， 2 云信）
     * @return bool
     */
    public function sendBindCard($mobile, $type) {

        if (empty($mobile)) {
            return false;
        }
        $code = $this->createCode($mobile, 'getcode_bank_');
        $content = '【先花一亿元】' . $code . '（先花一亿元手机验证码，请完成绑定银行卡）为了保护您的账号安全，验证码短信请勿转发其他人';
        return $this->choiceChannel($mobile, $content, $type, $code, 3);
    }

    /**
     * 获取优惠券短信发送
     * @param $mobile
     * @param $type 发送通道（1 创蓝 ， 2 云信）
     * @return bool
     */
    public function sendCoupon($mobile, $type) {

        if (empty($mobile)) {
            return false;
        }
        $content = '先花一亿元送您的借款优惠券已经放到了您的账户中，登陆 http://t.cn/R4K2tn5，即可使用';
//        return $this->choiceChannel($mobile, $content, $type, '', 1);  //选择
        //此处由于使用168通道，但是没有code，所以暂时没办法使用自动切换通道方法
        $apihttp = new Apihttp;
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'channel_type' => 3];
        //$sendRet = $apihttp->sendSmsByChuanglan($data); //调用路由，后续可改为调用短信路由接口
        $sendRet = $this->choiceChannel($mobile, $content, $type, '', 3);
        if ($sendRet['rsp_code'] == '0000') {
            $ret = $this->saveSms($data);
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
        $condition = [
            'content' => $data['content'],
            'recive_mobile' => $data['mobile'],
            'sms_type' => $data['sms_type'],
            'code' => isset($data['code']) ? (string) $data['code'] : '',
            'send_mobile' => '',
        ];
        $ret = (new Sms())->save_sms($condition);
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
        Logger::errorLog($mobile . "--" . print_r($sendRet, true), 'sms');
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
        $model = new CSms();
        if (empty($code) || strlen($code) < 4) {
            $res = $model->sendMarketingSms([['phone' => $mobile, 'content' => $content, 'channel_code' => $sendchannel]]);
            $this->saveSms($data);
        } else {
            $res = $model->sendIndustrySms($mobile, $content, $sendchannel);
            $this->saveSms($data);
        }
        return $res;
    }

    /**
     * 借款还清短信
     * @param string $mobile
     * @return boolean
     */
    public function sendRepaymentAllSms($mobile) {
        $content = addslashes('尊敬的用户您此次借款已还清。感谢您使用先花一亿元进行借款。');
        //return $this->sendSmsByChuanglan($mobile, $content, 37, '1');
        return $this->choiceChannel($mobile, $content, 37, '1', 3);
    }

    /**
     * 借款还清短信 
     * @author yangjinlong
     * @param string $mobile
     * @return boolean
     */
    public function sendSmsByRepaymentAll($mobile) {
        $content = addslashes('尊敬的用户您此次借款已还清。感谢您使用先花一亿元进行借款。');
        return $this->saveSmsSend($mobile, $content, 1);
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
        //return $this->sendSmsByChuanglan($mobile, $content, 38, '1');
        return $this->choiceChannel($mobile, $content, 38, '1', 3);
    }

    /**
     * 部分还款短信
     * @param string $mobile 手机号码
     * @param float $repay 还款金额
     * @param float $need 剩余还款金额
     * @return boolean
     */
    public function sendSmsByRepaymentPortion($mobile, $repay, $need) {
        $content = addslashes('尊敬的用户您已成功还 款' . sprintf('%.2f', $repay) . '元。仍需还 款' . sprintf('%.2f', $need) . '元。全额还 款后即可发起下一笔借款。');
        return $this->saveSmsSend($mobile, $content, 2);
    }

    /**
     * 分期借款还款短信
     * @param string $mobile 手机号码
     * @param float $repay 还款金额
     * @param float $need 剩余还款金额
     * @return boolean
     */
    public function sendSmsByRepaymentTerms($mobile, $repay, $phase=1 ,$totalphase=1) {
        $content = addslashes('尊敬的用户您已成功还 款' . sprintf('%.2f', $repay) . '元。已还' . $phase.'/'.$totalphase . '期，感谢您使用先花一亿元进行借款。');
        return $this->saveSmsSend($mobile, $content, 2);
    }


    // $smstype 短信类型 1、还款结清 2 部分还款 3 还款失败
    private function saveSmsSend($mobile, $content, $smstype) {
        $data = [
            'mobile' => $mobile,
            'content' => $content,
            'channel' => Yii::$app->params['sms_channel'],
            'sms_type' => $smstype,
        ];
        $smsSend = new SmsSend();
        return $smsSend->addSmsSend($data);
    }

    /**
     * 还款失败
     * @param string $mobile
     * @param float $need 剩余还款金额
     * @return boolean
     */
    public function sendRepaymentFailedSms($mobile, $need) {
        $content = '尊敬的用户您刚刚的还 款操作失败了，建议您选择其他还 款方式，当前仍需还 款' . sprintf('%.2f', $need) . '元，有任何疑问请咨询先花一亿元微信客服，按时还 款助您成长。';
        //return $this->sendSmsByChuanglan($mobile, $content, 39, '1');
        return $this->choiceChannel($mobile, $content, 39, '1', 3);
    }

    /**
     * 还款失败
     * @param string $mobile
     * @param float $need 剩余还款金额
     * @return boolean
     */
    public function sendSmsByRepaymentFailed($mobile, $need) {
        $content = '尊敬的用户您刚刚的还 款操作失败了，建议您选择其他还 款方式，当前仍需还 款' . sprintf('%.2f', $need) . '元，有任何疑问请咨询先花一亿元微信客服，按时还 款助您成长。';
        return $this->saveSmsSend($mobile, $content, 3);
    }

    /**
     * 还款失败
     * @param string $mobile
     * @param float $need 剩余还款金额
     * @return boolean
     */
    public function sendSmsByRepaymentFailedNew($mobile, $need) {
        $content = '尊敬的用户您刚刚的还 款操作失败了，建议您选择其他还 款方式，本期仍需还 款'.$need.'元，有任何疑问请咨询先花一亿元微信客服，按时还 款助您成长。';
        return $this->saveSmsSend($mobile, $content, 3);
    }


    /**
     * 存管借款已经打到用户的电子账户
     * @param $mobile
     * @param $loan_money
     * @param $real_money
     * @param $repay_date
     * @param $repay_money
     * @return bool
     */
    public function sendDebtLoanSuccessSms($mobile, $loan_money, $real_money, $repay_date, $repay_money) {
        $content = '尊敬的用户，您在先花一亿元有一笔' . sprintf('%.2f', $loan_money) . '元借款已通过审核，实际出款金额为' . sprintf('%.2f', $real_money) . '元，最后还款日为' . $repay_date . '，应还金额' . sprintf('%.2f', $repay_money) . '元，请在2小时内前往app进行提现，提现完成后注意查收您所绑定的银行卡。';
        return $this->choiceChannel($mobile, $content, 40, '1', 3);
    }

    /**
     * 体外出款，放款短信通知
     * @param $mobile
     * @param $loan_money
     * @param $real_money
     * @param $period
     * @return bool
     */
    public function sendLoanSuccessSms($loan_id) {
        if(empty($loan_id)){
            return FALSE;
        }
        $o_user_loan = (new User_loan())->getById($loan_id);
        if(empty($o_user_loan)){
            return FALSE;
        }
        if(empty($o_user_loan->user)){
            return FALSE;
        }
        $period = 1;
        if(in_array($o_user_loan->business_type,[5,6,11])){
            $period = count($o_user_loan->goodsbills);
        }
        $content = '尊敬的用户，您在先花一亿元有一笔' . sprintf('%.2f', $o_user_loan->amount) . '元借款已通过审核，分'.$period.'期，实际出款金额为' . sprintf('%.2f', $o_user_loan->amount) . '元，请在2小时内注意查收您所绑定的银行卡，前往先花一亿元查看更多账单信息。';
        return $this->choiceChannel($o_user_loan->user->mobile, $content, 45, '1', 3);
    }

    /**
     * 评测成功短信
     * @param $mobile
     * @param $money
     * @return bool
     * @author 王新龙
     * @date 2018/8/16 11:58
     */
    public function sendCreditSuccessSma($mobile, $money) {
        $content = '您已成功获得' . sprintf('%.2f', $money) . '元借款金额，请尽快前往一亿元领取，以免借款失效。';
        return $this->choiceChannel($mobile, $content, 43, '1', 3);
    }

}

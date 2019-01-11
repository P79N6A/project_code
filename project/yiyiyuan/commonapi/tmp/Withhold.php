<?php

namespace app\commonapi\tmp;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\Manager_logs;
use app\models\news\User_bank;
use app\models\news\User_loan;
use Yii;

class Withhold {

    public $enableCsrfValidation = false;

    /**
     * 代扣
     */
    public function actionIndex($bank, $direct_amount, $loan_id, $admin_id = '-100', $realname = '系统代扣') {
        //查询借款信息
        $loaninfo = $this->getLoanInfo($loan_id);
        if(empty($loaninfo)) {
            return false;
        }
        //生成一条还款记录，并保存操作记录,然后调用代扣的接口
        $orderid            = '';
        $times              = date('Y-m-d H:i:s');
        $data['repay_id']   = $orderid;
        $data['user_id']    = $loaninfo->user_id;
        $data['loan_id']    = $loan_id;
        $data['bank_id']    = $bank->id;
        $data['money']      = $direct_amount;
        $data['platform']   = 3;
        $data['source']     = 4;
        $data['repay_mark'] = '代扣';
        $repay_id           = (new Loan_repay)->addRepay($data);
        if (!$repay_id) {
            return false;
        }
        $orderid = Loan_repay::findOne($repay_id);
        if (empty($orderid->repay_id) || !isset($orderid->repay_id)) {
            return false;
        }
        $orderid = $orderid->repay_id;

        //保存操作记录
        $condition  = array(
            'admin_id'       => $admin_id,
            'admin_name'     => $realname,
            'operation_type' => 8,
            'log_id'         => $repay_id,
        );
        $result_log = (new Manager_logs)->updateManagerlogs($condition);

        //调用代扣的接口
        $card_top  = substr($bank->card, 0, 6);
        $card_last = substr($bank->card, -4);
        $postdata  = array(
            'orderid'      => $orderid, // 请求唯一号
            'transtime'    => time(), // 交易时间
            'amount'       => $direct_amount * 100, //交易金额
            'productname'  => '购买电子产品', //商品名称
            'productdesc'  => '购买电子产品', // 商品描述
            'identityid'   => $this->getPayIdentityid($loaninfo->user_id, $bank->card), // 用户标识
            'identitytype' => 2, // 用户类型
            'card_top'     => $card_top, // 卡号前6位
            'card_last'    => $card_last, // 卡号后4位
            'orderexpdate' => 60, // 商品类别码
            'userip'       => '121.69.71.58',
            'callbackurl'  => Yii::$app->params['yibao_repay'],
        );
        $openApi   = new Apihttp;
        $result    = $openApi->directbindpay($postdata);
        if ($result['res_code'] == '0000') {
            return true;
        } else {
            Logger::daylog('Withhold/index', '易宝代扣失败', 'data=>', $postdata, 'result=>', $result);
            return false;
        }
    }

    private function getUserBank($bank_id) {
        //银行卡信息
        $user_bank = (new User_bank)->getBankById($bank_id);
        if (empty($user_bank)) {
            $array = $this->errorreback('10041');
            return $array;
        }
        if ($user_bank->status != 1) {
            $array = $this->errorreback('60009');
            return $array;
        }

        if ($user_bank->type !== 0) {
            $array = $this->errorreback('60010');
            return $array;
        }
        return $user_bank;
    }

    private function getLoanInfo($loan_id) {
        //查询借款信息
        $loaninfo = (new User_loan)->getById($loan_id);
        if (empty($loaninfo)) {
            return null;
        }
        return $loaninfo;
    }

    private function getLoanRepaymentAmount($loaninfo, $direct_amount) {
        $repay_amount = $loaninfo->getRepaymentAmount($loaninfo);
        if ($repay_amount < $direct_amount) {
            //代扣金额大于还款金额
            $array = $this->errorreback('60004');
            return $array;
        }
        return $repay_amount;
    }

    private function getDayCount($bank_id) {
        //查询当天成功代扣的次数 
        $begin_time = date('Y-m-d 00:00:00');
        $end_time   = date('Y-m-d 23:59:59');
        $count_day  = (new Loan_repay)->getWithholdCount($bank_id, $begin_time, $end_time);
        if ($count_day >= 5) {
            //每日最多可成功代扣5笔
            $array = $this->errorreback('60005');
            return false;
        }
        return true;
    }

    private function getMonthCount($bank_id) {
        //查询当月成功代扣的次数
        $begin_date  = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));
        $last_date   = date('Y-m-d 23:59:59', strtotime("$begin_date +1 month -1 day"));
        $count_month = (new Loan_repay)->getWithholdCount($bank_id, $begin_date, $last_date);
        if ($count_month >= 30) {
            //每月最多可成功代扣30笔
            $array = $this->errorreback('60006');
            return faks;
        }
    }

    /**
     * 转换
     */
    private function getPayIdentityid($identityid, $cardno) {
        if (!$identityid || !$cardno) {
            return '';
        }
        $card_top   = substr($cardno, 0, 6);
        $card_last  = substr($cardno, -4);
        $identityid = $identityid . '-' . $card_top . $card_last;
        return $identityid;
    }

    public function errorreback($code) {
        $array['rsp_code'] = $code;
        $array['rsp_msg']  = $this->geterrorcode($code);
        return $array;
    }

    /**
     * @abstract 错误提示信息
     *
     * */
    public function geterrorcode($error_code) {
        $array_error_code = array(
            '0000'  => '成功',
            '10001' => '用户未注册',
            '10002' => '用户已注册',
            '10003' => '验证码最多可发送6次',
            '10004' => '验证码失效',
            '10005' => '验证码错误',
            '10006' => '身份证号错误',
            '10007' => '还未设置密码',
            '10008' => '手机或座机号格式错误',
            '10009' => '身份证号格式错误',
            '10010' => '设置密码失败',
            '10011' => '邀请码错误',
            '10012' => '未设置登录密码',
            '10013' => '密码错误',
            '10014' => '使用的邀请码不符合规则',
            '10015' => '注册失败',
            '10016' => '身份证信息填写有误',
            '10017' => '图片上传失败',
            '10018' => '身份信息更新失败',
            '10019' => '学校信息更新失败',
            '10020' => '完善资料提额失败',
            '10021' => '公司信息更新失败',
            '10022' => '学校信息不正确',
            '10023' => '你暂时不能操作此功能',
            '10024' => '用户可用额度小于投资额度',
            '10025' => '先花宝最多投资4000',
            '10026' => '资料信息已经提交过',
            '10027' => '投资先花宝失败',
            '10028' => '原始登陆密码错误',
            '10029' => '原始支付密码错误',
            '10030' => '赎回额度不能大于总投资额度', //1003*代表投资和赎回的错误代码
            '10031' => '还未进行投资',
            '10032' => '赎回失败请重新赎回',
            '10033' => '无消息',
            '10034' => '标的不存在',
            '10035' => '身份证号已经存在',
            '10036' => '担保额度不够',
            '10037' => '标的已募集完成',
            '10038' => '标的剩余份额小于用户投资份额',
            '10039' => '投资标的失败',
            '10040' => '该银行卡已经添加',
            '10041' => '银行卡号错误',
            '10042' => '添加失败，请重新添加',
            '10043' => '银行卡不存在',
            '10044' => '该银行卡不是您的',
            '10045' => '您存在借款或者只有一张借记卡',
            '10046' => '解绑卡失败',
            '10047' => '资料提交不完整',
            '10048' => '借款数据不正确',
            '10049' => '该优惠券您不能使用',
            '10050' => '您存在未完成的借款',
            '10051' => '借款失败',
            '10052' => '借款不存在',
            '10053' => '不符合提现标准',
            '10054' => '该笔借款不能取消',
            '10055' => '担保人未开放app账号',
            '10056' => '被认证人消息不存在',
            '10057' => '投资额度不能大于借款额度的1/3',
            '10058' => '投资额度不能大于您的可用额度',
            '10059' => '输入的投资金额多于未筹满的额度',
            '10060' => '投资失败',
            '10061' => '还款失败',
            '10062' => '由于你的征信记录有瑕疵，暂不可收将收益提现',
            '10063' => '今日已经提现成功一次，明天再来吧',
            '10064' => '今日提现操作已超过三次，明天再来吧',
            '10065' => '请不要频繁操作，稍后再试',
            '10066' => '花二哥下班了哦，7点以后再来吧',
            '10067' => '提现金额必须大于10元',
            '10068' => '提现成功后将打款到你尾号{{{card}}}的{{{bank}}}卡中',
            '10069' => '提现失败',
            '10070' => '您的账单已逾期，不能提现',
            '10071' => '提现金额大于可提现金额',
            '10072' => '更新联系人失败',
            '10073' => '请先完善个人信息',
            '10074' => '请升级到最新版本进行借款',
            '10075' => '该身份证与历史信息不符，请联系微信客服-先花一亿元确认信息安全',
            '10076' => '该身份证已注册，请联系微信客服-先花一亿元确认信息安全',
            '10077' => '请求失败',
            '10078' => '已激活,请等待打款',
            '10079' => '邮箱格式不正确',
            '10080' => '系统升级，请于11月30日 08：00后进行此操作',
            '10081' => '借款信息错误',
            '10082' => '待借款信息不存在',
            '10083' => '获取openid失败',
            '10084' => '还款金额不正确',
            '10085' => '还款记录创建失败',
            '10086' => 'IDFA已存在',
            '10087' => 'IDFA不存在',
            '10088' => '绑定的银行卡不能超过10张',
            '10089' => '续期失败，请重新请求',
            '10090' => '您暂时不能进行续期',
            '4002'  => '邮箱已激活，不能修改邮箱',
            '4003'  => '电话号码格式错误',
            '5000'  => '无版本信息',
            '5001'  => '保存意见反馈失败',
            '99992' => '因春节放假收益提现业务暂停，详情请看首页放假通知',
            '99993' => '尊敬的用户，收益计算已如期完成，请前往先花一亿元微信公众号进行操作，给您造成不便，敬请谅解！',
            '99994' => '参数不能为空',
            '99995' => '接口错误',
            '99996' => '参数错误',
            '99997' => '非法请求',
            '99998' => '签名无效',
            '99999' => '系统错误',
            '60000' => 'IP受限', //贷后系统错误代码
            '60001' => '拉黑失败', //贷后系统错误代码
            '60002' => '借款状态修改失败', //贷后系统错误代码
            '60003' => '该笔借款不能结清',
            '60004' => '扣款金额必须小于应还款金额',
            '60005' => '已超出单日代扣次数限制',
            '60006' => '已超出单月代扣次数限制',
            '60007' => 'Manager日志生成失败',
            '60008' => '代扣操作失败',
            '60009' => '银行卡未绑定',
            '60010' => '银行卡必须为借记卡',
            '60011' => '该笔借款已结清',
            '60012' => '还款信息不存在',
            '60013' => '还款信息保存错误',
            '60014' => '该还款状态不能驳回',
            '60015' => '白名单入库失败',
            '60016' => '缺少分期订单id参数',
            '60017' => '修改逾期状态失败',
            '60018' => '修改分期订单状态失败',
            '60019' => '修改use_loan状态失败',
        );
        return $array_error_code[$error_code];
    }

}

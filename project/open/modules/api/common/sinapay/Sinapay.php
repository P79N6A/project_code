<?php
namespace app\modules\api\common\sinapay;
set_time_limit(120);
if (!class_exists('controller_sina')) {
    include dirname(__File__) . "/controller/controller_sina.php";
}
use app\common\Logger;

class Sinapay {
    /**
     * 方法列表映射
     * @var [type]
     */
    static private $methods = [
        //创建激活会员
        'create_activate_member',
        //设置实名信息
        'set_real_name',
        //绑定认证信息
        'binding_verify',
        //解绑认证信息
        'unbinding_verify',
        //查询认证信息
        'query_verify',
        //绑定银行卡
        'binding_bank_card',
        //绑定银行卡推进
        'binding_bank_card_advance',
        //解绑银行卡
        'unbinding_bank_card',
        //查询我的银行卡
        'query_bank_card',
        //查询余额/基金份额
        'query_balance',
        //查询收支明细接口
        'query_account_details',
        //冻结余额
        'balance_freeze',
        //解冻余额
        'balance_unfreeze',
        //请求企业会员资质审核
        'audit_member_infos',
        //查询企业会员信息
        'query_member_infos',
        //查询企业会员审核结果
        'query_audit_result',
        //sina页面展示用户信息
        'show_member_infos_sina',
        //查询冻结解冻结果
        'query_ctrl_result',
        //经办人信息
        'smt_fund_agent_buy',
        //查询中间账户余额
        'query_middle_account',
        //解绑银行卡推进接口
        'unbinding_bank_card_advance',
        //修改，设置，找回支付密码
        'set_pay_password',
        'set_pay_password',
        'modify_pay_password',
        'find_pay_password',
        //查询是否设置了支付密码
        'query_is_set_pay_password',
        //修改认证手机号，找回认证手机号
        'modify_verify_mobile',
        //找回手机号
        'find_verify_mobile',
        //查询经办人信息
        'query_fund_agent_buy',
        //我的银行卡
        'web_binding_bank_card',
        //创建托管代收接口
        'create_hosting_collect_trade',
        //创建托管代付交易
        'create_single_hosting_pay_trade',
        //创建批量托管代付交易
        'create_batch_hosting_pay_trade',
        //托管交易支付
        'pay_hosting_trade',
        //托管交易查询
        'query_hosting_trade',
        //托管交易批次查询
        'query_hosting_batch_trade',
        //托管退款
        'create_hosting_refund',
        //托管退款查询
        'query_hosting_refund',
        //托管充值
        'create_hosting_deposit',
        //托管充值查询
        'query_hosting_deposit',
        //托管提现
        'create_hosting_withdraw',
        //托管提现查询
        'query_hosting_withdraw',
        //托管转账
        'create_hosting_transfer',
        //支付推进请求
        'advance_hosting_pay',
        //创建单笔代付到提现卡
        'create_single_hosting_pay_to_card_trade',
        //批量代付到提现卡
        'create_batch_hosting_pay_to_card_trade',
        //代收完成交易
        'finish_pre_auth_trade',
        //代收撤销交易
        'cancel_pre_auth_trade',
        //货币基金收益率查询
        'query_fund_yield',

        'query_bid_info',

        //标的录入接口
        'create_bid_info',
        //托管交易支付结果查询
        'query_pay_result',
    ];
    /**
     * 基本参数
     * @var [type]
     */
    static private $base_params = [
        'version' => sinapay_version, //接口版本
        'partner_id' => sinapay_partner_id, //合作商户号
        '_input_charset' => sinapay_input_charset, //字符集编码
        'sign_type' => sinapay_sign_type, //签名类型
    ];

    /**
     * 定义出错数据
     */
    public $errinfo;

    /**
     * 新浪api
     * @var [type]
     */
    private $controller_sina;

    // 最新一次接口执行时间
    private $execute_time;

    //最后一次响应结果
    private $last_response;

    public function __construct() {
        $this->controller_sina = new \controller_sina();
    }
    /**
     * 精确到毫秒的时间戳
     */
    private function mtime() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }
    // 接口执行时间
    public function getExecuteTime() {
        return $this->execute_time;
    }
    // 大于20秒并且无响应结果; 设置为超时
    public function isTimeout() {
        return $this->execute_time > 20 && empty($this->last_response);
    }
    /**
     * 返回错误信息
     * @param  false | null $result 错误信息
     * @param  str $errinfo 错误信息
     * @return false | null 同参数$result
     */
    private function returnError($result, $errinfo) {
        $this->errinfo = $errinfo;
        return $result;
    }
    /**
     * 检测结果:每一个接口均需要调用
     * @param  [] $res 响应结果
     * @return bool, 并设置错误标识
     */
    private function chk_response($res) {
        if (is_array($res) && $res['response_code'] == 'APPLY_SUCCESS') {
            return true;
        }

        // 当错误发生时
        if (!is_array($res) || !isset($res['response_code'])) {
            // 统一返回的错误结果
            $error = is_string($res) ? $res : '未知错误';
            $res = ['response_code' => '__USER_DEFINED', 'response_message' => $error];
        }
        $error = json_encode($res, JSON_UNESCAPED_UNICODE);
        return $this->returnError(false, $error);

    }
    /**
     * 对新浪api方法进行转发
     * @param  [type] $method [description]
     * @param  [type] $args   [description]
     * @return [type]         [description]
     */
    public function __call($method, $args) {
        if (in_array($method, static::$methods, true)) {
            return $this->call_sina_api($method, $args[0]);
        }
        throw new Exception("{$method} is not exists", 1);
    }
    /**
     * 填充基本信息
     * @param  str $method 方法名
     * @param  [] $args 参数
     * @return [] 整合后的参数
     */
    private function fill_base_params($method, $args) {
        if (!is_array($args)) {
            throw new Exception("{$method}: {$args} must be array", 1);
        }
        // 基本参数填充
        $base_params = static::$base_params;
        $base_params['service'] = $method;
        $base_params['request_time'] = date('YmdHis');
        foreach ($base_params as $name => $value) {
            if (!isset($args[$name])) {
                $args[$name] = $value;
            }
        }
        return $args;
    }
    /**
     * 调用新浪接口
     * @param  [string] $method
     * @param  [] $params
     * @return []
     */
    private function call_sina_api($method, $params) {
        //1. 开始时间初始化
        $this->execute_time = 0;
        $temp_time = $this->mtime();
        $this->last_response = null;

        //2. 请求接口
        try {
            $data = $this->fill_base_params($method, $params);
            $this->last_response = call_user_func([$this->controller_sina, $method], $data);
        } catch (\Exception $e) {
            $this->last_response = null;
        }

        //3. 结束纪录日志
        $this->execute_time = round($this->mtime() - $temp_time, 2);
        Logger::dayLog('sinaapi', $method, 'execute_time', $this->execute_time, $params, $this->last_response);

        return $this->last_response;
    }
    /**
     * 创建会员
     * @param  str $identity_id 唯一id
     * @return bool
     */
    public function create_activate_member($identity_id, $ip) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'UID';
        $param['member_type'] = 1; //会员类型 1 个人 2企业 默认 个人
        $param['client_ip'] = $ip;
        $res = $this->call_sina_api(__function__, $param);
        return $this->chk_response($res);
    }

    /**
     * 设置实名信息:
     * @param  str $identity_id 唯一id
     * @return bool
     */
    public function set_real_name($identity_id, $name, $idcard, $ip) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'UID';
        $param['real_name'] = $name;
        $param['cert_type'] = 'IC';
        $param['cert_no'] = $idcard;
        $param['client_ip'] = $ip;

        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return true;
        }

        // 重复当成正确处理
        if ($res['response_code'] == 'DUPLICATE_VERIFY') {
            return true;
        } else {
            return false;
        }
        /*
    [response_code] => DUPLICATE_VERIFY
    [response_message] => 实名已认证
     */
    }
    /**
     * 绑定认证信息:此接口重复当做正确
     * @param  str $identity_id 唯一id
     * @param str $phone 认证手机号
     * @return bool
     */
    public function binding_verify($identity_id, $phone, $ip) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'UID';
        $param['verify_type'] = 'MOBILE'; //EMAIL
        $param['verify_entity'] = $phone;
        $param['client_ip'] = $ip;

        $res = $this->call_sina_api(__function__, $param);
        return $this->chk_response($res);
    }

    /**
     * 查询认证信息
     * 返回手机号
     * @param  str $identity_id [description]
     * @return str   手机号 | 空
     */
    public function query_verify($identity_id) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'UID';
        $param['verify_type'] = 'MOBILE';
        $param['is_mask'] = 'N';

        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return $res['verify_entity'];
        } else {
            return '';
        }
        /*
    'response_code' =>  'APPLY_SUCCESS'
    'response_message' =>  '提交成功'
    'response_time' =>  '20160727155955'
    'verify_entity' =>  '13581524011'
     */
    }

    /**
     * 绑定银行卡: 此接口将重复的作为成功处理
     * @param  str $identity_id 认证
     * @param  str $request_no 请求号
     * @param  [] $data
     * @return int sina_card_id
     */
    public function binding_bank_card($identity_id, $request_no, $data) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'UID';
        $param['request_no'] = $request_no; //绑卡请求号
        $param['phone_no'] = ''; // 预留手机, 可空
        $param['province'] = isset($data['province']) ? $data['province'] : '北京';
        $param['city'] = isset($data['city']) ? $data['city'] : '北京';
        $param['verify_mode'] = ''; // 是否严格认证: 空:不认证, SIGN:严格
        $param['bank_code'] = $data['bank_code']; //银行编码 ICBC
        $param['bank_account_no'] = $data['bank_account_no']; //银行卡号
        $param['card_type'] = $data['card_type']; // DEBIT:借记; CREDIT:贷记（信用卡）
        $param['card_attribute'] = 'C'; //C 对私 B 对公
        $param['client_ip'] = $data['ip']; //ip地址

        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return $res['card_id'];
        } else {
            return 0;
        }
        /*
    'card_id' => '118546', // 用于解绑用
    'is_verified' => 'N', // 是否严格认证, 即verify_mode, 此字段没什么用
    'response_code' => 'APPLY_SUCCESS',
    'response_message' => '提交成功',
     */
    }
    /**
     * 设置密码
     * @param  str $identity_id 唯一id
     * @param  string $service  支付密码
     *    设置 set_pay_password
     *    修改 modify_pay_password
     *    找回 find_pay_password
     * @param  string $return_url  回调地址
     * @return str http链接
     */
    public function all_pay_password($identity_id, $service, $return_url) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'UID';
        $param['service'] = $service;
        $param['return_url'] = $return_url;
        $param['withhold_param'] = 'withhold_auth_type^NONE,ACCOUNT|is_check^N';

        $res = $this->call_sina_api(__function__, $param);

        $res = json_decode($res, true);
        if ($this->chk_response($res)) {
            return $res['redirect_url'];
        } else {
            return '';
        }
        /*
    response_code => APPLY_SUCCESS
    response_message => 提交成功
    redirect_url => https://test.pay.sina.com.cn/zjtg/website/view/set_paypwd.html?ft=4d01c2b8-2104-445c-a7f4-b3076cb54311
     */
    }
    /**
     * 查询是否设置密码
     * @param  str $identity_id 唯一id
     * @return bool
     */
    public function query_is_set_pay_password($identity_id) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'UID';

        $res = $this->call_sina_api(__function__, $param);

        if ($this->chk_response($res)) {
            return $res['is_set_paypass'] == 'Y';
        } else {
            return false;
        }
        /*
    [is_set_paypass] => Y
    [response_code] => APPLY_SUCCESS
    [response_message] => 提交成功
     */
    }

    // start 标的 录入
    /**
     * 标的添加
     * @param  [] $data 标的信息
     * @return bool
     */
    public function create_bid_info($data) {
        $param = [];
        $param['out_bid_no'] = $data['out_bid_no']; //商户标的号
        $param['web_site_name'] = $data['web_site_name']; // 平台名称, 先花一亿元
        $param['bid_name'] = $data['bid_name']; // 标的名称, 此名称不可重复
        $param['bid_type'] = $data['bid_type']; //CREDIT信用; MORTGAGE抵押; ASSIGNMENT_DEBT债权转让; OTHER其他
        $param['bid_amount'] = $data['bid_amount']; // 借款金额
        $param['bid_year_rate'] = $data['bid_year_rate']; // 年化率
        $param['bid_duration'] = $data['out_bid_no']; // 借款期限 20

        // repay_type
        // REPAY_CAPITAL_WITH_INTEREST 一次还本付息
        // AVERAGE_CAPITAL 等额本金
        // AVERAGE_CAPITAL_PLUS_INTERES 等额本息
        // SCHEDULED_INTEREST_PAYMENTS_DUE 按期付息到期还本
        // OTHER 其他
        $param['repay_type'] = $data['repay_type'];
        $param['begin_date'] = $data['begin_date']; //标的开始时间
        $param['term'] = $data['term']; //还款期限
        $param['guarantee_method'] = $data['guarantee_method']; //担保方式 企业担保 Xx保险担保 银行担保
        $param['borrower_info_list'] = "{$data['borrower_id']}~UID~{$data['bid_amount']}~{$data['borrower_tip']}~{$data['borrower_mobile']}";

        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return $res['bid_status'] == 'VALID';
        } else {
            return false;
        }
        /*
    [bid_status] => VALID  | REJECT
    [gmt_create] => 20160729101114
    [gmt_modify] => 20160729101114
    [inner_bid_no] => 411469758274021204830
    [out_bid_no] => 20160729101046
    [partner_id] => 200004595271
    [response_code] => APPLY_SUCCESS
    [response_message] => 提交成功
     */
    }
    /**
     * 查询标的
     * @param  str $out_bid_no 业务端唯一请求号
     * @return [] 标的信息
     */
    public function query_bid_info($out_bid_no) {
        $param = ['out_bid_no' => $out_bid_no];
        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return $res;
        } else {
            return null;
        }
    }
    // end 标的

    // 出款相关
    /**
     * 出款操作
     * @param  [] $data 出款金额等
     * @return []  出款信息
     */
    public function create_single_hosting_pay_to_card_trade($data) {
        $param = [];
        $param['notify_url'] = $data['notify_url'];
        $param['out_trade_no'] = $data['out_trade_no'];
        //2001 代付借款金
        //2002 代付（本金/收益）金
        $param['out_trade_code'] = $data['out_trade_code'];
        $param['amount'] = $data['amount'];
        $param['collect_method'] = "binding_card^{$data['identity_id']},UID,{$data['sina_card_id']}";
        $param['goods_id'] = $data['goods_id']; // @todo 可能不需要了
        $param['summary'] = $data['summary'];
        $param['payto_type'] = 'FAST'; //GENERAL： 普通; FAST: 快速
        $param['user_ip'] = $data['ip'];
        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return $res;
        } else {
            return null;
        }
        /*
    'out_trade_no' => '1469764459',
    'response_code' => 'APPLY_SUCCESS',
    'response_message' => '提交成功',
    'withdraw_status' => 'PROCESSING',
     */
    }
    /**
     * 支付: 目前来仅用于是否提交成功
     * @param str $out_pay_no 出款请求号
     * @return []  出款信息
     */
    public function query_pay_result($out_pay_no) {
        $param = ['out_pay_no' => $out_pay_no];
        $res = $this->call_sina_api(__function__, $param);
        return $res;
    }
    /*
     * 提现结果查询
     */
    /**
     * [query_hosting_withdraw description]
     * @param  string $identity_id
     * @param   $out_trade_no
     * @return
     */
    public function query_hosting_withdraw($identity_id, $out_trade_no) {
        $param = [
            'identity_id' => $identity_id,
            'identity_type' => 'UID',
            'out_trade_no' => $out_trade_no,
        ];

        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            if (isset($res['total_item']) && $res['total_item'] > 0) {
                return $res;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * 交互:这个应该不是提现接口
     * @param str $out_pay_no 出款请求号
     * @return []  出款信息
     */
    public function query_hosting_trade($identity_id, $out_trade_no) {
        $param = [
            'identity_id' => $identity_id,
            'identity_type' => 'UID',
            'out_trade_no' => $out_trade_no,
        ];
        $res = $this->call_sina_api(__function__, $param);
        return $res;
    }

    /**
     * 收款操作
     * @param  [] $data 出款金额等
     * @return []  出款信息
     */
    public function create_hosting_collect_trade($param) {
        $param["summary"] = '无';
        /**
         * 置未付款交易的超时时间，一旦超时，该笔交易就会自动被关闭。
         * 取值范围：1m～15d。 m-分钟，h-小时，d-天 不接受小数点，如1.5d，可转换为36h。
         */
        $param["trade_close_time"] = '1h'; // 1小时
        /**
         * 支付失败后，是否可以重复发起支付
         * 取值范围：Y、N(忽略大小写) Y：可以再次支付 N：不能再次支付
         * 默认值为Y
         */
        $param["can_repay_on_failed"] = 'N';

        $res = $this->call_sina_api(__function__, $param);
        /*最终交易结果以交易状态为准，
        状态为
        “PAY_FINISHED”表示用户已付款完成；
        “TRADE_FINISHED”表示交易资金处理已完成
         */
        $res = json_decode($res, true);
        if ($this->chk_response($res)) {
            return $res;
        } else {
            return null;
        }
    }

    /**
     * 公司内部转帐: 基本户->中间户
     * @param  [] $data
     * @return []
     */
    public function inner_pay_money($out_trade_no, $amount, $ip) {
        $data = [];
        $data["out_trade_no"] = $out_trade_no;
        $data['amount'] = $amount;

        $data["out_trade_code"] = '1001'; //1001:代收投资金; 1002:代收还款金

        $data['payer_id'] = 'xinlangzijintuoguan@xianhuahua.com';
        $data["payer_identity_type"] = 'EMAIL';

        $data["pay_method"] = 'balance';
        $data["account_type"] = 'BASIC';

        $data["return_url"] = isset($data["return_url"]) ? $data["return_url"] : 'http://open.xianhuahua.com/api/sinaback/innerpaysync'; // 同步通知
        $data["notify_url"] = isset($data["notify_url"]) ? $data["notify_url"] : 'http://open.xianhuahua.com/api/sinaback/innerpaynotify'; // 异步通知

        $data["payer_ip"] = $ip;
        return $this->create_hosting_collect_trade($data);
    }
    /**
     * 公司内部转帐: 基本户-> 还款
     * @param  [] $data
     * @return []
     */
    public function inner_pay_money_repay($out_trade_no, $amount, $ip) {
        $data = [];
        $data["out_trade_no"] = $out_trade_no;
        $data['amount'] = $amount;

        $data["out_trade_code"] = '1002'; //1001:代收投资金; 1002:代收还款金

        $data['payer_id'] = 'xinlangzijintuoguan@xianhuahua.com';
        $data["payer_identity_type"] = 'EMAIL';

        $data["pay_method"] = 'balance';
        $data["account_type"] = 'BASIC';

        $data["return_url"] = isset($data["return_url"]) ? $data["return_url"] : 'http://open.xianhuahua.com/api/sinaback/innerpaysync'; // 同步通知
        $data["notify_url"] = isset($data["notify_url"]) ? $data["notify_url"] : 'http://open.xianhuahua.com/api/sinaback/innerpaynotify'; // 异步通知

        $data["payer_ip"] = $ip;
        return $this->create_hosting_collect_trade($data);
    }
    /**
     * 公司内部转帐
     * 2001: 投资金->基本户
     * 2002: 还款金->基本户
     * @param  [type] $out_trade_no [description]
     * @param  [type] $amount       [description]
     * @param  [type] $ip           [description]
     * @return [type]               [description]
     */
    public function create_single_hosting_pay_trade($out_trade_no, $amount, $ip) {
        $data = [];

        $data["notify_url"] = isset($data["notify_url"]) ? $data["notify_url"] : 'http://open.xianhuahua.com/api/sinaback/innerpaynotify'; // 异步通知
        $data["out_trade_code"] = '2001'; //2001:代收投资金; 2002:代收还款金
        $data["out_trade_no"] = $out_trade_no;

        $data['payee_identity_id'] = 'xinlangzijintuoguan@xianhuahua.com';
        $data["payee_identity_type"] = 'EMAIL';

        $data["account_type"] = 'BASIC';
        $data['amount'] = $amount;

        $data["summary"] = '无';
        $data["user_ip"] = $ip;

        $res = $this->call_sina_api(__function__, $data);
        /*最终交易结果以交易状态为准，
        状态为
        “PAY_FINISHED”表示用户已付款完成；
        “TRADE_FINISHED”表示交易资金处理已完成
         */
        print_r($res);
        //$res = json_decode($res, true);
        if ($this->chk_response($res)) {
            return $res;
        } else {
            return null;
        }

    }
    /**
     * 查询中间帐号余额
     * @return [type] [description]
     */
    public function query_middle_account($out_trade_code) {
        $out_bid_no = (string) time();
        /*
        1000  代收-其它（如需要使用，请联系运营申请，新浪支付将会开设单独的中间账户）
        1001  代收投资金
        1002  代收还款金
        2000  代付-其他（如需要使用，请联系运营申请，新浪支付将会开设单独的中间账户）
        2001  代付借款金
        2002  代付（本金/收益）金
         */
        $param = [];
        $param['out_trade_code'] = $out_trade_code;
        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return $res['account_list'];
        } else {
            return null;
        }
    }
    /**
     * 查询中间帐号余额
     * @return [type] [description]
     */
    public function query_balance($identity_id) {
        $param = [];
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = 'MEMBER_ID';
        $res = $this->call_sina_api(__function__, $param);
        if ($this->chk_response($res)) {
            return $res['available_balance'];
        } else {
            return null;
        }
    }
}

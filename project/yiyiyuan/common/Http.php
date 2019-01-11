<?php

include_once 'Logger.php';
include_once 'Crypt3Des.php';

class Http {

    /**
     * 用户信息查询
     * @param int $mobile 用户手机号码
     */
    public static function getUserInfo($mobile) {
        $app_key = Yii::$app->params['xianhua_app_key'];
        $key = Yii::$app->params['xianhua_key'];
        $version = '1.0';
        $service_type = 'com.xianhuahua.activity';
        $sign = md5($app_key . $mobile . $service_type . $version);

        $url = Yii::$app->params['xianhua_url'];
        $data = 'app_key=' . $app_key . '&service_type=' . $service_type . '&mobile=' . $mobile . '&version=' . $version . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'getUserInfo');
        return $result;
    }

    /**
     * 连连支付接口
     * @param type $user_id 用户唯一标识id
     * @param type $merchant_type   请求业务类型
     * @param type $app_request wap:1 Android,2 ios,3   请求源
     * @param type $timestamp 时间戳
     * @param type $no_order    商户订单号
     * @param type $dt_order    订单日期
     * @param type $name_goods  商品名称
     * @param type $money_order 交易金额
     * @param type $notify_url  服务器异步通知地址
     * @param type $url_return  支付结束回显url
     * @param type $risk_item   风险控制参数
     * @param type $bind_mob 银行预留手机
     * @return type
     */
    public static function payLianLian($user_id, $merchant_type, $app_request, $no_order, $dt_order, $name_goods, $money_order, $notify_url, $url_return, $risk_item, $pay_type, $id_no, $acct_name, $card_no, $busi_partner = '', $valid_order = '0', $flag_modify = '0') {
        //获取当前参数
        $rc = new ReflectionClass('Http');
        $refle = $rc->getMethod('payLianLian')->getParameters();
        $parms = array();
        foreach ($refle as $p) {
            $parms[$p->name] = ${$p->name};
        }
        $md5_key = Yii::$app->params['xianhua_key'];
        $parms['ak'] = Yii::$app->params['ak'];
        $parms['service_type'] = 'open.api.service.llpay.payment';
        $parms['version'] = '1.0';

        if ($busi_partner == '') {
            unset($parms['busi_partner']);
        }
        if ($valid_order == '0') {
            unset($parms['valid_order']);
        }
        if ($flag_modify == '0') {
            unset($parms['flag_modify']);
        }
//         if ($cvv2 == '') {
//             unset($parms['cvv2']);
//         }
//         if ($validate == '') {
//             unset($parms['validate']);
//         }
        $data = '';
        $des3key = Yii::$app->params['des3key'];
        foreach ($parms as $key => $vals) {
            if ($key == 'cvv2') {
                $data .='&' . $key . '=' . urlencode(Crypt3Des::encrypt($vals, $des3key));
            } else {
                $data .='&' . $key . '=' . urlencode($vals);
            }
        }
        $data = substr($data, 1, strlen($data));
        $sign = self::createMd5($parms, $md5_key);
        //$sign = md5($str_md5);
        $url = Yii::$app->params['xianhua_url'];
        $data .='&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'payLianLian');
        //验签返回的sign
        $re_sign = self::createMd5($result, $md5_key);
        if (isset($result->sign) && $result->sign == $re_sign) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 提交验证码完成支付
     * @param type $merchant_type   virtual:小额虚拟,话费充值 entity:实名类,互联网还款  请求业务类型
     * @param type $app_request     wap:1 Android,2 ios,3    请求源
     * @param type $pay_key     支付key
     * @param type $bind_mob    银行卡预留手机号
     * @param type $verifyCode  验证码
     * @param type $isrecord    yes:绑定 no:不绑定  是否绑定为常用卡
     */
    public static function subPayLian($merchant_type, $app_request, $pay_key, $bind_mob, $verifyCode, $isrecord) {
        $md5_key = Yii::$app->params['xianhua_key'];
        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.llpay.wap.completepay';
        $version = '1.0';
        $sign = md5($ak . $app_request . $bind_mob . $isrecord . $merchant_type . $pay_key . $service_type . $verifyCode . $version . $md5_key);

        $url = Yii::$app->params['xianhua_url'];
        $data = 'ak=' . $ak . '&app_request=' . $app_request . '&bind_mob=' . $bind_mob . '&isrecord=' . $isrecord . '&merchant_type=' . $merchant_type . '&pay_key=' . $pay_key . '&service_type=' . $service_type . '&verifyCode=' . $verifyCode . '&version=' . $version . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        if (!empty($result)) {
            \Logger::errorLog(print_r($result, true), 'subPayLian');
        } else {
            \Logger::errorLog(print_r('返回为空', true), 'subPayLian');
        }

        $re_sign = self::createMd5($result, $md5_key);
        if (isset($result->sign) && $result->sign == $re_sign) {
            return $result;
        } else {
            return false;
        }
    }

    public static function createMd5($result, $md5_key, $sign_type = 0) {
        //不加入签名的字段
        $arr = array(
            'sign',
            'rsp_msg',
        );
        if ($sign_type == 0) {
            $arr[] = 'sign_type';
        }
        if (is_array($result)) {
            foreach ($arr as $val) {
                if (isset($result[$val])) {
                    unset($result[$val]);
                }
            }
            ksort($result);
            $str_md5 = '';
            foreach ($result as $vals) {
                $str_md5 .=$vals;
            }
            $str_md5 .=$md5_key;
            return $sign = md5($str_md5);
        } else if (is_object($result)) {
            $arrs = array();
            foreach ($result as $key => $v) {
                $arrs[$key] = $v;
            }
            return self::createMd5($arrs, $md5_key);
        }
    }

    //学籍验证接口
    public static function getStuVerify($mobile, $real_name, $card_no, $school_id, $edu_levels, $start_date) {
        $app_key = Yii::$app->params['xianhua_app_key'];
        $key = Yii::$app->params['xianhua_key'];
        $version = '1.0';
        $service_type = 'com.xianhuahua.stu.verification';
        $sign = md5($app_key . $card_no . $edu_levels . $mobile . $real_name . $school_id . $service_type . $start_date . $version);

        $url = Yii::$app->params['xianhua_url'];
        $data = 'app_key=' . $app_key . '&service_type=' . $service_type . '&mobile=' . $mobile . '&version=' . $version . '&real_name=' . $real_name . '&card_no=' . $card_no . '&school_id=' . $school_id . '&edu_levels=' . $edu_levels . '&start_date=' . $start_date . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'getStuVerify');
        return $result;
    }

    public static function sendByMobile($mobile, $content) {//拓鹏云信http://www.topencrm.com
        $userCode = 'XHJS2';
        $userPass = 'xianhuahua1605';
        $desNo = $mobile;
        $msg = $content . '【先花一亿元】';
        $channel = '';
        $url = 'http://121.199.48.186:1210/Services/MsgSend.asmx/SendMsg';
        $data = 'userCode=' . $userCode . '&userPass=' . $userPass . '&DesNo=' . $desNo . '&Msg=' . $msg . '&Channel=' . $channel;
        $ret = self::interface_post($url, $data);
        return $ret;
    }

    /**
     * 接口请求方式
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    public static function interface_post($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    public static function getCurl($url) {//get https的内容
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不输出内容
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public static function dataPost($post_string, $url) {//POST方式提交数据
        $context = array('http' => array('method' => "POST", 'header' => "Content-type: application/x-www-form-urlencoded", 'content' => $post_string));
        $stream_context = stream_context_create($context);
        $data = file_get_contents($url, FALSE, $stream_context);
        return $data;
    }

    public static function sendTemplatePost($post_content, $url) {
        $context = array('http' => array('method' => "POST", 'header' => "Content-type: application/x-www-form-urlencoded", 'content' => http_build_query($post_content)));
        $stream_context = stream_context_create($context);
        $data = file_get_contents($url, FALSE, $stream_context);
        return $data;
    }

    public static function stuVerfiy($school, $edu, $school_time, $realname, $identity) {
        if (empty($school) || empty($edu) || empty($school_time) || empty($realname) || empty($identity)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.certify';
        $key = Yii::$app->params['xianhua_key'];
        $version = '1.0';
        $auth_type = 'AUTH_STUDENT';
        $real_name = $realname;
        $cred_no = $identity;
        $admission_date = $school_time;
        $college = $school;
        $college_level = $edu;
        $sign = md5($admission_date . $ak . $auth_type . $college . $college_level . $cred_no . $real_name . $service_type . $version . $key);

        $url = Yii::$app->params['xianhua_url'];
        $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&auth_type=' . $auth_type . '&real_name=' . rawurlencode($real_name) . '&cred_no=' . $cred_no . '&admission_date=' . $admission_date . '&college=' . rawurlencode($college) . '&college_level=' . rawurlencode($college_level) . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'stuVerfiy');

        if ((trim($result->rsp_code) == '0000') && (trim($result->result) == 'AUTHOK')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 量化派
     * @param string $userId  用户id
     * @param string $realname    用户姓名
     * @param string $identity    用户身份证号码
     * @return object || false
     */
    public static function quantitative($userId, $realname, $identity) {
        if (empty($userId) || empty($realname) || empty($identity)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.lianghuapai.eduinfoaction';
        $key = Yii::$app->params['xianhua_key'];
        $version = '1.0';
        $real_name = $realname;
        $card_no = $identity;
        $user_id = $userId;
        $sign = md5($ak . $card_no . $real_name . $service_type . $user_id . $version . $key);

        $url = Yii::$app->params['xianhua_url'];
        $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&userId=' . $user_id . '&realname=' . rawurlencode($real_name) . '&cardNo=' . $card_no . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'quantitative');
        $re_sign = self::createMd5($result, $key);
        if (isset($result->sign) && $result->sign == $re_sign) {
            return $result;
        } else {
            return false;
        }
    }

    //身份验证接口
    public static function identity_check($real_name, $cred_no) {
        if (empty($real_name) || empty($cred_no)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.certify';
        $key = Yii::$app->params['xianhua_key'];
        $version = '1.0';
        $auth_type = 'AUTH_IDCARD';
        $sign = md5($ak . $auth_type . $cred_no . $real_name . $service_type . $version . $key);

        $url = Yii::$app->params['xianhua_url'];
        $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&auth_type=' . $auth_type . '&real_name=' . rawurlencode($real_name) . '&cred_no=' . $cred_no . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'identity_check');
        if ((trim($result->rsp_code) == '0000') && (trim($result->result) == 'AUTHOK')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 打款结算接口
     * @param type  $ak  访问的key 服务器获得
     * @param type  $service_type 接口名称
     * @param type  $version   版本号
     * @param type  $user_mobile  用户的手机号
     * @param type  $user_name 用户的姓名
     * @param type  $guest_account_name  持卡人姓名
     * @param type  $guest_account 银行卡号
     * @param type  $guest_account_bank 开户行名称
     * @param type  $guest_account_province 银行所属省
     * @param type  $guest_account_city 银行所属的市
     * @param type  $guest_account_bank_branch  银行所属的支行 非必须 给一个默认值
     * @param type  $account_type 账户类型
     * $settle_amount  打款金额
     * @param type  $sign 签名
     */
    public static function balance($req_id, $user_mobile, $user_name, $settle_amount, $guest_account_name, $guest_account, $guest_account_bank, $guest_account_province, $guest_account_city, $guest_account_bank_branch, $account_type) {
        if (empty($user_mobile) || empty($user_name) || empty($guest_account_name) || empty($guest_account) || empty($guest_account_bank) || empty($guest_account_province) || empty($guest_account_city) || empty($guest_account_bank_branch)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.remit';
        $key = Yii::$app->params['xianhua_key'];
        $version = '1.0';

        //echo $account_type . $ak . $guest_account . $guest_account_bank . $guest_account_bank_branch . $guest_account_city . $guest_account_name . $guest_account_province . $service_type . $settle_amount . $user_mobile .$user_name. $version . $key;exit;
        $sign = md5($account_type . $ak . $guest_account . $guest_account_bank . $guest_account_bank_branch . $guest_account_city . $guest_account_name . $guest_account_province . $req_id . $service_type . $settle_amount . $user_mobile . $user_name . $version . $key);
        $url = Yii::$app->params['xianhua_url'];
        $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&user_mobile=' . $user_mobile . '&user_name=' . rawurlencode($user_name) . '&guest_account_name=' . rawurlencode($guest_account_name) . '&guest_account=' . $guest_account . '&guest_account_bank=' . rawurlencode($guest_account_bank) . '&guest_account_province=' . rawurlencode($guest_account_province) . '&guest_account_city=' . rawurlencode($guest_account_city) . '&settle_amount=' . $settle_amount . '&guest_account_bank_branch=' . rawurlencode($guest_account_bank_branch) . '&account_type=' . $account_type . '&req_id=' . $req_id . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'balance');
        return $result;
    }

    public static function riskDecision_register_student($account_name, $account_mobile, $id_number, $ext_school, $ext_diploma, $ext_start_year, $seq_id, $ext_birth_year, $token_id = '') {
        if (empty($account_name) || empty($account_mobile) || empty($id_number) || empty($ext_school) || empty($ext_diploma) || empty($ext_start_year) || empty($seq_id)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.risk';
        $key = Yii::$app->params['xianhua_key'];
        $event_type = 'REG';
        $version = '1.0';
        $url = Yii::$app->params['xianhua_url'];
        if (empty($token_id)) {
            $sign = md5($account_mobile . $account_name . $ak . $event_type . $ext_birth_year . $ext_diploma . $ext_school . $ext_start_year . $id_number . $seq_id . $service_type . $version . $key);
            $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&ext_school=' . rawurlencode($ext_school) . '&ext_diploma=' . rawurlencode($ext_diploma) . '&ext_start_year=' . $ext_start_year . '&seq_id=' . $seq_id . '&sign=' . $sign;
        } else {
            $sign = md5($account_mobile . $account_name . $ak . $event_type . $ext_birth_year . $ext_diploma . $ext_school . $ext_start_year . $id_number . $seq_id . $service_type . $token_id . $version . $key);
            $data = 'ak=' . $ak . '&token_id=' . $token_id . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&ext_school=' . rawurlencode($ext_school) . '&ext_diploma=' . rawurlencode($ext_diploma) . '&ext_start_year=' . $ext_start_year . '&seq_id=' . $seq_id . '&sign=' . $sign;
        }
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'riskDecision_register_student');
        return $result;
    }

    public static function riskDecision_register_company($account_name, $account_mobile, $id_number, $ext_industry, $organization, $ext_position, $seq_id, $ext_birth_year, $token_id = '') {
        if (empty($account_name) || empty($account_mobile) || empty($id_number) || empty($ext_industry) || empty($organization) || empty($ext_position) || empty($seq_id)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.risk';
        $key = Yii::$app->params['xianhua_key'];
        $event_type = 'REG';
        $version = '1.0';
        $url = Yii::$app->params['xianhua_url'];
        if (empty($token_id)) {
            $sign = md5($account_mobile . $account_name . $ak . $event_type . $ext_birth_year . $ext_industry . $ext_position . $id_number . $organization . $seq_id . $service_type . $version . $key);
            $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&ext_industry=' . rawurlencode($ext_industry) . '&organization=' . rawurlencode($organization) . '&ext_position=' . rawurlencode($ext_position) . '&seq_id=' . $seq_id . '&sign=' . $sign;
        } else {
            $sign = md5($account_mobile . $account_name . $ak . $event_type . $ext_birth_year . $ext_industry . $ext_position . $id_number . $organization . $seq_id . $service_type . $token_id . $version . $key);
            $data = 'ak=' . $ak . '&token_id=' . $token_id . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&ext_industry=' . rawurlencode($ext_industry) . '&organization=' . rawurlencode($organization) . '&ext_position=' . rawurlencode($ext_position) . '&seq_id=' . $seq_id . '&sign=' . $sign;
        }
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'riskDecision_register_company');
        return $result;
    }

    public static function riskDecision_loan($account_name, $account_mobile, $id_number, $ext_school, $ext_diploma, $ext_start_year, $card_number, $pay_amount, $event_occur_time, $seq_id, $ext_birth_year, $token_id = '') {
        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.risk';
        $key = Yii::$app->params['xianhua_key'];
        $event_type = 'LOAN';
        $version = '1.0';
        $url = Yii::$app->params['xianhua_url'];
        if (empty($token_id)) {
            $sign = md5($account_mobile . $account_name . $ak . $card_number . $event_occur_time . $event_type . $ext_birth_year . $ext_diploma . $ext_school . $ext_start_year . $id_number . $pay_amount . $seq_id . $service_type . $version . $key);
            $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&ext_school=' . rawurlencode($ext_school) . '&ext_diploma=' . rawurlencode($ext_diploma) . '&ext_start_year=' . $ext_start_year . '&card_number=' . $card_number . '&pay_amount=' . $pay_amount . '&event_occur_time=' . $event_occur_time . '&seq_id=' . $seq_id . '&sign=' . $sign;
        } else {
            $sign = md5($account_mobile . $account_name . $ak . $card_number . $event_occur_time . $event_type . $ext_birth_year . $ext_diploma . $ext_school . $ext_start_year . $id_number . $pay_amount . $seq_id . $service_type . $token_id . $version . $key);
            $data = 'ak=' . $ak . '&token_id=' . $token_id . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&ext_school=' . rawurlencode($ext_school) . '&ext_diploma=' . rawurlencode($ext_diploma) . '&ext_start_year=' . $ext_start_year . '&card_number=' . $card_number . '&pay_amount=' . $pay_amount . '&event_occur_time=' . $event_occur_time . '&seq_id=' . $seq_id . '&sign=' . $sign;
        }
        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'riskDecision_loan');
        return $result;
    }

    //点赞算法
    public static function clickLike($clickCount) {
        $clickCount += 1;
        $k_amount = 0;
        $i = floor($clickCount / 5);
        $endAmount = 0.5;
        for ($n = $i; $n > 0; $n--) {
            $endAmount *= $endAmount;
        }

        $endAmount = 2 * ($endAmount / 0.5);
        $k_amount = self::randomFloat(0, $endAmount);
        $k_amount = round($k_amount, 2);
        if ($k_amount <= 0) {
            $k_amount = 0.01;
        }
        return $k_amount;
    }

    public static function randomFloat($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    //大学生额度算法
    public static function studentAmountStat($userscore) {
        $score_city = $userscore['city'];
        $score_school = $userscore['school'];
        $score_grade = $userscore['grade'];
        $score_degree = $userscore['degree'];
        ////用户评分
        $user_score = $score_city * 3 + $score_degree * 2 + $score_school * 1.5 + $score_grade;

        //////////还款能力
        $user_ability = $user_score;
        for ($i = 4; $i > 0; $i--) {//5次方
            $user_ability = $user_ability * $user_score;
        }

        $user_ability = round(($user_ability / 1800000 * 4 + 100 ) / 50);

        $user_ability = $user_ability * 50;

        ///////////////
        //授信额度
        $user_given = round($user_ability / 150) * 50;

        $user_given = $user_given - 100; //初始授信时都没有照片

        if ($user_given >= 200) {
            return $user_given;
        } else {
            return 200; //最低200
        }
    }

    //上班族额度算法
    public static function workAmountStat($userscore) {
        $score_city = $userscore['city'];
        $score_work = $userscore['work'];
        $score_job = $userscore['job'];
        ////用户评分
        $user_score = $score_city * 3 + $score_work * 2 + $score_job * 1.5;

        //////////还款能力
        $user_ability = $user_score;
        for ($i = 4; $i > 0; $i--) {//5次方
            $user_ability = $user_ability * $user_score;
        }
        $user_ability = round(($user_ability / 1800000 * 4 + 100 ) / 50);
        $user_ability = $user_ability * 50;
        //授信额度
        $user_given = round($user_ability / 150) * 50;

        $user_given = $user_given - 100; //初始授信时都没有照片

        if ($user_given >= 200) {
            return $user_given;
        } else {
            return 200; //最低200
        }
    }

    public static function checkport($port = null) {
        $a = $port;

        $rule = array(
            '####',
            'ABCD',
            'DCBA',
            'ABAB',
            'ABBA',
            '##**',
            '#####',
            'A####',
            '###**',
            '##***',
            'ABCDE',
            'EDCBA',
            'ABC**',
            'ABCDX',
            'DCBAX',
            'CBA**',
            '##ABC',
            'XABCD',
            '##X**',
            '######',
            '###***',
            '##**%%',
            '##X**X',
            'X##X**',
            'ABCDEF',
            'FEDCBA',
            '###BCD',
            'ABC***',
            'AB##BA'
        );
        $str = "";
        $pre = "";
        $now = "";
        $len = strlen($a);
        if ($len == 4) {
            $s1 = $a[0];
            $s2 = $a[1];
            $s3 = $a[2];
            $s4 = $a[3];
            if ($s1 == $s2 && $s2 == $s3 && $s3 == $s4 && $s4 == $s1) {
                return true;
            } else if ($s1 == $s2 && $s3 == $s4) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s2) + 1 == intval($s3) && intval($s3) + 1 == $s4) {
                return true;
            } else if (intval($s1) - 1 == intval($s2) && intval($s2) - 1 == intval($s3) && intval($s3) - 1 == $s4) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s3) - 1 == intval($s4) && $s2 == $s3) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s3) + 1 == intval($s4) && $s1 == $s3) {
                return true;
            }
        }
        ////////////////////////////////
        else if ($len == 5) {
            $s1 = $a[0];
            $s2 = $a[1];
            $s3 = $a[2];
            $s4 = $a[3];
            $s5 = $a[4];
            if ($s1 == $s2 && $s2 == $s3 && $s3 == $s4 && $s4 == $s5 && $s5 == $s1) {
                return true;
            } else if ($s2 == $s3 && $s3 == $s4 && $s4 == $s5) {
                return true;
            } else if ($s1 == $s2 && $s2 == $s3 && $s4 == $s5) {
                return true;
            } else if ($s1 == $s2 && $s3 == $s4 && $s4 == $s5) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s2) + 1 == intval($s3) && intval($s3) + 1 == $s4 && intval($s4) + 1 == intval($s5)) {
                return true;
            } else if (intval($s1) - 1 == intval($s2) && intval($s2) - 1 == intval($s3) && intval($s3) - 1 == $s4 && intval($s4) - 1 == intval($s5)) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s2) + 1 == intval($s3) && $s4 == $s5) {
                return true;
            } else if (intval($s1) - 1 == intval($s2) && intval($s2) - 1 == intval($s3) && $s4 == $s5) {
                return true;
            } else if ($s1 == $s2 && intval($s3) + 1 == intval($s4) && intval($s4) + 1 == intval($s5)) {
                return true;
            } else if ($s1 == $s2 && intval($s3) - 1 == intval($s4) && intval($s4) - 1 == intval($s5)) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s2) + 1 == intval($s3) && intval($s3) + 1 == $s4) {
                return true;
            } else if (intval($s1) - 1 == intval($s2) && intval($s2) - 1 == intval($s3) && intval($s3) - 1 == $s4) {
                return true;
            } else if (intval($s2) + 1 == intval($s3) && intval($s3) + 1 == intval($s4) && intval($s4) + 1 == $s5) {
                return true;
            } else if (intval($s2) - 1 == intval($s3) && intval($s3) - 1 == intval($s4) && intval($s4) - 1 == $s5) {
                return true;
            } else if ($s1 == $s2 && $s4 == $s5) {
                return true;
            }
        }
        //////////////////////////////////
        else if ($len == 6) {
            $s1 = $a[0];
            $s2 = $a[1];
            $s3 = $a[2];
            $s4 = $a[3];
            $s5 = $a[4];
            $s6 = $a[5];
            if ($s1 == $s2 && $s2 == $s3 && $s3 == $s4 && $s4 == $s5 && $s5 == $s6 && $s6 == $s1) {
                return true;
            } else if ($s2 == $s3 && $s3 == $s4 && $s4 == $s5 && $s5 == $s6) {
                return true;
            } else if ($s1 == $s2 && $s2 == $s3 && $s4 == $s5 && $s5 == $s6) {
                return true;
            } else if ($s1 == $s2 && $s3 == $s4 && $s5 == $s6) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s2) + 1 == intval($s3) && intval($s3) + 1 == $s4 && intval($s4) + 1 == intval($s5) && intval($s5) + 1 == intval($s6)) {
                return true;
            } else if (intval($s1) - 1 == intval($s2) && intval($s2) - 1 == intval($s3) && intval($s3) - 1 == $s4 && intval($s4) - 1 == intval($s5) && intval($s5) - 1 == intval($s6)) {
                return true;
            } else if ($s1 == $s2 && $s4 == $s5 && $s2 == $s4 && $s3 == $s6 && ($s1 . $s2 . $s3) == ($s4 . $s5 . $s6)) {
                return true;
            } else if ($s2 == $s3 && $s5 == $s6 && $s2 == $s5 && $s1 == $s4 && ($s1 . $s2 . $s3) == ($s4 . $s5 . $s6)) {
                return true;
            } else if ($s1 == $s2 && $s2 == $s3 && intval($s4) + 1 == intval($s5) && intval($s5) + 1 == intval($s6)) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && intval($s2) + 1 == intval($s3) && $s4 == $s5 && $s5 == $s6) {
                return true;
            } else if (intval($s1) + 1 == intval($s2) && $s3 == $s4 && intval($s5) - 1 == intval($s6) && $s2 == $s5) {
                return true;
            }
        }

        return false;
    }

    //重新获取图片
    public static function getImage($access_token, $serverid, $user_id, $backurl) {
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=" . $access_token . "&media_id=" . $serverid;
        $path = "upload/" . date("Y") . "/" . date('m') . "/" . date('d');
        $time = time();
        $filename = $path . '/' . $time . "_" . $user_id . ".jpg";
        $new_name = $time . "_" . $user_id . ".jpg";
        $fileInfo = self::downloadWeixinFile($url);
        self::saveWeixinFile($filename, $fileInfo["body"]);
        //////// 把图片同步到服务器统一地址////////////////////
        $urlPost = $backurl . '?r=upload';
        $file_content = base64_encode(file_get_contents($filename));
        $data = 'file_name=' . $new_name . '&file_content=' . rawurlencode($file_content) . '&file_path=' . $path;
        $ret = self::interface_post($urlPost, $data);
        \Logger::errorLog(print_r($ret, true), 'uploadimage');

        return true;
    }

    public static function downloadWeixinFile($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $package = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        $imageAll = array_merge(array('header' => $httpinfo), array('body' => $package));
        return $imageAll;
    }

    public static function saveWeixinFile($filename, $filecontent) {
        $local_file = fopen($filename, 'w');
        if (false !== $local_file) {
            if (false !== fwrite($local_file, $filecontent)) {
                fclose($local_file);
            }
        }
    }

    /**
     * 接口APP传递过来的参数
     * @return array
     */
    public static function getParamArr() {
        $param = array();
        //通过post方式接收参数
        $poststr = file_get_contents("php://input", 'r');
        $poststr = trim($poststr, '&');
        if (!empty($poststr)) {
            //拆分参数
            $paramarr = explode('&', $poststr);
            if (!empty($paramarr)) {
                foreach ($paramarr as $val) {
                    $strtemp = explode('=', $val);
                    $param[$strtemp[0]] = $strtemp[1];
                }
            }
        }
        $array_gets = $_GET;
        $array_notify = array_merge($array_gets, $param);
        return $array_notify;
    }

    /**
     * 手机号码归属地
     * @param type $mobile
     * @param type $output  xml, json, text 查询结果输出格式
     */
    public static function mobileHome($mobile, $output) {
        $url = "http://tcc.taobao.com/cc/json/mobile_tel_segment.htm";
        $curlPost = 'tel=' . $mobile;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = iconv('GB2312', "UTF-8", $data);
        preg_match_all("/(\w+):'([^']+)/", $data, $m);
        $arr = array_combine($m[1], $m[2]);
        return $arr;
    }

    /**
     * 手机流量充值
     */
    public static function mobileRecharge($mobile, $package) {
        if (empty($mobile) || empty($package)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.weike.viketraffic';
        $version = '1.0';
        $url = Yii::$app->params['xianhua_url'];
        $key = Yii::$app->params['xianhua_key'];

        $sign = md5($ak . $mobile . $package . $service_type . $version . $key);
        $data = 'ak=' . $ak . '&mobile=' . $mobile . '&package=' . $package . '&service_type=' . $service_type . '&version=' . $version . '&sign=' . $sign;

        $result = json_decode(self::interface_post($url, $data));
        \Logger::errorLog(print_r($result, true), 'mobileRecharge');
        return $result;
    }

}

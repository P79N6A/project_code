<?php

namespace app\commonapi;

use app\common\ApiClientCrypt;
use app\commonapi\Crypt3Des;
use app\commonapi\Logger;
use app\models\dev\Accesstoken;
use ReflectionClass;
use Yii;

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
        Logger::errorLog(print_r($result, true), 'getUserInfo');
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
        Logger::errorLog(print_r($result, true), 'payLianLian');
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
            Logger::errorLog(print_r($result, true), 'subPayLian');
        } else {
            Logger::errorLog(print_r('返回为空', true), 'subPayLian');
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
        Logger::errorLog(print_r($result, true), 'getStuVerify');
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

    //创蓝短信发送接口
    public static function sendByChuanglanMobile($mobile, $content) {
        $account = 'XXDD168';
        $pswd = '7zxqk6eoR';
        $msg = '【先花一亿元】' . $content;
        $needstatus = 'true';

        //$url = 'http://222.73.117.169/msg/HttpBatchSendSM';
        $url = 'http://sapi.253.com/msg/HttpBatchSendSM';
        $data = 'account=' . $account . '&pswd=' . $pswd . '&msg=' . $msg . '&mobile=' . $mobile . '&needstatus=' . $needstatus;
        //echo $url."?".$data;exit;
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

    /**
     * 接口请求方式
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    public static function interface_post_json_rong($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
        );

        $result = curl_exec($ch);
        return $result;
    }

    /**
     * 接口请求方式
     * @param unknown $url
     * @param json $data
     * @return mixed
     */
    public static function interface_post_json($url, $data) {
//        print_r($data);
        $headers = array(
            "Content-type:application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        );
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($ch, CURLOPT_POST, TRUE);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        $ret = curl_exec($ch);
//
//        curl_close($ch);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置超时
//        $url = '这里为请求地址';
//        if(0 === strpos(strtolower($url), 'https')) {
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //对认证证书来源的检查
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //从证书中检查SSL加密算法是否存在
//        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
//        $data = array(0=>1,1=>2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $ret = curl_exec($ch); //CURLOPT_RETURNTRANSFER 不设置  curl_exec返回TRUE 设置  curl_exec返回json(此处) 失败都返回FALSE
        curl_close($ch);
        return $ret;
    }

    /**
     * 接口请求方式
     * @param unknown $url
     * @param json $data
     * @return mixed
     */
    public static function post_json($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: " . strlen($data))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($return_code, $return_content);
    }

    /**
     * json方式请求（智齿）
     * @param $type
     * @param $url
     * @param $data
     * @return mixed
     * @author 王新龙
     * @date 2018/9/29 21:05
     */
    public static function curl_json($type, $url, $data) {
        $headers = array(
            "Content-type:application/json;charset='utf-8'",
            "Cache-Control: no-cache",
        );
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $type = strtolower($type);
        switch ($type){
            case 'get':
                break;
            case 'post':
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
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

    public function postCurl($postData, $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    public static function dataPost($post_string, $url) {//POST方式提交数据
//        return $post_string;
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

    //x-www-form-urlencoded请求
    public static function interface_post_form_urlencoded($url, $data) {
        $data = http_build_query($data);
        $curlobj = curl_init();
        curl_setopt($curlobj, CURLOPT_URL, $url);
        curl_setopt($curlobj, CURLOPT_HEADER, 0);
        curl_setopt($curlobj, CURLOPT_POST, 1);
        curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curlobj, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlobj, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curlobj, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curlobj, CURLOPT_HTTPHEADER, array("application/x-www-form-urlencoded;charset=utf-8", "Content-length:" . strlen($data)));
        $rtn = curl_exec($curlobj);
        curl_close($curlobj);
        return $rtn;
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
        Logger::errorLog(print_r($result, true), 'stuVerfiy');
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
        Logger::errorLog(print_r($result, true), 'quantitative');
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
        Logger::errorLog(print_r($result, true), 'identity_check');
        if (isset($result->rsp_code) && (trim($result->rsp_code) == '0000') && (trim($result->result) == 'AUTHOK')) {
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
        Logger::errorLog(print_r($result, true), 'balance');
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
        Logger::errorLog(print_r($result, true), 'riskDecision_register_student');
        return $result;
    }

    public static function riskDecision_register_company($account_name, $account_mobile, $id_number, $organization, $ext_position, $seq_id, $ext_birth_year, $token_id = '') {

        if (empty($account_name) || empty($account_mobile) || empty($id_number) || empty($organization) || empty($seq_id)) {
            return false;
        }
        $seq_id = date('YmdHis') . $seq_id;
        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.risk';
        $key = Yii::$app->params['xianhua_key'];
        $event_type = 'REG';
        $version = '1.0';
        $url = Yii::$app->params['xianhua_url'];
        if (empty($token_id)) {
            $sign = md5($account_mobile . $account_name . $ak . $event_type . $ext_birth_year . $ext_position . $id_number . $organization . $seq_id . $service_type . $version . $key);
            $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&organization=' . rawurlencode($organization) . '&ext_position=' . rawurlencode($ext_position) . '&seq_id=' . $seq_id . '&sign=' . $sign;
        } else {
            $sign = md5($account_mobile . $account_name . $ak . $event_type . $ext_birth_year . $ext_position . $id_number . $organization . $seq_id . $service_type . $token_id . $version . $key);
            $data = 'ak=' . $ak . '&token_id=' . $token_id . '&service_type=' . $service_type . '&version=' . $version . '&event_type=' . $event_type . '&ext_birth_year=' . $ext_birth_year . '&account_name=' . rawurlencode($account_name) . '&account_mobile=' . $account_mobile . '&id_number=' . $id_number . '&organization=' . rawurlencode($organization) . '&ext_position=' . rawurlencode($ext_position) . '&seq_id=' . $seq_id . '&sign=' . $sign;
        }
        $result = json_decode(self::interface_post($url, $data));
        Logger::errorLog(print_r($result, true), 'riskDecision_register_company');
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
        Logger::errorLog($seq_id . '--' . print_r($data, true) . "\n", 'riskDecision_loan_no');
        $result = json_decode(self::interface_post($url, $data));
        Logger::errorLog($seq_id . '--' . print_r($result, true), 'riskDecision_loan');
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
        Logger::errorLog(print_r($ret, true), 'uploadimage');

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
        Logger::errorLog(print_r($result, true), 'mobileRecharge');
        return $result;
    }

    /**
     * 身份证号规则验证
     */
    public static function checkIdenCard($idcard) {
        if (empty($idcard)) {
            return false;
        }
        $City = array(
            11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古",
            21 => "辽宁", 22 => "吉林", 23 => "黑龙江",
            31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东",
            41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南",
            50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏",
            61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆",
            71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外"
        );
        $iSum = 0;
        $idCardLength = strlen($idcard);

        //长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i', $idcard) && !preg_match('/^\d{15}$/i', $idcard)) {
            return false;
        }

        //地区验证
        if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15) {
            $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9); //15to18
            $Bit18 = self::getVerifyBit($idcard); //算出第18位校验码
            $idcard = $idcard . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }

        //身份证编码规范验证
        $idcard_base = substr($idcard, 0, 17);
        if (strtoupper(substr($idcard, 17, 1)) != self::getVerifyBit($idcard_base)) {
            return false;
        }
        return $idcard;
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    public static function getVerifyBit($idcard_base) {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

    public static function balanceresult($settle_request_id) {
        if (empty($settle_request_id)) {
            return false;
        }

        $ak = Yii::$app->params['ak'];
        $service_type = 'open.api.service.remit.result';
        $key = Yii::$app->params['xianhua_key'];
        $version = '1.0';

        $sign = md5($ak . $service_type . $settle_request_id . $version . $key);
        $url = Yii::$app->params['xianhua_url'];
        $data = 'ak=' . $ak . '&service_type=' . $service_type . '&version=' . $version . '&settle_request_id=' . $settle_request_id . '&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        Logger::errorLog(print_r($result, true), 'balanceresult');
        return $result;
    }

    public static function push_template($nickname) {
        if (empty($nickname)) {
            $nickname = "TA";
        }
        $template = array(
            array('title' => "不用你花一分钱就能帮{$nickname}拿到钱！", 'desc' => '你的大恩大德TA会记住哒！'),
            array('title' => "谁说借钱没面子，朋友多面子大，{$nickname}请你来一面！", 'desc' => ''),
            array('title' => "帮TA就是帮自己，{$nickname}请你帮他点一下！", 'desc' => 'TA拿钱，你赚钱，好基友么么哒！'),
        );
        $length = count($template);
        if ($length > 0) {
            $num = rand(0, $length - 1);
            return $template[$num];
        } else {
            return null;
        }
    }

    /**
     * 微信消息推送
     * */
    public static function sendTemplateMsg($data) {
        $wx_type = $data['wx_type'];
        $type = $data['type'];
        unset($data['wx_type']);
        unset($data['type']);
        $wxinfo = include Yii::$app->basePath . '/config/wx.php';
        //补上微信账号信息
        $data['appid'] = $wxinfo['appId'];
        $data['appurl'] = Yii::$app->params['app_url'];
        //根据发送消息类型获取消息模板
        $templateInfo = include Yii::$app->basePath . "/config/weixintemplate/" . $wx_type . ".php";
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $templateInfo = str_replace('{{{' . $key . '}}}', $value, $templateInfo);
            }
        }
        //判断发送客服消息还是模板消息
        if ($type == 'CUSTOM') {
            $postUrl = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . self::getAccessToken();
        } else {
            $postUrl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . self::getAccessToken();
        }

        $ret = self::dataPost($templateInfo, $postUrl);
        //记录日志

        return true;
    }

    //获取access_token值
    public static function getAccessToken() {
        $appInfo = include Yii::$app->basePath . '/config/params_' . SYSTEM_ENV . '.php';

        //先查询对应的数据表是否有token值
        $access_token = Accesstoken::find()->where(['type' => 1])->one();
        if (isset($access_token->access_token)) {
            //判断当前时间和数据库中时间
            $time = time();
            $gettokentime = $access_token->time;
            if (($time - $gettokentime) > 7000) {
                //重新获取token值然后替换以前的token值
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appInfo['AppID'] . "&secret=" . $appInfo['AppSecret'];
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
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appInfo['AppID'] . "&secret=" . $appInfo['appSecret'];
            $data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
            $resultArr = json_decode($data, true); //转为数组
            $accessToken = $resultArr["access_token"]; //获取access_token

            $time = time();
            $sql = "insert into " . Accesstoken::tableName() . "(access_token,time) value('$accessToken','$time')";
            $result = Yii::$app->db->createCommand($sql)->execute();

            return $accessToken;
        }
    }

    /**
     * 对应关系认证
     * @param type $type    认证方式1：卡号、姓名2： 卡号、手机号3：姓名、 身份证号5：卡号、姓名、身份证号、手机号
     * @param array $params = array('cardNumber','name','mobile','cid')
     */
    public static function bankidentityYoufen($type, $params) {
        $params['ak'] = Yii::$app->params['ak'];
        $params['service_type'] = 'open.api.service.bankcardauth.yf.relation';
        $params['version'] = '1.0';
        $params['type'] = $type;
        $md5_key = Yii::$app->params['xianhua_key'];
        switch ($type) {
            case 1:
                if (!isset($params['cardNumber']) || !isset($params['name'])) {
                    return false;
                }
                $result = self::youFen($params, $md5_key);
                break;
            case 2:
                if (!isset($params['cardNumber']) || !isset($params['mobile'])) {
                    return false;
                }
                $result = self::youFen($params, $md5_key);
                break;
            case 3:
                if (!isset($params['cid']) || !isset($params['name'])) {
                    return false;
                }
                $result = self::youFen($params, $md5_key);
                break;
            default :
                if (!isset($params['cardNumber']) || !isset($params['name']) || !isset($params['mobile']) || !isset($params['cid'])) {
                    return false;
                }
                $result = self::youFen($params, $md5_key);
        }
        Logger::errorLog(print_r($result, true), 'bankidentityYoufen');
        //验签返回的sign
        $re_sign = self::createMd5($result, $md5_key);
        if (isset($result->sign) && $result->sign == $re_sign) {
            return $result;
        } else {
            return false;
        }
    }

    private static function youFen($parms, $md5_key) {
        foreach ($parms as $key => $val) {
            if (empty($val)) {
                unset($parms[$key]);
            }
        }
        $data = '';
        foreach ($parms as $key => $vals) {
            $data .='&' . $key . '=' . urlencode($vals);
        }
        $data = substr($data, 1, strlen($data));
        $sign = self::createMd5($parms, $md5_key);
        $url = Yii::$app->params['xianhua_url'];
        $data .='&sign=' . $sign;
        $result = json_decode(self::interface_post($url, $data));
        return $result;
    }

    public static function juLixin($postDatas, $juxinli = '') {
        $process_recode = array('10003', '10008', '10009', '10010', '11000');
        $process_code = array('10002', '10004', '10006', '10007');
        $openApi = new ApiClientCrypt;
        if (empty($juxinli) || ($juxinli->type == 1 && $juxinli->last_modify_time <= date('Y-m-d H:i:s', strtotime('-4 month'))) || in_array($juxinli->process_code, $process_recode)) {
            $url = 'juxinli/postrequest';
        } elseif (in_array($juxinli->process_code, $process_code)) {
            $url = 'juxinli/postretry';
        } else {
            $url = 'juxinli/postrequest';
        }
        $res = $openApi->sent($url, $postDatas);
        Logger::errorLog(print_r($res, true), 'julixin_s');
        $result = $openApi->parseResponse($res);
        Logger::errorLog(print_r($result, true), 'julixin');
        return $result;
    }

    /**
     * 选填资料认证
     * @param type $user_id
     * @param type $source  1：学信网,2：社保,3：公积金
     * @param type $cb_url  回显地址  授权成功以后跳转的地址
     * @return boolean
     */
    public static function selection_choice($user_id, $source, $cb_url) {
        if (empty($user_id) || empty($source) || empty($cb_url)) {
            return FALSE;
        }
        $api = new ApiClientCrypt();
        $data = [
            'user_id' => $user_id,
            'source' => $source,
            'cb_url' => $cb_url,
        ];
        $url = 'sjmh/choice';
        $result = $api->sent($url, $data, $type = 7);
        Logger::dayLog('selection', 'choice', $result);
        $res = json_decode($result, TRUE);
        if (isset($res['res_url'])) {
            $res['res_url'] = stripslashes($res['res_url']);
        }
        return $res;
    }

    /**
     * 选填资料认证
     * @param type $user_id
     * @param type $source  1：学信网,2：社保,3：公积金
     * @param type $cb_url  回显地址  授权成功以后跳转的地址
     * @return boolean
     */
    public static function selection_save($user_id, $source, $cb_url, $task_id, $resuest_id) {
        if (empty($user_id) || empty($source) || empty($cb_url) || empty($task_id) || empty($resuest_id)) {
            return FALSE;
        }
        $api = new ApiClientCrypt();
        $data = [
            'user_id' => $user_id,
            'source' => $source,
            'callback_url' => $cb_url,
            'task_id' => $task_id,
            'request_id' => $resuest_id,
        ];
        $url = 'sjmh/save';
        $result = $api->sent($url, $data, $type = 7);
        Logger::dayLog('selection', 'save', $result);
        $res = json_decode($result, TRUE);
        if (isset($res['res_data']['callback_url'])) {
            $res['res_data']['callback_url'] = stripslashes($res['res_data']['callback_url']);
        }
        return $res;
    }

    /**
     * 长链变短链
     * @param type $user_id
     * @param type $source  1：学信网,2：社保,3：公积金
     * @param type $cb_url  回显地址  授权成功以后跳转的地址
     * @return boolean
     */
    public static function getSinaShortUrl($url_long) {
        $source = '2904585492';
        $api = 'http://api.t.sina.com.cn/short_url/shorten.json';
        if (!$url_long) {
            return FALSE;
        }
        $request_url = sprintf($api . '?source=%s&url_long=%s', $source, urlencode($url_long));
        $data = file_get_contents($request_url);
        $shortUrl = json_decode($data, TRUE);
        Logger::dayLog('shorturl', $url_long, $shortUrl);
        return $shortUrl[0]['url_short'];
    }


    /**
     * 银行流水
     * @param $user_id
     * @param $show_url 授权成功以后跳转的地址
     * @param $cb_url 异步地址
     * @return bool|mixed
     */
    public static function bank_flow($user_id, $show_url, $cb_url) {
        if (empty($user_id) || empty($show_url) || empty($cb_url)) {
            return FALSE;
        }
        $api = new ApiClientCrypt();
        $data = [
            'user_id' => $user_id,
            'show_url' => $show_url,
            'callback_url' => $cb_url,
        ];
        $url = 'slbank/index';
        $result = $api->sent($url, $data, $type = 7);
        Logger::dayLog('bankflow', 'choice', $result);
        $res = $api->parseResponse($result);
        return $res;
    }

    /**
     * 银行流水保存请求
     * @param $requestid
     * @param $org_biz_no
     * @param $biz_no
     * @return array|bool|mixed
     */
    public static function bank_flow_save($requestid, $org_biz_no, $biz_no) {
        if (empty($requestid) || empty($org_biz_no) || empty($biz_no)) {
            return FALSE;
        }
        $api = new ApiClientCrypt();
        $data = [
            'request_id' => $requestid,
            'org_biz_no' => $org_biz_no,
            'biz_no' => $biz_no,
        ];
        $url = 'slbank/savebizno';
        $result = $api->sent($url, $data, $type = 7);
        Logger::dayLog('bankflow', 'save', $result);
        $res = $api->parseResponse($result);
        return $res;
    }
}

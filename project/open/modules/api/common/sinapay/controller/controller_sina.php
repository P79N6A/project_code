<?php
/**
 * Created by PhpStorm.
 * User: linwei
 * Date: 16/4/11
 * Time: 16:46
 * demo只是提供一个接口对接编写的思路，具体接口对接商户技术以自身项目的实际情况来进行接口代码的编写。
 */


$sina_config = YII_ENV=='prod' ? 'config.prod.php' :  'config.dev.php';
//$sina_config = 'config.prod.php';
include (dirname ( __File__ ) . "/../config/{$sina_config}");
include (dirname ( __File__ ) . "/../api/weibopay.class.php");

class controller_sina
{

    /**
     * 创建激活会员
     * @param array $data
     * @return array 返回数组
     */
    function create_activate_member($data = array())
    {
        /**************获取创建激活会员参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型

        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//用户标识信息
        $identity_type = $data['identity_type'];//用户标识类型
        $member_type = $data['member_type'];//会员类型 1 个人 2企业 默认 个人
        $param = array();
        $param['service'] = $service;//服务名称
        $param['version'] = $version;//接口版本
        $param['request_time'] = $request_time;//请求时间
        $param['partner_id'] = $partner_id;//合作商户号
        $param['_input_charset'] = $_input_charset;//字符集编码
        $param['sign_type'] = $sign_type;//签名类型
        $param['identity_id'] = $identity_id;//用户标识
        $param['identity_type'] = $identity_type;
        $param['member_type'] = $member_type;
        $param['client_ip'] = $data['client_ip'];// new ip
        ksort($param);//对签名参数据排序
        $weibopay = new Weibopay();
        //对请求sina报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入报文数组
        $param['sign'] = $sign;
        $weibopay->write_log("创建激活会员请求参数" . json_encode($param));
        //调用createcurl_data创建模拟表单需要的数据
        $data = $weibopay->createcurl_data($param);
        // 使用模拟表单提交进行数据提交
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset);
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); //对返回数据进行排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 设置实名信息
     * set_real_name
     * @param array $data
     */
    function set_real_name($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取设置实名信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//用户标识信息
        $identity_type = $data['identity_type'];//用户标识类型
        $real_name = $data['real_name'];//真实姓名 需要RSA加密
        $cert_type = $data['cert_type'];//证件类型
        $cert_no = $data['cert_no'];//证件号码 需要RSA加密
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['real_name'] = $weibopay->Rsa_encrypt($real_name, sinapay_rsa_public__key,$_input_charset);
        $param['cert_no'] = $weibopay->Rsa_encrypt($cert_no, sinapay_rsa_public__key,$_input_charset);
        $param['cert_type'] = $cert_type;
        $param['client_ip'] = $data['client_ip'];// new ip
        ksort($param);//对签名参数据排序
        //对请求sina报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将计算出来的签名放入请求sina的报文数组中
        $param['sign'] = $sign;
        $weibopay->write_log("设置实名信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); //对返回数据进行排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 绑定认证信息
     * binding_verify
     * @param array $data
     */
    function binding_verify($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取绑定认证信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//用户标识信息
        $identity_type = $data['identity_type'];//用户标识类型
        $verify_type = $data['verify_type'];//认证类型
        $verify_entity = $data['verify_entity'];//认证内容 需要RSA加密
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['verify_type'] = $verify_type;
        $param['verify_entity'] = $weibopay->Rsa_encrypt($verify_entity, sinapay_rsa_public__key,$_input_charset);
        $param['client_ip'] = $data['client_ip'];// new ip
        ksort($param);//对签名参数据排序
        //对请求sina报文计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入请求sina数组中
        $param['sign'] = $sign;
        $weibopay->write_log("绑定认证信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); //对返回的数据进行排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 解绑认证信息
     * unbinding_verify
     * @param array $data
     */
    function unbinding_verify($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取解绑认证信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//用户标识信息
        $identity_type = $data['identity_type'];//用户标识类型
        $verify_type = $data['verify_type'];//认证类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['verify_type'] = $verify_type;
        ksort($param);//对签名参数据排序
        //对请求sina报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果存入请求sina的数组
        $param['sign'] = $sign;
        $weibopay->write_log("解绑认证信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询认证信息
     * query_verify
     * @param array $data
     */
    function query_verify($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询认证信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//用户标识信息
        $identity_type = $data['identity_type'];//用户标识类型
        $verify_type = $data['verify_type'];//认证类型
        $is_mask = $data['is_mask'];//是否掩码
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['verify_type'] = $verify_type;
        $param['is_mask'] = $is_mask;
        ksort($param);//对签名参数据排序
        //对查询认证信息报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将查询认证结果报文放入请求sina数组
        $param['sign'] = $sign;
        $weibopay->write_log("查询认证信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 绑定银行卡
     * binding_bank_card
     * @param array $data
     */
    function binding_bank_card($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取解绑认证信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $request_no = $data['request_no'];//绑卡请求号
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $bank_code = $data['bank_code'];//银行编码
        $bank_account_no = $weibopay->Rsa_encrypt($data['bank_account_no'], sinapay_rsa_public__key,$_input_charset);//银行卡号, 需要加密
        $card_type = $data['card_type'];//卡类型
        $card_attribute = $data['card_attribute'];//卡属性
        $phone_no = @$weibopay->Rsa_encrypt($data['phone_no'], sinapay_rsa_public__key,$_input_charset);//银行预留手机号
        $province = $data['province'];//省份
        $city = $data['city'];//城市
        $verify_mode = @$data['verify_mode'];//认证方式
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['request_no'] = $request_no;
        $param['bank_code'] = $bank_code;
        $param['bank_account_no'] = $bank_account_no;
        $param['card_type'] = $card_type;
        $param['card_attribute'] = $card_attribute;
        $param['client_ip'] = $data['client_ip'];
        if ($phone_no != "") {
            $param['phone_no'] = $phone_no;
        }
        $param['province'] = $province;
        if ($verify_mode != "") {
            $param['verify_mode'] = $verify_mode;
        }
        $param['city'] = $city;
        ksort($param);//对签名参数据排序
        //对绑定·银行卡请求报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入请求sina的报文数组中
        $param['sign'] = $sign;
        $weibopay->write_log("绑定银行卡信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 绑定银行卡推进接口
     * binding_bank_card_advance
     * @param array $data
     */
    function binding_bank_card_advance($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取绑定银行卡推进信息参数****************/
        $service = $data['service'];//服务名称
        $version = sinapay_version;//接口版本
        $request_time = date("YmdHis");//请求时间
        $partner_id = sinapay_partner_id;//合作者身份ID
        $_input_charset = sinapay_input_charset;//参数编码字符集
        $sign_type = sinapay_sign_type;//签名类型
        /****************业务参数***********************/
        $ticket = $data['ticket'];//ticket有效期为15分钟，可以多次使用（最多5次）
        $valid_code = $data['valid_code'];//用户绑卡手机收到的验证码
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['ticket'] = $ticket;
        $param['valid_code'] = $valid_code;
        ksort($param);//对签名参数据排序
        //对绑定银行卡推进请求报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入报文数组中
        $param['sign'] = $sign;
        $weibopay->write_log("绑定银行卡推进信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 解绑银行卡
     * unbinding_bank_card
     * @param array $data
     */
    function unbinding_bank_card($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取解绑银行卡信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $card_id = $data['card_id'];//银行卡ID
        $advance_flag = $data['advance_flag'];//是否推进,默认为Y
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['card_id'] = $card_id;
        $param['advance_flag'] = $advance_flag;
        ksort($param);//对签名参数据排序
        //对解绑银行卡报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果存入请求sina报文数组中
        $param['sign'] = $sign;
        $weibopay->write_log("解绑银行卡信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询我的银行卡
     * query_bank_card
     * @param array $data
     */
    function query_bank_card($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询我的银行卡信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        ksort($param);//对签名参数据排序
        //对查询银行卡报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果存入请求数组中
        $param['sign'] = $sign;
        $weibopay->write_log("查询银行卡信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询余额/查询基金份额
     * query_balance
     * @param array $data
     */
    function query_balance($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询余额/基金份额信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $account_type = $data['account_type'];//查询账户类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['account_type'] = $account_type;
        ksort($param);//对签名参数据排序
        //对查询余额基金份额报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入报文数组中
        $param['sign'] = $sign;
        $weibopay->write_log("查询余额/基金份额请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询收支明细
     * query_account_details
     * @param array $data
     */
    function query_account_details($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询收支明细信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $account_type = $data['account_type'];//查询账户类型
        $start_time = $data['start_time'];//开始时间
        $end_time = $data['end_time'];//结束时间
        $page_no = $data['page_no'];//页号
        $page_size = $data['page_size'];//每页大小
        @$extend_param = $data['extend_param'];//扩展参数

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['account_type'] = $account_type;
        $param['start_time'] = $start_time;
        $param['end_time'] = $end_time;
        $param['page_no'] = $page_no;
        $param['page_size'] = $page_size;
        if (isset($extend_param)) {
            $param['extend_param'] = $extend_param;
        }
        ksort($param);//对签名参数据排序
        //对查询收支明细报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名结果放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("查询收支明细请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 冻结余额
     * balance_freeze
     * @param array $data
     */
    function balance_freeze($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取冻结余额信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_freeze_no = $data['out_freeze_no'];//冻结订单号
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $account_type = $data['account_type'];//查询账户类型
        $amount = $data['amount'];//冻结金额
        $summary = $data['summary'];//摘要

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['account_type'] = $account_type;
        $param['amount'] = $amount;
        $param['summary'] = $summary;
        $param['out_freeze_no'] = $out_freeze_no;
        ksort($param);//对签名参数据排序
        //对冻结余额报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入请求数组
        $param['sign'] = $sign;
        $weibopay->write_log("冻结余额信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 解冻余额
     * balance_unfreeze
     * @param array $data
     */
    function balance_unfreeze($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取解冻结余额信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_unfreeze_no = $data['out_unfreeze_no'];//解冻订单号
        $out_freeze_no = $data['out_freeze_no'];//冻结订单号
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $amount = $data['amount'];//解冻结金额
        $summary = $data['summary'];//摘要

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['amount'] = $amount;
        $param['summary'] = $summary;
        $param['out_freeze_no'] = $out_freeze_no;
        $param['out_unfreeze_no'] = $out_unfreeze_no;
        ksort($param);//对签名参数据排序
        //对解冻余额报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入请求sina报文中
        $param['sign'] = $sign;
        $weibopay->write_log("解冻余额信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 请求企业会员资质审核
     * audit_member_infos
     * @param array $data
     */
    function audit_member_infos($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取解冻结余额信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $notify_url = $data['notify_url'];//异步回调地址
        /****************业务参数***********************/
        $audit_order_no = $data['audit_order_no'];//
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $company_name = $data['company_name'];//公司名称
        $address = $data['address'];//企业地址
        //执照号
        $license_no = $weibopay->Rsa_encrypt($data["license_no"], sinapay_rsa_public__key,$_input_charset);
        $license_address = $data['license_address'];//营业执照所在地
        $license_expire_date = $data['license_expire_date'];//执照过期日（营业期限）
        $business_scope = $data['business_scope'];//营业范围
        //联系电话
        $telephone = $weibopay->Rsa_encrypt($data["telephone"], sinapay_rsa_public__key,$_input_charset);
        //联系Email
        $email = $weibopay->Rsa_encrypt($data["email"], sinapay_rsa_public__key,$_input_charset);
        //组织机构代码
        $organization_no = $weibopay->Rsa_encrypt($data["organization_no"], sinapay_rsa_public__key,$_input_charset);
        $summary = $data['summary'];//企业简介

        //企业法人
        $legal_person = $weibopay->Rsa_encrypt($data["legal_person"], sinapay_rsa_public__key,$_input_charset);
        //法人证件号码
        $cert_no = $weibopay->Rsa_encrypt($data["cert_no"], sinapay_rsa_public__key,$_input_charset);
        $cert_type = $data['cert_type'];//证件类型

        //法人手机号码
        $legal_person_phone = $weibopay->Rsa_encrypt($data["legal_person_phone"], sinapay_rsa_public__key,$_input_charset);
        $bank_code = $data['bank_code'];//银行编号
        //银行卡号
        $bank_account_no = $weibopay->Rsa_encrypt($data["bank_account_no"], sinapay_rsa_public__key,$_input_charset);
        $card_type = $data['card_type'];//卡类型
        $card_attribute = $data['card_attribute'];//卡属性
        $province = $data['province'];//开户行省份
        $city = $data['city'];//开户行城市
        $bank_branch = $data['bank_branch'];//支行名称
        $fileName = $data["fileName"];//文件名称
        //demo默认的资质文件上传路径，资质文件先传到demo目录，demo再传到sina sftp
        $filedata = sinapay_file_path . $fileName;
        $digest = $weibopay->md5_file($filedata);//文件摘要
        $digestType = $data['digestType'];//文件摘要算法
        $weibopay->write_log("文件摘要:" . $digest);
        //sftp上传

        if ($data['update_status'] == "Y") {
            $weibopay->write_log("开始连接sftp进行文件上传:" . $digest);
            $is_upload = $weibopay->sftp_upload($filedata, $fileName);
            if ($is_upload) {
                $weibopay->write_log("上传资质文件成功！" . $filedata);
            } else {
                $weibopay->write_log("上传资质文件失败！" . $filedata);
            }
        }

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['notify_url'] = $notify_url;
        $param['audit_order_no'] = $audit_order_no;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['company_name'] = $company_name;
        $param['address'] = $address;
        $param['license_no'] = $license_no;
        $param['license_address'] = $license_address;
        $param['license_expire_date'] = $license_expire_date;
        $param['business_scope'] = $business_scope;
        $param['telephone'] = $telephone;
        $param['email'] = $email;
        $param['organization_no'] = $organization_no;
        $param['summary'] = $summary;
        $param['legal_person'] = $legal_person;
        $param['cert_no'] = $cert_no;
        $param['cert_type'] = $cert_type;
        $param['legal_person_phone'] = $legal_person_phone;
        $param['bank_code'] = $bank_code;
        $param['bank_account_no'] = $bank_account_no;
        $param['card_type'] = $card_type;
        $param['card_attribute'] = $card_attribute;
        $param['province'] = $province;
        $param['city'] = $city;
        $param['bank_branch'] = $bank_branch;
        $param['fileName'] = $fileName;
        $param['digest'] = $digest;
        $param['digestType'] = $digestType;
        ksort($param);//对签名参数据排序
        //计算请求报文签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名放到报文
        $param['sign'] = $sign;
        $weibopay->write_log("请求企业会员资质审核信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询企业会员审核结果
     * query_audit_result
     * @param array $data
     */
    function query_audit_result($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询企业会员审核参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("查询企业会员审核结果请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询企业会员资质信息
     * @param array $data
     */
    function query_member_infos($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询企业会员资质信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("查询企业会员审核结果请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }

    }

    /**
     * sina页面展示用户信息
     * show_member_infos_sina
     * @param array $data
     */
    function show_member_infos_sina($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取sina展示用户页面参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $return_url = $data['return_url'];//
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $resp_method = $data['resp_method'];//

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['resp_method'] = $resp_method;
        $param['return_url'] = $return_url;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("sina页面展示用户信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询冻结解冻交易结果
     * query_ctrl_result
     * @param array $data
     */
    function query_ctrl_result($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询冻结解冻结果参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_ctrl_no = $data['out_ctrl_no'];//冻结解冻单号

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['out_ctrl_no'] = $out_ctrl_no;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("查询冻结解冻交易请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 经办人信息
     * smt_fund_agent_buy
     * @param array $data
     */
    function smt_fund_agent_buy($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取经办人信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $agent_name = $weibopay->Rsa_encrypt($data['agent_name'], sinapay_rsa_public__key,$_input_charset);//经办人姓名 RSA加密
        $license_no = $weibopay->Rsa_encrypt($data['license_no'], sinapay_rsa_public__key,$_input_charset);//经办人身份证 rsa加密
        $license_type_code = $data['license_type_code'];//证件类型
        $agent_mobile = $weibopay->Rsa_encrypt($data['agent_mobile'], sinapay_rsa_public__key,$_input_charset);//经办人手机 RSA加密


        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['agent_name'] = $agent_name;
        $param['license_no'] = $license_no;
        $param['license_type_code'] = $license_type_code;
        $param['agent_mobile'] = $agent_mobile;

        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("经办人信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询中间账户
     * query_middle_account
     * @param array $data
     */
    function query_middle_account($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询中间账户余额结果参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_trade_code = $data['out_trade_code'];//外部业务码

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['out_trade_code'] = $out_trade_code;

        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("查询中间账户余额请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 解绑银行卡推进接口
     * unbinding_bank_card_advance
     * @param array $data
     */
    function unbinding_bank_card_advance($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取解绑银行卡推进结果参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $ticket = $data['ticket'];//绑卡时返回的ticket  ticket有效期为15分钟，可以多次使用（最多5次）
        $valid_code = $data['valid_code'];//短信验证码

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['ticket'] = $ticket;
        $param['valid_code'] = $valid_code;

        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("解绑银行卡推进请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mgs_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * all支付密码重定向
     * @return url
     */
    function all_pay_password($data = array())
    {
        $weibopay = new Weibopay ();
        /**************获取设置支付密码重定向基本参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $return_url = $data['return_url'];//同步返回地址
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['return_url'] = $return_url;

        $param['_input_charset'] = $_input_charset;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        ksort($param);//对签名参数据排序
        //计算报文签名
        $param['sign'] = $weibopay->getSignMsg($param, $param['sign_type'],$_input_charset);
        $url = sinapay_mgs_url;
        $data_url = $weibopay->createcurl_data($param);
        $result = $weibopay->curlPost($url, $data_url,$_input_charset);
        $splitdata = json_decode($result, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata['sign_type'],$_input_charset)) {
            $data = array('response_code' => $splitdata['response_code'],
                'response_message' => $splitdata['response_message'],
                'redirect_url' => @$splitdata['redirect_url']
            );
            return json_encode($data);
        } else {
            die ("sing error!");
        }
    }

    /**
     * 查询是否设置了支付密码
     */
    function query_is_set_pay_password($data = array())
    {
        $weibopay = new Weibopay ();
        /**************获取查询是否设置付密码重定向基本参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        ksort($param);//对签名参数据排序
        //计算报文签名
        $param['sign'] = $weibopay->getSignMsg($param, $param['sign_type'],$_input_charset);
        $url = sinapay_mgs_url;
        $data_url = $weibopay->createcurl_data($param);
        $result = $weibopay->curlPost($url, $data_url,$_input_charset);
        $splitdata = json_decode($result, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata['sign_type'],$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else { // 失败
                return $splitdata;
                exit();
            }
        } else {
            return "sing error!";
        }
    }

    /**
     *修改认证手机号，找回认证手机号
     */
    function all_verify_mobile($data = array())
    {
        $weibopay = new Weibopay ();
        /**************获取设置支付密码重定向基本参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $return_url = $data['return_url'];//同步返回地址
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['return_url'] = $return_url;
        $param['_input_charset'] = $_input_charset;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        ksort($param);//对签名参数据排序
        //计算报文签名
        $param['sign'] = $weibopay->getSignMsg($param, $param['sign_type'],$_input_charset);
        $url = sinapay_mgs_url;
        $data_url = $weibopay->createcurl_data($param);
        $result = $weibopay->curlPost($url, $data_url,$_input_charset);
        $splitdata = json_decode($result, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata['sign_type'],$_input_charset)) {
            $data = array('response_code' => $splitdata['response_code'],
                'response_message' => $splitdata['response_message'],
                'redirect_url' => @$splitdata['redirect_url']
            );
            return json_encode($data);
        } else {
            die ("sing error!");
        }
    }

    /**
     *查询经办人信息
     */
    function query_fund_agent_buy($data = array())
    {
        $weibopay = new Weibopay ();
        /**************获取查询是否设置付密码重定向基本参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        ksort($param);//对签名参数据排序
        //计算报文签名
        $param['sign'] = $weibopay->getSignMsg($param, $param['sign_type'],$_input_charset);
        $url = sinapay_mgs_url;
        $data_url = $weibopay->createcurl_data($param);
        $result = $weibopay->curlPost($url, $data_url,$_input_charset);
        $splitdata = json_decode($result, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata['sign_type'],$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else { // 失败
                return $splitdata;
                exit();
            }
        } else {
            return "sing error!";
        }
    }

    /**
     *我的银行卡接口
     */
    function web_binding_bank_card($data = array())
    {
        $weibopay = new Weibopay ();
        /**************获取设置支付密码重定向基本参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $return_url = $data['return_url'];//同步返回地址
        /****************业务参数***********************/
        $identity_id = $data['identity_id'];//会员标识
        $identity_type = $data['identity_type'];//用户标识类型
        $could_bind = $data['could_bind'];//绑定标识
        $could_unbind = $data['could_unbind'];//删除标识

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        /************业务参数*****************/
        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;
        $param['could_bind'] = $could_bind;
        $param['could_unbind'] = $could_unbind;
        $param['return_url'] = $return_url;

        ksort($param);//对签名参数据排序
        //计算报文签名
        $param['sign'] = $weibopay->getSignMsg($param, $param['sign_type'],$_input_charset);
        $url = sinapay_mgs_url;
        $data_url = $weibopay->createcurl_data($param);
        $result = $weibopay->curlPost($url, $data_url,$_input_charset);
        $splitdata = json_decode($result, true);
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $splitdata['sign_type'],$_input_charset)) {
            $data = array('response_code' => $splitdata['response_code'],
                'response_message' => $splitdata['response_message'],
                'redirect_url' => @$splitdata['redirect_url']
            );
            return json_encode($data);
        } else {
            die ("sing error!");
        }
    }

    /**
     * 创建托管代收交易
     * unbinding_bank_card_advance
     * @param array $data
     */
    function create_hosting_collect_trade($data = array())
    {
        $weibopay = new Weibopay();
        /************基本参数************/
        $service = $data ["service"]; // 接口名称
        $version = $data ["version"]; // 接口版本
        $request_time = $data ["request_time"]; // 接口版本
        $partner_id = $data ["partner_id"]; // 合作者身份ID
        $_input_charset = $data ["_input_charset"]; // 参数编码字符集
        $sign_type = $data ["sign_type"]; // 签名方式
        $return_url = $data ["return_url"];//同步页面返回地址
        $notify_url = $data ["notify_url"];//异步订单交易结果通知地址
        /***业务参数***/
        $out_trade_no = $data ["out_trade_no"]; // 交易订单号
        $out_trade_code = $data ["out_trade_code"]; //交易码
        $summary = $data["summary"];//摘要
        $trade_close_time = $data["trade_close_time"];//交易关闭时间
        $can_repay_on_failed = $data["can_repay_on_failed"];//支付失败后是否可以再次支付
        $goods_id = @$data["goods_id"];//标的号
        $payer_id = $data["payer_id"];//付款用户
        $payer_identity_type = $data["payer_identity_type"];//标示类型
        $pay_method = $data ["pay_method"];//demo自定义支付接口参数
        $pay_methodtype = $data ["pay_method"];
        $collect_trade_type = @$data['collect_trade_type'];//代收交易类型
        // 付款金额
        $amount = $data ["amount"];//demo自定义代收金额
        switch ($pay_method) {
            //网银支付
            case "online_bank" :
                // 网银支付参数
                // 银行编码
                $online_bank_bankid = $data ["online_bank_bankid"];
                // 卡类型
                $online_bank_card_type = $data ["online_bank_card_type"];
                // 卡属性
                $online_bank_card_attribute = $data ["online_bank_card_attribute"];
                // 支付方式
                $pay_method = "online_bank^" . $amount . "^" . $online_bank_bankid . "," . $online_bank_card_type . "," . $online_bank_card_attribute;
                break;
            //余额代收
            case "balance" :
                // 余额支付
                $account_type = $data ["account_type"];//账户类型
                $pay_method = "balance^" . $amount . "^" . $account_type;
                break;
            default :
                echo "支付方式错误！";
        }
        $param = array();
        $param ["service"] = $service;
        $param ["version"] = $version;
        $param ["request_time"] = $request_time;
        $param ["partner_id"] = $partner_id;
        $param ["_input_charset"] = $_input_charset;
        $param ["sign_type"] = $sign_type;
        $param ["return_url"] = $return_url;
        $param ["notify_url"] = $notify_url;
        /************业务参数************/
        $param ["out_trade_no"] = $out_trade_no;
        $param ["out_trade_code"] = $out_trade_code;
        $param ["summary"] = $summary;
        $param ["trade_close_time"] = $trade_close_time;
        $param ["can_repay_on_failed"] = $can_repay_on_failed;
        if ($goods_id != "") {
            $param ["goods_id"] = $goods_id;
        }
        $param ["payer_id"] = $payer_id;
        if ($collect_trade_type != "") {
            $param ["collect_trade_type"] = $collect_trade_type;
        }
        $param ["payer_identity_type"] = $payer_identity_type;
        $param ["pay_method"] = $pay_method;
        $param ["payer_ip"] = $data['payer_ip'];
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("创建托管代收交易请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset);
        return $result;
              }

    /**
     * 创建托管代付交易
     * create_single_hosting_pay_trade
     * @param array $data
     */
    function create_single_hosting_pay_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************创建托管代付交易****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $notify_url = $data ["notify_url"];//异步订单交易结果通知地址
        /****************业务参数***********************/
        $out_trade_no = $data['out_trade_no'];//交易订单号
        $out_trade_code = $data['out_trade_code'];//交易码
        $payee_identity_id = $data['payee_identity_id'];//收款人标识
        $payee_identity_type = $data['payee_identity_type'];//收款人标识类型
        $account_type = $data['account_type'];//收款人账户类型
        $amount = $data['amount'];//收款金额
        $split_list = @$data['split_list'];//分账信息
        $creditor_info_list = @$data['creditor_info_list'];//债权表东明细列表
        $summary = $data['summary'];//摘要

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['notify_url'] = $notify_url;

        $param['out_trade_no'] = $out_trade_no;
        $param['out_trade_code'] = $out_trade_code;
        $param['payee_identity_id'] = $payee_identity_id;
        $param['payee_identity_type'] = $payee_identity_type;
        $param['account_type'] = $account_type;
        $param['amount'] = $amount;
        if ($split_list != "") {
            $param['split_list'] = $split_list;
        }
        if ($creditor_info_list != "") {
            $param['creditor_info_list'] = $creditor_info_list;
        }
        $param['summary'] = $summary;
        $param ["user_ip"] = $data['user_ip'];
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管代付交易请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 创建批量托管代付交易
     * create_batch_hosting_pay_trade
     * @param array $data
     */
    function create_batch_hosting_pay_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************创建批量托管代付交易****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $notify_url = $data['notify_url'];//交易结果异步通知地址
        /****************业务参数***********************/
        $out_pay_no = $data['out_pay_no'];//支付请求号
        $out_trade_code = $data['out_trade_code'];//交易码
        $trade_list = $data['trade_list'];//交易列表
        $notify_method = $data['notify_method'];//通知方式

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['notify_url'] = $notify_url;

        $param['out_pay_no'] = $out_pay_no;
        $param['out_trade_code'] = $out_trade_code;
        $param['trade_list'] = $trade_list;
        $param['notify_method'] = $notify_method;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名结果放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管批量代付交易请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 创建托管交易支付
     * pay_hosting_trade
     * @param array $data
     */
    function pay_hosting_trade($data = array())
    {
        $weibopay = new Weibopay();
        /************基本参数************/
        $service = $data["service"]; // 接口名称
        $version = $data["version"]; // 接口版本
        $request_time = $data["request_time"]; // 接口版本
        $partner_id = $data["partner_id"]; // 合作者身份ID
        $_input_charset = $data["_input_charset"]; // 参数编码字符集
        $sign_type = $data["sign_type"]; // 签名方式
        $return_url = $data["return_url"];//同步页面返回地址
        $notify_url = $data["notify_url"];//异步订单交易结果通知地址
        /***业务参数***/
        $out_pay_no = $data["out_pay_no"]; //支付请求号
        $outer_trade_no_list = $data["outer_trade_no_list"];//商户网站唯一交易订单号集合
        $amount = $data["amount"];//商户网站唯一交易订单号集合
        $pay_method = $data["pay_method"];//demo自定义支付接口参数
        //获取付款用户IP
        $payer_ip = $weibopay->get_ip();
        $pay_methodtype = $data["pay_method"];
        switch ($pay_method) {
            //网银支付
            case "online_bank" :
                // 网银支付参数
                // 银行编码
                $online_bank_bankid = $data["online_bank_bankid"];
                // 卡类型
                $online_bank_card_type = $data["online_bank_card_type"];
                // 卡属性
                $online_bank_card_attribute = $data["online_bank_card_attribute"];
                // 支付方式
                $pay_method = "online_bank^" . $amount . "^" . $online_bank_bankid . "," . $online_bank_card_type . "," . $online_bank_card_attribute;
                break;
            //余额代收
            case "balance" :
                // 余额支付
                $account_type = $data ["account_type"];//账户类型
                $pay_method = "balance^" . $amount . "^" . $account_type;
                break;
            default :
                echo "支付方式错误！";
        }
        $param = array();
        $param ["service"] = $service;
        $param ["version"] = $version;
        $param ["request_time"] = $request_time;
        $param ["partner_id"] = $partner_id;
        $param ["_input_charset"] = $_input_charset;
        $param ["sign_type"] = $sign_type;
        $param ["return_url"] = $return_url;
        $param ["notify_url"] = $notify_url;
        /************业务参数************/
        $param ["out_pay_no"] = $out_pay_no;
        $param ["payer_ip"] = $payer_ip;
        $param ["outer_trade_no_list"] = $outer_trade_no_list;
        $param ["pay_method"] = $pay_method;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名结果放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("创建托管交易支付请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset);
        return $result;
              }

    /**
     * 支付结果查询
     * query_pay_result
     * @param array $data
     */
    function query_pay_result($data = array())
    {
        $weibopay = new Weibopay();
        /**************支付结果查询****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_pay_no = $data['out_pay_no'];//支付请求号

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;

        $param['out_pay_no'] = $out_pay_no;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("支付结果查询请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 托管交易查询
     * query_hosting_trade
     * @param array $data
     */
    function query_hosting_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************托管交易查询****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $identity_id = @$data['identity_id'];
        $identity_type = @$data['identity_type'];
        $out_trade_no = @$data['out_trade_no'];
        $start_time = @$data['start_time'];
        $end_time = @$data['end_time'];
        $page_no = @$data['page_no'];
        $page_size = @$data['page_size'];

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        if ($identity_id != "") {
            $param['identity_id'] = $identity_id;
        }
        if ($identity_type != "") {
            $param['identity_type'] = $identity_type;
        }
        if ($out_trade_no != "") {
            $param['out_trade_no'] = $out_trade_no;
        }
        if ($start_time != "") {
            $param['start_time'] = $start_time;
        }
        if ($end_time != "") {
            $param['end_time'] = $end_time;
        }
        if ($page_no != "") {
            $param['page_no'] = $page_no;
        }
        if ($page_size != "") {
            $param['page_size'] = $page_size;
        }
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管交易查询请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 托管交易批次查询
     * query_hosting_batch_trade
     * @param array $data
     */
    function query_hosting_batch_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************托管交易批次查询****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_batch_no = $data['out_batch_no'];
        $page_no = @$data['page_no'];
        $page_size = @$data['page_size'];

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['out_batch_no'] = $out_batch_no;
        if ($page_no != "") {
            $param['page_no'] = $page_no;
        }
        if ($page_size != "") {
            $param['page_size'] = $page_size;
        }
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将签名结果放入请求报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管交易批次查询请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 托管退款
     * create_hosting_refund
     * @param array $data
     */
    function create_hosting_refund($data = array())
    {
        $weibopay = new Weibopay();
        /**************托管退款****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $notify_url = $data['notify_url'];//异步回调地址
        /****************业务参数***********************/
        $out_trade_no = $data['out_trade_no'];
        $orig_outer_trade_no = $data['orig_outer_trade_no'];
        $refund_amount = $data['refund_amount'];
        $summary = $data['summary'];

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['notify_url'] = $notify_url;

        $param['_input_charset'] = $_input_charset;
        $param['out_trade_no'] = $out_trade_no;
        $param['orig_outer_trade_no'] = $orig_outer_trade_no;
        $param['refund_amount'] = $refund_amount;
        $param['summary'] = $summary;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管退款接口请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 托管退款查询
     * query_hosting_refund
     * @param array $data
     */
    function query_hosting_refund($data = array())
    {
        $weibopay = new Weibopay();
        /**************托管退款查询****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_trade_no = @$data['out_trade_no'];//退款单号
        $start_time = @$data['start_time'];//开始时间
        $end_time = @$data['end_time'];//结束时间
        $page_no = @$data['page_no'];//页号
        $page_size = @$data['page_size'];//页面大小
        $identity_id = @$data['identity_id'];//用户标识
        $identity_type = @$data['identity_type'];//用户标识类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;

        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;

        if ($out_trade_no != "") {
            $param['out_trade_no'] = $out_trade_no;
        }
        if ($start_time != "") {
            $param['start_time'] = $start_time;
        }
        if ($end_time != "") {
            $param['end_time'] = $end_time;
        }
        if ($page_no != "") {
            $param['page_no'] = $page_no;
        }
        if ($page_size != "") {
            $param['page_size'] = $page_size;
        }

        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管退款查询请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 托管充值
     * create_hosting_deposit
     * @param array $data
     */
    function create_hosting_deposit($data = array())
    {
        $weibopay = new Weibopay();
        $service = $data ["service"]; // 接口名称
        $version = $data ["version"]; // 接口版本
        $request_time = $data ["request_time"]; // 接口版本
        $partner_id = $data ["partner_id"]; // 合作者身份ID
        $_input_charset = $data ["_input_charset"]; // 参数编码字符集
        $sign_type = $data ["sign_type"]; // 签名方式
        $return_url = $data ["return_url"];//同步返回地址
        $notify_url = $data ["notify_url"];//异步通知地址
        //获取付款用户IP
        $payer_ip = $weibopay->get_ip();
        /**
         * ***************** 业务参数 *********************************
         */
        $out_trade_no = $data ["out_trade_no"]; // 交易订单号
        $identity_id = $data ["identity_id"]; // 用户标识信息
        $identity_type = $data ["identity_type"]; // 用户标识类型
        $account_type = $data ["account_type"]; // 账户类型
        $pay_method = $data ["pay_method"];
        $pay_methodtype = $data ["pay_method"];
        // 付款金额
        $amount = $data ["amount"];
        switch ($pay_method) {
            case "online_bank" :
                // 网银支付参数
                // 银行编码
                $online_bank_bankid = $data ["online_bank_bankid"];
                // 卡类型
                $online_bank_card_type = $data ["online_bank_card_type"];
                // 卡属性
                $online_bank_card_attribute = $data ["online_bank_card_attribute"];
                // 支付方式
                $pay_method = "online_bank^" . $amount . "^" . $online_bank_bankid . "," . $online_bank_card_type . "," . $online_bank_card_attribute;
                break;
            default :
                echo "支付方式错误！";
        }
        $param = array();
        $param ["service"] = $service;
        $param ["version"] = $version;
        $param ["request_time"] = $request_time;
        $param ["partner_id"] = $partner_id;
        $param ["_input_charset"] = $_input_charset;
        $param ["sign_type"] = $sign_type;
        $param ["return_url"] = $return_url;
        $param ["notify_url"] = $notify_url;
        $param ["pay_method"] = $pay_method;
        $param ["out_trade_no"] = $out_trade_no;
        $param ["identity_id"] = $identity_id;
        $param ["identity_type"] = $identity_type;
        $param ["account_type"] = $account_type;
        $param ["payer_ip"] = $payer_ip;
        $param ["amount"] = $amount;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管充值交易请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $weibopay->write_log($result);
       return $result;
        exit();
    }

    /**
     * 托管充值查询
     * query_hosting_deposit
     * @param array $data
     */
    function query_hosting_deposit($data = array())
    {
        $weibopay = new Weibopay();
        /**************托管充值查询****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_trade_no = @$data['out_trade_no'];//交易订单号
        $start_time = @$data['start_time'];//开始时间
        $end_time = @$data['end_time'];//结束时间
        $page_no = @$data['page_no'];//页号
        $page_size = @$data['page_size'];//页面大小
        $identity_id = @$data['identity_id'];//用户标识
        $identity_type = @$data['identity_type'];//用户标识类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;

        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;

        if ($out_trade_no != "") {
            $param['out_trade_no'] = $out_trade_no;
        }
        if ($start_time != "") {
            $param['start_time'] = $start_time;
        }
        if ($end_time != "") {
            $param['end_time'] = $end_time;
        }
        if ($page_no != "") {
            $param['page_no'] = $page_no;
        }
        if ($page_size != "") {
            $param['page_size'] = $page_size;
        }

        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管充值查询" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 托管提现接口
     * create_hosting_withdraw
     * @param array $data
     */
    function create_hosting_withdraw($data = array())
    {
        $weibopay = new Weibopay();
        /**************托管提现****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $notify_url = $data['notify_url'];//异步通知结果地址
        $return_url = $data['return_url'];//同步页面返回地址
        /****************业务参数***********************/
        $out_trade_no = $data['out_trade_no'];//交易订单号
        $summary = $data['summary'];//提现内容摘要
        $account_type = $data['account_type'];//账户类型
        $amount = $data['amount'];//提现金额
        $identity_id = $data['identity_id'];//用户标识
        $identity_type = $data['identity_type'];//用户标识类型
        $withdraw_mode = $data['withdraw_mode'];//提现方式
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['notify_url'] = $notify_url;
        $param['return_url'] = $return_url;

        $param['identity_id'] = $identity_id;
        $param['withdraw_mode'] = $withdraw_mode;
        $param['identity_type'] = $identity_type;
        $param['out_trade_no'] = $out_trade_no;
        $param['summary'] = $summary;
        $param['account_type'] = $account_type;
        $param['amount'] = $amount;
        ksort($param);//对签名参数据排序
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        $param['sign'] = $sign;
        $weibopay->write_log("托管提现请求报文" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        //收银台模式提现返回的是个表单不需要做json转换
        return $result;
    }

    /**
     * 托管提现查询
     * query_hosting_withdraw
     * @param array $data
     */
    function query_hosting_withdraw($data = array())
    {
        $weibopay = new Weibopay();
        /**************托管提现查询****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_trade_no = @$data['out_trade_no'];//交易单号
        $start_time = @$data['start_time'];//开始时间
        $end_time = @$data['end_time'];//结束时间
        $page_no = @$data['page_no'];//页号
        $page_size = @$data['page_size'];//页面大小
        $identity_id = @$data['identity_id'];//用户标识
        $identity_type = @$data['identity_type'];//用户标识类型
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;

        $param['identity_id'] = $identity_id;
        $param['identity_type'] = $identity_type;

        if ($out_trade_no != "") {
            $param['out_trade_no'] = $out_trade_no;
        }
        if ($start_time != "") {
            $param['start_time'] = $start_time;
        }
        if ($end_time != "") {
            $param['end_time'] = $end_time;
        }
        if ($page_no != "") {
            $param['page_no'] = $page_no;
        }
        if ($page_size != "") {
            $param['page_size'] = $page_size;
        }

        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管提现查询请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 转账接口
     * create_hosting_transfer
     * @param array $data
     */
    function create_hosting_transfer($data = array())
    {
        $weibopay = new Weibopay();
        /**************转账接口****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_trade_no = $data['out_trade_no'];//交易订单号
        $payer_identity_id = $data['payer_identity_id'];//付款人标识
        $payer_identity_type = $data['payer_identity_type'];//付款人标识类型
        $payer_account_type = @$data['payer_account_type'];//付款人账户类型
        $payee_identity_id = $data['payee_identity_id'];//收款人标识
        $payee_identity_type = $data['payee_identity_type'];//收款人标识类型
        $payee_account_type = @$data['payee_account_type'];//收款人账户类型
        $amount = $data['amount'];//转账金额
        $summary = @$data['summary'];//摘要
        $transfer_mode = @$data['transfer_mode'];//转账方式

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['transfer_mode'] = $transfer_mode;


        $param['out_trade_no'] = $out_trade_no;
        $param['payer_identity_id'] = $payer_identity_id;
        $param['payer_identity_type'] = $payer_identity_type;
        if ($payer_account_type != "") {
            $param['payer_account_type'] = $payer_account_type;
        }
        $param['payee_identity_id'] = $payee_identity_id;
        $param['payee_identity_type'] = $payee_identity_type;
        if ($payee_account_type != "") {
            $param['payee_account_type'] = $payee_account_type;
        }
        $param['amount'] = $amount;
        if ($summary != "") {
            $param['summary'] = $summary;
        }
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管转账接口请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
                return $result;
                exit();
    }

    /**
     * 支付推进请求号
     * advance_hosting_pay
     * @param array $data
     */
    function advance_hosting_pay($data = array())
    {
        $weibopay = new Weibopay();
        /**************支付推进请求****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_advance_no = $data['out_advance_no'];//支付推进请求号
        $ticket = $data['ticket'];//ticket有效期为15分钟，可以多次使用（最多5次）
        $validate_code = $data['validate_code'];//用户绑卡手机收到的验证码
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['ticket'] = $ticket;
        $param['validate_code'] = $validate_code;
        $param['out_advance_no'] = $out_advance_no;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("支付推进请求" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }
    /**
     * 创建单笔代付到提现卡
     * create_single_hosting_pay_to_card_trade
     * @param array $data
     */
    function create_single_hosting_pay_to_card_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************创建单笔代付到提现卡****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $notify_url = $data['notify_url'];//异步通知地址
        /****************业务参数***********************/
        $out_trade_no = $data['out_trade_no'];//交易订单号
        $out_trade_code = $data['out_trade_code'];//交易码
        $amount = $data['amount'];//收款金额
        $summary = $data['summary'];//摘要
        $collect_method = $data['collect_method'];//收款方式
        $goods_id = $data['goods_id'];//标的号
        $creditor_info_list = @$data['creditor_info_list'];//债权变动明细列表
        $payto_type = $data['payto_type'];
        
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['notify_url'] = $notify_url;
        if ($creditor_info_list != "") {
            $param['creditor_info_list'] = $creditor_info_list;
        }
        $param['out_trade_no'] = $out_trade_no;
        $param['out_trade_code'] = $out_trade_code;
        $param['amount'] = $amount;
        $param['collect_method'] = $collect_method;
        $param['goods_id'] = $goods_id;
        $param['summary'] = $summary;
        $param['payto_type'] = $payto_type;
        $param['user_ip'] = $data['user_ip'];
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("托管代付到提现卡" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 创建批量代付到提现卡交易
     * create_batch_hosting_pay_to_card_trade
     * @param array $data
     */
    function create_batch_hosting_pay_to_card_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************创建批量代付到提现卡****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        $notify_url = $data['notify_url'];//批量代付到提现卡
        /****************业务参数***********************/
        $out_pay_no = $data['out_pay_no'];//支付请求号
        $out_trade_code = $data['out_trade_code'];//交易码
        $trade_list = $data['trade_list'];//交易列表
        $notify_method = $data['notify_method'];//通知方式

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['notify_url'] = $notify_url;

        $param['out_pay_no'] = $out_pay_no;
        $param['out_trade_code'] = $out_trade_code;
        $param['trade_list'] = $trade_list;
        $param['notify_method'] = $notify_method;
        ksort($param);//对签名参数据排序
        //签名计算
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("批量代付到提现卡请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 代收完成交易
     * finish_pre_auth_trade
     * @param array $data
     */
    function finish_pre_auth_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************代收完成交易****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_request_no = $data['out_request_no'];//代收完成请求单号
        $trade_list = $data['trade_list'];//交易列表

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;

        $param['out_request_no'] = $out_request_no;
        $param['trade_list'] = $trade_list;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("创建代收完成交易" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 代收撤销
     * cancel_pre_auth_trade
     * @param array $data
     */
    function cancel_pre_auth_trade($data = array())
    {
        $weibopay = new Weibopay();
        /**************代收撤销****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_request_no = $data['out_request_no'];//代收完成请求单号
        $trade_list = $data['trade_list'];//交易列表

        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;

        $param['out_request_no'] = $out_request_no;
        $param['trade_list'] = $trade_list;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("创建代收撤销交易" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 存钱罐基金收益率查询
     * query_fund_yield
     * @param array $data
     */
    function query_fund_yield($data = array())
    {
        $weibopay = new Weibopay();
        /**************存钱罐基金收益率查询****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $fund_code = $data['fund_code'];//货币基金代码
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['sign_type'] = $sign_type;
        $param['_input_charset'] = $_input_charset;
        $param['fund_code'] = $fund_code;
        ksort($param);//对签名参数据排序
        //计算签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //签名放入报文
        $param['sign'] = $sign;
        $weibopay->write_log("创建货币基金收益率查询请求" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 查询标的信息
     * @param array $data
     * @return mixed
     */
    function query_bid_info($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询标的信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_bid_no = $data['out_bid_no'];//商户标的号
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;
        $param['out_bid_no'] = $out_bid_no;
        ksort($param);//对签名参数据排序
        //对查询商户标的信息报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将查询商户标的信息报文放入请求sina数组
        $param['sign'] = $sign;
        $weibopay->write_log("查询认证信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
        $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }

    /**
     * 标的录入接口
     */
    function create_bid_info($data = array())
    {
        $weibopay = new Weibopay();
        /**************获取查询标的信息参数****************/
        $service = $data['service'];//服务名称
        $version = $data['version'];//接口版本
        $request_time = $data['request_time'];//请求时间
        $partner_id = $data['partner_id'];//合作者身份ID
        $_input_charset = $data['_input_charset'];//参数编码字符集
        $sign_type = $data['sign_type'];//签名类型
        /****************业务参数***********************/
        $out_bid_no = $data['out_bid_no'];//商户标的号
        $web_site_name = $data['web_site_name'];//网站名称/平台名称
        $bid_name = $data['bid_name'];//标的名称
        $bid_type = $data['bid_type'];//标的类型
        $bid_amount = $data['bid_amount'];//标的金额
        $bid_year_rate = $data['bid_year_rate'];//年化收益率
        $bid_duration = $data['bid_duration'];//借款期限
        $repay_type = $data['repay_type'];//还款方式
        $begin_date = $data['begin_date'];//标的开始时间
        $term = $data['term'];//还款期限
        $guarantee_method = $data['guarantee_method'];//担保方式
        $borrower_info_list = $data['borrower_info_list'];//借款人信息列表
        $param = array();
        $param['service'] = $service;
        $param['version'] = $version;
        $param['request_time'] = $request_time;
        $param['partner_id'] = $partner_id;
        $param['_input_charset'] = $_input_charset;
        $param['sign_type'] = $sign_type;

        $param['out_bid_no'] = $out_bid_no;
        $param['web_site_name'] = $web_site_name;
        $param['bid_name'] = $bid_name;
        $param['bid_type'] = $bid_type;
        $param['bid_amount'] = $bid_amount;
        $param['bid_year_rate'] = $bid_year_rate;
        $param['bid_duration'] = $bid_duration;
        $param['repay_type'] = $repay_type;
        $param['begin_date'] = $begin_date;
        $param['term'] = $term;
        $param['guarantee_method'] = $guarantee_method;
        $param['borrower_info_list'] = $borrower_info_list;
        ksort($param);//对签名参数据排序
        //对标的录入的信息报文进行签名
        $sign = $weibopay->getSignMsg($param, $sign_type,$_input_charset);
        //将标的录入信息报文放入请求sina数组
        $param['sign'] = $sign;
        $weibopay->write_log("查询认证信息请求参数" . json_encode($param));
        $data = $weibopay->createcurl_data($param); // 调用createcurl_data创建模拟表单需要的数据
        $result = $weibopay->curlPost(sinapay_mas_url, $data,$_input_charset); // 使用模拟表单提交进行数据提交
       $splitdata = json_decode($result, true);
        $sign_type = $splitdata ['sign_type'];//签名方式
        ksort($splitdata); // 对签名参数据排序
        if ($weibopay->checkSignMsg($splitdata, $sign_type,$_input_charset)) {
            if ($splitdata["response_code"] == 'APPLY_SUCCESS') { // 成功
                return $splitdata;
                exit();
            } else {
                //业务处理失败
                return $splitdata;
                exit();
            }
        } else {
            die ("sing error！");
        }
    }
}

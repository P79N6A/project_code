<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/bangbf/key/';
return [
    "version"       =>"1.0",  #版本
    "merchantId"    =>"800010000050062",//商户编号
    "signType"      =>"RSA",
    "charset"       =>"02",//00 GBK 01 GB2312 02 UTF-8
    "service"       =>"FirstQPayment",
    "offlineNotifyUrl"  =>'http://'.$_SERVER['HTTP_HOST'].'/test/testjson',//异步回调地址
    "idType"        => "00",//身份证
    "cardType"      => "0",//0 借记卡 1贷记卡
    "encryptFlag"   => "1", //敏感信息加密 1 用户信息
    "currency"      => "CNY",//交易币种 人民币
    'isRegAgreement'=> "Y",//是否注册协议 Y/N
    //证书
    'private_key_password' => "1234qwer",	//商户私钥证书密码
    'private_key_path' => $keyPath."800010000050062.p12",  //商户私钥
    'public_key_path' => $keyPath."8f8server.cer",  //邦宝付公钥
    'bangbf_public_key' => "308203B63082029EA003020102021438129EA54B6611327421582D0855752AAA7676A7300D06092A864886F70D0101050500306C3126302406035504030C1DE5A4A9E5A881E8AF9AE4BFA1525341E6B58BE8AF95E794A8E688B7434131123010060355040B0C09525341E6B58BE8AF953121301F060355040A0C18E5A4A9E5A881E8AF9AE4BFA1E6B58BE8AF95E7B3BBE7BB9F310B300906035504061302434E301E170D3137303333303133343131315A170D3233303332393133333931325A307B31183016060355040A0C0FE5A4A9E5A881E8AF9AE4BFA1525341310B3009060355040B0C0252413130302E06092A864886F70D010901162141423034343333336C697579756665694061622D696E737572616E63652E636F6D310F300D06035504040C06736572766572310F300D06035504030C0673657276657230819F300D06092A864886F70D010101050003818D00308189028181008AEC5BFABD028E35CE7269C924BBA7B686643F58E903C71B350CD22186847470F4BDE9F6EC51BE5B925322051F5FDE94B780871F02276A01E1586AA8CDD72EDEB08B06A91F6D42987427BEEE76B01E824035593474F33696A4E9752BAD4E792D8D6E1FCAF91F40DCD8217C2809E20B5CC55FD03B521BDBF6918FCFC09CAF3A2F0203010001A381C43081C130090603551D1304023000300B0603551D0F0404030204F030670603551D1F0460305E305CA05AA0588656687474703A2F2F697472757364656D6F2E636F6D2F546F7043412F7075626C69632F697472757363726C3F43413D30323039413642414638384545323539414244433738363030423239393331343332334141353734301F0603551D23041830168014B46E6591914BD17BC1A09FA43E7DCF57E0B52E48301D0603551D0E041604140D7F1EC1F593B52EE1DF934F8DC7C3A4E20BDF50300D06092A864886F70D010105050003820101005590D530DCB789DF7CF1290FF7802B91C4627C6A5D3BD14F33EF4CC091AEDE9F5AA1AD21F5028729152A822516E72508B8AF7D37BD1F2EB879E1F39996039E864A010DE4B624C1D5F75EEC91E598857A35414E9C8722A0F8836D0F5F297943333F800811CC17AA4D9967A28BDCE9F245C7C620B7EBEC1BB35983CC07AA047DDC51F3F08FE902D5983398C930255EDA57FB6E1709B69EA920BAB7C5008E9FBA624BCC96F25EBA9F7BD23C2CEB8AE5D661655AD4623B47505BBD039E13BAD9F28721975F081448C7F8D84B3DFE734DD8C265196E6D61F452D6DF6C9EDC84022F80AA238E8ACCD5740BC414F1F080E0EC1F33FDF9232BA19F8D6F52CCE633CB0B3D",//邦宝付公钥

    // 操作url
    "action_url" => 'http://paytesta.8f8.com/cashier',
];
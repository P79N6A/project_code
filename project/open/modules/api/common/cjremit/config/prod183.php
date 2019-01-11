<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/cjremit/key/prod183/';
return [
	"Version"      =>"1.0",  #版本
	"Service"      =>"cjt_dsf",  #接口名称
	"PartnerId"    =>'200002160180',  #商户的唯一编号
	"CorpAcctNo" =>'gaomingyang@ihsmf.com', #企业账号

//	"business_code" => '00505', #业务代码
//	"product_code" => '60020002', #产品编码


	'InputCharset'=>'UTF-8',

	//证书
//	'private_key_pwd' => "123456",	//商户私钥证书密码
	'private_key_path' => $keyPath."rsa_private_key.pem",  //注意证书路径是否存在		私钥
	'public_key_path' => $keyPath."rsa_public_key.pem",//注意证书路径是否存在			公钥

	//请求接口url
	"trade_url" => 'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do?',	#请求地址

	'back_url' => 'http://open.xianhuahua.com/cjback/notify',	#通知地址
];
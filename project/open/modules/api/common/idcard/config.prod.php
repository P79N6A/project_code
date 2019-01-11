<?php
//配置host： 183.230.169.154  dataapi.msxf.lotest
// 测试账号
return [

	// 客户ID，以1000开头由20位纯数字组成，具有唯一性
	'partner' => "90005467943379905650",
	
    // 客户私钥
	'private_key' =>"MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAJoCFcdlXnXlZlR3G+D7ZbJt1mvHlbxerBNrMYMP9noj9dT3MGp71UV5asXuwfXfB6DkHsWX5q6BWVtu4gNkVE9sns7GuoMziBgs0MkAvu+SW56GHI2P5jK4UY6610w7h+1z4m8Sr3BZOz8l5lFo7tc8O6gpqG6IML/Ww31zk/RPAgMBAAECgYBaEFoYL6ncHHmJb9Z37cz9WcqJYUCp1lufR5K+6LjlmN4M2zoPK7f/VxAgDI6VcQaPCpkMSNb4umA9Xk0CWswKdgQzG4uwTLLA9sxZdLUzFv8P3P8stw9uanzZbJP7THfn/B9NqhSu5P4MznYzBK9Bwg52eLUSTtTpE2RTBETJ4QJBANJm1B6O98zpfxMTrAwah0HNhps55R4C+KuBwsZPeYDJ+0MB9/4icgTWx4GHefajLt6Oj1cXaM0lbwvw3IwQgYcCQQC7YoUxNy5rScUpaduOTquW9NUU5/aluEfj/cQQVbNi1Ml8Uf31J5QFMYs8hg3P38DiaRUWaC1Jmqi7qgp6KEj5AkEAtqTzAHAFZslPOYU7NeqVfabncbqJTUsxCNkE9teo1wb/agS8fczzA4Za+/NaCaNQnXxNrEGzBVq4kjoNij0N4wJAMxMEaMi4n0epNMOEs4If5PJwzdT39m2HMs5tTWJ+lZaYIImcpeCWyN+bKvEC/MDpKw0nUUct6Nz91sDfQDKQyQJAX8NuNoI8w3nddMUhm1ZtolD1drgAOjFyMiypm38jjA6MAKQOdJtRhYWDDzcYcqEahb1CnRDmfScgApFCy6KMIg==",
	
	//马上消费征信平台公钥
	'msxf_credit_public_key' => "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCO9/WSFp4wuPJt0HSqXsjP05jCGqmmVT60BnJgVVO0vEuE6g81Y+aTHiYH3d9WjIBCJVXAbiHO5nihbaYVpsqdSDFzOyH8O+fzC3Tf6ZpZ2LxU07jVsbnHm2SMNcma2Xu8WVHjGxpLkTPg8wB9I32tZnLZHPst3VSON6n+1GJB4wIDAQAB",
	
	// 字符编码格式 目前支持utf-8
	'charset' => "utf-8",
	
	//马上消费网关地址
	'msxf_gateway_url' => "http://dataapi.msxf.lotest/gateway",
	
	// 异步通知征信数据的url
	'notify_url' => "http://open.xianhuahua.com/api/idcardback/callurl" ,
	
	// 签名方式 不需修改
	'sign_type' => "RSA",
	
	//单用户实时查询征信数据的api方法
	'method' => "credit.single.realtime.query",
	
	// 马上消费征信平台版本信息
	'msxf_credit_version' => "1.0",
	
	//通知成功后返回的字符串
	'success' => "success",
	
	//马上消费征信平台反欺诈数据产品编码
	'msxf_credit_product_pf_antifo' => "PF_ANTIFO",
	
];

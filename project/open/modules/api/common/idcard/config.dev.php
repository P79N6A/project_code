<?php
//配置host： 183.230.169.154  dataapi.msxf.lotest
// 测试账号
return [

	// 客户ID，以1000开头由20位纯数字组成，具有唯一性
	'partner' => "90002315181114161199",
	
    // 客户私钥
	'private_key' =>"MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKFHOMfQO+bc8Wy5lg8Bbe+YgI5QH/WMCN6Yr4vsSR10d6eYUnOR+8+BhLpyKVbM849piBwmwfV9lWj/Hg1U+60F/GxORgKB+SzeTEgXVCfqXSSH2rWlwBE7OTXEFK12C5LinXGa/22LPNY7jBLBCG9SQozeJzRe69Ihuue69NyDAgMBAAECgYALRLGn7FmzGK7ZnOHqLHxk6C/bQafp/R3Fh7+END8riq6tjAv+vS1t4yvF5yISSYGe/I2hAeg9YcflrSWJYu2unajLAhtfr9HhKCJZk5l2YN41GzAGm1Qd47BKZtRi44BhpQgqW7+n0uQLITFLWZjNCN1vS2gaD4SkZYoTzGCSyQJBANuJbOcUrmTei4ENgVGONzQ+eHkrl7sZoItoiSSbTtXWd+d9Fzqab4NDkYa7YiKmqnxbLmetOdaBzmSPvnBOhoUCQQC8EKzVXWUnlTyP+xa5FH3Nn8OEMawRvrEaXVUQss1ieHc0f3xRL9koWfyvM2Rd99hPXUTPZyrp/Tk7b9pE3tlnAkEAwNQoj6AevgKrTiNqnxPncUAd2XBsya5s0YZ0T28LA9BpaS76pELaB9XlkQ4t2HnD7Y65Z99DmiJSAwovH74ZcQJAJgZtG7sFj+pR037eSk/FXAUYoCr28qOO5ZjHcVflxSo8WAYK2dOF4e3H9Ji8i29ociuWlSTz/Vmw776e8FvqswJBAMHj7TRgBojUAK4mJkbLMcWMo+bA2ijMdZdEBmeQSIQS7YnhvDI3KTEENJnkqHkFKMLiy4M7P+Nw1Ybm2UppCI0=",
	
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

<?php
// 生产账号
return [

	// start 同盾分配的合作方标示
	'fraudmetrix_partner_code' => 'xianhuahua',
	// end 
	
	// start 同盾分配的API秘钥 
	'fraudmetrix_secret_key' => '69ba0c3513e047c8a38b348184036aac',
	'fraudmetrix_secret_key_web' => '69ba0c3513e047c8a38b348184036aac',
	'fraudmetrix_secret_key_ios' => '69529551e88d47e287f1c0c7c2480b0b',
	'fraudmetrix_secret_key_android' => '84fcf52f81cc4070bdfb1e99d0f0e02a',
	// end
	
	// start 同盾借款事件ID
	'fraudmetrix_loan_event_id' => 'loan_web',
	'fraudmetrix_loan_event_id_web' => 'loan_web',
	'fraudmetrix_loan_event_id_ios' => 'loan_ios',
	'fraudmetrix_loan_event_id_android' => 'loan_android',
	// end
	
	// start 同盾注册事件ID
	'fraudmetrix_register_event_id' => 'register_web',
	'fraudmetrix_register_event_id_web' => 'register_web',
	'fraudmetrix_register_event_id_ios' => 'register_ios',
	'fraudmetrix_register_event_id_android' => 'register_android',
	// end
	
	//start 风险决策接口访问路径//
	// 'fraudmetrix_api_url' => 'https://api.fraudmetrix.cn/riskService',
	'fraudmetrix_api_url' => 'https://api.tongdun.cn/riskService',
	// end 风险决策 //
	
	//start 命中规则详情接口访问路径//
	'hitruledetail_api_url' => 'https://api.tongdun.cn/webService/hitRuleDetail',
	// end 同盾 //
	
	//start 命中规则详情接口合作方密钥//
	'hitruledetail_secret_key' => '947e5b7c6f1768b173acc14e7dc4b21d',
	// end 同盾 //
	
	//start 同盾证书//
	'fraudmetrix_cacert_url' => '/data/wwwroot/open/modules/api/common/fraudmetrix/prod/cacert.pem',
	//'fraudmetrix_cacert_url' => Yii::$app->basePath.'/modules/api/common/fraudmetrix/prod/cacert.pem',
	// end 同盾 //

	//===============================================================================
	'apiUrl'						=> 'https://api.tongdun.cn/riskService/v1.1',
	'hsmf_hit_api_url' 				=> 'https://api.tongdun.cn/risk/rule.detail/v3.2',
	'hsmf_partner_code'				=> 'xianhuahua',
	'hsmf_secret_key'				=> 'c7b2a32bf0db41e5a6faf60e9146fb42',
	'hsmf_reg_event_id'             => 'Register_web_20180604',
	'hsmf_log_event_id'             => 'Login_web_20180604',
	'hsmf_partner_key'				=> '947e5b7c6f1768b173acc14e7dc4b21d',

];

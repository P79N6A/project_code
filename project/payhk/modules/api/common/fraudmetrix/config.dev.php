<?php
// 测试账号
return [

	// start 同盾分配的合作方标示
	'fraudmetrix_partner_code' => 'xianhuahua',
	// end 
	
	// start 同盾分配的API秘钥 
	'fraudmetrix_secret_key' => 'acb65416d4e84623bb2382db437abffb',
	'fraudmetrix_secret_key_web' => 'acb65416d4e84623bb2382db437abffb',
	'fraudmetrix_secret_key_ios' => '29e11be94d2b4b9b94ab144b5f940e97',
	'fraudmetrix_secret_key_android' => '5b9113b2b7f340eabc386fb1e31d682b',
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
	'fraudmetrix_api_url' => 'https://apitest.fraudmetrix.cn/riskService',
	// end 风险决策 //
	
	//start 命中规则详情接口访问路径//
	'hitruledetail_api_url' => 'https://apitest.tongdun.cn/webService/hitRuleDetail',
	// end 同盾 //
	
	//start 命中规则详情接口合作方密钥//
	'hitruledetail_secret_key' => '5bb55dcf47474cc2ba7dd0a1dae41a2b',
	// end 同盾 //
	
	//start 同盾证书//
	'fraudmetrix_cacert_url' => '/data/wwwroot/open/modules/api/common/fraudmetrix/dev/cacert.pem',
	//'fraudmetrix_cacert_url' => Yii::$app->basePath.'/modules/api/common/fraudmetrix/dev/cacert.pem',
	// end 同盾 //


	//===============================================================================
	'apiUrl'						=> 'https://apitest.tongdun.cn/riskService/v1.1',
	'hsmf_hit_api_url' 				=> 'https://apitest.tongdun.cn/risk/rule.detail/v3.2',
	'hsmf_partner_code'				=> 'xianhuahua',
	'hsmf_secret_key'				=> '30041fd8f597461a877dc1a7668255ed',
	'hsmf_reg_event_id'             => 'Register_web_20180604',
	'hsmf_log_event_id'             => 'Login_web_20180604',
	'hsmf_partner_key'				=> '5bb55dcf47474cc2ba7dd0a1dae41a2b',

];

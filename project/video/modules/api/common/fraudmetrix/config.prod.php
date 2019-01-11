<?php
// 生产账号
return [

	// start 同盾分配的合作方标示
	'fraudmetrix_partner_code' => 'xianhuahua',
	// end 
	
	// start 同盾分配的API秘钥 
	'fraudmetrix_secret_key' => '69ba0c3513e047c8a38b348184036aac',
	// end
	
	// start 同盾借款事件ID
	'fraudmetrix_loan_event_id' => 'loan_web',
	// end
	
	// start 同盾注册事件ID
	'fraudmetrix_register_event_id' => 'register_web',
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
	// end 同盾 //
	
];

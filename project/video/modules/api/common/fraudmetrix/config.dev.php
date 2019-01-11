<?php
// 测试账号
return [

	// start 同盾分配的合作方标示
	'fraudmetrix_partner_code' => 'xianhuahua',
	// end 
	
	// start 同盾分配的API秘钥 
	'fraudmetrix_secret_key' => 'acb65416d4e84623bb2382db437abffb',
	// end
	
	// start 同盾借款事件ID
	'fraudmetrix_loan_event_id' => 'loan_web',
	// end
	
	// start 同盾注册事件ID
	'fraudmetrix_register_event_id' => 'register_web',
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
	// end 同盾 //
	
];

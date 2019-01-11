<?php
// 测试账号
return [
	// 支持的数据源地址 (看一下就行了)
	'datasources' => 'https://www.juxinli.com/orgApi/rest/v2/orgs/XIANHUAHUA/datasources',

	// 申请请求
	'request' => 'https://www.juxinli.com/orgApi/rest/v2/applications/XIANHUAHUA',

	// 提交采集
	'postreq' => 'https://www.juxinli.com/orgApi/rest/v2/messages/collect/req',


	/** 数据接口文档 */ 
	// 获取访问token(设置一次永久的就可以了。这个没必要次次获取,即是下面的access_token)
	'access_report_token' => 'https://www.juxinli.com/api/access_report_token',

	// 查询接口
	'access_raw_data' => 'https://www.juxinli.com/api/access_raw_data',
	'access_raw_data_by_token' => 'https://www.juxinli.com/api/access_raw_data_by_token',

	'access_e_business_raw_data' => 'https://www.juxinli.com/api/access_e_business_raw_data',
	'access_e_business_raw_data_by_token' => 'https://www.juxinli.com/api/access_e_business_raw_data_by_token',
      'access_report_data_by_token' => 'https://www.juxinli.com/api/access_report_data_by_token',
	/** end **/


	/** 账号相关 **/
	'org_name' => 'XIANHUAHUA',
	'client_secret' => '40e0cd37c8224e1f9dca44eb60ad831c',
	'access_token' => '743721bd1a9644f7917ece11ff6fe918',// 永久有效的
	/** end **/
	
];
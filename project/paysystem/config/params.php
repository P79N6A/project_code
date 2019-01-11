<?php

return [
	'serviceTypes' => [
		1 => '短信',
		
		6 => '学籍',
		7 => '身份证',
		8 => '聚信立',
		// 支付平台
		100 => '支付路由',
		101 => '易宝投资通',
		102 => '易宝一键支付',
	],
	
	// start 易宝wrap回调地址：
	'quickcallbackurl' => 'http://open.xianhuahua.com/yeepayback/quickcallurl', // 后台异步
	// end 易宝wrap回调地址
	
	// start 投资通：
	'tztpaycallbackurl' => 'http://open.xianhuahua.com/yeepayback/tztcallurl', // 后台异步
	// end 投资通	
	
	// start 神州融接口参数 *****************/
	// 学历类型 数字
	'studystyle' => [
			1 => '普通',
			2 => '研究生',
			5 => '成人 ',
			6 => '自考',
			7 => '网络教育',
			8 => '开放教育传数字',
	],
		
	// 学历 串
	'educationdegree' => [
		4 => '专科', 
		3 => '本科', 
		2 => '硕士',
		1 => '博士',
	],
	// end 神州融接口参数 *****************/
	
	'trideskey' => '579BEFGINPQUVZehilprstxy',
	'payroute' => [
		'1' => [101,102,104],
		'4' => [101,102],
	],
    'des3key'=>'p4w1k3Y0MzPvRRU3aDAeN8G4k1zXSy4V',
];

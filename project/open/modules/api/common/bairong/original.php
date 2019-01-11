<?php
	require_once("config.php");
	require_once("com.bairong.api.class.php");

	//var_dump($CN_2_EN_EC_FR_CLASS);
	//echo $KEY_WORD['name'];
	//if(is_array($CN_2_EN_EC_FR_CLASS)){
	//	echo $CN_2_EN_EC_FR_CLASS["数码"];
	//}

	//var_dump($MODEL[16]);



# 1: '身份信息核查', 2: '稳定性评估', 3: '商品消费', 4: '企业主/高管标示', 5: '媒体阅览', 6: '资产评估', 7: '地址信息', 8: '品牌兴趣评估', 9:'评分'
# 10: '信贷版商品消费', 11: '信贷版媒体阅览12': '收支等级', 13:'申请记录', 14: '特殊名单', 15: '支付消费', 16: '信贷版特殊名单', 17: '手机号有效性',
# 18: '新收支等级（按月）', 19: '信贷版稳定性评估'}

/*
{1: 'Authentication', 2: 'Stability', 3: 'Consumption', 4: 'Title', 5: 'Media', 6: 'Assets', 7: 'Location', 8: 'Brand', 9:'Score',
            10: 'Consumption_c', 11: 'Media_c', 12: 'Accountchange', 13:'ApplyLoan', 14: 'SpecialList', 15: 'PayConsumption', 16: 'SpecialList_c', 17: 'TelecomCheck',
            18: 'AccountchangeMonth', 19: 'Stability_c'}

*/

$headerTitle = array(
	//"haina" => array(
//		"BankFour"
//	),
//	//单独调用的模块（您自行填写）
	'huaxiang' => array(
		"SpecialList_c"
	) 
	//打包调用模块（您自行填写，建议先调用特殊名单，然后再调用其他模块）
);
	


/*

*/
$targetList = array(
array(
		//"line_num" => "000001",
		"name" => "阿斯",
		"id" => "310224196209243110",  //
		"cell" => "15921188518",   //
		"mail" => "000000@qq.com",
		"bank_id" => "4367421216244199784"

		/*"af_swift_number" => "1d8d45841aa3c2829ecaf34b00000151a58a28e2",
		"event" => "login",
		"apply_source" => "设备",
		"device_type" => "ios",
		"divice_id" => "ios",
		"user_date" => "2015-12",
        "tel_biz" => "021-68743263",
        "home_addr" => "上海市浦东新区下南路115弄1幢6号1101室",
		"biz_addr" => "上海市浦东新区下南路115弄1幢6号1101室",
		"per_addr" => "上海市浦东新区下南路115弄1幢6号1101室",
		"apply_addr" => "上海市浦东新区下南路115弄1幢6号1101室",
		"oth_addr" => "上海市浦东新区下南路115弄1幢6号1101室",
        "bank_id" => "6217001211017017888",
		"imei" => "74785nvdfn",
		"imsi" => "74785nvdfn",
		"mobile_type" => "vivo",
		"sex" => "女",
		"age" => "24",
		"educationallevel" => "本科",
        "marriage" => "未婚",
		  "income" => "5",
		  "biz_positon" => "职员",
		  "biz_workfor" => "百融",
		   "biz_regnum" => "2522222",
		  "upstreamCount" => "1",
          "downstreamCount" => "2",
		  "searchKey" => "雷军,小米科技有限公司",
		  "keyNo" => "3d9a2d02379f9ae6d16db49c3472f286",
		  "biz_type" => "私企",
		  "biz_industry" => "互联网",
		  "house_type" => "互联网",
		 "IP" => "192.168.162.111",
		  "longitude" => "192.168.162.111",
		  "latitude" => "192.168.162.111",
		  "MAC" => "192.168.162.111",
		  "user_id" => "192.168.162.111",
		  "user_name" => "去分期",
		  "user_nickname" => "去分期",




		   "postalcode" => "156208",
		   "apply_product" => "156208",
		   "apply_money" => "156208",
		   "apply_time" => "2016-01-01",
		   "loan_reason" => "缺钱",
		   "refund_periods" => "12",
		   "linkman_name" => "张三",
		   "linkman_cell" => "15718837809",
		   "linkman_rela" => "同学",
		   "app_visit_num" => "11",
		   "edu_att_num" => "11",
		   "bank_running_att_num" => "11",*/
),
//array(
		//"line_num" => "000002",
		//"name" => "\u767e\u878d\u91d1\u670d",
		//"name" => "\u767e",
//		"name" => "百融金服",
//		"id" => "310224196209243112",  //
//		"cell" => "15921188519",   //
//		"mail" => "000001@qq.com",
//		"bank_id" => "4367421216244199784"
//)
);

CONFIG::init();

$core = Core::getInstance(CONFIG::$account,CONFIG::$password,CONFIG::$apicode,CONFIG::$querys);

$core -> pushTargetList($targetList);
$core -> mapping($headerTitle);


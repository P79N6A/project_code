<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/com.bairong.api.class.php";

# 1: '身份信息核查', 2: '稳定性评估', 3: '商品消费', 4: '企业主/高管标示', 5: '媒体阅览', 6: '资产评估', 7: '地址信息', 8: '品牌兴趣评估', 9:'评分'
# 10: '信贷版商品消费', 11: '信贷版媒体阅览12': '收支等级', 13:'申请记录', 14: '特殊名单', 15: '支付消费', 16: '信贷版特殊名单', 17: '手机号有效性',
# 18: '新收支等级（按月）', 19: '信贷版稳定性评估'}

/*
{1: 'Authentication', 2: 'Stability', 3: 'Consumption', 4: 'Title', 5: 'Media', 6: 'Assets', 7: 'Location', 8: 'Brand', 9:'Score',
10: 'Consumption_c', 11: 'Media_c', 12: 'Accountchange', 13:'ApplyLoan', 14: 'SpecialList', 15: 'PayConsumption', 16: 'SpecialList_c', 17: 'TelecomCheck',
18: 'AccountchangeMonth', 19: 'Stability_c'}
 */

$headerTitle = [
	'huaxiang' => [
		"SpecialList_c", //信贷版特殊名单
		"ApplyLoan", // 多次申请核查
	],
];

$targetList = [

	[
		"name" => "阿斯",
		"id" => "310224196209243110", //
		"cell" => "15921188518", //
	],

	[
		"name" => "百融金服",
		"id" => "310224196209243112",
		"cell" => "15921188519",
	],


];

CONFIG::init();

$core = Core::getInstance(CONFIG::$account, CONFIG::$password, CONFIG::$apicode, CONFIG::$querys);

$core->pushTargetList($targetList);
$data = $core->mapping($headerTitle);
print_r($data);
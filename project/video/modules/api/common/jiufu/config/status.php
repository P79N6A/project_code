<?php
/**
 * 出款状态
 */
return [
	// 成功状态码
	'STATUS_SUCCESS' => [
		'F0243', //放款成功
	],
	// 失败状态码
	'STATUS_FAILURE' => [
		'F0242',//放款异常
		'F0208',//审核退回
		'F0211',//综合审批退回         ,
		'F0213',//审批退回
		'F0218',//稽核退回                                                                                                                                                                                         
	],
];
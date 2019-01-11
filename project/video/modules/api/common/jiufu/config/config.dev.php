<?php
// 测试账号
return [
	'rsa_encrypt' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7ud1VQqGDNF3MOTbzVGdaE8sekrkiz3ChfBfygvvfDb2ijSDeLRzFzVPTJa2pvkLNFuWpyJrArA1l2mMD1SxkHaWFZhUa4SsYnFE8pvKg5SGfesSRhhZFgZhkMoUvzZ/bXpR/O9hColCeNKbk9oqNW8dUOpoccwlVHDVdy3RZEQIDAQAB',

	'rsa_decrypt' => 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAKgAG7/hqF7XbkvVa4PayoNJrrnU5pw21jkNlPbdHniNL50T+4ZxjGklGtdBHp0j/V2RgMNvXlBElnWDlUMAsQ1dgk3U2M9H7FmV2RAMqFjXOz93DW3i1ugze0gr28dkzFe8Ht37IrMDy3FG0NHc+OocnWRWHUp7mv/CxNQ/TSMDAgMBAAECgYBGoB0KUnQ0wr4kdSkIuk7OWIhyqPT1kPwH6hTInvZzWW89yqu/vjZ38VhSS5byGrIlxshp4IS2m87gwhtamozTToiS/aQvejlOSIapyZutkMOHlyjPBOAFPYLk5kun+NzFJ/Ql2uWmCY+POYiE4MJBo8WnD7DHwuLpKAka2f4l8QJBAP5y0AolAVCSWs8+nJ/ZWtyRrgbEY+j+N+hMVyYmrqB34t2zY+1+XcbLTMMH8DszqARJfWrbexvsDBuX1LL1tmkCQQCpBlpEW+lpGsAHiQtYKsHDiGu3cEugOeFjDYrwrx6dr/2N/Jbp05BoKjybkwDb+TbtCyX4aBHZzzGx/TOz11iLAkBPfZrUsH7apv5LpGnV3ldudOyDHLOBxHm+zqqjNo5zf0CWtkZPmZy+UCDpBP/d3uNsg3D1AyBQtsuJi0NdrTmRAkBK2ZNTvlgIwV3UeG3bp2OTEXCSFVqII9mZob+rggFO10azf+3csmG6nymjw1+YCi62nj88V+m/yK87IOOqemytAkEAxLYnOTPjT563NahT7wat3EF8x41Fynlo2DEYPSkuzqv8FQqCxMU2FDZ0zuO/w+pd2bv/8g4ZHfAS7YZCrcsBXQ==',

	//1. soap: oss
	'oss_url' => 'http://123.57.48.237:7006/webservice/ossWebService?wsdl',

	/**
	 * 2. soap: 借款工单
	 * 3. soap: 生成合同
	 * 5. soap: 请求签章
	 */
	'loan_url' => 'http://123.57.48.237:7082/webservice/loanService?wsdl',

	// 4. rsa: 查询合同接口
	// 测试
	'query_contract_url' => 'http://123.57.48.237:7082/httpIntf/contractIntf/contract/queryContract.intf',

	// 6. rsa: 确认电子签章
	// 测试
	'get_seal_common_url' => 'http://123.57.48.237:7086/micro_kh/SealController/getSealCommon',

	// 7. rsa: 订单查询接口
	'loan_query_url' => 'http://123.57.48.237:7082/httpIntf/record/queryLoan.intf',

	//8. rsa: 结束订单接口
	'loan_end_url' => 'http://123.57.48.237:7082/httpIntf/record/endLoan.intf',

	//8. rsa: 订单支付结果查询
	//'query_pay_url' => 'http://123.57.48.237:7082/httpIntf/financial/notice!queryDelegatePaingRes.intf',
	'query_pay_url' => 'http://182.92.108.185:7009/intf/external/trans!server.intf',

	'local_stdkey_url' => 'http://127.0.0.1:8200/Service/ServiceHello?wsdl',
];

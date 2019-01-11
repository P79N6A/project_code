<?php
// 生产账号
return [
	'rsa_encrypt' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7ud1VQqGDNF3MOTbzVGdaE8sekrkiz3ChfBfygvvfDb2ijSDeLRzFzVPTJa2pvkLNFuWpyJrArA1l2mMD1SxkHaWFZhUa4SsYnFE8pvKg5SGfesSRhhZFgZhkMoUvzZ/bXpR/O9hColCeNKbk9oqNW8dUOpoccwlVHDVdy3RZEQIDAQAB',

	'rsa_decrypt' => 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAIfxPRyMtODTtVsrjQafSVsACW9ljUW/mRj1GjznGj904ElELOd+ITXVw/QiuXufobuiIWQvQPsUbA6LOHTnEsBKCuVRWe5okqVFQqQxaFAHYnAKWKuEhxQPlFCnKYgLQJEdjxgKVFBUHG6s33WO9UHCeeabK5f0VMuRVvcgcnq3AgMBAAECgYBpjdg8vciItfogg21qUe48eYfZ9kk+einfJhmsZmmMEi1A1m3jhZ011vjjLL9HDUkgjyBMUSCslEve8xzwMKfudtcU525eCubc+MzkJ48qyoBF8HPJoHxRWoRkaCdHGwOXXX2AKEPacR2NPlf70B0WT5s5IWQvW2R0wVqp+1q8EQJBAPlPBYbIZkgrP0Vlj2V0uSgjrOCHqEFlkwQ7lzSf0UMs2cubkoqeKOhQTBLOTySKyH4BWF7U9BN8Tz1KN8N2XP8CQQCLl0lrYVWxIQ3VCE5CvwLb3oRLnpWFHVG9sCd+D1mCiIxltnW/rrwviLYQVsE6Aq/l4UbxGM2qdtFegGhr8ApJAkA6e+0h9zT3TR3km7SN6lndLrFJYsl3vepFHe2UrMEcbxMQjohL+FpEVUHjT36FZgEufgZLCM3RHGJCUHzQX53lAkEAhZ5aVCRGz5fRUsNxjmijBu4X+v6hJ1uqXAXbt8pfpxioM9CVI9fSIToe9MLmkW3zC/w5WR2h+PNldK07x15tqQJAWbwlQ6eplDrSGiiNW8R+ccB+xBFBAO6ZAmRCEmN58ecgRy70h5+AfvhQygZIrPudbZTIhVEoquvBLYmFwKqOIg==',

	//1. soap: oss
	'oss_url' => 'http://ecm.9fbank.com:9006/webservice/ossWebService?wsdl',

	/**
	 * 2. soap: 借款工单
	 * 3. soap: 生成合同
	 * 5. soap: 请求签章
	 */
	'loan_url' => 'http://credit.9fbank.com:9082/webservice/loanService?wsdl',

	// 4. rsa: 查询合同接口
	// 生产:
	'query_contract_url' => 'http://credit.9fbank.com:9082/httpIntf/contractIntf/contract/queryContract.intf',

	// 6. rsa: 确认电子签章
	// 生产
	'get_seal_common_url' => 'http://59.110.85.90:9086/micro_kh/SealController/getSealCommon',

	// 7. rsa: 订单查询接口
	'loan_query_url' => 'http://credit.9fbank.com:9082/httpIntf/record/queryLoan.intf',

	//8. rsa: 结束订单接口
	'loan_end_url' => 'http://credit.9fbank.com:9082/httpIntf/record/endLoan.intf',

	//9. rsa: 订单支付结果查询
	//'query_pay_url' => 'http://credit.9fbank.com:9082/httpIntf/financial/notice!queryDelegatePaingRes.intf',
	'query_pay_url' => 'http://credit.9fbank.com:9082/intf/external/trans!server.intf',

	'local_stdkey_url' => 'http://127.0.0.1:8200/Service/ServiceHello?wsdl',
];

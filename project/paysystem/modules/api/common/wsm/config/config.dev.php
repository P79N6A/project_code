<?php
// 测试账号
return [
	'mid' => '68', //商户号 由微神马提供
	'cpm' => '59', //产品名 由微神马提供
	'enkeys' => '8ttaymylpmjy7blj', //加密解密串，由微神马提供
	'shmyc' => 'c53010567f81ef85c97a197eb64161bd', //商户密钥串，由微神马提供
	'callbackurl' => 'http://paytest.xianhuahua.com/wsm/wsmback/notify', //异步通知回调url 微神马回调地址
	'queryUrl' => 'https://lt-orderquery.wsmtec.com/index.php', // 微神马---查询地址
	'sendUrl' => 'https://lt-aquarius.wsmtec.com/OLM/index.php', //微神马---发送数据地址
	'afterTheLoan' => 'http://yyytest.xianhuahua.com/new/notifyfund', //贷后通知地址
	'yiyiyuanEncode' => '24BEFILOPQRUVWXcdhntvwxy', //贷后加密串
	'agreement' => 'http://weixin.xianhuahua.com/new/agreeloan', //协议地址
];

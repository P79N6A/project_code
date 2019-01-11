<?php
// 生产账号
return [
	'mid' => '67', //商户号 由微神马提供
	'cpm' => '58', //产品名 由微神马提供
	'enkeys' => 'u0237g0ofpgch9vx', //加密解密串，由微神马提供
	'shmyc' => '83c37bec43b0da29cce4043e3df9a3ff', //商户密钥串，由微神马提供
	'callbackurl' => 'http://pay.xianhuahua.com/wsm/wsmback/notify', //异步通知回调url 微神马回调地址
	'queryUrl' => 'https://orderquery.wsmtec.com/index.php', // 微神马---查询地址
	'sendUrl' => 'https://aquarius.wsmtec.com/OLM/index.php', //微神马---发送数据地址
	'afterTheLoan' => 'http://weixin.xianhuahua.com/new/notifyfund', //贷后通知地址
	'yiyiyuanEncode' => '24BEFILOPQRUVWXcdhntvwxy', //贷后加密串
	'agreement' => 'http://weixin.xianhuahua.com/new/agreeloan', //协议地址
];

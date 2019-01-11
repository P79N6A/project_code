<?php
// 一键支付接口文档  易宝一亿元回款（逾期-键支付）

// 商户编号
$merchantaccount = '10015471751';


// 商户私钥
$merchantPrivateKey = 'MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAJZws5dNuMyR01P6K/SkjXOjG0A7xjzbEwt3YzWiKvPX3pahV7xUqxx8GtabawbwEKaHI5+Sz+8ARz3PucOcHfm2630fjBAGB5Mm4uibZZNGs3Eoc7QVe60g5fOkuqr4x8SjABoiVkGLdy/EKSrpgpTJB5dFIaiuQzJBtG9SWmy9AgMBAAECgYAcyki0Rf19uNKWmaPb17nyTV6jtkzDzLEiWqCz3OsXa1J/xTTDJ/jvJJkGRQwAceTd2bLpkPEWLhl0LNLCKphZqtB+ID9S9vWksXbarvGscrVTGHj6aCAyeXkb39pmxRLf6REj+D+xZ7/OwRkawAuxdmbDHgVkQr9FHwdMg7gpQQJBAPPqB97KtFB6E8tFNQN/Ge1TLQCePjFwAbi1rWvrfl6n+clnkVR/S4rSlZcBLg62bK3/pf18t5QZf5eIiBdkr4UCQQCd5Pw9LJM48vMG9W1CfduFLv9ANBxYAsx670w0BwHUlvmX4/QuOrK85KSbyfnPcI9tHwRo548Vb+ADR3fN06HZAkEAuJuOrV76LlbXGGgfAbB3LRpg2zDpnX1KsERBJ4crM/UqpvcOFcfqov1TXuDzvQrxIph1R2/Xee36lfQuHJaGCQJBAJU3ZQWvDYcBWpkV8eanqICqFIGpfavTIUmAwqRchudQsppPzGCwCmCnN8Ue0J2xA2qdqH43b6pTqwGOVfNHcKkCQQDtNGd6w7+Ly57aAsZuLOD3GeN6poLRFlbZX4+InSH11wI8DGbenqdkB0gyFsax/A+w60XS35cv9jUSjrzsYdts';

// 商户公钥
$merchantPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCWcLOXTbjMkdNT+iv0pI1zoxtAO8Y82xMLd2M1oirz196WoVe8VKscfBrWm2sG8BCmhyOfks/vAEc9z7nDnB35tut9H4wQBgeTJuLom2WTRrNxKHO0FXutIOXzpLqq+MfEowAaIlZBi3cvxCkq6YKUyQeXRSGorkMyQbRvUlpsvQIDAQAB';

// 易宝公钥
$yeepayPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDP4vtfjmiMsElDGqd0ZeM5Ff0eXhDJ2sriaUhcujpufaviuF3ggsPMgKMILCcLkfxnCrLlb3dZl8NeJ/tUzOIM1J6VJCHNY+0ds1M4LzAtMfN9sh9uw/7ipfk050qHjje82rWD0bo5wKxjo+BMb0p8xJtr3TtBSiZzf8aTb2dv0wIDAQAB';
return [
	'merchantaccount'=>$merchantaccount,
	'merchantPublicKey'=>$merchantPublicKey,
	'merchantPrivateKey'=>$merchantPrivateKey,
	'yeepayPublicKey'=>$yeepayPublicKey,
];

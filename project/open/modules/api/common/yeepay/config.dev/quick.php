<?php
// 一键支付接口文档

// 商户编号
$merchantaccount = '10012537679';


// 商户私钥
$merchantPrivateKey = 'MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAMzxfODBoBDRrJrnOlU7/SgQLSbas2P8JSGgmbcyPW0wwca5Znff/L7cSVy0nHW/YrfiJQwzao1AgNoa6rH3cNRw9qz+Q1UfQvp7Ut/HZPHWWdgM1rjVaLQT2uEAZZKAWEWh3n767mxXn9s2d42yWmVoGLC17rv9jdCEydGtEyJ7AgMBAAECgYEAsWZDu2W/gW+N4lRPOSKBQ2GlQ+HBsMW8+nvDM0GozFCNG9C2cwOPC0Mxua6ZVI9DC3sUqJgFHpn8L09nYn+WyK9zL28UpIDWA5YtuwwvTzy4At6JRdDml2jSR9xIPkaDm5xfT2qT8khzz7SxFqkL30b8o03WqVxIi5PII5WVB/kCQQD7Uf0lccQjDHwl4q6NUnRayju9eqgx3HcQYauPT1f2WEjn3sVDHDclS9tnm/uRSn7nLvr9HOkgOsZCiD+TwhJfAkEA0MJtDtuPLrdrP605rfKBwrPKZms2ZUxKzpYSxKy/J4UrYh6J/Od2ke3LmBH0wftjiFh/2hZX5zia+80HbOb9ZQJBAJVkRlN0zf97k2y9077EDdBOOLbIa6TABbKiLGYS5xnTnvreDGp5Ijq0Xea37RGPs+HepmnBPr7e0S2Jail+CocCQQChWgUs/KqYcxAj8WGpfsyojoobyzYJ6YPQVNJAzTwZ8aXseqownT5Z4DACY66H2CPAGJcJG0fp4Sh5AqmAlLC5AkAlbyw8EeN8ApJ6Toy8lcIGZmwl/buDOJyzAauBLJP9WWGYUn9W12FfrGlcYWoo6fP2tPT7DZz0RgGbCdsIYYIl';

// 商户公钥
$merchantPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDM8XzgwaAQ0aya5zpVO/0oEC0m2rNj/CUhoJm3Mj1tMMHGuWZ33/y+3ElctJx1v2K34iUMM2qNQIDaGuqx93DUcPas/kNVH0L6e1Lfx2Tx1lnYDNa41Wi0E9rhAGWSgFhFod5++u5sV5/bNneNslplaBiwte67/Y3QhMnRrRMiewIDAQAB';

// 易宝公钥
$yeepayPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCkd+NQpKYuTcwNWA6CRfPuFJ0r+kOZTWhrtWEIA3RnicYOU3qxP0U1ucbz/HACpHFTFCfjP+zPfnMtFGTRorlYlQmLGNCx+32+dTQHVgcUjVw+IO/vrWykd+GhE3xAhsoE9Z92gRKYyEhoGBp95q7iuNqBMnH2Fcqxrloyq4T6iQIDAQAB';
return [
	'merchantaccount'=>$merchantaccount,
	'merchantPublicKey'=>$merchantPublicKey,
	'merchantPrivateKey'=>$merchantPrivateKey,
	'yeepayPublicKey'=>$yeepayPublicKey,
];

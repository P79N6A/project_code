<?php
// 一键支付接口文档  易宝一键支付(天津有信)

// 商户编号
$merchantaccount = '10015471751';


// 商户私钥
$merchantPrivateKey = 'MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAKiBM/UY5Ltmi2ocRCc+d9ofMs/wWlVKa4luko7QCnpZyXLFkfyWtXrEfQBjfOx4nYJ6bLBzW7i2LzfKj9n1uZHPfRaANaAWAIbeACgK5LUnlChIiwg2iMQ4Jabp2iS3xdBO1B8A+zhWdbDWaXBNWpY19drlPCrTyoGlN2lpKTkdAgMBAAECgYBD5LswmDUhJPIrcPQs88iKxGNO0UM0dmXZ3AmFLWHrZl36toxZv8ejjaPoEi31gavFNNqyRejBBfcEfcN0SMPZymdfBWI2gcPCJwELijZxvegFb4u+pnCCEbASe/ALO10ZZv6jFHUFmA/DHTZ2HRXOcnQXtg3nWJxW3A6JH21UgQJBAOBkH30aLgwF2W2h5BBmRPq+YbWKOJ5iusWAd8jP/T2E+ISVx6hEYECJpPZUIMtrmTomstBE4bQzLD6V7bm5mE0CQQDAPbunSSrDZhOxIxValqzwQ7OJtEJW/Q/YL9ZESOMsiW4O1+Lc7jNhZHGpMhJgctdxZsf3cH7T0eUmq6usvIwRAkBE6ZreEc84dAdtav2ep7nhg9yAI132Dn4rr6OZ8X5liVPFbDZwD+e7iko2OGoF7xqUyFO8MJtceybIZcUnd781AkEAhSb9IAxkqzy7rPig5MLye+RYqauKO5hCbjoMDfXyK7nw2iUcBGyUeAPLWibNZbKFmQ1YugFYRzzdnGBRo25hEQJAcyM0+I4Sek60tzGm9+ijP1s9yw25mMShGIpdYOP5NxEC1VInjZ4xLytsHmAztA8ibbbXKmia2lRzcanog6nkmw==';

// 商户公钥
$merchantPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCogTP1GOS7ZotqHEQnPnfaHzLP8FpVSmuJbpKO0Ap6WclyxZH8lrV6xH0AY3zseJ2Cemywc1u4ti83yo/Z9bmRz30WgDWgFgCG3gAoCuS1J5QoSIsINojEOCWm6dokt8XQTtQfAPs4VnWw1mlwTVqWNfXa5Twq08qBpTdpaSk5HQIDAQAB';

// 易宝公钥
$yeepayPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCsh37K9u+1FIgjQAc7E38sfO5EY23Ev7ULYEEW2eDkriKCXCxG6boKwmGpg5ty1vm57L1om0F3knwtCRC+jwbRznteBS2nkeWeleHjgYI8H5d/WdbbumcX+2u4yYGQOix9CXgB/hTfryqBck9l4UEJ0usx53lD1gGYrjR3R+V6xQIDAQAB';
return [
	'merchantaccount'=>$merchantaccount,
	'merchantPublicKey'=>$merchantPublicKey,
	'merchantPrivateKey'=>$merchantPrivateKey,
	'yeepayPublicKey'=>$yeepayPublicKey,
];

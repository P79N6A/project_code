<?php
// 投资通接口配置文档  易宝投资通(一亿元)

// 商户编号
$merchantaccount = '10014678148';


// 商户私钥
$merchantPrivateKey = 'MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAOD5nfH2YzYq2HEsZdscJC4NfSerm6Ap9haKsY3CG4xst7Y4fSC0tESjq0txop0BYgZS6a7LY6T79Enh3U2chsXZyGRfpAJjkBTEU5saKtWJ3YXxO0DgtwRb3xCQZreYXRjqJPXbToN8LMgeltWCWhZCimQmkm9fQDseCTE7cGSVAgMBAAECgYAauy4cMOVq6z5afCcCGN7npeyoCQjtx+6YkRQ1vsmdLtHJUf70IuSEf1n8Fd13gFGQZMulXD9TvCgzmyW7cgzFPmoakbT1dawBVE9Te/t34vWGT6J03dJm04ON0uc8jlwnp5RKk6j1RMoQhaP8PKmpAhXTy1nAfJBqDkqB9VnxZQJBAPGlL8sVz5H/1yjKrr93uWDxdEmKAE24sTfPwz2Gh0kFO3aoUN0QKL8AKoczHTt3DvLlJ1b7tqItZMm+hP4sBGsCQQDuVus1dnUCPIpDLWe5pSRuLUjhXQJdbkbZKEazZUkk4Ll2HmLIrA/KpGco2Jh3Fd1WdMAb6EH7ijhOrzKBt3r/AkEAtoDU9OQXLiR1Axj5HCC3QNF7y2LP0eNw7T8cLSainHK4M2jyEdP3gjIE7LGdHWFRR//sU1Su3hO8sGYVGcZy2wJBAKIZcZ2J9GjR/gNUdVB49f8NQ50rIfmjkAIP9435nDa9tMWWQv9SrubWy+am8YNE1qX/f807OO04g7VYSNSakscCQQCTOjjR8afCUUynYItDRH3PAlbySfb4A+riuAYfSRS1kycvNz234SCgN3oFNPhubR/BlPGXN+RcSYCWSyEKKhse';

// 商户公钥
$merchantPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDg+Z3x9mM2KthxLGXbHCQuDX0nq5ugKfYWirGNwhuMbLe2OH0gtLREo6tLcaKdAWIGUumuy2Ok+/RJ4d1NnIbF2chkX6QCY5AUxFObGirVid2F8TtA4LcEW98QkGa3mF0Y6iT1206DfCzIHpbVgloWQopkJpJvX0A7HgkxO3BklQIDAQAB';

// 易宝公钥
$yeepayPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCMZbSp8OOAYQNQLa38+A1XdpemoBxiGNHIefR4zkSWk6EX9+pVfa+pNrYrjHkI8j6ucfzV6zTeG3s1DJF601LnMsz/5BBzUM8zvLky/OO5Gi3Muxc6ROkMSoDjA3UgdIr6AZn8lU7IaG9kIk2NxP1+OWpun1Yj674Ax2Bjhp8CQQIDAQAB';
return [
	'merchantaccount'=>$merchantaccount,
	'merchantPublicKey'=>$merchantPublicKey,
	'merchantPrivateKey'=>$merchantPrivateKey,
	'yeepayPublicKey'=>$yeepayPublicKey,
];
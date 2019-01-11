<?php
namespace app\common;
/**
 * RSA算法类
 */
class RSALocal {
	private $rsa;
	public $privateKey;
	private $error;// 错误结果
	
	public function __construct(){
		$this->rsa = new RSA();

		$this->publicKey  = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDKGptPy5SEN2jU6LSLQNGUWJXnIaRq+FASq+USIv1bpH5vxu9VCjmOB7LNbqpwZFwRPlSmM6tX0d4W8X/x5rbUJFEHS7QZoAJd1HxQjyeT+03vfH9SVorN6WHgfX9XkyPdtqY3iaWYNOXW/xnduyvnKfuTQ7Cumq2zbO3HjSaZqwIDAQAB';
		$this->privateKey = 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMoam0/LlIQ3aNTotItA0ZRYlechpGr4UBKr5RIi/Vukfm/G71UKOY4Hss1uqnBkXBE+VKYzq1fR3hbxf/HmttQkUQdLtBmgAl3UfFCPJ5P7Te98f1JWis3pYeB9f1eTI922pjeJpZg05db/Gd27K+cp+5NDsK6arbNs7ceNJpmrAgMBAAECgYEAuHYgW2xMWYDZRbo/TvoST3urFhI2pwuMyf3qTetxozs3y32e49c5QND1+VqQZZS0E2j4idmPAdCjC/3P8VHKe6ZjdwvedXU7D/8EMp4sBqvYM6ceH+11brBBSwpc7xXd3U7yJpPXzotSTlChvZDwpx6OE6av0XthnFb8lqU2MMECQQDqU4nwcUNolZkKWZGU6wedmyMoIqb9qsxPn/SqfdAAh2Aj8E8ozYFJ6cr8KfD865BDL4bfhVSuxV3p7LqbGwhvAkEA3MwYQOiCqMBj/SrI9wYfdFW0z4s8z3znHCvf7YpxRYUGD4u8mJdM5ftscsJlQCi/viJJCo2DBy6pJ0aQhARIhQJAZdcm1TQ0qsiRufjRl9pJ9gqNzgy5bPgFUfnf+RUzCHfNpfD0RnSCY2BT0yJbVWD/0uNeB9lHw6mNtnQnae/mywJAbfgU6FclpGjWJCSoHShmiCmbuXbu3aSm8sgDaqr2SZq8bwe48gMBYNY9qFab2T2yaj9nQ6NBrFUYGKCzn50GhQJBAN2Do3OtghiebI5oZe77jJCXPF0Tdi3hI30yFdLRTmboNHXV3dWxA3vKh65M8UKV4nioIVherVlpM+MZlni9/JY=';
	}
	// 128位以下: 公钥加密，私钥解密
	public function encryptByPublic($str){
	    return $this->rsa->encryptByPublic($str,$this->publicKey);
	}
	public function decryptByPrivate($str){
		return $this->rsa->decrypt128ByPrivate($str,$this->privateKey);
	}
}
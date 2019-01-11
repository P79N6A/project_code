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

		$this->publicKey  = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDs4rozkQmAGJcXfkBrdaKr20dA6o0hbjZThlLKhp8Ar5gZYvfcQKr6xxYwDOsQZQNI7rXay3dVoAxeGDbs08kdDWi3zOfW+RFR9sza+L3GSoOuhJsm8lLIgDQ4T0TQbcWwmdQh/hoFJAtWrXgq59HKj2f09fCAw5+RHH9LFU+AnwIDAQAB";
		$this->privateKey = "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAOziujORCYAYlxd+QGt1oqvbR0DqjSFuNlOGUsqGnwCvmBli99xAqvrHFjAM6xBlA0jutdrLd1WgDF4YNuzTyR0NaLfM59b5EVH2zNr4vcZKg66EmybyUsiANDhPRNBtxbCZ1CH+GgUkC1ateCrn0cqPZ/T18IDDn5Ecf0sVT4CfAgMBAAECgYBqDdPpVEzZkajLdtWmhqMOp/yNnrUSu4INAP09+Olk6DiDXSC09irWXZ2cY5w12dOPqne2fhNuPVpsIhEtFGBcCd0lyZLhr+qnWmvrUKFJZlbaQdLs8PjD23iaR7JRZXd2AwD/ljbEHzTZ6R3oVWFd8QbsI/+fvmQVl19a1QMrIQJBAPwtKJ1pd8IcMZSLx9WcHsR3m5EutED3TNbqdfZymntUDBmL+P1cnKbHig4gLare0KXiFl3N9rEdobt73b9inLECQQDwejdulkI5KXciwFb8mL3WMVeMsagd6FUEJ6fEnPqO0r0lKHCOAM5ItuX8fA3tLdu/GC+oq7u+WQhPncDCxQZPAkEA+fbBZZcfsHdF5hrQULrZ/KEawURsRGFd90Kc/1cGLe1XuRL4Ehx04xSzkeDvo4oNhAChbwYz28ilgjP70DOtYQJBAN6uXcxCydoC4rZEY4iOrCO/FzJKhMIFFUy+p+Ux8/bzgID7HJbyehLtgrS173N05qri66cGN9kAuuh2zTvOlJMCQH+lPeNb1AMn8vscNXzjGPPm0pGGMXElxLDDIHM36KgLntVa8VLjxAk5hghNQ3E13iSCHk05PEdhlDSBSR09AIA=";
	}
	// 公钥加密，私钥解密
	public function encryptByPublic($str){
	    return $this->rsa->encryptByPublic($str,$this->publicKey);
	}
	public function decryptByPrivate($str){
		return $this->rsa->decrypt128ByPrivate($str,$this->privateKey);
	}
}
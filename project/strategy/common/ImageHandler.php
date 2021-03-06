<?php
namespace app\common;

use app\common\Crypt3Des;
use Yii;
class ImageHandler
{
	static $project='yiyiyuan';
	static $key = '013456GJLNVXZbdhijkmnprz';
	
	// 测试环境
	//static $img_domain='http://182.92.80.211:8081';
	//static $img_upload='http://test_upload.xianhuahua.com';
	
	// 生产环境
	static public $img_domain='http://img.xianhuahua.com';
	static public $img_upload='http://upload.xianhuahua.com';
	
	/**
	 * 加密链接地址
	 * @param int $uid 会员UID
	 * @param string $type 子目录,只能是小写字母目录
	 */
	public static function encryptKey($user_id, $type){
		$jsonStr = json_encode([
			'uid'=>$user_id,
			't'=>time(),
			'type'=>$type,
			'project'=>static::$project,
		]);
		//$str = $this->oRsa -> encryptByPublic($jsonStr);
		$str = Crypt3Des::encrypt($jsonStr, static::$key);
		
		return $str;
	}
	public static function getUrl( $url ){
		
		$prefix = 'upload';
		if( strpos($url, $prefix) === 0 ){
			return 'http://121.43.74.135/' . $url;
		}elseif( strpos($url, '/'.$prefix) === 0 ){
			return 'http://121.43.74.135' . $url;
		}else{
			return self::$img_domain.$url;
		}
	}
	/**
	 * 加密链接地址
	 */
	/*public static function decryptKey($encrypt){
		//1 解密操作
		if(!$encrypt){
			return null;
		}
		//$jsonStr = $this->oRsa -> decryptByPrivate($encrypt);
		$jsonStr = Crypt3Des::decrypt($encrypt, "013456GJLNVXZbdhijkmnprz");
		if(!$jsonStr){
			return null;
		}
		$data = json_decode($jsonStr,true);
		
		//2 检测数据是否合法
		if(empty($data) || !is_array($data) ){
			return null;
		}
		if(!isset($data['uid']) || !isset($data['t']) || !isset($data['id']) ){
			return null;
		}

		//3 检测超时与否 10分钟
		if( time() - $data['t'] > 600 ){
			return $this->returnError(null, "签名已经失效");
		}
		
		//4 返回结果
		return true;
	}*/
}
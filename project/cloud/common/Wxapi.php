<?php
/**
 * 商户贷API文档
 * 单例模式
 */
namespace app\common;
use Yii;

class Wxapi
{
	static $_instance=null;
	static $_appid = 'wx7f5617758332f49a';
	static $_appSecret = 'b06cf474410f41a217357956af0bc4cf';
	
	// 禁止外部实例化
	private function __construct(){
	}
	/**
	 * 单例模式实现
	 */
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	
	/**
	 * 获取token值, 默认缓存7000s
	 */
	public function getAccessToken(){
		//1 从缓存中获取accesstoken值
		$tokenkey = 'wx_accesstoken';
		$token = Yii::$app->cache -> get($tokenkey);
		
		//2 从微信接口中获取accesstoken值并设置到缓存中
		if( !$token ){
			$token = $this -> getNewAccessToken();
			Yii::$app->cache -> set($tokenkey, $token, 7000);
		}
		
		return $token;
	}
	/**
	 * 重新获取accesstoken
	 */
	public function getNewAccessToken(){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::$_appid."&secret=".self::$_appSecret;
		$data = Http::getCurl($url);//通过自定义函数getCurl得到https的内容
		$resultArr = json_decode($data, true);//转为数组
		$accessToken = is_array($resultArr) && isset($resultArr["access_token"]) ? $resultArr["access_token"] : '';
		return $accessToken;
	}
	
	
	//************ start menu **************//
	public function menuCreate(){
		$menuPath = Yii::$app ->basePath . '/config/wx_menu.json';
		$menu = file_get_contents($menuPath);
		$accessToken = $this->getAccessToken();
    	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$accessToken;//POST的url
    	$menu = Http::dataPost($menu, $url);//将菜单结构体POST给微信服务器
		return json_decode($menu, true);
	}
	public function menuDelete(){
		$accessToken = $this->getAccessToken();
    	$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$accessToken;//POST的url
    	$menu = Http::getCurl($url);//将菜单结构体POST给微信服务器
		return json_decode($menu, true);
	}
	//************ end menu **************//

	//************ start 登录授权相关 **************//
	// 第一步其实是菜单中的链接地址
	public function getWebAuth(){
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".self::$_appid."&redirect_uri=http://shanghu.xianhuahua.com/dev/wx/loan&response_type=code&scope=snsapi_userinfo&state=xhhmer#wechat_redirect";
		return $url; // 用浏览器打开即可
	}
	/**
	 * 第二步:获取openid
	 * @param $code
	 * @return [
	 * http://mp.weixin.qq.com/wiki/4/9ac2e7b1f1d22e9e57260f6553822520.html
	 * 中第二步
	 * ]
	 */
	public function getWebToken($code){
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".self::$_appid."&secret=".self::$_appSecret."&code=".$code."&grant_type=authorization_code";
        $data = Http::getCurl($url);
        $resultArr = json_decode($data, true); //转为数组
        if ( is_array($resultArr) && isset($resultArr['openid']) && $resultArr['openid']) {
			return $resultArr;
		}else{
			return null;
		}
	}	
	/**
	 * 第三步:获取openid
	 * @param $code
	 * @return [
	 * http://mp.weixin.qq.com/wiki/4/9ac2e7b1f1d22e9e57260f6553822520.html
	 * 中第三步
	 * ]
	 */
	public function getWebRefresh( $ret ){
		$url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".self::$_appid."&grant_type=refresh_token&refresh_token=".self::$_appSecret;
		$data = Http::getCurl($url);
		$resultArr = json_decode($data, true);//转为数组
		return $resultArr ;
	}
	/**
	 * 第四步: 获取用户信息（在用户关注公众号以后）
	 * @param $web_access_token 此token是第二步生成的
	 * @param $open_id
	 * @return [
	 * http://mp.weixin.qq.com/wiki/4/9ac2e7b1f1d22e9e57260f6553822520.html
	 * 中第四步
	 * ]
	 */
	public function getWebUser( $web_access_token, $open_id ){
		$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$web_access_token.'&openid='.$open_id.'&lang=zh_CN';
		$data = Http::getCurl($url);
		$resultArr = json_decode($data, true);//转为数组
		return $resultArr ;
	}
	//************ end 登录授权相关 **************//

	/**
	 * 获取用户信息（在用户关注公众号以后）
	 */
	public function getUserinfo( $access_token, $open_id ){
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$open_id.'&lang=zh_CN';
		$data = Http::getCurl($url);
		$resultArr = json_decode($data, true);//转为数组
		return $resultArr ;
	}
}
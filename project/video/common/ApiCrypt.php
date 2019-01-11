<?php
namespace app\common;
use Yii;
use app\common\Crypt3Des;

class ApiCrypt
{
	/**
	 * 加密数据与签名信息
	 */
	public function buildData( $postData, $auth_key ){
		if( !is_array($postData) ){
			return null;
		}
		$sign = $this->sign($postData, $auth_key);
		$postData['_sign'] = $sign;
		$jsonStr = json_encode($postData);
		return $this->encrypt($jsonStr, $auth_key);
	}
	/**
	 * 解密数据与验证签名信息
	 */
	public function parseData( $postData, $auth_key ){
		$postData = $this->decrypt($postData, $auth_key);
		$postData = json_decode($postData, true);
		if( !is_array($postData) ){
			return ['res_code'=>11,'res_data'=>'数据为空'];
		}
		if( !isset($postData['_sign']) ){
			return ['res_code'=>12,'res_data'=>'找不到签名信息，非法操作'];
		}
		$res = $this->verify($postData, $auth_key);
		if( $res ){
			unset($postData['_sign']);
			return ['res_code'=>0,'res_data' => $postData];
		}else{
			return ['res_code'=>13,'res_data' => '签名不正确'];
		}
	}
	/**
	 * 加密
	 */
	public function encrypt($postData, $key){
		return Crypt3Des::encrypt($postData, $key);
	}
	/**
	 * 解密
	 */
	public function decrypt($postData, $key){
		return Crypt3Des::decrypt($postData, $key);
	}
	
	/**
	 * 校验签名信息
	 */
	public function verify($postData, $key){
		//1 验证数据是否合法
		if( !is_array($postData) ){
			return false;
		}
		
		if( !isset($postData['_sign']) ){
			return false;
		}
		
		//2 数据暂存
		$_sign = $postData['_sign'];
		unset($postData['_sign']);
		
		//3 组合签名字符串
		$sign = $this->sign($postData, $key);
		if( $_sign != $sign ){
			return false;
		}

		return true;
	}
	/**
	 * 将一个数组创建签名
	 */
	public function sign($postData, $key){
		//1 验证数据是否合法
		if( !is_array($postData) ){
			return null;
		}

		//2 组合签名字符串
		$str = $this->getOrderStr($postData);
		$sign= $this->getSign( $str, $key );
		
		//3  验证签名是否正确
		return $sign;
	}
	/**
	 * 对一个字符串进行验名方法
	 */
	private function getSign($str, $key){
        $md5 = md5(md5($str).$key);
		return $md5;
	}
		
	/**
	 * 提交的数据转换成字符串
	 * @param $postData 默认必须含sign函数
	 */
	private function getOrderStr($postData){
		if( !is_array($postData) ){
			return null;
		}
		ksort ($postData);// 按字母正序排序
		$pieces = array_values($postData);
		//print_r($pieces);
		return implode('', $pieces);
	}
}
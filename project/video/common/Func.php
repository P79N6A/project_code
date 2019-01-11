<?php
namespace app\common;
use Yii;

class Func
{
	//建立文件夹，并且可以选择是否建立默认的index.html文件
	public static function makedir($param) {
		if(!file_exists($param)) {
			self::makedir(dirname($param));
			mkdir($param);
		}
	}
	
	/**
	 * 验证是否是有效并且不为空的数组
	 */
	public static function valid_array( &$arr ){
		if( !is_array($arr) || empty($arr) ){
			return false;
		}else{
			return true;
		}
	}
	/**
	 * 获取客户端IP地址
	 *
	 * @return ip
	 */
	public static function get_client_ip(){
	   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	       $ip = getenv("HTTP_CLIENT_IP");
	   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	       $ip = getenv("HTTP_X_FORWARDED_FOR");
	   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	       $ip = getenv("REMOTE_ADDR");
	   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	       $ip = $_SERVER['REMOTE_ADDR'];
	   else
	       $ip = "";
	   return($ip);
	}
	/**
	 * 取出查询结果集里面的id
	 *
	 * @param array $rows
	 * @param str $id
	 * @return array | null
	 */
	public static function onlyids( &$rows, $id = 'aid',$trimempty=false){
		if( empty($rows) ){
			return null;
		}
		$ids = array();
		foreach ( $rows as $row ){
			if( $trimempty ){
				if( intval($row[$id]) > 0){
					$ids[] = $row[$id];
				}
			}else{
				$ids[] = $row[$id];
			}
		}
		return $ids;
	}
	/**
	 * 将提交的id转换成纯数字的id:可能有两种形式一种是数组，一种是字符串
	 *
	 * @param str | array $c, 字符串要求是','分隔
	 */
	public static function toIds( $ids ){
		if( !is_array($ids) ){
			$ids = explode( ',', $ids );
		}
		foreach ( $ids as $k=>$id ){
			if( !is_numeric( $id )){
				unset( $ids[$k] );
			}
		}
		return $ids;
	}
	/**
	 * 两个数组根据某键合并在一起
	 *
	 * @param array $rows1 引用传值，避免复制
	 * @param array $rows2 引用传值，避免复制
	 * @param str $id 两数组关联键
	 * @param bool $once 仅匹配一次
	 * @return array
	 */
	public static function appends( &$rows1, &$rows2, $id = 'id', $once = true){
		if( !is_array($rows1) || empty( $rows1 ) ){
			return null;
		}
		if( !is_array($rows2) || empty( $rows2 ) ){
			return $rows1;
		}
		foreach ( $rows1 as &$row1){
			foreach ( $rows2 as $k2=>$row2){
				if($row1[$id] == $row2[$id]){
					$row1 = array_merge($row1,$row2);
					if( $once ){
						unset( $rows2[$k2] );
					}
				}
			}
		}
		return $rows1;
	}
	/**
	 * 给一个数组中的元素两边加上单引号
	 */
	public static function addQuote( $array, $n = "'" ){
	    if( is_array($array) ){
	        $str = implode("{$n},{$n}", $array);
			return "{$n}".$str."{$n}";
	    }else{
	        return $array;
	    }
	}
	public static function toMap(&$data,$key){
		if(!is_array($data)){
			return null;
		}
		$map=array();
		foreach($data as $v){
			$map[$v[$key]] = $v;
		}
		return $map;
	}
	/**
	 * 按键正序拼接数组元素成一个串
	 */
	public static function joinSortArr($postData){
		if( !is_array($postData) ){
			return '';
		}
		ksort ($postData);// 按字母正序排序
		$pieces = array_values($postData);
		//print_r($pieces);
		return implode('', $pieces);
	}
	/**
	 * 随机数字与字母组合
	 */
	public static function randStr($num){
		$num = intval($num);
		$data = array ( 0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9', 10 => 'A', 11 => 'B', 12 => 'C', 13 => 'D', 14 => 'E', 15 => 'F', 16 => 'G', 17 => 'H', 18 => 'I', 19 => 'J', 20 => 'K', 21 => 'L', 22 => 'M', 23 => 'N', 24 => 'O', 25 => 'P', 26 => 'Q', 27 => 'R', 28 => 'S', 29 => 'T', 30 => 'U', 31 => 'V', 32 => 'W', 33 => 'X', 34 => 'Y', 35 => 'Z', 36 => 'a', 37 => 'b', 38 => 'c', 39 => 'd', 40 => 'e', 41 => 'f', 42 => 'g', 43 => 'h', 44 => 'i', 45 => 'j', 46 => 'k', 47 => 'l', 48 => 'm', 49 => 'n', 50 => 'o', 51 => 'p', 52 => 'q', 53 => 'r', 54 => 's', 55 => 't', 56 => 'u', 57 => 'v', 58 => 'w', 59 => 'x', 60 => 'y', 61 => 'z', );
		$randKeys = array_rand($data, $num);
		
		// 获取随机值
		$randValues = [];
		foreach($randKeys as $key){
			$randValues[] = $data[$key];
		}
        shuffle($randValues);
		return implode('',$randValues);
	}
	/**
	 * 打印格式良好的数据
	 */
	public static function print_test($v){
		echo '<pre>';
		var_export($v);
		echo '</pre>';
	}
	public static function object_to_array( $obj ){
		$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
		foreach ($_arr as $key => $val){
			$val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
			$arr[$key] = $val;
		}
		return $arr;
	}
	public static function array_to_object( $obj ){
		if( !is_array($obj) ){
			return null;
		}
		return new ArrayObject( $obj );
	}
	/**
	 * 是否全由汉字组成 utf8
	 */
	public static function isAllChinese( $str ){
	    if(  !preg_match ("/[^\x80-\xff]/i",$str)   ){ 
	        return true;
	    }else{ 
	        return false;
	    } 
	}
	/**
	 * 去除空格
	 *
	 * @param str | array $string
	 * @return 同输入
	 */
	public static function new_trim($string){
		if(!is_array($string)) return trim($string);
		foreach($string as $key => $val){ 
			$string[$key] = self::new_trim($val); 
		}
		return $string;
	}
	
	/////////////////////// 易宝编号处理 ////////////////////////
	/**
	 * 此处理的目的是为了保证与易宝一一对应上
	 * 涉及到的字段包含requestid,orderid,identityid
	 */
	/**
	 * 转换成易宝的前缀形式, 数据库保存也是这种形式
	 */
	public static function toYeepayCode($id, $aid){
		$aid = trim($aid);
		$id = trim($id);
		if( !$aid || !$id ){
			return '';
		}
		return $aid.'_'.$id;
	}
	/**
	 * 去除aid前缀形式,传给客户端
	 */
	public static function toClientCode($id, $aid){
		$aid = trim($aid);
		$id = trim($id);
		if( !$aid || !$id ){
			return '';
		}
		return preg_replace("/^{$aid}_/", '', $id, 1);
	}
	/**
	 * 隐藏
	 */
	public static function strProtected($name){
		$len = mb_strlen($name,"UTF-8");
		if( $len < 2 ){
			return $name;
		}
		$lastName = mb_substr($name,-1,1,"UTF-8");
		
		$padLen = $len-1;
		if($padLen){
			$str = str_pad($input, $padLen, "*", STR_PAD_LEFT);
		}else{
			$str = '';
		}
		return $str.$lastName;
		
	}
	/////////////////////// 易宝编号处理 ////////////////////////
}
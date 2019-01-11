<?php
header("Content-Type: text/html; charset=UTF-8");
class Util {
	/**
	 * desc : csv文件表头字段
	 * $data : Array
	 **/
	public static $title = array();

	public static function post($url, $data, $timeout = 30) {
		$ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
		$ch = curl_init();
		$opt = array(
			CURLOPT_URL => $url,
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_POSTFIELDS => http_build_query($data),
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $timeout,
		);
		if ($ssl) {
			$opt[CURLOPT_SSL_VERIFYHOST] = 2;
			$opt[CURLOPT_SSL_VERIFYPEER] = FALSE;
		}
		curl_setopt_array($ch, $opt);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public static function getTitle($arr) {
		if (is_array($arr)) {

			foreach ($arr as $key => $val) {
				if (is_array($val)) {}
			}
		}
	}

	public static function array_remove($data, $key) {
		if (!array_key_exists($key, $data)) {
			return $data;
		}
		$keys = array_keys($data);
		$index = array_search($key, $keys);
		if ($index !== FALSE) {
			array_splice($data, $index, 1);
		}
		return $data;
	}

}

class Core {
	private $username;
	private $password;
	private $apicode;
	private $br_login_url;
	private $tokenid;
	private $ctitle = array();

	public $br_data_url;
	public $isLogin = false; //是否登录
	public $res_login;
	public $userList; //列表
	private $pass = true; //是否含有查询的必填字段 name,cell,id

	private static $_instance;

	function __construct($username, $password, $apicode, $querys, $br_login_url = 'https://api.100credit.cn/bankServer2/user/login.action') {
		$this->username = $username;
		$this->password = $password;
		$this->apicode = $apicode;

		$this->br_login_url = $br_login_url;
		$this->querys = $querys;

		$this->login();
	}

	public static function getInstance($username, $password, $apicode, $querys) {
		//对象方法不能访问普通的对象属性，所以$_instance需要设为静态的
		if (self::$_instance === null) {
			//                self::$_instance=new SqlHelper();//方式一
			self::$_instance = new self($username, $password, $apicode, $querys); //方式二
		}
		return self::$_instance;
	}

	public function login() {
		$postData = array(
			"userName" => $this->username,
			"password" => $this->password,
			"apiCode" => $this->apicode,
		);

		//echo $this -> br_login_url."<br />";

		$this->res_login = Util::post($this->br_login_url, $postData);

		if (CONFIG::isDebug()) {
			echo '<h3>登录结果</h3>';
			echo $this->res_login;
		}

		if ($this->res_login) {

			$loginData = json_decode($this->res_login, true);
			//var_dump($loginData);
			if ($loginData['code'] == 0) {
				$this->isLogin = true;
				$this->tokenid = $loginData['tokenid']; //取得tokenid
			} else {
				$this->isLogin = false;
			}

		}
	}

	function mapping($headerTitle) {
		if (!($this->pass)) {return;}

		//正式环境套餐字段固定
		if (STATUS == 1) {
			//$this -> headerTitle = $headerTitle;
		}

		$arr_querys = $this->querys;

		$result = [];
		if (is_array($headerTitle)) {
			foreach ($headerTitle as $key => $arr) {
				$result[$key] = $this->query($key, $arr_querys[$key], $arr);
			}
		}
		return $result;
		//var_dump($this -> querys);
		//$this -> query();
	}

	//查询数据接口
	function query($filename, $url, $titles) {
		//未登录先登录
		if (!$this->res_login) {
			$this->login();
			return;
		}

		$arr = array(); //查询结果
		$arr2 = array();
		$arr_pre1 = array(); //存储查询参数
		$arr_pre2 = array(); //存储默认flag

		$tid = $this->tokenid;
		$apicode = $this->apicode;
		//$url = $this -> br_data_url;
		$url = $url;
		$data = $this->userList;

		$headKey = array();

		if (STATUS == 2) {
			$meal = '';
		} else {
			//$meal = join(',',$this -> headerTitle);
			$meal = join(',', $titles);
		}

		$reserveTitle = array(
			'code',
			'swift_number',
			//'Flag'
		);

		//参数字段需显示在csv 文件中

		foreach ($data as $key => $value) {
			if (STATUS == 2) {
				$line_num = $value['line_num'];
			}

			foreach ($value as $key1 => $value1) {
				if ($key1 == 'name') {
					$data[$key][$key1] = $this->replaceLang($value1);
				}

				if ($key1 == 'mail') {
					if ($filename == 'huaxiang') {
						$data[$key][$key1] = array($value1);
					} else {
						$data[$key][$key1] = $value1;
					}

				}

				if ($key1 == 'cell') {
					if ($filename == 'huaxiang') {
						$data[$key][$key1] = array($value1);
					} else {
						$data[$key][$key1] = $value1;
					}
				}
				if (STATUS == 2 && preg_match('/meal/', $key1)) {
					$mealArr = explode('|', $value1);
					$meal = join(',', $mealArr);
				}
			}

			$data[$key]['meal'] = $meal;

			$postData = array(
				'tokenid' => $tid,
				'interCommand' => '1000',
				'apiCode' => $apicode,
				'jsonData' => json_encode($data[$key]),
				'checkCode' => md5(json_encode($data[$key]) . md5($apicode . $tid)),
			);

			//查询返回值
			//json string 格式
			$temp_res = Util::post($url, $postData);

			$temp_res_arr = json_decode($temp_res, true);

			//重新登录
			if ($temp_res_arr['code'] == 100004) {
				$this->login();
				$this->mapping();
				return;
			}

			if (CONFIG::isDebug()) {
				echo '<h3>post 参数</h3>';
				var_dump($postData);
				echo '<h3>post 返回值</h3>';
				echo ($temp_res);
			}

			if (STATUS == 1) {
				//加入csv默认需要显示的字段
				if ($filename == 'huaxiang') {
					$temp_res_arr = array_merge(CONFIG::defaultFlagTitle(), $temp_res_arr);
				}

				$this->ctitle = array_merge($value, CONFIG::fixSortTitle());
				$temp_res_arr = array_merge($value, $temp_res_arr);

				array_push($arr, $temp_res_arr);
			} else {
				//测试环境
				array_push($arr, $line_num . $temp_res);
			}

		}
		
		return $arr;
		/*
		if(STATUS == 2){
			$this -> createTxt($filename,$arr);
		}else{
			if($filename == 'huaxiang'){
				$this -> createCSV($filename,$arr);
			}else{
				$this -> createTxt($filename,$arr);
			}
		}*/
	}

	private function validator($arr) {

		foreach ($arr as $key => $value) {
			if (!($value['name'])) {
				$this->pass = false;
				echo '<h3>提示:name为必填字段</h3>';
				break;
			}
			if (!($value['cell'])) {
				$this->pass = false;
				echo '<h3>提示:cell为必填字段</h3>';
				break;
			}
			if (!($value['id'])) {
				$this->pass = false;
				echo '<h3>提示:id为必填字段</h3>';
				break;
			}
			if (isset($value['bank_id']) && !$value['bank_id']) {
				$this->pass = false;
				echo '<h3>提示:bank_id为必填字段</h3>';
				break;
			}
		}
		return $this->pass;
	}

	function pushTargetList($targetList) {
		//测试环境data在info.txt中获取
		if (STATUS == 2) {
			$targetList = CONFIG::$targetList;
		}

		if ($targetList && is_array($targetList)) {
			if ($this->validator($targetList)) {
				$this->userList = $targetList;
			}
		}
	}

	//生成txt文件
	private function createTxt($filename, $arr) {
		$filename = $filename . '.txt';

		$myfile = fopen($filename, "w");
		// 添加 BOM 解决中文乱码问题
		//fwrite($myfile, chr(0xEF).chr(0xBB).chr(0xBF));
		fwrite($myfile, chr(0xEF) . chr(0xBB) . chr(0xBF));
		foreach ($arr as $value) {
			$jsonstr = json_encode($value);
			$jsonstr = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2','UTF-8', pack('H4', '\\1'))", $jsonstr);
			fwrite($myfile, $jsonstr);
		}

		fclose($myfile);
	}

	/*
		处理array(
			array("key"=>"asdf"),
			array("key1"=>"rwer")
		);
		@return asdf
	*/
	private function getHeadKey($arr) {
		$headArr = array();
		$values = array();
		foreach ($arr as $key => $val) {
			foreach ($val as $k => $v) {
				$headArr[$k] = '';
			}
		}

		foreach ($arr as $i => $d) {
			$temp = json_encode(array_merge($headArr, $d));
			array_push($values, $temp);
		}

		return $values;
	}

	//生成csv文件
	private function createCSV($filename, $arr) {

		//$arr = ('code'=>'0','flag_media'=>'0');

		$targetArr = $this->getHeadKey($arr);

		//return;
		$csv_arr = array();
		if (is_array($targetArr)) {
			foreach ($targetArr as $key => $value) {
				$itemVal = $this->getKeyAndVal($value);
				if ($key == 0) {
					array_push($csv_arr, $itemVal[0]);
					array_push($csv_arr, $itemVal[1]);
				} else {
					array_push($csv_arr, $itemVal[1]);
				}
			}
		}

		$filename = $filename . '.csv';

		$myfile = fopen($filename, "w");
		// 添加 BOM 解决中文乱码问题
		fwrite($myfile, chr(0xEF) . chr(0xBB) . chr(0xBF));
		foreach ($csv_arr as $value) {
			fputcsv($myfile, $value);
		}

		fclose($myfile);

	}

	/**从map中选出含有指定字段数组
		 *$str 字段名字
	*/
	/*
		private function getArray($titleArr){
			//$titleArr = ["ApplyLoan","SpecialList","Accountchange"];
			$arr = CONFIG::map();
			$res = array();
			foreach ($arr as $key => $value) {
				foreach ($titleArr as $k => $v) {
					if(preg_match('/'.$v.'(?=_.[^_])/',$key)){
						//echo $key.'<br />';
						$res[$key] = '';
					};

				}
			}
			return $res;
		}
	*/

	//获取key & value

	private function alert($str) {
		echo '<script>console.log("' . $str . '");</script>';
	}

	private function getKeyAndVal($str) {
		//echo $str;
		if (is_string($str)) {
			//var_dump($str);
			//转数组

			$str = str_replace('[]', '""', $str);

			//打平但未映射数组
			//$newArr = $this -> setKeys($arrayData);
			//$newStr = $this -> replaceLang(json_encode($newArr));

			//$newStr = $this -> replaceLang($str);

			//删除多余废字段
			$newStr = $this->delGarbageTitle(json_decode($str, true));

			//$fromMapDefaultTitle = $this -> getArray($this -> headerTitle);

			//加入默认字段
			//$newStr = json_encode(array_merge($fromMapDefaultTitle,json_decode($newStr,true)));

			//$newStr = $this -> replaceLang($newStr);
			$newStr = str_replace('\\\\', '\\', $newStr);

			$map = CONFIG::map();

			foreach ($map as $key => $value) {
				$newStr = str_replace('"' . $key . '"', '"' . $value . '"', $newStr);
				//$newStr = preg_replace('/\"'.$key.'\"/', '"'.$value.'"', $newStr);
			}

			$finalArr = json_decode($newStr, true);

			$finalArr = array_merge($this->ctitle, $finalArr);

			$keys = array();
			$values = array();
			$res = array();

			foreach ($finalArr as $key => $value) {
				array_push($keys, $key);
				array_push($values, $value);
			}

			$res = array(
				$keys,
				$values,
			);
			//var_dump($res);
			return $res;
		}
	}

	//unicode 转 utf-8
	private function replaceLang($str) {
		//if(is_string($str)){
		//$str = preg_replace("#\\\u([0-9a-f]+)#ie","iconv('UCS-2BE','UTF-8', pack('H4', '\\1'))",$str);
		return $str;
		//}

		/*

			$str = rawurldecode($str);
			preg_match_all("/(?:%u.{4})|&#x.{4};|&#\d+;|.+/U",$str,$r);
			$ar = $r[0];
			//print_r($ar);
			foreach($ar as $k=>$v) {
				if(substr($v,0,2) == "%u"){
					$ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,-4)));
				}
				elseif(substr($v,0,3) == "&#x"){
					$ar[$k] = iconv("UCS-2BE","UTF-8",pack("H4",substr($v,3,-1)));
				}
				elseif(substr($v,0,2) == "&#") {

					$ar[$k] = iconv("UCS-2BE","UTF-8",pack("n",substr($v,2,-1)));
				}
			}
			return join("",$ar);

			*/

	}

	//删除多余字段
	//从CONFIG::
	private function delGarbageTitle($targetArr) {
		if (!is_array($targetArr)) {return;}
		$arr = CONFIG::garbageTitle();

		//echo 'delGarbageTitle<br />';
		//echo($targetArr);
		$res = array();
		foreach ($targetArr as $key => $value) {
			# code...

			if (!in_array($key, $arr)) {
				//echo $key.'<br>';
				$res[$key] = $value;
			}

		}
		return json_encode($res);
	}

	/*
		//打平
		private function setKeys($arr){
			//var_dump($arr);
			$this ->keys = array();
			$this -> _setKeys($this ->keys,$arr,'');
			if(CONFIG::isDebug()){
				var_dump($this ->keys);
			}
			return $this ->keys;
		}

		private function _setKeys($keys,$arr,$pre){
			foreach($arr as $k => $v){
				$cpre = $pre ? $pre.'_'.$k : $k;
				if(!is_array($v)){
					$this ->keys[$cpre] = $v;
				}else{
					$this -> _setKeys($this->keys,$v,$cpre);
				}
			}
		}
	*/

}

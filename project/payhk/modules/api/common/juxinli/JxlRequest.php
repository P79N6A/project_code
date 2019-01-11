<?php
namespace app\modules\api\common\juxinli;
use app\common\Logger;
use app\common\Curl;
set_time_limit(120); 

/**
 * 发起采集的请求
 */
class JxlRequest{
	private $config;
	/**
	 * 初始化
	 */
	public function __construct($c=''){
		//账号配置文件
		if($c != 1 && $c != 2){
			$c = 2;
		}
		$configPath = __DIR__ . "/config{$c}.php";
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
		$this->config = include( $configPath );
	}
	/**
	 * 获取选择的站点
	 * @param  string $name 姓名
	 * @return []
	 */
	public function getWebSite($name){
		$map = [
			'jingdong' => ['name'=>'jingdong', 'category'=>'e_business'],
		];
		return isset($map[$name]) ? $map[$name] : [];
	}
	/**
	 * 1 获取支持的数据源
	 * get 方式
	 */
	public function datasources(){
		$url = $this->config['datasources'];
		$content = $this->curlGet($url);
		$content = $this->parseJson($content);
		return $content;		
	}

	/**
	 * 2.1 申请请求
	 * post json传输
	 */
	public function request($data){
		if( !is_array($data) ){
			return null;
		}
		// 新增联系人业务
		$contacts = [];
		if(isset($data['contacts'])){
			$temp = json_decode($data['contacts'],true);
			if( is_array($temp)  && 
				isset($temp[0]) && is_array($temp[0]) &&
				isset($temp[0]['contact_tel']) 
				){
				$contacts = $temp;
			}
		}

		// 获取采集的网站
		$selected_website = $this->getWebSite($data['website']);

		// 请求参数
		$reqData = [
			'selected_website' => empty($selected_website) ? [] : [$selected_website] ,
			'basic_info' => [
				'name' => $data['name'],
				'id_card_num' => $data['id_card_num'],
				'cell_phone_num' => $data['cell_phone_num'],
			],
			'contacts' => $contacts,
			'skip_mobile' =>$data['skip_mobile'],
			'uid' => (string)$data['uid'],
		];
		//print_r($reqData);exit;
		// 发送请求
		$url = $this->config['request'];
		$content = $this->curlPost($url, $reqData);
		return $this->parseJson($content);
	}
	/**
	 * 2.2 提交采集
	 */
	public function  postreq($reqData){
		$url = $this->config['postreq'];
		$content = $this->curlPost($url, $reqData);
		return $this->parseJson($content);
	}
	
	
	/*******************下面是查询的方法 start*******************/
	/**
	 * 获取令牌，用于查询，使用一个永久的就可以
	 */
	public function accessReportToken(){
		$reqData = [
			'org_name' => $this->config['org_name'], //机构标识码，在申请阶段获得
			'client_secret' =>  $this->config['client_secret'], //访问授权码，通过access_report_token接口获得
			'hours' => 'per', /*[1]， token可使用1小时，过期失效
       							[24]，token可使用24小时，过期失效
      							[per] ，token为永久性可用 */
		];
		$url = $this->config['access_report_token'];
		$content = $this->curlGet($url, $reqData );
		return $this->parseJson($content);
		/**
		 * [
			  'access_token' => '74cab23575934fb2b9244ea582bd1d61',
			  'note' => '',
			  'success' => 'true',
			  'expires_in' => '1',
			]
		 */
	}
	/**
	 * 查询通话纪录
	 * https://www.juxinli.com/api/access_raw_data
	 * GET 
	 * $reqData = [
			client_secret，机构标识码，在申请阶段获得
			access_token，访问授权码，通过access_report_token接口获得
			name，申请人的姓名
			idcard，申请人的身份证号码
			phone，申请人的联系电话号码
	    ]
	 */
	public function accessRawData($data, $returnJson=false){
		$reqData = [
			'client_secret' => $this->config['client_secret'], //机构标识码，在申请阶段获得
			'access_token'  => $this->config['access_token'], //访问授权码，通过access_report_token接口获得
			'name'   => $data['name'], //申请人的姓名
			'idcard' => $data['idcard'], //申请人的身份证号码
			'phone'  => $data['phone'], //申请人的联系电话号码
		];
		$website = isset($data['website']) ? $data['website'] : '';
		switch($website){
			case 'jingdong':
				$url = $this->config['access_e_business_raw_data'];
				break;
			default:
				$url = $this->config['access_raw_data'];
		}

		$content = $this->curlGet($url, $reqData );
		return $returnJson ? $content : $this->parseJson($content);
	}
	/**
	 * 使用token来查询纪录
	 */
	public function accessRawDataByToken($token, $website = '', $returnJson=false){
		$reqData = [
			'client_secret' => $this->config['client_secret'], //机构标识码，在申请阶段获得
			'access_token'  => $this->config['access_token'], //访问授权码，通过access_report_token接口获得
			'token'  => $token,
		];

		switch($website){
			case 'jingdong':
				$url = $this->config['access_e_business_raw_data_by_token'];
				break;
			default:
				$url = $this->config['access_raw_data_by_token'];
		}

		$content = $this->curlGet($url, $reqData );
		return $returnJson ? $content : $this->parseJson($content);
	}
      /**
       * 使用token来查询纪录
       */
      public function accessReportDataByToken($token){
        $reqData = [
          'client_secret' => $this->config['client_secret'], //机构标识码，在申请阶段获得
          'access_token'  => $this->config['access_token'], //访问授权码，通过access_report_token接口获得
          'token'  => $token,
        ];
        $url = $this->config['access_report_data_by_token'];

        $content = $this->curlGet($url, $reqData );
        return $this->parseJson($content);
      }
  
	/*******************下面是查询的方法 end*******************/
	
	/**
	 * 获取数据
	 * @param array $data
	 * @param str2json
	 * @return null
	 */
	private function curlGet($url, $params = array()){
		$curl = new Curl();
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,120);
		$curl -> setOption(CURLOPT_TIMEOUT,120);
		$content = $curl -> get($url, $params);
		$status  = $curl -> getStatus();
		if( $status == 200 ){
			return $content;
		}else{
			Logger::dayLog(
				"juxinli",
				"请求信息",$url,$params,
				"http状态",$status,
				"响应内容",$content
			);
			return null;
		}
	
	}
	/**
	 * 提交数据
	 * @param array $data
	 * @param str json
	 * @return null
	 */
	private function curlPost($url, $data){
		$timeLog = new \app\common\TimeLog();
		
		$jsonString = json_encode($data);
		$curl = new Curl();
		$curl -> addHeader([
			'Content-Type' => 'application/json',  	
			'Content-Length' => strlen($jsonString)      
		]);
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,120);
		$curl -> setOption(CURLOPT_TIMEOUT,120);
		$content = $curl -> post($url, $jsonString);
		$status  = $curl -> getStatus();
		
		$timeLog->save('jxl',[
			'api',
			'POST',
			$status,
			$url,
			$jsonString,
			$content,
		]);
		
		if( $status == 200 ){
			return $content;
		}else{
			Logger::dayLog(
				"juxinli",
				"请求信息",$url,$data,
				"http状态",$status,
				"响应内容",$content
			);
			return null;
		}
	}
	/**
	 * json数据解析
	 */
	private function parseJson( $content ){
		$arr = json_decode($content,true);
		$err = json_last_error();
		if( $err ){
			Logger::dayLog(
				"juxinli",
				"解析json失败,错误码",$err,
				"解析原内容",$content
			);
			return null;
		}else{
			return $arr;
		}
	}
}

<?php

namespace app\models;
use app\common\Logger;
use app\models\YysClientNotify;

/**
 * This is the model class for table "yidun_request".
 */
class YidunRequest extends \app\models\BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'yidun_request';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['name', 'idcard', 'phone','password'], 'required'],
			[['process_code', 'create_time', 'aid', 'source','from','requestid','modify_time'], 'integer'],
			[['callbackurl', 'token','captcha_path'], 'string', 'max' => 100],
			[['result_status', 'client_status','is_smscode','is_imgcode','is_smscodejldx' ], 'integer'],
			[['name', 'password', 'website','query_pwd','bizno','orgbizno'], 'string', 'max' => 50],
			[['idcard', 'phone'], 'string', 'max' => 20],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'aid' => 'AID',
			'requestid' => '请求ID',
			'name' => '姓名',
			'idcard' => '身份证',
			'phone' => '手机号',
			'token' => 'token',
			'password' => '服务密码',
			'query_pwd' => '查询密码',
			'is_smscode' => '是否需要短信验证码',
			'is_imgcode' => '是否需要图片验证码',
			'captcha_path' => '图片验证码路径',
			'is_smscodejldx' => '是否需要手动发短信验证码',
			'bizno' =>'上数的业务流水',
			'orgbizno' => '商户业务流水号',
			'process_code' => '流程码',
			'website' => '运营商英文名称',
			'source' => '来源',//来源:1:XIANHUAHUA; 2:kuaip 3:融360 4:蚁盾-上数
			'create_time' => '创建时间',
			'modify_time' => '更新时间',
			'result_status' => '采集完成状态',
			'callbackurl' => '回调地址',
			'client_status' => '客户端响应状态'
		];
	}

	/**
	 * 保存数据
	 */
	public function saveYidunData($postData) {
		// 检测数据
		if (!$postData) {
			return $this->returnError(false, '不能为空');
		}
		$time = time();
		$data = [
				'aid' => isset($postData['aid']) ? $postData['aid'] : 0,
				'requestid' => isset($postData['user_id']) ? $postData['user_id'] : 0,
				'name' => isset($postData['name']) ? $postData['name'] : '',
				'idcard' => $postData['idcard'],
				'phone' => $postData['phone'],
				'token' => isset($postData['token']) ? $postData['token'] : '',
				'password' => $postData['password'],
				'query_pwd' => isset($postData['query_pwd']) ? $postData['query_pwd'] : '',
				'bizno' => isset($postData['bizno']) ? $postData['bizno'] : '',
				'orgbizno' => isset($postData['orgbizno']) ? $postData['orgbizno'] : '',
				'website' => isset($postData['website']) ? $postData['website'] : '',
				'process_code' => isset($postData['process_code']) ? $postData['process_code'] : 0,
				'source' => $postData['source'],
				'from' => isset($postData['from']) ? $postData['from'] : 0,
				'create_time' => $time,
				'modify_time' => $time,
				'result_status' => isset($postData['result_status']) ? $postData['result_status'] : 0,
				'client_status' => isset($postData['client_status']) ? $postData['client_status'] : 0,
				'callbackurl' => $postData['callbackurl'],
		];
		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, $error);
		}

		return $this->save();
	}

	public function getOneRequest($requestid,$bizno=''){
		if (!$requestid) {
			return $this->returnError(false, '参数不能为空');
		}
		if($bizno != ''){
			$where = ['bizno'=> $bizno];
		}else{
			$where = 1;
		}
		$model = YidunRequest::find()->where(['requestid' => $requestid])
			->andWhere($where)
			->orderBy("requestid DESC")
			->limit(1)
			->one();
		return $model;
	}

	public function getOneUserInfo($bizno){
		if (!$bizno) {
			return $this->returnError(false, '参数不能为空');
		}
		$model = YidunRequest::find()->where(['bizno' => $bizno])
			->orderBy("requestid DESC")
			->limit(1)
			->one();
		return $model;
	}

		/**
	 * POST 异步通知客户端:并仅通知最终结果, 即(成功|失败)
	 * @return bool
	 */
	public function clientNotify() {
		/*if ($this->process_code != '10000') {//成功不需要通知
			return false;
		}*/
		// 更新通知状态
		$data = $this->clientData();
		$result = $this->clientPost($this->callbackurl, $data, $this->aid);
		$saveNotify = new YysClientNotify();
		if ($result) {
			$grabStatus = 1;//成功
			$this->client_status = 1;
			$this->modify_time = time();
			$result = $this->save();
		} else {
			$grabStatus = 0;//失败
		}
		$res = $saveNotify->saveData($this->requestid,$grabStatus);
		if (!$res) {
			Logger::dayLog('grab/notify', 'ClientNotify/saveData-fails',$saveNotify->errors);
        }
		return $result;
	}

	/**
	 * 返回客户端响应结果
	 */
	public function clientData() {
		if($this->process_code == '10000'){
			$status = 4;//请求成功，详单拉取中
		}else{
			$status = 3;//失败
		}

		if($this->result_status == 1){
			$status = 1;//成功
		}
		if($this->result_status == 2){
			$status = 3;//失败
		}
		return [
				'requestid' => $this->requestid,
				'phone' => $this->phone,
				'from' => $this->from,
				'status' => $status,
				'source' => 4,
				'url' => ''
		];
	}

	/**
	 * POST 异步通知客户端
	 * @return bool
	 */
	public function clientPost($callbackurl, $data, $aid) {
		//1 加密
		$res_data = App::model()->encryptData($aid, $data);
		$postData = ['res_data' => $res_data, 'res_code' => 0];

		//2 post提交
		$oCurl = new \app\common\Curl;
		$res = $oCurl->post($callbackurl, $postData);
		Logger::dayLog('grab/clientPost', 'post', "客户响应|{$res}|", $callbackurl, $data,$res);

		//3 解析结果
		$res = strtoupper($res);
		return $res == 'SUCCESS';
	}
	/**
	 * GET 页面回调链接
	 */
	public function clientGet($callbackurl, $data, $aid) {
		//1 加密
		$res_data = App::model()->encryptData($aid, $data);
		//2 组成url
		$link = strpos($callbackurl, "?") === false ? '?' : '&';
		$url = $callbackurl . $link . 'res_code=0&res_data=' . rawurlencode($res_data);
		return $url;
	}

	/**
	 * GET 回调通知客户端 url
	 * @return url
	 */
	public function clientBackurl() {
		$data = $this->clientData();
		$url =  $this->clientGet($this->callbackurl, $data, $this->aid);
		return $url;
	}

}

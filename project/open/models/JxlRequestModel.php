<?php

namespace app\models;
use app\common\Logger;
use app\models\JxlPhoneRecord;
use app\models\YysClientNotify;
use app\modules\api\common\juxinli\JxlRequest;

/**
 * This is the model class for table "jxl_request".
 *
 * @property integer $id
 * @property string $name
 * @property string $idcard
 * @property string $phone
 * @property string $token
 * @property string $account
 * @property string $password
 * @property string $captcha
 * @property integer $type
 * @property string $website
 * @property integer $response_type
 * @property integer $process_code
 * @property string $create_time
 * @property string $result
 */
class JxlRequestModel extends \app\models\BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'jxl_request';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['name', 'idcard', 'phone'], 'required'],
			[['process_code', 'create_time', 'aid', 'source', 'from'], 'integer'],
			[['type', 'response_type'], 'string', 'max' => 20],
			[['callbackurl'], 'string', 'max' => 100],
			[['result_status', 'client_status'], 'integer'],
			[['contacts'], 'string'],
			[['result'], 'string'],
			[['name', 'token', 'account', 'password', 'website','query_pwd'], 'string', 'max' => 50],
			[['idcard', 'phone', 'captcha'], 'string', 'max' => 20],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'name' => '姓名',
			'idcard' => '银行卡',
			'phone' => '手机号',
			'token' => 'token',
			'account' => '帐号',
			'password' => '密码',
			'captcha' => '网站动态验证',
			'query_pwd' =>'网站查询密码',
			'type' => 'CONTROL控制类型的响应结果',
			'website' => '网站英文名称',
			'response_type' => '1 CONTROL控制类型的响应结果; 2 ERROR错误类型的响应结果 ;3 RUNNING 正在运行',
			'process_code' => '流程码，见文档',
			'source' => '来源',//1:XIANHUAHUA; 2:kuaip
			'create_time' => 'Create Time',
			'contacts' => '常见联系人',
			'result' => '最近电话资料集合（需要的话可扩展成单独表）',
		];
	}
	/**
	 * 根据id获取聚信立的数据
	 */
	public function queryDataFromJxl($id) {
		//1 参数校验与模型建立
		$oModel = $this->getById($id);
		if (!$oModel) {
			return $this->returnError(null, "没有找到纪录");
		}

		$recordModel = new JxlPhoneRecord;
		$jxlRequest = new JxlRequest($oModel->source);

		//2. 本地是否同步数据了, 存在的话直接返回
		if ($oModel->result_status == 1) {
			$data = $recordModel->getByRequestId($id);
			if ($data) {
				return $data;
			}
		}

		//3. 查询接口数据
		$data = [
			'name' => $oModel->name,
			'idcard' => $oModel->idcard,
			'phone' => $oModel->phone,
			'website' => $oModel->website,
		];
		$result = $jxlRequest->accessRawData($data);
		//print_r($result);
		$isOk = is_array($result) && isset($result['success']) && $result['success'] == true;
		if (!$isOk) {
			\app\common\Logger::dayLog(
				'juxinli',
				'actionQuerydata',
				'提交数据', $id,
				'失败原因', $result
			);
			return $this->returnError(null, '查询数据失败');
		}

		//4. 保存数据
		if (is_array($result) &&
			is_array($result['raw_data']) &&
			is_array($result['raw_data']['members']) &&
			is_array($result['raw_data']['members']['transactions']) &&
			is_array($result['raw_data']['members']['transactions'][0])
		) {
			$data = $result['raw_data']['members']['transactions'][0];

			$oModel->result_status = 1;
			$oModel->result = serialize($data);
			$res = $oModel->save();
			if (!$res) {
				\app\common\Logger::dayLog(
					'juxinli',
					'queryDataFromJxl',
					'提交数据', $data,
					'失败原因', $oModel->errors
				);
			}

			//$data = $this->getTestData(); //测试数据
			$res = $recordModel->batchSaveData($id, $data['calls']);

			// 返回通话纪录
			return $data['calls'];

		} else {
			return $this->returnError(null, "最近没有通话纪录");
		}
	}
	/**
	 * 判断年龄是否合法
	 * 1985年以后的才合法
	 */
	public function validBirth($idcard) {
		$birth = strlen($idcard) == 15 ? ('19' . substr($idcard, 6, 6)) : substr($idcard, 6, 8);
		return $birth >= '19650101';
	}
	/**
	 * 地区限制——抓取的身份证号前两位——15 内蒙 54 西藏  65 新疆
	 */
	public function validArea($idcard) {
		$num = substr($idcard, 0, 2);
		if ($num == 15) {
			// return $this->returnError(false, "暂不支持内蒙地区");
			return $this->returnError(false, "三个月内不能进行认证");
		} elseif ($num == 54) {
			// return $this->returnError(false, "暂不支持西藏地区");
			return $this->returnError(false, "三个月内不能进行认证");
		} elseif ($num == 65) {
			// return $this->returnError(false, "暂不支持新疆地区");
			return $this->returnError(false, "三个月内不能进行认证");
		} else {
			return true;
		}
	}
	/**
	 * 二分钟内相同重复的请求
	 * @param  string $account    手机,帐号
	 * @param  string $password 密码
	 * @param  string $captcha  验证码
	 * @return object
	 */
	public function getRecentSame($account, $password, $captcha) {
		//1 限制时间
		$limit_time = time() - 120;

		//2 二分钟内是否重复
		$model = JxlRequestModel::find()->where(['account' => $account])
			->andWhere(['password' => $password])
			->andWhere(['captcha' => $captcha])
			->andWhere(['>', 'create_time', $limit_time])
			->orderBy('create_time DESC')
			->one();
		return $model;
	}

	public function getRecentSameNew($account, $password) {
		//1 限制时间
		$limit_time = time() - 120;

		//2 二分钟内是否重复
		$model = JxlRequestModel::find()->where(['account' => $account])
				->andWhere(['password' => $password])
				->andWhere(['>', 'create_time', $limit_time])
				->orderBy('create_time DESC')
				->one();
		return $model;
	}
	/**
	 * 按权限获取配置文件名
	 * @return [type] [description]
	 */
	private function getSourceWeight(){
		$a = rand(0,9);
		if($a<1){
			return 1;
		}else{
			return 2;
		}
	}
	/**
	 * 自动分配
	 * @param  str  $phone      手机号
	 * @return int
	 */
	public function getAutoSource($phone,$website) {
		//1 查找同一手机号最近纪录
		$model = JxlRequestModel::find()->where(['phone' => $phone])
			//->andWhere(['>', 'create_time', $limit_time])
			->orderBy('create_time DESC')
			->limit(1)
			->one();

		//2 新纪录按权重分配
		if(!$model){
			return $this->getSourceWeight();
		}

		// 3 京东电商购物数据 来源必须是1或者2 如果不是强制重新获取
        if($website=='jingdong' && !in_array($model->source, [1,2])){
            return $this->getSourceWeight();
        }

		//4 一天内数据保持来源一致
		$limit_time = time() - 86400;// 24小时前
		if($model->create_time > $limit_time ){
			return $model->source;// 历史纪录
		}

		//5 自动分配来源
		$source = $this->getSourceWeight();
		if($source == 2){
			return 2;
		}else{
			return $model->source;// 历史纪录
		}
	}

	/**
	 * 自动分配
	 * @param  str  $phone      手机号  $source 来源
	 * @return int
	 */
	public function getAutoSourceNew($phone,$source) {
		//1 查找同一手机号最近纪录
		$model = JxlRequestModel::find()->where(['phone' => $phone , 'source' => $source])
				//->andWhere(['>', 'create_time', $limit_time])
				->orderBy('create_time DESC')
				->limit(1)
				->one();

		//2 新纪录按权重分配
		if(!$model){
			return $this->getSourceWeight();
		}

		//3 一天内数据保持来源一致
		$limit_time = time() - 86400;// 24小时前
		if($model->create_time > $limit_time ){
			return $model->source;// 历史纪录
		}

		//4 自动分配来源
		$source = $this->getSourceWeight();
		if($source == 2){
			return 2;
		}else{
			return $model->source;// 历史纪录
		}
	}
	/**
	 * 从接口中重新获取报告内容
	 * @param  [type] $requestid [description]
	 * @return [type]            [description]
	 */
	public function getApiReport($requestid) {
		$oModel = JxlRequestModel::findOne($requestid);
		if (empty($oModel)) {
			return null;
		}

		$token = $oModel->token;
		$oJxlApi = new JxlRequest($oModel->source);
		$report = $oJxlApi->accessReportDataByToken($token);
		if (is_array($report) && is_array($report['report_data'])) {
			$data = [
				"APP_IDCARD_NO"=> $oModel->idcard, 
				"APP_PHONE_NO"=> $oModel->phone, 
				"LOAN_APP_ID"=>  $oModel ->id, 
				"CUST_NAME"=>  $oModel ->name, 
				"JSON_INFO"=>  $report['report_data'],
			];
			return $data;
		}
		return null;
	}
	/**
	 * 从接口中重新获取报告内容
	 * @param  [type] $requestid [description]
	 * @return [type]            [description]
	 */
	public function getApiDetail($requestid) {
		$oModel = JxlRequestModel::findOne($requestid);
		if (empty($oModel)) {
			return null;
		}
		$oJxlApi = new JxlRequest($oModel->source);
		$detail = $oJxlApi->accessRawDataByToken($oModel['token'], $oModel['website']);
		return $detail;
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
		$res = $saveNotify->saveData($this->id,$grabStatus);
		if (!$res) {
			Logger::dayLog('grab/notify', 'ClientNotify/saveData-fails',$saveNotify->errors);
        }
		return $result;
	}


	/**
	 * 返回客户端响应结果
	 * @return  []
	 */
	public function clientData() {
		if($this->process_code == '10008'){
			$status = 1;
		}else{
			$status = 3;
		}
		return [
				'requestid' => $this->id,
				'phone' => $this->phone,
				'status' => $status,
				'source' => $this->source,
				'from' => $this->from,
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

	/**
	 * 保存数据
	 */
	public function saveJxlresquest($postData) {
		// 检测数据
		if (!$postData) {
			return $this->returnError(false, '数据不能为空');
		}
		$time = time();
		$data = [
				'aid' => isset($postData['aid']) ? $postData['aid'] : 0,
				'name' => isset($postData['name']) ? $postData['name'] : '',
				'idcard' => $postData['idcard'],
				'phone' => $postData['phone'],
				'token' => isset($postData['token']) ? $postData['token'] : '',
				'account' => isset($postData['account']) ? $postData['account'] : '',
				'password' => isset($postData['password']) ? $postData['password'] : '',
				'query_pwd' => isset($postData['query_pwd']) ? $postData['query_pwd'] : '',
				'captcha' => isset($postData['captcha']) ? $postData['captcha'] : '',
				'type' => isset($postData['type']) ? $postData['type'] : '',
				'website' => isset($postData['website']) ? $postData['website'] : '',
				'response_type' => isset($postData['response_type']) ? $postData['response_type'] : '',
				'process_code' => isset($postData['process_code']) ? $postData['process_code'] : 0,
				'source' => $postData['source'],
				'from' => isset($postData['from']) ? $postData['from'] : 0,
				'create_time' => $time,
				'result' => isset($postData['result']) ? $postData['result'] : '',
				'contacts' => isset($postData['contacts']) ? $postData['contacts'] : '',
				'callbackurl' => $postData['callbackurl'],
		];
		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, $error);
		}
		$this->save();
		return $this->id;
		
	}
	/**
     * Undocumented function
     * 根据requestid查询数据集
     * @param [type] $requestid
     * @return void
     */
    public function getJxlData($requestid){        
        if(empty($requestid)) return false;
        $data = static::findOne($requestid);
        return $data;
	}
	/**
	 * Undocumented function
	 * 更新状态码
	 * @param [type] $process_code
	 * @return void
	 */
	public function upJxlProcesscode($process_code){
		$this->process_code = $process_code;
		$this->modify_time = time();
		return $this->save();
	}
}

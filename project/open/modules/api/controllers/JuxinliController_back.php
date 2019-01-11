<?php
/**
 * 聚信立
 * 内部错误码范围8000-9000
 * @author lijin
 */
namespace app\modules\api\controllers;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\ApiController;
use app\modules\api\common\juxinli\JxlRequest;
use Yii;
use yii\helpers\ArrayHelper;

class JuxinliController extends ApiController {
	/**
	 * 服务id号
	 */
	protected $server_id = 8;

	/**
	 * 聚信立
	 */
	private $jxlRequest;

	/**
	 * 初始化
	 */
	public function init() {
		parent::init();
		$this->jxlRequest = new JxlRequest(2);// 默认使用2,即包月的
	}

	public function actionIndex() {

	}

	/******************采集流程*******************/
	public function actionDatasources() {
		$content = $this->jxlRequest->datasources();
		$this->resp(0, $content);
	}

	/**
	 * 发送请求并提交采集动作，相当于 actionRequest  和 actionPostreq
	 * 使用这个就可以了
	 */
	public function actionPostrequest() {
		//1 获取请求数据
		$data = $this->reqData;
		$idcard = $data['idcard'];
		if (!$idcard) {
			return $this->resp(8003, '身份证不能为空');
		}
		// website参数验证
		$website = isset($data['website']) ? $data['website'] : '';
		if(!in_array($website, ['','jingdong'])){
			return $this->resp(8002, '不支持此website');
		}
		$data['website'] = $website;
		// 若设置了website跳过运行商
		$skip_mobile = $data['website'] ? true : false;

		//2 检测年龄和区域
		$jxlRequestModel = new JxlRequestModel();
		if (!$jxlRequestModel->validBirth($idcard)) {
			return $this->resp(8004, '您的年龄不符合要求');
		}
		if (!$jxlRequestModel->validArea($idcard)) {
			return $this->resp(8005, $jxlRequestModel->errinfo);
		}

		//3 判断几个月时间内,使用历史数据
		$oJxlStat = new JxlStat;
		$oHistory = $oJxlStat->getHistory($data['phone'], $data['website']);
		if ($oHistory) {
			return $this->resp(0, [
				'requestid' => $oHistory['requestid'], // 查询时使用这个就可以了
				'token' => '', // 使用这个token也可以查询
				'phone' => $oHistory['phone'],
				'process_code' => 10008,
				'response_type' => '',
				'status' => true,
			]);
		}
		//return $this->resp(8010, "业务暂停");// @todo

		//3 最近2分钟时相同的数据返回同样的结果
		$account = isset($data['account']) ? $data['account'] : $data['phone'];
		$oSameJxl = (new JxlRequestModel)->getRecentSame($account, $data['password'], $data['captcha']);
		if ($oSameJxl) {
			return $this->resp(0, [
				'requestid' => $oSameJxl->id, // 查询时使用这个就可以了
				'token' => $oSameJxl->token, // 使用这个token也可以查询
				'phone' => $oSameJxl->phone,
				'process_code' => $oSameJxl->process_code,
				'response_type' => $oSameJxl->response_type,
				'status' => intval($oSameJxl->process_code) === 10008,
			]);
		}

		//4 查询最近来源信息,保证同一用户同一通道来源.
		$data['source'] = (new JxlRequestModel)->getAutoSource($data['phone']);

		//4 保存数据到db中
		$data['create_time'] = time();
		$data['account'] = $account;
		$data['type'] = isset($data['type']) ? $data['type'] : 'SUBMIT_CAPTCHA';
		$data['aid'] = $this->appData['id'];
		//zhangfei加
		/*$data['token'] = '';
		$data['response_type'] = '';
		$data['result'] = '';
		$data['contacts'] = '';
		$data['callbackurl'] = '';*/

		$jxlRequestModel = new JxlRequestModel();
		if ($errors = $jxlRequestModel->chkAttributes($data)) {
			return $this->resp(8003, implode('|', $errors));
		}
		$res = $jxlRequestModel->save();
		if (!$res) {
			return $this->dayLog(
				'juxinli',
				'actionPostrequest',
				'提交数据', $data,
				'失败原因', $jxlRequestModel->errors
			);
		}

		//5 发送请求部分
		$requestData = [
			'name' => $data['name'],
			'id_card_num' => $data['idcard'],
			'cell_phone_num' => $data['phone'],
			'uid' => $jxlRequestModel->id,
			'contacts' => isset($data['contacts']) ? $data['contacts'] : null,
			// 新增加两个参数
			'website' => $data['website'],
			'skip_mobile' => $skip_mobile,
		];
		$oJxlRequest = new JxlRequest($jxlRequestModel->source);
		$result = $oJxlRequest->request($requestData);
		$isOk = is_array($result) && isset($result['success']) && $result['success'] == true;
		if (!$isOk) {
			return $this->resp(8001, "发送请求失败");
		}

		//6  获取并保存 token
		$token = $result['data']['token'];
		if (empty($token)) {
			return $this->resp(8002, "没有获取到请求token");
		}
		$jxlRequestModel->token = $token;

		//7 获取运营商或电商等数据
		$website = ArrayHelper::getValue($result, 'data.datasource.website');
		if ($website) {
			$jxlRequestModel->website = $website;
		}
		$res = $jxlRequestModel->save();

		//5 提交到采集接口
		return $this->postByDb($jxlRequestModel);
	}
	/**
	 * 重新发送验证信息
	 */
	public function actionPostretry() {
		//1  验证数据合法性
		$data = $this->reqData;
		$requestid = $data['requestid'];
		if (!$requestid) {
			return $this->resp(8051, "没有查询requestid");
		}

		$m = new JxlRequestModel();
		$jxlRequestModel = $m->getById($requestid);

		if (!$jxlRequestModel) {
			return $this->resp(8052, "没有找到requestid");
		}

		//2 重新更新字段
		if (isset($data['password'])) {
			$jxlRequestModel->password = $data['password'];
		}
		if (isset($data['captcha'])) {
			$jxlRequestModel->captcha = $data['captcha'];
		}
		if (isset($data['type'])) {
			$jxlRequestModel->type = $data['type'];
		}
		$res = $jxlRequestModel->save();

		// 是否保存成功
		if (!$res) {
			$this->dayLog(
				'juxinli',
				'actionPostretry',
				'提交数据', $data,
				'失败原因', $jxlRequestModel->errors
			);
		}

		// 重新提交请求
		$this->postByDb($jxlRequestModel);
	}
	/**
	 * 提交采集接口
	 */
	private function postByDb($jxlRequestModel) {
		//4 组合提交采集接口数据
		$postData = [
			'token' => $jxlRequestModel->token,
			'account' => $jxlRequestModel->account,
			'password' => $jxlRequestModel->password,
			'captcha' => $jxlRequestModel->captcha,
			'type' => $jxlRequestModel->type,
			'website' => $jxlRequestModel->website,
		];
		$oJxlRequest = new JxlRequest($jxlRequestModel->source);
		$result = $oJxlRequest->postreq($postData);
		$isOk = is_array($result) && isset($result['success']) && $result['success'] == true;
		if (!$isOk) {
			if (is_array($result) && !$result['success'] && $result['message']) {
				$this->resp(8003, $result['message']);
			}
			$this->resp(8004, "采集失败");
		}
		$data = (array) $result['data'];

		$jxlRequestModel->response_type = $data['type'];
		$jxlRequestModel->process_code = $data['process_code'];
		$res = $jxlRequestModel->save();

		$this->resp(0, [
			'requestid' => $jxlRequestModel->id, // 查询时使用这个就可以了
			'token' => $jxlRequestModel->token, // 使用这个token也可以查询
			'phone' => $jxlRequestModel->phone,
			'process_code' => $data['process_code'],
			'response_type' => $data['response_type'],
			'status' => intval($data['process_code']) === 10008,
		]);
	}
	/**
	 * 开始查询数据
	 * 使用手机号
	 *
	 */
	public function actionQuery() {
		//1 验证
		$phone = $this->reqData['phone'];
		if (!$phone) {
			return $this->resp(8133, "手机号不能为空");
		}
		$website = isset($this->reqData['website']) ? $this->reqData['website'] : '';

		//2 查找文件
		$oJxlStat = new JxlStat();
		$data = $oJxlStat->getDataByPhone($phone,$website);
		if (empty($data)) {
			return $this->resp(8134, "数据为空");
		}

		//4 返回结果
		return $this->resp(0, $data);
	}
	/**
	 * 开始查询数据
	 * 使用手机号
	 *
	 */
	private function actionQueryhistory() {
		$phone = $this->reqData['phone'];
		$page = isset($this->reqData['page']) ? intval($this->reqData['page']) : 1;
		$limit = isset($this->reqData['pagenum']) ? intval($this->reqData['pagenum']) : 100;
		if (($page > 0) === false) {
			$this->resp(8121, "页数必须是大于0的整数");
		}
		if (($limit > 0) === false) {
			$this->resp(8122, "每页纪录数必须是大于0的整数");
		}
		if ($limit > 100) {
			$this->resp(8123, "每页纪录数应该小于100");
		}

		$jxlPhoneRecord = new \app\models\JxlPhoneRecord();
		$calls = $jxlPhoneRecord->getByPhone($phone, ($page - 1) * $limit, $limit);
		if (empty($calls)) {
			$this->resp(8124, "没有采集到数据");
		}
		$this->resp(0, [
			'app_id' => $this->appData['app_id'],
			'phone' => $phone,
			'calls' => $calls,
		]);
	}
	/**
	 * 开始查询数据
	 * 使用手机号
	 * 错误码 8100-8150
	 */
	private function actionQuerydata() {
		// 1. 是否为空
		$requestid = $this->reqData['requestid'];
		if (!$requestid) {
			return $this->resp(8101, "没有查询requestid");
		}

		// 2. 系统中是否有纪录
		$jxlRequestModel = new JxlRequestModel();
		$data = $jxlRequestModel->queryDataFromJxl($requestid);
		if (!$data) {
			return $this->resp(8102, $jxlRequestModel->errinfo);
		}

		// 3. 返回正确结果
		$this->resp(0, [
			//'app_id' => $this->appData['app_id'],
			'requestid' => $requestid,
			'calls' => $data,
		]);
	}

	/// 下面的方法是一一对就聚信立接口的程序
	/// 以下两个方法综合起来就是 actionPostrequest ，故没必要使用下面的两个，可用于调试
	/**
	 * 发送请求:备用
	 */
	public function actionRequest() {
		$data = $this->reqData;
		$requestData = [
			'name' => $data['name'],
			'id_card_num' => $data['idcard'],
			'cell_phone_num' => $data['phone'],
		];
		$content = $this->jxlRequest->request($requestData);
		$this->resp(0, $content);
	}
	/**
	 * 提交请求:备用
	 */
	public function actionPostreq() {
		$data = $this->reqData;
		$content = $this->jxlRequest->postreq($data);
		$this->resp(0, $content);
		/**
		 *   array (
		'success' => true,
		'data' =>
		array (
		'type' => 'CONTROL',
		'content' => '开始采集行为数据',
		'process_code' => 10008,
		'finish' => true,
		),
		),
		 */
	}
	/// end

	/*********************查询流程*********************/
	/**
	 * 获取查询令牌 查询一次就行了
	 */
	public function actionAccessReportToken() {
		$content = (new jxlRequest('config2'))->accessReportToken();
		print_r($content);
		$this->resp(0, $content);
	}
	/**
	 * 开始查询数据
	 * 使用手机号
	 */
	public function actionAccessRawData() {
		$data = $this->reqData;
		$content = $this->jxlRequest->accessRawData($data);
		$this->resp(0, $content);
	}
	/**
	 * 开始查询数据
	 */
	public function actionAccessRawDataByToken() {
		// 使用token
		$token = $this->reqData['token'];
		$website = $this->reqData['website'];
		$content = $this->jxlRequest->accessRawDataByToken($token,$website);
		return $this->resp(0, $content);
	}
	// end 查询流程
}

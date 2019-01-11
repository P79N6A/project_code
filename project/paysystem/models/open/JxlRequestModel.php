<?php

namespace app\models\open;
use app\common\Logger;

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
class JxlRequestModel extends \app\models\open\OpenBase {
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


	public static function fromList(){
		return [
			'1' => '聚信立',
			'2' => '聚信立',
			'4' => '上数',
			'5' => '导流'
		];

	}
}

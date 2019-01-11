<?php

namespace app\models;
use app\common\Logger;

/**
 * This is the model class for table "rong_request".
 *
 * @property integer $id
 * @property integer $requestid
 * @property string $method
 * @property string $create_time
 * @property string $modify_time
 */
class RongRequest extends \app\models\BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'rong_request';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['requestid'], 'required'],
			[['requestid', 'create_time', 'modify_time'], 'integer'],
			[['method'], 'string', 'max' => 20],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'requestid' => '请求ID',
			'method' => '请求方法名',
			'create_time' => '创建时间',
			'modify_time' => '创建时间'
		];
	}

	/**
	 * 保存数据
	 */
	public function saveRongData($postData) {
		// 检测数据
		if (!$postData) {
			return $this->returnError(false, '不能为空');
		}
		$time = time();
		$data = [
				'requestid' => isset($postData['requestid']) ? $postData['requestid'] : 0,
				'method' => isset($postData['method']) ? $postData['method'] : '',
				'create_time' => $time,
				'modify_time' => $time,
		];
		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, $error);
		}

		return $this->save();
	}

}

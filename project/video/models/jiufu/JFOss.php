<?php

namespace app\models\jiufu;
use app\modules\api\common\jiufu\JFApi;

/**
 * This is the model class for table "jf_oss".
 *
 * @property string $id
 * @property string $req_id
 * @property string $img_url
 * @property string $img_name
 * @property string $file_id
 * @property string $create_time
 * @property string $modify_time
 */
class JFOss extends \app\models\BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'jf_oss';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['aid', 'req_id', 'client_id', 'img_url', 'img_name', 'file_id', 'create_time', 'modify_time'], 'required'],
			[['aid'], 'integer'],
			[['create_time', 'modify_time'], 'safe'],
			[['req_id'], 'string', 'max' => 30],
			[['client_id', 'img_name'], 'string', 'max' => 50],
			[['img_url'], 'string', 'max' => 255],
			[['file_id'], 'string', 'max' => 100],
			[['req_id'], 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'id',
			'aid' => 'Aid',
			'req_id' => '请求ID',
			'client_id' => 'oss请求号',
			'img_url' => '持证自拍照',
			'img_name' => '持证照名称',
			'file_id' => 'oss_id',
			'create_time' => '创建时间',
			'modify_time' => '更新时间',
		];
	}
	/**
	 * 保存数据
	 */
	public function saveData($oss_data) {
		$time = date("Y-m-d H:i:s");
		$data = [
			'aid' => $oss_data['aid'],
			'req_id' => $oss_data['req_id'],
			'client_id' => $oss_data['client_id'],
			'img_url' => $oss_data['img_url'],
			'img_name' => $oss_data['img_name'],
			'file_id' => $oss_data['file_id'],
			'create_time' => $time,
			'modify_time' => $time,
		];
		$errors = $this->chkAttributes($data);
		if ($errors) {
			Logger::dayLog('sinauser', '保存失败', $data, $errors);
			return false;
		}

		return $this->save();
	}
	/**
	 * 获取file_id
	 * @param str$img_url
	 * @return str
	 */
	public function getByImgUrl($img_url) {
		return static::find()->where(['img_url' => $img_url])->andWhere(['!=', 'file_id', ''])->limit(1)->one();
	}
	/**
	 * 生成唯一标识
	 * @param  int $aid     客户应用id
	 * @param  int $user_id 客户端唯一id
	 * @return str          唯一id
	 */
	public function getClientId($aid, $user_id) {
		return 'O' . $aid . 'S' . $user_id;
	}
}

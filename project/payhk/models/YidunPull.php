<?php

namespace app\models;
use app\common\Logger;

class YidunPull extends \app\models\BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'yidun_back_pull';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['bizno', 'pull_status'], 'required'],
			[['pull_status'], 'integer'],
			[['create_time'], 'safe'],
			[['bizno'], 'string', 'max' => 50],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'bizno' => '上数流水号',
			'pull_status' => '拉取 0:初始1:成功:2:失败',
			'create_time' => '创建时间'
		];
	}

	/**
	 * 保存数据
	 */
	public function saveData($postData) {
		// 检测数据
		if (!$postData) {
			return $this->returnError(false, '不能为空');
		}
		$date = date("Y-m-d H:i:s");
		$data = [
				'pull_status' => isset($postData['pull_status']) ? $postData['pull_status'] : 0,
				'bizno' => $postData['bizno'],
				'create_time' => $date
		];
		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, $error);
		}

		return $this->save();
	}

	/**
	 * 获取状态为初始和失败的记录
	 * @param $start_time 精确到分
	 * @param $end_time  精确到分
	 * @return []
	 */
	public function getPullList($subdata,$limit=50) {
		if(!$subdata){
			$data = date("Y-m-d");
		}else{
			$data = $subdata;
		}
		$where = ['AND',
			['pull_status' => [0]],
			['>=', 'create_time', $data],
		];
		$dataList = self::find()->where($where)->orderBy("id DESC")->limit($limit)->all();
		if (!$dataList) {
			return null;
		}
		return $dataList;
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



}

<?php

namespace app\models\sina;

use app\modules\api\common\sinapay\Sinapay;

/**
 * 新浪绑卡
 *
 */
class SinaBindbank extends \app\models\BaseModel {
	/**
	 * 新浪处理类
	 * @var [type]
	 */
	private $sinapay;
	public function __construct() {
		$this->sinapay = new Sinapay();
	}
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'sina_bindbank';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['request_id', 'aid', 'user_id', 'identity_id', 'name', 'idcard', 'phone', 'cardno', 'bankcode', 'card_type', 'create_time', 'modify_time'], 'required'],
			[['aid', 'sina_card_id'], 'integer'],
			[['create_time', 'modify_time'], 'safe'],
			[['request_id', 'identity_id', 'name'], 'string', 'max' => 30],
			[['user_id', 'idcard', 'phone', 'bankcode', 'card_type'], 'string', 'max' => 20],
			[['cardno'], 'string', 'max' => 50],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => '主键',
			'request_id' => '请求号',
			'aid' => '应用id',
			'user_id' => '客户端uid',
			'identity_id' => '新浪唯一标识',
			'name' => '姓名',
			'idcard' => '身份证',
			'phone' => '手机号',
			'cardno' => '银行卡号',
			'bankcode' => '银行编号',
			'card_type' => '借记卡, 信用卡 DEBIT:借记; CREDIT:贷记（信用卡）',
			'sina_card_id' => '新浪的绑卡id',
			'create_time' => '创建时间',
			'modify_time' => '最后修改时间',
		];
	}
	/**
	 * 获取当前用户下的同名卡
	 * @param  str $identity_id 会员id
	 * @param  str $cardno  银行卡号
	 * @return obj
	 */
	public function getSameCard($identity_id, $cardno) {
		if (!$identity_id || !$cardno) {
			return null;
		}
		$where = [
			'identity_id' => $identity_id,
			'cardno' => $cardno,
		];
		return static::find()->where($where)->limit(1)->one();
	}
	/**
	 * 添加一条纪录到数据库
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function saveData($data) {
		//保存数据
		$time = date("Y-m-d H:i:s");
		$data = [
			'request_id' => $data['request_id'],
			'aid' => $data['aid'],
			'user_id' => $data['user_id'],
			'identity_id' => $data['identity_id'],
			'name' => $data['name'],
			'idcard' => $data['idcard'],
			'phone' => $data['phone'],
			'cardno' => $data['cardno'],
			'card_type' => $data['card_type'],
			'bankcode' => $data['bankcode'],
			'sina_card_id' => $data['sina_card_id'],
			'create_time' => $time,
			'modify_time' => $time,
		];
		$errors = $this->chkAttributes($data);
		if ($errors) {
			return $this->returnError(false, json_encode($errors));
		}

		return $this->save();
	}
}

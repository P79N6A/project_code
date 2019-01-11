<?php

namespace app\models\sina;

/**
 * 绑卡请求日志
 */
class SinaBindbankLog extends \app\models\BaseModel {

	// 状态
	const STATUS_INIT = 0; // 初始
	const STATUS_ING = 1; // 处理中
	const STATUS_OK = 2; // 成功
	const STATUS_FAIL = 11; // 失败

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'sina_bindbank_log';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['request_id', 'aid', 'user_id', 'identity_id', 'bankcode', 'card_type', 'cardno', 'name', 'idcard', 'phone', 'create_time', 'modify_time'], 'required'],
			[['aid', 'sina_card_id', 'status'], 'integer'],
			[['response'], 'string'],
			[['create_time', 'modify_time'], 'safe'],
			[['request_id', 'identity_id', 'name', 'response_code'], 'string', 'max' => 30],
			[['user_id', 'bankcode', 'card_type', 'idcard', 'phone'], 'string', 'max' => 20],
			[['cardno','ip'], 'string', 'max' => 50],
			[['response_message'], 'string', 'max' => 255],
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
			'identity_id' => '唯一',
			'bankcode' => '银行编号',
			'card_type' => '借记卡, 信用卡 DEBIT:借记; CREDIT:贷记（信用卡）',
			'cardno' => '银行卡号',
			'name' => '姓名',
			'idcard' => '身份证',
			'phone' => '手机号',
			'sina_card_id' => '新浪的绑卡id',
			'response_code' => '响应:状态码',
			'response_message' => '响应:原因',
			'response' => '响应结果',
			'status' => '状态:0:初始; 1:处理中; 2:验证成功; 11:验证失败',
			'ip' => 'ip',
			'create_time' => '创建时间',
			'modify_time' => '最后修改时间',
		];
	}
	/**
	 * 每日同一银行卡超限设置
	 * @param string $cardno
	 * @return bool
	 */
	public function chkQueryNum($cardno) {
		if (!$cardno) {
			return false;
		}
		$today = date('Y-m-d');
		$total = self::find()	->where(['cardno' => $cardno])
											->andWhere(['>=', 'create_time', $today])
											->count();
		// 同一张卡限定10次
		return $total > 10;
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

			'bankcode' => $data['bankcode'],
			'card_type' => $data['card_type'],
			'sina_card_id' => 0,

			'response_code' => '',
			'response_message' => '',
			'response' => '',

			'status' => 0,
			'ip' => $data['ip'],
			'create_time' => $time,
			'modify_time' => $time,

		];
		$errors = $this->chkAttributes($data);
		if ($errors) {
			return $this->returnError(false, json_encode($errors));
		}

		return $this->save();
	}
	/**
	 * 是否存在在日志当中
	 * @param [] $data
	 * @return bool
	 */
	public function existSameCard($data) {
		if (!is_array($data)) {
			return false;
		}
		$where = [
			'identity_id' => $data['identity_id'],
			'name' => $data['name'],
			'idcard' => $data['idcard'],
			'phone' => $data['phone'],
			'cardno' => $data['cardno'],
			'bankcode' => $data['bankcode'],
			'card_type' => $data['card_type'],
		];
		return self::find()->where($where)->one();
	}
	/**
	 * 生成唯一标识
	 * @param  int $aid     客户应用id
	 * @param  int $user_id 客户端唯一id
	 * @return str          唯一id
	 */
	public function generatorIdentityId($aid, $user_id) {
		return 'A'.$aid . 'U' . $user_id;
	}
	/**
	 * 转换成新浪需要的卡类型格式
	 * @param  int $card_type_num 卡类型
	 * @return str 新浪卡类型
	 */
	public function getCardType($card_type_num) {
		$card_type_num= intval($card_type_num);
		$card_types = [
			1 => 'DEBIT', // 借记卡
			2 => 'CREDIT', // 信用卡
		];
		if( isset($card_types[$card_type_num]) ){
			return $card_types[$card_type_num];
		}else{
			return '';
		}
	}
	public static function getByRequestId($request_id) {
		return static::find()->where(['request_id' => $request_id])->limit(1)->one();
	}
}

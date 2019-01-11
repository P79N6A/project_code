<?php
/**
 * 畅捷协议支付
 * @author 孙瑞
 */
namespace app\models\cjt;

use app\models\Payorder;
use app\common\Func;
use yii\helpers\ArrayHelper;

class CjxyOrder extends \app\models\BasePay {

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public static function tableName() {
		return 'cj_xy_order';
	}

	public function rules() {
		return [
			[['aid', 'orderid', 'payorder_id', 'channel_id','cli_orderid', 'amount', 'productcatalog', 'productname', 'productdesc', 'identityid', 'orderexpdate', 'userip', 'cardno', 'idcard', 'name', 'create_time', 'modify_time'], 'required', "message" => "{attribute}不能为空"],
			[['aid', 'payorder_id', 'channel_id', 'amount', 'productcatalog', 'orderexpdate', 'version','status','bind_id','bankcardtype','has_send'], 'integer', "message" => "{attribute}应该为数字"],
			[['create_time', 'modify_time'], 'safe'],
			[['productname', 'cli_identityid', 'userip', 'cardno', 'idcard', 'error_code', 'error_msg'], 'string', 'max' => 50],
			[['productdesc'], 'string', 'max' => 200],
			[['other_orderid'], 'string', 'max' => 100],
			[['bankcode', 'identityid','phone'], 'string', 'max' => 20],
			[['orderid', 'name'], 'string', 'max' => 30],
			[['cli_orderid'], 'string', 'max' => 50],
			[['orderid'], 'unique', 'message' => '{attribute}:{value}已经存在'],
		];
	}

	public function attributeLabels() {
		return [
			'id' => '主键',
			'payorder_id' => '主订单id',
			'aid' => '应用id',
			'orderid' => '客户订单号',
			'channel_id' => '通道',
			'currency' => '交易币种:默认156',
			'amount' => '交易金额',
			'productcatalog' => '商品类别码',
			'productname' => '商品名称',
			'productdesc' => '商品描述',
			'identityid' => '用户标识',
			'orderexpdate' => '订单有效期时间 以分为单位',
			'userip' => '用户IP',
			'version' => '网页收银台版本',
			'cardno' => '银行卡序列号',
			'idcard' => '证件号',
			'name' => '持卡人姓名',
			'phone' => '持卡人姓名',
			'create_time' => '(内部)创建时间',
			'modify_time' => '(内部)最后修改时间',
			'status' => '(内部)0:未支付 1完成支付 10支付失败',
			'other_orderid' => '(内部)流水号',
			'error_code' => '(内部)返回错误码',
			'error_msg' => '(内部)返回错误描述'
		];
	}
	
	public function optimisticLock() {
		return "version";
	}
	public function getPayorder() {
		return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
	}
	
	// 保存子订单数据
	public function saveOrder($data) {
		//1 数据验证
		if (!is_array($data) || empty($data)) {
			return $this->returnError(false, "数据不能为空");
		}
		if (!ArrayHelper::getValue($data, 'orderid', '')) {
			return $this->returnError(false, "订单不能为空");
		}
		if (!ArrayHelper::getValue($data, 'aid', '')) {
			return $this->returnError(false, "应用id不能为空");
		}

		//2 数据拼装
		$postData = [
			'payorder_id' => ArrayHelper::getValue($data, 'payorder_id', ''), // 主订单Id
			'aid' => ArrayHelper::getValue($data, 'aid', ''), // 请求端编号
			'channel_id' => ArrayHelper::getValue($data, 'channel_id', ''), // 通道编号
			'orderid' => ArrayHelper::getValue($data, 'orderid', ''), // 客户订单号
			'identityid' => ArrayHelper::getValue($data, 'identityid', ''), // 用户标识
			'cardno' => ArrayHelper::getValue($data, 'cardno', ''), // 银行卡号
			'bankcode' => '', // 银行编码 未使用
			'idcard' => ArrayHelper::getValue($data, 'idcard', ''), // 身份证号
			'idcardtype' => '01', // 目前只支持身份证  先写死
			'name' => ArrayHelper::getValue($data, 'name', ''), // 持卡人姓名
			'phone' => ArrayHelper::getValue($data, 'phone', ''), // 持卡人手机号
			'productcatalog' => (string)ArrayHelper::getValue($data, 'productcatalog', ''), // 商品类别码
			'productname' => (string)ArrayHelper::getValue($data, 'productname', ''), // 商品名称
			'productdesc' => (string)ArrayHelper::getValue($data, 'productdesc', ''), // 商品描述,最长200位
			'orderexpdate' => intval(ArrayHelper::getValue($data, 'orderexpdate', 0)), // 订单有效期时间,以分为单位
			'amount' => intval(ArrayHelper::getValue($data, 'amount', 0)), // 交易金额,以"分"为单位的整型
			'userip' => ArrayHelper::getValue($data, 'userip', ''), // 用户IP
			'status' => ArrayHelper::getValue($data, 'status', Payorder::STATUS_INIT),
			'bankcardtype' => ArrayHelper::getValue($data, 'card_type', 0),
			'other_orderid' => '', // 第三方支付返回订单号
			'bind_id' => 0, // 银行卡绑定信息 未使用
			'version' => 0, // 版本号
		];
		$postData['cli_orderid'] = Func::toYeepayCode($postData['orderid'], $postData['channel_id']);
		$postData['cli_identityid'] = Func::toYeepayCode($postData['identityid'], $postData['channel_id']);
		$postData['create_time'] = $postData['modify_time'] = date('Y-m-d H:i:s');

		//3 数据校验
		if ($errors = $this->chkAttributes($postData)) {
			return $this->returnError(false, implode('|', $errors));
		}
		
		//4 数据保存
		$result = $this->save($postData);
		if (!$result) {
			return $this->returnError(false, implode('|', $this->errors));
		}
		return true;
	}

	// 生成支付页面链接地址
	public function getPayUrls($pay_controller='cjxy', $pay_type='') {
		return parent::getPayUrls($pay_controller, Payorder::PAY_CJXY);
	}

	/**
	 * 保存订单状态
	 * @param $status 订单状态
	 * @param string $other_orderid 回执订单号
	 * @param string $res 错误编码和信息
	 * @return bool
	 */
	public function saveStatus($status, $other_orderid = '', $res = []) {
		if (!empty($other_orderid)) {
			$this->other_orderid = (string)$other_orderid;
		}

		if(!empty(ArrayHelper::getValue($res,'1'))){
			$this->error_code = (string) ArrayHelper::getValue($res,'0','-1');
			$this->error_msg = (string) ArrayHelper::getValue($res,'1','未知错误');
		}
		$status = intval($status);
		$this->status = $status;
		$this->modify_time = date('Y-m-d H:i:s');
		$result = $this->save();
		return $result;
	}

	// 通过主键找到对应订单对象
	public function getById($id) {
		$id = intval($id);
		if (($id > 0) === false) {
			return null;
		}
		return static::findOne($id);
	}

	/**
	 * 修改短信发送频次
	 * @param array $data //['has_send']
	 * @return boolean
	 */
	public function updateOrder($data) {
		$this->modify_time = date('Y-m-d H:i:s');
		if(!empty($data['has_send'])){
			$this->has_send = $data['has_send'];
		}
		$result = $this->save();
		return $result;
	}

	/**
	* 找到指定时间段内状态处理中的订单
	* @return []
	*/
	public function getAbnorList($start_time,$end_time,$limit = 200){
		$start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
		$where = ['AND',
			['status' => [Payorder::STATUS_DOING]],
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time],
		];
		$dataList = self::find()->where($where)->limit($limit)->all();
		if (!$dataList) {
			return null;
		}
		return $dataList;
	}

	// 通过商户唯一订单号获取订单对象
	public function getByCliOrderId($cliOrderId){
		if (!$cliOrderId) {
			return null;
		}
		return static::find()->where(['cli_orderid' => $cliOrderId])->limit(1)->one();
	}
}

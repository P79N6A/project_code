<?php
/**
 * 接口调用日志表, 每次调用均会纪录响应结果
 * @todo 应该将xml也保留下来
 */
namespace app\models\jiufu;

/**
 *
 */
class JFApiLog extends \app\models\BaseModel {
	/**
	 * 出款条数和查询条数
	 */
	const REMIT_NUM = 200;
	const QUERY_NUM = 400;

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'jf_api_log';
	}

	/**
	 * 表关联关系
	 */
	public function getRemit() {
		return $this->hasOne(Remit::className(), ['id' => 'remit_id']);
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['remit_id', 'pre_status', 'start_time'], 'required'],
			[['remit_id', 'pre_status', 'status'], 'integer'],
			[['start_time', 'end_time'], 'safe'],
			[['api_name','order_status'], 'string', 'max' => 20],
			[['rsp_status'], 'string', 'max' => 50],
			[['rsp_status_text'], 'string', 'max' => 255],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'remit_id' => '出款id',
			'api_name' => '玖富接口',
			'pre_status' => '前一状态',
			'status' => '处理后状态',
			'rsp_status' => '玖富响应状态',//空为新加, RSP_TIMEOUT表示无响应
			'rsp_status_text' => '玖富响应信息',
			'order_status' => '玖富订单状态',
			'start_time' => '请求开始时间',
			'end_time' => '请求结束时间',
		];
	}
	/**
	 * 保存数据到db库中
	 * @param $remit_id
	 * @param $pre_status 出款表当前的状态
	 * @param $api_name
	 * @return bool
	 */
	public function saveData($remit_id, $pre_status, $api_name) {
		if (!$remit_id) {
			return false;
		}
		$dayTime = date('Y-m-d H:i:s');
		$row = [
			'remit_id' => $remit_id,
			'api_name' => $api_name,
			'pre_status' => $pre_status,
			'status' => 0,
			'rsp_status' => '',
			'rsp_status_text' => '',
			'start_time' => $dayTime,
			'end_time' => '0000-00-00 00:00:00',
		];
		$error = $this->chkAttributes($row);
		if ($error) {
			return $this->returnError(false, current($error));
		}
		$res = $this->save();
		return $res;
	}
	/**
	 * 回写响应结果
	 * $this操作数据
	 * @param $status 当前处理结果后的状态
	 * @param $rsp_status 玖富接口响应状态
	 * @param $rsp_status_text 玖富接口响应结果
	 * @param $order_status 玖富订单状态
	 */
	public function saveRspStatus($status, $rsp_status, $rsp_status_text, $order_status) {
		$this->status = $status;
		$this->rsp_status = (string)$rsp_status;
		$this->rsp_status_text = (string)$rsp_status_text;
		$this->order_status = (string)$order_status;
		$this->end_time = date('Y-m-d H:i:s');
		$result = $this->save();
		return $result;
	}
}

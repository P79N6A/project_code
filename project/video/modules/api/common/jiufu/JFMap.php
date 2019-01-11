<?php
namespace app\modules\api\common\jiufu;
use app\models\jiufu\Remit;
/**
 * 9富字段映射关系
 */
class JFMap {
	private $config;
	private $status_fails;
	public function __construct() {
		$this->config = include __DIR__ . "/config/map.php";
		$this->status_fails = include __DIR__ . "/config/status.php";
	}
	/**
	 * 获取银行编码
	 * @param  string $bank_name
	 * @return string
	 */
	public function getBankCode($bank_name) {
		//1 获取银行编码
		$bank_name = $this->getAliasBankName($bank_name);
		$bank_code = $this->getBankCodeByName($bank_name);
		return $bank_code;
	}
	/**
	 * 获取别名
	 * @param  [type] $bank_name [description]
	 * @return [type]            [description]
	 */
	private function getAliasBankName($bank_name) {
		$bank_name_alias = $this->config['bank_name_alias'];
		if ($bank_name && isset($bank_name_alias[$bank_name])) {
			return $bank_name_alias[$bank_name];
		} else {
			return '';
		}
	}
	/**
	 * 获取银行编码
	 * @param  [type] $bank_name [description]
	 * @return [type]            [description]
	 */
	private function getBankCodeByName($bank_name) {
		if (!$bank_name) {
			return '';
		}
		$bank_names = $this->config['bank_name_code'];
		if ($bank_name && isset($bank_names[$bank_name])) {
			return $bank_names[$bank_name];
		} else {
			return '';
		}
	}
	/**
	 * 获取城市编码
	 * @param  [] $city_name
	 * @return []
	 */
	public function getCityCode($city_name) {
		//1 获取银行编码
		$bank_name = $this->getAliasBankName($bank_name);
		$bank_code = $this->getBankCodeByName($bank_name);
		return $bank_code;
	}
	/**
	 * 获取借款目的
	 * @param  [] $city_name
	 * @return []
	 */
	public function getPurpose($purpose) {
		$purposes = $this->config['purpose'];
		if ($purpose && in_array($purpose, $purposes)) {
			return $purpose;
		} else {
			return 'F1199'; // 其它
		}
	}
	/**
	 * 获取出款原因
	 * @param  string $order_status 玖富的appStatus
	 * @return str
	 */
	public function getStatusTxt($order_status){
		if( !$order_status ){
			return '';
		}
		$status = &$this->config['order_status'];
		$status_txt = isset($status[$order_status]) ? $status[$order_status] : '';
		return $status_txt;
	}
	/**
	 * 解析出款状态
	 * @param  string $order_status 玖富的appStatus
	 * @return string  [STATUS_SUCCESS,STATUS_FAILURE,STATUS_DOING]
	 */
	public function parseStatus($order_status) {
		$is_success = in_array($order_status, $this->status_fails['STATUS_SUCCESS']);
		if ($is_success) {
			return Remit::STATUS_SUCCESS;
		}
		$is_fail = in_array($order_status, $this->status_fails['STATUS_FAILURE']);
		if ($is_fail) {
			return Remit::STATUS_FAILURE;
		}
		return Remit::STATUS_DOING;
	}
}
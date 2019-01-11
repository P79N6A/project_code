<?php
namespace app\modules\api\controllers\actions;
use yii\base\Action;

class BaseAction extends Action {
	/**
	 * 调用方法的参数
	 */
	public $params;

	/**
	 * 请求参数
	 */
	public $reqData;

	/**
	 * 当前应用id
	 */

	public $appData;

	/**
	 * 请求类型，根据不同进行响应
	 * json: 返回到开发平台并退出程序
	 * return: 返回结果
	 */
	public $reqType = 'json';

	// 初始化
	public function init() {

	}

	/**
	 * 运行的方法
	 */
	public function run() {
		$params = empty($this->params) ? [] : $this->params;
		return call_user_func_array([$this, $this->id], $params);
	}

	/**
	 * apicontroller中的同名方法
	 */
	protected function resp($res_code, $res_data) {
		if ($this->reqType == 'json') {
			// 此时会直接退出 里面加了exit;
			return $this->controller->resp($res_code, $res_data);
		} elseif ($this->reqType == 'return') {
			return ['res_code' => $res_code, 'res_data' => $res_data];
		} else {
			return ['res_code' => 1111, 'res_data' => '未设置reqType'];
		}
	}

	/**
	 * 转换数据格式到开发平台的形式
	 */
	protected function parseData($result) {
		if (empty($result)) {
			return $this->resp(2601, "未能获取到数据");
		}
		if ($result['error_code']) {
			return $this->resp($result['error_code'], $result['error_msg']);
		}
		return $this->resp(0, $result);
	}
}
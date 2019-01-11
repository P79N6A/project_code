<?php
namespace app\modules\api\common\jiufu;
/**
 * 玖富头信息
 */
class TransHead {
	/**
	 * 返回head信息
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function get($transSerialNo, $transType) {
		return [
			'sourceClient' => 63, // String  客户端编码 63
			'transSerialNo' => $transSerialNo, // String  流水号
			'transType' => $transType, // String  交易编码 //  oss 11101 | 工单 11301
			'transExeDate' => date('Y-m-d'), // String  日期（yyyy-MM-dd）
			'transExeTime' => date('H:i:s'), // String  时间（HH:mm:ss）
			'fromSort' => 1, //int 起始条数
			'toSort' => 2, //int 终止条数
			'retCode' => '', // String  返回码
			'retMsg' => '', // String  返回信息
		];
	}
	/**
	 * oss 的soap类
	 * @param  [type] $transSerialNo [description]
	 * @return [type]                [description]
	 */
	public function getOss($transSerialNo) {
		return $this->get($transSerialNo, '11101');
	}
	/**
	 * loan 的soap类
	 * @param  [type] $transSerialNo [description]
	 * @return [type]                [description]
	 */
	public function getLoan($transSerialNo) {
		return $this->get($transSerialNo, '11301');
	}
}
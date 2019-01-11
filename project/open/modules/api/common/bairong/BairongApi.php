<?php
namespace app\modules\api\common\bairong;
use Yii;
if (!class_exists('Core')) {
	include __DIR__ . "/config.php";
	include __DIR__ . "/com.bairong.api.class.php";
}
use app\common\Logger;
class BairongApi {
	private $core;
	public function __construct() {
		\CONFIG::init();
		$this->core = \Core::getInstance(\CONFIG::$account, \CONFIG::$password, \CONFIG::$apicode, \CONFIG::$querys);
	}
	public function index() {

	}
	/**
	 * 获取数据
	 * @param  [] $targetList 二维, 请求客户列表
	 * @return [] 响应列表, 按请求顺序
	 */
	public function get($targetList,$headerTitle) {
		//@todo
		/*$data = Yii::$app->cache->get('huaxiang');
		if($data){
			return $data;
		}*/
		// $headerTitle = [
		// 	'huaxiang' => [
		// 		"SpecialList_c", //信贷版特殊名单
		// 		"ApplyLoan", // 多次申请核查
		// 	],
		// ];
		if( !is_array($targetList) || !is_array($targetList[0]) ){
			return null;
		}

		$this->core->pushTargetList($targetList);
		$data = $this->core->mapping($headerTitle);
		Logger::dayLog('bairong',$targetList, $data);
		if( !is_array($data) ){
			return null;
		}
		//Yii::$app->cache->set('huaxiang', $data['huaxiang']);// @todo
		return $data;
	}


}

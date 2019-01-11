<?php
/**
 * 创蓝校验手机空号检测
 * @author 孙瑞
 * @actionIndex 数据请求入库
 */
namespace app\modules\api\controllers;

use YII;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\modules\api\common\ApiController;
use app\modules\api\common\mbcheck\MbcheckService;
use app\modules\api\common\mbcheck\MbcheckCode;

header("Content-type: text/html; charset=utf-8");
set_time_limit(0);

class MbcheckController extends ApiController{

	protected $server_id = 105;

	public function init() {
		parent::init();
	}

	/**
	 * 数据请求入库
	 * @param json $mobiles 手机号json串
	 * @param int $type 结果通知方式 1=>单条同步返回 2=>多条异步通知
	 * @param string $callback_url 回调地址
	 * @return json 成功+成功结果 | 失败+失败原因
	 */
	public function actionIndex(){
		// 获取请求业务数据
		$request_data = $this->reqData;
		Logger::dayLog('mbcheck','request/logging 记录请求数据'.json_encode($request_data));
		// 校验手机号数据
		$is_single = intval(ArrayHelper::getValue($request_data, 'type', 2));
		$callback_url = ArrayHelper::getValue($request_data, 'callback_url', '');
		$mobiles = ArrayHelper::getValue($request_data, 'mobiles', []);
		if(!$mobiles || !$callback_url){
			return $this->resp(105201, ['reason' => MbcheckCode::getCodeMsg(105201)]);
		}
		// 如果手机号数量大于一个自动执行多条异步通知流程
		if(count($mobiles)>1){
			$is_single = 2;
		}
		// 保存请求数据
		$oMbcheckService = new MbcheckService();
		$save_res = $oMbcheckService->saveMobiles(array_unique($mobiles), $this->appData['id'], $callback_url, $is_single == 1);
		$save_res_code = ArrayHelper::getValue($save_res, 'code', '请求参数数据不全');
		$save_res_msg = ArrayHelper::getValue($save_res, 'data', []);
		if($save_res_code){
			Logger::dayLog('mbcheck','request/error '.$save_res_msg);
			return $this->resp($save_res_code, ['reason' => $save_res_msg]);
		}
		Logger::dayLog('mbcheck','request/success 保存请求数据成功,'.json_encode($save_res_msg));
		return $this->resp(0, $save_res_msg);
	}
}
?>
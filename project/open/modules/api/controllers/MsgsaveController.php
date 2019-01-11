<?php
/**
 * 保存一亿元采集的短信数据
 * open.xianhuahua.com
 * @author 孙瑞
 */
namespace app\modules\api\controllers;

use YII;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\modules\api\common\ApiController;
use app\modules\api\common\msgsave\MsgSaveService;

header("Content-type: text/html; charset=utf-8");
set_time_limit(0);

class MsgsaveController extends ApiController{

	protected $server_id = 109;

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
		Logger::dayLog('msgsave','request: 记录请求数据'.json_encode($request_data));
		// 校验手机号数据
		$mobile = ArrayHelper::getValue($request_data, 'mobile', '');
		$msgList = ArrayHelper::getValue($request_data, 'msg_list', '');
		// 执行请求数据
		$oMbcheckService = new MsgSaveService();
		$saveRes = $oMbcheckService->runOne($mobile, $msgList);
		$saveCode = ArrayHelper::getValue($saveRes, 'code', '109000');
		$saveData = ArrayHelper::getValue($saveRes, 'data', '请求参数数据不全');
		if($saveCode){
			Logger::dayLog('msgsave','request: '.$saveData);
			return $this->resp($saveCode, $saveData);
		}
		Logger::dayLog('msgsave','request: 短信列表保存成功');
		return $this->resp(0, $saveData);
	}
}
?>
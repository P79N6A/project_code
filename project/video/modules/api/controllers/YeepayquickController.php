<?php
/**
 * 易宝一键支付接口
 * 内部错误码范围2000-2100
 */
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\common\Func;

class YeepayquickController extends ApiController
{

	/**
	 * 服务id号
	 */
	protected $server_id = 102;
	
	/**
	 * 初始化
	 */
	public function init(){
		parent::init();
	}
	
	// 使用actions 重用
	public function actions(){
		$common = [
			    'class' => '\app\modules\api\controllers\actions\YeepayquickAction',
		    	'reqData' => $this->reqData,
		    	'appData' => $this->appData,
		    	'reqType' => 'json',
		];
		
		return [
			// 绑定接口
			'payrequest' => $common,
			'getorder'   => $common,
		]; 
	}
}

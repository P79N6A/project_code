<?php
/**
 * 控制器父类
 */
namespace app\Controllers;
use Yii;
abstract class BaseController extends \app\common\BaseController
{
	public $layout='main';

	/**
	 * 显示结果信息
	 * @param $res_code 错误码0 正确  | >0错误
	 * @param $res_data      结果   | 错误原因
	 */
	protected function showMessage($res_code, $res_data, $type=null, $redirect=null){
		// 自动判断返回类型
		if( empty($type) ){
			$type = Yii::$app->request->getIsAjax() ? 'json' : 'html';
		}
		$type = strtoupper($type);

		// 返回结果: 统一json格式或消息提示代码
		switch($type){
			case 'JSON':
				return json_encode([
					'res_code' => $res_code,
					'res_data' => $res_data,
				]);
				break;

			default:
				$redirect = is_null($redirect) ? Yii::$app->request->getReferrer() : $redirect;
				return $this->render('/showmessage',[
					'res_code' => $res_code,
					'res_data' => $res_data,
					'redirect' => $redirect,
				]);
				break;
		}
	}
}

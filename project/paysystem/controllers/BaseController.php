<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
/**
 * api入口文件方法
 * 若增加接口从此类继承，并且执行parent::init()方法
 * 系统错误码范围 1-999
 * @author lijin lijin@xianhuahua.com
 */
class BaseController extends Controller
{
	/**
	 * 定义出错数据
	 */
	public $errinfo;
	
	/**
	 * 初始化操作
	 */
	public function init(){
		
	}
	/**
	 * getpost 返回get,post的数据，简单封装下
	 */
	protected function get($name = null, $defaultValue = null){
		return Yii::$app->request->get($name,$defaultValue);
	}
	protected function post($name = null, $defaultValue = null){
		return Yii::$app->request->post($name,$defaultValue);
	}
	protected function getParam($name, $defaultValue = null){
		$value = $this->get($name);
		if( is_null($value) ){
			return $this->post($name, $defaultValue);
		}else{
			return $value;
		}
	}
	
	// end getpost
	/**
	 * 返回结果，同时纪录错误原因
	 */
	protected function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
	
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

<?php
namespace app\common;

use Yii;
use yii\web\Controller;
use app\common\Func;
/**
 * 若增加接口从此类继承，并且执行parent::init()方法
 * 系统错误码范围 1-999
 * @author lijin lijin@xianhuahua.com
 */
abstract class BaseController extends Controller
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
		$v = Yii::$app->request->get($name,$defaultValue);
		$v = $v ? Func::new_trim($v) : $v;
		return $v;
	}
	protected function post($name = null, $defaultValue = null){
		$v = Yii::$app->request->post($name,$defaultValue);
		$v = Func::new_trim($v);
		return $v;
	}
	protected function getParam($name, $defaultValue = null){
		$v = $this->get($name);
		if( is_null($v) ){
			$v = $this->post($name, $defaultValue);
		}
		$v = $v ? Func::new_trim($v) : $v;
		return $v;
	}
	protected function isPost(){
		return Yii::$app->request -> isPost;
	}
	/**
	 * json 输出
	 * @param  int $res_code 错误码 0:无错误
	 * @param  any $res_data 响应内容
	 * @return string json数据
	 */
	public function jsonOut($res_code, $res_data){
		return json_encode( [
			'res_code' => $res_code,
			'res_data' => $res_data,
		]);
	}
	// end getpost
	/**
	 * 返回结果，同时纪录错误原因
	 */
	protected function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
}

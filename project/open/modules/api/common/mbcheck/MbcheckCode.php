<?php
/**
 * 创蓝校验手机空号检测
 * 错误码信息 / 状态码映射
 * @author 孙瑞
 */
namespace app\modules\api\common\mbcheck;

use yii\helpers\ArrayHelper;

class MbcheckCode{
	private static $errorCode = array(
		// 错误码说明
		105201 => '请求参数数据不全',
		105202 => '请求过于频繁,请稍后再试',
		105203 => '手机号已锁定,请联系管理员解锁',
		105204 => '检测结果已获取未失效,请勿重新获取',
		105205 => '手机号数据保存失败',
		105206 => '手机号数据保存成功',
		105210 => '创蓝未返回数据或返回数据错误',
		105211 => '请求失败/业务异常,需要重试',
		105212 => '保存创蓝检测结果失败',
		105213 => '请求表状态修改失败',
		105214 => '创蓝检测失败',
		105215 => '创蓝检测成功',
	);

	private static $chLanCode2CheckCode = array(
		// 状态码说明
		0 => 1,	// '空号'
		1 => 2, // '实号',
		2 => 3, // '停机',
		3 => 4, // '库无',
		4 => 5, // '沉默号',
		11 => 11, // '检测失败'
	);
	
	private static $statusCode = array(
		// 状态码说明
		0 => '未检测',
		1 => '空号',
		2 => '实号',
		3 => '停机',
		4 => '库无',
		5 => '沉默号',
		11 => '检测失败',
	);

	public static function returnCodeArr($code, $data=0){
		$data = $data ? $data : self::getCodeMsg($code);
		return ['code' => $code,'data' => $data];
	}
	public static function getCodeMsg($code){
		return ArrayHelper::getValue(self::$errorCode, $code);
	}
	public static function getCheckCode($code){
		return ArrayHelper::getValue(self::$chLanCode2CheckCode, $code);
	}
	public static function getStatusMsg($code){
		return ArrayHelper::getValue(self::$statusCode, $code);
	}
}
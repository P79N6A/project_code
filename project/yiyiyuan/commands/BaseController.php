<?php

namespace app\commands;

use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

abstract class BaseController extends Controller {

	/**
	 * 转发到子类
	 */
	public function actionIndex() {
		//1 参数验证
		$args	 = func_get_args();
		$method	 = $args[0];
		if (empty($method)) {
			echo 'error:empty method!';
			exit;
		}
		unset($args[0]);

		//2 调用方法
		if (!is_array($args)) {
			$args = [];
		}
		return call_user_func_array([$this, $method], $args);
	}

	public function getBeforeDays() {
		return 3;
	}

	/*
	 * 根据天数获取productsource
	 */

	public function getProductsource($loanInfo) {
		$productsource = '1';
		if (isset($loanInfo['business_type']) && in_array($loanInfo['business_type'], [5, 6])) {
			return '3';
		}
		if (isset($loanInfo['business_type']) && in_array($loanInfo['business_type'], [7])) {
			return '6';
		}
		if (isset($loanInfo['business_type']) && in_array($loanInfo['business_type'], [10])) {
			return '10';
		}
		if ($loanInfo['days'] == 7 && strtotime($loanInfo['end_date']) < strtotime('2018-08-12')) {
			return '1';
		}
		if ($loanInfo['days'] != 7 && strtotime($loanInfo['end_date']) < strtotime('2018-08-31')) {
			return '1';
		}
		switch ($loanInfo['days']) {
			case 7 : $productsource	 = '7';
				break;
			case 14 : $productsource	 = '14';
				break;
			case 21 : $productsource	 = '21';
				break;
			case 28 : $productsource	 = '28';
				break;
			case 56 : $productsource	 = '1';
				break;
			default:$productsource	 = '1';
		}
		return $productsource;
	}

	/*
	 * 根据天数获取productsource
	 */

	public function getPrefixByDays($loanInfo) {
		$prefix = '1_';
		if (isset($loanInfo['business_type']) && in_array($loanInfo['business_type'], [5, 6])) {
			return '3_';
		}
		
		if (isset($loanInfo['business_type']) && in_array($loanInfo['business_type'], [7])) {
			return '6_';
		}
        
        if (isset($loanInfo['business_type']) && in_array($loanInfo['business_type'], [10])) {
			return '10_';
		}

		if ($loanInfo['days'] == 7 && strtotime($loanInfo['end_date']) < strtotime('2018-08-12')) {
			return '1_';
		}
		
		if ($loanInfo['days'] != 7 && strtotime($loanInfo['end_date']) < strtotime('2018-08-31')) {
			return '1_';
		}
		
		switch ($loanInfo['days']) {
			case 7 : $prefix	 = '7_';
				break;
			case 14 : $prefix	 = '14_';
				break;
			case 21 : $prefix	 = '21_';
				break;
			case 28 : $prefix	 = '28_';
				break;
			case 56 : $prefix	 = '1_';
				break;
			default:$prefix	 = '1_';
		}
		return $prefix;
	}

	/**
	 * 日志记法
	 * 0: file
	 * 1... 内容自动以\t分隔, 数组自动var_export($c,true)转换成串
	 */
	protected function dayLog() {
		call_user_func_array(['\app\commonapi\Logger', 'dayLog'], func_get_args());
		return true;
	}

	/**
	 * 加密数据
	 */
	public function encrySign($data) {
		if (empty($data) || !is_array($data)) {
			return '';
		}
		foreach ($data as $key => &$val) {
			if ($key == 'username') {
				$val = str_replace(' ', '', $val);
			}
			$val = strval($val);
		}
		ksort($data);
		$signstr = http_build_query($data);
		//系统分配的密匙
		$key	 = \Yii::$app->params['app_key'];
		//签名
		$sign	 = md5($signstr . $key);
		return $sign;
	}

}

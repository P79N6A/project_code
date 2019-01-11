<?php

namespace app\models\jxl;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Curl;
use app\common\Logger;
use app\common\ArrayGroupBy;

/**
 * 统一对外开放接口
 */
class JxlApi 
{
	private $ip_url;
	private $open_url;
	public static $jxl_arr;
	public static $mohe_arr;
	public static $shh_arr;
	const JXL_SOURCE = 1;
	const MOHE_SOURCE = 2;
	const SHH_SOURCE = 3;

	public function __construct()
	{
		self::$jxl_arr = [1,2,];
		self::$mohe_arr = [6,];
		self::$shh_arr = [4,];

		if (SYSTEM_PROD) {
            $this->ip_url = "http://10.139.36.194";
            $this->open_url = "http://open.xianhuahua.com";
        } else {
            $this->ip_url = 'http://182.92.80.211:8091';
            $this->open_url = 'http://182.92.80.211:8091';
        }
	}
	
	/**
	 * [getDetail 获取通讯录详单数据]
	 * @param  [type] $json_url [description]
	 * @return [array]           [description]
	 */
	public function getDetail($json_url)
	{	
		$real_data = [];
		try {
			$curl = new Curl();
			$res_data = $curl->dataGet($json_url);
			if (isset($res_data['http_status']) && $res_data['http_status'] != 200) {
				return [];
			}
	
			$data = json_decode($res_data['result'],true);
			if (!$data) {
				return [];
			}
			$calls_data = $data['raw_data']['members']['transactions'][0]['calls'];

			if (empty($calls_data)) {
				return [];
			}
			$group_by_fields = [
	            'other_cell_phone' => function($value){
            		$isMatched = preg_match('/^1[2-9][0-9]\d{8}$/', $value, $matche_phone);
					if ($isMatched > 0 && strlen($value) >= 5) {
						return $value;
					}
	            }
	        ];
	        $group_by_value = [
	            'other_cell_phone',
	            'call_times' => function($data){
	                    return count($data);
	                },
	            'use_time' => function($data){
						$use_time = ArrayHelper::getColumn($data,'use_time');
	            		return array_sum($use_time);
	            },
	            'max_time' => function($data){
						$use_time = ArrayHelper::getColumn($data,'start_time');
	            		return max($use_time);
	            },
	           	'min_time' => function($data){
						$use_time = ArrayHelper::getColumn($data,'start_time');
	            		return min($use_time);
	            },
	        ];
        	$real_data = ArrayGroupBy::groupBy($calls_data, $group_by_fields, $group_by_value);
        	return $real_data;
		} catch (\Exception $e) {
			Logger::dayLog("JxlApi", 'getDetail', '获取通讯录详单数据失败',$json_url,$e->getMessage());
		}
		return $real_data;
	}

	/**
	 * [getDetailPhones 获取通讯录详单数据]
	 * @param  [type] $json_url [description]
	 * @return [array]           [description]
	 */
	public function getDetailPhones($jxl_info)
	{	
		$real_data = [];
		try {
			if (empty($jxl_info)) {
				Logger::dayLog('getDetail','jxl_info is empty',$jxl_info);
				return []; 
			}
			$json_url = $this->getDetailUrl($jxl_info);
			if (empty($json_url)) {
				Logger::dayLog('getDetail','json_url is empty',$jxl_info);
				return []; 
			}
			$data = $this->curlGetJxl($json_url);
			if (empty($data)) {
				return [];
			}
			$calls_data = $data['raw_data']['members']['transactions'][0]['calls'];
			if (empty($calls_data)) {

				return [];
			}
			foreach ($calls_data as $value) {
				if (isset($value['other_cell_phone']) && !empty($value['other_cell_phone'])) {
					$phone = ArrayHelper::getValue($value, 'other_cell_phone', '');
					// 号码过滤
					$real_num = $this->numberRule($phone);
					if (empty($real_num)) {
						continue;
					}
	            	// 去重
	            	if (in_array($real_num,$real_data)) {
	            		continue;
	            	}
	            	if ($real_num) {
	            		$real_data[] = $real_num;
	            	}
				}
			}
        	return $real_data;
		} catch (\Exception $e) {
			Logger::dayLog("JxlApi", 'getDetailPhones', '获取通讯录详单数据失败',$json_url,$e->getMessage());
		}
		return $real_data;
	}

	// 验证手机号
	private function checkPhone($number)
	{
		$isMatched = preg_match('/^(\+?86-?)?1[2-9][0-9]\d{8}$/', $number, $matche_phone);
        if ($isMatched > 0) {
            if (substr($number,0,3) == '+86') {
                $number = trim(substr($number,3));
            }
            if (substr($number,0,2) == '86') {
                $number = trim(substr($number,2));
            }
            return (string)trim($number,'-');
        }
        return '';
	}
	// 验证电话号
	private function checkTel($number)
	{
		$isMatched = preg_match('/^800-?[0-9]{7}|^400-?[0-9]{7}|^0\d{2,3}-?\d{7,8}$|^([0-9]{3,4}-)?[0-9]{7,8}$/', $number, $matche_phone);
        if ($isMatched > 0) {
            return (string)$number;
        }
        return '';
	}

	public function getReport($jxl_info)
	{
		$real_data = [];
		try {
			if (empty($jxl_info)) {
				Logger::dayLog('getReport','jxl_info is empty',$jxl_info);
				return []; 
			}
			$json_url = $this->getReportUrl($jxl_info);
			if (empty($json_url)) {
				Logger::dayLog('getReport','json_url is empty',$jxl_info);
				return []; 
			}
			$source = ArrayHelper::getValue($jxl_info, 'source', 0);
			if ($source == 0) {
				Logger::dayLog('getReport','source is abnormal',$jxl_info);
				return []; 
			}
			$data = $this->curlGetJxl($json_url);
			if (empty($data)) {
				return [];
			}
			$phone = ArrayHelper::getValue($jxl_info, 'phone');
			if (empty($phone)) {
				return [];
			}
			if (in_array($source,self::$jxl_arr)) {
				$real_data = $this->analysisJxl($data,$phone,$source);
			} elseif (in_array($source,self::$mohe_arr)) {
				$real_data = $this->analysisMohe($data,$phone,$source);
			}
        	return $real_data;
		} catch (\Exception $e) {
			Logger::dayLog('getReport', 'analysis report file',$jxl_info,$e->getMessage());
		}
		return $real_data;
	}
	//分析聚信立
	private function analysisJxl($data,$user_phone,$source)
	{
		$jxl_tag = [];
		$jxl_data = [];
		$contact_list = $data['JSON_INFO']['contact_list'];
		if (empty($contact_list)) {
			return $jxl_tag;
		}
		foreach ($contact_list as $contact) {
			$tag = ArrayHelper::getValue($contact, 'needs_type','未知');
			$phone = ArrayHelper::getValue($contact, 'phone_num', '');
			// 过滤有效号码
			$rule = $this->numberRule($phone);
			// '未知','其他'标签不存
			if ($tag == '未知' || empty($rule) || $tag == '其它') {
				continue;
			}
			if (isset($jxl_tag[$phone])) {
				$tag = $jxl_tag[$phone]['tag'].','.$tag;
			}
			$set_data = ['tag' => $tag,'source' => $source];
			$jxl_tag[$phone] = $set_data;
			#   单条更新插入
			// $setOne = $this->setOneSsdb($phone,$set_data);
		}
		$jxl_data = ['tag_list' => $jxl_tag];
		return $jxl_data;
	}

	//分析魔盒
	private function analysisMohe($data,$user_phone,$source)
	{
		$mohe_tag = [];
		$mohe_data = [];
		$return_data = isset($data['returndata']) ? $data['returndata']:[];
		if (empty($return_data)) {
			return $mohe_data;
		}
		// 通讯录详单分析
		$contact_list = $return_data['all_contact_detail'];
		if (empty($contact_list)) {
			return $mohe_data;
		}
		$time = date('Y-m-d H:i:s');
		foreach ($contact_list as $contact) {
			$tag = ArrayHelper::getValue($contact, 'contact_type',null);
			$phone = ArrayHelper::getValue($contact, 'contact_number', '');
			// 过滤有效号码
			$rule = $this->numberRule($phone);
			// 'null' 其他标签不存
			if (empty($tag) || empty($rule) || $tag == '其它') {
				continue;
			}
			if (isset($mohe_tag[$phone])) {
				$tag = $mohe_tag[$phone]['tag'].','.$tag;
			}
			$set_data = ['tag' => $tag,'source' => $source];
			$mohe_tag[$phone] = $set_data;
			#   单条更新插入
			// $setOne = $this->setOneSsdb($phone,$set_data);
		}
		// 目标用户评分
		$contact_analysis = [
			'behavior_score' => json_encode(ArrayHelper::getValue($return_data, 'behavior_score', '')) ,
			'contact_blacklist_analysis' => json_encode(ArrayHelper::getValue($return_data, 'contact_blacklist_analysis', '')),
			'carrier_consumption_stats' => json_encode(ArrayHelper::getValue($return_data, 'carrier_consumption_stats', '')),
			'carrier_consumption_stats_per_month' => json_encode(ArrayHelper::getValue($return_data, 'carrier_consumption_stats_per_month', '')),
		];
		$mohe_data = ['contact_analysis'=>$contact_analysis,'tag_list'=> $mohe_tag];
		// var_dump($mohe_tag['contact_analysis']);die;
		#   单条更新插入
		// $setOne = $this->setOneSsdb($user_phone,json_encode($user_data));
		return $mohe_data;
	}
	// 获取报告地址
	public function getReportUrl($jxl_info)
	{
		$report_url = ArrayHelper::getValue($jxl_info, 'url', '');
		if (empty($report_url)) {
			return '';
		}
		$report_url = '/'.trim($report_url,'/');
		$now_time = date('Y-m-d');
        $out_time = date("Y-m-d",strtotime('+3 days',strtotime($jxl_info['create_time'])));
        $json_url = $this->open_url.$report_url;
        //解析详单三天后移出open
        if ($out_time < $now_time) {
            $json_url = $this->ip_url.$report_url;
        }
        return $json_url;
	}

	// 获取详单地址
	public function getDetailUrl($jxl_info)
	{
		$detail_url = ArrayHelper::getValue($jxl_info, 'url', '');
		if (empty($detail_url)) {
			return '';
		}
		$detail_url = substr($detail_url, 0,-5);
		$detail_url = '/'.trim($detail_url.'_detail.json','/');
		$now_time = date('Y-m-d');
        $out_time = date("Y-m-d",strtotime('+3 days',strtotime($jxl_info['create_time'])));
        $json_url = $this->open_url.$detail_url;
        //解析详单三天后移出open
        if ($out_time < $now_time) {
            $json_url = $this->ip_url.$detail_url;
        }
        return $json_url;
	}

	private function curlGetJxl($json_url)
	{
		$curl = new Curl();
		$res_data = $curl->dataGet($json_url);
		if (isset($res_data['http_status']) && $res_data['http_status'] != 200) {
			Logger::dayLog('curlGetJxl','get_data fail',$json_url,$res_data);
			return [];
		}

		$data = json_decode($res_data['result'],true);
		if (!$data) {
			Logger::dayLog('curlGetJxl','json_decode fail',$json_url,$res_data);
			return [];
		}
		return $data;
	}

	private function numberRule($num)
	{
		$real_num = '';
		if (empty($num) || strlen($num) <= 5) {
			return $real_num;
		}
		if (substr($num, 0, 3) == '400') {
    		return $real_num;
    	}

    	$real_num = $this->checkTel($num);
    	if (!empty($real_num)) {
    		return $real_num;
    	}
    	$real_num = $this->checkPhone($num);
    	if (!empty($real_num)) {
    		return $real_num;
    	}
    	return $real_num;
	}

	// 单条插入ssdb
	private function setOneSsdb($phone,$data)
	{
		$new_data = [];
		$set_data = [];
		$old_data = Yii::$app->ssdb->get($phone);
		if ($old_data) {
			$new_data = json_decode($old_data);
		}
		$new_data[] = $data;
		// $ok = Yii::$app->ssdb->del($phone);die;
		$ok = Yii::$app->ssdb->set($phone,json_encode($new_data));
		return $ok;
	}


}

<?php

namespace app\models\anti;

use Yii;
use app\common\Common;
use app\common\Logger;

/**
 * 统一对外开放接口
 */
class AfBaseApi extends AntiBaseModel
{
	private static $contact_analysis;
	public function __construct()
	{	
		self::$contact_analysis = new AfContactAnalysisList();
	}
	/**
	 * [saveContact 保存用户魔盒信息]
	 * @return [type] [description]
	 */
	public function saveContact($data,$jxl_info)
	{
		#1, 获取魔盒数据
		$contact_data = $this->getContactdata($jxl_info);
		if (empty($contact_data)) {
		#2, 没有则直接写入	
			$res = $this->insertContact($data,$jxl_info);
		} else {
		#3, 有则直接更新
			$res = $contact_data->updateDate($data);
		}
		return $res;
 	}

 	public function getContactdata($jxl_info)
 	{
 		$phone = $this->getValue($jxl_info,'phone');
 		if (!$phone) {
 			return null;
 		}
 		$contact_data = self::$contact_analysis->getContact(['phone'=>$phone]);
 		return $contact_data;
 	}

 	public function insertContact($data,$jxl_info)
 	{	
 		if (empty($data) || empty($jxl_info)) {
 			return false;
 		}
 		$saveData = [
 			'phone' => $this->getValue($jxl_info,'phone'),
 			'source' => $this->getValue($jxl_info,'source'),
			'behavior_score' => $this->getValue($data,'behavior_score'),
		    'contact_blacklist_analysis' => $this->getValue($data,'contact_blacklist_analysis'),
		    'carrier_consumption_stats' => $this->getValue($data,'carrier_consumption_stats'),
		    'carrier_consumption_stats_per_month' => $this->getValue($data,'carrier_consumption_stats_per_month'),
 		];
	    $res = self::$contact_analysis->saveData($saveData);
	    return $res;
 	}
}
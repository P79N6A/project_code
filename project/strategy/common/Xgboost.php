<?php
namespace app\common;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Xgboost模型接口
 * RETURN xg_prob
 */
class Xgboost
{
	private $xgboost_url;
	private $xgboost_auth_key;
	public function __construct(){
		$this->xgboost_auth_key = Yii::$app->params['xgboost']['auth_key'];
		if (SYSTEM_PROD) {
			$this->xgboost_url = Yii::$app->params['xgboost']['url'];
		} else {
			$this->xgboost_url = '182.92.80.211:8888/api/xgboost';
		}
		
		
	}
	 
	/**
	 * 接口入口（对外）
	 */
	public function xgboostOpen(&$prome_datas){
		# set xgboost params
		$xgboostParams = $this->getXgboostParams($prome_datas);
		# set sign 
		$xgboostParams['sign'] = $this->getSign($xgboostParams);
		#query XgboostApi
		$xgboost_score = $this->queryXgboostApi($xgboostParams);
		return $xgboost_score;
	}
	/**
	 * [setXgboostSign 数据加密]
	 */
    private function getSign($data)
    {
    	$str = '';
    	ksort($data);
    	foreach ($data as $k => $v) {
    		$str .= $k.'='.$v.'&';
    	}
    	$str = rtrim($str,'&');
    	$sign = md5(substr(md5($str),0,30).$this->xgboost_auth_key);
    	return $sign;
    }
	/**
	 * 解密数据与验证签名信息
	 */
	public function getXgboostParams(&$XgboostDatas){
		$params_map = [
				'PROME_V4_SCORE',
				'multi_p2p_p_class_7',
				'loan_all',
				'history_bad_status',
				'addr_phones_nodups',
				'addr_collection_count',
				'addr_tel_count',
				'com_r_duration_mavg',
				'com_c_total_mavg',
				'com_use_time',
				'com_count',
				'com_month_answer_duration',
				'com_mobile_people',
				'com_night_duration_mavg',
				'com_max_tel_connect',
				'vs_duration_match',
				'same_phone_num',
				'shutdown_max_days',
				'advertis_weight_loss_p',
				'express_aeavy_number_p',
				'harass_weight_loss_p',
				'house_agent_aeavy_number_lable',
				'cheat_aeavy_number_sign',
				'taxi_aeavy_number_sign',
				'ring_weight_loss_sign',
			];
		$xgboost_params = (new YArray)->getByKeys($XgboostDatas, $params_map, 0);
		$xgboost_params = array_map('floatval',$xgboost_params);
		return $xgboost_params;
	}
	/**
	 * [queryXgboostApi 请求API]
	 * @param  [type] $postData [description]
	 * @return [type]           [description]
	 */
	public function queryXgboostApi($postData){

		$curl = new Curl();
        $xgboost_json = $curl->postForm($this->xgboost_url,$postData);
        Logger::dayLog('queryXgboostApi', 'postdata', $postData, $xgboost_json,$this->xgboost_url);
        if (!$xgboost_json) {
        	return -111;
        }
        $xgboost_array = json_decode($xgboost_json,true);
        if (empty($xgboost_array)) {
        	return -111;
        }
        $res_code = ArrayHelper::getValue($xgboost_array,'code','111');
        if ($res_code != 0) {
        	return -111;
        }
        $xg_prob = ArrayHelper::getValue($xgboost_array, 'data.0.1',-111);
        return round($xg_prob,5);
	}
}
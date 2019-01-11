<?php
namespace app\common;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Ganoderma孚临接口
 * RETURN ganoderma_score
 */
class Ganoderma
{
	private $ganoderma_url;
	private $oApiClientCrypt;
	public function __construct(){
		$this->ganoderma_url = Yii::$app->params['ganoderma']['url'];
		$this->oApiClientCrypt =new ApiClientCrypt();
	}
	 
	/**
	 * 接口入口（对外）
	 */
	public function ganodermaOpen($prome_datas){
		# set ganoderma params
		$ganodermaParams = [
			'name' => ArrayHelper::getValue($prome_datas,'realname',''),
			'idCardno' => ArrayHelper::getValue($prome_datas,'identity',''),
			'mobile' => ArrayHelper::getValue($prome_datas,'mobile',''),
		];
		# send  query
		$ganoderma_json = $this->oApiClientCrypt->sent($this->ganoderma_url, $ganodermaParams);
		# encode data
		$result_fulin = $this->oApiClientCrypt->parseResponse($ganoderma_json);
		Logger::dayLog('queryganodermaApi', 'ganodermaParams', $ganodermaParams, 'result_fulin',$result_fulin,'url', $this->ganoderma_url);
		if (isset($result_fulin['res_code']) && $result_fulin['res_code'] != 0) {
        	return -111;
        }
        $ganoderma_data = ArrayHelper::getValue($result_fulin,'res_data',[]);
        if (empty($ganoderma_data)) {
        	return -111;
        }
        $ganoderma_score = ArrayHelper::getValue($ganoderma_data,'score',-111);
		return (int)$ganoderma_score;
	}
}
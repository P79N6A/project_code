<?php
namespace app\common;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 腾讯
 * RETURN ganoderma_score
 */
class AntifrauApi
{
    private $request_url;
    private $oApiClientCrypt;
    public function __construct(){
        $this->request_url = Yii::$app->params['request']['url'];
        $this->oApiClientCrypt =new ApiClientCrypt();
    }

    /**
     * 接口入口（对外）
     */
    public function requestOpen($prome_datas){
        $requestParams = [
            'user_id'           => ArrayHelper::getValue($prome_datas, "user_id", ''),
            'name'              => ArrayHelper::getValue($prome_datas,'realname',''),
            'idCardno'          => ArrayHelper::getValue($prome_datas,'identity',''),
            'mobile'            => ArrayHelper::getValue($prome_datas,'mobile',''),
        ];
        # send  query
        $request_json = $this->oApiClientCrypt->sent($this->request_url, $requestParams);
        # encode data
        $result_fulin = $this->oApiClientCrypt->parseResponse($request_json);
        Logger::dayLog('antifrauApi', 'antifrauParams', $requestParams, 'result_fulin',$result_fulin,'url', $this->request_url);
        if (isset($result_fulin['res_code']) && $result_fulin['res_code'] != 0) {
            return -111;
        }
        $request_data = ArrayHelper::getValue($result_fulin,'res_data',[]);
        if (empty($request_data)) {
            return -111;
        }
        $request_score = ArrayHelper::getValue($request_data,'riskScore',-111);
        return (int)$request_score;
    }
}
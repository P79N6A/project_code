<?php
namespace app\common;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Xgboost模型接口
 * RETURN xg_prob
 */
class FdXgBoostApi
{
    private $xgboost_url;
    private $xgboost_auth_key;
    public function __construct(){
        $this->xgboost_auth_key = Yii::$app->params['reloanxg']['auth_key'];
        if (SYSTEM_PROD) {
            $this->xgboost_url = Yii::$app->params['reloanxg']['url'];
        } else {
            $this->xgboost_url = '182.92.80.211:8888/api/reloanxg';
        }


    }

    /**
     * 接口入口（对外）
     */
    public function fdboostOpen(&$prome_datas){
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
            "success_num",
            "wst_dlq_sts",
            "PROME_V4_SCORE",
            "multi_all_p_class_30",
            "multi_p2p_p_class_30",
            "multi_small_p_class_30",
            "user_total",
            "realadl_tot_freject_num",
            "addr_count",
            "addr_tel_count",
            "com_c_rank",
            "com_month_num",
            "com_call_duration",
            "com_month_people",
            "com_days_call",
            "com_hours_answer_davg",
            "com_offen_connect",
            "com_valid_mobile",
            "vs_duration_match",
            "last3_answer",
            "same_phone_num",
            "phone_register_month",
            "total_duration",
            "tot_phone_num",
            "shutdown_duration_count",
            "shutdown_max_days",
            "advertis_aeavy_number_p",
            "advertis_weight_loss_label",
            "express_weight_loss_label",
            "express_weight_loss_p",
            "express_weight_loss_sign",
            "harass_aeavy_number_p",
            "harass_weight_loss_label",
            "harass_weight_loss_p",
            "house_agent_weight_loss_p",
            "cheat_aeavy_number_p",
            "cheat_weight_loss_sign",
            "company_tel_aeavy_number_p",
            "taxi_weight_loss_label",
            "taxi_weight_loss_p",
            "insurance_aeavy_number_lable",
            "insurance_aeavy_number_p",
            "ring_aeavy_number_p",
            "ring_weight_loss_sign"
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
        Logger::dayLog('queryFdboostApi', 'postdata', $postData, $xgboost_json,$this->xgboost_url);
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
<?php
/**
 * 信息采集H5加载
 * 功能：第三方通过拼接URL，加载开放平台信息采集H5。
 * 测试地址：
 *      http://ducredit.baidu.com/sat/platform/xxwcrawlerhp?ducredit_appid=20170720000000000213&ducredit_salt=1122&ducredit_ticket=NjgzNjE1MDA1NTg0MTc0OTM%3D&ducredit_sign=d31951cbffef9787eeaaaf9ebbd428b1
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/23
 * Time: 15:32
 */
namespace app\modules\api\common\eduauth;
use app\common\Logger;
use app\models\edu\DucreditTicket;
use yii\helpers\ArrayHelper;

class Ocollection
{
    private $oEduApi;
    public function __construct()
    {
        $this->oEduApi = new EduApi();
    }

    /**
     * 判断参数是否为空
     * @param $params
     * @return bool
     */
    public function checkParams($params)
    {
        if (empty($params)){
            return false;
        }
        $check_params = ['ducredit_ticket', 'ducredit_h5_callback_url'];
        foreach($check_params as $value){
            if (empty($params[$value])){
                Logger::dayLog("edu/Ocollection", '为空参数',$value.json_encode($params));
                return false;
            }
        }
        return true;
    }

    /**
     * 获取配置文件
     * @return mixed
     * @throws \Exception
     */
    public function getConfig()
    {
        $config = $this->oEduApi->getConfig();
        if (!is_array($config) || empty($config)){
            Logger::dayLog("edu/obtainEdu", "配置文件不存在", json_encode($config));
            return false;
        }
        return $config;
    }

    public function mosaicUrl($data_set, $config_data)
    {
        $url_params = [
            'ducredit_appid'                => ArrayHelper::getValue($data_set, 'ducredit_appid'), //Ducredit appid
            'ducredit_salt'                 => ArrayHelper::getValue($config_data, 'ducredit_salt'), //随机数
            'ducredit_sign'                 => $this->sign($data_set, $config_data), //签名
            'ducredit_ticket'               => ArrayHelper::getValue($data_set, 'ducredit_ticket'), //TICKET
            'ducredit_h5_callback_url'      => ArrayHelper::getValue($data_set, 'ducredit_h5_callback_url'),//回调地址
        ];
        $url_params = http_build_query($url_params);
        $http_url = ArrayHelper::getValue($config_data, 'h5_url');
        return $http_url.$url_params;
    }

    /**
     * 加密算法
     * @param $data_set
     * @param $config_data
     */
    private function sign($data_set, $config_data)
    {
        $appid = ArrayHelper::getValue($data_set, 'ducredit_appid');
        $salt = ArrayHelper::getValue($config_data, 'ducredit_salt');
        $ticket = ArrayHelper::getValue($data_set, 'ducredit_ticket');
        $key = ArrayHelper::getValue($config_data, 'key');
        return md5($appid.$salt.$ticket.$key);
    }
}
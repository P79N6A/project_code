<?php
/**
 * 获取数据访问Token（/sat/api/tokenhandler）
 * 功能：第三方使用DUCREDIT_TICKET获取数据访问TOKEN。
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/23
 * Time: 16:11
 */
namespace app\modules\api\common\eduauth;

use app\common\Logger;
use app\models\edu\DucreditTicket;
use app\models\edu\QueryTicket;
use yii\helpers\ArrayHelper;

class Oquerys
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
        $check_params = ['ducredit_ticket'];
        foreach($check_params as $value){
            if (empty($params[$value])){
                Logger::dayLog("edu/Oquerys", '为空参数',$value.json_encode($params));
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

    public function curlData($data_set, $config_data)
    {
        if (empty($data_set) || empty($config_data)){
            return false;
        }
        $sign = $this->sign($data_set, $config_data);
        $url = ArrayHelper::getValue($config_data, 'token_url');
        $data_aa = [
            'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid'),
            'ducredit_salt'         => ArrayHelper::getValue($config_data, 'ducredit_salt'),
            'ducredit_sign'         => $sign,
            'ducredit_ticket'       => ArrayHelper::getValue($data_set, 'ducredit_ticket'),
        ];
        $result = $this->oEduApi->Post($data_aa, $url);
        $result = json_decode($result, true);
        if (empty($result)){
            return false;
        }
        return $result;
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
        $key = ArrayHelper::getValue($config_data, 'key');
        $ticket = ArrayHelper::getValue($data_set, 'ducredit_ticket');
        return md5($appid.$salt.$ticket.$key);
    }

    /**
     * 保存数据
     * @param $data_set
     * @param $params
     * @return bool
     */
    public function saveData($data_set, $params)
    {
        if (empty($data_set)){
            return false;
        }

        $data = ArrayHelper::getValue($data_set, 'data');
        $save_data = [
            't_id'              => ArrayHelper::getValue($data, 'id'), //ID',
            'ducredit_appid'    => ArrayHelper::getValue($params, 'ducredit_appid'), //学历号',
            'ducredit_token'    => ArrayHelper::getValue($data, 'ducredit_token'), //爬取信息',
            't_create_time'     => ArrayHelper::getValue($data, 'create_time'), //应用信息时间',
            't_update_time'     => ArrayHelper::getValue($data, 'update_time'), //时间',
            'log_id'            => ArrayHelper::getValue($data_set, '_bd_log_id'), //
            'is_used'           => ArrayHelper::getValue($data, 'is_used'), //时间',
        ];
        if (ArrayHelper::getValue($data_set, 'errno') != 0) {
            $save_data['errmsg'] = json_encode($data_set);
        }
        $oQueryTicket = new QueryTicket();
        $ret = $oQueryTicket->saveData($save_data);
        if (!$ret){
            Logger::dayLog("edu/obtainEdu", '保存失败', json_encode($params));
            return false;
        }
        return true;
    }

    /**
     * 返回数据
     * @param $data_set
     * @return array
     */
    public function returnSuccessData($data_set)
    {
        $data_set = ArrayHelper::getValue($data_set, 'data');
        return [
            'id'        => ArrayHelper::getValue($data_set, 'id'),
            'ducredit_token'        => ArrayHelper::getValue($data_set, 'ducredit_token'),
            'create_time'        => ArrayHelper::getValue($data_set, 'create_time'),
            'update_time'        => ArrayHelper::getValue($data_set, 'update_time'),
            'is_used'        => ArrayHelper::getValue($data_set, 'is_used'),
            'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid'),

        ];
    }
}
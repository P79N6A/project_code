<?php
/**
 * Ducredit Ticket获取
 * 功能：第三方获取平台Ticket，用于信息提交、信息查询。
 * 测试地址：
        curl -i -X POST -d "ducredit_appid=20170720000000000213&ducredit_salt=1888&ducredit_sign=d382fda2457cea9d3b87b2672e9f2207" http://st01-rdqa-dev341-mouyantao.epc.baidu.com:8884/sat/api/duticket
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 10:45
 */
namespace app\modules\api\common\eduauth;

use app\common\Http;
use app\common\Logger;
use app\models\edu\DucreditTicket;
use yii\helpers\ArrayHelper;

class ObtainEdu
{
    private $oEduApi;
    public function __construct()
    {
        $this->oEduApi = new EduApi();
    }

    /**
     * 参数验证是否为空
     * @param $params
     * @return bool
     */
    public function checkParams($params)
    {
        $must_data = ['ducredit_appid'];
        if (empty($params)){
            return false;
        }
        if (!empty($must_data)){
            foreach($must_data as $value){
                if (empty($params[$value])){
                    Logger::dayLog("edu/obtainEdu", "为空参数:", $value.'--'.json_encode($params));
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 通过ducredit_appid获取最新的一条记录
     * @param $ducredit_appid
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getRecord($ducredit_appid)
    {
        if (empty($ducredit_appid)){
            return false;
        }
        $oDucreditTicket = new DucreditTicket();//表类
        return $oDucreditTicket->getRecord($ducredit_appid);
    }

    /**
     * 返回成功的数据
     * @param $data_set
     * @return array
     */
    public function returnSuccessMsg($data_set)
    {
        return [
            'expire'        => ArrayHelper::getValue($data_set, 'expire'),
            'ducredit_ticket'        => ArrayHelper::getValue($data_set, 'ducredit_ticket'),
            'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid'),
        ];
    }

    /**
     * 保存数据
     * @param $data_set
     * @return DucreditTicket|bool
     */
    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $oDucreditTicket = new DucreditTicket();//表类
        $save_data = [
            'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid', 0), //学历号',
        ];
        $save_state = $oDucreditTicket->saveData($save_data);
        if (!$save_state){
            Logger::dayLog("edu/obtainEdu", "保存失败", json_encode($save_data));
            return false;
        }
        return $oDucreditTicket;
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

    /**
     * 请求第三方
     * @param $data_set
     * @param $config_data
     * @return bool|mixed
     */
    public function curlData($data_set, $config_data)
    {
        if (empty($data_set) || empty($config_data)){
            return false;
        }
        $sign = $this->sign($data_set, $config_data);
        $url = ArrayHelper::getValue($config_data, 'ticket_url');
        $data_aa = [
            'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid'),
            'ducredit_salt'         => ArrayHelper::getValue($config_data, 'ducredit_salt'),
            'ducredit_sign'         => $sign,
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
        return md5($appid.$salt.$key);
    }

    /**
     * 更新数据
     * @param $oDucreditTicket
     * @param $data_set
     * @return bool
     */
    public function updateData($oDucreditTicket, $data_set)
    {
        //查找失败
        if (ArrayHelper::getValue($data_set, 'errno') != 0){
            $update_data['errmsg'] = json_encode($data_set);
        }else {
            //查找成功
            $http_data = ArrayHelper::getValue($data_set, 'data');
            $update_data = [
                'expire' => ArrayHelper::getValue($http_data, 'expire'),
                'ducredit_ticket' => ArrayHelper::getValue($http_data, 'ducredit_ticket'),
            ];
        }
        $res = $oDucreditTicket -> updateData($update_data);
        if (!$res){
            Logger::dayLog("edu/obtainEdu", "更新数据", json_encode($update_data));
            return false;
        }
        return true;
    }
}
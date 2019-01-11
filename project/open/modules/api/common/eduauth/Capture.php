<?php
/**
 * 功能：第三方使用DUCREDIT_TICKET查询抓取状态
 * 测试地址：
        curl -i  -X POST -d "ducredit_appid=20170720000000000213&ducredit_salt=1863&ducredit_ticket=OTQyNDE1MDA2MDgwNzM1MTA%3D&ducredit_sign=a944106d22dbd8a3f0ee73513e533373&ducredit_token= a944106d22dbd8a3f0ee73513e533373" https://ducredit.baidu.com/sat/api/xxwcrawlersdk
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/23
 * Time: 17:05
 */
namespace app\modules\api\common\eduauth;
use app\common\Logger;
use app\models\edu\DucreditTicket;
use app\models\edu\GrabTicket;
use yii\helpers\ArrayHelper;

class Capture
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
        $check_params = ['ducredit_appid', 'ducredit_ticket', 'ducredit_token'];
        foreach($check_params as $value){
            if (empty($params[$value])){
                Logger::dayLog("edu/Capture", '为空参数',$value.json_encode($params));
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
            Logger::dayLog("edu/Capture", "配置文件不存在", json_encode($config));
            return false;
        }
        return $config;
    }

    /**
     * 保存数据
     * @param $params
     * @return GrabTicket|bool
     */
    public function saveData($params)
    {
        if (empty($params)){
            return false;
        }
        $oGrabTicket = new GrabTicket();//表类
        $save_data = [
            'ducredit_appid'        => ArrayHelper::getValue($params, 'ducredit_appid', 0), //学历号',
            'ducredit_ticket'       => ArrayHelper::getValue($params, 'ducredit_ticket', 0),
            'ducredit_token'        => ArrayHelper::getValue($params, 'ducredit_token', 0),
        ];
        $save_state = $oGrabTicket->saveData($save_data);
        if (!$save_state){
            Logger::dayLog("edu/Capture", "保存失败", json_encode($save_data));
            return false;
        }
        return $oGrabTicket;
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
        $url = ArrayHelper::getValue($config_data, 'grab_url');
        $data_aa = [
            'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid'),
            'ducredit_salt'         => ArrayHelper::getValue($config_data, 'ducredit_salt'),
            'ducredit_ticket'         => ArrayHelper::getValue($data_set, 'ducredit_ticket'),
            'ducredit_token'         => ArrayHelper::getValue($data_set, 'ducredit_token'),
            'ducredit_sign'         => $sign,
        ];
        $result = $this->oEduApi->Post($data_aa, $url);
        /*
        $result = '{
    "errno": 0,
    "errmsg": "Success",
    "data": {
        "task_status": 5,
        "task_info": "",
        "open_id": "7bf6d692a247d0db846e6bf9b7f52278",
        "task_data": [
            {
                "real_name": "111",
                "sex": "男",
                "birth_date": "1989年06月24日",
                "nation": "汉族",
                "id_card": "37068111140012",
                "school_name": "大连理工大学",
                "level": "本科",
                "specialty": "软件工程(日语强化",
                "length_of_schooling": "4",
                "edu_type": "普通",
                "edu_form": "普通全日制",
                "department": "",
                "class_name": "软日0901",
                "enrollment_time": "2009年09月01日",
                "leave_school_time": "2013年06月26日",
                "status": "不在籍(毕业",
                "input_img": "111",
                "output_img": "111",
                "branch_school": ""
            }
        ],
        "app_info": {
            "ducredit_appid": "20170720100000001639"
        }
    },
    "_bd_log_id": "90e383e9d9f26134debc17fb00c94b16"
}';
        */
        Logger::dayLog('edu/capture_data', '抓取数据：',$result);
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
        $ticket = ArrayHelper::getValue($data_set, 'ducredit_ticket');
        $token = ArrayHelper::getValue($data_set, 'ducredit_token');
        $key = ArrayHelper::getValue($config_data, 'key');

        return md5($appid.$salt.$ticket.$token.$key);
    }

    /**
     * 更新数据
     * @param $grabTicket
     * @param $params
     * @return bool
     */
    public function updateData($grabTicket, $params)
    {
        if (empty($params)){
            return false;
        }
        $data_set = ArrayHelper::getValue($params, 'data');
        $task_data = ArrayHelper::getValue($data_set, 'task_data');
        $task_data = ArrayHelper::getValue($task_data, '0');
        $update_data = [
            'real_name'						=> ArrayHelper::getValue($task_data, 'real_name', ''), //COMMENT '姓名',
            'sex'							=> ArrayHelper::getValue($task_data, 'sex', ''), //性别',
            'birth_date'					=> ArrayHelper::getValue($task_data, 'birth_date', ''), //出生日期',
            'nation'						=> ArrayHelper::getValue($task_data, 'nation', ''), //民族',
            'id_card'						=> ArrayHelper::getValue($task_data, 'id_card', ''), //证件号码',
            'school_name'					=> ArrayHelper::getValue($task_data, 'school_name', ''), //学校名称',
            'level'							=> ArrayHelper::getValue($task_data, 'level', ''), //层次',
            'specialty'						=> ArrayHelper::getValue($task_data, 'specialty', ''), //专业',
            'length_of_schooling'			=> ArrayHelper::getValue($task_data, 'length_of_schooling', ''), //学制',
            'edu_type'						=> ArrayHelper::getValue($task_data, 'edu_type', ''), //学历类别',
            'edu_form'						=> ArrayHelper::getValue($task_data, 'edu_form', ''), //学习形式',
            'branch_school'					=> ArrayHelper::getValue($task_data, 'branch_school', ''), //分院',
            'department'					=> ArrayHelper::getValue($task_data, 'department', ''), //系（所、函授站）',
            'class_name'					=> ArrayHelper::getValue($task_data, 'class_name', ''), //班级',
            'student_id'					=> ArrayHelper::getValue($task_data, 'student_id', ''), //学号',
            'enrollment_time'				=> ArrayHelper::getValue($task_data, 'enrollment_time', ''), //入学日期',
            'leave_school_time'				=> ArrayHelper::getValue($task_data, 'leave_school_time', ''), //离校日期',
            'status'						=> ArrayHelper::getValue($task_data, 'status', ''), //学籍状态',
            //'input_img'						=> json_encode(ArrayHelper::getValue($task_data, 'input_img', '')), //录取照片（base64编码的图片）',
            //'output_img'					=> json_encode(ArrayHelper::getValue($task_data, 'output_img', '')), //学历照片（base64编码的图片）',
            'log_id'						=> ArrayHelper::getValue($data_set, '_bd_log_id', ''), //,
            'errmsg'                        => ArrayHelper::getValue($data_set, 'errmsg', ''),
            'app_info'                      => json_encode(ArrayHelper::getValue($data_set, 'app_info', '')),
            'open_id'                       => ArrayHelper::getValue($data_set, 'open_id', ''),
            'task_status'                   => (string)ArrayHelper::getValue($data_set, 'task_status', ''),
            'task_info'                     => ArrayHelper::getValue($data_set, 'task_info', ''),
        ];
        if (ArrayHelper::getValue($params, 'errno') != 0){
            $update_data['errmsg'] = json_encode($params);
        }
        //var_dump($update_data);
        $res = $grabTicket->updateData($update_data);
        if (!$res){
            Logger::dayLog("edu/Capture", "更新数据失败", json_encode($update_data));
            return false;
        }
        return true;
    }

    /**
     * 成功返回数据
     * @param $params
     * @return array|bool
     */
    public function returnSuccess($params)
    {
        if (empty($params)){
            return false;
        }
        $data_set = ArrayHelper::getValue($params, 'data');
        $data_set = ArrayHelper::getValue($data_set, 'task_data');
        $data_set = ArrayHelper::getValue($data_set, '0');
        $save_data = [
            'ducredit_appid'				=> ArrayHelper::getValue($data_set, 'ducredit_appid', ''), //Ducredit appid',
            'ducredit_ticket'				=> ArrayHelper::getValue($data_set, 'ducredit_ticket', ''), //TICKET',
            'ducredit_token'				=> ArrayHelper::getValue($data_set, 'ducredit_token', ''), //访问TOKEN，从3获取，一个TOKEN只能使用一次',
            'real_name'						=> ArrayHelper::getValue($data_set, 'real_name', ''), //COMMENT '姓名',
            'sex'							=> ArrayHelper::getValue($data_set, 'sex', ''), //性别',
            'birth_date'					=> ArrayHelper::getValue($data_set, 'birth_date', ''), //出生日期',
            'nation'						=> ArrayHelper::getValue($data_set, 'nation', ''), //民族',
            'id_card'						=> ArrayHelper::getValue($data_set, 'id_card', ''), //证件号码',
            'school_name'					=> ArrayHelper::getValue($data_set, 'school_name', ''), //学校名称',
            'level'							=> ArrayHelper::getValue($data_set, 'level', ''), //层次',
            'specialty'						=> ArrayHelper::getValue($data_set, 'specialty', ''), //专业',
            'length_of_schooling'			=> ArrayHelper::getValue($data_set, 'length_of_schooling', ''), //学制',
            'edu_type'						=> ArrayHelper::getValue($data_set, 'edu_type', ''), //学历类别',
            'edu_form'						=> ArrayHelper::getValue($data_set, 'edu_form', ''), //学习形式',
            'branch_school'					=> ArrayHelper::getValue($data_set, 'branch_school', ''), //分院',
            'department'					=> ArrayHelper::getValue($data_set, 'department', ''), //系（所、函授站）',
            'class_name'					=> ArrayHelper::getValue($data_set, 'class_name', ''), //班级',
            'student_id'					=> ArrayHelper::getValue($data_set, 'student_id', ''), //学号',
            'enrollment_time'				=> ArrayHelper::getValue($data_set, 'enrollment_time', ''), //入学日期',
            'leave_school_time'				=> ArrayHelper::getValue($data_set, 'leave_school_time', ''), //离校日期',
            'status'						=> ArrayHelper::getValue($data_set, 'status', ''), //学籍状态',
            'input_img'						=> ArrayHelper::getValue($data_set, 'input_img', ''), //录取照片（base64编码的图片）',
            'output_img'					=> ArrayHelper::getValue($data_set, 'output_img', ''), //学历照片（base64编码的图片）',
            'log_id'						=> ArrayHelper::getValue($data_set, 'log_id', ''), //,
            //'modify_time'					=> '', //更新时间',
            'create_time'					=> date("Y-m-d H:i:s"), //创建时间',
            'errmsg'                        => ArrayHelper::getValue($data_set, 'errmsg', ''),
            'open_id'                       => ArrayHelper::getValue($data_set, 'open_id', ''),
            'task_status'                   => ArrayHelper::getValue($data_set, 'task_status', ''),
            'task_info'                     => ArrayHelper::getValue($data_set, 'task_info', ''),
        ];
        return $save_data;
    }
}
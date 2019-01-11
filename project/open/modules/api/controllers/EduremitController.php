<?php
/**
 * 学历认证入口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 9:51
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\edu\DucreditTicket;
use app\modules\api\common\ApiController;
use app\modules\api\common\eduauth\Capture;
use app\modules\api\common\eduauth\Ceduremit;
use app\modules\api\common\eduauth\EduApi;
use app\modules\api\common\eduauth\ObtainEdu;
use app\modules\api\common\eduauth\Ocollection;
use app\modules\api\common\eduauth\Oquerys;
use yii\helpers\ArrayHelper;


class EduremitController extends ApiController
{

    /**
     * 服务id号
     */
    protected $server_id = 100;
    private $oeduapi;  //引入公共类

    public function init() {
        parent::init();
        $this->oeduapi = new EduApi();
    }

    /**
     *  一、Ducredit Ticket获取
     *  功能：第三方获取平台Ticket，用于信息提交、信息查询。
     */
    public function actionObtain()
    {
        //初始化逻辑类
        $oOtainEdu = new ObtainEdu();
        //1.获取数据
        $postData = $this->reqData;
        Logger::dayLog("edu/obtainEdu", '接收的数据', json_encode($postData));
        //2获取配置文件
        $config_data = $oOtainEdu->getConfig();
        if ($config_data === false){
            return $this->resp($this->server_id.'8002', $this->errorMsg('8002'));
        }
        //获取配置文件ducredit_appid
        $postData['ducredit_appid'] = ArrayHelper::getValue($config_data, 'appid');
        //$ducredit_appid = ArrayHelper::getValue($postData, 'ducredit_appid');
        //3.验证数据
        $checkParams = $oOtainEdu->checkParams($postData);
        if ($checkParams !== true){
            return $this->resp($this->server_id.'1001', $this->errorMsg('1001'));
        }
        /*
        //4.查询是否存在记录
        $record_info = $oOtainEdu->getRecord($ducredit_appid);
        //5.存在返回记录
        if (!empty($record_info)){
            $success_data = $oOtainEdu->returnSuccessMsg($record_info);
            return $this->resp(0, $success_data);
        }
        */

        //6.记录数据
        $save_info = $oOtainEdu->saveData($postData);
        if ($save_info === false){
            return $this->resp($this->server_id.'8001', $this->errorMsg('8001'));
        }

        //7.请求接口
        $http_state = $oOtainEdu->curlData($postData, $config_data);
        if ($http_state === false){
            return $this->resp($this->server_id.'8003', $this->errorMsg('8003'));
        }
        //8.更新表记录数据
        $update_state = $oOtainEdu -> updateData($save_info, $http_state);
        if ($update_state === false){
            return $this->resp($this->server_id.'8004', $this->errorMsg('8004'));
        }
        //9.失败
        if (ArrayHelper::getValue($http_state, 'errno') != 0){
            return $this->resp(ArrayHelper::getValue($http_state, 'errno'), ArrayHelper::getValue($http_state, 'errmsg'));
        }
        //10.成功返回数据
        if (!empty($save_info)){
            return $this->resp(0, $oOtainEdu->returnSuccessMsg($save_info));
        }
    }

    /**
     *  二、信息采集H5加载
     *  功能：第三方通过拼接URL，加载开放平台信息采集H5。
     */
    public function actionCollection()
    {
        //实例逻辑类
        $Ocollection = new Ocollection();
        //1.获取数据
        $postData = $this->reqData;
        /*
        $postData = [
            'ducredit_salt'  => '1122',
            'ducredit_ticket' => 'OTIxNTE1MTQ1Mjg5MzU1NjQ1Ng==',
            'ducredit_h5_callback_url' => 'http:://baidu.com',
        ];
        */
        Logger::dayLog("edu/Ocollection", '接收的数据', json_encode($postData));

        //2.获取配置文件
        $config_data = $Ocollection->getConfig();
        if ($config_data === false){
            return $this->resp($this->server_id.'8002', $this->errorMsg('8002'));
        }
        //获取配置文件ducredit_appid
        $postData['ducredit_appid'] = ArrayHelper::getValue($config_data, 'appid');

        //3.验证参数
        $checkParams = $Ocollection -> checkParams($postData);
        if ($checkParams === false){
            return $this->resp($this->server_id.'1001', $this->errorMsg('1001'));
        }
        //4.验证ducredit_ticket是否过期
        $ticket_data = $this->oeduapi->checkTicket(ArrayHelper::getValue($postData, 'ducredit_ticket'));
        if ($ticket_data === false){
            return $this->resp($this->server_id.'8005', $this->errorMsg('8005'));
        }

        //5.拼接url
        $http_data = $Ocollection->mosaicUrl($postData, $config_data);
        return $this->resp(0, ['msg'=>$http_data]);
    }

    /**
     *  三、获取数据访问Token（/sat/api/tokenhandler）
     *  功能：第三方使用DUCREDIT_TICKET获取数据访问TOKEN
     */
    public function actionQuerystatus()
    {
        //实例逻辑类
        $Oquerys = new Oquerys();
        //1.获取数据
        $postData = $this->reqData;
        Logger::dayLog("edu/Oquerys", '接收的数据', json_encode($postData));
        /*
        $postData = [
            'ducredit_ticket'   => 'Mjg1MzE1MTQ1MzE5MDE1NjU3MA==',
        ];
        */

        //2.获取配置文件
        $config_data = $Oquerys->getConfig();
        if ($config_data === false){
            return $this->resp($this->server_id.'8002', $this->errorMsg('8002'));
        }
        $postData['ducredit_appid'] =  ArrayHelper::getValue($config_data, 'appid');


        //3.验证参数
        $checkParams = $Oquerys -> checkParams($postData);
        if ($checkParams === false){
            return $this->resp($this->server_id.'1001', $this->errorMsg('1001'));
        }
        //4.验证ducredit_ticket是否过期
        $ticket_data = $this->oeduapi->checkTicket(ArrayHelper::getValue($postData, 'ducredit_ticket'));
        if ($ticket_data === false){
            return $this->resp($this->server_id.'8005', $this->errorMsg('8005'));
        }
        //5.请求第三方
        $http_data = $Oquerys->curlData($postData, $config_data);

        if ($http_data === false){
            return $this->resp($this->server_id.'8003', $this->errorMsg('8003'));
        }
        //6.保存数据
        $save_data = $Oquerys->saveData($http_data, $postData);
        if ($save_data === false){
            return $this->resp($this->server_id.'8001', $this->errorMsg('8001'));
        }
        //7.返回成功数据
        return $this->resp(0, $Oquerys->returnSuccessData($http_data));
    }

    /**
     * 四、抓取数据获取
     * 功能：第三方使用DUCREDIT_TICKET查询抓取状态
     */
    public function actionCapture()
    {
        //实例逻辑类
        $Ocapture = new Capture();
        //1.获取数据
        $postData = $this->reqData;
        /*
        $postData = [
            'ducredit_salt'     => '1863',
            'ducredit_ticket'   => 'OTIxNTE1MTQ1Mjg5MzU1NjQ1Ng==',
            'ducredit_token'    => 'abd534e3e23985b1477bf9313b4e877d',
        ];
        */

        Logger::dayLog("edu/Capture", '接收的数据', json_encode($postData));

        //2.获取配置文件
        $config_data = $Ocapture->getConfig();
        if ($config_data === false){
            return $this->resp($this->server_id.'8002', $this->errorMsg('8002'));
        }
        $postData['ducredit_appid'] = ArrayHelper::getValue($config_data, 'appid');


        //3.验证参数
        $checkParams = $Ocapture -> checkParams($postData);
        if ($checkParams === false){
            return $this->resp($this->server_id.'1001', $this->errorMsg('1001'));
        }
        //4.验证ducredit_ticket是否过期
        $ticket_data = $this->oeduapi->checkTicket(ArrayHelper::getValue($postData, 'ducredit_ticket'));
        if ($ticket_data === false){
            return $this->resp($this->server_id.'8005', $this->errorMsg('8005'));
        }

        //5.记录数据
        $save_info = $Ocapture->saveData($postData);
        if ($save_info === false){
            return $this->resp($this->server_id.'8001', $this->errorMsg('8001'));
        }

        //7.请求第三方
        $http_data = $Ocapture->curlData($postData, $config_data);
        if ($http_data === false){
            return $this->resp($this->server_id.'8003', $this->errorMsg('8003'));
        }
        //8.更新表记录数据
        $update_state = $Ocapture -> updateData($save_info, $http_data);
        if ($update_state === false){
            return $this->resp($this->server_id.'8004', $this->errorMsg('8004'));
        }
        
        //9.返回数据
        if (ArrayHelper::getValue($http_data, 'errno') != 0){
            return $this->resp(ArrayHelper::getValue($http_data, 'errno'), ArrayHelper::getValue($http_data, 'errmsg'));
        }
        return $this->resp(0, $Ocapture->returnSuccess($http_data));
    }


    /**
     * 返回错误信息
     * @param $code
     * @param string $msg
     * @return mixed|string
     */
    private function errorMsg($code, $msg = '')
    {
        $msg_data = [
            '0'			=> '成功',
            '1001'		=> '参数错误',
            '1002'		=> '数据库错误',
            '1003'		=> 'salt验证失败',
            '3001'		=> '未登录',
            '4006'		=> '签名错误',
            '4007'		=> '超过单个用户最大容量',
            '4202'		=> 'APPNAME为空',
            '4203'		=> 'APPNAME已存在',
            '4204'		=> '邮箱为空',
            '4205'		=> '邮箱已注册',
            '4206'		=> '电话为空',
            '4207'		=> '电话已经注册',
            '4208'		=> '冲突的APPID',
            '4209'		=> '冲突的user id',
            '4210'		=> '数据库错误',
            '4211'		=> '管理员为空',
            '4213'		=> 'APPID分配失败',
            '4214'		=> 'APP注册失败',
            '4215'		=> '未注册过API',
            '4216'		=> '短信发送失败',
            '4217'		=> '验证码非法',
            '4218'		=> '没有可更新字段',
            '4219'		=> '没更新',
            '4220'		=> '编译失败',
            '4221'		=> '本地信息处理失败',
            '4222'		=> '消息添加失败',
            '4223'		=> 'IP LIST 非法',
            '4224'		=> '邮件发送失败',
            '4225'		=> '公司未注册',
            '4225'		=> 'APP不存在',
            '4226'		=> '获取TICKET失败',
            '4227'		=> 'TICKET不合法',
            '4228'		=> 'TICKET 已使用',
            '4229'		=> 'NONE TICKET EXTRA',
            '4230'		=> 'INVALID ENCRYPT KEY',
            '4300'		=> 'FETCH CRAWLER TOKEN FAIL',
            '4301'		=> 'FETCH CRAWLER OPENID FAIL',
            '4302'		=> 'SUBMIT JOB FAIL',
            '4303'		=> 'QUERY JOB FAIL',
            '5100'		=> '访问过于频繁,请稍后再试',
            '6001'		=> 'db connection error',
            '6002'		=> 'DB_QUERY_ERROR',
            '6003'		=> 'DB_SELECT_ERROR',
            '6004'		=> 'DB_SELECTCOUNT_ERROR',
            '6005'		=> 'DB_UPDATE_ERROR',
            '6006'		=> 'DB_DELETE_ERROR',
            '6007'		=> 'DB_INSERT_ERROR',
            '6008'		=> 'DB_START_TRANSACTION_ERROR',
            '6009'		=> 'DB_COMMIT_TRANSACTION_ERROR',
            '6010'		=> 'DB_ROLLBACK_TRANSACTION_ERROR',
            '7001'		=> 'REDIS_CONFIG_ERR',
            '7002'		=> 'REDIS_CONNECT_FAIL',
            '7003'		=> 'REDIS_CHECK_ERR',
            '7004'		=> 'REDIS_CALL_EXCEPTION',
            '8001'		=> '保存数据失败',
            '8002'      => '配置文件不存在',
            '8003'      => '请求失败',
            '8004'      => '更新失败',
            '8005'      => 'TICKET失效',
        ];
        if (empty($msg)){
            $msg = empty($msg_data[$code]) ? "未知错误" : $msg_data[$code];
        }
        return ['msg' => $msg];
    }


}
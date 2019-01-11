<?php


namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\Request;
use app\models\Result;
use app\modules\api\logic\RegLogic;
use app\modules\api\common\BaseApi;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\yyy\RegisterEvent;
use app\models\yyy\User;
use app\models\yyy\UserExtend;
class RegController extends ApiController
{
    const REG = 1;
    // public function init()
    // {

    // }

    /**
     * 注册第一模块请求
     */
    public function actionRegfirst()
    {

    }

    //注册决策
    public function actionRegdecision()
    {
        $postdata = $this->postdata;
        Logger::dayLog('reg_event', 'postdata', $postdata);
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata','数据异常',$postdata);
            return $this->resp(0, 'Pass');
        }
        $user_id = ArrayHelper::getValue($postdata, 'user_id');
        $postdata['from'] = self::REG;
        //记录请求
        $request = new Request();
        $request_id = $request->addRequest($postdata);
        if (!$request_id) {
            Logger::dayLog('addRequest', 'addRequest',$request->errors,$postdata);
            return $this->resp(0, 'Pass');
        }
        $postdata['request_id'] = $request_id;
        $regLogic = new RegLogic;
        $regInfo = $regLogic->getRegInfo($postdata);
        if (!$regInfo) {
            Logger::dayLog('error', 'regInfo', $regLogic->info,$postdata);
            return $this->resp(0, 'Pass');
        }
        $reg_info = $regLogic->info;
        $reg_data = [
            'request_id' => $request_id,
            'process_code' => 'xhh_reg',
            'params_data' => $reg_info,
        ];
        //发送请求并接收返回结果
        $api = new \app\modules\api\common\BaseApi();
        $result = $api->sendRequest($reg_data);
        if (empty($result)) {
            Logger::dayLog('error', 'result', '结果为空：',$result,$postdata);
            return $this->resp(0, 'Pass');
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('error', 'result','决策异常:',$result,$postdata);
            return $this->resp(0, 'Pass');
        }
        $ret_info = json_encode($result, JSON_UNESCAPED_UNICODE);
        //记录结果
        $res_data = ArrayHelper::getValue($result, 'RESULT');
        $res_info = [
            'user_id' => $user_id,
            'request_id' => $request_id,
            'from' => self::REG,
            'res_info' => $ret_info,
            'res_status' => $res_data
        ];
        $record_res = new Result();
        $res = $record_res->addResInfo($res_info);
        if (!$res) {
            Logger::dayLog('error', 'result','结果记录失败:',$result,$postdata);
            return $this->resp(0, 'Pass');
        }
        switch ($res_data) {
            case 1:
                return $this->resp(0, 'Pass');
                break;
            case 2:
                return $this->resp(1, 'Manual ');
                break;
            case 3:
                return $this->resp(2, 'Reject');
                break;
            default:
                return $this->resp(0, 'Pass');
        }
    }
}
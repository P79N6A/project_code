<?php

/**
 * 注册第一模块请求
 */
namespace app\modules\sfapi\controllers;

use app\common\Logger;
use app\models\Request;
use app\models\Result;
use app\modules\sfapi\logic\RegLogic;
use app\modules\sfapi\common\BaseApi;
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
    //注册决策
    public function actionRegdecision()
    {
        $postdata = $this->postdata;
        Logger::dayLog('reg_event', 'postdata', $postdata);
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata','数据异常',$postdata);
            return $this->error('20001', '数据异常',$postdata,1);
        }
        $postdata['from'] = Request::REG;
        //验证请求唯一性 如存在则返回上一结果
        $request = new Request();
        $req = $request->getReqInfo($postdata);
        if ($req) {
            Logger::dayLog('addRequest', 'addRequest',$postdata);
            return $this->success($postdata, '', $req);
        }
        //记录业务请求
        $request_id = $request->saveRequest($postdata);
        if (!$request_id) {
            Logger::dayLog('addRequest', 'addRequest',$request->errors,$postdata);
            return $this->error('20002', $request->errors,$postdata,1);
        }
        $postdata['request_id'] = $request_id;
        $regLogic = new RegLogic;
        $regInfo = $regLogic->getRegInfo($postdata);
        if (!$regInfo) {
            Logger::dayLog('error', 'regInfo', $regLogic->info,$postdata);
            return $this->error('20003', $regLogic->info,$postdata,1);
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
            return $this->error('20004', '决策结果为空',$postdata,1);
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('error', 'result','决策异常:',$result,$postdata);
            return $this->error('20005', '决策异常',$postdata,1);
        }
        //记录结果
        $res_data = ArrayHelper::getValue($result, 'RESULT');
        $record_res = new Result();
        $res = $record_res->saveRes($postdata, $result);
        if (!$res) {
            Logger::dayLog('error', 'result','结果记录失败:',$result,$postdata);
            return $this->error('20006', '结果记录失败',$postdata,1);
        }
        return $this->success($postdata, $reg_info, $res_data);
    }
}
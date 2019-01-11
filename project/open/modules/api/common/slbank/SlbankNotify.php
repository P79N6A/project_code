<?php
/**
 * 数立银行流水通知方法
 * @author 孙瑞
 * @command
 */
namespace app\modules\api\common\slbank;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\slbank\SlbankRequest;
use app\models\slbank\SlbankNotify AS SlbankNotifyModel;

class SlbankNotify {
    private $oSlbankRequest;
    private $oSlbankNotify;

    public function __construct() {
        $this->oSlbankRequest = new SlbankRequest;
        $this->oSlbankNotify = new SlbankNotifyModel;
    }

    // 获取单条需要通知的数据
    public  function runOne($requestid) {
        //根据请求id去查询通知表 然后开始通知
        $oneData = $this->oSlbankNotify->getOne($requestid,'requestid');
        if (!$oneData) {
            Logger::dayLog('slbank/Notify', 'error 数据获取失败');
            return false;
        }
        $ups = $this->oSlbankNotify->lockNotify([$oneData->id]);
        if (!$ups) {
            Logger::dayLog('slbank/Notify', 'error 数据加锁失败');
            return false;
        }
        return $this->doNotify($oneData->attributes);
    }

    // 获取需要通知的数据
    public  function runAll() {
        $dataList = $this->oSlbankNotify->getNotifyList();
        return $this->runNotify($dataList);
    }

    // 执行通知操作
    private function runNotify($dataList) {
        if (!$dataList) {
            Logger::dayLog('slbank/Notify', 'logging 未获取到需要通知的数据列表');
            return 0;
        }
        // 锁定状态为通知中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oSlbankNotify->lockNotify($ids);
        if (!$ups) {
            Logger::dayLog('slbank/Notify', 'error 数据加锁失败');
            return 0;
        }
        // 循环执行通知任务
        $num = 0;
        foreach ($dataList as $oNotify) {
            $result = $this->doNotify($oNotify->attributes);
            if (!$result) {
                continue;
            }
            $num++;
        }
        logger::dayLog('slbank/Notify','notifyDone 数据通知成功条数:'.$num.' 成功数据ID为:'.json_encode($ids));
        return $num;
    }

    // 执行通知操作
    private function doNotify($notifyData) {
        $requestid = ArrayHelper::getValue($notifyData, 'requestid');
        $notifyid = ArrayHelper::getValue($notifyData, 'id');
        if(!$requestid || !$notifyid){
            return false;
        }
        // 获取回调地址
        $oRequest = $this->oSlbankRequest->getOne($requestid);
        if (!$oRequest || !$oRequest->callback_url) {
            $this->oSlbankNotify->changeNotifyStatus($notifyid, SlbankNotifyModel::STATUS_FAILURE, '没有回调地址');
            return false;
        }
        // 开始通知业务端
        Logger::dayLog('slbank/Notify', 'logging 开始将id为'.$requestid.'的请求的采集结果通知业务端');
        $data = [
            'request_id' => $oRequest->id,
            'user_id' => $oRequest->user_id,
            'status' => $oRequest->request_status,
        ];
        // 加密通知数据
        $encryptData = $this->encryptData($oRequest->aid,$data);
        if(!$encryptData){
            $this->oSlbankNotify->changeNotifyStatus($notifyid, SlbankNotifyModel::STATUS_FAILURE, '加密通知数据失败');
            return false;
        }
        // 发送通知数据
        $response = $this->curlPost($oRequest->callback_url,$encryptData);
        Logger::dayLog('slbank/Notify', 'logging 获取id为'.$requestid.'的请求通知业务端后的返回数据'.$response);
        // 设置通知数据状态
        if ($response != 'SUCCESS') {
            $notifyStatus = SlbankNotifyModel::STATUS_RETRY;
            $reason = !$response?'无响应':$response;
            $this->oSlbankNotify->changeNotifyStatus($notifyid, $notifyStatus, $reason);
            return false;
        }
        $notifyStatus = SlbankNotifyModel::STATUS_SUCCESS;
        $result = $this->oSlbankNotify->changeNotifyStatus($notifyid, $notifyStatus, $response);
        if (!$result) {
            $notifyStatus = SlbankNotifyModel::STATUS_RETRY;
            $reason = '通知状态修改失败';
            $this->oSlbankNotify->changeNotifyStatus($notifyid, $notifyStatus, $reason);
            return false;
        }
        Logger::dayLog('slbank/Notify', 'success id为'.$requestid.'的请求通知发送成功');
        return true;
    }

    /**
     * 加密
     */
    private function encryptData($aid, $data){
        // 加密信息
        try{
            $encryptData =  \app\models\App::model() -> encryptData($aid, $data);
            return [ 'res_data' => $encryptData, 'res_code'=> 0];
        }catch(\Exception $e){
            // log_here
            return '';
        }
    }

    /**
     * 提交数据
     * @param array $data
     * @param str data
     * @return null
     */
    public function curlPost($url, $data) {
        // 1 计算log
        $timeLog = new \app\common\TimeLog();

        //2 提前请求
        $curl = new \app\common\Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 20);
        $curl->setOption(CURLOPT_TIMEOUT, 20);
        $res = $curl->post($url, $data);
        $httpStatus = $curl->getStatus();

        //3 详细纪录请求与响应的结果
        $timeLog->save('slbank', [$url, $data, $httpStatus, $res]);
        return strtoupper($res);
    }
}
<?php
/**
 * 数据魔盒：通知ZZZ
 * @author zhangfei
 */
namespace app\modules\api\common\sjmh;
use app\common\Logger;
use app\models\sjmh\SjmhRequest;
use app\models\sjmh\SjmhNotify;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class SNotify {
    protected $oSjmhRequest;
    protected $oSjmhNotify;
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oSjmhRequest = new SjmhRequest;
        $this->oSjmhNotify = new SjmhNotify;
    }


    /**
     * 一般是每几分钟执行
     */
    public function runMinute($start_time, $end_time) {
        //1 获取需要通知的数据

        $dataList = $this->oSjmhNotify->getSjmhNotifyList($start_time, $end_time,1000);
        return $this->runNotify($dataList);
    }
    /**
     * 执行所有通知
     */
    public  function runAll() {
        //1 获取需要通知的数据
        $dataList = $this->oSjmhNotify->getSjmhNotifyList('0000-00-00', date('Y-m-d H:i:s'));
        return $this->runNotify($dataList);
    }
    /**
     * 暂时五分钟跑一批:
     */
    public function runNotify($dataList) {
        //1 验证
        if (!$dataList) {
            return false;
        }

        $oSjmhNotify = new SjmhNotify();
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $oSjmhNotify->lockNotify($ids); // 锁定出款接口的请求
        #$ups = true;
        if (!$ups) {
            return false;
        }

        //4 逐条处理
        //$total = count($dataList);
        $success = 0;
        foreach ($dataList as $oNotify) {
            $result = $this->doNotify($oNotify);

            if ($result) {
                $success++;
            } else {
                $oSjmhNotify->saveOneNotifyStatus($oNotify,$this->oSjmhNotify->gStatus('STATUS_RETRY'), "未知错误");
                Logger::dayLog('sjmh/SNotify', 'runNotify_error','处理失败',$oNotify);
            }
        }
        logger::dayLog('sjmh/SNotify','runNotify','通知成功条数：'.$success.',数据：',$ids);
        //5 返回结果
        var_dump($success);
        return $success;
    }
    /**
     * 处理单条通知
     * @param object $oRemit
     * @return bool
     */
    public  function doNotify($oNotify) {

        //1 参数验证
        if (!$oNotify) {
            return false;
        }

        //2 是否有回调链接地址
        $operatorObj = $this->oSjmhRequest->findOne($oNotify['request_id']);
//----------------------------------------------------------------------

        if (!$operatorObj) {
            Logger::dayLog('sjmh/SjmhCollection', 'doNotify', "没有这条纪录:",$oNotify);
            return false;
        }
        $oSjmhNotify = new SjmhNotify();

        if (empty($operatorObj['callback_url'])) {
            Logger::dayLog('sjmh/SjmhCollection', 'doNotify', "没有回调地址:",$oNotify);
            $oSjmhNotify->saveOneNotifyStatus($oNotify, $oSjmhNotify->gStatus('STATUS_FAILURE'), '没有回调地址');
            return false;
        }
        //3 通知
        $data = [
            'user_id' => $operatorObj['user_id'],           //用户id
            'task_id' => $operatorObj['task_id'],           //任务id
            'request_id' => $operatorObj['id'],             //请求id
            'status' => $operatorObj['request_status'],     //抓取状态
            'source' => $operatorObj['source'],             //类型  社保 公积金
            'create_time' => $operatorObj['create_time'],   //请求时间
            'reason' => $operatorObj['reason'],             //抓取状态信息
            'name' => $operatorObj['name'],                 //用户名
            'mobile' => $operatorObj['mobile'],             //手机号
            'idcard' => $operatorObj['idcard'],             //身份证号
        ];

        $dataen = $this -> encryptData($operatorObj['aid'], $data);
        $response = $this->curlPost($operatorObj['callback_url'], $dataen);
        if ($response == 'SUCCESS') {
            $nextStatus = $oSjmhNotify->gStatus('STATUS_SUCCESS');
        } else {
            $nextStatus = $oSjmhNotify->gStatus('STATUS_RETRY');
        }
        $reason = $response === false ? '无响应' : $response;
        if(!$reason){
            $reason="未知错误";
        }

        //4 保存状态
        $result = $oSjmhNotify->saveOneNotifyStatus($oNotify, $nextStatus, $reason);
        if (!$result) {
            Logger::dayLog('sjmh/SNotify', 'doNotify', $oNotify->errors);
            return FALSE;
        }
        Logger::dayLog('sjmh/SNotify', 'doNotify', '通知成功：',$oNotify);
        return true;
    }


    /**
     * GET 页面回调链接
     */
    public function clientGet($callbackurl, $data, $aid) {
        $data = $data->attributes;
        //1 加密
        $res_data = \app\models\App::model() -> encryptData($aid, $data);
        //2 组成url
        $link = strpos($callbackurl, "?") === false ? '?' : '&';
        $url = $callbackurl . $link . 'res_code=0&res_data=' . rawurlencode($res_data);
        return $url;
    }

    /**
     * GET 回调通知客户端 url
     * @return url
     */
    public function clientBackurl($data) {
        $aid = $data['aid'];
        unset($data['aid']);
        $url =  $this->clientGet( $data['callback_url'], $data, $aid);
        Logger::dayLog('sjmh','SNotify/clientBackurl','通知URL：'.$url.'数据：',$data);
        return $url;
    }

    /**
     * 提交数据
     * @param array $data
     * @param str data
     * @return null
     */
    protected function curlPost($url, $data) {
        // 1 计算log
        $timeLog = new \app\common\TimeLog();

        //2 提前请求
        $curl = new \app\common\Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 20);
        $curl->setOption(CURLOPT_TIMEOUT, 20);
        $res = $curl->post($url, $data);
        $httpStatus = $curl->getStatus();

        //3 详细纪录请求与响应的结果
        $timeLog->save('sjmh', [$url, $data, $httpStatus, $res]);

        return $res;
    }
    /**
     * 加密
     */
    protected function encryptData($aid, $data){
        // 加密信息
        try{
            $encryptData =  \app\models\App::model() -> encryptData($aid, $data);
            return [ 'res_data' => $encryptData, 'res_code'=> 0];
        }catch(\Exception $e){
            // log_here
            return '';
        }
    }
}
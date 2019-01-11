<?php
/**
 * 计划任务处理:中信出款流程
 * 这个是中信出款的逻辑类,相当于控制器功能
 * @author lijin
 */
namespace app\modules\api\common\remit;
use app\common\Logger;
use app\models\remit\ClientNotify;
use app\models\remit\Remit;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CNotify {
    protected $oRemit;
    protected $oClientNotify;
    protected $logname;
    protected $channelId;
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oRemit = new Remit;
        $this->oClientNotify = new ClientNotify;
        $this->logname = 'remit';
        $this->channelId = 2;
    }
    /**
     * 一般是每几分钟执行
     */
    public function runMinute($start_time, $end_time) {
        //1 获取需要通知的数据
        $dataList = $this->oClientNotify->getClientNotifyList($start_time, $end_time);
        return $this->runNotify($dataList);
    }
    /**
     * 执行所有通知
     * 暂不开放
     */
    protected function runAll() {
        //1 获取需要通知的数据
        $dataList = $this->oClientNotify->getClientNotifyList('0000-00-00', date('Y-m-d H:i:s'));
        return $this->runNotify($dataList);
    }
    /**
     * 暂时五分钟跑一批:
     * 处理出款
     */
    public function runNotify($dataList) {
        //1 验证
        if (!$dataList) {
            return false;
        }

        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oClientNotify->lockNotify($ids); // 锁定出款接口的请求
        if (!$ups) {
            return false;
        }

        //4 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $oNotify) {
            $result = $this->doNotify($oNotify);
            if ($result) {
                $success++;
            } else {
                $oNotify->saveNotifyStatus($this->oClientNotify->gStatus('STATUS_INIT'), "未知错误");
                Logger::dayLog($this->logname, 'CNotify/runNotify', '处理失败', $oNotify);
            }
        }

        //5 返回结果
        return $success;
    }

    public function synchroNotify($oNotify) {
//同步通知
        return $this->doNotify($oNotify);
    }
    /**
     * 处理单条出款
     * @param object $oRemit
     * @return bool
     */
    protected function doNotify($oNotify) {
        //1 参数验证
        if (!$oNotify) {
            return false;
        }

        //2 是否有回调链接地址
        $oRemit = $this->oRemit->findOne($oNotify['remit_id']);
        if (!$oRemit) {
            Logger::dayLog($this->logname, 'CNotify/doNotify', 'Remit/findOne', "没有这条纪录");
            return false;
        }
        if (!$oRemit['callbackurl']) {
            $ret = $oNotify->saveNotifyStatus($this->oClientNotify->gStatus('STATUS_FAILURE'), '没有回调地址');
            return false;
        }

        //3 通知
        $data = [
            'req_id' => $oRemit['req_id'],
            'client_id' => $oRemit['client_id'],
            'settle_amount' => $oRemit['settle_amount'],
            'remit_status' => $oRemit['remit_status'],
            'rsp_status' => $oRemit['rsp_status'],
            'rsp_status_text' => $oRemit['rsp_status_text'],
            'tip' => $oNotify['tip'],
            'channel_id' => $oRemit['channel_id'],
            'auth_time' => '',
            'remit_time' => '',
        ];

        //增加审核时间  auth_time remit_time
        if (isset($oRemit['auth_time']) && !empty($oRemit['auth_time'])) {
            $data['auth_time'] = $oRemit['auth_time'];
        }
        if (isset($oRemit['remit_time']) && !empty($oRemit['remit_time'])) {
            $data['remit_time'] = $oRemit['remit_time'];
        }

        $dataen = $this->encryptData($oRemit['aid'], $data);
        $response = $this->curlPost($oRemit['callbackurl'], $dataen);
        if ($response == 'SUCCESS') {
            $nextStatus = $this->oClientNotify->gStatus('STATUS_SUCCESS');
        } else {
            $nextStatus = $this->oClientNotify->gStatus('STATUS_RETRY');
        }
        $reason = $response === false ? '无响应' : $response;
        if (!$reason) {
            $reason = "未知错误";
        }

        //4 保存状态
        $result = $oNotify->saveNotifyStatus($nextStatus, $reason);
        if (!$result) {
            Logger::dayLog($this->logname, 'CNotify/doNotify', 'ClientNotify/saveNotifyStatus', $oNotify->errors);
            return FALSE;
        }

        return true;
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
        $timeLog->save($this->logname, [$url, $data, $httpStatus, $res]);

        return $res;
    }
    /**
     * 加密
     */
    protected function encryptData($aid, $data) {
        // 加密信息
        try {
            $encryptData = \app\models\App::model()->encryptData($aid, $data);
            return ['res_data' => $encryptData, 'res_code' => 0];
        } catch (\Exception $e) {
            // log_here
            return '';
        }
    }
}
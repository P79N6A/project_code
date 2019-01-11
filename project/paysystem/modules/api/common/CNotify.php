<?php
/**
 * 计划任务处理:中信出款流程
 * 这个是中信出款的逻辑类,相当于控制器功能
 * @author lijin
 */
namespace app\modules\api\common;
use app\common\Logger;
use app\models\ClientNotify;
use app\models\Payorder;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CNotify {
    protected $oClientNotify;
    protected $logname;
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oClientNotify = new ClientNotify;
        $this->logname = 'notify';
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
        $oPayorder = Payorder::findOne($oNotify['payorder_id']);
        if (empty($oPayorder)) {
            Logger::dayLog($this->logname, 'CNotify/doNotify', 'Payorder/findOne', "没有这条纪录");
            return false;
        }
        if (!$oPayorder['callbackurl']) {
            $ret = $oNotify->saveNotifyStatus($this->oClientNotify->gStatus('STATUS_FAILURE'));
            return false;
        }

        //3 通知
        $isNotify = $oPayorder->doClientNotify();
        if ($isNotify) {
            $nextStatus = $this->oClientNotify->gStatus('STATUS_SUCCESS');
        } else {
            $nextStatus = $this->oClientNotify->gStatus('STATUS_RETRY');
        }

        //4 保存状态
        $result = $oNotify->saveNotifyStatus($nextStatus);
        return true;
    }
    /**
     * Undocumented function
     * 处理通知锁定状态的数据
     * @return void
     */
    public function runLockNotify(){
        $num = 50;
        $dataList = $this->oClientNotify->getLockNotifyList($num);
        $success = 0;
        if(!empty($dataList)){
            foreach($dataList as $oNotify){
                $res = $oNotify->saveNotifyStatus(ClientNotify::STATUS_INIT);
                if($res){
                    $success++;
                }else{
                    Logger::dayLog($this->logname, 'CNotify/runLockNotify', '处理失败', $oNotify);
                }
            }
        }
        return $success;
    }
}
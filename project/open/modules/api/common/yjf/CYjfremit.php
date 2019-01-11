<?php

/**
 * 计划任务处理:易极付出款流程
 */

namespace app\modules\api\common\yjf;

use app\common\Logger;
use app\models\yjf\YjfClientNotify;
use app\models\yjf\YjfRemit;
use app\modules\api\common\yjf\Yjfpay;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CYjfremit
{
    /**
     * 初始化接口
     */
    public function __construct()
    {

    }
    /**
     * 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    private function getApi($aid)
    {
        static $map = [];
        $is_prod    = SYSTEM_PROD;
        //$is_prod = true;
        $env = $is_prod ? 'prod' . $aid : 'dev';
        if (!isset($map[$aid])) {
            $map[$aid] = new Yjfpay($env);
        }
        return $map[$aid];
    }
    /**
     * 暂时五分钟跑一批:
     * 处理出款
     */
    public function runRemits()
    {
        $res  = [];
        $aids = [1, 4];
        foreach ($aids as $aid) {
            $res[$aid] = $this->_runRemits($aid);
        }
        return $res;
    }
    /**
     * 按不同商编出款
     * @param  int $aid
     * @return []
     */
    private function _runRemits($aid)
    {
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];
        //2 一次性处理最大设置为20 约(200/12(60/5分))
        $restNum   = 50;
        $oRemit    = new YjfRemit();
        $remitData = $oRemit->getInitData($restNum, $aid);
        if (!$remitData) {
            return $initRet;
        }
        //var_dump($remitData);die;
        //3 锁定状态为出款中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理过滤
        $total         = count($remitData);
        $success       = 0;
        foreach ($remitData as $key => $oRemit) {
            $oRemit->refresh();
            $res = $this->getRemit($oRemit);
            if ($res) {
                $result = $this->doRemit($oRemit);
                if(isset($result['ret_code'])&&$result['ret_code']=='0'){
                    $success++;
                }else{
                    Logger::dayLog('cyjfremit', 'CYjfRemit/runRemits', '处理失败', $oRemit->attributes,$result);
                }
            }else {
                $res = $oRemit->saveRspStatus(YjfRemit::STATUS_FAILURE, '_ERROR', '规则出款限制','', '', 1);
                if (!$res) {
                    Logger::dayLog('cyjfremit', 'CYjfRemit/saveRspStatus', $oRemit->errors);
                }
                $this->addNotify($oRemit);
                Logger::dayLog('cyjfremit', 'CYjfRemit/runRemits', '处理失败,规则出款限制', $oRemit->attributes);
            }
        }

        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }
    /**
     * 处理单条出款
     * @param object $oRemit
     * @return bool
     */
    public function doRemit($oRemit) {
        //1. 检测是否是超限的数据
        if (!$oRemit) {
            return false;
        }
        if ($oRemit->remit_status != YjfRemit::STATUS_REQING_REMIT) {
            return false;
        }

        //2. 提交到接口中
        $remit_data = $this->getRemitApiData($oRemit);
        $result = $this->getApi($oRemit->aid)->payApply($remit_data);
        $res     = $this->saveStatus($oRemit, $result);
        return $result;
    }
    /**
     * 重组参数
     */
    private function getRemitApiData($data) {
        $remit_data = [];
        $remit_data['merchOrderNo'] = $data['req_id'];
        $remit_data['transAmount'] = $data['settle_amount'];
        $remit_data['accountName'] = $data['guest_account_name'];
        $remit_data['accountNo'] = $data['guest_account'];
        $remit_data['certNo'] = $data['identityid'];
        $remit_data['accountType'] = empty($data['account_type'])?'PRIVATE':'PUBLIC';
        return $remit_data;
    }

    /**
     * 请求成功，保存数据为处理中
     * @param type $oRemit
     * @param type $result
     * @return boolean
     */
    private function saveStatus($oRemit, $result)
    {
        if (empty($oRemit)) {
            return false;
        }
        $sub_remit_time = date('Y-m-d H:i:s');
        $res_code = isset($result['res_code'])?$result['res_code']:'';
        $res_data = isset($result['res_data'])?$result['res_data']:'';
        $orderNo = isset($result['res_data']['orderNo'])?$result['res_data']['orderNo']:'';
        $remit_status = YjfRemit::STATUS_DOING;
        if( $res_code=='0'){
            $ret_code = $result['res_data']['resultCode'];
            $ret_msg = $result['res_data']['resultMessage'];
            $res = $oRemit->saveRspStatus($remit_status, $ret_code, $ret_msg,$orderNo,$sub_remit_time, 1);
            if (!$res) {
                Logger::dayLog('cyjfremit', 'CYjfRemit/saveRspStatus', $oRemit->errors);
            }
        }else if(!empty($res_code)){
            //保存出款表中,只有初始状态和失败状态,处理中才更新，成功状态 异步回调处理
            $remit_status = YjfRemit::STATUS_FAILURE;
            $res = $oRemit->saveRspStatus($remit_status, $res_code, $res_data,$orderNo, $sub_remit_time, 1);
            if (!$res) {
                Logger::dayLog('cyjfremit', 'CYjfRemit/saveRspStatus', $oRemit->errors);
            }
            $this->addNotify($oRemit);
        }else{
            $res = $oRemit->saveRspStatus($remit_status, $res_code, $res_data,$orderNo,$sub_remit_time, 1);
            if (!$res) {
                Logger::dayLog('cyjfremit', 'CYjfRemit/saveRspStatus', $oRemit->errors);
            }
        }
        return true;
    }
     public function runQuerys() {
        $res = [];
        $aids = [1, 4];
        foreach ($aids as $aid) {
            $res[$aid] = $this->_runQuerys($aid);
        }
        return $res;
    }
    /**
     * @desc
     *
     */
    public function _runQuerys($aid) {
        //1 一次性处理最大设置为50
        $initRet = ['total' => 0, 'success' => 0];
        $restNum = 50;
        $oRemit = new YjfRemit;
        $remitData = $oRemit->getDoingData($restNum, $aid);
        if (!$remitData) {
            return $initRet;
        }
        //3 锁定状态为查询中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockQuery($ids);
        if (!$ups) {
            return $initRet;
        }
        //4 
        $total = count($remitData);
        $success = 0;
        foreach ($remitData as $oRemit) {
            $result = $this->queryPay($oRemit);
            if ($result > 0) {
                $success += $result;
                Logger::dayLog('cyjfremit', 'CYjfRemit/runQuerys', '处理成功', $result);
            }
        }
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }
    /**
     * 查询订单状态
     */
    public function queryPay($oRemit){
         $oRemit->refresh();
        //判断是否是终态
        if(YjfRemit::isFinished($oRemit)){
           return false;
        }
        $result = $this->getApi($oRemit->aid)->payQuery($oRemit->req_id);
        //var_dump($result);die;
        $remit_status = YjfRemit::STATUS_DOING;
        $res_code = isset($result['res_code'])?$result['res_code']:'';
        $res_data = isset($result['res_data'])?$result['res_data']:'';
        $orderNo = isset($result['res_data']['orderNo'])?$result['res_data']['orderNo']:'';
        if(isset($result['res_code'])&&$result['res_code']=='0') {
            $serviceStatus = isset($result['res_data']['serviceStatus'])?$result['res_data']['serviceStatus']:'';
            $resultMessage = isset($result['res_data']['resultMessage'])?$result['res_data']['resultMessage']:'';
            //订单明确失败时才 才改为失败状态
            if(isset($serviceStatus) && $serviceStatus=='REMITTANCE_FAIL'){
                $remit_status = YjfRemit::STATUS_FAILURE;
            }else if(isset($serviceStatus) && $serviceStatus=='REMITTANCE_DEALING'){
                //处理中
                $remit_status = YjfRemit::STATUS_DOING;
            }else if(isset($serviceStatus) && $serviceStatus=='REMITTANCE_SUCCESS'){
                $remit_status = YjfRemit::STATUS_SUCCESS;
            }
            $res_code = $serviceStatus;
            $res_data = $resultMessage;
        }
        $oRemit->refresh();
        //判断是否是终态
        if(YjfRemit::isFinished($oRemit)){
           return false;
        }
        $res = $oRemit->saveRspStatus($remit_status, $res_code, $res_data, $orderNo,'', '', 2);
        if (!$res) {
            Logger::dayLog('cyjfremit', 'CYjfRemit/saveRspStatus', $oRemit->errors);
        }
        $this->addNotify($oRemit);
        return $res;
    }
    /**
     * 预留出款限制
     * @param type $oRemit
     * @return boolean
     */
    private function getRemit($oRemit)
    {
        //1 检测是否是超限的数据
        if (!$oRemit) {
            return false;
        }
        $result = $oRemit->isTopLimit();
        if ($result) {
            return false;
        }
        return true;
    }





    /**
     * 加入通知列表中
     */
    private function addNotify(YjfRemit $oRemit)
    {
        if (in_array($oRemit['remit_status'], [YjfRemit::STATUS_SUCCESS, YjfRemit::STATUS_FAILURE])) {
            $oClientNotify = new YjfClientNotify();
            $result        = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('cyjfremit', 'CYjfRemit/addNotify', 'YjfClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }


}

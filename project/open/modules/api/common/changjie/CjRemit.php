<?php

/**
 * 计划任务处理:畅捷出款流程
 */

namespace app\modules\api\common\changjie;

use app\common\Logger;
use app\models\cjt\CjtRemit;
use app\models\cjt\ClientNotify;
use app\modules\api\common\changjie\CjtApi;
use app\modules\api\common\changjie\CNotify;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CjRemit {
    private $CjtRemit;
    const CJ_COMMIT_CODE = 'G10002'; //提交代付
    const CJ_QUERY_CODE = 'G20001'; //查询
    const CJ_NOTIFY_CODE = 'G20014'; //异步通知
    //成功 未知
    private static $commitProcessCode = [
        '0000', //代付请求交易成功（交易已受理）
        '2000', //代付系统正在对数据处理（处理中）
    ];
    //失败
    private static $commitFailCode = [
        '1000',
        '2004',
        '2009',
    ];
    /**
     * 初始化接口
     */
    public function __construct() {

    }
    /**
     * 按aid取不同的配置
     * @param  int  $channel_id 用于区分不同的商编
     * @return RbApi
     */
    private function getApi($channel_id) {
        static $map = [];
        $is_prod = SYSTEM_PROD;
        $is_prod = true;
        $env = $is_prod ? 'prod' . $channel_id : 'dev';
        if (!isset($map[$channel_id])) {
            $map[$channel_id] = new CjtApi($env);
        }
        return $map[$channel_id];
    }

    public function runRemits() {
        $res = [];
        $channel_ids =[117,118,127];
        foreach ($channel_ids as $channel_id) {
            $res[$channel_id] = $this->_runRemits($channel_id);
        }
        return $res;
    }
    /**
     * 出款
     * @return []
     */
    public function _runRemits($channel_id) {
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];
        //2 一次性处理最大设置为50
        $restNum = 50;
        $this->CjtRemit = new CjtRemit();
        $remitData = $this->CjtRemit->getInitData($restNum, $channel_id);
        if (!$remitData) {
            return $initRet;
        }
        //3 锁定状态为出款中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $this->CjtRemit->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理过滤
        $total = count($remitData);
        $success = 0;
        foreach ($remitData as $key => $oCjRemit) {
            $result = $this->doRemit($oCjRemit);
            if ($result) {
                $success++;
            } 
        }
        return ['total' => $total, 'success' => $success];
    }

    /**
     * nsq处理单条数据
     *
     * @param [type] $postData
     * @return void
     */
    public function runOneRemit($postData){
        $this->CjtRemit = new CjtRemit();
        $oCjRemit = $this->CjtRemit->getInitRemit($postData);
        if (!$oCjRemit) {
            return ['resp_code'=>$postData['channel_id'].'1001','resp_msg'=>'未找到对应数据'];
        }
        $result = $this->doRemit($oCjRemit);
         if($result){
            return ['resp_code'=>0,'resp_msg'=>$oCjRemit->id.'提交成功'];;
         }else{
            return ['resp_code'=>$oCjRemit->channel_id.'1004','resp_msg'=>$oCjRemit->id.'提交失败'];
         } 
    }
    /**
     * 单条出款
     * @param  [type] $oCjRemit [description]
     * @return [type]           [description]
     */
    private function doRemit($oCjRemit){
        $isLock=$oCjRemit->lockOneRemit();
        if(!$isLock){
            Logger::dayLog('cjremit', 'CjtRemit/doRemit', '乐观锁失败', $oCjRemit->id);
            return false;
        }
        $result = $this->getRemit($oCjRemit);
        if (!$result) {
            $res = $oCjRemit->saveRspStatus(CjtRemit::STATUS_FAILURE, '_ERROR', '规则出款限制', '','', 1);
            if (!$res) {
                Logger::dayLog('cjremit', 'CjtRemit/saveRspStatus', $oCjRemit->errors);
            }
            $this->addNotify($oCjRemit);
            Logger::dayLog('cjremit', 'CjtRemit/runRemits', '处理失败', $oCjRemit);
        }
        $result = $this->dealTrade($oCjRemit);
        return $result;
    }
    /**
     * @desc 请求畅捷
     * @param obj $remit_success
     * @return int $success
     */
    private function dealTrade($oCjRemit){
            $orderInfo = [];
            $orderInfo['client_id'] = $oCjRemit->client_id;
            $orderInfo['bankname'] = $oCjRemit->guest_account_bank;
            $orderInfo['cardno'] = $oCjRemit->guest_account;
            $orderInfo['name'] = $oCjRemit->guest_account_name;
            $orderInfo['amount'] = $oCjRemit->settle_amount;
            $orderInfo['card_type'] = $oCjRemit->card_type;

            // sleep(2);//由于并发限定2s/次
            $cjApiObj = $this->getApi($oCjRemit->channel_id);
            $bodyInfo = $cjApiObj->getBodyPayment($orderInfo);
            $trx_code = CjRemit::CJ_COMMIT_CODE;//代付
            $result = $cjApiObj->getXmlParam($bodyInfo,$trx_code,$oCjRemit->client_id);
            $res = $this->saveStatus($oCjRemit, $result);
            return $res;
    }

    public function runQuerys() {
        $res = [];
        $channel_ids =[117,118,127];
        foreach ($channel_ids as $channel_id) {
            $res[$channel_id] = $this->_runQuerys($channel_id);
        }
        return $res;
    }

    /**
     * @desc
     *
     */
    public function _runQuerys($channel_id) {
        //1 一次性处理最大设置为50
        $initRet = ['total' => 0, 'success' => 0];
        $restNum = 50;
        $oRemit = new CjtRemit;
        $remitData = $oRemit->getDoingData($restNum, $channel_id);
        if (!$remitData) {
            return $initRet;
        }
        //3 锁定状态为查询中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockQuery($ids);
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理
        $total = count($remitData);
        $success = 0;
        foreach ($remitData as $oRemit) {
            $isLock=$oRemit->lockOneQuery();
            if(!$isLock){
                continue;
            }
            $result = $this->doQuery($oRemit);
            if ($result) {
                $success++;
                Logger::dayLog('cjRemitQuery', 'CjRemit/runQuerys', '处理成功', $result);
            }
        }
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    /**
     * 请求成功，保存数据为处理中
     * @param type $remitData
     * @param type $result
     * @return boolean
     */
    private function saveStatus($remitData, $result)
    {
        if (empty($remitData) || empty($result)) {
            return false;
        }
        $content_text = (isset($result['INFO']['ERR_MSG']) && !empty($result['INFO']['ERR_MSG'])) ? $result['INFO']['ERR_MSG'] : '';
        if(!isset($result['INFO']['RET_CODE'])){
            return false;
        }
        $res_code = $result['INFO']['RET_CODE'];

        if (in_array($res_code,self::$commitFailCode)) {
            //保存出款表中,提交失败，恢复待出款状态
            $res = $remitData->saveRspStatus(CjtRemit::STATUS_FAILURE, $res_code, $content_text,'','',1);
            if (!$res) {
                Logger::dayLog('cjremit', 'CjtRemit/saveStatus',  $remitData->errors);
            }
            $this->addNotify($remitData);
        } else{
            // 保存出款表中,提交成功或者状态未知，更改状态为处理中
            $res = $remitData->saveRspStatus(CjtRemit::STATUS_DOING, $res_code, $content_text,'','', 1);
            if (!$res) {
                Logger::dayLog('cjremit', 'CjtRemit/saveRspStatus', $remitData->errors);
            }  
        }
        return true;
    }


    /**
     * 预留出款限制
     * @param type $oRemit
     * @return boolean
     */
    private function getRemit($oRemit) {
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
     * 处理批次出款
     * @param object $oRemit
     * @return int $succNun
     */
    private function doQuery($oRemit) {
        //1 参数验证
        if (!$oRemit) {
            return false;
        }

        $cjApiObj = $this->getApi($oRemit->channel_id);

        $bodyInfo = $cjApiObj->getQueryBody($oRemit->client_id);
        $trx_code = CjRemit::CJ_QUERY_CODE;//查询
        $qery_id = time().$oRemit->client_id;
        $result = $cjApiObj->getXmlParam($bodyInfo,$trx_code,$qery_id);

        $succNum = 0;
        if (!empty($result) && $result['INFO']['RET_CODE'] == '0000') {//查询请求成功
            $oRemit->refresh();
            if(!isset($result['BODY']['RET_CODE']) || !isset($result['BODY']['ERR_MSG'])){
                return false;
            }
            $resCode = isset($result['BODY']['RET_CODE']) ? $result['BODY']['RET_CODE'] : '';
            $resMsg  = isset($result['BODY']['ERR_MSG']) ? $result['BODY']['ERR_MSG'] : '';
            switch ($resCode) {
                case '0001':
                case '0002':
                    $state = CjtRemit::STATUS_DOING;
                    break;
                case '0000':
                    $state = CjtRemit::STATUS_SUCCESS;
                    break;
                case '2013':
                case '3999':
                    $state = CjtRemit::STATUS_FAILURE;
                    break;
                default:
                    $state = CjtRemit::STATUS_DOING;
                    break;
            }
            // 保存查询表中
            $res = $oRemit->saveRspStatus($state, $resCode, $resMsg,'','', 2);
            if (!$res) {
                Logger::dayLog('cjremit','CjtRemit/doQuery', 'cjremit/saveRspStatus', $oRemit->id, $oRemit->errors);
            }
            if ($state != CjtRemit::STATUS_DOING) {
                // 加入到通知列表中
                $result = $this->addNotify($oRemit);
                $succNum++;
            }
        }else{
            $result = $oRemit->saveRspStatus(CjtRemit::STATUS_DOING, '', '', '','',2);
            if (!$result) {
                Logger::dayLog('cjremit','CjtRemit/doQuery', 'cjremit/saveRspStatus', $oRemit->id, $oRemit->errors);
            }
            Logger::dayLog('cjcredit', 'doQuery', $oRemit->id,'查询超时');
        }
        return true;
    }

    public function InputNotify(CjtRemit $oRemit){//异步成功后直接发通知
        if (in_array($oRemit['remit_status'], [CjtRemit::STATUS_SUCCESS, CjtRemit::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('cjremit', 'CjRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
            $cjNotify = new CNotify();
            $res = $cjNotify->synchroNotify($oClientNotify);
        }
        return true;
    }
    /**
     * 加入通知列表中
     */
    private function addNotify(CjtRemit $oRemit) {
        if (in_array($oRemit['remit_status'], [CjtRemit::STATUS_SUCCESS, CjtRemit::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('cjremit', 'CjRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }


}

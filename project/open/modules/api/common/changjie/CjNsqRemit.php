<?php

/**
 * 计划任务处理:畅捷出款流程
 */

namespace app\modules\api\common\changjie;

use app\common\Logger;
use app\models\cjt\CjtRemit;
use app\modules\api\common\changjie\CjtApi;
use app\models\cjt\ClientNotify;

class CjNsqRemit 
{
    private $CjtRemit;
    const CJ_COMMIT_CODE = 'G10002'; //提交代付
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

    /**
     * 出款
     * @return []
     */
    public function doRemit($postData) {

        $this->CjtRemit = new CjtRemit();
        $oCjRemit = $this->CjtRemit->getInitRemit($postData);
        if (!$oCjRemit) {
            return ['resp_code'=>$postData['channel_id'].'1001','resp_msg'=>'未找到对应数据'];
        }
        //3 锁定状态为出款中
        $isLock=$oCjRemit->lockOneRemit();
        if(!$isLock){
            return ['resp_code'=>$postData['channel_id'].'1002','resp_msg'=>'订单{$oCjRemit->id}正在处理,请稍后重试'];
        }
    
        $result = $this->getRemit($oCjRemit);
        if (!$result) {
            $res = $oCjRemit->saveRspStatus(CjtRemit::STATUS_FAILURE, '_ERROR', '规则出款限制', '','', 1);
            if (!$res) {
                Logger::dayLog('cjremit', 'CjtRemit/saveRspStatus', $oCjRemit->errors);
            }
            $this->addNotify($oCjRemit);
            Logger::dayLog('cjremit', 'CjtRemit/runRemits', '处理失败', $oCjRemit);
             return ;
        }
        $dealRes = $this->dealTrade($oCjRemit);
        return $dealRes;
    }

    /**
     * @desc 请求畅捷
     * @param obj $remit_success
     * @return int $success
     */
    private function dealTrade($oCjRemit){
        $orderInfo = [];
        $orderInfo['client_id']     = $oCjRemit->client_id;
        $orderInfo['bankname']      = $oCjRemit->guest_account_bank;
        $orderInfo['cardno']        = $oCjRemit->guest_account;
        $orderInfo['name']          = $oCjRemit->guest_account_name;
        $orderInfo['amount']        = $oCjRemit->settle_amount;
        $orderInfo['card_type']     = $oCjRemit->card_type;

        $cjApiObj = $this->getApi($oCjRemit->channel_id);
        $bodyInfo = $cjApiObj->getBodyPayment($orderInfo);
        $trx_code = CjRemit::CJ_COMMIT_CODE;//代付
        $result = $cjApiObj->getXmlParam($bodyInfo,$trx_code,$oCjRemit->client_id);
        // if($result && in_array($result['INFO']['RET_CODE'],self::$commitProcessCode))
        $res = $this->saveStatus($oCjRemit, $result);
            if(!$res) return ['resp_code'=>$oCjRemit->channel_id.'1004','resp_msg'=>'更新失败'];
        return ['resp_code'=>0,'resp_msg'=>'提交成功'];;
    }



    /**
     * 请求成功，保存数据为处理中
     * @param type $oCjRemit
     * @param type $result
     * @return boolean
     */
    private function saveStatus($oCjRemit, $result)
    {
        if (empty($result)) {
            //超时异常
            $result['INFO']['RET_CODE'] = '9999';
        }
        $content_text = (isset($result['INFO']['ERR_MSG']) && !empty($result['INFO']['ERR_MSG'])) ? $result['INFO']['ERR_MSG'] : '';
        if(!isset($result['INFO']['RET_CODE'])){
            $result['INFO']['RET_CODE'] = '9999';
        }
        $res_code = $result['INFO']['RET_CODE'];

        if (in_array($res_code,self::$commitFailCode)) {
            //保存出款表中,提交失败，恢复待出款状态
            $res = $oCjRemit->saveRspStatus(CjtRemit::STATUS_FAILURE, $res_code, $content_text,'','',1);
            if (!$res) {
                Logger::dayLog('cjremit', 'CjtRemit/saveStatus',  $oCjRemit->errors);
            }
            $this->addNotify($oCjRemit);
        } else{
            // 保存出款表中,提交成功或者状态未知，更改状态为处理中
            $res = $oCjRemit->saveRspStatus(CjtRemit::STATUS_DOING, $res_code, $content_text,'','', 1);
            if (!$res) {
                Logger::dayLog('cjremit', 'CjtRemit/saveRspStatus', $oCjRemit->errors);
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

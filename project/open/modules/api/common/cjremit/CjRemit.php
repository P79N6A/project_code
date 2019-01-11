<?php

/**
 * 计划任务处理:畅捷出款流程   新版   2018-8-8 xlj
 */

namespace app\modules\api\common\cjremit;

use Yii;
use app\common\Logger;
use app\models\cjt\CjtRemit;
use app\models\cjt\ClientNotify;
use app\modules\api\common\cjremit\CjtApi;
use app\modules\api\common\cjremit\CNotify;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CjRemit {
    private $CjtRemit;
    private $channels = [174,183];      //通道id每次添加新的  都需要添加
    const CJ_COMMIT_CODE = 'T10000'; //提交代付
    const CJ_QUERY_CODE = 'C00000'; //查询


    #AcceptStatus   请求状态  S  F
    const AS_SUCCESS = 'S'; //AcceptStatus--成功
    const AS__FAIL = 'F'; //AcceptStatus--失败
    #PlatformRetCode    平台内部处理成功
    const PR_SUCCESS = '0000'; //PlatformRetCode--成功
//    const PR__FAIL = [1000,2004,2009]; //PlatformRetCode--失败
//    const PR__DOING = [2000]; //PlatformRetCode--处理中（暂时不用）
    #OriginalRetCode    订单当前状态
    const OR_SUCCESS = '000000'; //AcceptStatus--成功
    const OR__FAIL = '111111'; //AcceptStatus--失败
    const OR__DOING = '000001'; //AcceptStatus--处理中


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
        $channel_ids = $this->channels;
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
            if ($result) {;
                $success++;
            }
        }
        return ['total' => $total, 'success' => $success];
    }


    /**
     * 单条出款
     * @param  [type] $oCjRemit [description]
     * @return [type]           [description]
     */
    private function doRemit($oCjRemit){
        $isLock=$oCjRemit->lockOneRemit();
        if(!$isLock){
            Logger::dayLog('cjremit/doRemit', 'cjremit/doRemit', '乐观锁失败', $oCjRemit->id);
            return false;
        }
        $result = $this->getRemit($oCjRemit);
        if (!$result) {
            $res = $oCjRemit->saveRspStatus(CjtRemit::STATUS_FAILURE, '_ERROR', '规则出款限制', '','', 1);
            if (!$res) {
                Logger::dayLog('cjremit/doRemit', 'cjremit/saveRspStatus', $oCjRemit->errors);
            }
            $this->addNotify($oCjRemit);
            Logger::dayLog('cjremit/doRemit', 'cjremit/runRemits', '处理失败', $oCjRemit);
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
            // sleep(2);//由于并发限定2s/次
        $trx_code = CjRemit::CJ_COMMIT_CODE;//代付

        //判断银行卡的类型
        if(!isset($oCjRemit->card_type) || !$oCjRemit->card_type){
            $oCjRemit->card_type = 1; //默认借记卡
        }
        if($oCjRemit->card_type == 1){
            $account_type = '00';
        }elseif($oCjRemit->card_type == 2){
            $account_type = '01';  //贷记卡
        }else{
            $account_type = '00';
        }
        #todo


        $postData['TransCode'] =  $trx_code; //功能码
//
        $postData['OutTradeNo'] = $oCjRemit->client_id; //外部流水号
        $postData['BusinessType'] = '0';//业务类型 0私人 1公司
        $postData['BankCommonName'] = $oCjRemit->guest_account_bank;// 通用银行名称
        $postData['AccountType'] = $account_type;//账户类型 00借记卡 01贷记卡
        $postData['Currency'] = 'CNY';  //人民币
        $postData['TransAmt'] = $oCjRemit->settle_amount;//交易金额
//        $postData['PostScript'] = '用途';//交易金额

        $cjApiObj = $this->getApi($oCjRemit->channel_id);
        $response = $cjApiObj->getBodyPayment($oCjRemit,$postData,$oCjRemit->channel_id);
        Logger::dayLog('cjremit/cjremit', 'result', $response);
        $res = $this->saveStatus($oCjRemit, $response,1);
        return $res;
    }

    /**
     * @return array 单条订单查询 最终状态
     */
    public function runQuerys() {
        $res = [];
        $channel_ids = $this->channels;
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
     * @param type $code 1 出款操作，2主动查询操作，3异步通知（不用）
     * @return boolean
     * 同步状态判断，第一步，AcceptStatus你判断这个字段，如果是s，就说明订单已经发送到畅捷，进行下一步判断，如果是f则订单失败
    PlatformRetCode第二步判断这个字段, 如果是成功的话，进行第三步判断，如果失败，订单就是失败的。如果处理中，则调查询接口，或者等待异步回调。
    第三步，ORIGINAL_RET_CODE根据这个字段判断
     */
    private function saveStatus($remitData,$result,$code)
    {
        if (empty($remitData) || empty($result)) {
            return false;
        }
        $result = json_decode($result,true);
        $AcceptStatus =  ArrayHelper::getValue($result,'AcceptStatus','');
        if(empty($AcceptStatus)){
            Logger::dayLog('cjremit/chukuan', 'CjRemit/saveStatus', '请求超时！');
            $res = $remitData->saveRspStatus(CjtRemit::STATUS_DOING, 'xhh', '请求超时！！','','', $code);
            if (!$res) {
                Logger::dayLog('cjremit/chukuan', 'CjRemit/saveRspStatus', $remitData->errors);
                return false;
            }
            return false;
        }
        #AcceptStatus   请求状态  S  F
        #PlatformRetCode    平台内部处理成功
        #OriginalRetCode    订单当前状态

        if($AcceptStatus == self::AS_SUCCESS || $AcceptStatus == self::AS__FAIL){
            $PlatformRetCode  = ArrayHelper::getValue($result,'PlatformRetCode','') ;
            $PlatformErrorMessage  = ArrayHelper::getValue($result,'PlatformErrorMessage','');
            if(empty($PlatformRetCode)){
                $res = $remitData->saveRspStatus(CjtRemit::STATUS_DOING, ArrayHelper::getValue($result,'RetCode'),ArrayHelper::getValue($result,'RetMsg'), '','', $code);
                if (!$res) {
                    Logger::dayLog('cjremit/chukuan', 'CjRemit/saveRspStatus', $remitData->errors);
                    return false;
                }
                Logger::dayLog('cjremit/chukuan', '$PlatformRetCode为空', ArrayHelper::getValue($result,'RetCode'), ArrayHelper::getValue($result,'RetMsg'));
                return false;
            }
            if($PlatformRetCode == self::PR_SUCCESS){
                //成功
                $OriginalRetCode  = ArrayHelper::getValue($result,'OriginalRetCode','');
                $OriginalErrorMessage  =  ArrayHelper::getValue($result,'OriginalErrorMessage','');
                if($OriginalRetCode == self::OR_SUCCESS){    //成功
                    $res = $remitData->saveRspStatus(CjtRemit::STATUS_SUCCESS, $OriginalRetCode, $OriginalErrorMessage,'','',$code);
                    if (!$res) {
                        Logger::dayLog('cjremit/chukuan', 'CjRemit/saveStatus',  $remitData->errors);
                        return false;
                    }
                    $this->InputNotify($remitData);
                }
                if($OriginalRetCode == self::OR__FAIL){    //失败
                    $res = $remitData->saveRspStatus(CjtRemit::STATUS_FAILURE, $OriginalRetCode, $OriginalErrorMessage,'','',$code);
                    if (!$res) {
                        Logger::dayLog('cjremit/chukuan', 'CjRemit/saveStatus',  $remitData->errors);
                        return false;
                    }
                    $this->InputNotify($remitData);
                }
                if($OriginalRetCode == self::OR__DOING){  //处理中
                    $res = $remitData->saveRspStatus(CjtRemit::STATUS_DOING, $OriginalRetCode, $OriginalErrorMessage,'','', $code);
                    if (!$res) {
                        Logger::dayLog('cjremit/chukuan', 'CjRemit/saveRspStatus', $remitData->errors);
                        return false;
                    }
                }
                return true;
            }
            if(in_array($PlatformRetCode,[1000,2004,2009])){                  #失败
                $res = $remitData->saveRspStatus(CjtRemit::STATUS_FAILURE, $PlatformRetCode, $PlatformErrorMessage,'','',$code);
                if (!$res) {
                    Logger::dayLog('cjremit/chukuan', 'CjRemit/saveStatus',  $remitData->errors);
                    return false;
                }
                $this->InputNotify($remitData);
                return true;
            }

            #$PlatformRetCode == 2000  未处理中，考虑到会添加其他错误码 2000包括其他都改成处理中
            $res = $remitData->saveRspStatus(CjtRemit::STATUS_DOING, $PlatformRetCode, $PlatformErrorMessage,'','', $code);
            if (!$res) {
                Logger::dayLog('cjremit/chukuan', 'CjRemit/saveRspStatus', $remitData->errors);
                return false;
            }
            return true;
        }
        return false;
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
        查询
     * @param object $oRemit
     * @return int $succNun
     */
    private function doQuery($oRemit) {
        //1 参数验证
        if (!$oRemit) {
            return false;
        }

        $trx_code = CjRemit::CJ_QUERY_CODE;//查询

        $postData['TransCode'] =  $trx_code; //功能码
        $postData['OutTradeNo'] = $oRemit->client_id.time(); //外部流水号
        $postData['OriOutTradeNo'] = $oRemit->client_id;  //原交易订单


        $cjApiObj = $this->getApi($oRemit->channel_id);
        $result = $cjApiObj->getBodyPayment($oRemit,$postData,$oRemit->channel_id);

        Logger::dayLog('cjremit/doQueryResult', 'result', $result);
        return $this->saveStatus($oRemit, $result,2);
    }

    public function InputNotify(CjtRemit $oRemit){//异步成功后直接发通知
        if (in_array($oRemit['remit_status'], [CjtRemit::STATUS_SUCCESS, CjtRemit::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('cjremit/InputNotify', 'CjRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
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
                Logger::dayLog('cjremit/addNotify', 'CjRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }


}

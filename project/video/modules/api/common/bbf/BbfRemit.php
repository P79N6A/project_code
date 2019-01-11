<?php

/**
 * 计划任务处理:宝付出款流程
 */

namespace app\modules\api\common\bbf;

use app\common\Logger;
use app\models\bbf\ClientNotify;
use app\models\bbf\BbfRemit as BbfModel;
use app\modules\api\common\bbf\BbfApi;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class BbfRemit
{   
    private $BbfRemit;

    //处理中
    private static $bfProcessStatusCode = [
                                            'B0', //受理成功
                                            // 'B7'//系统异常
                                        ];
    //明确失败
    private static $bfFailStatusCode = [
                                            'B1',//验签失败
                                            'B2',//校验商户限额失败
                                            'B3',//异常商户
                                            'B4',//重复订单
                                            'B5',//商户余额不足
                                            'B6',//格式错误（参考3.3解析）
                                            'B7'//系统异常
                                        ];
    /**
     * 初始化接口
     */
    public function __construct(){

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
            $map[$aid] = new BbfApi($env);
        }
        return $map[$aid];
    }

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
     * 出款  
     * @return []
     */
    public function _runRemits($aid){   
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];
        //2 一次性处理最大设置为50
        $restNum   = 50;
        $this->BbfRemit  = new BbfModel();
        $remitData = $this->BbfRemit->getInitData($restNum, $aid);
        if (!$remitData) {
            return $initRet;
        }
         //锁定状态为出款中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $this->BbfRemit->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理过滤
        $total         = count($remitData);
        $success       = 0;
        $remit_success = [];
        foreach ($remitData as $key => $oBbfRemit) {
            $oBbfRemit->refresh();
            $result = $this->getRemit($oBbfRemit);
            $result = true;
            if ($result) {
                $remit_success[] = $oBbfRemit;
                $success++;
            } else {
                $res = $oBbfRemit->saveRspStatus(BbfModel::STATUS_FAILURE, '_ERROR', '规则出款限制', '', 1);
                if (!$res) {
                    Logger::dayLog('BbfRemit', 'BbfRemit/saveRspStatus', $oBbfRemit->errors);
                }
               $this->addNotify($oBbfRemit);
                Logger::dayLog('BbfRemit', 'BbfRemit/runRemits', '处理失败', $oBbfRemit);
            } 
        }
        if (empty($remit_success)) {
            $initRet = ['total' => $total, 'success' => 0];
            return $initRet;
        }
        $sucCount = $this->dealTrade($remit_success,$aid);
        return ['total' =>$total, 'success' => $sucCount];
    }


    /**
     * @desc 宝付每次请求条数不能超过5条
     * @param obj $remit_success
     * @return int $success
     */
    private function dealTrade($remit_success,$aid){
        
        $success = 0;
        $ymd = date("YmdHis");
        foreach ($remit_success as $k => $val) {
            $batch_no = $ymd;
            $oneId    = ArrayHelper::getColumn($val, 'id');
            $batch    = $this->BbfRemit->setBatchNo($oneId, $batch_no);
            
            $post_data = [];
            $post_data['mcSequenceNo'] = $val->client_id;
            $post_data['mcTransDateTime'] = date('YmdHis',strtotime($val->create_time));
            $post_data['orderNo'] = $val->req_id;
            $post_data['amount'] = $val->settle_amount*100;
            $post_data['cardNo'] = $val->guest_account;
            $post_data['accName'] = $val->guest_account_name;
            $post_data['idInfo'] = $val->identityid;
            $post_data['lBnkNo'] = '102100004951';
            $post_data['lBnkNam'] = '中国工商银行总行营业部';
            $result = $this->getApi($aid)->bbfTrade($post_data);
            $res    = $this->saveStatus($val, $result, $sub_remit_time);
        
            if($result && in_array($result['respCode'],self::$bfProcessStatusCode))
                $success += count($val);
        }
        //5 返回结果
        return $success;
    }

    public function runQuerys()
    {
        $res  = [];
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
    public function _runQuerys($aid)
    {
        //1 一次性处理最大设置为50
        $initRet   = ['total' => 0, 'success' => 0];
        $restNum   = 50;
        $oRemit    = new BbfModel;
        $remitData = $oRemit->getDoingData($restNum,$aid);
        if (!$remitData) {
            return $initRet;
        }
        //3 锁定状态为查询中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockQuery($ids);
        if (!$ups) {
            return $initRet;
        }
        //4 每条处理
        $total   = count($remitData);
        $success = 0;
        foreach ($remitData as $oRemit) {
            $result = $this->doQuery($oRemit,$aid);
            if ($result > 0) {
                $success += $result;
                Logger::dayLog('BbfRemitQuery', 'BbfRemit/runQuerys', '处理成功',$result);
            } 
        }
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    /**
     * 请求成功，保存数据为处理中
     * @param [] $oRemit
     * @param [] $result
     * @return boolean
     */
    private function saveStatus($oRemit, $result, $sub_remit_time = ''){
        if (empty($oRemit)) {
            return false;
        }
        //如果$result为空 则为超时
        $resCode =isset($result['respCode']) ? $result['respCode']:503;
        $resMsg = isset($result['respMsg']) ?  $result['respMsg'] : '无响应';
        $fileType = mb_detect_encoding($resMsg , array('UTF-8','GBK','LATIN1','BIG5'));
        $resMsg = mb_convert_encoding($resMsg ,'utf-8' , $fileType);
        if (in_array($resCode,self::$bfFailStatusCode)) {
            $res = $oRemit->saveRspStatus(BbfModel::STATUS_FAILURE, $resCode, $resMsg, $sub_remit_time, 1);
            if (!$res) {
                Logger::dayLog('bbf', 'BbfRemit/doRemit', 'Remit/saveRspStatus', $oRemit->errors);
            }
            $this->addNotify($oRemit);
        } else{
            //7.1 保存出款表中,提交成功，更改状态为处理中
            $res = $oRemit->saveRspStatus(BbfModel::STATUS_DOING, '', '', $sub_remit_time, 1);
            if (!$res) {
                Logger::dayLog('bbf', 'BbfRemit/saveRspStatus', $oRemit->errors);
            }
        }
        return true;
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
     * 处理单次出款
     * @param object $oRemit
     * @return int $succNun
     */
    private function doQuery($oRemit,$aid)
    {
        //1 参数验证
        if (!$oRemit) {
            return false;
        }
        $data       = [];
        $data['mcSequenceNo'] = $oRemit->client_id;
        $data['mcTransDateTime'] = date('YmdHis',strtotime($oRemit->create_time));
        $data['orderNo'] = $oRemit->req_id;
        $data['amount'] = $oRemit->settle_amount*100;

        //5 提交到接口中并解析响应结果
        $response = $this->getApi($aid)->bfQuery($data);
        //5.1 解析状态响应码
        $succNum = 0;
        if(empty($response)) return $succNum;
        if($response['respCode'] == 'B0'){
            $oRemit->refresh();
            switch($response['ordSts']){
                case 'U':
                    $state = BbfModel::STATUS_DOING;
                break;
                case 'S':
                    $state = BbfModel::STATUS_SUCCESS;
                break;
                case 'F':
                case 'J':
                    $state = BbfModel::STATUS_FAILURE;
                break;
                default:
                    $state = BbfModel::STATUS_DOING;
                break;
            }
            // 保存查询表中
            $result = $oRemit->saveRspStatus($state,$response['respCode'],$response['respMsg'], '', 2);
            if (!$result) {
                Logger::dayLog('BbfRemit', 'BbfRemit/saveRspStatus', $oRemit->id, $oRemit->errors);
            }
            if($state != BbfModel::STATUS_DOING){
                // 加入到通知列表中
                $result = $this->addNotify($oRemit); 
                $succNum ++;
            }
        }
        return $succNum;
    }


    /**
     * 加入通知列表中
     */
    private function addNotify(BbfModel $oRemit)
    {
        if (in_array($oRemit['remit_status'], [BbfModel::STATUS_SUCCESS, BbfModel::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result        = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('BbfRemit', 'BbfRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }

}

<?php

/**
 * 计划任务处理:宝付出款流程
 */

namespace app\modules\api\common\baofoo;

use app\common\Logger;
use app\models\baofoo\BfRemit;
use app\models\baofoo\ClientNotify;
use app\modules\api\common\baofoo\BaofooApi;
use app\modules\api\common\baofoo\CNotify;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class BaofooRemit {
    private $BfRemit;
    //交易批次处理条数  由于同批次订单一个订单格式有误会将同一批次订单置为失败因为改成1
    const TRADEMAXCOUNT = 1;
    //查询批次处理条数
    const QUERYMAXCOUNT = 5;
    //成功 未知
    private static $bfSuccStatusCode = [
        '0000', //代付请求交易成功（交易已受理）
        '0300', //代付交易未明，请发起该笔订单查询
        '0401', //未知，请根据对账文件
        '0999', //代付主机系统繁忙
    ];
    //失败
    private static $bfFailStatusCode = [
        '0001',
        '0002',
        '0003',
        '0004',
        '0201',
        '0202',
        '0203',
        '0204',
        '0205',
        '0206',
        '0207',
        '0208',
        '0301',
        '0501',
        '0601',
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
            $map[$channel_id] = new BaofooApi($env);
        }
        return $map[$channel_id];
    }

    public function runRemits() {
        $res = [];
        $channel_ids =[107,113,114,185];
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
        $restNum = 200;
        $this->BfRemit = new BfRemit();
        $remitData = $this->BfRemit->getInitData($restNum, $channel_id);
        if (!$remitData) {
            return $initRet;
        }
        //3 锁定状态为出款中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $this->BfRemit->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理过滤
        $total = count($remitData);
        $success = 0;
        $remit_success = [];
        foreach ($remitData as $key => $oBfRemit) {
            // $oBfRemit->refresh();
            $isLock=$oBfRemit->lockOneRemit();
            if(!$isLock){
                continue;
            }
            $result = $this->getRemit($oBfRemit);
            if ($result) {
                $remit_success[] = $oBfRemit;
                $success++;
            } else {
                $res = $oBfRemit->saveRspStatus(BfRemit::STATUS_FAILURE, '_ERROR', '规则出款限制', '','', 1);
                if (!$res) {
                    Logger::dayLog('bfremit', 'BfRemit/saveRspStatus', $oRemit->errors);
                }
                $this->addNotify($oBfRemit);
                Logger::dayLog('bfremit', 'BfRemit/runRemits', '处理失败', $oBfRemit);
            }
        }
        if (empty($remit_success)) {
            $initRet = ['total' => $total, 'success' => 0];
            return $initRet;
        }
        $sucCount = $this->dealTrade($remit_success);
        return ['total' => $total, 'success' => $sucCount];
    }

    /**
     * @desc 宝付每次请求条数不能超过5条
     * @param obj $remit_success
     * @return int $success
     */
    private function dealTrade($remit_success) {

        $chuckPostData = array_chunk($remit_success, self::TRADEMAXCOUNT);
        $success = 0;
        foreach ($chuckPostData as $k => $val) {
            //$batch_no = $this->BfRemit->getBatchNo();
            $ymd = date("YmdHis");
            $batch_no = $ymd . "_" . $k;
            $ids2 = ArrayHelper::getColumn($val, 'id');
            $batch = $this->BfRemit->setBatchNo($ids2, $batch_no);

            $handleData = [];
            $post_data = [];
            $handleData = ArrayHelper::getColumn($val, 'Attributes');
            $channel_id = $handleData[0]['channel_id'];
            $post_data = $this->getBanthContent($handleData);
            $result = $this->getApi($channel_id)->bfTrade($post_data);
            $res = $this->saveStatus($val, $result, $sub_remit_time);

            if ($result && in_array($result['trans_content']['trans_head']['return_code'], self::$bfSuccStatusCode)) {
                $success += count($val);
            }

        }
        //5 返回结果
        return $success;
    }

    public function runQuerys() {
        $res = [];
        $channel_ids =[107,113,114,185];
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
        $restNum = 200;
        $oRemit = new BfRemit;
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
        //4 每5条处理
        $total = count($remitData);
        $success = 0;
        $chuckData = array_chunk($remitData, self::QUERYMAXCOUNT);
        foreach ($chuckData as $oRemit) {
            $result = $this->doQuery($oRemit);
            if ($result > 0) {
                $success += $result;
                Logger::dayLog('bfRemitQuery', 'BfRemit/runQuerys', '处理成功', $result);
            }
        }
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    /**
     * 请求成功，保存数据为处理中
     * @param [] $remitData
     * @param [] $result
     * @return boolean
     */
    private function saveStatus($remitData, $result, $sub_remit_time = '') {
        if (empty($remitData)) {
            return false;
        }
        $isBatch = count($remitData) > 1 ? true : false;
        $returnRes = $this->parseQueryParam($result, $isBatch);
        
        $resCode = isset($returnRes['res_code']) ? $returnRes['res_code'] : 503;
        $resMsg = isset($returnRes['res_msg']) ? $returnRes['res_msg'] : '无响应';
        $resInfo = isset($returnRes['res_data']) ? $returnRes['res_data'] : [];
        if($resInfo){
            $parseArr = ArrayHelper::index($resInfo, 'trans_no');
        }
        if (in_array($resCode, self::$bfFailStatusCode)) {
            foreach ($remitData as $key => $oRemit) {
                //7.1 保存出款表中,提交失败，恢复待出款状态
                $bf_orderid = isset($parseArr[$oRemit->client_id]['trans_orderid']) ? $parseArr[$oRemit->client_id]['trans_orderid'] : '';
                //var_dump($bf_orderid);exit;
                $res = $oRemit->saveRspStatus(BfRemit::STATUS_FAILURE, $resCode, $resMsg, $sub_remit_time,$bf_orderid, 1);
                if (!$res) {
                    Logger::dayLog('baofoo', 'BfRemit/doRemit', 'Remit/saveRspStatus', $oRemit->errors);
                }
                $this->addNotify($oRemit);
            }
        } else {
            foreach ($remitData as $k1 => $oRemit1) {
                $bf_orderid = isset($parseArr[$oRemit1->client_id]['trans_orderid']) ? $parseArr[$oRemit1->client_id]['trans_orderid'] : '';
                //7.1 保存出款表中,提交成功，更改状态为处理中
                $res = $oRemit1->saveRspStatus(BfRemit::STATUS_DOING, '', '', $sub_remit_time,$bf_orderid, 1);
                if (!$res) {
                    Logger::dayLog('baofoo', 'BfRemit/saveRspStatus', $oRemit1->errors);
                }
            }
        }
        return true;
    }

    /**
     * 批量代付提交 组合代付数据格式
     * @param [] $handleData
     * @return []
     */
    private function getBanthContent($handleData) {
        if (empty($handleData)) {
            return [];
        }

        $params = [];
        foreach ($handleData as $key => $value) {
            $params[$key]['trans_no'] = $value['client_id'];
            $params[$key]['trans_money'] = $value['settle_amount'] ? $value['settle_amount'] : "";
            $params[$key]['to_acc_name'] = $value['guest_account_name'] ? $value['guest_account_name'] : "";
            $params[$key]['to_acc_no'] = $value['guest_account'] ? $value['guest_account'] : "";
            $params[$key]['to_bank_name'] = $value['guest_account_bank'] ? $value['guest_account_bank'] : "";
            if ($params[$key]['to_bank_name'] == '中国邮政储蓄') {
                $params[$key]['to_bank_name'] = "邮政储蓄银行";
            }
            if ($params[$key]['to_bank_name'] == '广发银行股份有限公司') {
                $params[$key]['to_bank_name'] = "广发银行";
            }
            $params[$key]['to_pro_name'] = $value['guest_account_province'] ? $value['guest_account_province'] : "";
            $params[$key]['to_city_name'] = $value['guest_account_city'] ? $value['guest_account_city'] : "";
            $params[$key]['to_acc_dept'] = $value['guest_account_bank_branch'] ? $value['guest_account_bank_branch'] : "";
            if ($params[$key]['to_acc_dept'] == '中国邮政储蓄') {
                $params[$key]['to_acc_dept'] = "邮政储蓄银行";
            }
            if ($params[$key]['to_acc_dept'] == '广发银行股份有限公司') {
                $params[$key]['to_acc_dept'] = "广发银行";
            }
            $params[$key]['trans_card_id'] = $value['identityid'] ? $value['identityid'] : "";
            $params[$key]['trans_mobile'] = $value['user_mobile'] ? $value['user_mobile'] : "";
            $params[$key]['trans_summary'] = $value['settlement_desc'] ? $value['settlement_desc'] : "";
        }
        return $params;
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
        $data = [];
        $data = $this->getqueryParam($oRemit);
        //5 提交到接口中并解析响应结果
        $response = $this->getApi($oRemit[0]['channel_id'])->bfQuery($data);
        //5.1 解析状态响应码
        //判断是批次请求
        $isBatch = count($oRemit) > 1 ? true : false;
        $returnRes = $this->parseQueryParam($response, $isBatch);
        $succNum = 0;
        if (!$returnRes) {
            foreach ($oRemit as $tk => $tv) {
                // $tv->refresh();
                $isLock=$tv->lockOneQuery();
                if(!$isLock){
                    continue;
                }
                $result = $tv->saveRspStatus(BfRemit::STATUS_DOING, '', '', '', '',2);
                if (!$result) {
                    Logger::dayLog('baofoo/select', 'BfRemit/saveRspStatus', $tv->id, $tv->errors);
                }
                Logger::dayLog('baofoo', 'doQuery', $tv->id,'查询超时');
            }
            return $succNum;
        }
        $parseArr = ArrayHelper::index($returnRes['res_data'], 'trans_no');
        if ($returnRes['res_code'] == '0000' && !empty($parseArr)) {
            foreach ($oRemit as $key => $value) {
                // $value->refresh();
                $isLock=$value->lockOneQuery();
                if(!$isLock){
                    continue;
                }
                $selfParam = isset($parseArr[$value->client_id]) ? $parseArr[$value->client_id] : [];
                switch ($selfParam['state']) {
                case 0:
                    $state = BfRemit::STATUS_DOING;
                    break;
                case 1:
                    $state = BfRemit::STATUS_SUCCESS;
                    break;
                case -1:
                case 2:
                    $state = BfRemit::STATUS_FAILURE;
                    break;
                default:
                    $state = BfRemit::STATUS_DOING;
                    break;
                }
                // 保存查询表中
                $result = $value->saveRspStatus($state, '', $selfParam['trans_remark'], '','', 2);
                if (!$result) {
                    Logger::dayLog('baofoo/select', 'BfRemit/saveRspStatus', $value->id, $value->errors);
                }
                if ($state != BfRemit::STATUS_DOING) {
                    // 加入到通知列表中
                    $result = $this->addNotify($value);
                    $succNum++;
                }
            }
        }else{
            foreach ($oRemit as $tk => $tv) {
                // $tv->refresh();
                $isLock=$tv->lockOneQuery();
                if(!$isLock){
                    continue;
                }
                $result = $tv->saveRspStatus(BfRemit::STATUS_DOING, '', '', '', '',2);
                if (!$result) {
                    Logger::dayLog('baofoo/select', 'BfRemit/saveRspStatus', $tv->id, $tv->errors);
                }
                Logger::dayLog('baofoo', 'doQuery', $tv->id,'查询超时');
            }
            return $succNum;
        }
        return $succNum;
    }

    /**
     * 查询数据格式化
     * @param  obj $oRemit
     * @return []
     */
    private function getqueryParam($oRemit) {
        if (empty($oRemit)) {
            return [];
        }

        $params = [];
        foreach ($oRemit as $key => $value) {
            $params[$key]['trans_no'] = $value['client_id'];
        }
        return $params;
    }
    //异步成功后直接发通知
    public function InputNotify(BfRemit $oRemit){
        if (in_array($oRemit['remit_status'], [BfRemit::STATUS_SUCCESS, BfRemit::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('Bfremit', 'BfRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
            $cNotify = new CNotify();
            $res = $cNotify->synchroNotify($oClientNotify);
        }
        return true;
    }
    /**
     * 加入通知列表中
     */
    private function addNotify(BfRemit $oRemit) {
        if (in_array($oRemit['remit_status'], [BfRemit::STATUS_SUCCESS, BfRemit::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('Bfremit', 'BfRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }

    /**
     * @desc 宝付查询接口返回数据解析 标准化输出
     * @param [] $response
     * @param bool $isBatch
     * $return []
     */
    private function parseQueryParam($response, $isBatch) {
        if (empty($response)) {
            return [];
        }

        $returnArr = [];
        $returnArr['res_code'] = $response['trans_content']['trans_head']['return_code'];
        $returnArr['res_msg'] = $response['trans_content']['trans_head']['return_msg'];
        if ($isBatch && !empty($response['trans_content']['trans_reqDatas'])) {
            $resData = $response['trans_content']['trans_reqDatas'][0]['trans_reqData'];
            foreach ($resData as $key => $value) {
                $returnArr['res_data'][] = $value;
            }
        } else {
            $returnArr['res_data'][] = $response['trans_content']['trans_reqDatas'][0]['trans_reqData'];
        }
        return $returnArr;
    }

}

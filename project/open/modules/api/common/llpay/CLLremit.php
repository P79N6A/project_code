<?php

/**
 * 计划任务处理:连连出款流程
 */

namespace app\modules\api\common\llpay;

use app\common\Logger;
use app\models\lian\LLClientNotify;
use app\models\lian\LLRemit;
use app\modules\api\common\llpay\LLpay;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CLLremit
{
     //失败
    private static $llFailStatusCode = [
        '1001',//商户请求签名未通过
        '1004',//商户请求参数校验错误
        '1005',//不支持该银行账户类型
        '1008',//商户请求IP错误
        '3001',//非法的商户
        '4005',//商户未开通权限
        '4012',//银行卡查询异常
        '4015',//大额行号查询失败
        '9104',//账户余额不足
        '9910',//风险等级过高
        '9911',//超过单笔限额
        '9912',//超过单日限额
        '9913',//超过单月限额
    ];
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
            $map[$aid] = new LLpay($env);
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
        $oRemit    = new LLRemit();
        $remitData = $oRemit->getInitData($restNum, $aid);
        if (!$remitData) {
            return $initRet;
        }
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
            $oRemit->refresh();//lock乐观锁
            $res = $this->getRemit($oRemit);//方法名
            if ($res) {
                $result = $this->doRemit($oRemit);
                if(isset($result['ret_code'])&&$result['ret_code']=='0000'){
                    $success++;
                } else {
                    Logger::dayLog('cllremit', 'CLLRemit/runRemits', '处理失败', $oRemit->attributes,$result);
                }
            } else {
                $res = $oRemit->saveRspStatus(LLRemit::STATUS_FAILURE, '_ERROR', '规则出款限制','', '', 1);
                if (!$res) {
                    Logger::dayLog('cllremit', 'CLLRemit/saveRspStatus', $oRemit->errors);
                }
                $this->addNotify($oRemit);
                Logger::dayLog('cllremit', 'CLLRemit/runRemits', '处理失败,规则出款限制', $oRemit->attributes);
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
        if ($oRemit->remit_status != LLRemit::STATUS_REQING_REMIT) {
            return false;
        }

        //2. 提交到接口中
        $remit_data = $this->getRemitApiData($oRemit);
        $result = $this->getApi($oRemit->aid)->payApply($remit_data);
        $res     = $this->saveStatus($oRemit, $result);
        return $result;
    }
    /**
     * 获取请求参数 150001-100
     * @param  [] $data 参数类型
     * @return [] 重组参数
     */
    private function getRemitApiData($data) {
        $remit_data = [];
        $remit_data['no_order'] = $data['req_id'];
        $remit_data['dt_order'] = date('YmdHis');
        $remit_data['money_order'] = $data['settle_amount'];
        $remit_data['acct_name'] = $data['guest_account_name'];
        $remit_data['card_no'] = $data['guest_account'];
        $remit_data['flag_card'] = $data['account_type'];
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
        $ret_code = isset($result['ret_code']) ? $result['ret_code'] : '';
        $content_text = isset($result['ret_msg']) ? $result['ret_msg'] : '无响应';
        $confirm_code = isset($result['confirm_code']) ? $result['confirm_code'] : '';
        $oid_paybill = isset($result['oid_paybill']) ? $result['oid_paybill'] : '';
        $remit_status = LLRemit::STATUS_DOING;
        if(!empty($ret_code) && in_array($ret_code,self::$llFailStatusCode)){
            $remit_status = LLRemit::STATUS_FAILURE;           
        }
        $res = $oRemit->saveRspStatus($remit_status, $ret_code, $content_text, $oid_paybill,$confirm_code, $sub_remit_time, 1);
        if (!$res) {
            Logger::dayLog('cllremit', 'CLLRemit/saveRspStatus', $oRemit->errors);
        }
        $this->addNotify($oRemit);
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
        $oRemit = new LLRemit;
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
                Logger::dayLog('cllremit', 'CLLRemit/runQuerys', '处理成功', $result);
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
        if(LLRemit::isFinished($oRemit)){
           return false;
        }
        $result = $this->getApi($oRemit->aid)->queryPay($oRemit->req_id);
        $remit_status = LLRemit::STATUS_DOING;
        $ret_code = isset($result['ret_code']) ? $result['ret_code'] : '';
        $content_text = isset($result['ret_msg']) ? $result['ret_msg'] : '无响应';
        $result_pay = isset($result['result_pay']) ? $result['result_pay'] : '';
        $oid_paybill = isset($result['oid_paybill']) ? $result['oid_paybill'] : '';
        if(isset($result['ret_code'])&&$result['ret_code']=='0000') {
            //订单明确失败时才 才改为失败状态
            if(isset($result_pay) && in_array($result_pay,array('CANCEL','FAILURE','CLOSED'))){
                $remit_status = LLRemit::STATUS_FAILURE;
            }else if(isset($result_pay) && $result_pay=='SUCCESS'){
                $remit_status = LLRemit::STATUS_SUCCESS;
            }
            $content_text = $result_pay;
        }
        $oRemit->refresh();
        //判断是否是终态
        if(LLRemit::isFinished($oRemit)){
           return false;
        }
        $res = $oRemit->saveRspStatus($remit_status, $ret_code, $content_text, $oid_paybill,'', '', 2);
        if (!$res) {
            Logger::dayLog('cllremit', 'CLLRemit/saveRspStatus', $oRemit->errors);
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
    private function addNotify(LLRemit $oRemit)
    {
        if (in_array($oRemit['remit_status'], [LLRemit::STATUS_SUCCESS, LLRemit::STATUS_FAILURE])) {
            $oClientNotify = new LLClientNotify();
            $result        = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('cllremit', 'CLLRemit/addNotify', 'LLClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }


}

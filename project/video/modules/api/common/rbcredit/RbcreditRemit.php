<?php

/**
 * 计划任务处理:融宝出款流程
 */

namespace app\modules\api\common\rbcredit;

use app\common\Logger;
use app\models\rbcredit\ClientNotify;
use app\models\rbcredit\RbCreditRemit as RbRemit;
use app\modules\api\common\rbcredit\RbCreditApi;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class RbcreditRemit
{   
    //提交明确失败的错误码
    public $commitFailCode = [
        '6100','6101','6102','6103','6104','6105','6106','6107','6108',
        '4001','4002','4003','4004','4005','4006','4007','4008','4009',
        '4010','4011','4012','4013','4014','4015','4016','4017','4018',
        '4019','4020','4021','4022','4023','4024','4025','4026','4027',
        '4028','4029','4030','4031','4032','4033','4034','4037','4038',
        '1004','1007','1006','1008','1020','1231','6001','6002','6003',
        '6004','6005','6006','6007','6008','6009','6016','6017','6018',
        '6019','6024','6025', 
    ];
    //
    private $commitProcessCode = [
        '0000',
        '1001',
        '2016',
        '4035',
        '4036',
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
        $is_prod = true;
        $env = $is_prod ? 'prod' . $aid : 'dev';
        if (!isset($map[$aid])) {
            $map[$aid] = new RbCreditApi($env);
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
        $aids = [4,1];
        foreach ($aids as $aid) {
            $res[$aid] = $this->_runRemits($aid);
        }
        return $res;
    }

    /**
     *  出款
     *
     * @param  $aid 
     * @return void
     */
    private function _runRemits($aid)
    {
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];
        //2 一次性处理最大设置为50 约(200/12(60/5分))
        $restNum   = 50;
        $oRemit    = new RbRemit();
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
        $remit_success = [];
        foreach ($remitData as $key => $oRemit) {
            $oRemit->refresh();
            $result = $this->getRemit($oRemit);
            if ($result) {
                $remit_success[] = $oRemit;
                $success++;
            } else {
                $res = $oRemit->saveRspStatus(RbRemit::STATUS_FAILURE, '_ERROR', '规则出款限制', '', 1);
                if (!$res) {
                    Logger::dayLog('rbcredit', 'rbcredit/saveRspStatus', $oRemit->errors);
                }
                $this->addNotify($oRemit);
                Logger::dayLog('rbcredit', 'rbcredit/runRemits', '处理失败', $oRemit);
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
     * @desc 
     * @param obj $remit_success
     * @param int $aid
     * @return int $success
     */
    private function dealTrade($remit_success,$aid){
        
        $success = 0;
        
        foreach ($remit_success as $k => $val) {           
            $post_data = [];
            $post_data['customer_order']    = (string)$val->client_id;
            $post_data['trans_time']        = (string)date('Y-m-d',strtotime($val->create_time));//交易请求时间
            $post_data['bank_code']         = (string)$val->guest_account_bank_code; //开户行编码
            $post_data['card_num']          = (string)$val->guest_account; //银行账户
            $post_data['card_name']         = (string)$val->guest_account_name; //开户名
            $post_data['amount']            = (string)$val->settle_amount;//还款金额（元）
            $post_data['amount_type']       = 'CNY';//币种
            $post_data['mobile']            = (string)$val->user_mobile; //手机号
            $post_data['certificate_type']  = '01';  //证件类型 01-身份证
            $post_data['certificate_num']   = (string)$val->identityid; //证件号
            $post_data['charset']           = 'UTF-8';
            $post_data['remark']            = '';
            sleep(2);//由于并发限定2s/次
            $result = $this->getApi($aid)->send($post_data,1);
            $res    = $this->saveStatus($val, $result);
            if($result && in_array($result['result_code'],$this->commitProcessCode))
                $success += count($val);
        }
        //5 返回结果
        return $success;
    }

    public function runQuerys() {
        $res = [];
        $aids = [4, 1];
        foreach ($aids as $aid) {
            $res[$aid] = $this->_runQuerys($aid);
        }
        return $res;
    }

    /**
     * 单条查询
     * 暂定每分钟最多跑50个
     */
    public function _runQuerys($aid)
    {   
        //1 一次性处理最大设置为10
        $initRet   = ['total' => 0, 'success' => 0];
        $restNum   = 50;
        $oRemit    = new RbRemit;
       
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
        //4 逐条处理
        $total   = count($remitData);
        $success = 0;
        foreach ($remitData as $oRemit) {
            $oRemit->refresh();
            sleep(2);//由于并发限定2s/次
            $result = $this->doQuery($oRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('rbcredit', 'rbcredit/runQuerys', '处理失败', $oRemit);
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
        if (empty($remitData)) {
            return false;
        }
        $content_text = isset($result['result_msg']) ? $result['result_msg'] : '无响应';
        if (in_array($result['result_code'],$this->commitFailCode)) {
            //保存出款表中,提交失败，恢复待出款状态
            $res = $remitData->saveRspStatus(RbRemit::STATUS_FAILURE, $result['result_code'], $content_text,'',1);
            if (!$res) {
                Logger::dayLog('rbcredit', 'rbcredit/doRemit', 'Remit/saveRspStatus', $remitData->errors);
            }
            $this->addNotify($remitData);
        } else{
            // 保存出款表中,提交成功或者状态未知，更改状态为处理中
            $res = $remitData->saveRspStatus(RbRemit::STATUS_DOING, $result['result_code'], $content_text,$result['credit_id'], 1);
            if (!$res) {
                Logger::dayLog('rbcredit', 'rbcredit/saveRspStatus', $remitData->errors);
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
     * 处理单条出款
     * @param object $oRemit
     * @return bool
     */
    private function doQuery($oRemit)
    {
        //1 参数验证
        if (!$oRemit) {
            return false;
        }
        $data = [
            'customer_order' => (string)$oRemit->client_id,
            'trans_time'     => (string)date('Y-m-d',strtotime($oRemit->create_time)), //交易请求
            'charset'        => 'UTF-8',
        ];
        $succNum = 0;
        //5 提交到接口中并解析响应结果
        $response = $this->getApi($oRemit['aid'])->send($data,2);
        if (!empty($response) && $response['result_code'] == '0001') {
            $oRemit->refresh();
            $resCode = explode(',',$response['content'])[0];
            $resMsg  = explode(',',$response['content'])[1];
            switch ($resCode) {
            case 'R':
                $state = RbRemit::STATUS_DOING;
                break;
            case 'S':
                $state = RbRemit::STATUS_SUCCESS;
                break;
            case 'F':
                $state = RbRemit::STATUS_FAILURE;
                break;
            default:
                $state = RbRemit::STATUS_DOING;
                break;
            }
            // 保存查询表中
            $result = $oRemit->saveRspStatus($state, '', $resMsg,'', 2);
            if (!$result) {
                Logger::dayLog('rbcredit','rbcredit/doQuery', 'rbcredit/saveRspStatus', $oRemit->id, $oRemit->errors);
            }
            if ($state != RbRemit::STATUS_DOING) {
                // 加入到通知列表中
                $result = $this->addNotify($oRemit);
                $succNum++;
            }
        }else{
            $result = $oRemit->saveRspStatus(RbRemit::STATUS_DOING, '', '', '',2);
            if (!$result) {
                Logger::dayLog('rbcredit','rbcredit/doQuery', 'rbcredit/saveRspStatus', $oRemit->id, $oRemit->errors);
            }
            Logger::dayLog('rbcredit', 'doQuery', $oRemit->id,'查询超时');
        }
        return true;
    }

    /**
     * 加入通知列表中
     */
    private function addNotify(RbRemit $oRemit)
    {
        if (in_array($oRemit['remit_status'], [RbRemit::STATUS_SUCCESS, RbRemit::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result        = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('rbcredit', 'rbcredit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }

}

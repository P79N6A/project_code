<?php

/**
 * 计划任务处理:融宝出款流程
 */

namespace app\modules\api\common\rbremit;

use app\common\Logger;
use app\models\rongbao\ClientNotify;
use app\models\rongbao\Remit;
use app\modules\api\common\rbremit\RbApi;
use yii\helpers\ArrayHelper;
use Yii;

set_time_limit(0);

class Rbremit
{
    /**
     * 初始化接口
     */
    public function __construct()
    {

    }
    /**
     * 按channel_id取不同的配置
     * @param  int  $channel_id 用于区分不同的商编
     * @return RbApi
     */
    private function getApi($channel_id)
    {
        static $map = [];
        $is_prod    = SYSTEM_PROD;
        $is_prod = true;
        $env = $is_prod ? 'prod' . $channel_id : 'dev';
        if (!isset($map[$channel_id])) {
            $map[$channel_id] = new RbApi($env);
        }
        return $map[$channel_id];
    }
    /**
     * 暂时五分钟跑一批:
     * 处理出款
     */
    public function runRemits()
    {
        $res  = [];
        $channel_ids =[176];
        foreach ($channel_ids as $channel_id) {
            $res[$channel_id] = $this->_runRemits($channel_id);
        }
        return $res;
    }
    /**
     * 按不同商编出款
     * @param  int $channel_id
     * @return []
     */
    private function _runRemits($channel_id)
    {
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];
        //2 一次性处理最大设置为20 约(200/12(60/5分))
        $restNum   = 50;
        $oRemit    = new Remit();
        $remitData = $oRemit->getInitData($restNum, $channel_id);
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
        $amount        = 0;
        $remit_success = [];
        foreach ($remitData as $key => $oRemit) {
            // $oRemit->refresh();
            $isLock=$oRemit->lockOneRemit();
            if(!$isLock){
                continue;
            }
            $result = $this->getRemit($oRemit);
            if ($result) {
                $success++;
                $amount += $oRemit['settle_amount'];
                $oRemit->batch_id = $success;
                $result = $oRemit->save();
                if($result){
                    $remit_success[] = $oRemit;
                }else{
                    Logger::dayLog('rbremit/remit', 'Rbremit/runRemits', '保存batch_id失败', $oRemit->errors);
                }
            } else {
                $res = $oRemit->saveRspStatus(Remit::STATUS_FAILURE, '_ERROR', '规则出款限制', '', 1);
                if (!$res) {
                    Logger::dayLog('rbremit/remit', 'RbRemit/saveRspStatus', $oRemit->errors);
                }
                $this->addNotify($oRemit);
                Logger::dayLog('rbremit/remit', 'Rbremit/runRemits', '处理失败', $oRemit);
            }
            $batch_no = $oRemit['id'];
        }

        if (empty($remit_success)) {
            $initRet = ['total' => $total, 'success' => 0];
            return $initRet;
        }

        //设置当前请求的批次号
        // $batch_no = $oRemit->getBatchNo(date('Y-m-d'));
        $ids2     = ArrayHelper::getColumn($remit_success, 'id');
        $batch    = $oRemit->setBatchNo($ids2, $batch_no);
        if (!$batch_no && $batch) {
            return $initRet;
        }

        $post_data      = $this->getBanthContent($remit_success, $batch_no, $amount, $success, $channel_id);
        $sub_remit_time = $post_data['trans_time'];
        Logger::dayLog(
                        'rbremit/curl', '请求日志', $batch_no, $post_data
                );
        $result = $this->getApi($channel_id)->payApply($post_data);
        Logger::dayLog(
            'rongbao', '返回日志', $batch_no, $result
        );
        $res     = $this->saveStatus($remitData, $result, $sub_remit_time);
        $success = $result->result_code == '0000' ? $success : 0;
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    /**
     * 单条查询
     * 暂定每分钟最多跑20个
     */
    public function runQuerys()
    {
        //1 一次性处理最大设置为10
        $initRet   = ['total' => 0, 'success' => 0];
        $restNum   = 50;
        $oRemit    = new Remit;
        $remitData = $oRemit->getDoingData($restNum);
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
            // $oRemit->refresh();
            $isLock=$oRemit->lockOneQuery();
            if(!$isLock){
                continue;
            }
            sleep(2);//由于并发限定2s/次
            $result = $this->doQuery($oRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('rbremit/query', 'CRemit/runQuerys', '处理失败', $oRemit);
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
    private function saveStatus($remitData, $result, $sub_remit_time = '')
    {
        if (empty($remitData)) {
            return false;
        }
        $content_text = isset($result->result_msg) ? $result->result_msg : '无响应';
        if ($result->result_code == '0000') {
            foreach ($remitData as $key => $oRemit) {
                //7.1 保存出款表中,提交成功，更改状态为处理中
                $res = $oRemit->saveRspStatus(Remit::STATUS_DOING, $result->result_code, $content_text, $sub_remit_time, 1);
                if (!$res) {
                    Logger::dayLog('rbremit/query', 'RbRemit/saveRspStatus', $oRemit->errors);
                }
            }
        } elseif (isset($result->result_code)) {
            foreach ($remitData as $key => $oRemit) {
                //7.1 保存出款表中,提交失败，恢复待出款状态
                $res = $oRemit->saveRspStatus(Remit::STATUS_FAILURE, $result->result_code, $content_text, $sub_remit_time, 1);
                if (!$res) {
                    Logger::dayLog('rbremit/query', 'CRemit/doRemit', 'Remit/saveRspStatus', $oRemit->errors);
                }
                $this->addNotify($oRemit);
            }
        }
        return true;
    }

    /**
     * 批量代付提交 组合代付数据格式
     * @param [] $remitData
     * @param str $batch_no
     * @param int $amount
     * @param int $num 数量
     * @param int $aid 用于区分商编
     * @return []
     */
    private function getBanthContent($remitData, $batch_no, $amount, $num, $channel_id)
    {
        $con    = $this->getApi($channel_id)->getConf();
        $params = [
            'trans_time'   => date('Y-m-d H:i:s'),
            'notify_url'   => $con['notify_url'], //Yii::$app->params['rongbao_notify_url'], //$this->oRbApi->config['notify_url'],
            'batch_no'     => $batch_no,
            'batch_count'  => $num,
            'pay_type'     => '1',
            'batch_amount' => $amount,
            'content'      => $this->getContent($remitData),
            "charset"      => "UTF-8",
        ];
        if (!SYSTEM_PROD) {
            //测试地址，自己根据情况来改动，不能用函数来获取
            $params['notify_url'] =  'http://182.92.80.211:8799/rbremitback/rbremit/prod'.$channel_id;
        }
        return $params;
    }

    private function getContent($contents)
    {

        if (empty($contents)) {
            return '';
        }
        //序号,银行账户,开户名,
        //开户行,分行,支行,
        //公/私,金额,币种,
        //省,市,手机号,
        //证件类型,证件号,用户协议号,
        //商户订单号,备注
        $content = '';
        foreach ($contents as $key => $val) {
            $content .= $val['batch_id'] . ',' . $val['guest_account'] . ',' . $val['guest_account_name'] . ',';
            $content .= $val['guest_account_bank'] . ',,,';
            $content .= '私,' . $val['settle_amount'] . ',CNY,';
            $content .= $val['guest_account_province'] . ',' . $val['guest_account_city'] . ',' . $val['user_mobile'] . ',';
            $content .= '身份证,' . $val['identityid'] . ',,';
            $content .= $val['client_id'] . ',' . $val['settlement_desc'];
            $content .= '|';
        }
//        1,210302196001012114,韩梅梅,建设银行,分行,支行,私,2,CNY,北京,北京,13220482188,身份证,210302196001012114,jk-07253dd,xh1234,test
        return substr($content, 0, count($content) - 2);
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

        $data = $this->getsinglequeryApply($oRemit);
        //5 提交到接口中并解析响应结果
        $response = $this->getApi($oRemit['channel_id'])->singlepayqueryApply($data);
        //5.1 解析状态响应码
        $oRemitStatus = new RemitStatus();
        $result       = $oRemitStatus->parseQueryStatus($response);
        if (!$result) {
            return false;
        }

        //5.2 保存查询表中
        $result = $oRemit->saveRspStatus($oRemitStatus->remit_status, $oRemitStatus->rsp_status, $oRemitStatus->rsp_status_text, '', 2);
        if (!$result) {
            Logger::dayLog('rbremit/query', 'Remit/saveRspStatus', $oRemit->id, $oRemit->errors);
            return false;
        }

        //6 加入到通知列表中
        $result = $this->addNotify($oRemit);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * 单笔查询数据格式
     * @param  [] $payData
     * @return []
     */
    private function getsinglequeryApply($payData)
    {
        $data = [
            'trans_time' => date('Y-m-d', strtotime($payData['sub_remit_time'])), //交易时间
            //            'notify_url' => $this->config['batchpay_notify_url'], //异步地址
            'batch_no'   => $payData['batch_no'],
            "charset"    => "UTF-8",
            //"detail_no"  => $payData['id'],
            "detail_no"  => (string)$payData['batch_id'],
        ];
        return $data;
    }

    public function InputNotify(Remit $oRemit)
    {
        return $this->addNotify($oRemit);
    }

    /**
     * 加入通知列表中
     */
    private function addNotify(Remit $oRemit)
    {
        if (in_array($oRemit['remit_status'], [Remit::STATUS_SUCCESS, Remit::STATUS_FAILURE])) {
            $oClientNotify = new ClientNotify();
            $result        = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('rbremit/Notify', 'CRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }
    public function runErrorQuerys()
    {
        //1 一次性处理最大设置为10
        $initRet   = ['total' => 0, 'success' => 0];
        $restNum   = 700;
        $oRemit    = new Remit;
        $remitData = $oRemit->getErrorData($restNum);
        //var_dump($remitData);die;
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
            // $oRemit->refresh();
            $isLock=$oRemit->lockOneQuery();
            if(!$isLock){
                continue;
            }
            sleep(2);//由于并发限定2s/次
            $result = $this->doErrorQuery($oRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('rbremit/query', 'CRemit/runQuerys', '处理失败', $oRemit);
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
    private function doErrorQuery($oRemit)
    {
        //1 参数验证
        if (!$oRemit) {
            return false;
        }

        $data = $this->geterrorsinglequeryApply($oRemit);
        //5 提交到接口中并解析响应结果
        $response = $this->getApi($oRemit['channel_id'])->singlepayqueryApply($data);
        //5.1 解析状态响应码
        $oRemitStatus = new RemitStatus();
        $result       = $oRemitStatus->parseQueryStatus($response);
        if (!$result) {
            return false;
        }

        //5.2 保存查询表中
        $result = $oRemit->saveRspStatus($oRemitStatus->remit_status, $oRemitStatus->rsp_status, $oRemitStatus->rsp_status_text, '', 2);
        if (!$result) {
            Logger::dayLog('rbremit/query', 'Remit/saveRspStatus', $oRemit->id, $oRemit->errors);
            return false;
        }

        //6 加入到通知列表中
        $result = $this->addNotify($oRemit);
        if (!$result) {
            return false;
        }

        return true;
    }
    /**
     * 单笔查询数据格式
     * @param  [] $payData
     * @return []
     */
    private function geterrorsinglequeryApply($payData)
    {
        $data = [
            'trans_time' => date('Y-m-d', strtotime($payData['sub_remit_time'])), //交易时间
            //            'notify_url' => $this->config['batchpay_notify_url'], //异步地址
            'batch_no'   => $payData['batch_no'],
            "charset"    => "UTF-8",
            "detail_no"  => $this->getErrorDataKey($payData['batch_no'],$payData['id']),
        ];
        return $data;
    }
    /**
     * Undocumented function
     * 根据batch_no查询数据 返回key+1
     * @param [type] $batch_no
     * @return void
     */
    public function getErrorDataKey($batch_no,$id){
        $return_key = 0;
        $oRemit    = new Remit;
        $batchData = $oRemit->getBatchData($batch_no);
        if(empty($batchData)) return $return_key;
        foreach($batchData as $key=>$val){
            if($val['id']==$id){
                $return_key = $key+1;
                break;
            }
        }
        
        return $return_key;
    }
}

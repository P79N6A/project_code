<?php

/**
 * 控制器功能
 * 计划任务处理: 玖富查询的逻辑类
 * @author lijin
 */
namespace app\modules\api\common\jiufu;

use app\common\Logger;
use app\models\jiufu\JFApiLog;
use app\models\jiufu\JFClientNotify;
use app\models\jiufu\JFRemit;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CJFRemit
{
    /**
     * 接口类
     */
    private $oRemitApi;
    /**
     * 初始化接口
     */
    public function __construct($env)
    {
        $this->oRemitApi = new JFApi($env);
    }
    public function runRemits()
    {
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];

        //2 一次性处理最大设置为20 约(200/12(60/5分))
        $oRemit = new JFRemit;
        $remitData = $oRemit->getInitData(100);
        if (!$remitData) {
            return $initRet;
        }

        //3 锁定状态为出款中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }

        //4 逐条处理
        $total = count($remitData);
        $success = 0;
        foreach ($remitData as $oRemit) {
            //1 判断条件
            $isLock = $oRemit -> lockOneRemit();
            if(!$isLock){
                continue;
            }

            $appId = $oRemit->order_id;
            if (!$appId) {
                continue;
            }
         
            //2 等综合审批; 拆分状态
            if (in_array($oRemit->order_status, ['F0206', 'F0281'])) {
                // -> F0220待签
                $result = $this->toContractStatus($oRemit);
                if(!$result){
                    continue;
                }
            }

            //3. 进行出款操作
            $result = $this->doRemit($oRemit);
            if ($result) {
                $success++;
            }
        }

        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }
    /**
     * 调用并纪录接口日志
     * 待综合审批F0206 -> F0220 待签
     * @param  object $oRemit
     * @return bool
     */
    private function toContractStatus($oRemit)
    {
        //1 纪录日志
        $oApiLog = new JFApiLog;
        $result = $oApiLog->saveData($oRemit['id'], $oRemit['remit_status'], 'toContractStatus');

        //2 调用查询订单接口
        $res = $this->oRemitApi->query($oRemit['order_id'], $oRemit['product_id']);
        $order_status = $res['res_code'] == 0 ? ArrayHelper::getValue($res, 'res_data.appStatus', '') : '';
        $result = $oRemit->saveRspStatus($res['res_code'], $res['res_data'], $order_status);
        $result = $oApiLog->saveRspStatus($oRemit['remit_status'], $oRemit['rsp_status'], $oRemit['rsp_status_text'], $oRemit['order_status']);

        //3 响应结果
        if (in_array($order_status, ['F0206', 'F0281'])) {
            // 等待 F0220 的到来
            $result = $oRemit->nextInitTime();
            return $result;
        }
        elseif (in_array($order_status, $oRemit->getInitOrderStatus())) {
            //保存当前的初始流程码
            $result = $oRemit->saveInitOrderStatus($order_status);
            return $result;
        }
        else {
            //更新为失败并通知
            $oRemit->saveToFailed();
            $result = $this->addNotify($oRemit);
            return false;
        }
    }

    /**
     * 处理单条出款: 仅处理F0206, F0220订单
     * @param object $oRemit
     * @return bool
     */
    private function doRemit($oRemit)
    {
        //1. 生成合同 F0220 -> F0222 待查看合同
        if ($oRemit->order_status == 'F0220') {
            // 待生成合同
            $result = $this->getApi($oRemit, 'generateContract');
            if (!$result) {
                return false;
            }
        }

        //2 请求签名 待查看合同(待签):F0222 -> F0223:待电子签章(确认)
        if ($oRemit->order_status == 'F0222') {
            $result = $this->getApi($oRemit, 'seeContract');
            if (!$result) {
                return false;
            }else{
                // 几分钟后才进行确认签章，否则因为时间差会出错
                $result = $oRemit->nextInitTime();
                return true;
            }
        }

        //3 确认电子签章 待电子签章:F0223 -> F0225:待运营审核
        if ($oRemit->order_status == 'F0223') {
            $result = $this->getApi($oRemit, 'getSealCommon');
            if(!$result){
                return false;
            }
        }
       
        //4 更新状态 -> 进行中 | 失败
        // 保留F0223因为 确认电子签章 的接口延迟比较严重
        if ( in_array($oRemit->order_status, ['F0223','F0225', 'F0230', 'F0231','F0243'] ) ) {
            $result = $oRemit->saveToDoing();
        } else {
            return false; //锁定中
            //$result = $oRemit->saveToFailed();
            // 加入到失败通知列表中
            //$notify_result = $this->addNotify($oRemit);
        }

        return true;
    }

    /**
     * 整合定义接口和查询接口
     * @param  obj $oRemit  [description]
     * @param  string $apiName oRemitApi中对应的方法名称
     * @return bool
     */
    private function getApi($oRemit, $apiName)
    {
        //1. 调用api接口
        $appId = $oRemit->order_id; // 玖富工单号
        $oApiLog = new JFApiLog;
        $result = $oApiLog->saveData($oRemit['id'], $oRemit['remit_status'], $apiName);

        //2. 调用接口
        $order_status = "";
        if (SYSTEM_PROD) {
            $res = call_user_func([$this->oRemitApi, $apiName], $appId, $oRemit['product_id']);
            if ($res['res_code'] != 0) {
                Logger::dayLog('9f', 'CRemit/getApi', '调用接口失败', $appId, $apiName, $res);
            }
            // 调用查询接口: 更新订单状态, 纪录日志
            $res = $this->oRemitApi->query($appId, $oRemit['product_id']);
            $order_status = ArrayHelper::getValue($res, 'res_data.appStatus', '');
        }
        else {
            // 测试桩
            $appStatus = [
                'generateContract' => 'F0222',
                'seeContract' => 'F0223',
                'getSealCommon' => 'F0225',
            ];
            $res = ['res_code' => 0, 'res_data' => '测试桩'];
            $order_status = $appStatus[$apiName];
        }

        //3. 保存接口数据
        $result = $oRemit->saveRspStatus($res['res_code'], $res['res_data'], $order_status);
        $result = $oApiLog->saveRspStatus($oRemit['remit_status'], $oRemit['rsp_status'], $oRemit['rsp_status_text'], $oRemit['order_status']);

        return $res['res_code'] == 0;
    }
    // 出款流程结束-----


    /**
     * 处理查询
     * 暂定每分钟最多跑10个
     */
    public function runQuerys()
    {
        //2 一次性处理最大设置为10
        $initRet = ['total' => 0, 'success' => 0];
        $oRemit = new JFRemit;
        $remitData = $oRemit->getDoingData(200);
        if (!$remitData) {
            return $initRet;
        }

        //3 锁定状态为查询中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockQuery($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }

        //4 逐条处理
        $total = count($remitData);
        $success = 0;
        foreach ($remitData as $oRemit) {
            $isLock = $oRemit -> lockOneQuery();
            if(!$isLock){
                continue;
            }
            $result = $this->doQuery($oRemit);
            if ($result) {
                $success++;
            }
            else {
                Logger::dayLog('9f', 'CRemit/runQuerys', '处理失败', $oRemit);
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
    private function doQuery($oRemit)
    {
        //1 参数验证
        if (!$oRemit) {
            return false;
        }
        if (!isset($oRemit['order_id']) || !$oRemit['order_id']) {
            return false;
        }

        //2. 调用api接口
        $appId = $oRemit->order_id; // 玖富工单号
        $oApiLog = new JFApiLog;
        $result = $oApiLog->saveData($oRemit['id'], $oRemit['remit_status'], 'query');

        //3. 调用接口
        $res = $this->oRemitApi->query($appId, $oRemit['product_id']);
        $order_status = ArrayHelper::getValue($res, 'res_data.appStatus', '');

        //3. 保存接口数据
        $rsp_status_text = is_string($res['res_data']) ? $res['res_data'] : 'query ok';
        $result = $oApiLog->saveRspStatus($oRemit['remit_status'], $res['res_code'], $rsp_status_text, $order_status);
        //$order_status = "F0243";//@todo

        /**
         * 失败状态列表
         *F0232 债匹异常
         *F0238 划拨异常
         *F0242 放款异常
         *F0244 放款退回
         */
        $fail_status = ['F0232', 'F0238', 'F0242', 'F0244'];
        if (in_array($order_status, $fail_status)) {
            //更新为失败
            $result = $oRemit->saveToFailed();
        }
        elseif ($order_status == 'F0243') {
            // F0243 放款成功
            $result = $oRemit->saveToPaying($order_status);

        }
        else {
            // 加入下次查询
            $result = $oRemit->nextQueryTime();
        }

        //4 加入到通知列表中
        $result = $this->addNotify($oRemit);
        return true;
    }
    /**
     * 处理支付结果查询
     * 暂定每分钟最多跑10个
     */
    public function runPayQuerys()
    {
        //2 一次性处理最大设置为10
        $initRet = ['total' => 0, 'success' => 0];
        $oRemit = new JFRemit;
        $remitData = $oRemit->getPayQueryData(200);
        if (!$remitData) {
            return $initRet;
        }

        //3 锁定状态为查询中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockPayQuery($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }

        //4 逐条处理
        $total = count($remitData);

        //2. 保存接口数据
        $total = 0;
        foreach ($remitData as $oRemit) {
            $isLock = $oRemit -> lockOnePayQuery();
            if(!$isLock){
                continue;
            }
            $result = $this->doOnePayQuery($oRemit);
            if( $result ){
                $success ++;
            }
        }

        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }
    /**
     * 查询一次支付结果
     *
     * @param [type] $oRemit
     * @return void
     */
    private function doOnePayQuery($oRemit)
    {
        //1. 验证失败与否
        if (!$oRemit) {
            return false;
        }
        $res = $this->oRemitApi->queryPay($oRemit);
        $status = ArrayHelper::getValue($res, 'status');
        if($status === 'SUCCESS'){
            $oRemit->rsp_status = '0';
            $oRemit->rsp_status_text = "pay_success";
            $result = $oRemit->saveToSuccess();
        }elseif($status === 'FAIL'){
            //暂时关闭
            $rsp_status = ArrayHelper::getValue($res, 'rsp_status');
            $rsp_status_text = ArrayHelper::getValue($res, 'rsp_status_text');
            $oRemit->rsp_status = (string)$rsp_status;
            $oRemit->rsp_status_text =  (string)$rsp_status_text;
            $result = $oRemit->saveToFailed();
        }else{
            $result = $oRemit->nextPayTime();
        }

        //2 加入到通知列表中
        $result = $this->addNotify($oRemit);
    }

    /**
     * 加入通知列表中
     * 只有最终状态的才会去通知
     */
    private function addNotify(JFRemit $oRemit)
    {
        if (in_array($oRemit['remit_status'], [JFRemit::STATUS_SUCCESS, JFRemit::STATUS_FAILURE])) {
            $oClientNotify = new JFClientNotify;
            $result = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
            if (!$result) {
                Logger::dayLog('9f', 'cjfremit/addnotify', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }
    /**
     * 按id通知结果
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function clientNotify($id)
    {
        $id = intval($id);
        if (!$id) {
            return false;
        }
        $oRemit = JFRemit::findOne($id);
        return $this->addNotify($oRemit);
    }
    /**
     * 结束工单
     * @param  [] $order_ids
     * @return bool
     */
    public function endloads($order_ids)
    {
        //1. 查询订单
        if (!is_array($order_ids)) {
            return false;
        }
        $where = [
            'AND',
            ['order_id' => $order_ids],
            ['not in', 'remit_status', [JFRemit::STATUS_SUCCESS]],
        ];
        $remitData = JFRemit::find()->where($where)->limit(100)->all();
        if (empty($remitData)) {
            return false;
        }

        //2. 循环查询
        foreach ($remitData as $oRemit) {
            $res = $this->oRemitApi->loanend($oRemit['order_id'], $oRemit['product_id']);
            //$res = ['res_code'=>0,'res_data'=>[]];
            if ($res['res_code'] > 0) {
                Logger::dayLog('9f', 'cjfremit/endloads', '工单结束失败', $oRemit['order_id'], $res);
            }
            else {
                if ($oRemit->remit_status != JFRemit::STATUS_FAILURE) {
                    $result = $oRemit->saveToFailed();
                    $result = $this->addNotify($oRemit);
                }
            }
        }
        return true;
    }
}

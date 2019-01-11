<?php
/**
 * 计划任务处理:新浪出款流程
 * @author lijin
 */
namespace app\modules\api\common\sinapay;
use app\common\Logger;
use app\models\sina\SinaBindbank;
use app\models\sina\SinaRemit;
use app\modules\api\common\sinapay\Sinapay;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CSinaRemit {
    /**
     * 暂时五分钟跑一批:
     * 处理出款
     */
    public function runRemits() {
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];

        //2 一次性处理最大设置为20 约(200/12(60/5分))
        $oRemit = new SinaRemit;
        $remitData = $oRemit->getInitData(20);
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
            $result = $this->doRemit($oRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('sinaremit', 'CSinaRemit/runRemits', '处理失败', $oRemit);
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
    public function doRemit($oSinaRemit) {
        //1. 检测是否是超限的数据
        if (!$oSinaRemit) {
            return false;
        }
        if ($oSinaRemit->remit_status == SinaRemit::STATUS_INIT) {
            $oSinaRemit->remit_status = SinaRemit::STATUS_REQING_REMIT;
            $res = $oSinaRemit->save();
        }
        if ($oSinaRemit->remit_status != SinaRemit::STATUS_REQING_REMIT) {
            return false;
        }

        //2. 提交到接口中
        $remit_data = $this->getRemitApiData($oSinaRemit);
        $oSinapayApi = new Sinapay;
        for ($i = 0; $i < 2; $i++) {
            $response = $oSinapayApi->create_single_hosting_pay_to_card_trade($remit_data);
            $error = $oSinapayApi->errinfo;

            if ($oSinapayApi->isTimeout()) {
                // 查询是否真的没有提交
                $qr = $oSinapayApi->query_pay_result($oSinaRemit->req_id);
                if (is_array($qr) && isset($qr['response_code'])) {
                    if ($qr['response_code'] == 'ILLEGAL_OUTER_TRADE_NO') {
                        // 新浪查询无订单时, 那么重试
                        continue;
                    }elseif($qr['response_code'] == 'APPLY_SUCCESS'){
                        //有出款纪录时, 说明已经提交过. 成功与失败不可确认
                        Logger::dayLog("sinaremit", 'doRemit', '已经提交, 但状态不明', $oSinaRemit->id);
                        return false;
                    }
                }
            }

            break;
        }

        // 是否再次超时
        if ($oSinapayApi->isTimeout()) {
            Logger::dayLog("sinaremit", 'doRemit', '状态不明', $oSinaRemit->id);
            return false;
        } 

        //3. 错误处理
        if (!$response) {
            $err_data = json_decode($error, true);
            $result = $oSinaRemit->saveRspStatus($err_data['response_code'], $err_data['response_message'], '', '');

            Logger::dayLog("sinaremit", 'doRemit', '接口失败', $oSinaRemit->req_id, $error);
            return false;
        }

        //4. 保存出款状态
        $withdraw_status = isset($response['withdraw_status']) ? $response['withdraw_status'] : '';
        $client_id = isset($response['inner_trade_no']) ? $response['inner_trade_no'] : '';
        $result = $oSinaRemit->saveRspStatus($response['response_code'], $response['response_message'], $withdraw_status, $client_id);
        if (!$result) {
            Logger::dayLog("sinaremit", 'doRemit', '出款状态保存失败', $oSinaRemit->rsp_status, $oSinaRemit->errors);
        }

        return $result;
    }
    /**
     * 获取请求参数 150001-100
     * @param  [] $data 参数类型
     * @return [] 重组参数
     */
    private function getRemitApiData($data) {
        $card = (new SinaBindbank)->getSameCard($data['identity_id'], $data['cardno']);
        $sina_card_id = $card->sina_card_id;

        $remit_data = [];
        $remit_data['notify_url'] = 'http://open.xianhuahua.com/api/sinaback/remitnotifyurl';
        $remit_data['out_trade_no'] = $data['req_id'];
        $remit_data['out_trade_code'] = '2001'; //2001 代付借款金2002 代付（本金/收益）金
        $remit_data['amount'] = intval($data['settle_amount']);

        $remit_data['identity_id'] = $data['identity_id'];
        $remit_data['sina_card_id'] = $sina_card_id;
        //$remit_data['goods_id'] =  1;//'1469764926'; // 标的信息
        $remit_data['summary'] = '无';
        $remit_data['ip'] = $data['ip'];
        return $remit_data;
    }
}
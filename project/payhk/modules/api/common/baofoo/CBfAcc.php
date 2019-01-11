<?php
namespace app\modules\api\common\baofoo;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\baofoo\BfPayOrder;
use app\models\policy\ZhanPolicy;
use Yii;

/**
 * @desc 宝付认证API
 * @author lubaba
 */
class CBfAcc {
    private $bfChannel = 114;//转账通道ID
    //提交成功 未知
    private static $bfSubSuccCode = [
        '0000', //代付请求交易成功（交易已受理）
        '0300', //代付交易未明，请发起该笔订单查询
        '0401', //未知，请根据对账文件
        '0999', //代付主机系统繁忙
    ];

    //转账最终状态结果0：转账中；1：转账成功；-1：转账失败；2：转账退款
    private static $bfState = [
        'doing' => '0',
        'succ' => '1',
        'fail' => '-1',
        'refund' => '2'
    ];
    //失败
    private static $bfFailCode = [
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
     * @desc 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod102
     */
    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }
    /**
     * @desc 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    public function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new BaofooApi($cfg);
        }
        return $map[$channel_id];
    }

    /**
     * @提交商户转账
     * 
     */

    public function runPayment() {
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];
        //2 一次性处理最大设置为100
        $restNum = 100;
        $oPay = new BfPayOrder();
        $remitData = $oPay->getInitData($restNum);
        if (!$remitData) {
            return $initRet;
        }
        //3 锁定状态为出款中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oPay->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理过滤
        $total = count($remitData);
        $success = 0;
        foreach ($remitData as $key => $oBfRemit) {
            $result = $this->doRemit($oBfRemit);
            if ($result) {
                $success++;
            } 
        }
        return ['total' => $total, 'success' => $success];
    }



    /**
     * 单条支付
     * @param 
     * @return
     */
    private function doRemit($oBfRemit){
        $isLock=$oBfRemit->lockOneRemit();
        if(!$isLock){
            Logger::dayLog('bfpay', 'doRemit', '乐观锁失败', $oBfRemit->id);
            return false;
        }
        //是否有限制一天可转账几次
        $result = $this->dealTrade($oBfRemit);
        return $result;
    }
    /**
     * @desc 请求宝付转账交易接口
     * @param obj $remit_success
     * @return int $success
     */
    private function dealTrade($oBfRemit){
        $orderInfo = [];
        $orderInfo['trans_no'] = $oBfRemit->client_id;
        $orderInfo['trans_money'] = $oBfRemit->settle_amount;
        $orderInfo['trans_summary'] = '宝付转账';

        sleep(1);//由于并发限定1s/次
        $bfApiObj = $this->getApi($this->bfChannel);
        $response = $bfApiObj->transferAcc($orderInfo);
        // var_dump($response);exit;
        $res = $this->saveStatus($oBfRemit,$response);
        return $res;
    }
    /**
     * 请求成功，回写单子状态
     * @param [] $remitData  
     * @param [] $result
     * @return boolean  0：转账中；1：转账成功；-1：转账失败；2：转账退款
     */
    private function saveStatus($oBfRemit,$result) {
        if (empty($result) || empty($oBfRemit)) {
            return false;
        }
        $code = ArrayHelper::getValue($result, 'trans_content.trans_head.return_code','');
		$msg = ArrayHelper::getValue($result, 'trans_content.trans_head.return_msg','');
        $bfOrderid = (string)ArrayHelper::getValue($result, 'trans_content.trans_reqDatas.0.trans_reqData.trans_orderid', '');//宝付订单号
        $trans_no = ArrayHelper::getValue($result, 'trans_content.trans_reqDatas.0.trans_reqData.trans_no', '');//订单流水号
        $state = ArrayHelper::getValue($result, 'trans_content.trans_reqDatas.0.trans_reqData.state', '');
        $oPolicy = new ZhanPolicy;
        if(in_array($code, self::$bfFailCode) || $state == self::$bfState['fail']){//转账失败
            $res = $oBfRemit->saveToFail($code,$msg);
            $pres = $oPolicy->upPolicy($oBfRemit->req_id,$trans_no,ZhanPolicy::PAY_FAILURE);
        }

        if( in_array($code, self::$bfSubSuccCode) || $state == self::$bfState['doing']){//处理中
            $res = $oBfRemit->saveToDoing($bfOrderid);
            $pres = $oPolicy->upPolicy($oBfRemit->req_id,$trans_no,ZhanPolicy::PAY_DOING);
        }

        if( $code == '0000' && $state == self::$bfState['succ']){//转账成功
            $res = $oBfRemit->saveToSuccess($bfOrderid);
            $pres = $oPolicy->upPolicy($oBfRemit->req_id,$trans_no,ZhanPolicy::PAY_SUCCESS);
        }
        //@todo 转账退款
        if( $code == '0000' && $state == self::$bfState['refund']){//退款
            $res = $oBfRemit->saveToRefund($bfOrderid);
            $pres = $oPolicy->upPolicy($oBfRemit->req_id,$trans_no,ZhanPolicy::PAY_FAILURE);
        }

        if(!$res){
            Logger::dayLog('bfpay','saveStatus','更新转账状态失败',$result);
            return false;
        }

        if(!$pres){
            Logger::dayLog('bfpay','upPolicyStatus','更新保单支付状态失败',$result);
            return false;
        }

        return true;
    }

    /**
     * @查询转账结果
     * 
     */
    public function runPayquery(){
        //1 一次性处理最大设置为10
        $initRet   = ['total' => 0, 'success' => 0];
        $restNum   = 100;
        $oRemit    = new BfPayOrder;
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
            $isLock=$oRemit->lockOneQuery();
            if(!$isLock){
                continue;
            }
            $result = $this->doQuery($oRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('remitchaxun', 'CRemit/runQuerys', '处理失败', $oRemit);
            }
        }

        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    /**
     * 处理单条转账查询
     * @param object $oRemit
     * @return bool
     */
    private function doQuery($oRemit){
        //1 参数验证
        if (!$oRemit) {
            return false;
        }
        // 提交到接口中并解析响应结果
        $data = [];
        $data['trans_no'] = $oRemit->client_id;
        $response = $this->getApi($this->bfChannel)->transferQuery($data);

        $res = $this->saveStatus($oRemit,$response);

        return $res;
    }

}

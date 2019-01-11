<?php
/**
 * 计划任务处理:保险转账
 * 弃用
 */
namespace app\modules\api\common\policy;
use app\common\Logger;
use app\models\policy\ZhanPolicy;
use yii\helpers\ArrayHelper;
use app\models\baofoo\BfPayOrder;


set_time_limit(0);

class CPolicyPay1 {
    private $oPolicy;
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oPolicy = new ZhanPolicy;
    }

    
    /**
     * 暂时五分钟跑一批:
     * 保存到宝付转账订单表中
     */
    public function runPay() {

        $initRet = ['total' => 0, 'success' => 0];
        //1 获取数据
        $restNum = 100;
        $dataList = $this->oPolicy->getPayInitData($restNum);
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oPolicy->lockPay($ids); // 锁定接口的请求
        if (!$ups) {
            return $initRet;
        }
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oPolicy) {
            $result = $this->doPay($oPolicy);
            if ($result) {
                $success++;
            } else {
                //如果保存失败 修改为支付初始状态
                $res = $oPolicy->toPayInit();
                if(!$res){
                    Logger::dayLog('policy/cpolicypay', 'toPayInit', '保存失败', $oPolicy->id);
                }              
            }
        }
        //5 返回结果
        return ['total' => $total, 'success' => $success];
    }
    private function doPay($oPolicy) {
        if(empty($oPolicy)) return false;
        $isLock=$oPolicy->lockOnePay();
        if(!$isLock){
            Logger::dayLog('policy/cpolicypay', 'lockOnePay', '锁失败', $oPolicy->id);
            return false;
        }
        $oPayOrder = new BfPayOrder;
        $postdata  = [
            'aid'    => $oPolicy->aid,
            'req_id' => $oPolicy->client_id,
            'settle_amount' => $oPolicy->premium,
        ];
        $result = $oPayOrder->saveRemitData($postdata);
        if(!$result){
            Logger::dayLog('policy/cpolicypay','saveRemitData','保存宝付订单失败',$oPayOrder->errinfo,$postdata);
        }
        return $result;
    }
}
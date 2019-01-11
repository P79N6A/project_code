<?php
/**
 * 计划任务处理:拉取账单
 */
namespace app\modules\api\common\xn;
use app\common\Logger;
use app\models\xn\XnClientNotify;
use app\models\xn\XnRemit;
use app\models\xn\XnBill;
use yii\helpers\ArrayHelper;
use app\modules\api\common\xn\XnApi;


set_time_limit(0);

class CxnLoan {
    private $XRemit;
    const RSP_SUCCESS_CODE =0;
    /**
     * 初始化接口 拉取账单接口
     */
    public function __construct() {
        $this->XRemit = new XnRemit;
    }

    /**
     * 配置
     * 
     * @return XnApi
     */
    private function getApi() {
        static $map = '';
        $is_prod = SYSTEM_PROD;
        //$is_prod = true;
        $env = $is_prod ? 'prod' : 'dev';
        $map = new XnApi($env);
        return $map;
    }
    /**
     * 一般是每几分钟执行
     */
    public function runQuery() {
        //查询需拉取账单的订单
        $restNum = 100;
        $dataList = $this->XRemit->getListLoan($restNum);
        return $this->runBill($dataList);
    }

    /**
     * 暂时五分钟跑一批:
     * 
     */
    public function runBill($dataList) {
        $initRet = ['total' => 0, 'success' => 0];
        //1 验证
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->XRemit->lockBill($ids); // 锁定拉账单接口的请求
        if (!$ups) {
            return $initRet;
        }
        //3 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oXnRemit) {
            $result = $this->doBill($oXnRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('xn/cxnloan', 'runBill', '处理失败', $oXnRemit->id);
            }
        }
        return ['total' => $total, 'success' => $success];
    }
   
    /**
     * 拉取账单
     * @param object $oXnRemit
     * @return bool
     */
    private function doBill($oXnRemit) {
        if (!$oXnRemit) {
            return false;
        }
        $isLock=$oXnRemit->lockOneBill();
        if(!$isLock){
            Logger::dayLog('xn/cxnloan', 'lockOneBill', '锁失败', $oXnRemit->id);
            return false;
        }
        $result = $this->dealTrade($oXnRemit);
        return $result;
    }

       /**
     * @desc 请求小诺
     * @param obj $remit_success
     * @return int $success
     */
    private function dealTrade($oXnRemit){
        $bodyInfo = $this->getFormatData($oXnRemit);
        $xnApiObj = $this->getApi();
        $result = $xnApiObj->getJsonParam($bodyInfo,'bill');
        $res = $this->saveStatus($oXnRemit, $result);
        return $res;
    }

    private function getFormatData($data)
    {
        $res = [
            'bid_no'=>$data['client_id'],
        ];
        return $res;
    }

    /**
     * 请求成功，保存数据为处理中
     * @param type $oXnRemit
     * @param type $result
     * @return boolean
     */
    private function saveStatus($oXnRemit, $result)
    {
        if (empty($oXnRemit)) {
            return false;
        }
        
        if(empty($result)){
            //请求超时 无响应 改成初始状态从新拉取
            $bill_status = XnRemit::BILL_INIT;
        }
        Logger::dayLog('xn/cxnloan','拉取账单结果',$result,$oXnRemit->client_id);
        $res_code = ArrayHelper::getValue($result,'code','');
        $billDatas = ArrayHelper::getValue($result,'bills');
        $billData = empty($billDatas[0])?[]:$billDatas[0];
        if ($res_code == self::RSP_SUCCESS_CODE) {
            //拉取成功
            $bill_status = XnRemit::BILL_SUCCESS;
            
        } else{
            $bill_status = XnRemit::BILL_INIT;
        }
        //如果成功保存到账单表中
        if($bill_status==XnRemit::BILL_SUCCESS){
            $billData['bid_no'] = $oXnRemit->client_id;
            $oBill = new XnBill;
            $res = $oBill->saveBillData($billData);
            if(!$res){
                Logger::dayLog('xn/cxnloan','账单保存失败',$billData,$oBill->errinfo);
                $bill_status = XnRemit::BILL_INIT;
            }
        }
        //更新出款账单拉取状态
        $res = $oXnRemit->updateRemitBill($bill_status);
        return $res;
    }


}
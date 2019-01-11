<?php
/**
 * 计划任务处理:小诺还款通知
 */
namespace app\modules\api\common\xn;
use app\common\Logger;
use app\models\xn\XnClientNotify;
use app\models\xn\XnRemit;
use app\models\xn\XnBill;
use yii\helpers\ArrayHelper;
use app\modules\api\common\xn\XnApi;


set_time_limit(0);

class CxnRepay {
    protected $XNbill;
    //成功 未知
    const commitProcessCode = 0;
    const BILL_STATUS_RETRY = 3; // 逾期未还
    const STATUS_SECCESS = 0;//正常
    const STATUS_FAILE = 1; //逾期
    /**
     * 初始化接口 还款通知接口
     */
    public function __construct() {
        $this->XNbill = new XnBill;
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
    public function runRepayment() {
        $restNum = 100;
        $dataList = $this->XNbill->getPaymentList($restNum);
        return $this->runPayment($dataList);
    }

    /**
     * 暂时五分钟跑一批:
     * 处理出款
     */
    public function runPayment($dataList) {
        $initRet = ['total' => 0, 'success' => 0];
        if (!$dataList) {   
            return $initRet;
        }
        //锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->XNbill->lockBill($ids); // 锁定还款通知接口的请求
        if (!$ups) {
            return $initRet;
        }
        //逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oBill) {
            $result = $this->doRepay($oBill);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('xn/cxnrepay', 'runPayment', '处理失败', $oBill->id);
            }
        }
        //返回结果
        return ['total' => $total, 'success' => $success];
    }
    /**
     * 处理单条还款通知
     * @param object $doRepay
     * @return bool
     */
    private function doRepay($oBill) {
        $isLock=$oBill->lockOneBill();
        if(!$isLock){
            Logger::dayLog('xn/cxnrepay', 'doRepay', '乐观锁失败', $oBill->id);
            return false;
        }
        if (!$oBill) {
            return false;
        }
        $result = $this->doTrade($oBill);
        return $result;
    }

       /**
     * @desc 请求小诺
     * @param obj $remit_success
     * @return int $success
     */
    private function doTrade($oBill){
        $bodyInfo = $this->mergeData($oBill);
        $xnApiObj = $this->getApi();
        $result = $xnApiObj->getJsonParam($bodyInfo,'repayment');
        $res = $this->saveStatus($oBill, $result);
        return $res;
    }

    public function mergeData($data)
    {
        $total = $data['repayment_corpus']+$data['repayment_interest']+$data['repayment_viorate']+$data['repayment_fine']+$data['repayment_fee'];
        $repay_type = $this->getRepayType($data['status']);
        $res = [
            'bid_no'=>$data['bid_no'],
            'period'=>1,
            'amount'=>$total,
            'repay_type'=>$repay_type
        ];
        return $res;
    }

    /**
     * 请求成功，保存数据为处理中
     * @param type $oBill
     * @param type $result
     * @return boolean
     */
    private function saveStatus($oBill, $result)
    {
        if (empty($oBill)) {
            return false;
        }
        
        if(empty($result)){
            //请求超时 无响应 改成初始状态从新通知
            $bill_status = XnBill::BILL_STATUS_INIT;
        }
        Logger::dayLog('xn/cxnrepay','还款通知结果',$result,$oBill->bid_no);
        $res_code = ArrayHelper::getValue($result,'code','');
        $res_msg = ArrayHelper::getValue($result,'msg','');
        if ($res_code == self::commitProcessCode) {
            //通知成功
            $bill_status = XnBill::BILL_STATUS_NOTICE_SUCCESS;
            
        } else{
            $bill_status = XnBill::BILL_STATUS_INIT;
        }
        //更新还款通知状态
        $res = $oBill->updateRepayStatus($bill_status,$res_code,$res_msg);
        if(!$res){
            Logger::dayLog('xn/cxnrepay','更新还款通知失败',$result,$oBill->bid_no);
        }
        return $res;
    }
    /**
     * Undocumented function
     * 获得还款类型 0正常1逾期
     * @param [type] $status
     * @return void
     */
    public function getRepayType($status)
    {
        if($status == self::BILL_STATUS_RETRY)
        {
            return self::STATUS_FAILE;
        }else{
            return self::STATUS_SECCESS;
        }   
    }
}
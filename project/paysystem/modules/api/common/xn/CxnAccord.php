<?php
/**
 * 计划任务处理:协议下载
 * 
 */
namespace app\modules\api\common\xn;
use app\common\Logger;
use app\models\xn\XnClientNotify;
use app\models\xn\XnRemit;
use app\models\xn\XnAgreement;
use yii\helpers\ArrayHelper;
use app\modules\api\common\xn\XnApi;


set_time_limit(0);

class CxnAccord {
    protected $XRemit;
    //成功 未知
    private static $commitProcessCode = 0;
    /**
     * 初始化接口 协议
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
    public function runAccord() {
        //1 获取需要的数据
        $restNum = 100;
        $dataList = $this->XRemit->getAgreementList($restNum);
        return $this->runAgreement($dataList);
    }

    /**
     * 暂时五分钟跑一批:
     */
    public function runAgreement($dataList) {
        $initRet = ['total' => 0, 'success' => 0];

        //1 验证
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->XRemit->lockAgreement($ids); // 锁定接口的请求
        if (!$ups) {
            return $initRet;
        }
        //3 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oXnRemit) {
            $result = $this->doRemit($oXnRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('xn/cxnaccord', 'doRemit', '处理失败', $oXnRemit->id);
            }
        }
        //4 返回结果
        return ['total' => $total, 'success' => $success];
    }
    /**
     * 拉取协议
     * @param object $oRemit
     * @return bool
     */
    private function doRemit($oXnRemit) {
        $isLock=$oXnRemit->lockOneAgreement();
        if(!$isLock){
            Logger::dayLog('xn/cxnaccord', 'lockOneAgreement', '乐观锁失败', $oXnRemit->id);
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
        $bodyInfo = $this->mergeData($oXnRemit);
        $xnApiObj = $this->getApi();
        $result = $xnApiObj->getJsonParam($bodyInfo,'agreement');
        $res = $this->saveStatus($oXnRemit, $result);
        return $res;
    }

    public function mergeData($data)
    {
        $res = [
            'bid_no'=>$data['client_id']
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
            $agreement_status = XnRemit::AGREEMENT_INIT;
        }
        Logger::dayLog('xn/cxnaccord','拉取协议结果',$result,$oXnRemit->client_id);
        $res_code = ArrayHelper::getValue($result,'code','');
        $msg = ArrayHelper::getValue($result,'msg','');
        $url = ArrayHelper::getValue($result,'url','');
        if ($res_code == self::$commitProcessCode) {
            $agreement_status = XnRemit::AGREEMENT_SUCCESS;
        } else{
            $agreement_status = XnRemit::AGREEMENT_INIT;
        }
        //如果成功保存到协议表中
        if($agreement_status==XnRemit::AGREEMENT_SUCCESS){
            //成功保存
            $bidNum = $oXnRemit['client_id'];
            $url_data = $this->getUrl($url);
            $url_data['code']=(int)$res_code;
            $url_data['msg'] = $msg;
            $result = (new XnAgreement)->saveData($bidNum,$url_data);
            if(!$result){
                Logger::dayLog('xn/cxnaccord', 'saveData', $url_data,$bidNum,'保存失败');
                $agreement_status = XnRemit::AGREEMENT_INIT;
            }
            
        }
        // 保存出款表中,提交成功或者状态未知
        $res = $oXnRemit->saveAgreementStatus($agreement_status);
        if (!$res) {
            Logger::dayLog('xn/cxnaccord', 'saveAgreementStatus', $oXnRemit->client_id,$agreement_status,$res);
        }  
        return $res;
    }

    public function getUrl($data)
    {
        $loan_url = isset($data[0]['second'])?$data[0]['second']:'';//借款协议
        $consulting_url = isset($data[1]['second'])?$data[1]['second']:'';//借款咨询协议
        $entrustment_url = isset($data[2]['second'])?$data[2]['second']:'';//委托协议
        $res=array(
            'loan_url'=>$loan_url,
            'consulting_url'=>$consulting_url,
            'entrustment_url'=>$entrustment_url
        );
        return $res;
    }

}
<?php
/**
 * 计划任务处理:上标流程
 */
namespace app\modules\api\common\xn;
use app\common\Logger;
use app\models\remit\RemitNotify;
use app\models\xn\XnRemit;
use app\models\xn\XnAgreement;
use yii\helpers\ArrayHelper;
use app\modules\api\common\xn\XnApi;
use app\modules\api\common\CRemitNotify;


set_time_limit(0);

class CxnRemit {
    protected $oRemit;
    //成功 未知
    private static $commitProcessCode = 0;
    /**
     * 初始化接口 上标接口
     */
    public function __construct() {
        $this->oRemit = new XnRemit;
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
     * 暂时五分钟跑一批:
     * 处理出款
     */
    public function runRemits($start_time, $end_time) {

        $initRet = ['total' => 0, 'success' => 0];
        //1 获取需要上标的数据
        //一次性处理最大设置为50
        $restNum = 100;
        $dataList = $this->oRemit->getInitData($start_time, $end_time,$restNum);
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oRemit->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oXnRemit) {
            $result = $this->doRemit($oXnRemit);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('xn/cxnremit', 'runRemits', '处理失败', $oXnRemit->id);
            }
        }
        //5 返回结果
        return ['total' => $total, 'success' => $success];
    }
    /**
     * 处理单条出款
     * @param object $oRemit
     * @return bool
     */
    private function doRemit($oXnRemit) {
        $isLock=$oXnRemit->lockOneRemit();
        if(!$isLock){
            Logger::dayLog('xn/cxnremit', 'lockOneRemit', '锁失败', $oXnRemit->id);
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
        $result = $xnApiObj->getJsonParam($bodyInfo,'bank');
        $res = $this->saveStatus($oXnRemit, $result);
        return $res;
    }

    private function getFormatData($data)
    {
        $tip = json_decode($data['tip'],true);
        $res = [
            'name'=>ArrayHelper::getValue($data,'guest_account_name',''),
            'tel'=>ArrayHelper::getValue($data,'user_mobile',''),
            'idNumber'=>ArrayHelper::getValue($data,'identityid',''),
            'bidNum'=>ArrayHelper::getValue($data,'client_id',''),
            'loanPeriod'=>ArrayHelper::getValue($data,'time_limit',''),
            'loanAmount'=>(int)ArrayHelper::getValue($data,'settle_amount','')*100,
            'bidApr'=>XnRemit::BIND_AIR,//借款利率
            'repaymentType'=>XnRemit::REPAYMENT_TYPE,//等本等息
            'loanPurpose'=>ArrayHelper::getValue($tip,'loan_purpose',''),
            'loanPurposeDesc'=>ArrayHelper::getValue($tip,'loanPurposeDesc',''),
            'bankCard'=>ArrayHelper::getValue($data,'guest_account',''),
            'bankName'=>ArrayHelper::getValue($data,'guest_account_bank',''),
            'bankMobile'=>ArrayHelper::getValue($data,'user_mobile',''),
            'proId'=>XnRemit::PRO_ID,
            'liveAddrDetail'=> ArrayHelper::getValue($tip,'liveAddrDetail',''),
            'company'=> ArrayHelper::getValue($tip,'company',''),
            'companyPhone'=>ArrayHelper::getValue($tip,'companyPhone',''),
            'isRepeatLoan'=>ArrayHelper::getValue($tip,'isRepeatLoan',''),
            'marryType'=>ArrayHelper::getValue($tip,'marryType',''),
            'hukouAddrDetail'=>ArrayHelper::getValue($tip,'hukouAddrDetail',''),
            'emergencyContactName1'=>ArrayHelper::getValue($tip,'emergencyContactName1',''),
            'emergencyContactRelation1'=>ArrayHelper::getValue($tip,'emergencyContactRelation1',''),
            'emergencyContactPhone1'=>ArrayHelper::getValue($tip,'emergencyContactPhone1',''),
            'gpsInfo'=>ArrayHelper::getValue($tip,'gpsInfo',''),
            'equipmentNum'=>ArrayHelper::getValue($tip,'equipmentNum',''),
            'loanIp'=>ArrayHelper::getValue($tip,'loanIp',''),
            'applyTime'=>ArrayHelper::getValue($data,'create_time',''),
            'faceRecognition'=>ArrayHelper::getValue($tip,'faceRecognition',''),
            'sex'=>ArrayHelper::getValue($tip,'customer_sex','')==2?1:0,
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
            //请求超时 无响应
            $res = $oXnRemit->saveToDoing();
            if(!$res){
                Logger::dayLog('xn/cxnremit', 'saveToDoing','响应超时',$oXnRemit->id);
                return false;
            }
            return true;
        }
        $res_code = ArrayHelper::getValue($result,'code','');
        $content_text = ArrayHelper::getValue($result,'msg','');
        if ($res_code != self::$commitProcessCode) {
            //保存出款表中,提交失败
            $res = $oXnRemit->saveToFail($res_code, $content_text);
            if (!$res) {
                Logger::dayLog('xn/cxnremit', 'XnRemit/saveStatus',  $oXnRemit->errors);
            }
            //失败加入通知列表中
            $this->addNotify($oXnRemit);
            return false;
        } else{
            // 保存出款表中,提交成功或者状态未知，更改状态为处理中
            $res = $oXnRemit->saveToDoing();
            if(!$res){
                Logger::dayLog('xn/cxnremit', 'saveToDoing','出款处理中',$oXnRemit->id,$result);
                return false;
            }
            return true;
        }
        
    }
    public function InputNotify(XnRemit $oRemit)
    {
        if (in_array($oRemit['remit_status'], [XnRemit::STATUS_SUCCESS, XnRemit::STATUS_FAILURE])) {
            $oClientNotify = new RemitNotify();
            $postdata = $oRemit->attributes;
            $postdata['channel_id'] = XnRemit::SOURCEID;
            unset($postdata['tip']);          
            $result = $oClientNotify->saveData($postdata);
            if (!$result) {
                Logger::dayLog('remitnotify/xn', 'XnRemit/InputNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
            $oNotify = new CRemitNotify();
            $res = $oNotify->synchroNotify($oClientNotify);
        }
        return true;
    }
    /**
     * 加入通知列表中
     */
    private function addNotify(XnRemit $oRemit) {
        if (in_array($oRemit['remit_status'], [XnRemit::STATUS_SUCCESS, XnRemit::STATUS_FAILURE])) {
            $oClientNotify = new RemitNotify();
            $postdata = $oRemit->attributes;
            $postdata['channel_id'] = XnRemit::SOURCEID;
            unset($postdata['tip']);         
            $result = $oClientNotify->saveData($postdata);
            if (!$result) {
                Logger::dayLog('remitnotify/xn', 'XnRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }


}
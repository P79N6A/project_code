<?php
/**
 * 计划任务处理:核保接口
 * 弃用，现在同步调用接口返回
 */
namespace app\modules\api\common\policy;
use app\common\Logger;
use app\models\policy\ZhanPolicy;
use yii\helpers\ArrayHelper;
use app\models\policy\PolicyNotify;
use app\modules\api\common\CPolicyNotify;
use app\modules\api\common\policy\CPolicyApi;


set_time_limit(0);

class CPolicyBatch {
    private $oPolicy;
    private $oApi;
    /**
     * 初始化接口 上标接口
     */
    public function __construct() {
        $this->oPolicy = new ZhanPolicy;
        $this->oApi = $this->getApi();
    }

    /**
     * 配置
     * 
     * @return Api
     */
    private function getApi() {
        static $map = '';
        $is_prod = SYSTEM_PROD;
        //$is_prod = true;
        $env = $is_prod ? 'prod' : 'dev';
        $map = new CPolicyApi($env);
        return $map;
    }
    /**
     * 暂时五分钟跑一批:
     * 处理核保
     */
    public function runPolicy() {

        $initRet = ['total' => 0, 'success' => 0];
        //1 获取数据
        $restNum = 100;
        $dataList = $this->oPolicy->getInitData($restNum);
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oPolicy->lockRemit($ids); // 锁定接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oPolicy) {
            $result = $this->doPolicy($oPolicy);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('policy/cpolicy', 'runPolicy', '处理失败', $oPolicy->id);
            }
        }
        //5 返回结果
        return ['total' => $total, 'success' => $success];
    }
    /**
     * 处理单条核保
     * @param object $oPolicy
     * @return bool
     */
    private function doPolicy($oPolicy) {
        $isLock=$oPolicy->lockOneRemit();
        if(!$isLock){
            Logger::dayLog('policy/cpolicy', 'lockOneRemit', '锁失败', $oPolicy->id);
            return false;
        }
        $result = $this->dealTrade($oPolicy);
        return $result;
    }

    /**
     * @desc 请求众安接口
     * @param obj $remit_success
     * @return int $success
     */
    private function dealTrade($oPolicy){
        $postdata = $this->getFormatData($oPolicy);
        $result = $this->oApi->call($postdata);
        $res = $this->saveStatus($oPolicy, $result);
        return $res;
    }
    /**
     * Undocumented function
     * 获取请求接口参数
     * @param [type] $oPolicy
     * @return void
     */
    private function getFormatData($oPolicy)
    {
        $policyDate = $oPolicy->policyDate;
        $policyBeginDate = date('Y-m-d',strtotime('+1 day'));
        $policyEndDate = date('Y-m-d',strtotime('+'.$policyDate.' day',strtotime($policyBeginDate)));
        $postdata = [
            'channelOrderNo'=>$oPolicy->client_id,
            'policyHolderUserName'=>$oPolicy->user_name,
            'policyHolderCertiType'=>'I',
            'policyHolderCertiNo'=>$oPolicy->identityid,
            'policyHolderPhone'=>$oPolicy->user_mobile,
            'relation' =>$oPolicy->relation,
            'benifitName'=>$oPolicy->benifitName,
            'benifitCertiType'=>$oPolicy->benifitCertiType,
            'benifitCertiNo'=>$oPolicy->benifitCertiNo,
            'sumInsured'=>$oPolicy->sumInsured,
            'premium'=>$oPolicy->premium,
            'policyBeginDate'=>$policyBeginDate,
            'policyEndDate'=>$policyEndDate
        ];
        return $postdata;
    }

    /**
     * 请求成功，保存数据为处理中
     * @param type $oPolicy
     * @param type $result
     * @return boolean
     */
    private function saveStatus($oPolicy, $result)
    {
        if (empty($oPolicy)) {
            return false;
        }
        if(empty($result)){
            //请求超时 无响应
            Logger::dayLog('policy/checktimeout','响应超时',$oPolicy->id,'响应结果',$result);
            return false;
        }
        $errorCode      = ArrayHelper::getValue($result,'bizContent.errorCode','');
        $errorMsg       = ArrayHelper::getValue($result,'bizContent.errorMsg','');
        $channelOrderNo = ArrayHelper::getValue($result,'bizContent.channelOrderNo','');
        $applyNo        = ArrayHelper::getValue($result,'bizContent.applyNo','');
        $isSuccess      = ArrayHelper::getValue($result,'bizContent.isSuccess','');
        if($oPolicy->client_id!=$channelOrderNo){
            Logger::dayLog('policy/cpolicy','订单号不同','client_id',$oPolicy->client_id,'channelOrderNo',$channelOrderNo);
            return false;
        }
        if ($isSuccess) {
            // 成功
            $res = $oPolicy->saveToDoing($errorCode,$errorMsg,$applyNo);
            if(!$res){
                Logger::dayLog('policy/cpolicy', 'saveToDoing','保存失败',$oPolicy->id,$result);
                return false;
            }
            return true;
            
        } else{
            //失败
            $res = $oPolicy->saveToCheckFail($errorCode, $errorMsg);
            if (!$res) {
                Logger::dayLog('policy/cpolicy', 'saveToFail',  $oPolicy->errors);
            }
            //失败加入通知列表中
            $this->addNotify($oPolicy);
            return false;
        }
        
    }
    /**
     * 加入通知列表中
     */
    private function addNotify(ZhanPolicy $oPolicy) {
        if (in_array($oPolicy['remit_status'], [ZhanPolicy::STATUS_SUCCESS,ZhanPolicy::STATUS_CHECKFAIL, ZhanPolicy::STATUS_FAILURE])) {
            $oClientNotify = new PolicyNotify();
            $postdata = $oPolicy->attributes;        
            $result = $oClientNotify->saveData($postdata);
            if (!$result) {
                Logger::dayLog('policynotify', 'addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
        }
        return true;
    }


}
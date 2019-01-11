<?php
/**
 * 计划任务处理:出保接口
 */
namespace app\modules\api\common\policy;
use app\common\Logger;
use app\models\policy\ZhanPolicy;
use yii\helpers\ArrayHelper;
use app\models\policy\PolicyNotify;
use app\modules\api\common\CPolicyNotify;
use app\modules\api\common\policy\CPolicyApi;


set_time_limit(0);

class CPolicyApply {
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
    public function runApply() {

        $initRet = ['total' => 0, 'success' => 0];
        //1 获取数据
        $restNum = 100;
        $dataList = $this->oPolicy->getDoingData($restNum);
        if (!$dataList) {   
            return $initRet;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oPolicy->lockApply($ids); // 锁定接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oPolicy) {
            $result = $this->doApply($oPolicy);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('policy/cpolicyapply', 'runApply', '处理失败', $oPolicy->id);
            }
        }
        //5 返回结果
        return ['total' => $total, 'success' => $success];
    }
    /**
     * 处理单条出单
     * @param object $oPolicy
     * @return bool
     */
    private function doApply($oPolicy) {
        $isLock=$oPolicy->lockOneApply();
        if(!$isLock){
            Logger::dayLog('policy/cpolicyapply', 'lockOneApply', '锁失败', $oPolicy->id);
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
        $result = $this->oApi->apply($postdata);
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
        $postdata = [
            'channelOrderNo'=>$oPolicy->client_id,
            'applyNo'=>$oPolicy->applyNo,
            'payTradeNo'=>$oPolicy->orderId,
        ];
        return $postdata;
    }

    /**
     * 请求成功，保存数据
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
            $res = $oPolicy->saveToDoing('-200','_timeout');
            if(!$res){
                Logger::dayLog('policy/cpolicyapply', 'saveToDoing','响应超时',$oPolicy->id);
                return false;
            }
            return true;
        }
        $errorCode      = ArrayHelper::getValue($result,'bizContent.errorCode','');
        $errorMsg       = ArrayHelper::getValue($result,'bizContent.errorMsg','');
        $channelOrderNo = ArrayHelper::getValue($result,'bizContent.channelOrderNo','');
        $policyNo       = ArrayHelper::getValue($result,'bizContent.policyNo','');
        $ePolicyUrl     = ArrayHelper::getValue($result,'bizContent.ePolicyUrl','');
        $isSuccess      = ArrayHelper::getValue($result,'bizContent.isSuccess','');
        if ($isSuccess) {
            //成功
            $res = $oPolicy->saveToSuccess($policyNo,$ePolicyUrl,$errorCode,$errorMsg);
            if (!$res) {
                Logger::dayLog('policy/cpolicyapply', 'saveToSuccess',  $oPolicy->errors,$result);
                $oPolicy->saveToDoing('-1','saveToSuccessError');
            }
            $this->addNotify($oPolicy);
            return $res;
            
        } else{
            //
            $res = $oPolicy->saveToFail($errorCode,$errorMsg);
            if(!$res){
                Logger::dayLog('policy/cpolicyapply', 'saveToFail',$oPolicy->errors,$result);
                $oPolicy->saveToDoing('-1','saveToFailError');
            }
            $this->addNotify($oPolicy);
            return $res;
        }
        
    }
    /**
     * 加入通知列表中
     */
    private function addNotify(ZhanPolicy $oPolicy) {
        if (in_array($oPolicy['remit_status'], [ZhanPolicy::STATUS_SUCCESS, ZhanPolicy::STATUS_FAILURE])) {
            $oClientNotify = new PolicyNotify();
            $postdata = $oPolicy->attributes;       
            $result = $oClientNotify->saveData($postdata);
            if (!$result) {
                Logger::dayLog('policynotify', 'addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
                return false;
            }
            $oNotify = new CPolicyNotify();
            $res = $oNotify->synchroNotify($oClientNotify);
        }
        return true;
    }


}
<?php
/**
 * 核保接口
 */
namespace app\modules\api\common\policy;
use app\common\Logger;
use app\models\policy\ZhanPolicy;
use yii\helpers\ArrayHelper;
use app\models\policy\PolicyNotify;
use app\modules\api\common\CPolicyNotify;
use app\modules\api\common\policy\CPolicyApi;


set_time_limit(0);

class CPolicy {
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
     * 处理单条核保
     * @param object $oPolicy
     * @return bool
     */
    public function doPolicy($oPolicy) {
        //请求参数处理
        $postdata = $this->getFormatData($oPolicy);
        //请求众安接口
        $result = $this->oApi->call($postdata);
        //处理返回结果
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
        $create_time = $oPolicy->create_time;
        $policyEndDate = date('Y-m-d',strtotime('+'.$policyDate.' day',strtotime($create_time)));
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
        $oPolicy->refresh();
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
            return false;
        }
        
    }
}
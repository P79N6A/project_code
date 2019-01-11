<?php
/**
 * 众安收银台支付接口
 */
namespace app\modules\api\common\policy;
use app\common\Logger;
use app\models\policy\ZhanPolicy;
use app\models\policy\PolicyPay;
use app\models\policy\PolicyNotify;
use app\modules\api\common\policy\CPolicyApi;
use app\modules\api\common\CPolicyNotify;
use yii\helpers\ArrayHelper;
use app\common\Crypt3Des;
use Yii;
set_time_limit(0);

class CPolicyPay {
    private $oPolicyPay;
    private $oApi;
    /**
     * 初始化接口
     */
    public function __construct() {
        $this->oPolicyPay = new PolicyPay;
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
    public function doPay($oPolicy,$oPolicyPay) {
        if(empty($oPolicy)) return false;
        $isLock=$oPolicy->lockOnePay();
        if(!$isLock){
            Logger::dayLog('policy/cpolicypay', 'lockOnePay', '锁失败', $oPolicy->id);
            return false;
        }
        $res = $oPolicyPay->saveToReqing();
        if(!$res){
            Logger::dayLog('policy/cpolicypay', 'saveData', '更新支付表失败', $oPolicyPay->id,$updata);
            return false;
        }
        $postdata = $this->getFormatData($oPolicyPay);
        $result = $this->oApi->pay($postdata);
        return $result;
    }
    private function getFormatData($oPolicyPay){
        $notify_info = [
            'client_id' => $oPolicyPay->client_id
        ];
        $postdata = [
            'out_trade_no'      =>$oPolicyPay->order_id,
            'amt'               =>$oPolicyPay->premium,
            'notify_info'       =>json_encode($notify_info)
        ];
        return $postdata;
    }
    /**
     * 加解密id
     * @param  int $id
     * @return str
     */
    public function encryptId($id) {
        return Crypt3Des::encrypt((string) $id, Yii::$app->params['trideskey']);
    }
    /**
     * 加入通知列表中
     */
    public function addNotify(PolicyPay $oPolicyPay) {
        if (in_array($oPolicyPay['pay_status'], [PolicyPay::STATUS_SUCCESS,PolicyPay::STATUS_OUTER,PolicyPay::STATUS_FAILURE])) {
            $oClientNotify = new PolicyNotify();
            $postdata = [
                'aid'           => $oPolicyPay->aid,
                'req_id'        => $oPolicyPay->req_id,
                'client_id'     => $oPolicyPay->client_id,
                'rsp_status'    => $oPolicyPay->pay_result,
                'remit_status'  => $oPolicyPay->pay_status,
                'callbackurl'   => $oPolicyPay->callbackurl,
                'pay_time'      => $oPolicyPay->pay_time,
            ]; 
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
    /**
     * Undocumented function
     * 处理30分钟未支付请求
     * @return void
     */
    public function runPayQuery(){
        $initRet = ['total' => 0, 'success' => 0];
        //1 获取数据
        $restNum = 100;
        $dataList = $this->oPolicyPay->getReqingData($restNum);
        //var_dump($dataList);die;
        if (!$dataList) {   
            return $initRet;
        }
        //4 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $key => $oPolicyPay) {
            $result = $this->doQuery($oPolicyPay);
            if ($result) {
                $success++;
            } else {
                Logger::dayLog('policy/cpolicypay', 'runPayQuery', '处理失败', $oPolicyPay->id);
            }
        }
        //5 返回结果
        return ['total' => $total, 'success' => $success];
    }
    private function doQuery($oPolicyPay){
        $oPolicyPay->saveToOuter();
        $res = $this->addNotify($oPolicyPay);
        return $res;
    }
}
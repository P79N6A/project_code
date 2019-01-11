<?php
/**
 * 智融 授信决策
 * 
 * 定时拉取st_strategy_request到st_request表中，同时调用java决策并存储决策结果
 * D:\software\amp\php\php.exe D:\workspace\strategy\yii strategy runStrategy
 * D:\phpstudy\php55\php.exe  D:\phpstudy\WWW\strategy_new\yii credit runCredit
 */
namespace app\commands;

use yii\helpers\ArrayHelper;
use Yii;

use app\common\Logger;
use app\models\Request;
use app\models\Result;
use app\models\StNotify;
use app\models\StAntiLoan;
use app\models\credit\CreditApi;
use app\models\StrategyRequest;
use app\modules\api\common\JavaCrif;
use app\modules\api\common\Analysis;
use app\commands\credit\logic\CreditLogic;

class CreditController extends BaseController
{
    protected $oStrategyReq;
    protected $operaAnls;
    protected $oJavaCrif;
    protected $from;
    protected $org_from;
    protected $aid;
    protected $oCreditApi;
    private $oCreditLogic;
    private $default_res;

    public function init() {
        parent::init();
        $this->oStrategyReq = new StrategyRequest();
        $this->operaAnls = new Analysis();
        $this->oJavaCrif = new JavaCrif();
        $this->oCreditApi = new CreditApi();
        $this->oCreditLogic = new CreditLogic();
        $this->from = Yii::$app->params['from']['STRATEGY_CREDIT'];
        $this->org_from = Yii::$app->params['from']['STRATEGY_CREDIT_ORIGIN'];
        $this->aid = Yii::$app->params['aid']['SOURCE_CREDIT'];
        $this->default_res = [
            'AMOUNT' =>  '0',
            'CARD_MONEY' =>  '0',
            'CRAD_RATE' =>  '0',
            'DAYS' =>  '0',
            'INTEREST_RATE' =>  '0',
            'RESULT' => Result::STATUS_REJECT, //驳回
            'ious_days' => '0',
            'ious_status' => '0',
            'result_tq' => '0',
            'result_model_tq' => '0',
        ];
    }

    public function runCredit() {
        // 1.获取st_strategy_request表数据
        $strateReqs = $this->oStrategyReq->getByStatus(StrategyRequest::OPERA_SUCCESS,$this->from,$this->aid);
        if(empty($strateReqs)) {
            Logger::dayLog('runcredit', 'run', 'there is nothing data to deal with');
            return false;
        }

        // 2.锁定状态
        $strateReqIds = ArrayHelper::getColumn($strateReqs, 'id');
        $lockRes = $this->oStrategyReq->lockStrateReq($strateReqIds, StrategyRequest::JAVA_DOING);
        if(!$lockRes) {
            Logger::dayLog('runcredit','lockStrateReq', $strateReqIds, 'strateReq锁定失败');
            return false;
        }

        // 3.导入到st_request表，并调用java决策
        foreach($strateReqs as $strateReq) {
            // try {
                // 1)存入request表并请求java决策记录结果
                $strateReqId = $strateReq['id'];
                $javaCrif = $this->strategy($strateReq,$strateReqId);
                $requestId = ArrayHelper::getValue($javaCrif,'request_id',0);
                
                // 2)插入通知表
                $oStNotify = new StNotify();
                $resNotify = $oStNotify->saveData($strateReqId, $this->from);
                if(!$resNotify) {
                    Logger::dayLog('runcredit', 'saveNotify', $requestId, '通知表插入失败:'.$oStNotify->errinfo);
                }

                // 3)回写成功的状态
                $resStrateReq = $this->oStrategyReq->updateStatus($strateReqId, StrategyRequest::JAVA_SUCCESS);
                if(!$resStrateReq) {
                    Logger::dayLog('runcredit', 'updStatus', $strateReqId, 'strateReq表更新失败');
                }
            // } catch (\Exception $e) {
            //     Logger::dayLog('runcredit/error','run',$strateReqId,'定时执行失败:'.$e->getMessage());
            // }
        }
    }

    private function strategy($strateReq,$strateReqId){
        // 1)存入st_request表
        $stReqInfo  = [
            'user_id' => $strateReq['user_id'],
            'from' => $this->from,
            'loan_id' => $strateReq['loan_id'],
            'prd_type' => $strateReq['aid'],
            'req_id' => $strateReqId,
        ];
        $oStRequest = new Request();
        $requestId = $oStRequest->addRequest($stReqInfo);
        if(!$requestId) {
            Logger::dayLog('runcredit/strategy', 'saveReq', $strateReqId, 'st_request表保存失败:'.$oStRequest->errinfo);
            $this->default_res['request_id'] = 0;
            // error_code
            $this->default_res['error_code'] = '10001';
            $save_res = $this->saveResult($strateReq, $this->default_res, $this->from);
            return $save_res;
        }
        $this->default_res['request_id'] = $requestId;
        $strateReq['request_id'] = $requestId;
        // 2)获取java请求参数
        $javaData = $this->javaCreditData($strateReq);
        if (empty($javaData)) {
            Logger::dayLog('runcredit/strategy','javaCreditData',$strateReqId,'请求参数异常');
            // error_code
            $this->default_res['error_code'] = '10002';
            $save_res = $this->saveResult($strateReq, $this->default_res, $this->from);
            return $save_res;
        } 
        // 3)存储java决策请求参数
        // $oStAntiLoan = new StAntiLoan();
        // $resAntiLoan = $oStAntiLoan->saveAnti($javaData);
        // if (empty($resAntiLoan)) {
            Logger::dayLog('runcredit/javaCreditData', 'javaCreditData', json_encode($javaData));
        //     return $res;
        // }
        // 4)调用java决策
        $javaCrif = $this->oJavaCrif->queryCrif($requestId, $javaData, JavaCrif::PRO_CODE_CREDIT);
        // var_dump($javaCrif);die;
        if (empty($javaCrif)) {
            Logger::dayLog('runcredit/strategy','javaCrif',$strateReqId,'授信决策异常');
            $this->default_res['error_code'] = '10003';
            $save_res = $this->saveResult($strateReq, $this->default_res, $this->from);
            return $save_res;
        }
        $javaCrif['request_id'] = $requestId;
        // 5)授信决策存入结果记录表中
        $save_res = $this->saveResult($strateReq, $javaCrif ,$this->from);
        if (!$save_res) {
            Logger::dayLog('runcredit/strategy','javaCrif',$strateReqId,'授信决策储存失败');
        }
        // 6) 请求天启决策
        $org_res = $this->javaOrigin($javaCrif, $strateReq, $javaData);
        return $javaCrif;
    }

    //获取java决策所需数据
    private function javaCreditData($data){
        $res = $this->oCreditApi->getCreditInfo($data);
        return $res;
    }
    // 天启决策
    private function javaOrigin($javaCrif, $strateReq, &$javaData){
        if (!SYSTEM_PROD) {
            # test data
            // $javaCrif['result_tq'] = '1';
            // $javaCrif['result_model_tq'] = '1';
        }
        $result_model_tq = ArrayHelper::getValue($javaCrif,'result_model_tq',0);
        $strateReqId = ArrayHelper::getValue($strateReq,'id',0);
        if ($result_model_tq == '1') {
            $process_code = JavaCrif::PRO_CODE_PROME_TQ;
            $origin_crif_res = $this->oCreditLogic->queryOriginCrif($javaData,$javaCrif,$process_code);
            if (empty($origin_crif_res)) {
                Logger::dayLog('runcredit/javaOrigin','javaOrigin',$strateReqId,'天启决策异常');
                $this->default_res['error_code'] = '10004';
                $save_res = $this->saveResult($strateReq, $this->default_res, $this->org_from);
                return $save_res;
            }
            // 记录天启结果
            $save_res = $this->saveResult($strateReq, $origin_crif_res, $this->org_from);
            if (!$save_res) {
                Logger::dayLog('runcredit/javaOrigin','javaOrigin',$strateReqId,'授信决策储存失败');
            }
            return $save_res;
        }
        return true;
    }
    // save result
    private function saveResult($strateReq, $javaCrif, $from){
        $oResult = new Result();
        $strateReq['from'] = $from;
        $save_res = $oResult->saveRes($strateReq, $javaCrif);
        if (!$save_res) {
            Logger::dayLog('runcredit/saveResult', 'saveResult', $oResult->errinfo, $strateReq);
        }
        return $save_res;
    }
}

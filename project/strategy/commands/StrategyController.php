<?php
/**
 * 定时拉取st_strategy_request到st_request表中，同时调用java决策并存储决策结果
 * D:\software\amp\php\php.exe D:\workspace\strategy\yii strategy runStrategy
 * D:\phpstudy\php55\php.exe  D:\phpstudy\WWW\peanut\strategy_tmp\yii strategy runStrategy
 */
namespace app\commands;

use yii\helpers\ArrayHelper;
use Yii;

use app\common\Logger;
use app\models\Request;
use app\models\Result;
use app\models\StNotify;
use app\models\StAntiLoan;
use app\models\StrategyRequest;
use app\models\yyy\YyyApi;
use app\modules\api\common\JavaCrif;
use app\modules\api\common\Analysis;

class StrategyController extends BaseController
{
    protected $oStrategyReq;
    protected $oYyyApi;
    protected $operaAnls;
    protected $oJavaCrif;
    protected $from;
    protected $aid;

    public function init() {
        parent::init();
        $this->oStrategyReq = new StrategyRequest();
        $this->oYyyApi = new YyyApi();
        $this->operaAnls = new Analysis();
        $this->oJavaCrif = new JavaCrif();
        $this->from = Yii::$app->params['from']['STRATEGY_ANTIFRAUD'];
        $this->aid = Yii::$app->params['aid']['SOURCE_YYY'];
    }

    public function runStrategy() {
        // 1.获取st_strategy_request表数据
        $strateReqs = $this->oStrategyReq->getByStatus(StrategyRequest::OPERA_SUCCESS,$this->from,$this->aid);
        if(empty($strateReqs)) {
            Logger::dayLog('runstrategy', 'run', 'there is nothing data to deal with');
            return false;
        }

        // 2.锁定状态
        $strateReqIds = ArrayHelper::getColumn($strateReqs, 'id');
        $lockRes = $this->oStrategyReq->lockStrateReq($strateReqIds, StrategyRequest::JAVA_DOING);
        if(!$lockRes) {
            Logger::dayLog('runstrategy','lockStrateReq', $strateReqIds, 'strateReq锁定失败');
            return false;
        }

        // 3.导入到st_request表，并调用java决策
        foreach($strateReqs as $strateReq) {
            try {
                // 1)存入request表并请求java决策
                $strateReqId = $strateReq['id'];
                $javaCrif = $this->strategy($strateReq,$strateReqId);

                // 2)存入决策结果记录表中
                $requestId = $javaCrif['request_id'];
                $oResult = new Result();
                $strateReq['from'] = $this->from;
                $strateReq['request_id'] = $requestId;
                $res = $oResult->saveRes($strateReq, $javaCrif);
                if (!$res) {
                    Logger::dayLog('runstrategy', 'saveResult', '结果记录失败:'.$oResult->errinfo, $strateReq);
                }

                // 3)插入通知表
                $oStNotify = new StNotify();
                $resNotify = $oStNotify->saveData($strateReqId, $this->from);
                if(!$resNotify) {
                    Logger::dayLog('runstrategy', 'saveNotify', $requestId, '通知表插入失败:'.$oStNotify->errinfo);
                }

                // 4)回写成功的状态
                $resStrateReq = $this->oStrategyReq->updateStatus($strateReqId, StrategyRequest::JAVA_SUCCESS);
                if(!$resStrateReq) {
                    Logger::dayLog('runstrategy', 'updStatus', $strateReqId, 'strateReq表更新失败');
                }
            } catch (\Exception $e) {
                Logger::dayLog('runstrategy','run',$strateReqId,'定时执行失败:'.$e);
            }
        }
    }

    private function strategy($strateReq,$strateReqId){
        // 1)存入st_request表
        $res = [
            'LOAN_RESULT' => Result::STATUS_REJECT //驳回
        ];
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
            Logger::dayLog('runstrategy', 'saveReq', $strateReqId, 'st_request表保存失败:'.$oStRequest->errinfo);
            $res['request_id'] = 0;
            return $res;
        }
        $res['request_id'] = $requestId;
        // 2)获取java请求参数
        $javaData = $this->javaStrateData($strateReq);

        // 3)存储java决策请求参数
        $javaData['request_id'] = $requestId;
        $oStAntiLoan = new StAntiLoan();
        $resAntiLoan = $oStAntiLoan->saveAnti($javaData);
        if (empty($resAntiLoan)) {
            Logger::dayLog('runstrategy', 'saveAntiLoan', $javaData, 'java决策参数存储失败:'.$oStAntiLoan->errinfo);
            return $res;
        }
        // 4)调用java决策
        $javaCrif = $this->oJavaCrif->queryCrif($requestId, $javaData, JavaCrif::PRO_CODE_FRAUD);
        if (empty($javaCrif)) {
            Logger::dayLog('runstrategy','javaCrif',$requestId,'决策异常');
            return $res;
        }
        unset($res['LOAN_RESULT']);
        $res = array_merge($javaCrif, $res);
        return $res;
    }

    //获取java决策所需数据
    private function javaStrateData($data){
        $res = [];
        // 1、获取借款用户类型（初贷/复贷）
        $userType = $this->oYyyApi->getUserType($data['user_id']);
        $res['type'] = $userType;
        // 2、获取用户借款信息
        $loanField = 'amount,days,create_time as loan_create_time,loan_id,source';
        $userLoan = $this->oYyyApi->getLoanData($data, $loanField);
        // 3、获取用户基本信息
        $userInfo = $this->oYyyApi->getUserInfo($data);
        // 4、获取运营商数据
        $operaInfo = $this->operaAnls->getAntiInfo($data);
        $res = array_merge($res, $userLoan, $userInfo, $operaInfo, $data);
        return $res;
    }
}

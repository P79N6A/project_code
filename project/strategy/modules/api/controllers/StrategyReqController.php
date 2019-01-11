<?php
namespace app\modules\api\controllers;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\StrategyRequest;

class StrategyReqController extends ApiController
{   
    protected $oStrategy;
    
    public function init() {
        parent::init();
        $this->oStrategy = new StrategyRequest();
    }

    public function actionRequest() {
        $postData = $this->postdata;
        if (!is_array($postData) || empty($postData)) {
            Logger::dayLog(
                'strategyReq', 'postdata','请求数据异常',$postData
            );
            return $this->resp(100001, '请求数据异常');
        }
        //业务参数
        $aid          = ArrayHelper::getValue($postData,'aid',0);
        $req_id       = ArrayHelper::getValue($postData,'req_id','');
        $user_id      = ArrayHelper::getValue($postData,'user_id',0);
        $loan_id      = ArrayHelper::getValue($postData,'loan_id',0);
        $callbackurl  = ArrayHelper::getValue($postData,'callbackurl','');
        
        //决策来源
        $postData['come_from'] = Yii::$app->params['from']['STRATEGY_ANTIFRAUD'];

        if (empty($req_id) || empty($aid) || empty($user_id) || empty($loan_id) || empty($callbackurl) ){
            return $this->resp(100002, "参数信息不完整");
        }

        $strategyReqID = $this->oStrategy->saveRequest($postData);
        if (!$strategyReqID) {
            Logger::dayLog(
                'strategyReq', 'saveData', 'strategyReq数据保存失败', $this->oStrategy->errinfo
            );
            return $this->resp(100003, $this->oStrategy->errinfo);
        }
        $res = [
            'aid' => $aid,
            'user_id' => $user_id,
            'loan_id' => $loan_id,
            'strategy_req_id' => $strategyReqID,
            'status' => StrategyRequest::INIT_STATUS,
        ];
        return $this->resp(0, $res);
    }

    public function actionCredit() {
        $postData = $this->postdata;
        // $postData = [
        //     'aid'       => '10',
        //     'req_id'    => mt_rand(1000000,9999999),
        //     'user_id'   => '6132748',
        //     'callbackurl' => 'http://182.92.80.211:8122/sfapi/cloud/setblack',
        // ];
        if (!is_array($postData) || empty($postData)) {
            Logger::dayLog(
                'strategyReq', 'postdata','请求数据异常',$postData
            );
            return $this->resp(100001, '请求数据异常');
        }
        //业务参数
        $aid          = ArrayHelper::getValue($postData,'aid',0);
        $req_id       = ArrayHelper::getValue($postData,'req_id','');
        $user_id      = ArrayHelper::getValue($postData,'user_id',0);
        $callbackurl  = ArrayHelper::getValue($postData,'callbackurl','');

        //决策来源
        $postData['come_from'] = Yii::$app->params['from']['STRATEGY_CREDIT'];

        if (empty($req_id) || empty($aid) || empty($user_id) || empty($callbackurl) ){
            return $this->resp(100002, "参数信息不完整");
        }

        $strategyReqID = $this->oStrategy->saveRequest($postData);
        if (!$strategyReqID) {
            Logger::dayLog(
                'strategyReq', 'saveData', 'strategyReq数据保存失败', $this->oStrategy->errinfo
            );
            return $this->resp(100003, $this->oStrategy->errinfo);
        }
        $res = [
            'aid' => $aid,
            'user_id' => $user_id,
            'strategy_req_id' => $strategyReqID,
            'status' => StrategyRequest::INIT_STATUS,
        ];
        return $this->resp(0, $res);
    }

    public function actionYyyCredit() {
        $postData = $this->postdata;
        // $postData = [
        //     'aid'       => '1',
        //     'req_id'    => mt_rand(1000000,9999999),
        //     'user_id'   => '2599989',
        //     'callbackurl' => 'http://182.92.80.211:8122/sfapi/cloud/setblack',
        // ];
        if (!is_array($postData) || empty($postData)) {
            Logger::dayLog(
                'strategyReq', 'postdata','请求数据异常',$postData
            );
            return $this->resp(100001, '请求数据异常');
        }
        //业务参数
        $aid          = ArrayHelper::getValue($postData,'aid',0);
        $req_id       = ArrayHelper::getValue($postData,'req_id','');
        $user_id      = ArrayHelper::getValue($postData,'user_id',0);
        $callbackurl  = ArrayHelper::getValue($postData,'callbackurl','');

        $come_from = Yii::$app->params['from']['STRATEGY_ALLIN'];
        //决策来源
        $postData['come_from'] = ArrayHelper::getValue($postData,'come_from',$come_from);

        if (empty($req_id) || empty($aid) || empty($user_id) || empty($callbackurl) ){
            return $this->resp(100002, "参数信息不完整");
        }

        $strategyReqID = $this->oStrategy->saveRequest($postData);
        if (!$strategyReqID) {
            Logger::dayLog(
                'strategyReq', 'saveData', 'strategyReq数据保存失败', $this->oStrategy->errinfo
            );
            return $this->resp(100003, $this->oStrategy->errinfo);
        }
        $res = [
            'aid' => $aid,
            'user_id' => $user_id,
            'strategy_req_id' => $strategyReqID,
            'status' => StrategyRequest::INIT_STATUS,
        ];
        return $this->resp(0, $res);
    }
}

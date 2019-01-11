<?php

/**
 * 7-14黑名单设置
 */
namespace app\modules\sfapi\controllers;

use app\common\Logger;
use app\modules\sfapi\logic\CloudLogic;
use app\modules\sfapi\logic\ReportLogic;
use app\modules\sfapi\common\BaseApi;
use Yii;
use yii\helpers\ArrayHelper;
class CloudController extends ApiController
{

    // public function init()
    // {

    // }
    // 拉黑接口
    public function actionSetblack()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('cloud_error', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常',$postdata,3);
        }
        Logger::dayLog('setblack', '拉黑数据', $postdata);
        $cloudLogic = new CloudLogic();
        $res = $cloudLogic->setBlack($postdata);
        if (!$res) {
            Logger::dayLog('cloud_error', 'result','拉黑失败',$cloudLogic->info);
            return $this->error('20011', '拉黑失败',$postdata,3);
        }
        return $this->success($postdata, $cloudLogic->info, 0);
    }

    // 取消黑名单接口
    public function actionOutblack()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('cloud_error', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常',$postdata,3);
        }
        Logger::dayLog('setblack', '拉黑数据', $postdata);
        $cloudLogic = new CloudLogic();
        $res = $cloudLogic->unsetBlack($postdata);
        if (!$res) {
            Logger::dayLog('cloud_error', 'result','拉黑失败',$cloudLogic->info);
            return $this->error('20011', '拉黑失败',$postdata,3);
        }
        return $this->success($postdata, $cloudLogic->info, 0);
    }

    //天启接口
    public function actionOrigin()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('origin_error', 'postdata', '数据异常', $postdata);
            return $this->err('20001', '数据异常',$postdata,3);
        }

        if (!isset($postdata['user_id'])) {
             Logger::dayLog('origin_error', 'postdata', '数据异常', $postdata);
            return $this->err('20002', '数据异常',$postdata,3);
        }
        $postdata['identity_id'] = $postdata['user_id'];
        $cloudLogic = new CloudLogic();
        $res = $cloudLogic->getOrigin($postdata);
        $org_res = $cloudLogic->info;
        if (!$res) {
            Logger::dayLog('origin_error', 'result',$org_res);
            return $this->err('20003', $org_res,$postdata,3);
        }
        return $this->suc($postdata, '', $org_res);
    }

    //天行学历决策接口
    public function actionTxskedu()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('txskedu_error', 'postdata', '数据异常', $postdata);
            return $this->err('20001', '数据异常',$postdata,3);
        }

        if (!isset($postdata['user_id'])) {
             Logger::dayLog('txskedu_error', 'postdata', '数据异常', $postdata);
            return $this->err('20002', '数据异常',$postdata,3);
        }
        $postdata['identity_id'] = $postdata['user_id'];
        $cloudLogic = new CloudLogic();
        $res = $cloudLogic->getTxskEdu($postdata);
        $edu_res = $cloudLogic->info;
        if (!$res) {
            Logger::dayLog('txskedu_error', 'result',$edu_res);
            return $this->err('20003', $edu_res,$postdata,3);
        }
        return $this->suc($postdata, '', $edu_res);
    }
    
    /**
     * [action 运营商决策入口]
     * @return [type] [description]
     */
    public function actionReport()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('txskedu_error', 'postdata', '数据异常', $postdata);
            return $this->operatorReturn('20001', '数据异常',$postdata);
        }

        if (empty($postdata['mobile'])) {
            Logger::dayLog('txskedu_error', 'postdata', '手机号不能为空', $postdata);
            return $this->operatorReturn('20002', '手机号不能为空',$postdata);
        }
        $oReportLogic = new ReportLogic();
        $res = $oReportLogic->mobileCredit($postdata);
        $report_res = $oReportLogic->info;
        if (!$res) {
            Logger::dayLog('txskedu_error', 'result',$report_res);
            return $this->operatorReturn('20003', $report_res,$postdata);
        }
        return $this->operatorReturn('0000','success', $report_res);
    }
}
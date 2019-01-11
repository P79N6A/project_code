<?php

/**
 * 分期决策
 */
namespace app\modules\sfapi\controllers;

use Yii;
use app\common\Logger;
use app\modules\sfapi\logic\PeriodLogic;
use app\modules\sfapi\common\JavaCrif;
use app\common\ApiSign;

class PeriodsController extends ApiController
{
    
    private $test_data;
    // public function init()
    // {
    //     $this->test_data = ['user_id'=>'6132748'];
    // }

    //prome模型
    public function actionAntiperiod()
    {
        $postdata = $this->postdata;
        // $postdata = $this->test_data;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', $postdata);
            return $this->periodsReturn('20001', '数据异常', $postdata);
        }
        //获取数据并发送请求
        $loan_logic = new PeriodLogic($postdata);
        $res = $loan_logic->loanPeriods($postdata);
        $res_data = $loan_logic->info;
        if (!$res) {
            return $this->periodsReturn('20002', $res_data, $postdata);
        }
        return $this->periodsReturn('0000','success', $res_data);
    }
}
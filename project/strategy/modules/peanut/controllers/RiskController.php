<?php

/**
 * 7-14黑名单设置
 */
namespace app\modules\peanut\controllers;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;
use app\modules\peanut\logic\RiskLogic;
class RiskController extends ApiController
{
    private $test_data;
    public function init() {
        parent::init();
        // $this->test_data = [
        //     'bank_create_time' => '2018-06-29 18:25:00',
        //     'invest_num' => '3',
        //     'mobile' => '13120117991',
        //     'bank_mobile' => '13120117991',// 13120117991    15595417640
        //     'query_time' => date('Y-m-d H:i:s'),
        //     'reg_create_time' => '2018-01-29 18:25:00',
        //     'st_source' => '3',
        //     'user_id' => '41196',
        //     'order_id' => '1234567',
        //     'ip_address' => '127.0.0.1',
        //     'refer_url' => 'www.baidu.com',
        //     'token_id' => 'f123818273bc544aa9490fae1cf763ba',
        //     'state' => '0',
        //     'user_name' => '程振远',
        // ];
    }

    // 米富 提现决策
    public function actionWithdraw() {
        $postdata = $this->postdata;
        // $postdata = $this->test_data;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('withdraw_error', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常',$postdata,3);
        }

        if (empty($postdata['user_id']) || empty($postdata['st_source']) || empty($postdata['mobile']) || empty($postdata['bank_mobile'])) {
             Logger::dayLog('withdraw_error', 'postdata', '数据异常', $postdata);
            return $this->error('20002', '数据异常',$postdata,3);
        }

        $oRiskLogic = new RiskLogic();
        $res = $oRiskLogic->Withdraw($postdata);
        $draw_res = $oRiskLogic->info;
        if (!$res) {
            Logger::dayLog('withdraw_error', 'result',$draw_res);
            return $this->error('20003', $draw_res,$postdata,3);
        }
        return $this->success($postdata, $draw_res);
    }
    
}
<?php

/**
 * 注册请求
 */
namespace app\modules\promeapi\controllers;

use Yii;
use app\common\Logger;
use app\modules\promeapi\logic\PromeLogic;
use app\modules\promeapi\common\JavaCrif;

class PromeController extends ApiController
{
    
    // private $test_data;
    // public function init()
    // {
    //     $this->test_data = ['user_id'=>'6132686','loan_id' => '18554249','aid' => 1,'query_time'=>date('Y-m-d H:i:s')];
    // }

    //prome模型
    public function actionPromevf()
    {
        $postdata = $this->postdata;
        // $postdata = $this->test_data;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常', $postdata, 3);
        }
        //获取数据
        $prome_logic = new PromeLogic();
        $res = $prome_logic->promeData($postdata);
        $res_data = $prome_logic->info;
        if (!$res) {
            return $this->error('20002', $res_data, $postdata, 3);
        }
        //请求反欺诈决策系统
        $process_code = JavaCrif::PROME_CODE;
        $res = $prome_logic->queryCrif($process_code,$res_data);
        if (!$res) {
            return $this->error('20003', $prome_logic->info, $postdata, 3);
        }
        $crif_res = $prome_logic->info;
        return $this->success($postdata, '', $crif_res);
    }
}
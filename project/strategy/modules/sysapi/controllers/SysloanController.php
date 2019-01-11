<?php


namespace app\modules\sysapi\controllers;

use app\common\Logger;
use app\models\yyy\UserLoan;
use Yii;
use yii\helpers\ArrayHelper;
use app\modules\sysapi\logic\SysloanLogic;
use app\modules\sysapi\common\BaseApi;
use app\models\Request;
class SysloanController extends ApiController
{
//     private $test_data;
//     public function init()
//     {
//             $this->test_data = [
//                 'aid'=>1,
//                 'user_id'=> '2599989',
//                 'loan_id'=> '19947140',
//                 'amount' => 1500,
//                 'query_time' => date('Y-m-d H:i:s'),
//             ];
//     }

    //催收模型
    public function actionCollect()
    {
        $postdata = $this->postdata;
        //$postdata = $this->test_data;
        Logger::dayLog('collect_event', 'postdata', $postdata);
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata','数据异常',$postdata);
            return $this->error('20001', '数据异常',$postdata);
        }
        $postdata['from'] = Request::SYSLOAN;//催收模型

        //获取数据并发送请求
        $SysLogic = new SysloanLogic();
        $res = $SysLogic->Collect($postdata);
        $res_info  = $SysLogic->info;
        if (!$res) {  
            return $this->error('20002', $res_info,$postdata);
        }
        return $this->suc($res_info,$postdata);
    }

    //逾前模型
    public function actionOverbefore()
    {
        $postdata = $this->postdata;
        //$postdata = $this->test_data;
        Logger::dayLog('over_event', 'postdata', $postdata);
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata','数据异常',$postdata);
            return $this->error('20001', '数据异常',$postdata);
        }
        $postdata['from'] = Request::OVERBEFORE;//逾前模型

        //获取数据并发送请求
        $SysLogic = new SysloanLogic();
        $res = $SysLogic->Overbefore($postdata);
        $res_info  = $SysLogic->info;
        if (!$res) {  
            return $this->error('20002', $res_info,$postdata);
        }
        return $this->success($res_info,$postdata);
    }

    
}
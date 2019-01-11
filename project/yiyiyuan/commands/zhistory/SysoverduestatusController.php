<?php

namespace app\commands\sysloan;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\OverdueLoan;
use Yii;

/**
 * 每天凌晨1点执行
 * 同步贷后逾期状态（处理逾前推送状态为9定时为逾期12）
 * C:\wamp64\bin\php\php7.0.0\php.exe C:\wamp64\www\yiyiyuan\yii sysloan/sysoverdueloan index
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SysoverduestatusController extends BaseController {

    // 命令行入口文件
    public function actionIndex() {
        $time          = time();
        $startTime     = date('Y-m-d 00:00:00', $time);
        $endTime       = date('Y-m-d 00:00:00', $time + 86400);
        $business_type = [1, 4, 5, 6];
        //获取所有逾期数量
        $count         = (new OverdueLoan)->getOverdueNum($startTime, $endTime, $business_type);
        $limit         = 1000;
        $forcount      = ceil($count / $limit);
        for ($i = 1; $i <= $forcount; $i++) {
            $offset = ($i - 1) * $limit;
            //获取逾期账单信息
            $res    = (new OverdueLoan)->getOverdueInfo($startTime, $endTime, $offset, $limit, $business_type);
            foreach ($res as $key => $val) {
                $data            = [];
                $data['loan_id'] = $this ->getPrefixByDays($val) . $val['loan_id'];
                $data['status']  = $val['loan_status'];
                $data['sign']    = $this->encrySign($data);
//                调用贷后接口 
                $url             = Yii::$app->params['daihou_api_url'] . "/api/loan/overduestatus";
                $result          = (new Curl())->post($url, $data);
                $resultArr       = json_decode($result, true);
                if ($resultArr['rsp_code'] != '0000') {
                    Logger::dayLog('sysloan', '同步逾期账单', $data, $result);
                }
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}

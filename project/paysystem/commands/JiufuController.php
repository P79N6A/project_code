<?php
/**
 * 出款计划任务
 * windows d:\xampp\php\php.exe D:\www\open\yii jiufu queryOrder
 */
// 公司内部充值
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii jiufu queryOrder 1>/dev/null 2>&1

namespace app\commands;
use app\common\Logger;
use app\modules\api\common\jiufu\CJFNotify;
use app\modules\api\common\jiufu\CJFRemit;
use app\modules\api\common\jiufu\JFApi;
use Yii;

/**
 * 玖富
 */
class JiufuController extends BaseController {
    private $env;
    public function init(){
        $this->env = SYSTEM_PROD ? 'prod' : 'dev';
    }
    /**
     * 查询
     * 每五分钟执行一次
     */
    public function runRemits() {
        $oM = new CJFRemit($this->env);
        $data = $oM->runRemits();
        Logger::dayLog('command', '9f', 'runRemits', $data);
        return json_encode($data);
    }
    /**
     * 查询
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oM = new CJFRemit($this->env);
        $data = $oM->runQuerys();
        Logger::dayLog('command', '9f','runQuerys', $data);
        return json_encode($data);
    }
    /**
     * 支付结果查询
     * 每五分钟执行一次
     */
    public function runPayQuerys() {
        $oM = new CJFRemit($this->env);
        $data = $oM->runPayQuerys();
        Logger::dayLog('command', '9f','runPayQuerys', $data);
        return json_encode($data);
    }
    /**
     * 通知
     * 每五分钟执行一次
     * @param $start_time 默认1小时内
     * @param $end_time 默认当前分钟
     */
    public function runNotify($start_time = null, $end_time = null) {
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00');
        }
        if (!$start_time) {
            // 默认1小时内
            $start_time = date('Y-m-d H:i:00', $time - 3600);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));

        $oM = new CJFNotify;
        $data = $oM->runMinute($start_time, $end_time);
        Logger::dayLog('command', '9f', $data);
        return json_encode($data);
    }
    /**
     * 查询单个订单状态
     */
    public function queryOrder($appId = null) {
        if (!$appId) {
            echo "please set [appId]\n";
            return false;
        }
        $oLoan = new JFApi($this->env);
        $res = $oLoan->query($appId);
        print_r($res);
        return true;
    }
    /**
     * 结束工单
     * @param  str $orderids 11,23311,222
     * @return bool
     */
    public function endloans($oids){
        $oids = explode(',',$oids);
        $orderids = [];
        foreach ($oids as $id) {
            $id = trim($id);
            $id = intval($id);
            if($id > 0){
                $orderids[] = $id;
            }
        }
        if(empty($orderids)){
            echo "orderids:{$orderids}不合法";exit;
        }
        $oM = new CJFRemit($this->env);
        $data = $oM->endloads($orderids);
        print_r($res);
        return true;
    }
}

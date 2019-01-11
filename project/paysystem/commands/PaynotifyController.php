<?php
namespace app\commands;
use app\common\Logger;
use app\models\yeepay\YpTztOrder;
use app\models\Payorder;
use app\modules\api\controllers\actions\YeepayquickAction;

/**
 * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii paynotify quickorder  "2016-05-24 06:57:48" "2016-05-24 06:58:48"
 * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii paynotify quicknotify  "2016-05-24 06:57:48" "2016-05-24 06:58:48"
 */
class PaynotifyController extends BaseController {

    /**
     * 查询订单,默认查询前30-20分钟10分钟
     */
    public function quickorder($start_date = null, $end_date = null) {
        //1 默认前两分钟 - 前一分钟
        $start_time = $start_date ? strtotime($start_date) : time() - 1800;
        $end_time = $end_date ? strtotime($end_date) : $start_time + 600;

        $start_time = strtotime(date('Y-m-d H:i:00', $start_time));
        $end_time = strtotime(date('Y-m-d H:i:00', $end_time));

        //2 查询初始状态数据
        $where = [
            'AND',
            ['>=', 'create_time', $start_time],
            ['<', 'create_time', $end_time],
            ['pay_status' => YpQuickOrder::STATUS_INIT],
        ];
        $rows = YpQuickOrder::find()->where($where)->orderBy("id ASC")->limit(1000)->all();
        Logger::dayLog('paynotify', 'quickorder', '条数', count($rows));
        if (!$rows) {
            return false;
        }

        foreach ($rows as $order) {
            $result = $this->getQuickOrder($order->orderid, $order->aid);
            Logger::dayLog('paynotify', 'quickorder', $order->orderid, $result);
        }

        //3 调用通知接口
        $this->quicknotify($start_date, $end_date);
    }
    /**
     * 获取订单
     * @param  string $orderid 订单号
     * @param  int $aid     应用id
     * @return []  结果
     */
    private function getQuickOrder($orderid, $aid) {
        $oAction = new YeepayquickAction('getorder', $this);
        $oAction->reqData = ['orderid' => $orderid];
        $oAction->appData = ['id' => $aid];
        $oAction->reqType = 'return';

        $result = $oAction->runWithParams([]);
        return $result;
    }

    /**
     * 一健支付通知
     */
    public function quicknotify($start_date = null, $end_date = null) {
        //1 默认前两分钟 - 前一分钟
        $start_time = $start_date ? strtotime($start_date) : time() - 1800;
        $end_time = $end_date ? strtotime($end_date) : $start_time + 600;

        $start_time = strtotime(date('Y-m-d H:i:00', $start_time));
        $end_time = strtotime(date('Y-m-d H:i:00', $end_time));

        //2 查询通知的数据
        $where = [
            'AND',
            ['>=', 'create_time', $start_time],
            ['<', 'create_time', $end_time],
            ['client_status' => 0],
            ['!=', 'pay_status', YpQuickOrder::STATUS_INIT],
        ];
        $rows = YpQuickOrder::find()->where($where)->orderBy("id ASC")->limit(1000)->all();
        Logger::dayLog('paynotify', 'quicknotify', '条数', count($rows));
        if (!$rows) {
            return false;
        }

        foreach ($rows as $order) {
            $result = $order->clientNotify();
            Logger::dayLog('paynotify', 'quicknotify', $order->orderid, $result);
        }

        return $result;
    }
    /**
     * payorder 表按id发送通知
     * @param  int $id 
     * @return str
     */
    public function clientNotify($id){
    	$id = intval($id);
    	if(!$id){
    		echo "$id must be >0";
    		return false;
    	}
    	$oPayorder = Payorder::findOne($id);
    	$result = $oPayorder -> clientNotify();
    	$res = $result ?  'success' : 'error' ;
    	echo $res . "\n";
    	return true;
    }
}
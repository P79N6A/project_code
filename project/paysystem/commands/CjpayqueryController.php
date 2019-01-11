<?php
/**
 * 查询支付订单状态
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/16
 * Time: 16:52
 * D:\phpStudy\php\php-5.6.27-nts\php.exe d:\www\paysystem\yii repayquery runQuerys
 * D:\phpStudy\php\php-5.6.27-nts\php.exe d:\www\paysystem\yii repayquery runQuerys
 */
namespace app\commands;
use app\common\Common;
use app\common\Logger;
use app\models\repayment\PayAlipayOrder;
use app\modules\api\common\repayment\Cjpayment;
use Yii;
use yii\helpers\ArrayHelper;

class CjpayqueryController extends BaseController
{

    /**
     * 支付--补单
     * 每五分钟执行一次
     */

    public function runQuerys($start_time = null, $end_time = null){
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00', $time - 1800);//生产
        }
        if (!$start_time) {
            $start_time = date("Y-m-d", strtotime("-7 days"));
        }
        $oRepayment = new Cjpayment();
        $data = $oRepayment->runMinute($start_time, $end_time);
        Logger::dayLog('command', 'cjzfbQuery', $data);
        return json_encode($data);
    }


}
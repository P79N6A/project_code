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
use app\modules\api\common\repayment\Repayment;
use Yii;
use yii\helpers\ArrayHelper;

class RepayqueryController extends BaseController
{
    private $env;
    public function init(){
        $this->env = SYSTEM_PROD ? 'prod' : 'dev';
    }

    /**
     * 支付--补单
     * 每五分钟执行一次
     */
    public function runQuerys()
    {

        //查找数据
        $oPayAlipayOrder = new PayAlipayOrder();
        $start_time = date("Y-m-d", strtotime("-7 days"));
        //$time = time();
        //$end_time =  date("Y-m-d H:i:s", ($time-30*60));
        $end_time =  date("Y-m-d H:i:s", strtotime("-1 days"));//生产
        //$end_time =  date("Y-m-d H:i:s", time());//测试
        $data_set = $oPayAlipayOrder->getOrderData($start_time, $end_time, 200);
        if (!empty($data_set)){
            $ids = Common::ArrayToString($data_set, 'id');
            $ids_data = $oPayAlipayOrder -> lockStatus($ids);
            Logger::dayLog("repay/runquery", "id", $ids);
            if ($ids_data == 0){
                return false;
            }
            foreach($data_set as $value) {
                $oRepayment = new Repayment(ArrayHelper::getValue($value, 'channel_id', 139));
                $oRepayment->queryOrder(ArrayHelper::getValue($value, 'cli_orderid', 0));
            }
        }
    }


}
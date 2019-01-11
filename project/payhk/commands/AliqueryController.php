<?php
/**
 * 益倍嘉
 * 支付宝查询支付订单状态
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
use app\modules\api\common\repayali\Repayali;
use Yii;
use yii\helpers\ArrayHelper;

class AliqueryController extends BaseController
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
        $end_time =  date("Y-m-d H:i:s", strtotime("-1 days"));
        //$end_time =  date("Y-m-d H:i:s");
        $data_set = $oPayAlipayOrder->getOrderAliData($start_time, $end_time, 200);
        if (!empty($data_set)){
            $ids = Common::ArrayToString($data_set, 'id');
            $ids_data = $oPayAlipayOrder -> lockStatus($ids);
            Logger::dayLog("repaywx/runquery", "id", $ids);
            if ($ids_data == 0){
                return false;
            }
            foreach($data_set as $value) {
                $oRepayment = new Repayali(ArrayHelper::getValue($value, 'channel_id'));
                $oRepayment->queryOrder(ArrayHelper::getValue($value, 'cli_orderid', 0));
            }
        }
    }

    /* 下文为最新益倍嘉商户号，
        开发支付方式：支付宝还款和微信公众号还款
        商户编号：101118102 （天津有信而立）
        商户密钥：bfc7897bb6e44814acb86497a8c79c6e
        尊敬的商户101118102：
     * @return bool
     */
    public function runQueryO()
    {
        return false;
        //查找数据
        $oPayAlipayOrder = new PayAlipayOrder();
        $start_time = date("Y-m-d", strtotime("-7 days"));
        //$time = time();
        //$end_time =  date("Y-m-d H:i:s", ($time-30*60));
        $end_time =  date("Y-m-d H:i:s", strtotime("-10 minute"));
        //$end_time =  date("Y-m-d H:i:s");
        $data_set = $oPayAlipayOrder->getOrderXyAliData($start_time, $end_time, 200);
        if (!empty($data_set)){
            $ids = Common::ArrayToString($data_set, 'id');
            $ids_data = $oPayAlipayOrder -> lockStatus($ids);
            Logger::dayLog("repaywx/runquery", "id", $ids);
            if ($ids_data == 0){
                return false;
            }
            foreach($data_set as $value) {
                $oRepayment = new Repayali(153);
                $oRepayment->queryOrder(ArrayHelper::getValue($value, 'cli_orderid', 0));
            }
        }
    }


}
<?php
/**
 * 易宝API投资通服务
 * @author lijin
 */
namespace app\modules\api\common\yeepay;
use app\models\Payorder;

/**
 * 易宝投资通代扣业务
 */
class CYeepaydirect {

    public function init() {
        parent::init();
    }
    /**
     * 创建支付订单
     * @param  obj $oPayorder
     * @return  [res_code,res_data]
     */
    public function createOrder($oPayorder) {
        $oCYeepaytzt = new CYeepaytzt;
        return $oCYeepaytzt->directpay($oPayorder);
    }
}

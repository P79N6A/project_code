<?php
namespace app\modules\api\common\baofoo;
use app\common\Logger;
use app\models\Payorder;
use Yii;

/**
 * 宝付支付回调结果处理
 */
class CBack {

    /**
     * 支付结果异步回调:连连仅支付成功才回调
     * @param  [] $dataArr
     * @return [res_code, res_data]
     */
    public function backpay($oBfOrder,$dataArr) {
        $resp_code = $dataArr['resp_code'];
        if('0000' == $resp_code){
            $result = $oBfOrder->savePaySuccess($dataArr['trans_no']);
        }else{
            $result = $oBfOrder->savePayFail($resp_code, $dataArr['resp_msg']);
            Logger::dayLog('bfauth', 'bfauth/savePayFail',$dataArr['resp_msg']);
        }
        if (!$result) {
            return false;
        }
        return true;
    }
    /**
     * POST 异步通知客户端
     * @param  object $oBfOrder
     * @return bool
     */
    public function clientNotify($oBfOrder) {
        if (!$oBfOrder) {
            return false;
        }
        $oPayorder = (new Payorder)->getByOrder($oBfOrder->orderid, $oBfOrder->aid);
        if (!$oPayorder) {
            return false;
        }
        $result = $oPayorder->clientNotify();
        return $result;
    }
}
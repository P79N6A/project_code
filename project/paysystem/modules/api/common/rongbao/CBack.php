<?php
namespace app\modules\api\common\rongbao;
use app\common\Logger;
use app\models\Payorder;
use Yii;

/**
 * 融宝代扣回调结果处理
 */
class CBack {

    public function backpay($oRbOrder,$dataArr) {
        $status = $dataArr['sts'];
        if('1' == $status){
            $result = $oRbOrder->savePaySuccess('');
        }elseif('2'== $status){
            $result = $oRbOrder->savePayFail($dataArr['result_code'], $dataArr['result_msg']);
            Logger::dayLog('CRbwithhold', 'CRbwithhold/savePayFail',$dataArr['resp_msg']);
        }
        if (!$result) {
            return false;
        }
        return true;
    }
    /**
     * POST 异步通知客户端
     * @param  object $oRbOrder
     * @return bool
     */
    public function clientNotify($oRbOrder) {
        if (!$oRbOrder) {
            return false;
        }
        $oPayorder = (new Payorder)->getByOrder($oRbOrder->orderid, $oRbOrder->aid);
        if (!$oPayorder) {
            return false;
        }
        $result = $oPayorder->clientNotify();
        return $result;
    }
}
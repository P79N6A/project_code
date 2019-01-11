<?php
/**
 *  宝付快捷支付路由地址
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\App;
use app\models\Payorder;
use app\models\cg\CgOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\cg\CCgnew;
use app\common\ApiSign;
use Yii;
use yii\helpers\ArrayHelper;

    
class CgbackController extends ApiController {

    public function init() {

    }
    public function beforeAction($action) {
        if (in_array($action->id, ['backpay'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * 存管支付异步通知接口
     */
    public function actionBackpay() {
        Logger::dayLog('cg/back', '异步回调数据',$this->post());

        $data = $this->post('data');
        $sign = $this->post('_sign');
        if (empty($data) || empty($sign)) {
            return $this->returnError(false, '参数不合法');
        }
        $apiSign = new ApiSign();
        $isVerify = $apiSign->verifyData($data, $sign);
        
        if (!$isVerify) {
            return $this->returnError(false, '验签失败');
        }
        
        $dataArr = json_decode($data,true);

        $orderid = ArrayHelper::getValue($dataArr,'request_no');//商户订单号
        $res_status = ArrayHelper::getValue($dataArr,'res_status',0);
        $amount = ArrayHelper::getValue($dataArr,'amount');
        $error_code = ArrayHelper::getValue($dataArr,'error_code','');
        $error_msg = ArrayHelper::getValue($dataArr,'error_msg','');

        /*$resCode = ArrayHelper::getValue($dataArr,'res_code','');
        $resData = ArrayHelper::getValue($dataArr,'res_data','');
        $payRes = json_decode($resData,true);

        $retCode = ArrayHelper::getValue($payRes,'retCode');
        $retMsg = ArrayHelper::getValue($payRes,'retMsg');
        $txAmount = ArrayHelper::getValue($payRes,'txAmount');
        $acqRes = ArrayHelper::getValue($payRes,'acqRes');//商户订单号*/
        

        Logger::dayLog('cg/back', "res_status", $res_status, 'orderid',$orderid,'amount',$amount,'data',$dataArr);

        $oCgorder = new CgOrder();
        $orderInfo = $oCgorder->getOrderInfo($orderid);

        if (!$orderInfo) {
            return $this->returnError(false, '未找到该订单');
        }

        if($amount <= 0 && $amount != ($orderInfo->amount/100)){
            Logger::dayLog('cg/back', "金额不一致", $amount, ($orderInfo->amount/100),'订单号',$orderid);
            return $this->returnError(false, '交易金额有误');
        }

        if($res_status == '6'){//支付成功
            $result = $orderInfo->savePaySuccess($orderid);
        }

        if($res_status == '11'){//支付失败
            $result = $orderInfo->savePayFail($error_code,$error_msg);
        }
        
        if($result){
            echo 'SUCCESS';
            $oPayorder = (new Payorder)->getByOrder($orderInfo->orderid, $orderInfo->aid);
            if (!$oPayorder) {
                return false;
            }
            $result = $oPayorder->clientNotify($orderInfo);
            if (!$result) {
                Logger::dayLog('cg/back','异步回调通知客户端失败',$orderInfo->orderid);
            }
        }else{
            echo 'FAIL';
        }
        
    }

    
    public function actionGetback() {
        $getData = $this->get();
        $id = ArrayHelper::getValue($getData,'id','');
        $aid = ArrayHelper::getValue($getData,'aid',1);
        $orderid = substr(base64_decode($id),1,-1);
        $oPay = new Payorder();
        $resData = $oPay->getByOrder($orderid,$aid);
        $url = $resData->clientBackurl();
        Logger::dayLog("cg/back","orderid", $orderid, "aid", $aid);
        return $this->redirect($url);
    }
}

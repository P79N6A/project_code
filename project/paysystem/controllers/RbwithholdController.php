<?php
/**
 * @desc 融宝代扣
 * @author lubaba
 */
namespace app\controllers;

use app\common\Logger;
use app\modules\api\common\rongbao\RbWithholdApi;
use app\models\rongbao\RbWithholdOrder;
use app\modules\api\common\rongbao\CBack;
use Yii;

class RbwithholdController extends BaseController {

    private $merchantMap = [
        '100000001301654'=> '121',
        '100000001301640'=> '122',
    ];
    
    public function beforeAction($action) {
        if (in_array($action->id, ['backpay'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * 支付异步通知接口
     */
    public function actionBackpay() {
        $isPost = Yii::$app->request->isPost;
        $merchantId = $this->post('merchant_id');
        $data        = $this->post('data');
        $encryptkey  = $this->post('encryptkey');
        Logger::dayLog('RbWithhold/Backpay',$merchantId , $data , $encryptkey);
        //1. 纪录日志并获取参数
        if(!in_array($merchantId,array_keys($this->merchantMap))){
            Logger::dayLog('RbWithhold/Backpay', '请求非法',$merchantId);
            exit;
        }
        $channelId = $this->merchantMap[$merchantId];
       
        $cfg = "prod".$channelId;
        $rbObj = new RbWithholdApi($cfg);
        
        $encryptkey = $rbObj->RSADecryptkey($encryptkey);
        $decryData = $rbObj->AESDecryptResponse($encryptkey, $data);
        $jsonObject = json_decode($decryData,true);
        if(!$jsonObject){
            Logger::dayLog('RbWithhold/Backpay', '解密数据为空',$encryptkey,$decryData);
            exit;
        }
        $paramarr    = [];
        $sign        = $jsonObject['sign'];
        foreach ($jsonObject as $k => $v) {
            if ($k == 'sign' || $k == 'sign_type') {
                continue;
            }
            $paramarr[$k] = $v;
        }
        $mysign = $rbObj->createSign($paramarr);
        if ($mysign != $sign){
            Logger::dayLog('RbWithhold/Backpay', '验签失败',$mysign,$sign);
            exit;
        }
        $order_no = $jsonObject['order_no'];
        if (!$order_no) {
            Logger::dayLog('RbWithhold/Backpay', '订单号不能为空',$jsonObject);
            exit;
        }
        $Model = new RbWithholdOrder;
        $oRbOrder = $Model->getByCliOrderId($order_no);
        if (!$oRbOrder) {
            Logger::dayLog('RbWithhold/Backpay', '未找到该订单',$order_no);
            exit;
        }
        $is_finished = $oRbOrder->is_finished();
        if($is_finished){
            echo 'SUCCESS';
            exit;
        }
        //3 保存状态
        $oCBack = new CBack;
        $result = $oCBack->backpay($oRbOrder,$jsonObject);
        //4 输出结果
        if (!$result) {
            Logger::dayLog('rbwithhold', 'rbwithhold/backpay','异步回调保存状态失败');
            exit;
        }
        echo 'SUCCESS';
        //5 异步通知客户端
        $result = $oCBack->clientNotify($oRbOrder);
        if (!$result) {
            Logger::dayLog('rbwithhold', 'rbwithhold/clientNotify','异步回调通知客户端失败');
            exit;
        }
    }

}

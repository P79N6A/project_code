<?php
/**
 * @desc 宝付协议支付H5页面控制器
 * @author 孙瑞
 */

namespace app\controllers;
use Yii;
use app\common\Crypt3Des;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\StdError;
use app\models\baofoo\BfXYOrder;
use app\models\Payorder;
use app\modules\api\common\ApiController;
use app\modules\api\common\baofoo\CBfXY;

class BfxyController extends ApiController {
    public function init() {}

    // 显示宝付支付链接地址
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return StdError::returnStdErrorJson("163","0001");
        }
        //3  获取是否存在该订单
        $baofooDetail = $this->getBaofooOrder($xhhorderid);
        if (ArrayHelper::getValue($baofooDetail, 'res_code')) {
            return json_encode($baofooDetail,JSON_UNESCAPED_UNICODE);
        }
        $oBaofoo = ArrayHelper::getValue($baofooDetail, 'res_data');
        //4 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $oBaofoo->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/bfxy/getsmscode",
        ]);
    }

    // 发送签约验证码
    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        if (!isset($xhhorderid) || !$xhhorderid) {
            return StdError::returnStdErrorJson("163","0001");
        }
        //2  获取是否存在该订单
        $baofooDetail = $this->getBaofooOrder($xhhorderid);
        if (ArrayHelper::getValue($baofooDetail, 'res_code')) {
            return json_encode($baofooDetail,JSON_UNESCAPED_UNICODE);
        }
        $oBaofoo = ArrayHelper::getValue($baofooDetail, 'res_data');
        //3 发送短信
        $oCBfXY = new CBfXY();
        $sendRes = $oCBfXY->sendSignSms($oBaofoo->payorder_id,$oBaofoo->channel_id,$oBaofoo->bind_id);
        if ($sendRes['res_code']) {
            return json_encode($sendRes,JSON_UNESCAPED_UNICODE);
        }
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/bfxy/paycomfirm',
        ]);
    }

    // 验证签约验证码并支付
    public function actionPaycomfirm() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $validatecode = $this->post('validatecode');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        if (!isset($xhhorderid) || !$xhhorderid) {
            return StdError::returnStdErrorJson("163","0001");
        }
        if (!$validatecode) {
            return StdError::returnStdErrorJson("163","0007");
        }
        //2 获取是否存在该订单
        $baofooDetail = $this->getBaofooOrder($xhhorderid);
        if (ArrayHelper::getValue($baofooDetail, 'res_code')) {
            return json_encode($baofooDetail,JSON_UNESCAPED_UNICODE);
        }
        $oBaofoo = ArrayHelper::getValue($baofooDetail, 'res_data');
        //3 校验短信验证码
        $oCBfXY = new CBfXY();
        $checkRes = $oCBfXY->checkSignSms($oBaofoo->payorder_id,$oBaofoo->channel_id,$validatecode,$oBaofoo->bind_id);
        if ($checkRes['res_code']) {
            return json_encode($checkRes,JSON_UNESCAPED_UNICODE);
        }
        //4 调用支付接口并执行通知操作
        $payRes = $oCBfXY->pay($oBaofoo->payorder_id,$oBaofoo->channel_id,$oBaofoo->bind_id);
        if ($payRes['res_code']) {
            return json_encode($payRes,JSON_UNESCAPED_UNICODE);
        }
        return $this->showMessage(0, ['callbackurl' => $payRes['res_data']]);
    }

    // 协议支付统一回调地址
    public function actionBackpay($channelId = '') {
        if(!isset($channelId) || !$channelId){
            Logger::dayLog('bfxy', 'backpay_Fail: 宝付回调参数未指定通道ID');
            die('Again');
        }
        //1.获取参数
        $dataContent = file_get_contents("php://input", 'r');
        Logger::dayLog('bfxy', 'backpay_Log: 宝付回调的信息为:'. $dataContent);
        //2 解析数据保存入库并通知业务端
        $resData = (new CBfXY())->checkPayRes($dataContent,$channelId);
        if($resData['res_code']){
            Logger::dayLog('bfxy', 'backpay_Log: 宝付的回调数据解析失败'. $resData['res_data']);
            die('Again');
        }
        Logger::dayLog('bfxy', 'backpay_Success: 订单回调支付成功');
        die('OK');
    }

    /**
     * 根据订单id获取对应宝付子订单信
     * @param int $id 子订单的订单id
     * @return array res_code=>返回码 res_data=>返回信息
     */
    private function getBaofooOrder($id) {
        if (empty($id)) {
            return StdError::returnStdError("163","0002");
        }
        $baofooModel = new BfXYOrder();
        $baofooDetail = $baofooModel->getOne($id);
        if (!$baofooDetail) {
            return StdError::returnStdError("163","0003");
        }
        if (in_array($baofooDetail->status,[Payorder::STATUS_PAYOK,Payorder::STATUS_PAYFAIL])) {
            return StdError::returnStdError("163","0004");
        }
        if ($baofooDetail->status != Payorder::STATUS_INIT) {
            return StdError::returnStdError("163","0017");
        }
        if (empty($baofooDetail->payorder)) {
            return StdError::returnStdError("163","0006");
        }
        return ['res_code' => 0,'res_data' => $baofooDetail];
    }

    /**
     * 显示结果信息
     * @param $res_code 错误码0 正确  | >0错误
     * @param $res_data      结果   | 错误原因
     */
    protected function showMessage($res_code, $res_data, $type = 'json', $redirect = null) {
        switch ($type) {
        case 'json':
            return json_encode([
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        default:
            return $this->render('/pay/showmessage', [
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        }
    }
}

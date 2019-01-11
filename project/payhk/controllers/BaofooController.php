<?php
/**
 *  宝付代扣
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\App;
use app\models\Payorder;
use app\models\baofoo\baofooOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\baofoo\CBaofooQuick;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\StdError;

class BaofooController extends ApiController {

    public function init() {

    }
    /**
     * 显示宝付请求绑卡链接地址
     * xhhorderid
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return StdError::returnStdErrorJson("106","0001");
            //return $this->showMessage(10200, "订单不合法或信息不完整", '');
        }
        //3  获取是否存在该订单
        $baofooDetail = $this->getBaofooOrder($xhhorderid);
        if (!$baofooDetail) {
            return StdError::returnStdErrorJson("106","0003");
            //return $this->showMessage(10201, $this->errinfo, '');
        }
        //4 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $baofooDetail->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/baofoo/getsmscode",
        ]);
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
    /**
     * @des 根据id找到对应宝付订单信息
     * @param int $id
     * @return obj 
     */
    private function getBaofooOrder($id) {
        if (empty($id)) {
            return StdError::returnStdErrorJson("106","0002");
            //return $this->returnError(null, "订单号不存在");
        }
        $baofooModel = new BaofooOrder();
        $baofooDetail = $baofooModel->getByBaofooId($id);
        if (!$baofooDetail) {
            return StdError::returnStdErrorJson("106","0003");
            //return $this->returnError(null, "未找到订单信息");
        }
        if (in_array($baofooDetail->status,[Payorder::STATUS_PAYOK,Payorder::STATUS_PAYFAIL])) {
            return StdError::returnStdErrorJson("106","0004");
            //return $this->returnError(null, "此订单已处理，不必重复提交");
        }
        return $baofooDetail;
    }
    /**
     * 判断支付
     * 
     */
    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);

        if (!isset($xhhorderid) || !$xhhorderid) {
            return StdError::returnStdErrorJson("106","0001");
            //return $this->showMessage(10200, "订单不合法或信息不完整");
        }
        //2 获取是否存在该订单
        $baofooDetail = $this->getBaofooOrder($xhhorderid);
        if (!$baofooDetail) {
            return StdError::returnStdErrorJson("106","0005");
            //return $this->showMessage(10201, $this->errinfo);
        }
        return $this->requestSms($baofooDetail->payorder);
    }


    /**
     * 发送短信程序
     */
    private function requestSms($oPayorder) {
        //发送短信
        $result = $oPayorder->requestSms();
        if (!$result) {
            //return StdError::returnStdErrorJson("106","0010");
            return $this->showMessage(10602, $oPayorder->errinfo);
        }
        //返回结果
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/baofoo/paycomfirm',
        ]);
    }
    /**
     * 输入验证码确定并支付
     * @param xhhorderid
     * @param validatecode
     *
     * 成功回调客户端
     */
    public function actionPaycomfirm() {
        //1  参数验证
        $xhhorderid = $this->post('xhhorderid');
        $validatecode = $this->post('validatecode');
        $xhhorderid = Crypt3Des::decrypt($xhhorderid, Yii::$app->params['trideskey']);
        if (empty($xhhorderid)) {
            return StdError::returnStdErrorJson("106","0002");
            //return $this->showMessage(10203, "$xhhorderid未找到");
        }
        if (empty($validatecode)) {
            return StdError::returnStdErrorJson("106","0007");
            //return $this->showMessage(10204, "smscode未找到");
        }
        //2  获取是否存在该订单
        $baofooDetail = $this->getBaofooOrder($xhhorderid);
        if (!$baofooDetail) {
            return StdError::returnStdErrorJson("106","0003");
            //return $this->showMessage(10205, $this->errinfo);
        }
        //4  获取主订单, 短信验证码检测
        $oPayorder = $baofooDetail->payorder;
        if (!$oPayorder) {
            return StdError::returnStdErrorJson("106","0006");
            //return $this->showMessage(10206, "主订单异常,请联系相关人员");
        }
        if ($validatecode != $oPayorder->smscode) {
            return StdError::returnStdErrorJson("106","0008");
            //return $this->showMessage(10207, "验证码错误");
        }
        //5 调用支付接口
        $baofooQuick = new CBaofooQuick();
        $resBf = $baofooQuick->pay($baofooDetail);
        if ($oPayorder->status == Payorder::STATUS_PAYOK) {
            return $this->showMessage(0, '操作成功');
        } else {
            return StdError::returnStdErrorJson("106","0009");
            //return $this->showMessage(10208, "订单处理失败");
        }
    }
}

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
use app\modules\api\common\cg\CCg;
use Yii;
use yii\helpers\ArrayHelper;

    
class CgController extends ApiController {

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
     * 显示存管请求支付链接地址
     * xhhorderid
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        //2  解析数据
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(10200, "订单不合法或信息不完整", '');
        }
        //3  获取是否存在该订单
        $cgInfo = $this->getCgOrder($xhhorderid);

        if (!is_object($cgInfo)) {
            return $this->showMessage(10201, $this->errinfo, '');
        }
        
        //5  获取主订单校验
        $oPayorder = $cgInfo->payorder;
        if (!$oPayorder) {
            return $this->showMessage(140204, "主订单异常,请联系相关人员");
        }

        //7 输出
        $this->layout = false;
        return $this->render('/pay/payurl', [
            'oPayorder' => $cgInfo->payorder,
            'xhhorderid' => $cryid,
            'smsurl' => "/cg/getsmscode",
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
    private function getCgOrder($id) {
        if (empty($id)) {
            return $this->showMessage(140207, "订单号不存在");
        }
        $cgModel = new CgOrder();
        $cgInfo = $cgModel->getByCgId($id);
        if (!is_object($cgInfo)) {
            return $this->showMessage(140208, "未找到订单信息");
        }
        if (in_array($cgInfo->status,[Payorder::STATUS_PAYOK,Payorder::STATUS_PAYFAIL])) {
            return $this->showMessage(140209, "此订单已处理，不必重复提交");
        }
        return $cgInfo;
    }


    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid');
        $xhhorderid = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
        if (!isset($xhhorderid) || !$xhhorderid) {
            return $this->showMessage(140210, "信息不完整");
        }
        //2 获取是否存在该订单
        $cgInfo = $this->getCgOrder($xhhorderid);
        if (!is_object($cgInfo)) {
            return $this->showMessage(140211, $this->errinfo);
        }

        /*if (!in_array($cgInfo->status,[Payorder::STATUS_PREDO,Payorder::STATUS_BIND])) {
            return $this->showMessage(140212, "此订单状态错误!无法完成操作");
        }*/
        return $this->requestSms($cgInfo);
    }
 


    /**
     * 发送短信程序
     */
    private function requestSms($cgInfo) {
        //发送短信
        $oCg = new CCg();
        $result = $oCg->cgSendSms($cgInfo->payorder);
        if (empty($result)) {//包括超时情况
            return $this->showMessage(1470003, '短信发送异常 请重试');
        }
        
        $res_code = ArrayHelper::getValue($result,'res_code','');//商户平台订单号
        $retCode  = ArrayHelper::getValue($result,'res_data.retCode','');//COde码
        $smsSeq  = ArrayHelper::getValue($result,'res_data.smsSeq','');//短信序号
        $cgInfo->smsseq = $smsSeq;
        $cgInfo->save();
        if($res_code > 0 || $retCode != '00000000'){
            return $this->showMessage(1470004, ArrayHelper::getValue($result,'res_data','短信发送失败 请重试'));
        }
        
        //返回结果
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/cg/paycomfirm',
        ]);
    }

    /**
     * 输入验证码确定并支付
     * @param xhhorderid
     * @param validatecode
     *
     */
    public function actionPaycomfirm() {
        //1  参数验证
        $xhhorderid = $this->post('xhhorderid');
        $validatecode = $this->post('validatecode');
        $xhhorderid = Crypt3Des::decrypt($xhhorderid, Yii::$app->params['trideskey']);
        if (empty($xhhorderid)) {
            return $this->showMessage(140210, "$xhhorderid未找到");
        }
        if (empty($validatecode)) {
            return $this->showMessage(140211, "验证码未找到");
        }
        //2  获取是否存在该订单
        $cgInfo = $this->getCgOrder($xhhorderid);
        if (!is_object($cgInfo)) {
            return $this->showMessage(140212, $this->errinfo);
        }
        //3 检测是不是未处理状态
        // if ($cgInfo->status != Payorder::STATUS_PREDO) {
        //     return $this->showMessage(140213, " 订单状态异常,请联系相关人员");
        // }
        //4  获取主订单
        $oPayorder = $cgInfo->payorder;
        if (!$oPayorder) {
            return $this->showMessage(140214, "主订单异常,请联系相关人员");
        }

        //5 调用支付接口
        $oCgpay = new CCg;
        $status = $oCgpay->confirmPay($cgInfo,$validatecode);

        //6. 只有支付成功, 支付失败状态有效
        if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])) {
            //return $this->showMessage(0, '操作成功');
            //7 异步通知客户端
            $result = $oPayorder->clientNotify();

            $url = $oPayorder->clientBackurl();
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        }elseif($status == Payorder::STATUS_DOING){
            $url = $oPayorder->clientBackurl();
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        }else {
            return $this->showMessage(140215, "订单处理失败");
        }
    }

    

}

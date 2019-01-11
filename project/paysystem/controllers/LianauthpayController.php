<?php
/**
 * @desc 连连认证支付
 * @author lubaba
 */
namespace app\controllers;
use app\common\Logger;
use app\models\lian\LianauthBindbank;
use app\models\lian\LianauthOrder;
use app\modules\api\common\lianlian\CBack;
use app\modules\api\common\lianlian\CAuthlian;
use Yii;

class LianauthpayController extends BaseController {

    public $layout = false;
    private $oCLian; 

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $env = SYSTEM_PROD ? 'prod' : 'dev';
        $this->oCLian = new CAuthlian($env);
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['backpay','returnurl'])) {
            // 局部关闭csrf验证
            $action->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * 支付链接地址
     * @return html
     */
    public function actionPayurl() {
        //1 验证参数是否正确
        $cryid = $this->get('xhhorderid', '');
        $lian_id = (new LianauthOrder)->decryptId($cryid);
        if (!$lian_id) {
            return $this->showMessage(140101, "订单不合法或信息不完整", '');
        }
        //2  获取是否存在该订单
        $oLianOrder = (new LianauthOrder)->getByLianId($lian_id);
        if (!$oLianOrder) {
            return $this->showMessage(140102, '此订单不存在');
        }
        //3. 组合数据
        $res = $this->oCLian->authpay($oLianOrder);
        if ($res['res_code'] != 0) {
            return $this->showMessage($res['res_code'], $res['res_data']);
        }
        echo $res['res_data']['html_code'];
        exit;
    }

    /**
     * 支付异步通知接口
     */
    public function actionBackpay() {
        //1. 纪录日志并获取参数
        $data_json = file_get_contents("php://input");
        Logger::dayLog('lian', 'lianauthpay/backpay', $data_json);

        // 本地测试
        if (!$data_json && defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            $data_json = $this->testBackpay();
        }
        //3 解析数据; 保存状态. 通知结果
        $oCBack = new CBack;
        $oCBack->oCLian = $this->oCLian;
        $result = $oCBack->backauthpay($data_json);
        //4 输出结果`
        if (!$result) {
            Logger::dayLog('lian', 'lianauthpay/backauthpay', $oCBack->errinfo);
            return $this->showMessage(140411, '支付失败');
        }
        //5 异步通知客户端
        $result = $oCBack->clientNotify($oCBack->oLianOrder);
        if (!$result) {
            return $this->showMessage(140412, '支付失败');
        }

        //6 异步回调成功返回状态码
        return json_encode([
            'ret_code' => '0000',
            'ret_msg' => '交易成功',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 支付返回页面
     */
    public function actionReturnurl(){
        $cryid = $this->get('xhhorderid', '');
        $lian_id = (new LianauthOrder)->decryptId($cryid);
        Logger::dayLog('lian', 'lianauthpay/returnurl', $lian_id);
        if (!$lian_id) {
            return $this->showMessage(1080101, "订单不合法或信息不完整", '');
        }
        //2  获取是否存在该订单
        $oLianOrder = (new LianauthOrder)->getByLianId($lian_id);
        if (!$oLianOrder) {
            return $this->showMessage(1080102, '此订单不存在');
        }
        $url = $oLianOrder->payorder->clientBackurl();
        if ($url) {
            header("Location:{$url}");
        }else{
            return $this->showMessage(1080103, '返回错误');
        }
    }

    /**
     * 测试桩
     */
    private function testBackpay() {
        return '{
            "bank_code":"03010000",
            "dt_order":"20170502113649",
            "info_order":"购买电子产品",
            "money_order":"0.01",
            "no_order":"108_R20170410113357383ererer",
            "oid_partner":"201704171001649504",
            "oid_paybill":"2017050286149548",
            "pay_type":"D",
            "result_pay":"SUCCESS",
            "settle_date":"20170502",
            "sign":"f53a53362f34685525ac666e977110aa",
            "sign_type":"MD5"
         }';      
    }
}

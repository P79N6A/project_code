<?php

/**
 *支付宝 微信支付路由 
 */

namespace app\modules\api\controllers;
use app\common\Logger;
use app\models\Rule;
use app\models\BankStandard;
use app\models\Payorder;
use app\modules\api\common\ApiController;
use app\modules\api\common\repayali\Repayali;
use app\modules\api\common\repayment\Repayment;
use app\modules\api\common\repayment\Cjpayment;
use app\modules\api\common\repaywx\Repaywx;
use yii\helpers\ArrayHelper;

class PaymentController extends ApiController {
    /**
     * 服务id号
     */
    protected $server_id = 100;

    /**
     * 路由首页
     */
    public function actionPay() {

        //1  参数验证
        $postData = $this->reqData;
        Logger::dayLog("repay/parama", "接收参数：", json_encode($postData));
        if (empty($postData['bankcode']) && empty($postData['bankname'])) {
            return $this->resp('10101', "银行卡编码或名称不能同时为空");
        }
        if (!isset($postData['card_type']) || empty($postData['card_type'])) {
            return $this->resp('10102', "银行卡类型不能为空");
        }
        //获取 应用id
        $postData['aid'] = $this->appData['id'];
        // 因为生产环境和测试共用一个帐号. 现将非生产唯一identityid加个前缀
        $identityid = (string) $postData['identityid'];
        if (!SYSTEM_PROD) {
            $postData['identityid'] = "T" . $identityid;
        }
        //2 路由到通道
        $res = (new Rule)->getRoute($postData);
        if ($res['res_code'] != '0000') {
            return $this->resp($res['res_code'], $res['res_data']);
        }
        $support = $res['res_data'];
        $support->std_bankname = $postData['bankname'];
        //3 保存订单
        $oPayorder = new Payorder;
        $result = $oPayorder->saveOrder($postData, $support);
        if (!$result) {
            return $this->resp('10103', $oPayorder->errinfo);
        }
        //4 路由订单
        $res = $this->route($oPayorder, $postData);
        return $this->resp($res['res_code'], $res['res_data']);
    }
    /**
     * 路由
     * @param  obj $oPayorder
     * @param  [] $postData
     * @return [res_code, res_data]
     */
    private function route($oPayorder, &$postData) {

        switch ($oPayorder['channel_id']) {

        // 上海易旨支付宝一麻袋
        case 170:
            $oPay = new Repayment($oPayorder['channel_id']);
            $res = $oPay->createOrder($oPayorder, ArrayHelper::getValue($postData, 'source', 0));//写入次表
            break;

        default:
            $res = ['res_code' => '10105', 'res_data' => "不支持此通道"];
            break;
        }

        return $res;
    }
}

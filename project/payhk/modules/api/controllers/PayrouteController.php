<?php

namespace app\modules\api\controllers;
use app\common\Logger;
use app\models\Rule;
use app\models\BankStandard;
use app\models\Payorder;
use app\modules\api\common\ApiController;
use app\modules\api\common\lianlian\CLian;
use app\modules\api\common\yeepay\CYeepaytzt;
use app\modules\api\common\yeepay\CYeepayquick;
use app\modules\api\commom\yeepay\CYeepaydirect;
use app\modules\api\common\rongbao\CRongbao;
use app\modules\api\common\baofoo\BaofooClient;
use app\modules\api\common\baofoo\CBaofooAuth;
use app\modules\api\common\baofoo\CBfXY;
use app\modules\api\common\baofoo\CBfXY181;
use app\modules\api\common\rongbao\CRbwithhold;
use app\modules\api\common\lianlian\CAuthlian;
use app\modules\api\common\cjt\CCjt;
use app\modules\api\common\cjquick\CCjquick;
use app\modules\api\common\cjxy\CCjxy;
use app\modules\api\common\cg\CCg;
use app\modules\api\common\cg\CCgnew;
use app\modules\api\common\jd\CJdquick;
use app\modules\api\common\rongbaoxy\CRbxy;
use app\modules\api\common\ymdxy\CYmdxy;

class PayrouteController extends ApiController {
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
        Logger::dayLog('payorder/createorder', $postData);
        if (empty($postData['bankcode']) && empty($postData['bankname'])) {
            return $this->resp('10101', "银行卡编码或名称不能同时为空");
        }
        if (!isset($postData['card_type']) || empty($postData['card_type'])) {
            return $this->resp('10102', "银行卡类型不能为空");
        }
        //获取 应用id
        $postData['aid'] = $this->appData['id'];
        //var_dump($postData);die;
        // 因为生产环境和测试共用一个帐号. 现将非生产唯一identityid加个前缀
        $identityid = (string) $postData['identityid'];
        if (!SYSTEM_PROD) {
            $postData['identityid'] = "T" . $identityid;
        }
        // 获取银行的标准名称
        $postData['bankname'] = (new BankStandard)->getStdBankName($postData['bankname']);
        //2 路由到银行卡
        $res = (new Rule)->getBankRoute($postData);
        if ($res['res_code'] != '0000') {
            return $this->resp($res['res_code'], $res['res_data']);
        }
        $supportBank = $res['res_data'];
        //3 保存订单
        $oPayorder = new Payorder;
        $result = $oPayorder->saveOrder($postData, $supportBank);
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
        //$oPayorder['channel_id'] = 106;
        switch ($oPayorder['channel_id']) {

        //宝付协议支付
        case 171:
            $oPay = new CBfXY;
            $res = $oPay->createOrder($oPayorder);
            break;

		//畅捷协议
        case 177:
            $oPay = new CCjxy;
            $res = $oPay->createOrder($oPayorder);
            break;

        //融宝协议支付
        case 184:
            $oPay = new CRbxy;
            $res = $oPay->createOrder($oPayorder,$postData);
            break;
        default:
            $res = ['res_code' => '10105', 'res_data' => "不支持此银行卡"];
            break;
        }
 
        return $res;
    }
}

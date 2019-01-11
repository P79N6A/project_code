<?php
/**
 * 易宝API投资通服务
 * 内部错误码范围2700-2800
 * @author lijin
 */
namespace app\modules\api\controllers;
use app\common\Crypt3Des;
use app\common\Logger;
use app\models\BankStandard;
use app\models\BankSupport;
use app\models\BindBank;
use app\models\Payorder;
use app\models\YpBindbank;
use app\modules\api\common\ApiController;
use app\modules\api\common\yeepay\YeepayTzt;
use app\modules\api\common\lianlian\CLian;
use Yii;

class PayrouteController extends ApiController {
    /**
     * 服务id号
     */
    protected $server_id = 100;
    /**
     * 获取银行标准名称
     */
    private $bankStandModel;

    /**
     * 支付路由
     */
    private $bankSupportModel;

    /**
     * 订单类
     */
    private $payOrderModel;

    public function init() {
        parent::init();
        $this->bankStandModel = new BankStandard();
        $this->bankSupportModel = new BankSupport();
        $this->payOrderModel = new Payorder();
    }
    /**
     * 路由首页
     */
    public function actionPay() {
        //1  参数验证
        $env = SYSTEM_PROD ? 'prod' : 'dev';
        $postData = $this->reqData;
        if (empty($postData['bankcode']) && empty($postData['bankname'])) {
            return $this->resp(2701, "银行卡编码或名称不能同时为空");
        }
        if (!isset($postData['card_type']) || empty($postData['card_type'])) {
            return $this->resp(2702, "银行卡类型不能为空");
        }

        // 因为生产环境和测试共用一个帐号. 现将非生产唯一identityid加个前缀
        $identityid = (string) $postData['identityid'];
        if (!SYSTEM_PROD) {
            $postData['identityid'] = "T" . $identityid;
        }

        //获取银行的标准名称
        $postData['bankname'] = $this->bankStandModel->getStdBankName($postData['bankname']);

        //2 路由选择
        //获取 应用id
        $postData['aid'] = $this->appData['id'];
        //获取应用ID支持的通道
        $payroute = Yii::$app->params['payroute'][$postData['aid']];
        $route = $this->bankSupportModel->getNewPayRoute($postData['bankname'], $postData['card_type'], $payroute);
        if (empty($route)) {
            return $this->resp(2703, "不支持此银行卡");
        }

        $postData['pay_type'] = $route->pay_type;
        $payOrderModel = $this->payOrderModel->saveOrder($postData);
        if (!$payOrderModel) {
            Logger::dayLog(
                'yeepayquick/error',
                'actionPayrequest',
                'Payorder 数据保存失败',
                '提交数据', $postData,
                '错误原因', $this->payOrderModel->errinfo
            );
            return $this->resp(2704, $this->payOrderModel->errinfo);
        }

        //4  切换处理方式
        switch ( $postData['pay_type'] ) {
        //1  投资通
        case 101:
            $result = $this->yeepaytzt($payOrderModel);
            break;

        //2  一键支付
        case 102:
            $result = $this->yeepayquick($payOrderModel);
            break;

        //3  畅捷支付
        case 103:
            $result = $this->chanpayquick($payOrderModel, $postData['card_type'], $route['std_bankname'], $route['bankcode'], $postData['validate'], $postData['cvv2'], $postData['userip']);
            break;

        //4 连连支付
        case 104:
            $oCLian = new CLian($env);
            $res = $oCLian->createPayOrder($payOrderModel, $postData['bankname']);
            $result = $this->resp($res['res_code'], $res['res_data']);
            break;
        default:
            $result = $this->resp(2704, "不支持此银行卡");
            break;
        }
        /*
        [
        'url' => $this->createBindPayUrl($payOrderModel->id),
        'pay_type' => Payorder::PAY_TZT, // 投资通
        'status'   => Payorder::STATUS_NOBIND,// 未绑卡
        'orderid'  => $payOrderModel->id,
        ]
         */
        return $result;
    }

    /**
     * 101 投资通
     */
    private function yeepaytzt($payOrderModel) {
        //1  是否绑卡
        $isBind = \app\models\YpBindbank::model()->chkSameUserCard(
            $payOrderModel->aid,
            $payOrderModel->identityid,
            $payOrderModel->cardno
        );

        //2  是否绑卡
        $payOrderModel->status = $isBind ? Payorder::STATUS_BIND : Payorder::STATUS_NOBIND;
        $payOrderModel->save();
        return $this->resp(0, [
            'url' => $this->createBindPayUrl($payOrderModel->id),
            'pay_type' => Payorder::PAY_TZT, // 投资通
            'status' => $payOrderModel->status,
            'orderid' => $payOrderModel->orderid,
        ]);

        exit;

        // 后面的不需要了

        //2  直接支付操作： 组合支付需要的数据
        /*$card_top = substr($payOrderModel->cardno,0,6);
        $card_last= substr($payOrderModel->cardno,-4);
        $reqData = [
        'orderid'         =>$payOrderModel->orderid, //     商户订单号√string商户生成的唯一订单号，最长50位
        'transtime'        =>time(), //   交易时间√int时间戳，例如：1361324896，精确到秒
        'amount'        =>$payOrderModel->amount, //  交易金额√int以"分"为单位的整型
        'productname'    =>$payOrderModel->productname, //     商品名称√string最长50位
        'productdesc'    =>$payOrderModel->productdesc, //     商品描述string最长200位
        'identityid'    =>$payOrderModel->identityid, //      用户标识√string最长50位，商户生成的用户唯一标识
        'card_top'        =>$card_top,  //    卡号前6位√string
        'card_last'        =>$card_last, //   卡号后4位√string
        'orderexpdate'    =>$payOrderModel->orderexpdate, //    订单有效期int单位：分钟，例如：60，表示订单有效期为60分钟
        'callbackurl'    =>$payOrderModel->callbackurl, //     回调地址√string用来通知商户支付结果
        'userip'        =>$payOrderModel->userip, //  用户请求ip√string用户支付时使用的网络终端IP
        ];

        //9 调用支付接口
        $action = Yii::createObject([
        'class' => '\app\modules\api\controllers\actions\YeepaytztAction',
        'reqData' => $reqData,
        'appData' => $this->appData,
        'reqType' => 'return',
        ],
        ['directbindpay', $this]);

        $result = $action->runWithParams([]);
        // @todo
        /*
        $ybResult = [
        'orderid' => $postData['orderid'],
        'yborderid' => "123432142134",
        'amount' => $postData['amount'],
        ];
         */

        /*if( $result['res_code'] ){
        return $this->resp($result['res_code'],$result['res_data']);
        }
        $res_data  = $result['res_data'];
        return $this->resp(0,$res_data);

        //10 查询接口 下面没有用途。因为每次都是进行
        /*$res_data  = $ybResult['res_data'];
        $orderid   = $res_data['orderid'];
        $yborderid = $res_data['yborderid'];
        $amount    = $res_data['amount'];*/

        /*$action = Yii::createObject([
    'class' => '\app\modules\api\controllers\actions\YeepaytztAction',
    'reqData' => ['orderid'=>$payOrderModel->orderid],
    'appData' => $this->appData,
    'reqType' => 'return',
    ],
    ['queryorder', $this]);

    $result = $action->runWithParams([]);
    if($result['res_code']){
    return $this->resp($result['res_code'],$result['res_data']);
    }else{
    $res_data = $result['res_data'];
    return $this->resp(0, [
    'pay_type' => Payorder::PAY_TZT, // 投资通
    'status'   => YpTztOrder::model()->getPayorderStatus($res_data['status']),
    'orderid'  => $res_data['orderid'],
    'yborderid'=> $res_data['yborderid'],
    ]);
    }*/
    }

    /**
     * 生成绑卡的链接地址
     * @param $orderid
     * @return string
     */
    private function createBindPayUrl($id, $type = 101) {
        $cryStr = Crypt3Des::encrypt($id, Yii::$app->params['trideskey']);
        if ($type == 101) {
            $url = Yii::$app->request->hostInfo . '/pay/payurl/?xhhorderid=' . urlencode($cryStr);
        } elseif ($type == 103) {
            $url = Yii::$app->request->hostInfo . '/pay/chanpaypayurl/?xhhorderid=' . urlencode($cryStr);
        } else {
            $url = Yii::$app->request->hostInfo . '/pay/payurl/?xhhorderid=' . urlencode($cryStr);
        }
        return $url;
    }

    /**
     * 102  一键支付
     */
    private function yeepayquick($payOrderModel) {
        // 执行易宝一健支付动作
        $action = Yii::createObject([
            'class' => '\app\modules\api\controllers\actions\YeepayquickAction',
            'reqData' => $payOrderModel->attributes,
            'appData' => $this->appData,
            'reqType' => 'return',
        ],
            ['payrequest', $this]);

        $result = $action->runWithParams([]);
        if ($result['res_code']) {
            return $this->resp($result['res_code'], $result['res_data']);
        } else {
            $res_data = $result['res_data'];
            return $this->resp(0, [
                'url' => $res_data['url'],
                'pay_type' => Payorder::PAY_QUICK, // 一键支付
                'status' => $res_data['status'], //一键支付订单状态只有0,2与 总订单一致
                'orderid' => $res_data['orderid'],
            ]);
        }
    }

    /**
     * 畅捷快捷API支付
     */
    private function chanpayquick($payOrderModel, $card_type, $bankname, $bankcode, $validate, $cvv2, $userip) {
        //1  查询对应银行卡的信息
        $bindbank = new BindBank();
        $bankinfo = $bindbank->getBindBankInfo($payOrderModel->aid, $payOrderModel->identityid, $payOrderModel->cardno);
        if (empty($bankinfo->cardno)) {
            //保存银行卡的信息
            $condition = array(
                'aid' => $payOrderModel->aid,
                'identityid' => $payOrderModel->client_identityid,
                'idcardno' => $payOrderModel->idcard,
                'user_name' => $payOrderModel->username,
                'cardno' => $payOrderModel->cardno,
                'bank_mobile' => $payOrderModel->phone,
                'card_type' => $card_type,
                'bank_name' => $bankname,
                'bank_code' => $bankcode,
                'validate' => !empty($validate) ? $validate : '0',
                'cvv2' => !empty($cvv2) ? $cvv2 : '0',
                'userip' => $userip,
            );

            $result = $bindbank->saveOrder($condition);
        }

        $status = 1;
        //2  是否绑卡
        $payOrderModel->status = $status;
        $payOrderModel->save();
        return $this->resp(0, [
            'url' => $this->createBindPayUrl($payOrderModel->id, 103),
            'pay_type' => Payorder::PAY_CHANPAY, // 畅捷支付
            'status' => $payOrderModel->status,
            'orderid' => $payOrderModel->orderid,
        ]);
    }

    /**
     * 获取支付的路由
     */
    public function actionGetroute() {
        $data = $this->reqData;
        if (empty($data['bankcode']) && empty($data['bankname'])) {
            return $this->resp(2701, "银行编码或名称不能同时为空");
        }
        if (!isset($data['card_type']) || empty($data['card_type'])) {
            return $this->resp(2701, "银行卡类型不能为空");
        }
        $data['bankcode'] = $this->bankSupportModel->getBankCodeAlias($data['bankcode'], $data['bankname']);

        $route = $this->bankSupportModel->getPayRoute($data['bankcode'], $data['card_type']);
        if (empty($route)) {
            return $this->resp(2703, "不支持此银行卡");
        }

        $data = $route->attributes;
        if (is_array($data)) {
            $data['pay_type_str'] = Payorder::model()->getPayTypeMsg($data['pay_type']);
        }
        return $this->resp(0, $data);
    }

    /**
     * 获取支付的路由
     */
    public function actionGetorder() {
        //1 参数验证
        $data = $this->reqData;
        $orderid = $this->reqData['orderid'];
        if (empty($orderid)) {
            return $this->resp(2711, '订单不能为空！');
        }
        $aid = $this->appData['id'];

        //2  总表中是否存在
        $payModel = Payorder::model()->getByOrder($orderid, $aid);
        if (!$payModel) {
            return $this->resp(2712, "未找到该订单");
        }

        //3 查询订单
        switch ($payModel->pay_type) {
        //1  投资通
        case 101:
            $action = Yii::createObject([
                'class' => '\app\modules\api\controllers\actions\YeepaytztAction',
                'reqData' => ['orderid' => $orderid],
                'appData' => $this->appData,
                'reqType' => 'return',
            ],
                ['getorder', $this]);

            $result = $action->runWithParams([]);
            if ($result['res_code']) {
                return $this->resp($result['res_code'], $result['res_data']);
            } else {
                $res_data = $result['res_data'];
                return $this->resp(0, [
                    'pay_type' => Payorder::PAY_TZT, // 投资通
                    'status' => $res_data['status'],
                    'orderid' => $res_data['orderid'],
                    'yborderid' => $res_data['yborderid'],
                ]);
            }

            break;

        //2  一键支付
        case 102:
            $action = Yii::createObject([
                'class' => '\app\modules\api\controllers\actions\YeepayquickAction',
                'reqData' => ['orderid' => $orderid],
                'appData' => $this->appData,
                'reqType' => 'return',
            ],
                ['getorder', $this]);

            $result = $action->runWithParams([]);
            if ($result['res_code']) {
                return $this->resp($result['res_code'], $result['res_data']);
            } else {
                $res_data = $result['res_data'];
                return $this->resp(0, [
                    'pay_type' => Payorder::PAY_QUICK, // 一键支付
                    'status' => $res_data['status'], // 状态是一样的
                    'orderid' => $res_data['orderid'],
                    'yborderid' => $res_data['yborderid'],
                ]);
            }

            break;

        // ....

        default:
            $result = $this->resp(2703, "不支持此银行卡");
            return $result;
            break;
        }

    }

}

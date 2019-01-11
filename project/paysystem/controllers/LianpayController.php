<?php
/**
 * 易宝API投资通服务
 * 内部错误码范围1040000-1040099
 * @author lijin
 */
namespace app\controllers;
use app\common\Logger;
use app\models\App;
use app\models\PaySms;
use app\models\BindBank;
use app\models\lian\LianOrder;
use app\models\Payorder;
use app\models\yeepay\YpBindbank;
use app\modules\api\common\lianlian\CBack;
use app\modules\api\common\lianlian\CLian;
use Yii;

class LianpayController extends BaseController {

    public $layout = false;
    private $oCLian; 

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $env = SYSTEM_PROD ? 'prod' : 'dev';
        $this->oCLian = new CLian($env);
    }
    public function beforeAction($action) {
        if (in_array($action->id, ['backbind', 'backpay'])) {
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
        $lian_id = (new LianOrder)->decryptId($cryid);
        if (!$lian_id) {
            return $this->showMessage(140101, "订单不合法或信息不完整", '');
        }

        //2  获取是否存在该订单
        $oLianOrder = (new LianOrder)->getByLianId($lian_id);
        if (!$oLianOrder) {
            return $this->showMessage(140102, '此订单不存在');
        }
        // $oLianOrder->status = 8;
        //3 按状态进行处理
        if ($oLianOrder->status == Payorder::STATUS_NOBIND) {
            // 未绑定: 打开绑定页面
            return $this->bindview($oLianOrder);

        } elseif ($oLianOrder->status == Payorder::STATUS_BIND) {
            // 已绑定: 打开支付页面
            return $this->payview($oLianOrder);

        } elseif ($oLianOrder->status == Payorder::STATUS_PAYOK) {
            return $this->showMessage(140103, '此订单已经处理完毕, 并且支付成功');
        } elseif ($oLianOrder->status == Payorder::STATUS_PAYFAIL) {
            return $this->showMessage(140104, '此订单已经处理完毕, 并且支付失败');
        } else {
            return $this->showMessage(140105, '此订单状态不合法');
        }
    }
    /**
     * 未绑定时: 签约并授权
     * @param  object $oLianOrder
     * @return html
     */
    private function bindview($oLianOrder) {
        //1. 组合数据
        $res = $this->oCLian->signApply($oLianOrder);
        if ($res['res_code'] != 0) {
            return $this->showMessage($res['res_code'], $res['res_data']);
        }

        //2. 输出js代码
        //echo htmlentities(var_export($res['res_data'], true)) ;
        echo $res['res_data']['html_code'];
        exit;
    }

    /**
     * 显示支付页面
     * @param  object $oLianOrder
     * @return  html
     */
    private function payview($oLianOrder) {
        //1 绑定关系
        $oBind = BindBank::findOne($oLianOrder->bind_id);
        if (!$oBind || $oBind->status != BindBank::STATUS_BINDOK) {
            return $this->showMessage(140106, '此订单银行卡绑定不正确');
        }

        //2 输出页面
        $cryid = $oLianOrder->encryptId($oLianOrder->id);
        return $this->render('payurl', [
            'oLianOrder' => $oLianOrder,
            'oBind' => $oBind,
            'xhhorderid' => $cryid,
        ]);
    }
    /**
     * 判断 是绑定还是 支付
     * 绑定：自己发短信
     * 未绑定：请求绑定，易宝返回验证码
     */
    public function actionGetsmscode() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid', '');
        $lian_id = (new LianOrder)->decryptId($cryid);
        if (!$lian_id) {
            return $this->showMessage(140201, "订单不合法或信息不完整", '');
        }

        //2  获取是否存在该订单
        $oLianOrder = (new LianOrder)->getByLianId($lian_id);
        if (!$oLianOrder) {
            return $this->showMessage(140202, '此订单不存在');
        }

        if ($oLianOrder->status != Payorder::STATUS_BIND) {
            return $this->showMessage(140203, "此订单状态错误!无法完成操作");
        }

        //3 获取主订单
        $oPayorder = (new Payorder)->getByOrder($oLianOrder->orderid, $oLianOrder->aid);
        if (!$oPayorder) {
            return $this->showMessage(140204, "主订单异常,请联系相关人员");
        }

        //4 发送短信, 子函数
        return $this->requestSms($oPayorder);
    }
    /**
     * 发送短信程序
     */
    private function requestSms($oPayorder) {
        //1 保存短信验证码
        if ($oPayorder->status != Payorder::STATUS_BIND) {
            return $this->showMessage(2123, "支付的银行卡必须是绑定的");
        }
        $smscode = rand(100000, 999999);
        $oPayorder->smscode = (string) $smscode;
        $res = $oPayorder->save();

        //2 发送短信
        if (!(defined('SYSTEM_LOCAL') && SYSTEM_LOCAL)) {
            $res = (new PaySms)->sendSms(
                $oPayorder->phone,
                $smscode,
                'MERCHANT',
                $oPayorder->amount,
                $oPayorder->aid
            );
            if (!$res) {
                return $this->showMessage(140304, "系统故障!请稍后重试或请您联系客服");
            }
        }

        //3 返回结果
        return $this->showMessage(0, [
            'isbind' => false,
            'nexturl' => Yii::$app->request->hostInfo . '/lianpay/paycomfirm',
        ]);
    }

    /**
     * 确认支付
     */
    public function actionPaycomfirm() {
        //1 验证参数是否正确
        $cryid = $this->post('xhhorderid', '');
        $lian_id = (new LianOrder)->decryptId($cryid);
        if (!$lian_id) {
            return $this->showMessage(140301, "订单不合法或信息不完整");
        }

        //2  获取是否存在该订单
        $oLianOrder = (new LianOrder)->getByLianId($lian_id);
        if (!$oLianOrder) {
            return $this->showMessage(140302, '此订单不存在');
        }

        $validatecode = $this->post('validatecode');
        if (empty($validatecode)) {
            return $this->showMessage(140303, "smscode未找到");
        }

        //3 必须是绑定状态下
        if ($oLianOrder->status != Payorder::STATUS_BIND) {
            return $this->showMessage(140304, "此卡状态已经变更!");
        }

        //4  获取主订单, 短信验证码检测
        $oPayorder = $oLianOrder->payorder;
        if (!$oPayorder) {
            return $this->showMessage(140204, "主订单异常,请联系相关人员");
        }
        if ($validatecode != $oPayorder->smscode) {
            return $this->showMessage(140305, "验证码错误");
        }

        //5 调用支付接口
        $status = $this->oCLian->applyPay($oLianOrder);

        //6. 只有支付中, 支付成功, 支付失败三种状态有效
        if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL, Payorder::STATUS_DOING])) {
            $url = $oPayorder->clientBackurl();
            return $this->showMessage(0, [
                'callbackurl' => $url,
            ]);
        } else {
            return $this->showMessage(140308, "订单处理失败");
        }
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
            return $this->render('showmessage', [
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        }
    }
    /**
     * 连连绑卡页面回调
     * /lianpay/backbind/tYMA?status=0000&result=%7B%22agreeno%22%3A%222017010934533801%22%2C%22oid_partner%22%3A%22201612161001339313%22%2C%22repayment_no%22%3A%22R1O1483928630%22%2C%22sign%22%3A%2220d379e035a03e595076c4a6ce89838d%22%2C%22sign_type%22%3A%22MD5%22%2C%22user_id%22%3A%22I1Ulj_0000001%22%7D
     *
     * /lianpay/backbind/tYMA?status=10044&result=商户请求参数[sign]校验错误[10044]
     */
    public function actionBackbind($lianid = null) {
        //1. GET方式,纪录日志并获取参数
        Logger::dayLog('lian', 'lianpay/backbind', $lianid, 'GET', $this->get(), 'POST', $this->post());
        $status = $this->getParam('status');
        $result = $this->getParam('result');
        if (!isset($status) || !isset($result)) {
            return $this->showMessage(140401, '非法请求');
        }

        //2. 进行绑卡操作
        $lianid = $lianid ? $this->oCLian->decryptLianId($lianid) : 0;

        $oCBack = new CBack;
        $oCBack->oCLian = $this->oCLian;
        $isBindOk = $oCBack->backbind($lianid, $status, $result);
        $oLianOrder = $oCBack->oLianOrder;
        if (!$oLianOrder) {
            return $this->showMessage(140403, '绑卡签约回调异常');
        }
        Logger::dayLog('lian', 'lianpay/isBindOk', $isBindOk);
        //4. 按绑定结果跳转
        if ($isBindOk) {
            //跳转到支付链接
            $url = $oLianOrder->getPayUrl($oLianOrder->id,'lianpay');
        } else {
            //业务端回调失败结果
            $oPayorder = (new Payorder)->getByOrder($oLianOrder->orderid, $oLianOrder->aid);
            // Logger::dayLog('lian', 'lianpay/oPayorder', $oPayorder);
            if ($oPayorder) {
                $url = $oPayorder->clientBackurl();
            }else{
            	$url = "";
            }
        }
        
        // Logger::dayLog('lian', 'lianpay/url', $url);
        if (!$url) {
            return $this->showMessage(140404, '回调异常');
        }
        header("Location:{$url}");
        exit;
    }
    /**
     * 支付异步通知接口
     */
    public function actionBackpay() {
        //1. 纪录日志并获取参数
        $data_json = file_get_contents("php://input");
        Logger::dayLog('lian', 'lianpay/backpay', $data_json);

        // 本地测试
        if (!$data_json && defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            $data_json = $this->testBackpay();
        }

        //3 解析数据; 保存状态. 通知结果
        $oCBack = new CBack;
        $oCBack->oCLian = $this->oCLian;
        $result = $oCBack->backpay($data_json);

        //4 输出结果
        if (!$result) {
            Logger::dayLog('lian', 'lianpay/backpay', $oCBack->errinfo);
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
     * 测试桩
     */
    private function testBackpay() {
        return '{
  "bank_code": "03080000",
  "dt_order": "20170110151947",
  "info_order": "购买电子产品",
  "money_order": "0.02",
  "no_order": "1_1484031228",
  "oid_partner": "201612161001339313",
  "oid_paybill": "2017011011836009",
  "pay_type": "D",
  "result_pay": "SUCCESS",
  "settle_date": "20170110",
  "sign": "5f5d56b7770343cb779aba31265db012",
  "sign_type": "MD5"
            }';
    }
}

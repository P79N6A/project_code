<?php
namespace app\modules\api\common\lianlian;
use app\common\DigitEncrypt;
use app\common\Logger;
use app\models\BindBank;
use app\models\lian\LianOrder;
use app\models\Payorder;
use Yii;

/**
 * 连连支付流程处理: 签约->授权->支付
 */
class CLian {
    private $oLianApi;
    public function __construct($cfg) {
        $this->oLianApi = new LianApi($cfg);
    }
    /**
     * 通过主订单调用支付流程
     * @param  obj $oPayorder
     * @param  str $bankname
     * @return [res_code, res_data]
     */
    public function createPayOrder($oPayorder, $bankname) {
        //1. 绑定银行卡, 保存当前订单信息
        $data = $oPayorder->attributes;
        $data['bankname'] = $bankname; // 历史遗留问题, 未存bankname
        $data['identityid'] = $data['client_identityid']; //此处应该为客户端实际user_id

        //2. 保存连连支付订单
        $oLianOrder = $this->saveBindOrder($data);
        if (!$oLianOrder) {
            return ['res_code' => 1040001, 'res_data' => '订单保存失败'];
        }

        //3. 调用授权接口
        /*if ($oLianOrder->status == Payorder::STATUS_BIND) {
        $result = $this->apply($oLianOrder);
        if (!$result) {
        return ['res_code' => 1040002, 'res_data' => '授权失败'];
        }
        }*/

        //4. 判断状态是否正确
        if (!in_array($oLianOrder->status, [Payorder::STATUS_NOBIND, Payorder::STATUS_BIND])) {
            return ['res_code' => 1040003, 'res_data' => '订单状态不合法'];
        }

        //5. 同步主订单状态
        $result = $oPayorder->saveStatus($oLianOrder->status);

        //6. 返回下一步处理流程
        $res_data = $this->getPayUrl($oLianOrder);
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    /**
     * 保存绑卡和订单信息
     * @param  [] $data
     * @return obj
     */
    private function saveBindOrder($data) {
        //1 获取并保存绑卡信息
        $oBind = $this->getBindBank($data);
        if (empty($oBind)) {
            return null;
        }

        //2 保存连连订单
        $data['bind_id'] = $oBind->id;
        $data['status'] = $oBind->status == BindBank::STATUS_BINDOK ? Payorder::STATUS_BIND : Payorder::STATUS_NOBIND;
        $oLianOrder = $this->saveOrderInfo($data);
        return $oLianOrder;
    }
    /**
     * 通过数据库保存
     * @param object  $oLianOrder
     * @return [res_code, res_data]
     */
    private function getPayUrl($oLianOrder) {
        return [
            'url' => $oLianOrder->getLianPayUrl($oLianOrder->id),
            'pay_type' => Payorder::PAY_LIANLIAN,
            'status' => $oLianOrder->status, //1,8
            'orderid' => $oLianOrder->orderid,
        ];
    }
    /**
     * 未绑定时: 签约
     * @param  obj $oLianOrder
     * @return [res_code, res_data]
     */
    public function signApply($oLianOrder) {
        //1 获取绑定关系
        $oBind = BindBank::findOne($oLianOrder->bind_id);
        if (!$oBind) {
            return ['res_code' => 1040010, 'res_data' => "此订单银行卡绑定不正确"];
        }
        if ($oBind->status == BindBank::STATUS_BINDOK) {
            return ['res_code' => 1040011, 'res_data' => "此卡已经签约成功,不能重复"];
        }

        //2. 目前是金额的单位是分
        $amount = $oLianOrder->amount / 100;
        $date = date('Y-m-d');
        $user_id = $oLianOrder->getIdentityid($oLianOrder['identityid'], $oLianOrder['aid']);

        //3 回调地址将id加密进去
        $enIdStr = $this->encryptLianId($oLianOrder->id);
        $url_return = Yii::$app->request->hostInfo . '/lianpay/backbind/' . $enIdStr;

        //4 . 调用接口
        $payData = [
            'contract_type' => '先花花', // @todo
            'contact_way' => '010-82662678', // @todo

            'user_id' => $user_id,
            'id_no' => $oBind['idcardno'],
            'acct_name' => $oBind['user_name'],
            'card_no' => $oBind['cardno'],
            'url_return' => $url_return,
            'user_info_bind_phone' => $oBind['bank_mobile'],
            /*'date' => $date,
        'amount' => $amount,
        'repayment_no' => $oLianOrder['repayment_no'],*/
        ];
        $res = $this->oLianApi->signApply($payData);
        if ($res['res_code'] > 0) {
            return ['res_code' => 1040012, 'res_data' => "调用签约接口失败"];
        }
        return $res;
    }
    /**
     * 已绑定: 直接授权
     * @param object $oLianOrder
     * @return bool
     */
    public function apply($oLianOrder) {
        //1. 目前是金额的单位是分
        $amount = $oLianOrder->amount / 100;
        $date = date('Y-m-d');

        //2. 授权已绑定时: 调用授权接口
        $user_id = $oLianOrder->getIdentityid($oLianOrder['identityid'], $oLianOrder['aid']);
        $oBind = (new BindBank)->getByBid($oLianOrder['bind_id']);
        if (!$oBind || !isset($oBind['bind_no'])) {
            return false;
        }
        $payData = [
            'contract_type' => '先花花', // @todo
            'contact_way' => '010-82662678', // @todo
            'user_id' => $user_id,
            'date' => $date,
            'amount' => $amount,
            'repayment_no' => $oLianOrder['repayment_no'],
            'no_agree' => $oBind['bind_no'],
            'user_info_bind_phone' => $oBind['bank_mobile'],
        ];
        $res = $this->oLianApi->apply($payData);

        //3. 失败处理
        if ($res['res_code'] != 0) {
            // 保存错误信息到订单中
            $result = $oLianOrder->savePayFail($res['res_code'], $res['res_data']);
            if (!$result) {
                Logger::dayLog('lian', "clian/apply", $oLianOrder->attributes, $oLianOrder->errors);
            }
            return false;
        }

        return true;
    }
    /**
     * 支付结果
     * @param  object $oLianOrder
     * @return int 支付状态. 目前只可能是 4, 11(支付中, 支付失败) 和 -1 (无效)
     */
    public function pay($oLianOrder) {
        //1. 增加状态锁定
        $result = $oLianOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return -1;
        }

        //2. 目前是金额的单位是分
        $amount = $oLianOrder->amount / 100;
        $notify_url = SYSTEM_PROD ?
        Yii::$app->request->hostInfo . '/lianpay/backpay/' :
        'http://182.92.80.211:8091/lianpay/backpay';

        //3. 银行卡绑定判断
        $user_id = $oLianOrder->getIdentityid($oLianOrder['identityid'], $oLianOrder['aid']);
        $oBind = (new BindBank)->getByBid($oLianOrder['bind_id']);
        if (!$oBind || !isset($oBind['bind_no'])) {
            return -1;
        }

        //3. 商品名称
        $oPayorder = (new Payorder)->getByOrder($oLianOrder->orderid, $oLianOrder->aid);
        if ($oPayorder) {
            $name_goods = $oPayorder['productname'];
            $info_order = $oPayorder['productdesc'];
        } else {
            $name_goods = '购买电子产品';
            $info_order = '购买电子产品';
        }

        $payData = [
            'user_id' => $user_id,

            'no_order' => $oLianOrder['aid_orderid'],
            'dt_order' => date('YmdHis'),
            'name_goods' => $name_goods,
            'info_order' => $info_order,
            'money_order' => $amount,
            'notify_url' => $notify_url,

            'schedule_repayment_date' => date('Y-m-d'),
            'repayment_no' => $oLianOrder['repayment_no'],
            'no_agree' => $oBind['bind_no'],

            'id_no' => $oBind['idcardno'],
            'acct_name' => $oBind['user_name'],
            'card_no' => $oBind['cardno'],
            'user_info_bind_phone' => $oBind['bank_mobile'],
        ];
        $res = $this->oLianApi->pay($payData);

        //3. 支付错误处理
        if ($res['res_code'] != 0) {
            $result = $oLianOrder->savePayFail($res['res_code'], $res['res_data']);
            //纪录错误日志
            if (!$result) {
                Logger::dayLog('lian', "clian/pay/savePayFail", $oLianOrder->attributes, $oLianOrder->errors);
            }
        }

        //6. 返回当前状态
        return $oLianOrder->status;
    }
    /**
     * 授权并支付: 综合apply, pay接口
     * @param  object $oLianOrder
     * @return status  同pay
     */
    public function applyPay($oLianOrder) {
        //1. 授权
        $result = $this->apply($oLianOrder);
        if (!$result) {
            Logger::dayLog('lian', "clian/applyPay/apply", $oLianOrder->id, "授权失败");
            return -1;
        }

        //2. 支付
        $status = $this->pay($oLianOrder);
        return $status;
    }

    /**
     * 保存连连支付订单
     * @param  [] $data
     * @return [res_code, res_data]
     */
    private function saveOrderInfo($data) {
        $oLianOrder = new LianOrder;
        $result = $oLianOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('lian', "clian/saveorderInfo", $oLianOrder->attributes, $oLianOrder->errors);
        }

        return $result ? $oLianOrder : null;
    }

    /**
     * 获取并保存绑定信息
     * @param [] $data
     * @return  object
     */
    private function getBindBank($data) {
        //1 获取绑定信息
        $oBind = (new BindBank)->getBindBankInfo($data['aid'], $data['identityid'], $data['cardno'], 104);
        if (empty($oBind)) {
            $bindData = [
                'pay_type' => 104,
                'aid' => $data['aid'],
                'identityid' => $data['client_identityid'],
                'idcardno' => $data['idcard'],
                'user_name' => $data['username'],
                'cardno' => $data['cardno'],
                'card_type' => 1,
                'bank_mobile' => $data['phone'],
                'bank_name' => $data['bankname'],
                'userip' => $data['userip'],
            ];
            $oBind = new BindBank;
            $result = $oBind->saveOrder($bindData);
            if (!$result) {
                Logger::dayLog('lian', "clian/getBindBank", $oBind->attributes, $oBind->errors);
            }
            return $result ? $oBind : null;
        }
        return $oBind;
    }
    /**
     * 验签方法
     * @param  [] $json_data
     * @return bool
     */
    public function verifyJson($json_data) {
        $data = json_decode($json_data, true);
        return $this->oLianApi->verify($data);
    }
    /**
     * 用于绑卡回调加解密
     * @param  int $lianid
     * @return  string
     */
    public function encryptLianId($lianid) {
        return (new DigitEncrypt(115927))->encrypt($lianid);
    }
    public function decryptLianId($str) {
        $str = (string) $str;
        return (new DigitEncrypt(115927))->decrypt($str);
    }
}
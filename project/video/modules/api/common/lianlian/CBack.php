<?php
namespace app\modules\api\common\lianlian;
use app\common\Logger;
use app\models\App;
use app\models\lian\LianOrder;
use app\models\Payorder;
use Yii;

/**
 * 连连支付回调结果处理
 */
class CBack {
    /**
     * 当前回调处理的订单
     */
    public $oLianOrder;
    /**
     * 定义出错
     */
    public $errinfo;
    private function returnError($result, $errinfo) {
        $this->errinfo = $errinfo;
        return $result;
    }
    /**
     * 签约授权接口回调处理规则
     * @param  int $lian_id
     * @param  str $status
     * @param  json | str $result
     * @return str $url
     */
    public function backbind($lian_id, $status, $result) {
        if ($status == '0000') {
            $res = json_decode($result, true);
            $result = $this->backbindSuccess($lian_id, $res);
        } else {
            $result = $this->backbindFail($lian_id, $status, $result);
        }

        if (!$result) {
            Logger::dayLog('lian', 'cback/backbind', $lian_id, $status, $result);
            return false;
        }
        return $this->oLianOrder->status == Payorder::STATUS_BIND;
    }
    /**
     * 签约授权接口回调成功时
     * @param [] $res
     * @return LianOrder
     */
    private function backbindSuccess($lian_id, $res) {
        //1 获取连连订单信息
        $this->oLianOrder = $oLianOrder = (new LianOrder)->getByLianId($lian_id);
        if (!$oLianOrder) {
            return $this->returnError(false, "未找到id:{$lian_id}信息");
        }
        if ($oLianOrder->status != Payorder::STATUS_NOBIND) {
            return $this->returnError(false, "当前状态不合法");
        }

        //2 参数提取与校验是否是同一订单
        $user_id = $oLianOrder->getIdentityid($oLianOrder['identityid'], $oLianOrder['aid']);
        if ($res['user_id'] != $user_id) {
            // 非法请求
            Logger::dayLog('lian', "cback/backbindSuccess/user_id", $res['user_id'], $user_id);
            return $this->returnError(false, "repayment_no无法对应. 非法请求");
        }

        //3 更新为绑定状态
        $result = $oLianOrder->savePayBind($res['agreeno']);
        if (!$result) {
            Logger::dayLog('lian', "cback/backbindSuccess/savePayBind", $oLianOrder['agreeno']);
            return $this->returnError(false, "数据保存失败");
        }

        return true;
    }
    /**
     * 签约授权接口回调失败时
     * @param   $lian_id
     * @param   $error_code
     * @param   $error_msg
     * @return LianOrder
     */
    private function backbindFail($lian_id, $error_code, $error_msg) {
        //1 获取连连订单信息
        $this->oLianOrder = $oLianOrder = (new LianOrder)->getByLianId($lian_id);
        if (!$oLianOrder) {
            return $this->returnError(false, "未找到id:{$lian_id}信息");
        }
        if ($oLianOrder->status != Payorder::STATUS_NOBIND) {
            return $this->returnError(false, "当前状态不合法");
        }

        //2 更新为失败状态
        $result = $oLianOrder->savePayFail($error_code, $error_msg);
        return $result;
    }
    /**
     * 支付结果异步回调:连连仅支付成功才回调
     * @param  [] $data
    [
    'bank_code' => '01030000', // 连连银行编码
    'dt_order' => '20171221171613', // 订单时间
    'info_order' => '买了一部苹果手机，客户要求后天发货',
    'money_order' => '0.01', //金额
    'no_order' => '2013051508953', // 商户订单号
    'oid_partner' => '201612161001339313', //商户编号
    'oid_paybill' => '2016122708536780', // 连连支付订单号
    'pay_type' => 'D',
    'result_pay' => 'SUCCESS', // 支付结果
    'settle_date' => '20161227', // 清算时间
    'sign' => '68d812df242f58b426635c9557b220a7',
    'sign_type' => 'MD5',
    ]
     * @return [res_code, res_data]
     */
    public function backpay($data) {
        //1 参数校验
        if (!is_array($data) || !isset($data['no_order'])) {
            return $this->returnError(false, '参数不合法');
        }
        $aid_orderid = $data['no_order'];
        if (!$aid_orderid) {
            return $this->returnError(false, '此订单no_order为空');

        }
        $result_pay = strtoupper($data['result_pay']);
        if ($result_pay !== 'SUCCESS') {
            return $this->returnError(false, '此订单返回状态码不正确');

        }

        //2 获取订单
        $this->oLianOrder = $oLianOrder = (new LianOrder)->getByAOrderId($aid_orderid);
        if (!$oLianOrder) {
            return $this->returnError(false, '未找到该订单');
        }

        //3 更新订单为成功
        $is_finished = $oLianOrder->is_finished();
        if (!$is_finished) {
            $transaction = Yii::$app->db->beginTransaction();
            $result = $oLianOrder->savePaySuccess($data['oid_paybill']);
            if (!$result) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        }
        return true;
    }
    /**
     * POST 异步通知客户端
     * @param  object $oLianOrder
     * @return bool
     */
    public function clientNotify($oLianOrder) {
        if (!$oLianOrder) {
            return false;
        }
        $oPayorder = (new Payorder)->getByOrder($oLianOrder->orderid, $oLianOrder->aid);
        if (!$oPayorder) {
            return false;
        }
        $result = $oPayorder->clientNotify();
        return $result;
    }
}
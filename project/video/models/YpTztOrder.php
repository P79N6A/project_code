<?php
/**
 * 接口返回状态码
 * 异步全是终态 0:支付失败; 1:成功; 2:已撤消;
 * 主动查询状态 0:支付失败; 1:成功; 2:未处理; 3:处理中; 4:已撤消;
 * 对应本表状态：基本上是查询接口返回状态码+1
 * 0..         1:请求成功; 2:成功; 3:未处理; 4:处理中; 5:已撤消;   12:请求失败; 11:支付失败
 */
namespace app\models;

use app\models\Payorder;

/**
 * 易宝投资通
 */
class YpTztOrder extends \app\models\BaseModel {

    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_REQOK = 1; // 请求成功
    const STATUS_PAYOK = 2; // 支付成功
    const STATUS_PREDO = 3; // 未处理
    const STATUS_DOING = 4; // 处理中
    const STATUS_CANCEL = 5; // 已撤消
    const STATUS_PAYFAIL = 11; // 支付失败
    const STATUS_REQNO = 12; // 请求失败

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yp_tzt_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'orderid', 'aid_orderid', 'transtime', 'amount', 'productname', 'identityid', 'identitytype', 'card_top', 'card_last', 'callbackurl', 'userip', 'create_time', 'modify_time'], 'required'],
            [['aid', 'transtime', 'currency', 'amount', 'identitytype', 'orderexpdate', 'create_time', 'modify_time', 'closetime', 'pay_status', 'client_status', 'error_code'], 'integer', 'message' => "{attribute}必须是数字"],
            [['productname', 'identityid', 'imei', 'yborderid', 'error_msg'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['card_top'], 'string', 'max' => 6],
            [['card_last'], 'string', 'max' => 4],
            [['callbackurl'], 'string', 'max' => 255],
            [['orderid', 'userip', 'source_type'], 'string', 'max' => 30],
            [['ua'], 'string', 'max' => 100],
            [['aid_orderid'], 'string', 'max' => 50],
            [['orderid'], 'unique', 'message' => '此orderid{value}已经存在'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'orderid' => '客户订单号',
            'transtime' => '交易时间',
            'currency' => '交易币种:默认156',
            'amount' => '交易金额',
            'productname' => '商品名称',
            'productdesc' => '商品描述',
            'identityid' => '用户标识',
            'identitytype' => '用户标识类型',
            'card_top' => '卡前六位',
            'card_last' => '卡后四位',
            'orderexpdate' => '订单有效期时间 以分为单位',
            'callbackurl' => '系统提供的回调地址',
            'imei' => '设备唯一号',
            'userip' => '用户ip',
            'ua' => '浏览器信息',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'pay_status' => '(内部)0:未支付 1完成支付; 11支付失败; 12:支付撤消;',
            'client_status' => '(内部)异步通知客户端状态 0:未响应 1:响应支付成功 2：响应支付失败',
            'yborderid' => '(内部)易宝流水号',
            'error_code' => '(内部)易宝返回错误码',
            'error_msg' => '(内部)易宝返回错误描述',
        ];
    }

    /**
     * 根据订单获取纪录
     * @param $orderid 订单号
     * @param $aid 应用id
     * @return [] | null
     */
    public function getByOrder($orderid, $aid) {
        if (!$orderid) {
            return null;
        }
        $condition = [
            'orderid' => $orderid,
            'aid' => $aid,
        ];
        return static::find()->where($condition)->one();
    }
    /**
     * 获取某订单信息
     */
    public function getByAidOrderId($aid_orderid) {
        if (empty($aid_orderid)) {
            return null;
        }
        return self::find()->where(["aid_orderid" => $aid_orderid])->one();
    }
    /**
     * 接口返回状态码
     * 主动查询状态 0:失败; 1:成功; 2:未处理; 3:处理中; 4:已撤消;
     * 对应本表状态：基本上是查询接口返回状态码+1
     * 0..     1:请求成功; 2:成功; 3:未处理; 4:处理中; 5:已撤消;   12:请求失败; 11:支付失败
     */
    public function syncStatus($status) {
        $map = [
            0 => self::STATUS_PAYFAIL, // 支付失败
            1 => self::STATUS_PAYOK, // 支付成功
            2 => self::STATUS_PREDO, // 未处理
            3 => self::STATUS_DOING, // 处理中
            4 => self::STATUS_CANCEL, // 已撤消
        ];
        $payStatus = isset($map[$status]) ? $map[$status] : (-1 * $status);
        return $payStatus;
    }
    /**
     * 易宝异步返回的状态转换成数据库需要的形式
     * 异步状态转换关系
     * 异步全是终态  0:支付失败; 1:成功; 2:已撤消;
     */
    public function asyncStatus($status) {
        $map = [
            0 => self::STATUS_PAYFAIL, //支付失败
            1 => self::STATUS_PAYOK, //支付成功
            2 => self::STATUS_CANCEL, //已撤消
        ];
        $payStatus = isset($map[$status]) ? $map[$status] : (-1 * $status);
        return $payStatus;
    }
    /**
     * 转换成主订单表状态
     * 两者基本一致
     */
    public function getPayorderStatus($tztStatus) {

        /*const STATUS_INIT = 0;
        const STATUS_REQOK = 1; // 请求成功
        const STATUS_PAYOK = 2; // 支付成功
        const STATUS_PREDO = 3; // 未处理
        const STATUS_DOING = 4; // 处理中
        const STATUS_CANCEL = 5; // 已撤消
        const STATUS_PAYFAIL = 11; // 支付失败
        const STATUS_REQNO = 12; // 请求失败
         */

        $status = Payorder::STATUS_INIT;
        switch ($tztStatus) {
        case self::STATUS_INIT: // 初始
            $status = Payorder::STATUS_INIT;
            break;
        case self::STATUS_REQOK: // 请求成功
            $status = Payorder::STATUS_INIT;
            break;

        case self::STATUS_PAYOK:
            $status = Payorder::STATUS_PAYOK;
            break;

        case self::STATUS_PREDO:
            $status = Payorder::STATUS_INIT;
            break;

        case self::STATUS_DOING:
            $status = Payorder::STATUS_INIT;
            break;

        case self::STATUS_CANCEL:
            $status = Payorder::STATUS_PAYFAIL;
            break;

        case self::STATUS_PAYFAIL:
            $status = Payorder::STATUS_PAYFAIL;
            break;

        case self::STATUS_REQNO:
            $status = Payorder::STATUS_PAYFAIL;
            break;

        default:
            $status = Payorder::STATUS_INIT;
        }
        return $status;
    }
    /**
     * 更新总订单表的状态
     */
    public function upPayorderStatus() {
        // 总支付平台
        $oPayorder = Payorder::model()->getByOrder($this->orderid, $this->aid);
        if (!$oPayorder) {
            return false;
        }
        // 获取订单状态
        $status = $this->getPayorderStatus($this->pay_status);
        if (!$status) {
            return false;
        }
        $result = $oPayorder->saveStatus($status, $this->error_msg, $this->yborderid);
        if (!$result) {
            Logger::dayLog(
                'db',
                'ypquickorder',
                'upPayorderStatus',
                '错误原因', $oPayorder->errors
            );
        }
        return true;
    }

    /**
     * 异步通知. 目前仅发送成功或失败状态
     * @return bool
     */
    public function clientNotify() {
        //1 仅最终状态发送通知
        $status = $this->getPayorderStatus($this->pay_status);
        if (!in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])) {
            return false;
        }

        //2 发送通知post
        $oPaymodel = new Payorder;
        $oPayorder = $oPaymodel->getByOrder($this->orderid, $this->aid);
        if ($oPayorder) {
            // 使用主订单发送
            $result = $oPayorder->clientNotify();
        } else {
            // 单独发送
            $data = $this->clientData();
            $result = $oPaymodel->clientPost($this->callbackurl, $data, $this->aid);
        }

        //3 保存当前通知状态
        if (!$result) {
            return false;
        }
        $this->client_status = 1;
        $this->modify_time = time();
        return $this->save();
    }
    /**
     * 返回客户端响应结果
     * @return  []
     */
    public function clientData() {
        $status = $this->getPayorderStatus($this->pay_status);
        return [
            'pay_type' => Payorder::PAY_QUICK,
            'status' => $status, //转换成主订单状态
            'orderid' => $this->orderid,
            'yborderid' => $this->yborderid,
            'amount' => $this->amount,
            'error_code' => $error_code,
            'error_msg' => $error_msg,
        ];
    }
}

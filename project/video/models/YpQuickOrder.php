<?php

namespace app\models;

use app\common\Logger;
use app\models\Payorder;

/**
 * 易宝一键支付
 */
class YpQuickOrder extends \app\models\BaseModel {
    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_PAYOK = 2;
    const STATUS_PAYFAIL = 11;

    const STATUS_NOPAY = 6; // 未支付
    const STATUS_CANCEL = 5; // 已撤消
    const STATUS_RISK = 13; // 风控阻断交易

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yp_quick_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'orderid', 'aid_orderid', 'transtime', 'amount', 'productcatalog', 'productname', 'productdesc', 'identityid', 'identitytype', 'terminaltype', 'terminalid', 'orderexpdate', 'userip', 'callbackurl', 'fcallbackurl', 'version', 'paytypes', 'cardno', 'idcardtype', 'idcard', 'owner', 'create_time', 'modify_time'], 'required', "message" => "{attribute}不能为空"],
            [['aid', 'transtime', 'currency', 'amount', 'productcatalog', 'identitytype', 'terminaltype', 'orderexpdate', 'version', 'create_time', 'modify_time', 'pay_status', 'error_code'], 'integer', "message" => "{attribute}应该为数字"],
            [['yeepay_url'], 'string'],
            [['productname', 'identityid', 'userip', 'paytypes', 'cardno', 'idcard', 'error_msg'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['terminalid', 'yborderid'], 'string', 'max' => 100],
            [['userua', 'callbackurl', 'fcallbackurl'], 'string', 'max' => 255],
            [['idcardtype'], 'string', 'max' => 10],
            [['bankcode'], 'string', 'max' => 20],
            [['orderid', 'owner'], 'string', 'max' => 30],
            [['aid_orderid'], 'string', 'max' => 50],
            [['orderid'], 'unique', 'message' => '{attribute}:{value}已经存在'],
        ];
    }

    /**
     * 获取某订单信息
     */
    public function getByOrder($orderid, $aid) {
        if (empty($orderid)) {
            return null;
        }
        if (empty($aid)) {
            return null;
        }
        return self::find()->where(["orderid" => $orderid, 'aid' => $aid])->one();
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
     * 获取某app的支付列表
     */
    public function getByAppId($aid, $offset = 0, $limit = 100) {
        if (empty($orderid)) {
            return null;
        }
        return self::find()->where(["aid" => $aid])->offset($offset)->limit($limit)->all();
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
            'productcatalog' => '商品类别码',
            'productname' => '商品名称',
            'productdesc' => '商品描述',
            'identityid' => '用户标识',
            'identitytype' => '用户标识类型',
            'terminaltype' => '终端类型',
            'terminalid' => '终端ID',
            'orderexpdate' => '订单有效期时间 以分为单位',
            'userip' => '用户IP',
            'userua' => '用户使用的移动终端的UA信息',
            'callbackurl' => '商户后台系统的回调地址（异步回调）',
            'fcallbackurl' => '商户前台系统提供的回调地址(用户点击)',
            'version' => '网页收银台版本',
            'paytypes' => '支付方式 1- 借记卡支付；2- 信用卡支付；3- 手机充值卡支付；4- 游戏点卡支付',
            'cardno' => '银行卡序列号',
            'idcardtype' => '证件类型      01：身份证',
            'idcard' => '证件号',
            'owner' => '持卡人姓名',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'pay_status' => '(内部)0:未支付 1完成支付 10支付失败',
            'client_status' => '(内部)异步通知客户端状态 0:未响应 1:响应支付成功 2：响应支付失败',
            'yborderid' => '(内部)易宝流水号',
            'error_code' => '(内部)易宝返回错误码',
            'error_msg' => '(内部)易宝返回错误描述',
            'yeepay_url' => '(内部)易宝访问地址',
        ];
    }
    // 保存请求的数据
    public function saveOrder($postData) {
        //1 数据验证
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(false, "数据不能为空");
        }
        if (empty($postData['orderid'])) {
            return $this->returnError(false, "订单不能为空");
        }
        if (empty($postData['aid'])) {
            return $this->returnError(false, "应用id不能为空");
        }
        $postData['create_time'] = $postData['modify_time'] = time();

        // 参数检证是否有错
        if ($errors = $this->chkAttributes($postData)) {
            return $this->returnError(false, implode('|', $errors));
        }
        $result = $this->save($postData);
        if (!$result) {
            return $this->returnError(false, implode('|', $this->errors));
        }
        return true;
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

        // 只成功和失败才处理即 2, 11两种状态
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
     * 接口返回状态码
     * 主动查询状态 0:未支付; 1:成功; 2:已撤消; 3:风控阻断交易
     * 对应本表状态
     */
    public function syncStatus($status) {
        $map = [
            0 => self::STATUS_NOPAY, // 未支付
            1 => self::STATUS_PAYOK, // 支付成功
            2 => self::STATUS_CANCEL, // 已撤消
            3 => self::STATUS_RISK, // 风控阻断交易
        ];
        $payStatus = isset($map[$status]) ? $map[$status] : 0;
        return $payStatus;
    }
    /**
     * 转换响应状态
     * @param  int $pay_status db中状态
     * @return int 仅有三种0:初始 2:成功 11:失败
     */
    public function getPayorderStatus($pay_status) {
        $status = 0;
        switch ($pay_status) {

        case self::STATUS_INIT:
            $status = 0;
            break;

        case self::STATUS_PAYOK:
            $status = 2;
            break;

        case self::STATUS_PAYFAIL:
            $status = 11;
            break;

        case self::STATUS_NOPAY:
            $status = 0;
            break;

        case self::STATUS_CANCEL:
            $status = 11;
            break;

        case self::STATUS_RISK:
            $status = 11;
            break;

        default:
            $status = 0;
            break;
        }
        return $status;
    }
    /**
     * 异步通知. 目前仅发送成功或失败状态
     * @return bool
     */
    public function clientNotify() {
        //1 发送通知post
        $oPayorder = (new Payorder)->getByOrder($this->orderid, $this->aid);
        if (!$oPayorder) {
            return false;
        }
        $result = $oPayorder->clientNotify();
        return $result;
    }
    /**
     * 回调连接地址
     * @return str url
     */
    public function clientBackurl() {
        $oPayorder = (new Payorder)->getByOrder($this->orderid, $this->aid);
        if (!$oPayorder) {
            return '';
        }
        $url = $oPayorder->clientBackurl();
        return $url;
    }
}
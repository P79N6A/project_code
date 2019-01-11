<?php

namespace app\models\yeepay;

use app\models\Payorder;
use app\common\Func;

/**
 * 易宝一键支付
 */
class YpQuickOrder extends \app\models\BasePay {

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
            [['aid', 'orderid', 'payorder_id', 'channel_id','cli_orderid', 'transtime', 'amount', 'productcatalog', 'productname', 'productdesc', 'identityid', 'cli_identityid', 'orderexpdate', 'userip', 'cardno', 'idcard', 'name', 'create_time', 'modify_time'], 'required', "message" => "{attribute}不能为空"],
            [['aid', 'payorder_id', 'channel_id','transtime', 'amount', 'productcatalog', 'orderexpdate', 'version'], 'integer', "message" => "{attribute}应该为数字"],
            [['yeepay_url'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['productname', 'cli_identityid', 'userip', 'cardno', 'idcard', 'error_code', 'error_msg'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['other_orderid'], 'string', 'max' => 100],
            [['bankcode', 'identityid'], 'string', 'max' => 20],
            [['orderid', 'name'], 'string', 'max' => 30],
            [['cli_orderid'], 'string', 'max' => 50],
            [['orderid'], 'unique', 'message' => '{attribute}:{value}已经存在'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'payorder_id' => '主订单id',
            'aid' => '应用id',
            'orderid' => '客户订单号',
            'channel_id' => '通道',
            'payorder_id' => '客户订单号',
            'transtime' => '交易时间',
            'currency' => '交易币种:默认156',
            'amount' => '交易金额',
            'productcatalog' => '商品类别码',
            'productname' => '商品名称',
            'productdesc' => '商品描述',
            'identityid' => '用户标识',
            'orderexpdate' => '订单有效期时间 以分为单位',
            'userip' => '用户IP',
            'callbackurl' => '商户后台系统的回调地址（异步回调）',
            'fcallbackurl' => '商户前台系统提供的回调地址(用户点击)',
            'version' => '网页收银台版本',
            //'paytypes' => '支付方式 1- 借记卡支付；2- 信用卡支付；3- 手机充值卡支付；4- 游戏点卡支付',
            'cardno' => '银行卡序列号',
            'idcard' => '证件号',
            'name' => '持卡人姓名',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'status' => '(内部)0:未支付 1完成支付 10支付失败',
            'other_orderid' => '(内部)易宝流水号',
            'error_code' => '(内部)易宝返回错误码',
            'error_msg' => '(内部)易宝返回错误描述',
            'yeepay_url' => '(内部)易宝访问地址',
        ];
    }
    public function optimisticLock() {
        return "version";
    }
    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }
    // 保存请求的数据
    public function saveOrder($data) {
        //1 数据验证
        if (!is_array($data) || empty($data)) {
            return $this->returnError(false, "数据不能为空");
        }
        if (empty($data['orderid'])) {
            return $this->returnError(false, "订单不能为空");
        }
        if (empty($data['aid'])) {
            return $this->returnError(false, "应用id不能为空");
        }

       $postData = [
            'payorder_id' => $data['payorder_id'],
            'other_orderid' => '',
            'aid' => $data['aid'],
            'channel_id' => $data['channel_id'],
            'orderid' =>  $data['orderid'], //客户订单号   √   string  商户生成的唯一订单号，最长50位
            'identityid' =>  $data['identityid'], //用户标识    √   string  最长50位，商户生成的用户账号唯一标识

            'cardno' =>  $data['cardno'], //银行卡序列号   在进行网页支付请求的时候，如果传此参数会把银行卡号直接在银行信息界面显示卡号，注意：P2P商户此参数须必填
            'idcard' =>  $data['idcard'], //证件号     注意：P2P商户此参数须必填
            'name' => $data['name'], //持卡人姓名      注意：P2P商户此参数须必填

            'productcatalog' => (string) $data['productcatalog'], //商品类别码   √   string  详见商品类别码表
            'productname' => (string) $data['productname'], //商品名称    √   string  最长50位，出于风控考虑，请按下面的格式传递值：'应用商品名称，如“诛仙-3阶成品天琊”，此商品名在发送短信校验的时候会发给用户，所以描述内容不要加在此参数中，以提高用户的体验度。
            'productdesc' => (string) $data['productdesc'], //商品描述     最长200位
            'orderexpdate' => intval($data['orderexpdate']), //订单有效期时间       int     以分为单位
            'amount' => intval($data['amount']), //交易金额    √   int     以"分"为单位的整型，必须大于零

            'userip' => (string) $data['userip'], //用户IP    √   string  用户支付时使用的网络终端IP
            'transtime' => time(), //交易时间    √   int     时间戳，例如：1361324896，精确到秒
            'version' => 0,
        ];

        $postData['cli_identityid'] = (new YpBindbank)->getPayIdentityid(
            $postData['identityid'],
            $postData['cardno'],
            $postData['channel_id']
        );
        $postData['cli_orderid'] = Func::toYeepayCode($postData['orderid'], $postData['channel_id']);
        $postData['create_time'] = $postData['modify_time'] = date('Y-m-d H:i:s');

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
     * 接口返回状态码
     * 主动查询状态 0:未支付; 1:成功; 2:已撤消; 3:风控阻断交易
     * 对应本表状态
     */
    public function syncStatus($status) {
        $map = [
            0 => Payorder::STATUS_WILLPAY, // 未支付
            1 => Payorder::STATUS_PAYOK, // 支付成功
            2 => Payorder::STATUS_CANCEL, // 已撤消
            3 => Payorder::STATUS_DOING, // 风控阻断交易
        ];
        $payStatus = isset($map[$status]) ? $map[$status] : Payorder::STATUS_DOING;
        return $payStatus;
    }

    /**
     * 返回链接地址
     * @return []
     */
    public function getPayUrls($pay_controller='', $pay_type='') {
        return [
            'url' => $this->yeepay_url,
            'pay_type' => Payorder::PAY_QUICK,
            'status' => $this->status, //1,8
            'orderid' => $this->orderid,
        ];
    }
    /**
     * 转换
     */
    public function getPayIdentityid($identityid, $cardno) {
        if (!$identityid || !$cardno) {
            return '';
        }
        $card_top = substr($cardno, 0, 6);
        $card_last = substr($cardno, -4);
        $identityid = $identityid . '-' . $card_top . $card_last;
        return $identityid;
    }
}

<?php
/**
 * 统一订单处理类
 */
namespace app\models;
use app\common\Logger;
use Yii;

/**
 * This is the model class for table "{{%payorder}}".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $identityid
 * @property integer $identitytype
 * @property string $orderid
 * @property string $cardno
 * @property string $idcard
 * @property string $username
 * @property string $phone
 * @property string $productname
 * @property string $productdesc
 * @property integer $amount
 * @property integer $orderexpdate
 * @property string $create_time
 * @property string $modify_time
 * @property integer $pay_type
 * @property integer $status
 * @property string $reason
 */
class Payorder extends \app\models\BaseModel {
    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_NOBIND = 1;
    const STATUS_BIND = 8;
    const STATUS_PAYOK = 2;
    const STATUS_PREDO = 3; // 未处理
    const STATUS_DOING = 4; // 处理中
    const STATUS_CANCEL = 5; // 已撤消
    const STATUS_WILLPAY = 6; // 未支付
    const STATUS_PAYFAIL = 11;
    const STATUS_OTHER = 99;

    // 支付方式
    const PAY_TZT = 101; // 投资通
    const PAY_QUICK = 102; // 一键支付
    const PAY_CHANPAY = 103; //畅捷支付
    const PAY_LIANLIAN = 104; //连连支付

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    /**
     * 获取支付类型的中文描述
     * @param int $pay_type 支付类型
     * $param string
     */
    public function getPayTypeMsg($pay_type) {
        $payTypes = [
            self::PAY_TZT => '投资通',
            self::PAY_QUICK => '一键支付',
        ];
        return isset($payTypes[$pay_type]) ? $payTypes[$pay_type] : '';
    }
    /**
     * 获取状态
     */
    public function getStatus() {
        return [
            static::STATUS_INIT => '初始',
            static::STATUS_NOBIND => '未绑卡',
            static::STATUS_BIND => '已绑卡',
            static::STATUS_PAYOK => '成功',
            static::STATUS_PREDO => '未处理', // 未处理
            static::STATUS_DOING => '处理中', // 处理中
            static::STATUS_CANCEL => '已撤消', // 已撤消
            static::STATUS_PAYFAIL => '失败',
            static::STATUS_OTHER => '未知',
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%payorder}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'client_identityid', 'identityid', 'orderid', 'cardno', 'idcard', 'username', 'productname', 'userip'], 'required'],
            [['aid', 'identitytype', 'amount', 'orderexpdate', 'productcatalog', 'create_time', 'modify_time', 'pay_type', 'status', 'client_status'], 'integer'],
            [['payorderid', 'cardno', 'productname'], 'string', 'max' => 50],
            [['orderid'], 'string', 'max' => 30],
            [['client_identityid'], 'string', 'max' => 30],
            [['identityid'], 'string', 'max' => 40],
            [['idcard', 'username', 'phone', 'userip', 'smscode'], 'string', 'max' => 20],
            [['productdesc', 'reason', 'callbackurl'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'identityid' => '商户生成的用户唯一用户id',
            'identitytype' => '用户标识类:2为用户',
            'orderid' => '商户生成的唯一绑卡请求号，最长',
            'payorderid' => '第三方支付订单号',
            'cardno' => '银行卡号',
            'idcard' => '身份证号',
            'username' => '姓名',
            'phone' => '银行留存电话',
            'productname' => '商品名称',
            'productdesc' => '商品描述',
            'amount' => '交易金额：分为单位',
            'orderexpdate' => '订单有效期',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'pay_type' => '支付方式101:易宝投资通; 102:易宝一键支付',
            'status' => '0:默认; 1:未支付;  2:已支付; 11:支付失败',
            'reason' => '支付失败原因',
            'smscode' => '短信验证码',
            'client_status' => '客户端通知状态',
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
     * 是否存在订单
     */
    public function existsOrder($orderid, $aid) {
        if (!$orderid) {
            return null;
        }
        $condition = [
            'orderid' => $orderid,
            'aid' => $aid,
        ];
        return static::find()->where($condition)->count() > 0;
    }
    /**
     * 保存数据
     */
    public function saveOrder($postData) {
        //1 数据验证
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(null, "数据不能为空");
        }
        if (empty($postData['orderid'])) {
            return $this->returnError(null, "订单不能为空");
        }
        if (empty($postData['aid'])) {
            return $this->returnError(null, "应用id不能为空");
        }

        //2  保存数据
        // 转换 identityid 为传过来的加银行卡号的形式
        $client_identityid = $postData['identityid'];
        $server_identityid = $this->getPayIdentityid($client_identityid, $postData['cardno']);

        $time = time(); // 组合
        $data = [
            'aid' => $postData['aid'],
            'client_identityid' => $client_identityid,
            'identityid' => $server_identityid,
            'orderid' => $postData['orderid'],
            'payorderid' => '',
            'cardno' => $postData['cardno'],
            'idcard' => $postData['idcard'],
            'username' => $postData['username'],
            'phone' => isset($postData['phone']) ? $postData['phone'] : '',

            'productcatalog' => $postData['productcatalog'],
            'productname' => $postData['productname'],
            'productdesc' => $postData['productdesc'],
            'amount' => $postData['amount'],
            'orderexpdate' => $postData['orderexpdate'],
            'create_time' => $time,
            'modify_time' => $time,
            'pay_type' => $postData['pay_type'],
            'status' => self::STATUS_INIT,
            'callbackurl' => $postData['callbackurl'],
            'userip' => $postData['userip'],
            'client_status' => 0,
        ];

        //3  是否存在订单
        $orderM = $this->getByOrder($postData['orderid'], $postData['aid']);
        if ($orderM) {
            if ($orderM->status != 0) {
                return $this->returnError(null, "此订单已经存在");
            }

            // 检测是否与db存在的一致, 可能会多次提交
            if ($this->chkEQ($data, $orderM->attributes)) {
                return $orderM;
            } else {
                return $this->returnError(null, "订单信息不一致");
            }
        }

        //4  字段检测
        $model = new self();
        if ($errors = $model->chkAttributes($data)) {
            return $this->returnError(null, implode('|', $errors));
        }

        //5  保存数据
        $result = $model->save();
        if (!$result) {
            return $this->returnError(null, implode('|', $model->errors));
        }

        return $model;
    }
    /**
     * 可能存在两次提交订单信息，所以得检查订单信息是否一致
     */
    private function chkEQ($newData, $oldData) {
        $cmps = [
            'aid',
            'identityid',
            'orderid',
            'cardno',
            'idcard',
            'username',
            'phone',
            'productcatalog',
            'productname',
            'productdesc',
            'amount',
            'orderexpdate',
            'pay_type',
            'status',
        ];
        $result = true;
        foreach ($cmps as $k) {
            if ($oldData[$k] != $oldData[$k]) {
                $result = false;
                break;
            }
        }
        return $result;
    }
    /**
     * 易宝获取回调地址
     */
    public function getCallbackurl($pay_type) {
        switch ($pay_type) {
        case 101: // 投资通
            $callbackurl = Yii::$app->params['tztpaycallbackurl'];
            break;
        case 102: // 易宝一键支付
            $callbackurl = Yii::$app->params['callbackurl'];
            break;
        default:
            $callbackurl = '';
            break;
        }

        return $callbackurl;
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
    /**
     * 保存订单
     * @param  int $status
     * @param  string $reason
     * @param  string $payorderid 支付流水号
     * @return bool
     */
    public function saveStatus($status, $reason = '', $payorderid = '') {
        if ($reason) {
            $reason = (string) $reason;
            $reason = substr($reason, 0, 200);
            $this->reason = $reason;
        }
        if ($payorderid) {
            $this->payorderid = $payorderid;
        }

        $this->status = $status;
        $this->modify_time = time();
        $result = $this->save();
        return $result;
    }
    /**
     * 返回客户端的状态
     * @param int $status
     * @return int
     */
    public function returnClientStatus($status) {
        if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL, Payorder::STATUS_DOING])) {
            return $status;
        } else {
            return Payorder::STATUS_DOING;
        }
    }
    /**
     * 返回客户端响应结果
     * @return  []
     */
    public function clientData() {
        return [
            'pay_type' => $this->pay_type,
            'status' => $this->returnClientStatus($this->status),
            'orderid' => $this->orderid,
            'yborderid' => $this->payorderid,
            'amount' => $this->amount,
            'error_code' => '',
            'error_msg' => $this->reason,
        ];
    }
    /**
     * POST 异步通知客户端
     * @return bool
     */
    public function clientPost($callbackurl, $data, $aid) {
        //1 加密
        $res_data = App::model()->encryptData($aid, $data);
        $postData = ['res_data' => $res_data, 'res_code' => 0];

        //2 post提交
        $oCurl = new \app\common\Curl;
        $res = $oCurl->post($callbackurl, $postData);
        Logger::dayLog('payorder/clientPost', 'post', "客户响应|{$res}|", $callbackurl, $data);

        //3 解析结果
        $res = strtoupper($res);
        return $res == 'SUCCESS';
    }
    /**
     * GET 页面回调链接
     */
    public function clientGet($callbackurl, $data, $aid) {
        //1 加密
        $res_data = App::model()->encryptData($aid, $data);

        //2 组成url
        $link = strpos($callbackurl, "?") === false ? '?' : '&';
        $url = $callbackurl . $link . 'res_code=0&res_data=' . rawurlencode($res_data);
        return $url;
    }
    /**
     * POST 异步通知客户端:并仅通知最终结果, 即(成功|失败)
     * @return bool
     */
    public function clientNotify() {
        // 已经通知过了
        /*if ($this->client_status == 1) {
        return true;
        }*/
        if (!in_array($this->status, [static::STATUS_PAYOK, static::STATUS_PAYFAIL])) {
            return false;
        }

        // 更新通知状态
        $data = $this->clientData();
        $result = $this->clientPost($this->callbackurl, $data, $this->aid);
        if ($result) {
            $this->client_status = 1;
            $this->modify_time = time();
            $result = $this->save();
        } else {
            //@todo 加入通知队列中
            //
        }
        return $result;
    }
    /**
     * GET 回调通知客户端 url
     * @return url
     */
    public function clientBackurl() {
        $data = $this->clientData();
        $url =  $this->clientGet($this->callbackurl, $data, $this->aid);
        return $url;
    }
}

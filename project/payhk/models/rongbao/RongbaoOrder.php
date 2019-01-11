<?php

namespace app\models\rongbao;

use app\models\BindBank;
use app\models\Payorder;

/**
 * This is the model class for table "pay_rongbao_order".
 *
 * @property integer $id
 * @property integer $payorder_id
 * @property integer $bind_id
 * @property integer $aid
 * @property integer $channel_id
 * @property string $orderid
 * @property string $cli_orderid
 * @property string $identityid
 * @property integer $amount
 * @property string $create_time
 * @property string $modify_time
 * @property integer $status
 * @property string $other_orderid
 * @property string $error_code
 * @property string $error_msg
 * @property integer $version
 */
class RongbaoOrder extends \app\models\BasePay {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'pay_rongbao_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['payorder_id', 'aid', 'channel_id', 'orderid', 'cli_orderid', 'identityid', 'amount', 'create_time', 'modify_time', 'card_type'], 'required'],
            [['payorder_id', 'aid', 'channel_id', 'amount', 'status', 'version', 'card_type','bind_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['orderid'], 'string', 'max' => 25],
            [['cli_orderid'], 'string', 'max' => 30],
            [['identityid', 'name', 'error_code', 'idcard', 'userip', 'phone'], 'string', 'max' => 20],
            [['other_orderid', 'cardno', 'bankname', 'productname'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['error_msg', 'productdesc'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'            => 'ID',
            'payorder_id'   => 'Payorder ID',
            'bind_id'       => 'Bind ID',
            'aid'           => 'Aid',
            'channel_id'    => 'Channel ID',
            'orderid'       => 'Orderid',
            'cli_orderid'   => 'Cli Orderid',
            'identityid'    => 'Identityid',
            'amount'        => 'Amount',
            'create_time'   => 'Create Time',
            'modify_time'   => 'Modify Time',
            'status'        => 'Status',
            'other_orderid' => 'Other Orderid',
            'error_code'    => 'Error Code',
            'error_msg'     => 'Error Msg',
            'version'       => 'Version',
            'name'          => 'name',
            'idcard'        => 'idcard',
            'phone'         => 'phone',
            'cardno'        => 'cardno',
            'bankname'      => 'bankname',
            'userip'        => 'userip',
            'card_type'     => 'card_type',
            'productdesc'   => 'productdesc',
            'productname'   => 'productname',
        ];
    }

    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }

    public function getBindbank() {
        return $this->hasOne(BindBank::className(), ['id' => 'bind_id']);
    }

    public function getRongbindbank() {
        return $this->hasOne(RongbaoBindbank::className(), ['id' => 'bind_id']);
    }

    private function getAOrderId($orderid, $channel_id) {
        return "{$channel_id}_{$orderid}";
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
        if (empty($postData['channel_id'])) {
            return $this->returnError(null, "通道channel_id不能为空");
        }
        //2  保存数据
        $time        = date("Y-m-d H:i:s");
        //这个订单长度可能不够. 若不行使用保存的id号, 形如: 4I123
        $cli_orderid = $this->getAOrderId($postData['orderid'], $postData['channel_id']);
        $data        = [
            'payorder_id'   => $postData['payorder_id'],
            'aid'           => $postData['aid'],
            'channel_id'    => $postData['channel_id'],
            'orderid'       => $postData['orderid'],
            'cli_orderid'   => $cli_orderid,
            'identityid'    => $postData['identityid'],
            'amount'        => $postData['amount'],
            'name'          => $postData['name'],
            'idcard'        => $postData['idcard'],
            'phone'         => $postData['phone'],
            'userip'        => $postData['userip'],
            'bankname'      => $postData['bankname'],
            'cardno'        => $postData['cardno'],
            'card_type'     => $postData['card_type'],
            'productname'   => $postData['productname'],
            'productdesc'   => $postData['productdesc'],
            'bind_id'       => $postData['bind_id'],
            'create_time'   => $time,
            'modify_time'   => $time,
            'status'        => $postData['status'],
            'other_orderid' => '',
            'error_code'    => '',
            'error_msg'     => '',
            'version'       => 0,
        ];

        //4  字段检测
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(null, implode('|', $errors));
        }

        //5  保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, implode('|', $this->errors));
        }
        return true;
    }

    public function getByRongId($id) {
        $id = intval($id);
        if (($id > 0) === false) {
            return null;
        }

        //2. 获取订单数据
        return static::findOne($id);
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 返回链接地址数组
     * @return []
     */
    public function getPayUrls($pay_controller = 'rongpay', $pay_type = '') {
        return parent::getPayUrls($pay_controller, $pay_type);
    }
    
        /**
     * 保存订单
     * @param  int $status
     * @param  str $other_orderid
     * @return bool
     */
    public function saveStatus($status, $other_orderid) {
        if ($other_orderid) {
            $this->other_orderid = (string) $other_orderid;
        }

        $status = intval($status);
        $this->status = $status;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

}

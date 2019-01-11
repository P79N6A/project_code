<?php

namespace app\models\rongbao;

use Yii;
use app\models\Payorder;
use app\common\Func;

/**
 * This is the model class for table "rb_withhold_order".
 *
 * @property integer $id
 * @property integer $payorder_id
 * @property integer $bind_id
 * @property integer $aid
 * @property integer $channel_id
 * @property string $orderid
 * @property string $cli_orderid
 * @property integer $amount
 * @property string $productname
 * @property string $productdesc
 * @property string $identityid
 * @property string $cli_identityid
 * @property string $cardno
 * @property integer $orderexpdate
 * @property string $userip
 * @property string $create_time
 * @property string $modify_time
 * @property integer $status
 * @property string $other_orderid
 * @property string $error_code
 * @property string $error_msg
 * @property integer $version
 */
class RbWithholdOrder extends \app\models\BasePay
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rb_withhold_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payorder_id', 'bind_id', 'aid', 'channel_id', 'orderid', 'cli_orderid', 'amount', 'productname', 'productdesc', 'identityid', 'cli_identityid', 'cardno', 'userip', 'create_time', 'modify_time', 'version'], 'required'],
            [['payorder_id', 'bind_id', 'aid', 'channel_id', 'amount', 'orderexpdate', 'status', 'version'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['orderid', 'userip'], 'string', 'max' => 30],
            [['cli_orderid', 'productname', 'cli_identityid', 'cardno', 'other_orderid', 'error_code', 'error_msg'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['identityid'], 'string', 'max' => 20],
            [['orderid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payorder_id' => 'Payorder ID',
            'bind_id' => 'Bind ID',
            'aid' => 'Aid',
            'channel_id' => 'Channel ID',
            'orderid' => 'Orderid',
            'cli_orderid' => 'Cli Orderid',
            'amount' => 'Amount',
            'productname' => 'Productname',
            'productdesc' => 'Productdesc',
            'identityid' => 'Identityid',
            'cli_identityid' => 'Cli Identityid',
            'cardno' => 'Cardno',
            'orderexpdate' => 'Orderexpdate',
            'userip' => 'Userip',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'status' => 'Status',
            'other_orderid' => 'Other Orderid',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'version' => 'Version',
        ];
    }
    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }

    public function saveOrder($postData) {
        //1 字段验证
        if (empty($postData)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $cli_orderid = Func::toYeepayCode($postData['orderid'], $postData['channel_id']);
        $data = [
            'payorder_id' =>$postData['payorder_id'],
            'aid' =>$postData['aid'],
            'bind_id'=>$postData['bind_id'],
            'channel_id' =>$postData['channel_id'],
            'orderid' =>$postData['orderid'],
            'cli_orderid' => $cli_orderid,
            'amount' => intval($postData['amount']),
            'productname' =>$postData['productname'],
            'productdesc' =>$postData['productdesc'],
            'identityid' =>  $postData['identityid'],
            'cli_identityid' =>  $postData['cli_identityid'],
            'cardno' =>  $postData['cardno'],
            'orderexpdate' => intval($postData['orderexpdate']),
            'userip' => $postData['userip'],
            'create_time' => $time,
            'modify_time' => $time,
            'status' => $postData['status'],
            'version' => 0,
        ];
        //2  字段检测
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(false, implode('|', $errors));
        }
        //3  保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(false, implode('|', $this->errors));
        }
        return true;
    }

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
    
    public function getByCliOrderId($cliOrderId){
        if (!$cliOrderId) {
            return null;
        }
        return static::find()->where(['cli_orderid' => $cliOrderId])->limit(1)->one();
    }
}

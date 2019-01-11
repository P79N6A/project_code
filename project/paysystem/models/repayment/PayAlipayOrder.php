<?php

namespace app\models\repayment;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pay_alipay_order".
 *
 * @property integer $id
 * @property integer $payorder_id
 * @property integer $aid
 * @property integer $channel_id
 * @property integer $amount
 * @property integer $status
 * @property string $other_orderid
 * @property string $error_code
 * @property string $error_msg
 * @property string $create_time
 * @property string $modify_time
 * @property integer $version
 */
class PayAlipayOrder extends \app\models\BaseModel
{
    const STATUS_INIT = 0; //默认
    const STATUS_HANDLE = 4; //处理中
    const STATUS_SUCCESS = 2;
    const STATUS_FIAL = 11;

    const TYPE_ONE = 1; //1支付宝H5
    const TYPE_TWO = 2; //2支付宝扫码
    const TYPE_THREE = 3;  //3微信扫码
    const TYPE_FOUR = 4;  //4快捷支付
    const TYPE_FIVE = 5; //微信支付
    const TYPE_SIX = 6; //支付宝
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_alipay_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payorder_id', 'cli_orderid','amount', 'create_time', 'modify_time', 'version'], 'required'],
            [['aid', 'channel_id', 'status', 'version', 'type'], 'integer'],
            [['amount'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['error_msg'], 'string', 'max' => 255],
            [['payorder_id', 'other_orderid', 'cli_orderid'], 'string', 'max' => 50],
            [['error_code'], 'string', 'max' => 20]
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
            'aid' => 'Aid',
            'channel_id' => 'Channel ID',
            'amount' => 'Amount',
            //'callbackurl' => 'Callbackurl',
            'status' => 'Status',
            'other_orderid' => 'Other Orderid',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'version' => 'Version',
            'type'      => 'Type',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 保存数据
     * @param $data_set
     * @return bool|false|null
     */
    public function saveOrder($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $save_data = [
            'cli_orderid'         => (string)ArrayHelper::getValue($data_set, 'cli_orderid', 0),
            'payorder_id'		=> (string)ArrayHelper::getValue($data_set, 'payorder_id', 0), //商户订单号',
            'aid'		        => ArrayHelper::getValue($data_set, 'aid', 1), //应用id',
            'channel_id'		=> ArrayHelper::getValue($data_set, 'channel_id', 0), //通道id',
            'amount'		    => (float)ArrayHelper::getValue($data_set, 'amount', 0), //交易金额(单位：分)',
            //'callbackurl'		=> ArrayHelper::getValue($data_set, 'callbackurl', ''), //异步通知回调url',
            'status'		    => ArrayHelper::getValue($data_set, 'status', 0), //0:默认;2:成功;4处理中;11:失败',
            'other_orderid'		=> ArrayHelper::getValue($data_set, 'other_orderid', ''), //第三方交易号',
            'error_code'		=> ArrayHelper::getValue($data_set, 'error_code', ''), //返回错误码',
            'error_msg'		    => ArrayHelper::getValue($data_set, 'error_msg', ''), //返回错误描述',
            'type'              => ArrayHelper::getValue($data_set, 'type', ''),
            'create_time'		=> date("Y-m-d H:i:s", time()), //创建时间',
            'modify_time'		=> date("Y-m-d H:i:s", time()), //最后修改时间',
            'version'		    => 1, //版本号',
        ];
        $error = $this->chkAttributes($save_data);
        if ($error) {
            return $this->returnError(false, current($error));
        }
        $res = $this->save();
        return $res;

    }

    /**
     * 查找订单
     * @param $payorder_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getOrder($payorder_id)
    {
        if (empty($payorder_id)){
            return false;
        }
        return self::find()->where(['cli_orderid'=>$payorder_id])->one();
    }

    /**
     * 修改数据
     * @param $data_set
     * @return bool
     */
    public function updateOrder($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $this->modify_time = date("Y-m-d H:i:s", time());
        foreach ($data_set as $key => $value){
            $this->$key = $value;
        }
        return $this->save();
    }


    //获取数据
    public function getOrderData($start_time, $end_time, $limit = 100)
    {
        if (empty($start_time) || empty($end_time)){
            return false;
        }
        $where_config = [
            'and',
            ['=', 'status', self::STATUS_INIT],
            ['in', 'channel_id', [139,140,141,142,161,162,166,170]],
            ['>=', 'create_time', $start_time],
            ['<=', 'create_time', $end_time]

        ];
        return self::find()->where($where_config)->limit($limit)->all();
    }
    //获取通道所有id
    public function getCjOrder($start_time, $end_time, $limit = 100){
        if (empty($start_time) || empty($end_time)){
            return false;
        }
        $where = [
            'and',
            ['in', 'status', [self::STATUS_INIT,self::STATUS_HANDLE]],
            ['in', 'channel_id', [162,166]],
            ['>=', 'create_time', $start_time],
            ['<=', 'create_time', $end_time]
        ];
        return self::find()->where($where)->limit($limit)->all();

    }

    //获取数据
    public function getOrderAliData($start_time, $end_time, $limit = 100)
    {
        if (empty($start_time) || empty($end_time)){
            return false;
        }
        $where_config = [
            'and',
            ['=', 'status', self::STATUS_INIT],
            ['in', 'channel_id', [151, 153, 154, 156]],
            ['>=', 'create_time', $start_time],
            ['<=', 'create_time', $end_time]

        ];
        return self::find()->where($where_config)->limit($limit)->all();
    }

    //获取数据
    public function getOrderXyAliData($start_time, $end_time, $limit = 100)
    {
        if (empty($start_time) || empty($end_time)){
            return false;
        }
        $where_config = [
            'and',
            ['=', 'status', self::STATUS_INIT],
            ['=', 'channel_id', 153],
            ['>=', 'create_time', $start_time],
            ['<=', 'create_time', $end_time]

        ];
        return self::find()->where($where_config)->limit($limit)->all();
    }

    //获取数据
    public function getOrderWxData($start_time, $end_time, $limit = 100)
    {
        if (empty($start_time) || empty($end_time)){
            return false;
        }
        $where_config = [
            'and',
            ['=', 'status', self::STATUS_INIT],
            ['in', 'channel_id', [149,155]],
            ['>=', 'create_time', $start_time],
            ['<=', 'create_time', $end_time]

        ];
        return self::find()->where($where_config)->limit($limit)->all();
    }

    public function lockStatus($ids)
    {
        if (empty($ids)){
            return 0;
        }
        return static::updateAll(['status' => static::STATUS_HANDLE], ['id' => $ids]);
    }
}
<?php

namespace app\models\bangbf;
use app\models\Payorder;
use Yii;

/**
 * 邦宝付快捷支付
 *
 */
class BangbfOrder extends \app\models\BasePay {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%bangbf_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['payorder_id', 'aid', 'channel_id', 'orderid', 'cli_orderid', 'amount', 'create_time', 'modify_time'], 'required'],
            [['aid', 'amount', 'status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['orderid'], 'string', 'max' => 25],
            [['cli_orderid'], 'string', 'max' => 30],
            [['error_msg'], 'string', 'max' => 255],
            [['other_orderid'], 'string', 'max' => 50],
            [['error_code'], 'string', 'max' => 20],
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
            'channel_id' => '通道',
            'orderid' => '客户订单号',
            'cli_orderid' => '唯一订单号',
            'amount' => '交易金额(单位：分)',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'status' => '(内部)0:默认;2:成功;4处理中;11:失败',
            'other_orderid' => '(内部)第三方流水号',
            'error_code' => '(内部)返回码',
            'error_msg' => '(内部)返回描述',
            'version' => '乐观锁版本号'
        ];
    }
    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }
    /**
     * 获取cliOrderId
     * @param  str $orderid
     * @param  int $channel_id
     * @return   str
     */
    public function getAOrderId($orderid, $channel_id) {
        return "{$channel_id}_{$orderid}";
    }

    /**
     * 根据商户唯一订单号查询d
     * @param  str $cli_orderid
     * @return bool
     */
    public function getByCliOrderId($cliOrderId){
        if (!$cliOrderId) {
            return null;
        }
        return static::find()->where(['cli_orderid' => $cliOrderId])->limit(1)->one();
    }
    /**
     * 保存数据
     */
    public function saveOrder($oPayorder) {
        //1 数据验证
        if (!is_object($oPayorder) || empty(get_object_vars($oPayorder))) {
            return $this->returnError(null, "数据不能为空");
        }
        if (empty($oPayorder->orderid)) {
            return $this->returnError(null, "订单不能为空");
        }
        if (empty($oPayorder->channel_id)) {
            return $this->returnError(null, "通道channel_id不能为空");
        }
        $cli_orderid = $this->getAOrderId($oPayorder->orderid,$oPayorder->channel_id);

        //2  保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'payorder_id' => $oPayorder->id,
            'aid' => $oPayorder->aid,
            'channel_id' => $oPayorder->channel_id,
            'orderid' => $oPayorder->orderid,
            'cli_orderid' => $cli_orderid,
            'amount' => $oPayorder->amount,
            'create_time' => $time,
            'modify_time' => $time,
            'status' => $oPayorder->status,
            'other_orderid' => '',
            'error_code' => '',
            'error_msg' => '',
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
        return $result;
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

    /**
     * @des 主键找到对应订单对象
     * @param  int $id
     * @return obj
     */
    public function getByBaofooId($id) {
        $id = intval($id);
        if (($id > 0) === false) {
            return null;
        }
        //2. 获取订单数据
        return static::findOne($id);
    }


    public function optimisticLock() {
        return null;
    }

    /**
     * 更新订单响应错误结果
     * @param str  $res_code  响应码
     * @param str  $res_msg  响应原因
     * @return bool
     */
    public function saveResponse($res_code, $res_msg) {
        $this->error_code = $res_code;
        $this->error_msg = $res_msg;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 更新订单的宝付交易号
     * @param str  $other_orderid  
     * @return bool
     */
    public function saveOtherOrderid($other_orderid) {
        $this->other_orderid = $other_orderid;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 返回链接地址数组
     * @return []
     */
    public function getPayUrls($pay_controller='bangbf',$pay_type='') {
        return parent::getPayUrls($pay_controller,$this->channel_id);
    }
    /**
     * 找到指定时间段内状态处理中的订单
     * @return []
     */
    public function getAbnorList($start_time,$end_time){
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
		$where = ['AND',
			['status' => [Payorder::STATUS_DOING]],
			['>=', 'create_time', $start_time], //@todo 暂不限制开始
			['<', 'create_time', $end_time],
		];
		$dataList = self::find()->where($where)->all();
		if (!$dataList) {
			return null;
		}
		return $dataList;
    }
}
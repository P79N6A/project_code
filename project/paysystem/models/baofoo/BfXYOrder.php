<?php
/**
 * 宝付协议支付子订单
 */
namespace app\models\baofoo;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\Payorder;

class BfXYOrder extends \app\models\BasePay {
    public static function tableName() {
        return 'pay_bfxy_order';
    }

    public function rules() {
        return [
            [['payorder_id', 'aid', 'bind_id', 'channel_id', 'orderid', 'cli_orderid', 'amount', 'identityid', 'cli_identityid', 'cardno', 'create_time', 'modify_time',], 'required'],
            [['payorder_id', 'aid', 'channel_id', 'amount', 'orderexpdate', 'status','version',], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['orderid', 'userip'], 'string', 'max' => 30],
            [['cli_orderid', 'productname', 'cli_identityid', 'cardno', 'other_orderid','error_code', 'error_msg'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['identityid'], 'string', 'max' => 20],
            [['orderid'], 'unique']
        ];
    }

    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'channel_id' => '通道',
            'payorder_id' => '主订单id',
            'bind_id' => '签约表Id',
            'orderid' => '客户订单号',
            'cli_orderid' => '宝付唯一订单号',
            'identityid' => '商户生成的用户唯一',
            'cli_identityid' => '商户生成的该渠道下的用户唯一',
            'cardno' => '银行卡号',
            'amount' => '交易金额(单位：分)',
            'productname' => '产品名称',
            'productdesc' => '产品描述',
            'orderexpdate' => '订单有效期(单位:分钟)',
            'userip' => '用户访问IP',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'status' => '(内部)子订单状态,复用主订单状态码',
            'other_orderid' => '(内部)第三方流水号',
            'error_code' => '(内部)返回码',
            'error_msg' => '(内部)返回描述',
            'version' => '乐观锁版本号'
        ];
    }

    public function optimisticLock() {
        return null;
    }

    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }

    public function getOne($data,$column='id'){
        if(!$data){
            return false;
        }
        $result = self::find()->where([$column=>$data])->one();
        return $result;
    }

    /**
     * 保存子订单信息
     * @param array $payOrderInfo 主订单订单信息
     * @return boolean 保存是否成功
     */
    public function addBfxyOrder($payOrderInfo) {
        // 1 校验数据
        if (empty($payOrderInfo)) {
            return $this->returnError(false, "数据不能为空");
        }
        $orderId = ArrayHelper::getValue($payOrderInfo, 'orderid','');
        if (!$orderId) {
            return $this->returnError(false, "订单不能为空");
        }
        $channelId = ArrayHelper::getValue($payOrderInfo, 'channel_id','');
        if (!$channelId) {
            return $this->returnError(false, "通道channel_id不能为空");
        }
        // 2 保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'aid' => ArrayHelper::getValue($payOrderInfo, 'aid',''),
            'channel_id' => $channelId,
            'payorder_id' => ArrayHelper::getValue($payOrderInfo, 'id',''),
            'bind_id' => ArrayHelper::getValue($payOrderInfo, 'bind_id',''),
            'orderid' => $orderId,
            'cli_orderid' => $channelId.'_'.$orderId,
            'identityid' => ArrayHelper::getValue($payOrderInfo, 'identityid',''),
            'cli_identityid' => ArrayHelper::getValue($payOrderInfo, 'cli_identityid',''),
            'cardno' =>  ArrayHelper::getValue($payOrderInfo, 'cardno',''),
            'amount' => ArrayHelper::getValue($payOrderInfo, 'amount',0),
            'productname' => ArrayHelper::getValue($payOrderInfo, 'productname',''),
            'productdesc' => ArrayHelper::getValue($payOrderInfo, 'productdesc',''),
            'orderexpdate' => intval(ArrayHelper::getValue($payOrderInfo, 'orderexpdate',0)),
            'userip' => ArrayHelper::getValue($payOrderInfo, 'userip',''),
            'create_time' => $time,
            'modify_time' => $time,
            'status' => ArrayHelper::getValue($payOrderInfo, 'status',0),
            'other_orderid' => '',
            'error_code' => '',
            'error_msg' => '',
        ];
        // 3 字段检测
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(false, implode('|', $errors));
        }
        // 4 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(false, implode('|', $this->errors));
        }
        return $result;
    }

    // 返回链接地址数组
    public function getPayUrls($pay_controller='bfxy',$pay_type='') {
        return parent::getPayUrls($pay_controller,$this->channel_id);
    }

    // 保存订单状态
    public function saveStatus($status) {
        if(!array_key_exists($status, Payorder::getStatus())){
            return false;
        }
        $this->status = intval($status);
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

	// 保存订单状态
    public function saveBindId($bindId) {
        $this->bind_id = intval($bindId);
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 获取找到指定时间段内状态处理中的订单
     */
    public function getProcessList($start_time,$end_time,$limit=100){
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
		$where = ['AND',
			['in','status',[Payorder::STATUS_DOING]],
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time],
		];
		$dataList = self::find()->where($where)->limit($limit)->all();
		if (!$dataList) {
			return [];
		}
		return $dataList;
    }
}
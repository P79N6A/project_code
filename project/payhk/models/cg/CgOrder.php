<?php
namespace app\models\cg;

use app\models\Payorder;
use app\common\Func;
use yii\helpers\ArrayHelper;
/**
 * 存管支付订单
 */
class CgOrder extends \app\models\BasePay {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'pay_cg_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['payorder_id', 'aid', 'loan_id', 'channel_id', 'orderid', 'cli_orderid', 'amount', 'identityid', 'cardno', 'create_time', 'modify_time',], 'required'],
            [['payorder_id', 'aid', 'loan_id', 'channel_id', 'amount', 'orderexpdate', 'status','version',], 'integer'],
            [['create_time', 'modify_time','interest_fee','coupon_repay_amount'], 'safe'],
            [['orderid', 'userip'], 'string', 'max' => 30],
            [['cli_orderid', 'productname', 'cardno', 'account_id','error_code','other_orderid'], 'string', 'max' => 50],
            [['productdesc', 'error_msg'], 'string', 'max' => 200],
            [['identityid','smsseq'], 'string', 'max' => 20],
            [['orderid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'payorder_id' => '主订单id',
            'loan_id' => '借款ID',
            'aid' => '应用id',
            'channel_id' => '支付通道',
            'orderid' => '客户订单号',
            'cli_orderid' => '发送存管的orderid',
            'other_orderid' => '流水号',
            'account_id' => '电子账号',
            'smsseq' => '短信序号',
            'amount' => '交易金额',
            'productname' => '商品名称',
            'productdesc' => '商品描述',
            'identityid' => '用户标识',
            'cardno' => '银行卡号',
            'orderexpdate' => '订单有效期时间 以分为单位',
            'userip' => '用户ip',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'status' => '(内部)0:默认; 1:请求成功; 2:成功; 3:未处理; 4:处理中; 5:已撤消; 11:支付失败;  12:请求失败;',
            'error_code' => '(内部)宝付返回错误码',
            'error_msg' => '(内部)宝付返回错误描述',
        ];
    }
    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }

    public function optimisticLock() {
        return "version";
    }
    /**
     * 接口返回状态码
     * 主动查询状态 0:失败; 1:成功; 2:未处理; 3:处理中; 4:已撤消;
     * 对应本表状态：基本上是查询接口返回状态码+1
     * 0..     1:请求成功; 2:成功; 3:未处理; 4:处理中; 5:已撤消;   12:请求失败; 11:支付失败
     */
    public function syncStatus($status) {
        $map = [
            0 => Payorder::STATUS_PAYFAIL, // 支付失败
            1 => Payorder::STATUS_PAYOK, // 支付成功
            2 => Payorder::STATUS_PREDO, // 未处理
            3 => Payorder::STATUS_DOING, // 处理中
            4 => Payorder::STATUS_CANCEL, // 已撤消
        ];
        $payStatus = isset($map[$status]) ? $map[$status] : Payorder::STATUS_DOING;
        return $payStatus;
    }
        /**
     * 保存数据
     */
    public function saveOrder($postData) {
        //1 字段验证
        if (empty($postData)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $cli_orderid = Func::toYeepayCode($postData['orderid'], $postData['channel_id']);
        $data = [
            'payorder_id' =>$postData['payorder_id'],
            'loan_id' =>$postData['loan_id'],
            'aid' =>$postData['aid'],
            'channel_id' =>$postData['channel_id'],
            'account_id' =>$postData['account_id'],
            'smsseq' => '',
            'other_orderid' => '',
            'orderid' =>$postData['orderid'],
            'cli_orderid' => $cli_orderid,
            'amount' => intval($postData['amount']),
            'interest_fee' => ArrayHelper::getValue($postData,'interest_fee','0'),
            'coupon_repay_amount' => ArrayHelper::getValue($postData,'coupon_repay_amount','0'),
            'productname' =>$postData['productname'],
            'productdesc' =>$postData['productdesc'],
            'identityid' =>  $postData['identityid'],
            'cardno' =>  $postData['cardno'],
            'orderexpdate' => intval($postData['orderexpdate']),
            'userip' => $postData['userip'],
            'create_time' => $time,
            'modify_time' => $time,
            'status' => $postData['status'],
            'error_code' => '',
            'error_msg' => '',
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
    public function getByCgId($id) {
        $id = intval($id);
        if (($id > 0) === false) {
            return null;
        }
        //2. 获取订单数据
        return static::findOne($id);
    }

    /**
     * 返回链接地址数组
     * @return []
     */
    public function getPayUrls($pay_controller='cg',$pay_type='') {
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
            // ['>=', 'create_time', $start_time], //@todo 暂不限制开始
            ['>=', 'create_time', '2017-06-11 00:00:00'],//分账之后数据
			['<', 'create_time', $end_time],
		];
		$dataList = self::find()->where($where)->all();
		if (!$dataList) {
			return [];
		}
		return $dataList;
    }
    /**
     * 找到指定时间段内状态处理中的订单
     * @return []
     */
    public function getProcessList($start_time,$end_time,$limit=100){
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
		$where = ['AND',
			['in','status',[Payorder::STATUS_PREDO,Payorder::STATUS_BIND]],
			['>=', 'create_time', '2017-06-11 00:00:00'], //分账开始之后
			['<', 'create_time', $end_time],
		];
		$dataList = self::find()->where($where)->limit($limit)->all();
		if (!$dataList) {
			return [];
		}
		return $dataList;
    }

    public function getOrderInfo($orderid){
        if (!$orderid) {
            return null;
        }
        return static::find()->where(['orderid' => $orderid])->limit(1)->one();
    }
}

<?php

namespace app\models\yjf;

use app\common\Logger;
use app\models\Payorder;
use Yii;

/**
 * This is the model class for table "pay_yjf_order".
 *
 */
class YjfOrder extends \app\models\BasePay {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{pay_yjf_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['bind_id', 'payorder_id', 'aid', 'channel_id', 'orderid', 'cli_orderid', 'identityid', 'amount', 'create_time', 'modify_time', 'repayment_no'], 'required'],
            [['bind_id', 'aid', 'amount', 'status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['orderid'], 'string', 'max' => 25],
            [['cli_orderid'], 'string', 'max' => 30],
            [['error_msg'], 'string', 'max' => 255],
            [['other_orderid', 'repayment_no'], 'string', 'max' => 50],
            [['error_code', 'identityid'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'payorder_id' => '主订单id',
            'bind_id' => '绑卡id',
            'aid' => '应用id',
            'channel_id' => '通道',
            'orderid' => '客户订单号',
            'cli_orderid' => '唯一订单号',
            'identityid' => '用户',
            'amount' => '交易金额(单位：分)',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'status' => '(内部)0:默认;2成功;5:已撤消; 6:未支付;11失败; 13:风控',
            'other_orderid' => '(内部)畅捷流水号',
            'repayment_no' => '连连订单号',
            'error_code' => '(内部)畅捷返回错误码',
            'error_msg' => '(内部)畅捷返回错误描述',
            'version' => '乐观锁版本号'
        ];
    }
    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }
    /**
     * 获取编号
     * @param  str $orderid
     * @param  int $channel_id
     * @return   str
     */
    private function getRepaymentNo($orderid, $channel_id) {
        return "R{$channel_id}O{$orderid}";
    }
    private function getAOrderId($orderid, $channel_id) {
        return "{$channel_id}_{$orderid}";
    }
    public function getIdentityid($identityid, $channel_id) {
        return "I{$channel_id}U{$identityid}";
    }
    public function getById($id) {
        $id = intval($id);
        if (($id > 0) === false) {
            return null;
        }

        //2. 获取订单数据
        return static::findOne($id);
    }
    /**
     * 根据商户唯一订单号查询d
     * @param  str $cli_orderid
     * @return bool
     */
    public function getByRepaymentNo($repayment_no) {
        if (!$repayment_no) {
            return null;
        }
        return static::find()->where(['repayment_no' => $repayment_no])->limit(1)->one();
    }

    public function getByCliOrderId($cliOrderId){
        if (!$cliOrderId) {
            return null;
        }
        return static::find()->where(['cli_orderid' => $cliOrderId])->limit(1)->one();
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

        $repayment_no = $this->getRepaymentNo($postData['orderid'], $postData['channel_id']);

        //这个订单长度可能不够. 若不行使用保存的id号, 形如: 4I123
        $cli_orderid = $this->getAOrderId($postData['orderid'], $postData['channel_id']);

        //2  保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'payorder_id' => $postData['payorder_id'],
            'aid' => $postData['aid'],
            'channel_id' => $postData['channel_id'],
            'bind_id' => $postData['bind_id'],
            'orderid' => $postData['orderid'],
            'cli_orderid' => $cli_orderid,
            'identityid' => $postData['identityid'],
            'amount' => $postData['amount'],
            'create_time' => $time,
            'modify_time' => $time,
            'status' => $postData['status'],
            'other_orderid' => '',
            'repayment_no' => $repayment_no,
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

    public function optimisticLock() {
        return "version";
    }
    /**
     * 返回链接地址数组
     */
    public function getPayUrls($pay_controller="yjf", $pay_type='') {
        return parent::getPayUrls($pay_controller, Payorder::PAY_YJF);
    }

    /**
     * 找到指定时间段内状态处理中的订单
     */
    public function getAbnorList($start_time,$end_time){
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
		$where = ['AND',
			['status' => [Payorder::STATUS_DOING]],
			['>=', 'create_time', $start_time], 
			['<', 'create_time', $end_time],
		];
		$dataList = self::find()->where($where)->all();
		if (!$dataList) {
			return null;
		}
		return $dataList;
    }
    /**
     * 由未绑定更新为绑定状态
     * @return bool
     */
    public function savePayBind() {
        //1 更新为绑定状态
        if ($this->status != Payorder::STATUS_NOBIND) {
            return false;
        }
        $result = $this->saveStatus(Payorder::STATUS_BIND, '');
        if (!$result) {
            return false;
        }

        //2. 同步主订单状态
        $result = $this->upPayorderStatus();
        if (!$result) {
            return false;
        }
        return true;
    }
}
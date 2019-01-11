<?php
namespace app\models\jd;

use app\models\Payorder;
use app\common\Func;
use yii\helpers\ArrayHelper;
/**
 * 京东快捷支付订单
 */
class JdOrder extends \app\models\BasePay {

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'pay_jd_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['payorder_id', 'aid',  'channel_id', 'orderid', 'cli_orderid', 'amount', 'identityid', 'cardno', 'create_time', 'modify_time','card_bank_code','phone','idcard',], 'required'],
            [['payorder_id', 'aid',  'channel_id', 'amount',  'status','version','productcatalog','orderexpdate','card_type'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['coupon_repay_amount', 'interest_fee'], 'number'],
            [['orderid', 'userip'], 'string', 'max' => 30],
            [['cli_orderid', 'productname', 'cardno', 'bankname','error_code','other_orderid'], 'string', 'max' => 50],
            [['productdesc', 'error_msg','callbackurl'], 'string', 'max' => 255],
            [['identityid','idcard','phone','name','account_id','loan_id','smscode'], 'string', 'max' => 20],
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
            'amount' => '交易金额',
            'productname' => '商品名称',
            'productdesc' => '商品描述',
            'identityid' => '用户标识',
            'other_orderid' => '京东流水号',
            'error_code' => '(内部)京东回错误码',
            'error_msg' => '(内部)京东返回错误描述',
            'cardno' => '银行卡号',
            'card_bank_code' => '银行编码',
            'bankname' => '银行名称',
            'phone' => '手机号',
            'idcard' => '证件号',
            'card_idtype' => '证件类型 默认为1',
            'card_type' => '卡类型',
            'userip' => '用户ip',
            'callbackurl'=>'回调地址',
            'orderexpdate' => '订单有效期时间 以分为单位',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'status' => '(内部)0:默认; 1:请求成功; 2:成功; 3:未处理; 4:处理中; 5:已撤消; 11:支付失败;  12:请求失败;',
        ];
    }
    public function getPayorder() {
        return $this->hasOne(Payorder::className(), ['id' => 'payorder_id']);
    }

    public function optimisticLock() {
        return "version";
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
            'smscode' => '',
            'other_orderid' => '',
            'orderid' =>$postData['orderid'],
            'cli_orderid' => $cli_orderid,
            'amount' => intval($postData['amount']),
            'interest_fee' => ArrayHelper::getValue($postData,'interest_fee','0'),
            'coupon_repay_amount' => ArrayHelper::getValue($postData,'coupon_repay_amount','0'),
            'productcatalog' =>$postData['productcatalog'],
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
            'callbackurl' => $postData['callbackurl'],

            'bankname' =>  $postData['bankname'],
            'card_type' =>  $postData['card_type'],
            'card_idtype' => 1,
            'idcard' =>  $postData['idcard'],
            'name' =>  $postData['name'],
            'phone' =>  $postData['phone'],
            'card_bank_code' =>  $postData['card_bank_code'],
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
     * 返回链接地址数组
     * @return []
     */
    public function getPayUrls($pay_controller='jd',$pay_type='') {
        return parent::getPayUrls($pay_controller,$this->channel_id);
    }


    /**
     * @des 主键找到对应订单对象
     * @param  int $id
     * @return obj
     */
    public function getByJdId($id) {
        $id = intval($id);
        if (($id > 0) === false) {
            return null;
        }
        //2. 获取订单数据
        return static::findOne($id);
    }


    /**
     * @des 订单号找到对应订单对象
     * @param  int $id
     * @return obj
     */
    public function getByJdOrderId($orderid) {
        if(empty($orderid)){
            return  false;
        }
        $where = [
            'orderid'=>$orderid,
        ];
        //2. 获取订单数据
        return self::find()->where($where)->one();
    }


    /**
     * 保存订单状态
     * @param $status   状态
     * @param string $other_orderid 订单id
     * @param string $sign_no   签约号
     * @param string $res   错误信息
     * @return bool
     */
    public function saveStatus($status, $other_orderid='',$sign_no='',$res='') {
        if (!empty($other_orderid)) {
            $this->other_orderid = (string) $other_orderid;
        }
        if (!empty($sign_no)) {
            $this->sign_no = (string) $sign_no;
        }
        if(!empty(ArrayHelper::getValue($res,'1'))){
            $this->error_code = (string) ArrayHelper::getValue($res,'0','-1');
            $this->error_msg = (string) ArrayHelper::getValue($res,'1','未知错误');
        }
        $status = intval($status);
        $this->status = $status;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }



    /**
     * 找到指定时间段内状态处理中的订单
     * @return []
     */
    public function getAbnorList($start_time,$end_time,$page=200){
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $where = ['AND',
            ['in','status', [Payorder::STATUS_DOING,Payorder::STATUS_BIND]],
            ['>=', 'create_time', $start_time],
            ['<', 'create_time', $end_time],
        ];
        $dataList = self::find()->where($where)->limit($page)->all();
        if (!$dataList) {
            return [];
        }
        return $dataList;
    }



}

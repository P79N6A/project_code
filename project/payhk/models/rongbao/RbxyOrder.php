<?php

namespace app\models\rongbao;

use app\models\Payorder;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pay_rbxy_order".
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
class RbxyOrder extends \app\models\BasePay {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'pay_rbxy_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['payorder_id', 'aid', 'channel_id', 'orderid', 'cli_orderid', 'identityid', 'amount', 'create_time', 'modify_time', 'card_type'], 'required'],
            [['payorder_id', 'aid', 'channel_id', 'amount', 'status', 'version', 'card_type','productcatalog'], 'integer'],
            [['coupon_repay_amount','interest_fee'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['smscode'], 'string', 'max' => 8],
            [['cli_orderid','orderid','account_id','loan_id'], 'string', 'max' => 30],
            [[ 'name',   'phone','idcard'], 'string', 'max' => 20],
            [['other_orderid', 'cardno', 'bankname', 'productname','cli_orderid','identityid','error_code','productname','userip'], 'string', 'max' => 50],
            [['sign_no'], 'string', 'max' => 100],
            [['error_msg', 'productdesc','callbackurl'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'            => 'ID',
            'payorder_id'   => 'Payorder ID',
            'sign_no'       => '签约协议号 ',
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


    /**
     * 保存数据
     */
    public function saveOrder($postData) {
        //1 字段验证
        if (empty($postData)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $cli_orderid = $postData['channel_id'].'_'.$postData['orderid'];
        $data = [
            'payorder_id' =>ArrayHelper::getValue($postData,'payorder_id',''),
            'loan_id' =>ArrayHelper::getValue($postData,'loan_id',''),
            'aid' =>ArrayHelper::getValue($postData,'aid',''),
            'channel_id' =>ArrayHelper::getValue($postData,'channel_id',''),
            'account_id' =>ArrayHelper::getValue($postData,'account_id',''),
            'smscode' => '',
            'other_orderid' => '',
            'orderid' =>ArrayHelper::getValue($postData,'orderid',''),
            'cli_orderid' => $cli_orderid,
            'amount' => intval(ArrayHelper::getValue($postData,'amount','0')),
            'interest_fee' => ArrayHelper::getValue($postData,'interest_fee','0'),
            'coupon_repay_amount' => ArrayHelper::getValue($postData,'coupon_repay_amount','0'),
            'productcatalog' =>ArrayHelper::getValue($postData,'productcatalog',''),
            'productname' =>ArrayHelper::getValue($postData,'productname',''),
            'productdesc' =>ArrayHelper::getValue($postData,'productdesc',''),
            'identityid' => ArrayHelper::getValue($postData,'identityid',''),
            'sign_no' =>  '',

            'cardno' =>  ArrayHelper::getValue($postData,'cardno',''),
            'orderexpdate' => intval(ArrayHelper::getValue($postData,'orderexpdate','20')),

            'userip' => ArrayHelper::getValue($postData,'userip',''),
            'create_time' => $time,
            'modify_time' => $time,
            'status' => ArrayHelper::getValue($postData,'status',''),
            'error_code' => '',
            'error_msg' => '',
            'version' => 0,
            'callbackurl' => ArrayHelper::getValue($postData,'callbackurl',''),

            'bankname' =>  ArrayHelper::getValue($postData,'bankname',''),
            'card_type' =>  ArrayHelper::getValue($postData,'card_type',''),
            'idcard' =>  ArrayHelper::getValue($postData,'idcard',''),
            'name' =>  ArrayHelper::getValue($postData,'name',''),
            'phone' =>  ArrayHelper::getValue($postData,'phone',''),
            #'card_bank_code' =>  ArrayHelper::getValue($postData,'card_bank_code',''),
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

    public function getRbxyById($id) {
        $id = intval($id);
        if (($id > 0) === false) {
            return null;
        }
        //2. 获取订单数据
        return static::findOne($id);
    }

    /**
     *  根据订单号查询 id
     * @param $id
     * @return int
     */
    public function getRbxyByOrderid($orderid) {
        if (empty($orderid)) {
            return 0;
        }
        //2. 获取订单数据
        $data= self::find()->where(['orderid'=>$orderid])->one();
        if(empty($data)){
            return 0;
        }
        return $data->id;
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 返回链接地址数组
     * @return []
     */
    public function getPayUrls($pay_controller = 'rbxy', $pay_type = '') {
        return parent::getPayUrls($pay_controller, $pay_type);
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
            ['>=', 'modify_time', $start_time],
            ['<', 'modify_time', $end_time],
        ];
        $dataList = self::find()->where($where)->limit($page)->all();
        if (!$dataList) {
            return [];
        }
        return $dataList;
    }

    /**
     * 锁定正在查询接口的状态
     * @param $ids
     * @return int
     */
    public function lockQuerys($ids){
        if(!is_array($ids) || empty($ids)){
            return 0;
        }
        $ups = static::updateAll(['remit_status' => "+100"],['id'=> $ids]);
        return $ups;
    }

    /**
     * 单条锁定正在查询接口的状态
     * @param $status
     * @return bool
     */
    public function lockQuery($status){
        $this->remit_status = "";
        $result = $this->save();
        return $result ;
    }



}


<?php

namespace app\models\xn;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;

/**
 * This is the model class for table "st_request".
 *
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property integer $from
 * @property string $loan_id
 * @property string $create_time
 * @property string $modify_time
 */
class XnBill extends BaseModel
{
    //-2未还，0已还，-3逾期未还，-1逾期已还
    //
    const BILL_STATUS_INIT = 0; //  未还
    const BILL_STATUS_DOING = 1; // 通知中
    const BILL_STATUS_SUCCESS = 2; // 已还
    const BILL_STATUS_RETRY = 3; // 逾期未还
    const BILL_STATUS_AREADY = 4; // 逾期已还
    const BILL_STATUS_FAILURE = 11; // 失败
    const BILL_STATUS_NOTIFY_MAX = 13; // 通知达上限
    const BILL_STATUS_NOTICE_SUCCESS = 6; //通知响应成功
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xn_bill';
    }

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['bid_no'], 'required'],
            [['period', 'status', 'repayment_day', 'repayment_corpus', 'repayment_interest', 'repayment_viorate', 'repayment_fine', 'real_repayment_corpus', 'real_repayment_interest', 'real_repayment_viorate', 'real_repayment_fine', 'code','pay_notice_status'], 'integer'],
            [['createtime', 'updatetime'], 'safe'],
            [['bid_no'], 'string', 'max' => 50],
            [['msg'], 'string', 'max' => 200]
        ]; 
          
        
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => '主键',
            'bid_no' => '进件编号',
            'period'=>'期数（第几期）',
            'status' => '帐单的状态( -2未还，0已还，-3逾期未还，-1逾期已还 )',
            'repayment_day' => '计划还款时间（20160917）',
            'repayment_corpus' => '应还本金（分）',
            'repayment_interest' => '应还利息（分）',
            'repayment_viorate'=>'应还违约金（分）',
            'repayment_fine' => '应还罚息（分）',
            'real_repayment_corpus'=>'已还本金（分',
            'real_repayment_interest'=>'已还利息（分）',
            'real_repayment_viorate'=>'已还违约金（分）',
            'real_repayment_fine'=>'已还罚息（分）',
            'createtime'=>'创建时间',
            'updatetime'=>'更新时间',
            'code'=>'返回码',
            'msg'=>'返回错误信息',
        ]; 
    } 

    public function optimisticLock() {
        return "version";
    }
    /**
     * Undocumented function
     * 查询还款通知列表 默认查询还款日前一天的数据
     * @param integer $limit
     * @return void
     */
    public function getPaymentList($limit=100)
    {
        $now_time = date('Ymd',strtotime('+1 day'));
        $where = ['AND',
            ['status' => [self::BILL_STATUS_INIT,self::BILL_STATUS_RETRY]],
            ['pay_notice_status' => [self::BILL_STATUS_INIT]],
            ['repayment_day' => $now_time]
        ];
        $dataList = self::find()->where($where)->limit($limit)->all();
        if (!$dataList) 
        {
            return null;
        }
        return $dataList;
    }

    //获取状态映射 0已还 ，-1逾期已还 -2未还，-3逾期未还
    public function getStatus($status)
    {
        $sta = array(
            "0"=>self::BILL_STATUS_SUCCESS,
            "-1"=>self::BILL_STATUS_AREADY,
            "-2"=>self::BILL_STATUS_INIT,
            "-3"=>self::BILL_STATUS_RETRY
        );
        return $sta[$status];
    }

    /**
     * 锁定还款通知请求接口的状态
     */
    public function lockBill($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }

        $ups = static::updateAll(['pay_notice_status' => static::BILL_STATUS_DOING], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定正在通知接口的状态
     */
    public function lockOneBill(){
        try{
            $this->pay_notice_status = static::BILL_STATUS_DOING;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }
     /**
     * 根据编号获取纪录
     * @param $partner_trade_no
     * @return object
     */
    public  function getByOrderid($order_id){
        if(!$order_id){
            return null;
        }
        return static::find() -> where(["bid_no"=>$order_id]) ->one();
    }
    /**
     * Undocumented function
     * 保存账单数据
     * @param [type] $postData
     * @return void
     */
    public function saveBillData($postData){
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $postData['createtime'] = $time;
        $postData['updatetime'] = $time;
        $bill_status= $this->getStatus($postData['status']);
        if(isset($bill_status)){
            $postData['status'] = $bill_status;
        }else{
            $postData['status'] = self::BILL_STATUS_FAILURE;
        }      
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(null,implode('|', $error));
        }
        $res = $this->save();
        if (!$res) {
            return $this->returnError(null,implode('|', $this->errors));
        }
        return true;
    }
    /**
     * Undocumented function
     * 更新还款通知状态
     * @param [type] $status
     * @param [type] $code
     * @param [type] $msg
     * @return void
     */
    public function updateRepayStatus($status,$code,$msg){
        $this->pay_notice_status = $status;
        $this->code = (int)$code;
        $this->msg = $msg;
        $this->updatetime = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 还款回调返回还款状态
     * @param [type] $status
     * @param [type] $code
     * @return void
     */
    public function updateBillStatus($status,$code){
        $bill_status = $this->getStatus($status);
        if(!isset($bill_status)){
            return false;
        }
        $this->status = $bill_status;
        $this->code = (int)$code;
        $this->updatetime = date('Y-m-d H:i:s');
        return $this->save();
    }
}

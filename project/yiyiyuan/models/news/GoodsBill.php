<?php

namespace app\models\news;

use app\commonapi\Logger;
use Yii;
use app\models\news\User_loan;


/**
 * This is the model class for table "yi_goods_bill".
 *
 * @property integer $id
 * @property string $bill_id
 * @property string $order_id
 * @property integer $goods_id
 * @property integer $loan_id
 * @property integer $user_id
 * @property integer $fee_type
 * @property integer $type
 * @property integer $phase
 * @property string $fee
 * @property integer $number
 * @property string $goods_amount
 * @property string $current_amount
 * @property string $actual_amount
 * @property string $repay_amount
 * @property string $principal
 * @property string $interest
 * @property string $start_time
 * @property string $end_time
 * @property integer $days
 * @property integer $bill_status
 * @property string $repay_time
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class GoodsBill extends \app\models\BaseModel
{
    CONST STATUS_NORMAL     = 9;//正常
    CONST STATUS_FINISH     = 8;//结清
    CONST STATUS_FAIL       = 11;//驳回
    CONST STATUS_YQING      = 12;//逾期
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bill_id', 'goods_id', 'loan_id', 'user_id', 'phase', 'fee', 'number', 'goods_amount', 'current_amount', 'actual_amount', 'repay_amount', 'principal', 'interest', 'start_time', 'end_time', 'days', 'bill_status', 'repay_time', 'create_time', 'last_modify_time'], 'required'],
            [['goods_id', 'loan_id', 'user_id', 'fee_type', 'type', 'phase', 'number', 'days', 'bill_status', 'version'], 'integer'],
            [['fee', 'goods_amount', 'current_amount', 'actual_amount', 'repay_amount', 'principal', 'interest'], 'number'],
            [['start_time', 'end_time', 'repay_time', 'create_time', 'last_modify_time'], 'safe'],
            [['bill_id', 'order_id'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bill_id' => 'Bill ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'fee_type' => 'Fee Type',
            'type' => 'Type',
            'phase' => 'Phase',
            'fee' => 'Fee',
            'number' => 'Number',
            'goods_amount' => 'Goods Amount',
            'current_amount' => 'Current Amount',
            'actual_amount' => 'Actual Amount',
            'repay_amount' => 'Repay Amount',
            'principal' => 'Principal',
            'interest' => 'Interest',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'days' => 'Days',
            'bill_status' => 'Bill Status',
            'repay_time' => 'Repay Time',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * 查询loan_id下账单数量
     * @param $loanId
     * @return int
     */
    public function getCountByLoanId($loanId) {
        return self::find()->where(['loan_id' => $loanId])->count();
    }


    //批量查询是否存在
    public function isbill($billarr){
        if(empty($billarr)){
            return false;
        }
        foreach ($billarr as $item) {
            $oGoodsBill=self::find()->where(['id'=>$item])->one();
            if(empty($oGoodsBill)){
                return false;
            }
        }
        return true;
    }
    public function getBillrepay() {
        return $this->hasOne(BillRepay::className(), ['bill_id' => 'id']);
    }
    public function getOverdueloan() {
        return $this->hasOne(OverdueLoan::className(), ['bill_id' => 'bill_id']);
    }
    /**
     * 添加数据
     * @param $data
     */
    public function addRecord($data) {
        //1 检测数据是否有效
        if (!is_array($data) || empty($data)) {
            return false;
        }

        //2  设置当前类为可添加的, 并检测是否有错误发生
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(false, $errors);
        }

        return $this->save();
    }
    /**
     * 批量新增记录
     * @param $postdata
     */
    public function batchAddGoodsBill($postdata) {
        if (!is_array($postdata) || empty($postdata)) {
            return false;
        }
        try{
            return static::insertBatch($postdata);           
        }catch(\Exception $e){
            return false;
        }
        
    }
    /**
     * 获取分期账单
     *
     * @param [type] $loan_id
     * @return void
     */
    public function getGoodsBills($loan_id){
        if (empty($loan_id)){
            return false;
        }
        $where = [
            'loan_id'=>$loan_id,
        ];
        $data = self::find()->where($where)->all();
        return $data;
    }
    /**
     * 获取分期待还款账单
     *
     * @param [type] $loan_id
     * @return void
     */
    public function getPrepayGoodsBills($loan_id){
        if (empty($loan_id)){
            return false;
        }
        $where = [
            'AND',
            ['loan_id'=>$loan_id],
            ['in','bill_status',[static::STATUS_NORMAL,static::STATUS_YQING]],
        ];
        $data = self::find()->where($where)->orderBy('phase')->indexBy('phase')->all();
        return $data;
    }  
     /**
     * 本期待还的账单
     * @param type $loan_id
     * @param type $startTime
     * @param type $endTime
     * @return boolean
     */
    public function getPayGoodsBill($loan_id) {
        if ( empty($loan_id) ) {
            return false;
        }
        $now_time  = date('Y-m-d H:i:s',time());
        $where     = [
            'and',
            ['=', 'loan_id', $loan_id],
            ['!=', 'bill_status', 8],
            ['<=', 'start_time', $now_time],
            ['>', 'end_time', $now_time],
        ];
        $goodsBill = self::find()->where($where)->one();
        return $goodsBill;
    }
    
    public function getFirstPeriodFee($loan_id){
        if ( empty($loan_id) ) {
            return false;
        }
        $where     = [
            'and',
            ['=', 'loan_id', $loan_id],
            ['=', 'phase', 1],
            ['!=', 'bill_status', 8],
        ];
        $goodsBillFee = self::find()->select('interest')->where($where)->one();
        return empty($goodsBillFee) ? 0: $goodsBillFee['interest'];
    }

    /**
     * 待还期数
     * @param type $oUserLoan
     */
    public function getPeriodNum($oUserLoan){
        if(empty($oUserLoan)){
            return 0;
        }
        if( !in_array($oUserLoan->business_type, [5, 6, 11])){
            return 0;
        }
        $loan_id   = $oUserLoan->loan_id;
        $where     = [
            'and',
            ['=', 'loan_id', $loan_id],
            ['!=', 'bill_status', 8],
         ];
         $periodNum = self::find()->where($where)->count();
         return $periodNum;
}
    public function toSuccess($ids) {
        $attributes = [
            'bill_status'       => static::STATUS_FINISH,
            'last_modify_time'  => date("Y-m-d H:i:s"),
            'repay_time'        => date('Y-m-d H:i:s'),
        ];
        $condition = [
            'id' => $ids,
        ];
        $update_num = self::updateAll($attributes, $condition);
        return $update_num;
    }
    public function toFail($ids) {
        $attributes = [
            'bill_status' => static::STATUS_FAIL,
            'last_modify_time' => date("Y-m-d H:i:s"),
        ];
        $condition = [
            'id' => $ids,
        ];
        $update_num = self::updateAll($attributes, $condition);
        return $update_num;
    }
    
    /**
     * 修改起息日 结束日
     * @param $time 
     * @return bool
     */
    public function updatetime($start_time, $end_time) {
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->last_modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /*
     * 获取所有未还账单
     * $type 1:所有待还账单 2：必还账单 3:所有账单
     */
    public function getNotYetBillList($loan_id,$type = 1){
        if(empty($loan_id)){
            return false;
        }
        $loanInfo = User_loan::find()->where(['loan_id' => $loan_id,'status' => [9,11,12,13],'business_type' => [5,6,11]])->one();
        if(empty($loanInfo)){
            return false;
        }
        $now_time  = date('Y-m-d H:i:s',time());
        $where = [
            'and',
            ['=', 'loan_id', $loan_id],
        ];
        if($type == 1){
            $where[] = ['!=', 'bill_status', 8];
        }else if($type == 2){
            $where[] = ['!=', 'bill_status', 8];
            $where[] = ['<=', 'start_time', $now_time];
        }
        $notYetBillList = self::find()->select(['id','number','bill_status','actual_amount','days','end_time','phase'])->where($where)->orderBy('phase asc')->asArray()->all();
        return $notYetBillList;
    }

    
    /**
     * 获取逾期天数兼容分期
     * @param $status
     * @param $end_date
     * @return float|int
     */
    public function getFqOverdueDays($userLoanObj) {
        $overdue_days = 0;
        $day =  (new GoodsBill())->find()->select('end_time')->where(['loan_id'=>$userLoanObj->loan_id,'bill_status' => 12])->asArray()->all();
        $min_day = min( $day );
        if (time() > strtotime( $min_day['end_time'] )) {
               $overdue_days = ceil((time() - strtotime($min_day['end_time'])) / 24 / 3600);
        } 
        return $overdue_days;
    }


    /**
     * 获取最近一期未还清账单信息
     * @param $loanId
     * @return array|null|ActiveRecord
     */
    public function getLatelyPhase($loanId) {

        if (empty($loanId) || !is_numeric($loanId)) {
            return NULL;
        }

        $where = [
            'and',
            ['=', 'loan_id', $loanId],
            ['!=', 'bill_status', 8],
        ];
        return self::find()->where($where)->orderBy('end_time asc')->one();
    }

    /**
     * 根据loan_id查询子订单
     */
    public function getRepaylistInfo($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return NULL;
        }
        $where = ['loan_id' => $loan_id];
        $order = self::find()->where($where)->asArray()->all();
        return $order;
    }
    /**
     * 根据bill_id数组获重组数组
     */
    public function getPeriods($goodbill_arr){
        if(empty($goodbill_arr)){
            return false;
        }
        $goodbill= self::find()->select(['phase','actual_amount'])->where(['id'=>$goodbill_arr])->asArray()->all();
        if(empty($goodbill)){
            return false;
        }
        foreach($goodbill as $key=>$value){
            $periods[$value['phase']]=$value['actual_amount'];
        }
        ksort($periods);
       return $periods;
    }
    /**
     * 分期还款检测
     * @ture 是金额正确
     */
    public function check_repay($loaninfo,$money,$goodbill_arr){
        if(empty($loaninfo) || empty($money) || empty($goodbill_arr)){
            return false;
        }
        //分期还款（分期应还金额=还款金额）
        $yinghuanMoney = (new User_loan())->getRepaymentAmount($loaninfo,1,$goodbill_arr);
        if ( $yinghuanMoney !=  $money) {
            Logger::dayLog('cunguan/isDepositoryRepay', 4, $yinghuanMoney, $money);
            return false;
        }else{
            return true;
        }
    }



}

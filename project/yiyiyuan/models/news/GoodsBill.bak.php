<?php

namespace app\models\news;

use app\models\BaseModel;
use Exception;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception as Exception2;

/**
 * This is the model class for table "yi_goods_bill".
 *
 * @property string $id
 * @property string $bill_id
 * @property string $order_id
 * @property string $goods_id
 * @property string $loan_id
 * @property string $user_id
 * @property integer $phase
 * @property integer $fee
 * @property integer $number
 * @property string $goods_amount
 * @property string $current_amount
 * @property string $repay_amount
 * @property string $actual_amount
 * @property string $start_time
 * @property string $end_time
 * @property integer $days
 * @property integer $bill_status
 * @property string $remit_status
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class GoodsBill extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_goods_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['goods_id', 'loan_id', 'user_id', 'phase', 'fee', 'number', 'days', 'bill_status', 'version'], 'integer'],
            [['goods_amount', 'current_amount', 'actual_amount', 'repay_amount', 'principal', 'over_principal', 'interest', 'over_interest', 'over_late_fee'], 'number'],
            [['start_time', 'end_time','repay_time', 'create_time', 'last_modify_time'], 'safe'],
            [['bill_id', 'order_id'], 'string', 'max' => 64],
            [['remit_status'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'bill_id'          => 'Bill ID',
            'order_id'         => 'Order ID',
            'goods_id'         => 'Goods ID',
            'loan_id'          => 'Loan ID',
            'user_id'          => 'User ID',
            'phase'            => 'Phase',
            'fee'              => 'Fee',
            'number'           => 'Number',
            'goods_amount'     => 'Goods Amount',
            'current_amount'   => 'Current Amount',
            'actual_amount'    => 'Actual Amount',
            'start_time'       => 'Start Time',
            'end_time'         => 'End Time',
            'days'             => 'Days',
            'bill_status'      => 'Bill Status',
            'remit_status'     => 'Remit Status',
            'create_time'      => 'Create Time',
            'repay_time'       => 'Repay Time',
            'last_modify_time' => 'Last Modify Time',
            'version'          => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getRemit() {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['user_id' => 'user_id']);
    }

    public function getPromes() {
        return $this->hasOne(Promes::className(), ['loan_id' => 'loan_id']);
    }

    public function getUserloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getOverdueflows() {
        return $this->hasOne(OverdueLoanFlows::className(), ['loan_id' => 'loan_id']);
    }

    public function getOverdueloan() {
        return $this->hasOne(OverdueLoan::className(), ['bill_id' => 'bill_id']);
    }

    public function getGoodsBill($startTime, $endTime) {
        if (empty($startTime) || empty($endTime)) {
            return false;
        }
        $where     = [
            'and',
            ['=', 'bill_status', 12],
            ['>=', 'start_time', $startTime],
            ['<', 'end_time', $endTime],
        ];
        $goodsBill = self::find()->where($where)->all();
        return $goodsBill;
    }

    /**
     * 根据loan_id查询子订单
     */
    public function getRepaylist($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return NULL;
        }
        $where = ['loan_id' => $loan_id];
        $order = self::find()->where($where)->asArray()->all();
        return $order;
    }

    /*
     * 修改订单
     */

    public function saveGoodsBill($data) {
        if (empty($data)) {
            return false;
        }
        try {
            $data['last_modify_time'] = date("Y-m-d H:i:s");
            $error                    = $this->chkAttributes($data);
            if ($error) {
                return false;
            }
            $res = $this->save();
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    /*
     * 结清订单
     */

    public function clearGoodsBill($data) {
        if (empty($data)) {
            return false;
        }
        $data['modify_time'] = date("Y-m-d H:i:s");
        $data['bill_status'] = 8;
        $this->attributes    = $data;
        $res                 = $this->save();
        return $res;
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
     * 获分期取逾期金额
     * @param type $bill_info
     * @param type $chase_amount
     * @return type
     */
    public function getOverdueAmount($bill_info, $chase_amount) {
//        后一日某期应还款金额= 今日该期应还款金额*1.01+未到期应还款金额*0.01/已到期期数
        $time           = time();     //当前时间
        $overdue_days   = ceil(($time - strtotime($bill_info['end_time'])) / 86400) -1;  //31
        $days           = $bill_info['days'];           //分期周期天数  28
        $parse          = $bill_info['phase'];          //当前订单期数  1 
        $number         = $bill_info['number'];         //订单总分期数  3 
        $level          = floor($overdue_days / $days); //临时期数  1
        $already_period = $parse + $level;              //已到期期数
        $surplus_amount = 0;
        if ($already_period < $number) {
            $tmp_arr = [];
            for ($i = $already_period + 1; $i <= $number; $i++) {
                $tmp_arr[] = $i;
            }
            $where          = [
                'and',
                ['loan_id' => $bill_info['loan_id']],
                ['in', 'phase', $tmp_arr]
            ];
            $surplus_amount = self::find()->where($where)->select('sum(current_amount) as current_amount')->one();
            $surplus_amount = $surplus_amount['current_amount'];
        }
        $surplus_amount = $surplus_amount < 0 ? 0 : $surplus_amount;
        $return_amount  = $chase_amount * 1.01 + $surplus_amount * 0.01 / $already_period;
        return floor($return_amount * 100) / 100;
    }

    /**
     * 批量新增记录
     * @param $condition
     * @return bool
     */
    public function batchAddGoodsBill($value) {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        $key = ['bill_id', 'order_id', 'goods_id', 'loan_id', 'user_id', 'phase', 'fee', 'number', 'goods_amount', 'current_amount', 'actual_amount', 'repay_amount', 'principal', 'interest', 'start_time', 'end_time', 'days', 'bill_status', 'last_modify_time', 'create_time'];
        try {
            $num = Yii::$app->db->createCommand()->batchInsert(GoodsBill::tableName(), $key, $value)->execute();
        } catch (Exception $e) {
            $num = 0;
        }
        return $num;
    }

    /**
     * 根据条件查找数据
     * @param type $where  条件
     * @return [] 
     */
    public function getPostData($where) {
        return self::find()->where($where)->all();
    }

    /**
     * 查询loan_id下账单数量
     * @param $loanId
     * @return int
     */
    public function getCountByLoanId($loanId) {
        return self::find()->where(['loan_id' => $loanId])->count();
    }

    /**
     * 获取逾期记录，根据loan_id
     * @param $loanId
     * @return array|null|ActiveRecord[]
     */
    public function listOverDueByLoanId($loanId) {
        if (!is_numeric($loanId) || empty($loanId)) {
            return null;
        }
        return self::find()->where(['loan_id' => $loanId, 'bill_status' => 12])->orderBy(['create_time' => 'desc'])->all();
    }

    /**
     * 根据接口传过来的数据计算分期订单本金和利息的综合
     * @param type $httpParams 贷后接口传输过来的参数
     */
    public function getBillAmount($loan_id , $bill_ids) {
        $bill_where   = ['and'];
        $bill_where[] = ['loan_id' => $loan_id];
        $bill_where[] = ['bill_id' => $bill_ids];
        $select       = 'sum(principal) as principal , sum(interest) as interest ,sum(repay_amount) as repay_amount';
        return self::find()->select($select)->where($bill_where)->one();
    }

    /**
     * 根据条件获取总量
     * @param type $where 条件
     * @author zhangtian zhangtian@xianhuahua.com
     * @copyright (c) 2017, 11 23
     */
    public function getTotalNum($where = []) {
        return self::find()->where($where)->count();
    }
    
    /**
     * 结清分期订单
     * @param type $bill_ids []
     */
    public function changeBillStatus($bill_ids){
        if(!$bill_ids){
            return FALSE;
        }
        $res = GoodsBill::updateAll(['status'=> 8],['bill_id'=>$bill_ids]);
    }

    public function listRepayPlan($amount, $period, $fee, $day = 30, $start_date = ''){
        $single = bcdiv();
    }
}

<?php

namespace app\models\yyy;
use app\models\yyy\YiUserLoan;
use app\models\yyy\YiUserRemitList;
use Yii;

/**
 * This is the model class for table "yi_renewal_payment_record".
 *
 * @property integer $loan_id
 * @property integer $parent_loan_id
 * @property integer $number
 * @property integer $settle_type
 * @property integer $user_id
 * @property string $loan_no
 * @property string $real_amount
 * @property string $amount
 * @property string $recharge_amount
 * @property string $credit_amount
 * @property string $current_amount
 * @property integer $days
 * @property string $start_date
 * @property string $end_date
 * @property string $open_start_date
 * @property string $open_end_date
 * @property integer $type
 * @property integer $status
 * @property integer $prome_status
 * @property string $interest_fee
 * @property string $desc
 * @property string $contract
 * @property string $contract_url
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 * @property string $repay_time
 * @property string $withdraw_fee
 * @property string $chase_amount
 * @property string $like_amount
 * @property string $collection_amount
 * @property string $coupon_amount
 * @property integer $is_push
 * @property integer $final_score
 * @property integer $repay_type
 * @property integer $business_type
 * @property string $withdraw_time
 * @property integer $bank_id
 * @property integer $source
 * @property integer $is_calculation
 */
class YiRenewalPrecord extends \app\models\yyy\YyyBase
{

    const GENE  = 1;//0:初始状态;1为线上还款成功;2还款失败
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_renewal_payment_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'parent_loan_id', 'new_loan_id','user_id','bank_id','platform','source','status','version'], 'integer'],
            [['user_id', 'money', 'actual_money'], 'required'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['loan_no', 'contract'], 'string', 'max' => 64],
            [['desc'], 'string', 'max' => 1024],
            [['contract_url'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'      => 'ID',
            'loan_id' => 'Loan ID',
            'order_id' => 'Order ID',
            'parent_loan_id' => 'Parent Loan ID',
            'new_loan_id' => 'New Loan ID',
            'user_id' => 'User ID',
            'bank_id' => 'Bank ID',
            'platform' => 'Platform',
            'source' => 'Source',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'status' => 'Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    public function getLoanByLoanId($loan_id){
        $data = static::findOne($loan_id);
        return $data;
    }

    /**
     * 拆账使用，获取展期的公司主体和类型已经金额
     *@param $where
    **/

    public function  getAlldatas($filter_where){

        $start = $filter_where['start_time'];
        $end = $filter_where['end_time'];
        $where = [
            'and',
            [self::tableName().'.status'=>self::GENE],
            ['>=',self::tableName().'.last_modify_time',$start],
            ['<',self::tableName().'.last_modify_time',$end],
            ['s.remit_status'=>'SUCCESS'],
        ];
        $data = self::find()->select([
            'sum('.self::tableName().'.money) as total_money',
            'l.days as days',
            's.fund as fund',
            self::tableName().'.id',
        ])
            ->leftJoin('yi_user_loan  l',self::tableName().'.loan_id = l.loan_id')
            ->leftJoin('yi_user_remit_list s','l.parent_loan_id=s.loan_id')
            ->where($where)->groupBy('l.days,s.fund')->asArray()->all();
        return $data;
    }

    /**
     *  获取 展期总金额
     * @param $filter_where
     * @return mixed
     */
    public function getTotalMoney($filter_where){
        $start = $filter_where['start_time'];
        $end = $filter_where['end_time'];
        $where = [
            'and',
            [self::tableName().'.status'=>self::GENE],
            ['>=',self::tableName().'.last_modify_time',$start],
            ['<',self::tableName().'.last_modify_time',$end]
        ];

        return self::find()->where($where)->sum('actual_money');
    }
}
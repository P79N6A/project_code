<?php

namespace app\modules\balance\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_renewal_payment_record".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $order_id
 * @property string $parent_loan_id
 * @property string $new_loan_id
 * @property string $user_id
 * @property string $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $money
 * @property string $actual_money
 * @property string $paybill
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class RenewalPaymentRecord extends YyyBase
{
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
            [['loan_id', 'order_id', 'parent_loan_id', 'user_id', 'platform', 'source', 'last_modify_time', 'create_time'], 'required'],
            [['loan_id', 'parent_loan_id', 'new_loan_id', 'user_id', 'bank_id', 'platform', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['order_id'], 'string', 'max' => 32],
            [['paybill'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
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
    /**
     * 通过loan_id获取还款记录
     * @param $loan_id
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getDataByLoanid($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        return self::find()->where(['loan_id' => $loan_id])->all();
    }

    /**
     * 获取展期还款成功记录
     * @param $filter_where
     */
    private function renewalWhere($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find()->select([self::tableName().'.*', UserLoan::tableName().'.days', UserLoan::tableName().'.status as loan_status', UserLoan::tableName().'.amount',  UserLoan::tableName().'.start_date', UserLoan::tableName().'.end_date', UserLoan::tableName().'.interest_fee', UserLoan::tableName().'.like_amount', UserLoan::tableName().'.coupon_amount'])
                    ->leftJoin(UserLoan::tableName(),UserLoan::tableName().".loan_id=".self::tableName().".loan_id");

        if (!empty($filter_where['days'])){
            $result->andWhere([ UserLoan::tableName().'.days' => $filter_where['days']]);
        }
        if (!empty($filter_where['loan_id'])){
            $result->andWhere([ self::tableName().'.loan_id' => $filter_where['loan_id']]); 
        }
        if (!empty($filter_where['order_id'])){
            $result->andWhere([ self::tableName().'.order_id' => $filter_where['order_id']]); 
        }
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=',  self::tableName().'.create_time', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=', self::tableName().'.create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        $result->andWhere(['>=', self::tableName().'.create_time', '2018-01-01']);
        $result->andWhere([self::tableName().'.status'=>'1']);
        return $result;
    }

    /**
     * 获取展期还款成功记录
     * @param $filter_where
     */
    private function renewalWheres($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find()->select([
            'id'               =>  'yi_renewal_payment_record.id',
            'actual_money'      => "sum(yi_renewal_payment_record.actual_money)",
            'amount'            => "sum(yi_user_loan.amount)",
            'all_interest_fee'  => "sum(yi_user_loan.interest_fee)",

          ])
            ->leftJoin(UserLoan::tableName(),UserLoan::tableName().".loan_id=".self::tableName().".loan_id");

        if (!empty($filter_where['days'])){
            $result->andWhere([ UserLoan::tableName().'.days' => $filter_where['days']]);
        }
        if (!empty($filter_where['loan_id'])){
            $result->andWhere([ self::tableName().'.loan_id' => $filter_where['loan_id']]);
        }
        if (!empty($filter_where['order_id'])){
            $result->andWhere([ self::tableName().'.order_id' => $filter_where['order_id']]);
        }
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=',  self::tableName().'.create_time', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=', self::tableName().'.create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        $result->andWhere(['>=', self::tableName().'.create_time', '2018-01-01']);
        $result->andWhere([self::tableName().'.status'=>'1']);
        return $result;
    }

    /**
     * 计算时间区间条数
     * @param $filter_where
     * @return int
     */
    public function countRenewal($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->renewalWhere($filter_where);
        return $result->orderBy( self::tableName().'.create_time desc')->count();
    }

    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     */
    public function getAllData($pages, $filter_where)
    {
        if (empty($pages)){
            return false;
        }
        $result = $this->renewalWhere($filter_where);

        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy( self::tableName().'.create_time desc')
            ->asArray()
            ->all();
    }
    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     */
    public function getAllDatas($pages,$filter_where)
    {
        if (empty($pages)){
            return false;
        }
        $result = $this->renewalWheres($filter_where);

        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy( self::tableName().'.create_time desc')
            ->asArray()
            ->one();
    }
    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     */
    public function getLoanData($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->renewalWhere($filter_where);

        return $result->asArray()->one();
    }

    /**
     * 获取下载数据
     * @param $pages
     * @param $filter_where
     */
    public function getDownData($filter_where){
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->renewalWhere($filter_where);
        return $result->orderBy( self::tableName().'.create_time desc')
            ->asArray()
            ->all();
    }


    /*
     * 实收展期服务费金额
     */
    public function renewServer($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $where_config = [
            'AND',
            ['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
            ['<=', 'create_time', ArrayHelper::getValue($condition, 'end_time')],
            ['=', 'status', 1],
        ];
        $total = self::find()->where($where_config)->sum('actual_money');
        return empty($total) ? 0 : $total;
    }
}
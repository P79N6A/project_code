<?php

namespace app\modules\balance\models\yyy;
use Yii;


class CgRemit extends YyyBase
{
    public $bill_date;
    public $total;
    public $sum;
    public $txFee;

    public $remit_id;
    public $cg_id;

    private $cgRemitTablename;
    private $userLoanTablename;
    private $userTablename;
    private $userRemitTablename;
    public function __construct(){
        $this->cgRemitTablename = self::tableName();
        $this->userLoanTablename = UserLoan::tableName();
        $this->userTablename = User::tableName();
        $this->userRemitTablename = User_remit_list::tableName();
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_cg_remit';
    }
    /**
     * 债券类型
     *
     */

    public static function getBondtype()
    {
        return [
            '1' => '7天',
            '2' => '14天',
            '3' => '28天',
            '4' => '42天',
            '5' => '56天',
            '6' => '63天',
            '7' => '84天',
            '8' => '168天',
            '9' => '336天'
        ];
    }
    /**
     * Undocumented function
     * 获取账单分组总数
     * @param [type] $where
     * @return void
     */
    public function countRemitData($filter_where){
        
        $query = self::find();
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->leftJoin($this->userLoanTablename,$this->cgRemitTablename.'.loan_id='.$this->userLoanTablename.'.loan_id')->
            leftJoin($this->userRemitTablename,$this->cgRemitTablename.'.loan_id='.$this->userRemitTablename.'.loan_id')
            ->groupBy('DATE ('.$this->cgRemitTablename.'.create_time),'.$this->userLoanTablename.'.days')->count();
        return $data;
    }
    /**
     * Undocumented function
     * 获取账单分组数据
     * @param [type] $where
     * @return void
     */
    public function getRemitData($pages,$filter_where){
        $query = self::find()->select([$this->cgRemitTablename.'.id,count(*) as all_num,'.$this->userLoanTablename.'.days,DATE('.$this->cgRemitTablename.'.create_time) as bill_date,sum(amount) as money,sum(interest_fee) as fee,sum(amount+interest_fee) as all_money,'.$this->userRemitTablename.'.fund']);
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->leftJoin($this->userLoanTablename,$this->cgRemitTablename.'.loan_id='.$this->userLoanTablename.'.loan_id')->leftJoin($this->userRemitTablename,$this->cgRemitTablename.'.loan_id='.$this->userRemitTablename.'.loan_id')->offset($pages->offset)->limit($pages->limit)->groupBy('DATE ('.$this->cgRemitTablename.'.create_time),'.$this->userLoanTablename.'.days')->orderBy($this->cgRemitTablename.'.create_time desc')->asArray()->all();
        return $data;
    }

    /**
     * Undocumented function
     * 获取账单汇总数据
     * @param [type] $where
     * @return void
     */
    public function getRemitDatas($filter_where){
        $query = self::find()->select([
            'id'   => 'yi_cg_remit.id',
            'money' => "sum(yi_user_loan.amount)",
            'fee'   => "sum(yi_user_loan.interest_fee)",
            ]);
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->leftJoin($this->userLoanTablename,$this->cgRemitTablename.'.loan_id='.$this->userLoanTablename.'.loan_id')->leftJoin($this->userRemitTablename,$this->cgRemitTablename.'.loan_id='.$this->userRemitTablename.'.loan_id')->orderBy($this->cgRemitTablename.'.create_time desc')->asArray()->one();
        return $data;
    }
    
    /**
     * 初始条件
     * @param $filter_where
     * @return int|\yii\db\ActiveQuery
     */
    private function fundWhere($query,$filter_where)
    {

        if (!empty($filter_where['start_time'])){
            $query->andWhere(['>=', $this->cgRemitTablename.'.create_time', $filter_where['start_time']]);
        }
        if (!empty($filter_where['end_time'])){
            $query->andWhere(['<=', $this->cgRemitTablename.'.create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        if(!empty($filter_where['days'])){
            $query->andWhere([$this->userLoanTablename.'.days' => $filter_where['days']]);
        }
        if(!empty($filter_where['capitalSide'])){
            $query->andWhere([$this->userRemitTablename.'.fund' => $filter_where['capitalSide']]);
        }
        $query->andWhere([$this->cgRemitTablename.'.remit_status'=>'success']);
        return $query;
    }
    /**
     * Undocumented function
     * 获取导出明细数据
     * @param [type] $bill_date
     * @param [type] $days
     * @return void
     */
    public function getExportData($bill_date,$days){
        $where = [ 
            'and',         
            ['>=',$this->cgRemitTablename.'.create_time',$bill_date],
            ['<=',$this->cgRemitTablename.'.create_time',$bill_date. ' 23:59:59'],
            [$this->userLoanTablename.'.days'=>$days],
            [$this->cgRemitTablename.'.remit_status'=>'success']
        ];
        $data = self::find()->select($this->userRemitTablename.'.id , '.$this->cgRemitTablename.'.id as cg_id, '.$this->cgRemitTablename.'.loan_id,
         '.$this->cgRemitTablename.'.order_id,'.$this->userLoanTablename.'.create_time,days,amount,interest_fee,mobile,realname,DATE('.$this->cgRemitTablename.'.create_time) as bill_date,'.$this->userRemitTablename.'.fund')
                ->leftJoin($this->userLoanTablename,$this->cgRemitTablename.'.loan_id='.$this->userLoanTablename.'.loan_id')
                ->leftJoin($this->userTablename,$this->cgRemitTablename.'.user_id='.$this->userTablename.'.user_id')
                ->leftJoin($this->userRemitTablename,$this->cgRemitTablename.'.loan_id='.$this->userRemitTablename.'.loan_id')
                ->where($where)->asArray()->all();
        return $data;
    }

    public function conditionService($condition)
    {
        if (empty($condition)){
            return false;
        }
        $where_condition = [
            'AND',
            ['>', '`real_amount`', '`settle_amount`'],
            ['IN', 'remit_status', ['SUCCESS']]
        ];
        $result = self::find()
            -> select([
                'bill_date'     => 'DATE_FORMAT(`last_modify_time`,"%Y-%m-%d")',
                'total'         => 'count(id)',
                'sum'           => 'sum(settle_amount)',
                'txFee'         => '`real_amount`-`settle_amount`',
                'order_id'
            ])
            -> where($where_condition);
        if (!empty($condition['start_time'])){
            $result->andWhere(['>=', 'last_modify_time', date("Y-m-d 00:00:00", strtotime($condition['start_time']))]);
        }
        if (!empty($condition['end_time'])){
            $result->andWhere(['<=', 'last_modify_time', date("Y-m-d 23:59:59", strtotime($condition['end_time']))]);
        }
        $result->groupBy( 'bill_date');
        return $result;
    }

    public function getServiceTotal($condition)
    {
        if (empty($condition)){
            return 0;
        }
        return $this->conditionService($condition)->count();
    }

    public function getServiceData($pages, $condition)
    {
        if (empty($condition) || empty($pages)){
            return 0;
        }
        return $this->conditionService($condition)->offset($pages->offset)
            ->limit($pages->limit)->orderBy("bill_date desc")->all();
    }

    public function serverDown($bill_date)
    {
        if (empty($bill_date)){
            return false;
        }
        $where_condition = [
            'AND',
            ['>', '`real_amount`', '`settle_amount`'],
            ['IN', 'remit_status', ['SUCCESS']],
            ['>=', 'last_modify_time', date("Y-m-d 00:00:00", strtotime($bill_date))],
            ['<=', 'last_modify_time', date("Y-m-d 23:59:59", strtotime($bill_date))],
        ];
        return self::find()->where($where_condition)->all();
    }

    public function serverAllCondition($condition)
    {
        if (empty($condition)){
            return false;
        }
        $where_condition = [
            'AND',
            ['>', '`real_amount`', '`settle_amount`'],
            ['IN', 'remit_status', ['SUCCESS']]
        ];
        $result = self::find()
            -> where($where_condition);
        if (!empty($condition['start_time'])){
            $result->andWhere(['>=', 'last_modify_time', date("Y-m-d 00:00:00", strtotime($condition['start_time']))]);
        }
        if (!empty($condition['end_time'])){
            $result->andWhere(['<=', 'last_modify_time', date("Y-m-d 23:59:59", strtotime($condition['end_time']))]);
        }
        return $result;
    }

    public function getServerAllTotal($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $total = $this->serverAllCondition($condition)->count();
        return empty($total) ? 0 : $total;
    }

    public function getServerAllMoney($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $total = $this->serverAllCondition($condition)->sum('settle_amount');
        return empty($total) ? 0 : $total;
    }
}
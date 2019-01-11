<?php

namespace app\modules\balance\models;
use Yii;


class ZhanPolicy extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zhan_policy';
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
    public function countPolicyData($filter_where){
        
        $query = self::find();
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->groupBy('DATE (create_time),policyDate')->count();
        return $data;
    }
    /**
     * Undocumented function
     * 获取账单分组数据
     * @param [type] $where
     * @return void
     */
    public function getPolicyData($pages,$filter_where){
        $query = self::find()->select(['count(*) as all_num,policyDate,DATE(create_time) as bill_date,sum(premium) as money']);
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->offset($pages->offset)->limit($pages->limit)->groupBy('DATE (create_time),policyDate')->orderBy('create_time desc')->asArray()->all();
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
            $query->andWhere(['>=','create_time', $filter_where['start_time']]);
        }
        if (!empty($filter_where['end_time'])){
            $query->andWhere(['<=','create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        if(!empty($filter_where['policyDate'])){
            $query->andWhere(['policyDate' => $filter_where['policyDate']]);
        }
        $query->andWhere(['remit_status'=>'6']);
        return $query;
    }
    /**
     * Undocumented function
     * 获取导出明细数据
     * @param [type] $bill_date
     * @param [type] $days
     * @return void
     */
    public function getExportData($filter_where){
        $query = self::find()->select(['id,req_id,client_id,user_name,user_mobile,premium,create_time,policy_time,DATE(create_time) as bill_date']);
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->orderBy('create_time desc')->asArray()->all();
        return $data;
    }
}
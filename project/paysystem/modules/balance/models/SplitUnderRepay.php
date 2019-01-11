<?php

namespace app\modules\balance\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 *线下还款  拆账结果表
 * This is the model class for table "payment_details".
 *
 * @property string $id
 * @property string $repay_id
 * @property integer $user_id
 * @property string $loan_id
 * @property string $settle_amount
 * @property string $settle_fee
 * @property string $fund
 * @property string $fund_party
 * @property string $jk_amount
 * @property string $jk_interest_fee
 * @property string $jk_number
 * @property string $jk_settle_type
 * @property string $parent_loan_id
 * @property string $withdraw_fee
 * @property integer $is_calculation
 * @property string $jk_status
 * @property integer $split_interest
 * @property integer $split_principal
 * @property integer $split_fine
 * @property string $create_time
 * @property integer $last_modify_time
 * @property integer $status
 * @property string $remark
 * @property integer $channel_id
 * @property integer $aid
 * @property string $mechart_num
 * @property string $bill_time
 * @property integer $bill_money
 * @property integer $bill_service_charge
 */
class SplitUnderRepay extends \app\models\BaseModel
{
    const TYPE_SUCCESS = 1; //成功
    const TYPE_FAIL = 2;  //失败


    const FUND_XXDD = 1;  //小小黛朵
    const FUND_XHH = 2; //先花花
    const FUND_ZRYS =3; //智融钥匙
    const FUND_PXHT =4; //萍乡海桐   体外 7天
    //app\modules\balance\controllers\AdminController  main_body() 381行也得改

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cg_split_under_repay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['repay_id', 'loan_id', 'settle_amount', 'fund', 'parent_loan_id', 'create_time', 'settle_fee', 'jk_interest_fee','paybill'], 'required'],
            [['user_id', 'loan_id', 'fund', 'fund_party', 'jk_number', 'jk_settle_type', 'is_calculation', 'jk_status', 'status', 'days'], 'integer'],
            [['settle_amount', 'settle_fee', 'jk_amount', 'jk_interest_fee', 'withdraw_fee', 'total_interest', 'split_interest', 'split_principal','split_fine','repay_total_money','bill_money'], 'number'],
            [[ 'create_time', 'last_modify_time','repay_time','start_date','end_date'], 'safe'],
            [['remark'], 'string'],
            [['repay_id'], 'string', 'max' => 50],
            [['paybill'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'repay_id' => '（还款）订单号',
            'user_id' => '用户id',
            'loan_id' => '借款id（副）',
            'settle_amount' => '实际出款',
            'settle_fee' => '实际出款金额手续费（这个为准）',
            'fund' => '资金方（不太准确）',
            'fund_party' => '出款公司主体（这个为准）',
            'jk_amount' => '借款金额',
            'jk_interest_fee' => '借款利息',
            'jk_number' => '续期次数',
            'jk_settle_type' => '续期状态',
            'parent_loan_id' => '借款id（主）',
            'withdraw_fee' => '服务费',
            'is_calculation' => '是否有服务费',
            'jk_status' => '借款状态',
            'total_interest' => '总利息',
            'split_interest' => '本次还款拆分-利息',
            'split_principal' => '本次还款拆分-本金',
            'split_fine' => '本次还款拆分-罚息',
            'create_time' => '创建时间',
            'last_modify_time'=> '修改时间',
            'status'=>'状态',
            'remark'=> '备注',
            'repay_total_money'=>'本次账单之前的还款',

            'paybill'=>'交易流水号',
            'repay_time'=>'还款时间',
            'start_date'=>'起息日------征用成为还款最后修改时间 财务点击确认时间',
            'end_date'=>'到期日',
            'bill_money'=>'还款金额',



        ];
    }

    /**
     * 获取字符串形式状态
     * @param  string $status_str
     * @return int | []
     */
    public function gStatus($status_str=null){
        if($status_str){
            return $this->notifyMap[$status_str];
        }else{
            return $this->notifyMap;
        }
    }


    /**
     * 获取该订单号所有的还款记录
     * @return []
     */
    public function getAllRecord($parent_loan_id) {
        $where = ['parent_loan_id'=>$parent_loan_id];
        $dataList = self::find()->where($where)->all();
        if (!$dataList) {
            return null;
        }
        return $dataList;
    }


    /**
     * Undocumented function
     * 保存数据
     * @param [type] $postdata
     * @return void
     */
    public function saveData($postData){
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(false, '不能为空');
        }
        $re = $this->getOne(ArrayHelper::getValue($postData,'repay_id'),'repay_id');
        if($re){
            $new_time = date('Y-m-d H:i:s');
            $re->user_id            = ArrayHelper::getValue($postData, 'user_id', 0);
            $re->loan_id                = ArrayHelper::getValue($postData, 'loan_id', 0);
            $re->paybill                = ArrayHelper::getValue($postData, 'paybill', 0);
            $re->repay_time                = ArrayHelper::getValue($postData, 'repay_time', 0);
            $re->bill_money                = ArrayHelper::getValue($postData, 'bill_money', 0);

            $re->settle_amount        = ArrayHelper::getValue($postData, 'settle_amount', 0);
            $re->settle_fee                  = ArrayHelper::getValue($postData, 'settle_fee', 0);
            $re->fund          = ArrayHelper::getValue($postData, 'fund', 0);
            $re->fund_party         = ArrayHelper::getValue($postData, 'fund_party', 0);
            $re->jk_amount            = ArrayHelper::getValue($postData, 'jk_amount', 0);
            $re->jk_interest_fee           = ArrayHelper::getValue($postData, 'jk_interest_fee', 0);
            $re->jk_number                = ArrayHelper::getValue($postData, 'jk_number', '0');
            $re->jk_settle_type        = ArrayHelper::getValue($postData, 'jk_settle_type', 0);
            $re->parent_loan_id            = ArrayHelper::getValue($postData, 'parent_loan_id', 0);
            $re->withdraw_fee         = ArrayHelper::getValue($postData, 'withdraw_fee', '0');
            $re->is_calculation           = ArrayHelper::getValue($postData, 'is_calculation', '0');
            $re->jk_status               = ArrayHelper::getValue($postData, 'jk_status', '0');
            $re->start_date               = ArrayHelper::getValue($postData, 'start_date', '0');
            $re->end_date               = ArrayHelper::getValue($postData, 'end_date', '0');


            $re->total_interest  = ArrayHelper::getValue($postData, 'total_interest', 0);
            $re->split_interest   = ArrayHelper::getValue($postData, 'split_interest', 0);
            $re->split_principal     = ArrayHelper::getValue($postData, 'split_principal', 0);
            $re->split_fine   = ArrayHelper::getValue($postData, 'split_fine', 0);

            $re->last_modify_time  = $new_time;
            $re->status       = ArrayHelper::getValue($postData, 'status', 0);
            $re->remark       = ArrayHelper::getValue($postData, 'remark', '');

            $re->repay_total_money       = ArrayHelper::getValue($postData, 'repay_total_money', '0');
            $re->days       = ArrayHelper::getValue($postData, 'days', '0');
            $result =$re->update($postData);
            return $result;
        }
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(false, $error);
        }
        $result = $this->save();
        return $result;
    }

    /*
 * 根据id 查询单条语句
 * */
    public function getOne($id,$column='id'){
        if(empty($id)){
            return false;
        }
        $result = self::find()->where(['=' , $column , $id] )->one();
        return $result;
    }








    /**
     * 条件
     * @param $getdata
     * @return int|\yii\db\Query
     */
    public function paymentWhere($getdata){
        if(empty($getdata)){
            return 0;
        }
        $postdata= [
//            'fund_party'      => ArrayHelper::getValue($getdata, 'fund_party'),//公司主体
            'start_time'       => ArrayHelper::getValue($getdata, 'start_time'),//日期 开始
            'end_time'         => ArrayHelper::getValue($getdata, 'end_time'),//日期  结束
        ];
        $result = self::find()
            ->select([
                'date_format(start_date,\'%Y-%m-%d\') as repayTime',
                'sum(split_principal) as principal',
                'sum(split_interest) as interest',
                'sum(split_fine) as fine',
                'sum(bill_money) as total_money',
            ]);

        //公司主体
        if(!empty($postdata['fund_party'])){
            if($postdata['fund_party']==3){
                $result->andWhere(['in', self::tableName().'.fund_party' , [0,3]]);
            }else{
                $result->andWhere(['=', self::tableName().'.fund_party' , $postdata['fund_party']]);
            }
        }


//    start_date     征用成为还款最后修改时间 财务点击确认时间
        //开始时间
        if(!empty($postdata['start_time'])){
            $result->andWhere(['>=', self::tableName().'.start_date' , $postdata['start_time'].' 00:00:00']);
        }
        //结束时间
        if(!empty($postdata['end_time'])){
            $result->andWhere(['<=', self::tableName().'.start_date' , $postdata['end_time'].' 23:59:59']);
        }
//        $result->andWhere(['!=', self::tableName().'.paybill' , '0000']);    //这个条件是查线下银行还款的
        return  $result;
    }

    //获取日总条数
    public function getTotal( $filter_where)
    {
        if (empty($filter_where)){
            return false;
        }

        $result = $this->paymentWhere($filter_where);
        return $result
            ->GroupBy('repayTime')
            ->COUNT();
    }


    /**
     *  获取 总金额
     * @param $filter_where
     * @return mixed
     */
    public function getTotalMoney($filter_where,$type=0){
        $result = $this->paymentWhere($filter_where);
        return $result
            ->sum('bill_money');
    }



    /**
     * 获取本金总金额
     * @param $filter_where
     * @return mixed
     */
    public function split_principal($filter_where){
        $result = $this->paymentWhere($filter_where);
        return $result
            ->sum('split_principal');
    }

    /**
     * 获取利息总额
     * @param $filter_where
     * @return mixed
     */
    public function split_interest($filter_where){
        $result = $this->paymentWhere($filter_where);
        return $result
            ->sum('split_interest');
    }

    /**
     * 获取罚息总额
     * @param $filter_where
     * @return mixed
     */
    public function split_fine($filter_where){
        $result = $this->paymentWhere($filter_where);
        return $result
            ->sum('split_fine');
    }

    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     */
    public function getAllData( $filter_where,$pages)
    {
        $result = $this->paymentWhere($filter_where);
        if (empty($pages)){
            return $result
                ->groupBy('repayTime')
                ->orderBy('repayTime desc')
                ->asArray()
                ->all();
        }
        if (empty($filter_where)){
            return false;
        }

        return $result
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->groupBy('repayTime')
            ->orderBy('repayTime desc')
            ->asArray()
            ->all();
    }





}
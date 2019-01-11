<?php

namespace app\modules\balance\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
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
class SplitBusiness extends \app\models\BaseModel
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
        return 'cg_split_business';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['repay_id', 'bill_time', 'aid', 'mechart_num', 'channel_id', 'loan_id', 'settle_amount', 'fund', 'parent_loan_id', 'create_time', 'settle_fee', 'jk_interest_fee','bill_money','bill_service_charge'], 'required'],
            [['user_id', 'loan_id', 'fund', 'fund_party', 'jk_number', 'jk_settle_type', 'is_calculation', 'jk_status', 'status', 'channel_id', 'aid','days'], 'integer'],
            [['settle_amount', 'settle_fee', 'jk_amount', 'jk_interest_fee', 'withdraw_fee', 'total_interest', 'split_interest', 'split_principal','split_fine','bill_service_charge','bill_money','repay_total_money'], 'number'],
            [['bill_time', 'create_time', 'last_modify_time','bill_time'], 'safe'],
            [['remark'], 'string'],
            [['repay_id'], 'string', 'max' => 50],
            [['return_channel'], 'string', 'max' => 5],
            [['mechart_num'], 'string', 'max' => 100]
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
            'bill_time' => '账单时间',
            'create_time' => '创建时间',
            'last_modify_time'=> '修改时间',
            'status'=>'状态',
            'remark'=> '备注',
            'channel_id'=> '商编号id',
            'aid'=> '应用id',
            'mechart_num'=> '商编号',
            'repay_total_money'=>'本次账单之前的还款'
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
            $re->bill_service_charge       = ArrayHelper::getValue($postData, 'bill_service_charge', 0);
            $re->bill_money   = ArrayHelper::getValue($postData, 'bill_money', 0);
            $re->bill_time   = ArrayHelper::getValue($postData, 'bill_time', 0);
            $re->total_interest  = ArrayHelper::getValue($postData, 'total_interest', 0);
            $re->split_interest   = ArrayHelper::getValue($postData, 'split_interest', 0);
            $re->split_principal     = ArrayHelper::getValue($postData, 'split_principal', 0);
            $re->split_fine   = ArrayHelper::getValue($postData, 'split_fine', 0);
            $re->last_modify_time  = $new_time;
            $re->status       = ArrayHelper::getValue($postData, 'status', 0);
            $re->remark       = ArrayHelper::getValue($postData, 'remark', '');
            $re->channel_id       = ArrayHelper::getValue($postData, 'channel_id', 0);
            $re->aid       = ArrayHelper::getValue($postData, 'aid', 0);
            $re->mechart_num       = ArrayHelper::getValue($postData, 'mechart_num', '0');
            $re->return_channel       = ArrayHelper::getValue($postData, 'return_channel', '0');
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
            'fund_party'      => ArrayHelper::getValue($getdata, 'fund_party'),//公司主体
            'mechart_num'     => ArrayHelper::getValue($getdata, 'mechart_num'),//商编号
            'aid'             => ArrayHelper::getValue($getdata, 'aid'),//通道id
            'start_time'       => ArrayHelper::getValue($getdata, 'start_time'),//日期 开始
            'end_time'         => ArrayHelper::getValue($getdata, 'end_time'),//日期  结束
        ];
        $result = self::find()
            ->select(['bill_time','return_channel','mechart_num','sum(bill_service_charge) as service','sum(bill_money) as money',
            'sum(split_principal) as principal','sum(split_interest) as interest','sum(split_fine) as fine',
                'sum(bill_service_charge) as charge','fund_party as party','days']);

        //公司主体
        if(!empty($postdata['fund_party'])){
            if($postdata['fund_party']==3){
                $result->andWhere(['in', self::tableName().'.fund_party' , [0,3]]);
            }else{
                $result->andWhere(['=', self::tableName().'.fund_party' , $postdata['fund_party']]);
            }
        }
        //商编号
        if(!empty($postdata['mechart_num'])){
            $result->andWhere(['in', self::tableName().'.mechart_num',$postdata['mechart_num'] ]);
        }
        //通道id
        if(!empty($postdata['aid'])){
            $result->andWhere(['=', self::tableName().'.aid', $postdata['aid']]);
        }
        //开始时间
        if(!empty($postdata['start_time'])){
            $result->andWhere(['>=', self::tableName().'.bill_time' , $postdata['start_time']]);
        }
        //结束时间
        if(!empty($postdata['end_time'])){
            $result->andWhere(['<=', self::tableName().'.bill_time' , $postdata['end_time']]);
        }
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
            ->GroupBy('bill_time,mechart_num')
            ->COUNT();
    }
//获取月总条数
    public function getTotalMouth( $filter_where)
    {
        if (empty($filter_where)){
            return false;
        }

        $result = $this->paymentWhere($filter_where);
        return $result
            ->GroupBy('bill_time,mechart_num')
            ->COUNT();
    }
    /**
     *  获取 总金额
     * @param $filter_where
     * @return mixed
     */
    public function getTotalMoney($filter_where,$type=0){

        if($type==3){
            $filter_where['aid'] = 10;
            //$filter_where['fund_party'] = 0;
        }
        $result = $this->paymentWhere($filter_where);
        return $result
            ->sum('bill_money');
    }

    /**
     * 获取总手续费
     * @param $filter_where
     * @return mixed
     */
    public function getTotalServiceCharge($filter_where,$type=0){
        if($type==3){
            $filter_where['aid'] = 10;
        }
        $result = $this->paymentWhere($filter_where);
        return $result
            ->sum('bill_service_charge');
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
        if (empty($pages)){
            return false;
        }
        if (empty($filter_where)){
            return false;
        }
        $result = $this->paymentWhere($filter_where);
        return $result
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->groupBy('bill_time,mechart_num')
            ->orderBy(self::tableName().'.bill_time desc')
            ->asArray()
            ->all();
    }

    /**
     * 月对账条件查询
     * @param $pages
     * @param $filter_where
     */
    public function mouthAllData( $filter_where,$pages)
    {
        if (empty($pages)){
            return false;
        }
        if (empty($filter_where)){
            return false;
        }
        $result = $this->paymentWhere($filter_where);
        return $result
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->groupBy('mechart_num,fund_party,days')
            ->orderBy(self::tableName().'.bill_time desc')
            ->asArray()
            ->all();
    }
    /**
     * @param $filter_where
     * 导出数据
     */

    public function accountWhere( $filter_where)
    {

        if (empty($filter_where)){
            return false;
        }

        $result = $this->paymentWhere($filter_where);
        return $result
            ->groupBy('mechart_num,fund_party,days')
            ->orderBy(self::tableName().'.bill_time desc')
            ->asArray()
            ->all();
    }
/*---------------------------------明细-----------------------------------------*/

    public function detailedWhere($getdata){
        if(empty($getdata)){
            return 0;
        }
        $postdata= [
            'bill_time'     => ArrayHelper::getValue($getdata, 'bill_time'),//账单时间
            'mechart_num'       => ArrayHelper::getValue($getdata, 'mechart_num'),//商编号
        ];
        $result = self::find()
            ->select(['bill_time','mechart_num',
                'coalesce(sum(split_interest),0) AS interest',
                'coalesce(sum(split_principal),0) AS principal',
                'coalesce(sum(split_fine),0) AS fine',
                'coalesce(sum(bill_money),0) AS money',
                'coalesce(sum(bill_service_charge),0) AS service'
            ]);
        //商编号
        if(!empty($postdata['mechart_num'])){
            $result->andWhere(['=', self::tableName().'.mechart_num', $postdata['mechart_num']]);
        }
        //账单时间
        if(!empty($postdata['bill_time'])){
            $result->andWhere(['=', self::tableName().'.bill_time' , $postdata['bill_time']]);
        }
        return  $result;
    }


    /**
     * 获取小小黛朵  拆账数据
     * @param $filter_where
     * @return mixed
     */
    public function getXXDDData($filter_where){
        $result = $this->detailedWhere($filter_where);
        $result->andWhere(['=', 'fund_party' , self::FUND_XXDD]);
        $result->andWhere(['!=', 'aid' , '10']);
        return $result->asArray()->one();
    }

    /**
     * 获取小小黛朵  拆账数据
     * @param $filter_where
     * @return mixed
     */
    public function getXHHData($filter_where){
        $result = $this->detailedWhere($filter_where);
        $result->andWhere(['=', 'fund_party' , self::FUND_XHH]);
        $result->andWhere(['!=', 'aid' , '10']);
        return $result->asArray()->one();
    }

    /**
     * 获取  智融钥匙  拆账数据
     *      aid  为 10  就是智融钥匙
     * @param $filter_where
     * @return mixed
     */
    public function getZRYSData($filter_where){
        $result = $this->detailedWhere($filter_where);
        $result->andWhere(['=', 'fund_party' , 3]);
        $result->andWhere(['=', 'aid' , 10]);
        return $result->asArray()->one();
    }


}
<?php

namespace app\modules\balance\models\yx;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\balance\models\yx\YxUser;
use app\modules\balance\models\yx\OrderPay;
use app\modules\balance\models\yx\CouponList;
use app\models\Channel;


class Yxious extends \app\modules\balance\models\yx\YxBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_ious';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id','status', 'chase_amount', 'start_time', 'end_time', 'invalid_time','create_time','last_modify_time','version'], 'required'],
            [['user_id', 'order_id'], 'integer'],
            [['create_time', 'last_modify_time','invalid_time'], 'safe'],
            [['create_time', 'limit_start_time', 'limit_end_time'], 'string', 'max' => 50],

        ];
    }

    /**
     * @inheritdoc
     * @yx_ious 逾期表 白条
     * @yx_order_pay 订单表
     * @yx_user 用户表
     * @yx_order_fail 退卡表
     * @yx_coupon_apply 优惠券表
     */
    public function attributeLabels() {

        return [
            'id' => '主键',//逾期表id yx_ious
            'order_pay_no' => '订单号', //yx_order_pay
            'paybill' => '商户订单号', //yx_ious
            'creat_time' => '创建id', //yx_ious
            'realname' => '姓名', //yx_user
            'shangbian' => '商编号',
            '收款通道' => 'platform', //yx_order_pay
            '应还款日期' => 'end_time', //yx_ious
            '白条金额' => 'money', //yx_ious
            '延期服务费' => 'chase_amount', //yx_ious
            '结算状态' => 'status', //yx_ious
            '付款日期' => 'repay_time', //yx_order_pay
            '手机号' => 'mobile', //yx_user

        ];
    }

/**
**连接订单表获取订单号
 **/
    public function getOrderpayno() {
        return $this->hasOne(OrderPay::className(), ['order_id' => 'order_id']);
    }

    /**
     **连接user表获取用户信息
     **/
    public function getUser() {
        ////参数一 关联Model名   参数二 关联字段 不能写表.t_id 自己默认后边是本Model的表id  前边是关联表的id
        return $this->hasOne(Yxuser::className(), ['user_id' => 'user_id']);
    }

    public function check($getdata){

        if(empty($getdata)){
            return 0;
        }

        $postdata= [

            'order_pay_no'     => ArrayHelper::getValue($getdata, 'order_pay_no'),//订单号
            'paybill'          => ArrayHelper::getValue($getdata, 'paybill'),//商户订单号
            //'realname'         => ArrayHelper::getValue($getdata, 'realname'),//姓名
            //'mobile'           => ArrayHelper::getValue($getdata, 'mobile'),//电话号码
           // 'channel_id'         => ArrayHelper::getValue($getdata, 'channel_id'),//商编号
            'is_end'           => ArrayHelper::getValue($getdata, 'is_end'),//是否到期 10是 20否
            'status'           => ArrayHelper::getValue($getdata, 'status'),//结算状态 8已结清 9未结清
            'repay_time'       => ArrayHelper::getValue($getdata, 'repay_time'),//付款日期 开始
            'end_time'         => ArrayHelper::getValue($getdata, 'end_time'),//付款日期  结束
        ];
        //var_dump($postdata);die;
        $result = self::find()->select(
            [self::tableName().'.id',self::tableName().'.user_id',
                self::tableName().'.end_time',self::tableName().'.start_time',self::tableName().'.create_time',
                self::tableName().'.money',self::tableName().'.chase_amount',self::tableName().'.status',
                //YxUser::tableName().'.realname',YxUser::tableName().'.mobile',
                OrderPay::tableName().'.order_pay_no',OrderPay::tableName().'.repay_time',
                OrderPay::tableName().'.paybill',OrderPay::tableName().'.actual_money',OrderPay::tableName().'.coupon_id',
                //CouponList::tableName().'.val',
            ])
           // ->leftjoin(YxUser::tableName(),YxUser::tableName().".user_id=".self::tableName().".user_id")
           ->leftjoin(CouponList::tableName(),CouponList::tableName().".apply_id=".self::tableName().".coupon_id")
            ->leftjoin(OrderPay::tableName(),OrderPay::tableName().".order_id=".self::tableName().".order_id");
        //var_dump($postdata);die;
        //姓名  用户表
       /* if(!empty($postdata['realname'])){

            $result->andWhere([ YxUser::tableName().'.realname' => $postdata['realname']]);
        }
        //手机号 用户表
        if(!empty($postdata['mobile'])){

            $result->andWhere([ YxUser::tableName().'.mobile' => $postdata['mobile']]);
        }*/
        //订单号  订单表
        if(!empty($postdata['order_pay_no'])){

            $result->andWhere([ OrderPay::tableName().'.order_pay_no' => $postdata['order_pay_no']]);


        }
        //商户订单号  订单表
        if(!empty($postdata['paybill'])){

            $result->andWhere([ OrderPay::tableName().'.paybill' => $postdata['paybill']]);
        }

        //商编号
        if (!empty($postdata['channel_id'])){

            $object = new Channel();
            $channel_id = $object->find()->where(['mechart_num'=>$postdata['channel_id']])->one();
           // $sql = $channel_id->createCommand()->getRawsql();//获取上次sql
            if($channel_id){
                $result->andWhere([ OrderPay::tableName().'.channel_id' => $channel_id['id']]);
            }else{
                $result->andWhere([ OrderPay::tableName().'.channel_id' => 0]);

            }

        }

       /* //付款日期 开始 订单表
        if(!empty($postdata['repay_time'])){

            $result->andWhere(['>=', OrderPay::tableName().'.repay_time' , $postdata['repay_time'].'00:00:00']);
        }
        //付款日期 结束 订单表
        if(!empty($postdata['end_time'])){

            $result->andWhere(['<=', self::tableName().'.end_time' , $postdata['end_time'].'23:59:59']);
        }*/
        //付款日期 开始 订单表
        if(!empty($postdata['repay_time'])){

            $result->andWhere(['>=', self::tableName().'.last_modify_time' , $postdata['repay_time'].'00:00:00']);
        }
        //付款日期 结束 订单表
        if(!empty($postdata['end_time'])){

            $result->andWhere(['<=', self::tableName().'.last_modify_time' , $postdata['end_time'].'23:59:59']);
        }


        //是否到期 10是 20否
        if(!empty($postdata['is_end'])){

            if($postdata['is_end']==10){

                $result->andWhere(['<', self::tableName().'.end_time' , date('Y-m-d H:i:s')]);
            }
        }

        //是否到期 10是 20否
        if(!empty($postdata['is_end'])){
            if($postdata['is_end']==20){
             $result->andWhere(['>=', self::tableName().'.end_time' ,date('Y-m-d H:i:s')]);
            }
        }
        //结算状态  8已结清 9未结清

        if(!empty($postdata['status'])){
            if($postdata['status']==8){
                $result->andWhere([ self::tableName().'.status'=>8]);
            }
        }
        //结算状态  8已结清 9未结清

        if(!empty($postdata['status'])){
            if($postdata['status']==9){
                $result->andWhere([ self::tableName().'.status'=>9]);
            }
        }
        //订单的状态
        #$result->andWhere([ OrderPay::tableName().'.status' => 1]);
        return  $result;
    }
    /**
     * 总条数
     * @param $filter_where
     * @return int
     */
    public function getTotal($getdata)
    {
        if (empty($getdata)){
            return 0;
        }
        $result = $this->check($getdata);
        //var_dump($result);die;
        $total = $result->count();
        //var_dump($total);die;
        return empty($total) ? 0 : $total;
    }

    /*
     *  获取时间区间和条件中的总和
     * */
    public function getSum($getdata,$field)
    {
        if (empty($getdata)){
            return 0;
        }
        if (empty($field)){
            return 0;
        }
        $result = $this->check($getdata);
        //$total = $result->sum(self::tableName().'.money');
        $total = $result->sum(self::tableName().'.'.$field);
        //var_dump($total);die;
        return empty(Number_format($total,2)) ? 0 : Number_format($total,2);
    }
    /*
    **获取实收累计金额
    */
    public function moneySum($getdata,$field)
    {
        if (empty($getdata)){
            return 0;
        }
        if (empty($field)){
            return 0;
        }
        $result = $this->check($getdata);
        $total = $result->sum(OrderPay::tableName().'.'.$field);
        return empty(Number_format($total,2)) ? 0 : Number_format($total,2);
    }
    /*
     * 获取优惠券总金额
     */
    public function CouponSum($getdata,$field)
    {
        if (empty($getdata)){
            return 0;
        }
        if (empty($field)){
            return 0;
        }
        $result = $this->check($getdata);
        $total = $result->sum(CouponList::tableName().'.'.$field);
        return empty(Number_format($total,2)) ? 0 : Number_format($total,2);
    }
    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     */
    public function getAllData( $getdata,$pages)
    {
        if (empty($getdata)){
            return false;
        }
        if (empty($getdata)){
            return false;
        }
        $result = $this->check($getdata);

        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(OrderPay::tableName().'.create_time desc')
            #->groupBy('channel_id, payment_date')
            ->asArray()
            ->all();
    }
}
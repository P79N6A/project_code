<?php

namespace app\modules\balance\models\yx;
use app\modules\balance\models\yx\YxUser;
use app\modules\balance\models\yx\CouponList;
use app\models\Channel;
use Yii;

/**
 * This is the model class for table "yx_order_pay".
 *
 * @property integer $id
 * @property integer $order_pay_no
 * @property integer $user_id
 * @property integer $order_id
 * @property integer $bank_id
 * @property integer $platform
 * @property string $pay_type
 * @property string $source
 * @property string $status
 * @property string $money
 * @property string $actual_money
 * @property string $paybill
 * @property integer $channel_id
 * @property integer $pic_repay1
 * @property integer $pic_repay2
 * @property integer $pic_repay3
 * @property string $coupon_id
 * @property integer $return_code
 * @property integer $return_msg
 * @property string $repay_time
 * @property string $last_modify_time
 * @property string $create_time
 */
class OrderPay extends \app\modules\balance\models\YxBase
{

    const STATUS_SUCCESS = 1; //成功
    #const TYPE_FAIL = 2;  //失败


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_order_pay';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_pay_no', 'user_id', 'platform', 'source', 'status', 'money', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'order_id', 'bank_id', 'pay_type', 'platform', 'source', 'status', 'channel_id'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'create_time', 'repay_time'], 'safe'],
            [['return_code','paybill','order_pay_no'], 'string', 'max' => 64],
            [['pic_repay3', 'pic_repay2', 'pic_repay1'], 'string', 'max' => 128],
            [['return_msg'], 'string', 'max' => 255]
        ];

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_pay_no' => '订单号',
            'user_id' => '用户ID',
            'order_id' => '订单ID',
            'bank_id' => '银行卡ID',
            'platform' => '支付平台1：2：3：微信',
            'pay_type' => '支付方式1:正常支付2:白条支付',
            'source' => '来源1：微信、2：android、3：IOS',
            'status' => '状态 0：初始、-1：支付中、1：成功、2：失败、3：失效',
            'money' => '支付金额',
            'actual_money' => '实际支付金额',
            'paybill' => '流水号',
            'channel_id' => 'Channel Id',
            'pic_repay1' => '还款凭证URL',
            'pic_repay2' => '',
            'pic_repay3' => '',
            'coupon_id' => '优惠卷id',
            'return_code' => '返回状态',
            'return_msg' => '返回消息',
            'repay_time' => '还款时间',
            'last_modify_time' => '最后修改时间',
            'create_time'=> '创建时间',
        ];
    }




    /**
     * 初始条件
     * @param $filter_where
     * @return int|\yii\db\ActiveQuery
     */
    private function paymentWhere($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find()
            ->select(
                [self::tableName().'.id', self::tableName().'.order_pay_no', self::tableName().'.user_id',
                    self::tableName().'.source', self::tableName().'.status', self::tableName().'.money', self::tableName().'.actual_money',
                    self::tableName().'.paybill', self::tableName().'.channel_id', self::tableName().'.coupon_id',
                    self::tableName().'.repay_time', self::tableName().'.last_modify_time', self::tableName().'.create_time',
//                CouponList::tableName().'.val',
//                YxUser::tableName().'.realname', YxUser::tableName().'.mobile',
                ]
            );
//            ->LeftJoin(YxUser::tableName(),YxUser::tableName().".user_id=".self::tableName().".user_id")
//            ->LeftJoin(CouponList::tableName(),CouponList::tableName().".apply_id=".self::tableName().".coupon_id");
        //姓名  用户表
//        if (!empty($filter_where['realname'])){
//            $result->andWhere([ YxUser::tableName().'.realname' => $filter_where['realname']]);
//        }
//        //手机号  用户表
//        if (!empty($filter_where['mobile'])){
//            $result->andWhere([ YxUser::tableName().'.mobile' => $filter_where['mobile']]);
//        }
        //订单号
        if (!empty($filter_where['order_pay_no'])){
            $result->andWhere([ self::tableName().'.order_pay_no' => $filter_where['order_pay_no']]);
        }
        //商户订单号
        if (!empty($filter_where['paybill'])){
            $result->andWhere([self::tableName().'.paybill'=> $filter_where['paybill']]);
        }
        //商编号
        if (!empty($filter_where['channel_id'])){

            $object = new Channel();
            $channel_id = $object->find()->where(['mechart_num'=>$filter_where['channel_id']])->one();
            if($channel_id){
                $result->andWhere([self::tableName().'.channel_id' => $channel_id['id']]);
            }else{
                $result->andWhere([self::tableName().'.channel_id' => 0]);

            }

            //$result->andWhere([ self::tableName().'.channel_id' => $channel_id['id']]);

           // $result->andWhere([self::tableName().'.channel_id'=> $filter_where['channel_id']]);
        }
        //还款的开始结束时间
        if (!empty($filter_where['repay_start_time'])){
            $result->andWhere(['>=',self::tableName().'.last_modify_time' , $filter_where['repay_start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['repay_end_time'])){
            $result->andWhere(['<=',self::tableName().'.last_modify_time' , $filter_where['repay_end_time']. ' 23:59:59']);
        }
        //创建开始结束时间
        if (!empty($filter_where['create_start_time'])){
            $result->andWhere(['>=', self::tableName().'.create_time', $filter_where['create_start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['create_end_time'])){
            $result->andWhere(['<=', self::tableName().'.create_time', $filter_where['create_end_time']. ' 23:59:59']);
        }
        $result->andWhere([self::tableName().'.status' => 1]);
        return $result;
    }



    /**
     * 购卡订单条数
     * @param $filter_where
     * @return int
     */
    public function getTotal($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }

    /*
     *  获取时间区间和条件中的总和
     * */
    public function getSum($filter_where,$field)
    {
        if (empty($filter_where)){
            return 0;
        }
        if (empty($field)){
            return 0;
        }
        $result = $this->paymentWhere($filter_where);
        $total = $result->sum($field);
        return empty(Number_format($total,2)) ? 0 : Number_format($total,2);
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
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(self::tableName().'.create_time desc')
            ->asArray()
            ->all();
    }













}
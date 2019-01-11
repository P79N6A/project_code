<?php

namespace app\modules\balance\models\yx;

use Yii;
use app\models\Channel;
/**
 * This is the model class for table "yx_order_fail".
 *
 * @property integer $id
 */
class OrderFail extends \app\modules\balance\models\yx\YxBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_order_fail';
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
                [
                    self::tableName().'.id',self::tableName().'.last_modify_time',self::tableName().'.status',self::tableName().'.create_time',
                    OrderPay::tableName().'.order_pay_no', OrderPay::tableName().'.user_id', OrderPay::tableName().'.channel_id',
                    OrderPay::tableName().'.status as order_status', OrderPay::tableName().'.money', OrderPay::tableName().'.actual_money',
                    OrderPay::tableName().'.paybill', OrderPay::tableName().'.channel_id', OrderPay::tableName().'.repay_time',OrderPay::tableName().'.order_id',
                    YxUser::tableName().'.realname', YxUser::tableName().'.mobile',]
            )
            ->LeftJoin(OrderPay::tableName(),OrderPay::tableName().".order_id=".self::tableName().".order_id")
            ->LeftJoin(YxUser::tableName(),YxUser::tableName().".user_id=".self::tableName().".user_id");

        //退卡方式  暂时不用
        /*if (!empty($filter_where['realname'])){
            $result->andWhere([ YxUser::tableName().'.realname' => $filter_where['realname']]);
        }*/
        //姓名  用户表
        if (!empty($filter_where['realname'])){
            $result->andWhere([ YxUser::tableName().'.realname' => $filter_where['realname']]);
        }
        //手机号  用户表
        if (!empty($filter_where['mobile'])){
            $result->andWhere([ YxUser::tableName().'.mobile' => $filter_where['mobile']]);
        }
        //退卡订单号
        if (!empty($filter_where['id'])){
            $result->andWhere([self::tableName().'.id' => $filter_where['id']]);
        }
        //退卡状态
        if (!empty($filter_where['status']) || $filter_where['status'] == '0'){
            $result->andWhere([self::tableName().'.status' => $filter_where['status']]);
        }

        //订单号
        if (!empty($filter_where['order_pay_no'])){
            $result->andWhere([ OrderPay::tableName().'.order_pay_no' => $filter_where['order_pay_no']]);
        }
        //商户订单号
        if (!empty($filter_where['paybill'])){
            $result->andWhere([OrderPay::tableName().'.paybill' => $filter_where['paybill']]);
        }
        //商编号
        if (!empty($filter_where['channel_id'])){
            $object = new Channel();
            $channel_id = $object->find()->where(['mechart_num'=>$filter_where['channel_id']])->one();
            if($channel_id){
                $result->andWhere([ OrderPay::tableName().'.channel_id' => $channel_id['id']]);
            }else{
                $result->andWhere([ OrderPay::tableName().'.channel_id' => 0]);

            }
            //$result->andWhere([ OrderPay::tableName().'.channel_id' => $channel_id['id']]);
            //$result->andWhere([OrderPay::tableName().'.channel_id' => $filter_where['channel_id']]);
        }
        //退卡创建开始结束时间
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=',self::tableName().'.last_modify_time' , $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=',self::tableName().'.last_modify_time' , $filter_where['end_time']. ' 23:59:59']);
        }
        $result->andWhere([OrderPay::tableName().'.status' => 1]);

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
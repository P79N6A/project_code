<?php

namespace app\modules\balance\models\zrys;

use Yii;
use yii\helpers\ArrayHelper;


class ZrysPostpone extends \app\modules\balance\models\zrys\ZrysBase
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
            'order_id' => '商户订单号', //yx_ious
            'creat_time' => '创建id', //yx_ious
            'realname' => '姓名', //yx_user
            'shangbian' => '商编号',
            '收款通道' => 'platform', //yx_order_pay
            '应还款日期' => 'end_time', //yx_ious
            '白条金额' => 'money', //yx_ious
            '延期服务费' => 'chase_amount_money', //yx_ious
            '结算状态' => 'status', //yx_ious
            '付款日期' => 'repay_time', //yx_order_pay
            '手机号' => 'mobile', //yx_user

        ];
    }
    /**
     * 还款渠道
     */
    public static function getRepaymentChannel() {
        return [
            '101' => 3, //易宝投资通
            '102' => 2, //易宝一键支付
            '104' => 6, //连连支付（一亿元）
            '107' => 9, //宝付认证支付（一亿元）
            '108' => 10, //连连认证支付（花生米富）
            '109' => 11, //易宝代扣
            '110' => 12, //融宝快捷（一亿元）
            '112' => 13, //融宝快捷（米富）
            '113' => 14, //宝付（一亿元）
            '114' => 15, //宝付（米富）
            '128' => 16, //融宝(逾期)
            '123' => 17, //宝付（逾期）
            '117' => 18,//畅捷
            '131' => 19, //畅捷快捷
            '105' => 20, //融宝快捷（花生米富）
            '106' => 21, //宝付代扣
            '139' => 22, //新微信
            '140' => 23, //新支付宝 废弃
            '141' => 24, //新微信（逾期）
            '142' => 25, //新支付宝（逾期）
            '147' => 26, //存管还款
            '150' => 27, //存管还款（新）
            '153' => 28, //支付宝（新）
        ];
    }

    /**
     * 还款渠道后台显示
     */
    public static function showRepaymentChannel() {
        return [
            '1' => '线下',
            '2' => '易宝一键支付', //易宝一键支付
            '3' => '易宝投资通', //易宝投资通
            '4' => '微信支付',
            '5' => '支付宝',
            '6' => '连连支付（一亿元）', //连连支付（一亿元）
            '7' => '微信（逾期）', //微信逾期还款
            '8' => '支付宝（逾期）', //支付宝逾期还款
            '9' => '宝付认证支付（一亿元）', //宝付认证支付（一亿元）
            '10' => '连连认证支付（花生米富）', //连连认证支付（花生米富）
            '11' => '易宝代扣', //易宝代扣
            '12' => '融宝快捷（一亿元）', //融宝快捷（一亿元）
            '13' => '融宝快捷（米富）', //融宝快捷（一亿元）
            '14' => '宝付快捷（一亿元）', //融宝快捷（一亿元）
            '15' => '宝付快捷（米富）', //融宝快捷（一亿元）
            '16' => '融宝(逾期)',
            '17' => '宝付(逾期)',
            '18' => '畅捷出款',
            '19' => '畅捷快捷',
            '20' => '存管',//原新支付宝
            '21' => '新支付宝(逾期)',//废弃
            '22' => '新微信',
            '23' => '新支付宝',
            '24' => '新微信(逾期)',
            '25' => '新支付宝(逾期)',
            '26' => '存管还款',
            '28' => '支付宝（新）'
        ];
    }
/**
**连接订单表获取订单号
 **/
    public function getOrderpayno() {
        return $this->hasOne(yx_order_pay::className(), ['order_id' => 'order_id']);
    }

    /**
     **连接user表获取用户信息
     **/
    public function getUser() {
        return $this->hasOne(yx_user::className(), ['user_id' => 'user_id']);
    }

    public function check($getdata){

        $postdata= [

            'order_pay_no'          => ArrayHelper::getValue($getdata, 'order_pay_no'),//订单号
            'order_id'      => ArrayHelper::getValue($getdata, 'order_id'),//商户订单号
            'realname'      => ArrayHelper::getValue($getdata, 'realname'),//姓名
            'mobile'      => ArrayHelper::getValue($getdata, 'mobile'),//电话号码
            'shangbian'      => ArrayHelper::getValue($getdata, 'shangbian'),//商编号
            'is_end'      => ArrayHelper::getValue($getdata, 'is_end'),//是否到期 10是 20否
            'status'      => ArrayHelper::getValue($getdata, 'status'),//结算状态 60已结清 70未结清
            'repay_time'      => ArrayHelper::getValue($getdata, 'repay_time'),//付款日期 开始
            'end_time'      => ArrayHelper::getValue($getdata, 'end_time'),//付款日期  结束
        ];
        //$data = static::find()->where(['id'=>$id])->one();

        if(empty($postdata['order_pay_no'])==''){

            $arrays['order_pay_no'] = $postdata['order_pay_no'];
        }
        if(empty($postdata['order_id'])==''){

            $arrays['order_id'] = $postdata['order_id'];
        }
        if(empty($postdata['realname'])==''){

            $arrays['realname'] = $postdata['realname'];
        }
        if(empty($postdata['mobile'])==''){

            $arrays['mobile'] = $postdata['mobile'];
        }
        if(empty($postdata['shangbian'])==''){

            $arrays['shangbian'] = $postdata['shangbian'];
        }
        if(empty($postdata['is_end'])==''){

            $arrays['is_end'] = $postdata['is_end'];
        }
        if(empty($postdata['status'])==''){

            $array['status'] = $postdata['status'];
        }
        if(empty($postdata['repay_time'])==''){

            $arrays['repay_time'] = $postdata['repay_time'];
        }
        if(empty($postdata['end_time'])==''){

            $arrays['end_time'] = $postdata['end_time'];
        }
        var_dump($arrays);die;
    }

}
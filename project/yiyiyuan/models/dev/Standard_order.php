<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Standard_order extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_standard_order';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getStatistics() {
        return $this->hasOne(Standard_statistics::className(), ['user_id' => 'user_id', 'standard_id' => 'standard_id']);
    }

    public function addStandardOrder($user,$standard_info,$invest_share,$profit,$coupon_id) {
        $now_time = date('Y-m-d H:i:s');
        //开始购买标的
        //如果用户是首次购买，则同时在用户投资标的总资产统计表，标的进度信息表，用户标的统计信息表，标的订单信息表，资金交易流水信息表中保存一条记录
        //保存一条标的订单信息
        $standard_order = new Standard_order();
        $standard_order->version = 1;
        $standard_order->order_no = date('YmdHis') . rand(100000, 999999);
        $standard_order->standard_id = $standard_info->id;
        $standard_order->user_id = $user->user_id;
        $standard_order->cupon_id = $coupon_id;
        $standard_order->buy_type = 'GENE';
        $standard_order->goods_name = $standard_info->name;
        $standard_order->goods_desc = $standard_info->desc;
        $standard_order->org_number = $standard_info->number;
        $standard_order->buy_amount = $invest_share;
        $standard_order->buy_share = $invest_share;
        $standard_order->order_status = 'SUCCESS';
        $standard_order->achieved_interest = 0;
        $standard_order->achieving_interest = $profit;
        $standard_order->start_date = $standard_info->start_date;
        $standard_order->end_date = $standard_info->end_date;
        $standard_order->last_modify_time = $now_time;
        $standard_order->create_time = $now_time;
        $result = $standard_order->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }

    //已投资额度
    public function getBuyAmount($standard_id) {
        if (empty($standard_id)) {
            return null;
        }
        $invest_list = Standard_order::find()->select(Standard_order::tableName() . '.buy_share')->joinWith('statistics', true, 'LEFT JOIN')->where([Standard_order::tableName() . '.standard_id' => $standard_id])->all();
        return $invest_list;
    }

}

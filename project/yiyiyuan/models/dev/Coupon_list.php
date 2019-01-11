<?php

namespace app\models\dev;

use yii\db\Query;

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
class Coupon_list extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_coupon_list';
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

    public function getCouponuse() {
        return $this->hasOne(Coupon_use::className(), ['discount_id' => 'id']);
    }

    /**
     * 根据手机号查询优惠券
     * @param type $mobile
     * @param int $status 1：未使用；2：已使用；3：过期；4：所有
     * @return type
     */
    public function getCouponByMobile($mobile, $status = 1, $order = '') {
        if (empty($mobile)) {
            return null;
        }
        $now_time = date('Y-m-d H:i:s');
        if ($status == 4) {
            $coupon = Coupon_list::find()->where(['mobile' => $mobile]);
        } else {
            $coupon = Coupon_list::find();
            if ($status == 1) {
                $coupon = $coupon->where(['status' => $status, 'mobile' => $mobile])->andWhere("end_date > '$now_time'");
            } else {
                if ($status == 3) {
                    $coupon = $coupon->where(['status' => $status])->orWhere("end_date < '$now_time'");
                }else{
                    $coupon = $coupon->orWhere(['status' => $status]);                    
                }
                $coupon = $coupon->andWhere(['mobile' => $mobile]);
            }
        }
        if (!empty($order)) {
            $coupon = $coupon->orderBy($order);
        }
        $couponlist = $coupon->all();
        return $couponlist;
    }

    public function getAllCoupon($mobile, $status = 1, $order = '') {
        if ($status != 4) {
            if ($status == 1) {
                $coupon_sql = "select `id`,`mobile`,`title`,`end_date`,`status`,'loan' as 'coupon_type',`val`,`limit` from `yi_coupon_list` where `mobile`='" . $mobile . "' and `status`='" . $status . "' and `end_date`>'" . date('Y-m-d H:i:s') . "'";
                $standard_sql = "select `id`,`mobile`,`title`,`end_date`,`status`,'invest' as 'coupon_type',`cycle` as 'val',`field` as 'limit' from `yi_standard_coupon_list` where `mobile`='" . $mobile . "' and `status`='" . $status . "' and `end_date`>'" . date('Y-m-d H:i:s') . "'";
            } else {
                $coupon_sql = "select `id`,`mobile`,`title`,`end_date`,`status`,'loan' as 'coupon_type',`val`,`limit` from `yi_coupon_list` where `mobile`='" . $mobile . "' and `status`='" . $status . "'";
                $standard_sql = "select `id`,`mobile`,`title`,`end_date`,`status`,'invest' as 'coupon_type',`cycle` as 'val',`field` as 'limit' from `yi_standard_coupon_list` where `mobile`='" . $mobile . "' and `status`='" . $status . "'";
            }
        } else {
            $coupon_sql = "select `id`,`mobile`,`title`,`end_date`,`status`,'loan' as 'coupon_type',`val`,`limit` from `yi_coupon_list` where `mobile`='" . $mobile . "'";
            $standard_sql = "select `id`,`mobile`,`title`,`end_date`,`status`,'invest' as 'coupon_type',`cycle` as 'val',`field` as 'limit' from `yi_standard_coupon_list` where `mobile`='" . $mobile . "'";
        }
        $sql = "(" . $coupon_sql . ") union (" . $standard_sql . ") order by " . $order;
        $ret = \Yii::$app->db->createCommand($sql)->queryAll();
        return $ret;
    }

    public function updateCouponList($condition = array()) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $result = $this->save();
        if ($result) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * 获取某一用户可用的优惠券
     * 
     */
    public function getValids($mobile) {
        $couponlist = $this->getCouponByMobile($mobile, 1, 'end_date ASC');
        if (!$couponlist) {
            return null;
        }
        $data = \yii\helpers\ArrayHelper::toArray($couponlist);

        // @todo
        /* $data = static::find()
          ->select(['id','val','end_date'])
          ->where(['status' => 1,])
          ->limit(20)
          ->asArray(['val','end_date'])
          ->all(); */
        // end todo
        usort($data, [$this, 'sortCouponList']);
        return $data;
    }

    /**
     * 

      优惠券：判断当前优惠券账户是否有可用的借款优惠券，
      优先匹配全免券，如果有两张以上全免券则优先匹配当前距有效期最接近的全免券，
      其次匹配金额券，按照最大面值的优惠依次匹配，如果面值相同则优先匹配当前距有效期最接近的优惠券；
      如果没有则优惠券项整个置灰显现
     * 
     */
    public function sortCouponList($o1, $o2) {
        if ($o1['val'] != $o2['val']) {
            if ($o1['val'] == 0) {
                return -1;
            } elseif ($o2['val'] == 0) {
                return 1;
            } else {
                // 从大到小排序
                return $o1['val'] > $o2['val'] ? -1 : 1;
            }
        } else {
            // 从小到大排序
            return $o1['end_date'] < $o2['end_date'] ? -1 : 1;
        }
    }

}

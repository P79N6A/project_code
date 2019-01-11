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
class Coupon_apply extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_coupon_apply';
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

    /**
     * 先花一亿元自动发送优惠券
     * @param type $user_id
     * @param type $type  1注册自动发券 2 输入手机号自动发券 3 分享成功自动发券
     */
    public function sendcoupon($user_id, $title, $type, $day, $field, $number = 10000) {
        $nowtime = date('Y-m-d H:i:s');
        $endtime = date('Y-m-d 00:00:00', strtotime("+$day days"));
        $standard = Coupon_apply::find()->where(['title' => $title, 'type' => $type, 'start_date' => date('Y-m-d 00:00:00'), 'val' => $field, 'end_date' => $endtime, 'apply_depart' => -1, 'apply_user' => -1, 'audit_person' => -1, 'status' => 3])->orderBy('id desc')->one();
        
        if (empty($standard) || ($standard['number'] - $standard['send_num']) < 0) {
            $standard = new Coupon_apply();
            $standard->title = $title;
            $standard->type = $type;
            $standard->limit = 0;
            $standard->val = $field;
            $standard->number = $number;
            $standard->send_num = 0;
            $standard->start_date = date('Y-m-d 00:00:00');
            $standard->end_date = $endtime;
            $standard->apply_depart = -1;
            $standard->apply_user = -1;
            $standard->audit_person = -1;
            $standard->status = 3;
            $standard->create_time = $nowtime;
            $standard->audit_time = $nowtime;
            $standard->version = 1;
            $standard->save();
        }
        $sn = date('ymdHis', time()) . '1';
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        $mobile = $userinfo['mobile'];
        $standlist = Coupon_list::find()->where(['title' => $title, 'type' => $type, 'mobile' => $mobile, 'val' => $field])->one();
        if (empty($standlist)) {
            $sql = "insert into " . Coupon_list::tableName() . " (apply_id,title,type,sn,`limit`,val,start_date,end_date,mobile,status,create_time) value ('" . $standard['id'] . "','" . $standard['title'] . "'," . $standard['type'] . ",'$sn'," . $standard['limit'] . ",'" . $standard['val'] . "','" . $standard['start_date'] . "','" . $standard['end_date'] . "','$mobile',1,'$nowtime')";
            $ret = Yii::$app->db->createCommand($sql)->execute();
            $id = Yii::$app->db->getLastInsertID();
            if ($ret) {
                $send_num = $standard->send_num + 1;
                $stas = $standard->number > $send_num ? 3 : 5;
                $applystatus = "update " . Coupon_apply::tableName() . " set status=$stas,send_num=$send_num where id=" . $standard['id'];
                Yii::$app->db->createCommand($applystatus)->execute();
                return $id;
            }
        } else {
            return $standlist->id;
        }
    }

    /**
     * 先花一亿元自动发送优惠券
     * @param type $user_id
     * @param type $type  1注册自动发券 2 输入手机号自动发券 3 分享成功自动发券
     */
    public function sendcouponactivity($user_id, $title, $type, $day, $field, $number = 10000) {
        $nowtime = date('Y-m-d H:i:s');
        $endtime = date('Y-m-d 00:00:00', strtotime("+$day days"));
        $standard = Coupon_apply::find()->where(['title' => $title, 'type' => $type, 'start_date' => date('Y-m-d 00:00:00'), 'val' => $field, 'end_date' => $endtime, 'apply_depart' => -1, 'apply_user' => -1, 'audit_person' => -1, 'status' => 3])->one();
        if (empty($standard)) {
            $standard = new Coupon_apply();
            $standard->title = $title;
            $standard->type = $type;
            $standard->limit = 0;
            $standard->val = $field;
            $standard->number = $number;
            $standard->send_num = 0;
            $standard->start_date = date('Y-m-d 00:00:00');
            $standard->end_date = $endtime;
            $standard->apply_depart = -1;
            $standard->apply_user = -1;
            $standard->audit_person = -1;
            $standard->status = 3;
            $standard->create_time = $nowtime;
            $standard->audit_time = $nowtime;
            $standard->version = 1;
            $standard->save();
        }
        $sn = date('ymdHis', time()) . '1';
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        $mobile = $userinfo['mobile'];
        $sql = "insert into " . Coupon_list::tableName() . " (apply_id,title,type,sn,`limit`,val,start_date,end_date,mobile,status,create_time) value ('" . $standard['id'] . "','" . $standard['title'] . "'," . $standard['type'] . ",'$sn'," . $standard['limit'] . ",'" . $standard['val'] . "','" . $standard['start_date'] . "','" . $standard['end_date'] . "','$mobile',1,'$nowtime')";
        $ret = Yii::$app->db->createCommand($sql)->execute();
        $id = Yii::$app->db->getLastInsertID();
        if ($ret) {
            $send_num = $standard->send_num + 1;
            $stas = $standard->number > $send_num ? 3 : 5;
            $applystatus = "update " . Coupon_apply::tableName() . " set status=$stas,send_num=$send_num where id=" . $standard['id'];
            Yii::$app->db->createCommand($applystatus)->execute();
            return $id;
        } else {
            return false;
        }
    }

    public function sendCrontabCoupon($user_id, $title, $type, $field, $start_date, $endtime, $number = 10000) {
        $nowtime = date('Y-m-d H:i:s');
        $standard = Coupon_apply::find()->where(['title' => $title, 'type' => $type, 'start_date' => $start_date, 'val' => $field, 'end_date' => $endtime, 'apply_depart' => -1, 'apply_user' => -1, 'audit_person' => -1, 'status' => 3])->one();
        if (empty($standard)) {
            $standard = new Coupon_apply();
            $standard->title = $title;
            $standard->type = $type;
            $standard->limit = 0;
            $standard->val = $field;
            $standard->number = $number;
            $standard->send_num = 0;
            $standard->start_date = $start_date;
            $standard->end_date = $endtime;
            $standard->apply_depart = -1;
            $standard->apply_user = -1;
            $standard->audit_person = -1;
            $standard->status = 3;
            $standard->create_time = $nowtime;
            $standard->audit_time = $nowtime;
            $standard->version = 1;
            $standard->save();
        }
        $sn = date('ymdHis', time()) . '1';
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        $mobile = $userinfo['mobile'];
        $standlist = Coupon_list::find()->where(['title' => $title, 'type' => $type, 'mobile' => $mobile, 'val' => $field])->one();
        if (empty($standlist)) {
            $sql = "insert into " . Coupon_list::tableName() . " (apply_id,title,type,sn,`limit`,val,start_date,end_date,mobile,status,create_time) value ('" . $standard['id'] . "','" . $standard['title'] . "'," . $standard['type'] . ",'$sn'," . $standard['limit'] . ",'" . $standard['val'] . "','" . $standard['start_date'] . "','" . $standard['end_date'] . "','$mobile',1,'$nowtime')";
            $ret = Yii::$app->db->createCommand($sql)->execute();
            $id = Yii::$app->db->getLastInsertID();
            if ($ret) {
                $send_num = $standard->send_num + 1;
                $stas = $standard->number > $send_num ? 3 : 5;
                $applystatus = "update " . Coupon_apply::tableName() . " set status=$stas,send_num=$send_num where id=" . $standard['id'];
                Yii::$app->db->createCommand($applystatus)->execute();
                return $id;
            }
        } else {
            return $standlist->id;
        }
    }

    /**
     * 修改优惠卷发送数量，或修改状态
     * @param $num
     * @param int $statue
     * @return bool
     */
    public function setSendNum($num,$status = 0)
    {
        $sendNum = $this->send_num;
        try {
            $this->send_num = $sendNum + $num;
            if($status !== 0){
                $this->status = $status;
            }
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}

<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "access_token".
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
class Red_packets_receive extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_red_packets_receive';
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

    public function getAuthuser() {
        return $this->hasOne(User::className(), ['user_id' => 'auth_user_id']);
    }

    /**
     * 
     */
    public function getSumRed($user_id) {
        if (empty($user_id)) {
            return 0;
        }
        $num = Red_packets_receive::find()->where(['user_id' => $user_id])->sum('amount');
        return $num;
    }
    
    /**
     * 当日红包收益
     */
    public function getTodaySumRed($user_id) {
    	if (empty($user_id)) {
    		return 0;
    	}
    	$begin_time = date('Y-m-d'.' 00:00:00');
    	$end_time = date('Y-m-d'.' 23:59:59');
    	$num = Red_packets_receive::find()->where(['user_id' => $user_id])->andWhere("create_time >= '$begin_time' and create_time <= '$end_time'")->sum('amount');
    	return $num;
    }

    /**
     * 领取红包
     */
    public function addRedPacket($condition) {
        $now_time = date('Y-m-d H:i:s');
        $red_packets_receive = new Red_packets_receive();
        $red_packets_receive->user_id = $condition['user_id'];
        $red_packets_receive->grant_id = $condition['grant_id'];
        $red_packets_receive->auth_user_id = $condition['auth_user_id'];
        $red_packets_receive->amount = $condition['amount'];
        $red_packets_receive->current_amount = $condition['current_amount'];
        $red_packets_receive->status = 'NORMAL';
        $red_packets_receive->create_time = $now_time;
        $red_packets_receive->last_modify_time = $now_time;
        $red_packets_receive->version = 1;

        if ($red_packets_receive->save()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        } else {
            return false;
        }
    }

}

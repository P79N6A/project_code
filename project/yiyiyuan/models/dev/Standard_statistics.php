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
class Standard_statistics extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_standard_statistics';
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

    public function getInformation() {
        return $this->hasOne(Standard_information::className(), ['id' => 'standard_id']);
    }

    public function getCoupon() {
        return $this->hasOne(Standard_coupon_list::className(), ['id' => 'coupon_id']);
    }

    public function getStandStatisticsByUserIdStandId($user_id, $standard_id) {
        if (empty($user_id) || empty($standard_id)) {
            return null;
        }
        $standard_statistics = Standard_statistics::find()->where(['user_id' => $user_id, 'standard_id' => $standard_id])->one();
        if (!empty($standard_statistics)) {
            return $standard_statistics;
        } else {
            return false;
        }
    }

    public function addStandardStatistics($standard_id, $user, $coupon_id, $invest_share, $profit) {
        $now_time = date('Y-m-d H:i:s');
        $standardStatModel = new Standard_statistics();
        $standardStatModel->version = 1;
        $standardStatModel->standard_id = $standard_id;
        $standardStatModel->user_id = $user->user_id;
        $standardStatModel->coupon_id = $coupon_id;
        $standardStatModel->total_invested = $invest_share;
        $standardStatModel->total_invested_share = $invest_share;
        $standardStatModel->total_onInvested = $invest_share;
        $standardStatModel->total_onInvested_share = $invest_share;
        $standardStatModel->available_trans = $invest_share;
        $standardStatModel->available_trans_share = $invest_share;
        $standardStatModel->transfered_amount = 0;
        $standardStatModel->transfered_share = 0;
        $standardStatModel->transfering_amount = 0;
        $standardStatModel->transfering_share = 0;
        $standardStatModel->achieved_interest = 0;
        $standardStatModel->achieving_interest = $profit;
        $standardStatModel->user_type = 'NORMAL';
        $standardStatModel->last_modify_time = $now_time;
        $standardStatModel->create_time = $now_time;
        $result = $standardStatModel->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    public function updateStandardStatistics($coupon_id, $invest_share, $profit) {
        $now_time = date('Y-m-d H:i:s');
        $this->coupon_id = $coupon_id;
        $this->total_invested += $invest_share;
        $this->total_invested_share += $invest_share;
        $this->total_onInvested += $invest_share;
        $this->total_onInvested_share += $invest_share;
        $this->available_trans += $invest_share;
        $this->available_trans_share += $invest_share;
        $this->achieving_interest += $profit;
        $this->last_modify_time = $now_time;
        $this->version += 1;
        $result = $this->save();
        return $result;
    }
    
    //返还收益至标的统计表
    public function setStandardStatisticsProfit($profit, $now_time, $id, $version){
    	$sql_standard_statistics = "update ".Standard_statistics::tableName()." set achieved_interest=(achieved_interest+".$profit."),last_modify_time='$now_time',version=version+1 where id=".$id." and version=".$version;
    	$ret_standard_statistics = Yii::$app->db->createCommand($sql_standard_statistics)->execute();
    	
    	return $ret_standard_statistics;
    }

}

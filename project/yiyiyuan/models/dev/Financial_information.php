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
class Financial_information extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_financial_information';
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

    public function addFinancialInformation($user_id, $standard_id, $invest_share) {
        $now_time = date('Y-m-d H:i:s');
        $financial_information = new Financial_information();
        $financial_information->version = 1;
        $financial_information->standard_id = $standard_id;
        $financial_information->user_id = $user_id;
        $financial_information->trade_type = 'GENE';
        $financial_information->funds_direction = 'INCR';
        $financial_information->trade_amount = $invest_share;
        $financial_information->trade_share = $invest_share;
        $financial_information->last_modify_time = $now_time;
        $financial_information->create_time = $now_time;
        $result = $financial_information->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

}

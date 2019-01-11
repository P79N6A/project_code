<?php

namespace app\models\dev;

use Yii;

class User_credit_reback extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_credit_reback';
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
     * 添加赎回记录
     * @param type $user_id
     * @param type $reback_share
     * @return boolean
     */
    public function addCreditReback($user_id, $reback_share) {
        if (empty($user_id) || empty($reback_share)) {
            return false;
        }
        $this->user_id = $user_id;
        $this->amount = $reback_share;
        $this->create_time = date("Y-m-d H:i:s");
        if ($this->save()) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

}

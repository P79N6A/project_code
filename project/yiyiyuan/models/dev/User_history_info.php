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
class User_history_info extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_history_info';
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
     * 添加用户信息更改记录
     */
    public static function addHistoryInfo($user, $condition) {
        if (empty($condition) || !isset($condition['data_type'])) {
            return false;
        }
        $history_info = new User_history_info();
        foreach ($condition as $key => $val) {
            $history_info->{$key} = $val;
        }
        $history_info->create_time = date('Y-m-d H:i:s');
        if ($history_info->save()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        } else {
            return false;
        }
    }

    /**
     * 获取最近一条历史修改记录
     * @param $user_id
     * @return array|bool|static
     */
    public function newestHistory($user_id)
    {
        if (empty($user_id)) return false;
        $history_info = User_history_info::find()->where(['user_id'=>$user_id])->orderBy(['create_time'=>SORT_DESC])->one();
        if (!empty($history_info)){
            return $history_info;
        }
        return array();
    }

}

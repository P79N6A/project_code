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
class Standard_progress extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_standard_progress';
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

    public function getStandardProgressByStandardId($standard_id) {
        if (empty($standard_id)) {
            return false;
        }
        $result = Standard_progress::find()->where(['standard_id' => $standard_id])->one();
        return $result;
    }

    public function updateStandardProgress($condition) {
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->version += 1;
        $result = $this->save();
        return $result;
    }

}

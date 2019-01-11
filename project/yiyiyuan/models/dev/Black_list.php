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
class Black_list extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_black_list';
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
     * 判断是否在黑名单内
     * @param type $identity
     */
    public function getInBlack($identity) {
        $blackinfo = Black_list::find()->where(['cred_no' => $identity])->one();
        if(!empty($blackinfo)){
            return true;
        }else{
            return false;
        }
    }

}

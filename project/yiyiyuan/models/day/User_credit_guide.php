<?php

namespace app\models\day;

use Yii;

/**
 * This is the model class for table "yi_user_credit_guide".
 *
 * @property string $id
 * @property string $mobile
 * @property string $realname
 * @property string $identity
 * @property string $create_time
 */
class User_credit_guide extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_user_seniority';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['create_time'], 'safe'],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['realname'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'realname' => 'Realname',
            'identity' => 'Identity',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 获取记录，根据身份证号
     * @param $identity
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/2 19:04
     */
    public function getByIdentity($identity) {
        if (empty($identity)) {
            return null;
        }
        return self::find()->where(['identity' => $identity])->one();
    }
}

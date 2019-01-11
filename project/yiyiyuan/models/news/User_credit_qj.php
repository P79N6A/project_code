<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "qj_user_credit".
 *
 * @property string $id
 * @property string $mobile
 * @property string $realname
 * @property string $identity
 * @property string $create_time
 */
class User_credit_qj extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_user_credit';
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

    public function getByIdentity($identity) {
        if (empty($identity)) {
            return NULL;
        }
        $credit = self::find()->where(['identity' => $identity])->one();
        return $credit;
    }

    public function getUserOne($identity) {
        $credit = self::find()->where(['identity'=>$identity])->asArray()->one();
        return $credit;
    }

}

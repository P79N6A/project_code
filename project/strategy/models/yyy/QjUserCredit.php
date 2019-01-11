<?php

namespace app\models\yyy;

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
class QjUserCredit extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qj_user_credit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['realname'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => '用户手机号码',
            'realname' => '真实姓名',
            'identity' => '身份证号',
            'create_time' => 'Create Time',
        ];
    }

    public function getUserByIdentity($identity) {
        if (empty($identity)) {
            return false;
        }
        return $this->find()->where(['identity' => $identity])->limit(1)->one();
    }
}

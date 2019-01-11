<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_quota".
 *
 * @property string $id
 * @property string $user_id
 * @property string $quota
 * @property string $temporary_quota
 * @property integer $grade
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class User_quota_new extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_quota_new';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'quota', 'temporary_quota', 'grade', 'last_modify_time', 'create_time', 'version'], 'required'],
            [['user_id', 'grade', 'version'], 'integer'],
            [['quota', 'temporary_quota'], 'number'],
            [['last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'quota' => 'Quota',
            'temporary_quota' => 'Temporary Quota',
            'grade' => 'Grade',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 获取额度根据user_id
     * @param $userId
     * @return int|mixed
     */
    public function getQuotaByUserId($userId)
    {
        $quota = 1500;
        if (empty($userId) || !is_numeric($userId)) {
            return $quota;
        }
        $userQuotaObj = self::find()->where(['user_id' => $userId])->one();
        if (!empty($userQuotaObj)) {
            $quota = $userQuotaObj->quota + $userQuotaObj->temporary_quota;
        }
        return $quota;
    }
}

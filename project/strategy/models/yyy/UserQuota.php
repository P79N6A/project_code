<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
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
class UserQuota extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_quota';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_yyy');
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
            'quota' => '用户可借额度',
            'temporary_quota' => '用户临时额度',
            'grade' => '用户等级',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
            'version' => '乐观锁版本号',
        ];
    }

    public function getUserQuota($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->orderby('ID DESC')->Asarray()->one();
        if (empty($res)) {
            return [];
        }
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}

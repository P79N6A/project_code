<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_term".
 *
 * @property string $id
 * @property string $user_id
 * @property string $db_term
 * @property integer $db_canterm
 * @property string $db_amount
 * @property string $xy_term
 * @property integer $xy_canterm
 * @property string $xy_amount
 * @property string $last_modify_time
 * @property string $create_time
 * @property string $version
 */
class YiTerm extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_term';
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
            [['user_id', 'version'], 'required'],
            [['user_id', 'db_canterm', 'xy_canterm', 'version'], 'integer'],
            [['db_amount', 'xy_amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['db_term', 'xy_term'], 'string', 'max' => 10],
            [['user_id'], 'unique']
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
            'db_term' => '担保借款可分期数',
            'db_canterm' => '担保借款是否可以分期1：可分期2：不可分期',
            'db_amount' => '担保借款可分期额度',
            'xy_term' => '信用借款可分期数',
            'xy_canterm' => '信用借款是否可以分期1：可分期2：不可分期',
            'xy_amount' => '信用借款可分期额度',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => '乐观锁版本号',
        ];
    }

    public function getTerm($where)
    {
        return $this->find()->where($where)->orderby('ID DESC')->one();
    }
}

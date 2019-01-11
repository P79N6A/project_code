<?php
//废弃表
namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%register}}".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $create_time
 */
class XsRegister extends \app\models\xs\XsBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%register}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbxs');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basic_id'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'basic_id' => '请求表id',
            'create_time' => '注册时间',
        ];
    }
}

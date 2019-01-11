<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_idfa".
 *
 * @property string $id
 * @property string $idfa
 */
class Idfa extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_idfa';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idfa' => 'Idfa',
        ];
    }
}

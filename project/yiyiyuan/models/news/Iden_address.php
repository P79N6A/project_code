<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_iden_address".
 *
 * @property string $id
 * @property string $code
 * @property string $address
 * @property string $create_time
 */
class Iden_address extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_iden_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['code'], 'string', 'max' => 32],
            [['address'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'address' => 'Address',
            'create_time' => 'Create Time',
        ];
    }
}

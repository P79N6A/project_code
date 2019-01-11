<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_attention".
 *
 * @property integer $id
 * @property integer $qr_id
 * @property string $openid
 * @property string $create_time
 * @property string $cancle_time
 * @property integer $type
 */
class Attention extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_attention';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['qr_id', 'type'], 'integer'],
            [['create_time', 'cancle_time'], 'safe'],
            [['openid'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'qr_id' => 'Qr ID',
            'openid' => 'Openid',
            'create_time' => 'Create Time',
            'cancle_time' => 'Cancle Time',
            'type' => 'Type',
        ];
    }
}

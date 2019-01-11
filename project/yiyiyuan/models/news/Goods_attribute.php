<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_attribute".
 *
 * @property string $id
 * @property string $cid
 * @property string $attribute
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Goods_attribute extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_attribute';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid'], 'required'],
            [['cid', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['attribute'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cid' => 'Cid',
            'attribute' => 'Attribute',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }
}

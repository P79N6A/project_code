<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_attribute_value".
 *
 * @property string $id
 * @property string $goods_id
 * @property string $gid
 * @property string $value
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Goods_attribute_value extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_attribute_value';
    }

    public function getAttr() {
        return $this->hasOne(Goods_attribute::className(), ['id' => 'gid']);
    }

    public function getAttribute($goods_id) {
        return self::find()->joinWith('attr', true, 'LEFT JOIN')->where(['goods_id'=>$goods_id])->orderBy('yi_goods_attribute.id asc')->all();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'gid'], 'required'],
            [['goods_id', 'gid', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['value'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'gid' => 'Gid',
            'value' => 'Value',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }
}

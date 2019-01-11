<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_goods".
 *
 * @property string $id
 * @property string $goods_id
 * @property string $goods_name
 * @property string $price
 * @property string $memory
 * @property integer $stock
 * @property string $image_path
 * @property integer $sale_number
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Goods extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'goods_name', 'price', 'image_path', 'create_time', 'last_modify_time'], 'required'],
            [['goods_id', 'stock', 'sale_number', 'version'], 'integer'],
            [['price'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['goods_name'], 'string', 'max' => 64],
            [['memory'], 'string', 'max' => 32],
            [['image_path'], 'string', 'max' => 255]
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
            'goods_name' => 'Goods Name',
            'price' => 'Price',
            'memory' => 'Memory',
            'stock' => 'Stock',
            'image_path' => 'Image Path',
            'sale_number' => 'Sale Number',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    /**
     * 获取一个价格最高的商品
     * @param $price
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getGoodsByMaxPrice()
    {
        return self::find()->orderBy(['price' => 'desc'])->one();
    }
}
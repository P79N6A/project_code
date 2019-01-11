<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_shop".
 *
 * @property integer $id
 * @property string $title
 * @property string $tag
 * @property string $pic
 * @property string $price
 * @property string $shop
 * @property string $create_time
 * @property string $modify_time
 * @property integer $version
 */
class Goods_shop extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_goods_shop';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['title', 'tag', 'pic', 'price', 'shop', 'create_time', 'modify_time', 'version'], 'required'],
            [['price'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['version'], 'integer'],
            [['title', 'tag', 'pic', 'shop'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'tag' => 'Tag',
            'pic' => 'Pic',
            'price' => 'Price',
            'shop' => 'Shop',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'version' => 'Version',
        ];
    }

    public function getByPrice($amount,$type=1) {
        $o = self::find()->andWhere(['type'=>$type])->andWhere(['BETWEEN', 'price', $amount - 500, $amount + 500])->all();
        $total = count($o);
        if ($total < 2) {
            return $o[0];
        } else {
            $num = rand(0, $total - 1);
            return $o[$num];
        }
    }

}

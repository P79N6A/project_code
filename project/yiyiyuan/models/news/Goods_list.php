<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_list".
 *
 * @property string $id
 * @property string $goods_name
 * @property string $cid
 * @property string $goods_price
 * @property string $instalment
 * @property string $description
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Goods_list extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_list';
    }

    public function getPic() {
        return $this->hasOne(Goods_pic::className(), ['gid' => 'id']);
    }
    public function getSlpic($goods_id) {
        return Goods_pic::find()->where(['gid' => $goods_id,'pic_type' => 2])->all();
    }
    public function getXpic($goods_id) {
        return Goods_pic::find()->where(['gid' => $goods_id,'pic_type' => 1])->one();
    }
    public function getDpic($goods_id) {
        return Goods_pic::find()->where(['gid' => $goods_id,'pic_type' => 3])->orderBy('sort_id asc')->all();
    }

    public function getAttr() {
        return $this->hasOne(Goods_attribute_value::className(), ['goods_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_name', 'cid', 'goods_price'], 'required'],
            [['cid', 'version'], 'integer'],
            [['goods_price', 'instalment'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['goods_name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_name' => 'Goods Name',
            'cid' => 'Cid',
            'goods_price' => 'Goods Price',
            'instalment' => 'Instalment',
            'description' => 'Description',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function getGoodsByCid($cid, $limit=4){
        $cid = intval($cid);
        if(!$cid){
            return null;
        }
        return self::find()->where(['cid'=>$cid])->limit($limit)->orderBy('create_time desc')->all();
    }

    public function getGoodsById($id){
        if(!$id) {
            return null;
        }
        return self::find()->where(['id'=>$id])->one();
    }

    /**
     * 根据金额，商品类型获取商品信息
     * @param $amount 借款金额
     * @param $cid 商品分类id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getGoodsByLoanAmount($amount,$cid)
    {
        $where = [
            'AND',
            ['IN', 'cid', $cid],
            ['>', 'goods_price', $amount],
            ['<', 'goods_price', $amount*3],
        ];
        $res = self::find()->where($where)->joinWith('attr', 'TRUE', 'LEFT JOIN')->limit(20)->asArray()->all();
        if(empty($res)){
            $res = self::find()->where(['cid' => $cid])->joinWith('attr', 'TRUE', 'LEFT JOIN')->limit(20)->asArray()->all();
        }
        return $res;
    }
}

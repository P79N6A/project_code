<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_pic".
 *
 * @property string $id
 * @property string $pic_url
 * @property string $gid
 * @property integer $pic_type
 * @property integer $is_main
 * @property integer $sort_id
 * @property string $create_time
 */
class Goods_pic extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_pic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pic_url', 'gid', 'pic_type', 'is_main', 'sort_id', 'create_time'], 'required'],
            [['gid', 'pic_type', 'is_main', 'sort_id'], 'integer'],
            [['create_time'], 'safe'],
            [['pic_url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pic_url' => 'Pic Url',
            'gid' => 'Gid',
            'pic_type' => 'Pic Type',
            'is_main' => 'Is Main',
            'sort_id' => 'Sort ID',
            'create_time' => 'Create Time',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function getPicUrlByGid($gid){
        $gid = intval($gid);
        if(!$gid){
            return '';
        }
        $data = self::find()->select('pic_url')->where(['gid'=>$gid])->asArray()->one();
        return isset($data['pic_url']) ? $data['pic_url'] : '';
    }
}

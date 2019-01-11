<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "card_info_channel".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $name
 * @property string $mechart_num
 * @property integer $status
 * @property string $create_time
 * @property integer $sort_num
 */
class CardInfoChannel extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cardinfo_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'status', 'sort_num'], 'integer'],
            [['name', 'mechart_num', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['name'], 'string', 'max' => 30],
            [['mechart_num'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'name' => 'Name',
            'mechart_num' => 'Mechart Num',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'sort_num' => 'Sort Num',
        ];
    }

    public function cardChannel($data){
        $where = [
            'and',
            [CardInfoChannel::tableName().'.status' => 1],
            [CardInfoChannel::tableName().'.aid' => $data['aid']],
        ];
        $channels = static::find()
            ->where($where)
            ->orderBy('sort_num asc')
            ->asArray()
            ->all();
        if (empty($channels)) {
            return [];
        }
        return \yii\helpers\ArrayHelper::getColumn($channels, 'channel_id');
    }
    /**
     * @param string $channel
     * @return array|object
     */
    public function getByChannelId($channelId=''){
        $where = [];
        if(!empty($channelId)){
            $where = [ 'channel_id' => $channelId ];
        }
        return static::find()->where($where)->all();
    }

}

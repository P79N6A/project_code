<?php

namespace app\models\phonelab;

use app\models\repo\CloudBase;

/**
 * This is the model class for table "tellab_channel".
 *
 * @property integer $id
 * @property string $name
 * @property string $gateway
 * @property integer $status
 * @property integer $sort_num
 * @property integer $is_default
 * @property string $create_time
 */
class TelLabChannel extends CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tellab_channel';
    }

    /**
     * @inheritdoc
     */
     public function rules()
    {
        return [
            [['status', 'is_default', 'sort_num'], 'integer'],
            [['name', 'gateway', 'create_time', 'sort_num'], 'required'],
            [['create_time'], 'safe'],
            [['name'], 'string', 'max' => 30],
            [['gateway'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'gateway' => 'Gateway',
            'status' => 'Status',
            'is_default' => 'Is Default',
            'create_time' => 'Create Time',
            'sort_num' => 'Sort Num',
        ];
    }

    public function supportChannel()
    {
        $where = [
            TelLabChannel::tableName() . '.status' => 1
        ];
        $channels = static::find()->where($where)
            ->orderBy('sort_num asc')
            ->asArray()
            ->all();
        if (empty($channels)) {
            return [];
        }
        return $channels;
    }
}
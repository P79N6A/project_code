<?php

namespace app\models\repo;

use \app\models\BaseModel;

/**
 * This is the model class for table "tellab_channel".
 *
 * @property integer $id
 * @property string $name
 * @property string $gateway
 * @property integer $status
 * @property integer $sort_num
 * @property integer $quota
 * @property string $create_time
 */
class TelLabChannel extends BaseModel
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
            [['status', 'sort_num', 'quota'], 'integer'],
            [['name', 'gateway', 'create_time', 'sort_num', 'quota'], 'required'],
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
            'sort_num' => 'Sort Num',
            'quota' => 'Quota',
            'create_time' => 'Create Time',
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
<?php

namespace app\models\repo;

use \app\models\BaseModel;

/**
 * This is the model class for table "ipaddr_channel".
 *
 * @property integer $id
 * @property string $name
 * @property string $gateway
 * @property integer $status
 * @property integer $is_default
 * @property string $create_time
 * @property integer $sort_num
 */
class IPAddrChannel extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ipaddr_channel';
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
            IPAddrChannel::tableName() . '.status' => 1
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
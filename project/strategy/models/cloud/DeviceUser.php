<?php

namespace app\models\cloud;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dc_device_user".
 *
 * @property string $id
 * @property string $aid
 * @property string $identity_id
 * @property string $event
 * @property string $device
 * @property string $create_time
 * @property string $modify_time
 */
class DeviceUser extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_device_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid'], 'integer'],
            [['identity_id', 'event', 'device', 'create_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['identity_id'], 'string', 'max' => 50],
            [['event'], 'string', 'max' => 20],
            [['device'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '业务应用ID',
            'identity_id' => '业务唯一标识',
            'event' => '事件类型',
            'device' => '设备号',
            'create_time' => '创建时间',
            'modify_time' => 'Modify Time',
        ];
    }
}

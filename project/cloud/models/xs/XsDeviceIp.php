<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%device_ip}}".
 *
 * @property string $id
 * @property string $aid
 * @property string $ip
 * @property string $device
 * @property string $create_time
 */
class XsDeviceIp extends \app\models\repo\CloudBase {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'dc_device_ip';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid'], 'integer'],
            [['ip', 'device', 'event', 'create_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['ip'], 'string', 'max' => 30],
            [['device'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'aid' => '业务应用ID',
            'device' => '设备号',
            'ip' => 'IP地址',
            'event' => '事件',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ];
    }
    private function getSame($device, $ip, $event, $aid) {
        $where = [
            'device' => $device,
            'ip' => $ip,
            'event' => $event,
            'aid' => $aid,
        ];
        return static::find()->where($where)->limit(1)->one();
    }
    public function chkAndSave($device, $ip, $event, $aid) {
        if (!$device || !$ip) {
            return false;
        }
        $time = date("Y-m-d H:i:s");
        $m = $this->getSame($device, $ip, $event, $aid);
        if ($m) {
            $m->modify_time = $time;
            $result = $m->save();
            return $result;
        }
        $postData = [
            'aid' => $aid,
            'device' => $device,
            'ip' => $ip,
            'event' => $event,
            'modify_time' => $time,
            'create_time' => $time,
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }

        return $this->save();
    }
    public function sameIpDevices($ip) {
        if (!$ip) {
            return 0;
        }
        $total = static::find()->where(['ip' => $ip])->count();
        return $total;
    }

    public function sameDeviceIps($device) {
        if (!$device) {
            return 0;
        }
        $total = static::find()->where(['device' => $device])->count();
        return $total;
    }
}

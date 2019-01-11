<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%ip_user}}".
 *
 * @property string $id
 * @property string $aid
 * @property string $identity_id
 * @property string $event
 * @property string $ip
 * @property string $create_time
 */
class XsIpUser extends \app\models\xs\XsBaseNewModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%ip_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid'], 'integer'],
            [['identity_id', 'event', 'ip', 'create_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['identity_id'], 'string', 'max' => 50],
            [['event'], 'string', 'max' => 20],
            [['ip'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'aid' => '业务应用ID',
            'identity_id' => '业务唯一标识',
            'event' => '事件类型',
            'ip' => 'IP地址',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ];
    }

    private function getSame($ip, $identity_id, $event, $aid) {
        $where = [
            'ip' => $ip,
            'identity_id' => $identity_id,
            'event' => $event,
            'aid' => $aid,
        ];
        return static::find()->where($where)->limit(1)->one();
    }
    public function chkAndSave($ip, $identity_id, $event, $aid) {
        if (!$ip || !$identity_id) {
            return false;
        }
        $time = date("Y-m-d H:i:s");
        $m = $this->getSame($ip, $identity_id, $event, $aid);
        if ($m) {
            $m->modify_time = $time;
            $result = $m->save();
            return $result;
        }
        $postData = [
            'aid' => $aid,
            'ip' => $ip,
            'identity_id' => $identity_id,
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
    public function sameIpUsers($ip) {
        if (!$ip) {
            return 0;
        }
        $total = static::find()->where(['ip' => $ip])->count();
        return $total;
    }
}

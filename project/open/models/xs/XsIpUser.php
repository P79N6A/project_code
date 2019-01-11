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
class XsIpUser extends \app\models\repo\CloudBase {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'dc_ip_user';
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
            [['event','phone'], 'string', 'max' => 20],
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
            'phone' => '用户手机号',
            'event' => '事件类型',
            'ip' => 'IP地址',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ];
    }

    private function getSame($ip, $identity_id, $event, $aid, $phone) {
        $where = [
            'ip' => $ip,
            'event' => $event,
            'aid' => $aid,
        ];
        if ($phone) {
            $where['phone'] = $phone;
        }
        if ($identity_id) {
            $where['identity_id'] = $identity_id;
        }
        return static::find()->where($where)->limit(1)->one();
    }
    public function chkAndSave($ip, $identity_id, $event, $aid, $phone) {
        if (!$ip || (!$identity_id && !$phone)) {
            return false;
        }
        $time = date("Y-m-d H:i:s");
        $m = $this->getSame($ip, $identity_id, $event, $aid, $phone);
        if ($m) {
            $m->modify_time = $time;
            if ($phone) {
                $m->phone = $phone;
            }
            if ($identity_id) {
                $m->identity_id = $identity_id;
            }
            $result = $m->save();
            return $result;
        }
        $postData = [
            'aid' => $aid,
            'ip' => $ip,
            'identity_id' => $identity_id,
            'phone' => $phone,
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

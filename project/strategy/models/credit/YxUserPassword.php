<?php

namespace app\models\credit;

use Yii;

/**
 * This is the model class for table "yx_user_password".
 *
 * @property string $id
 * @property string $user_id
 * @property string $login_password
 * @property string $pay_password
 * @property string $device_tokens
 * @property string $device_type
 * @property string $device_ip
 * @property string $last_modify_time
 * @property string $create_time
 * @property string $version
 */
class YxUserPassword extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_user_password';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'last_modify_time', 'create_time', 'version'], 'required'],
            [['user_id', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['login_password', 'pay_password'], 'string', 'max' => 64],
            [['device_tokens'], 'string', 'max' => 128],
            [['device_type'], 'string', 'max' => 10],
            [['device_ip'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'login_password' => '登录密码',
            'pay_password' => '支付密码',
            'device_tokens' => '设备编号',
            'device_type' => '设备类型',
            'device_ip' => '设备ip',
            'last_modify_time' => '最后修改时间',
            'create_time' => '添加时间',
            'version' => '乐观锁',
        ];
    }

    public function getUserCredit() {
        return $this->hasOne(YxUserCredit::className(), ['user_id' => 'user_id']);
    }
}

<?php

namespace app\models\loan;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property string $user_id
 * @property string $mobile
 * @property string $realname
 * @property string $identity
 * @property integer $status
 * @property integer $identity_valid
 * @property integer $come_from
 * @property integer $from_code
 * @property string $last_login_time
 * @property string $invite_code
 * @property string $invite_from
 * @property integer $version
 * @property string $verify_time
 * @property string $modify_time
 * @property string $create_time
 */
class SfUser extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'identity_valid', 'come_from', 'from_code', 'version'], 'integer'],
            [['last_login_time', 'verify_time', 'modify_time', 'create_time'], 'safe'],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['realname', 'invite_code', 'invite_from'], 'string', 'max' => 32],
            [['mobile'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'ID',
            'mobile' => '用户手机号码',
            'realname' => '用户姓名',
            'identity' => '用户身份证号',
            'status' => '用户状态:1初始；2待审核；3审核通过；4审核驳回；5黑名单用户；6禁用；7 冻结',
            'identity_valid' => '身份验证:1初始；2成功；3失败; 4身份证年龄区域不在借款范围',
            'come_from' => '设备来源 :  1 ios 2 android 3 微信 4 web',
            'from_code' => '渠道来源 :1 自有2一亿元  3  其他 ',
            'last_login_time' => '最后登录时间',
            'invite_code' => '我的邀请码',
            'invite_from' => '邀请来源',
            'version' => '乐观锁版本号',
            'verify_time' => '审核时间',
            'modify_time' => 'Modify Time',
            'create_time' => '用户注册时间',
        ];
    }
}

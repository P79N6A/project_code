<?php

namespace app\modules\balance\models\yyy;

use Yii;


/**
 * This is the model class for table "yi_user".
 *
 * @property string $user_id
 * @property string $openid
 * @property string $mobile
 * @property string $invite_code
 * @property string $invite_qrcode
 * @property string $from_code
 * @property integer $user_type
 * @property integer $status
 * @property integer $identity_valid
 * @property integer $school_valid
 * @property string $school
 * @property integer $school_id
 * @property string $edu
 * @property string $school_time
 * @property string $realname
 * @property string $identity
 * @property integer $industry
 * @property string $company
 * @property string $position
 * @property string $telephone
 * @property string $address
 * @property string $pic_self
 * @property string $pic_identity
 * @property integer $pic_type
 * @property integer $come_from
 * @property string $down_from
 * @property string $serverid
 * @property string $create_time
 * @property string $pic_up_time
 * @property integer $final_score
 * @property integer $birth_year
 * @property string $last_login_time
 * @property string $last_login_type
 * @property string $verify_time
 * @property string $is_webunion
 * @property string $webunion_confirm_time
 * @property string $is_red_packets
 */
class User extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_type', 'status', 'identity_valid', 'school_valid', 'school_id', 'industry', 'pic_type', 'come_from', 'final_score', 'birth_year'], 'integer'],
            [['create_time', 'pic_up_time', 'last_login_time', 'verify_time', 'webunion_confirm_time'], 'safe'],
            [['openid', 'school', 'edu', 'school_time'], 'string', 'max' => 64],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['invite_code', 'invite_qrcode', 'from_code', 'realname', 'telephone', 'down_from'], 'string', 'max' => 32],
            [['company', 'position', 'address', 'pic_self', 'pic_identity', 'serverid'], 'string', 'max' => 128],
            [['last_login_type'], 'string', 'max' => 16],
            [['is_webunion'], 'string', 'max' => 8],
            [['is_red_packets'], 'string', 'max' => 4],
            [['mobile'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'openid' => 'Openid',
            'mobile' => 'Mobile',
            'invite_code' => 'Invite Code',
            'invite_qrcode' => 'Invite Qrcode',
            'from_code' => 'From Code',
            'user_type' => 'User Type',
            'status' => 'Status',
            'identity_valid' => 'Identity Valid',
            'school_valid' => 'School Valid',
            'school' => 'School',
            'school_id' => 'School ID',
            'edu' => 'Edu',
            'school_time' => 'School Time',
            'realname' => 'Realname',
            'identity' => 'Identity',
            'industry' => 'Industry',
            'company' => 'Company',
            'position' => 'Position',
            'telephone' => 'Telephone',
            'address' => 'Address',
            'pic_self' => 'Pic Self',
            'pic_identity' => 'Pic Identity',
            'pic_type' => 'Pic Type',
            'come_from' => 'Come From',
            'down_from' => 'Down From',
            'serverid' => 'Serverid',
            'create_time' => 'Create Time',
            'pic_up_time' => 'Pic Up Time',
            'final_score' => 'Final Score',
            'birth_year' => 'Birth Year',
            'last_login_time' => 'Last Login Time',
            'last_login_type' => 'Last Login Type',
            'verify_time' => 'Verify Time',
            'is_webunion' => 'Is Webunion',
            'webunion_confirm_time' => 'Webunion Confirm Time',
            'is_red_packets' => 'Is Red Packets',
        ];
    }

    public function getUserInfo($userid){
        if(!$userid){
            return NUll;
        }
        $userInfo = static::find()->where(["user_id"=>$userid])->one();

        return $userInfo;
    }
}
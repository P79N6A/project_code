<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_user".
 *     一亿元用户表
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
class User extends BaseDBModel
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
            'openid' => 'openid',
            'mobile' => '用户手机号码',
            'invite_code' => '我的邀请码',
            'invite_qrcode' => '渠道邀请码',
            'from_code' => '来源邀请码',
            'user_type' => '用户类型：1大学生；2社会人',
            'status' => '1初始；2待审核；3审核通过；4审核驳回',
            'identity_valid' => '1初始；2成功；3失败',
            'school_valid' => '初始；2成功；3失败',
            'school' => '学校',
            'school_id' => '学校ID',
            'edu' => '学历',
            'school_time' => '入学时间',
            'realname' => '真实姓名',
            'identity' => '身份证号',
            'industry' => '行业',
            'company' => '公司',
            'position' => '职位',
            'telephone' => '公司电话',
            'address' => '公司地址',
            'pic_self' => '个人自拍照',
            'pic_identity' => '身份证件照',
            'pic_type' => '拍照类型',
            'come_from' => '1原先花过审用户；2新增',
            'down_from' => '下载来源',
            'serverid' => 'Serverid',
            'create_time' => 'Create Time',
            'pic_up_time' => 'Pic Up Time',
            'final_score' => '同盾风险系数',
            'birth_year' => '出生年份',
            'last_login_time' => '最后登录时间',
            'last_login_type' => '最后登录位置',
            'verify_time' => '审核时间',
            'is_webunion' => 'yes:是；no:不是,默认为no',
            'webunion_confirm_time' => '网盟用户确认时间',
            'is_red_packets' => '是否发放红包',
        ];
    }

    /**
     * 表关联关系
     */
    public function getRegisterEvent() {
        return $this->hasOne(RegisterEvent::className(), ['user_id' => 'user_id']);
    }

    public function getUserPassword() {
        return $this->hasOne(UserPassword::className(), ['user_id' => 'user_id']);
    }

    public function getUserExtend() {
        return $this->hasOne(UserExtend::className(), ['user_id' => 'user_id']);
    }

    public function getUser($where) {
        return $this->find()->where($where)->limit(1)->one();
    }

    public function getUserIdByMobiles($mobiles){
        $res = $this->find()->select('user_id')->where(['in','mobile',$mobiles])->limit(1000)->asArray()->all();
        $user_ids =  ArrayHelper::getColumn($res,'user_id',[]);
        return $user_ids;
    }
}

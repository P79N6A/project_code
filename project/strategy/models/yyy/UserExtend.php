<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_user_extend".
 *
 * @property string $id
 * @property string $user_id
 * @property string $uuid
 * @property integer $school_valid
 * @property integer $school_id
 * @property string $school
 * @property string $edu
 * @property string $school_time
 * @property integer $industry
 * @property string $company
 * @property string $position
 * @property string $profession
 * @property string $telephone
 * @property integer $marriage
 * @property string $email
 * @property string $income
 * @property integer $home_area
 * @property string $home_address
 * @property integer $company_area
 * @property string $company_address
 * @property integer $version
 * @property integer $is_new
 * @property integer $is_callback
 * @property string $reg_ip
 * @property string $last_modify_time
 * @property string $create_time
 */
class UserExtend extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_extend';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_yyy');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'school_valid', 'school_id', 'industry', 'marriage', 'home_area', 'company_area', 'version', 'is_new', 'is_callback'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['uuid', 'telephone', 'email', 'income'], 'string', 'max' => 32],
            [['school', 'edu', 'school_time'], 'string', 'max' => 64],
            [['company', 'position', 'profession', 'home_address', 'company_address'], 'string', 'max' => 128],
            [['reg_ip'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'uuid' => 'app设备编号',
            'school_valid' => '学籍认证状态1初始；2成功；3失败',
            'school_id' => '学校id',
            'school' => '学校',
            'edu' => '学历',
            'school_time' => '入学时间',
            'industry' => '行业',
            'company' => '公司',
            'position' => '职位',
            'profession' => '职业',
            'telephone' => '公司电话',
            'marriage' => '婚姻',
            'email' => '邮箱',
            'income' => '月收入',
            'home_area' => '常住地址区域',
            'home_address' => '常住地址',
            'company_area' => '单位地址区域',
            'company_address' => '单位地址',
            'version' => '乐观所版本号',
            'is_new' => '是否是改版后的新用户',
            'is_callback' => '是否发短信通知回来0:不通知;1:通知',
            'reg_ip' => '用户注册IP',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
}

<?php

namespace app\models\news;

use yii\web\IdentityInterface;
use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Manager extends \app\models\BaseModel implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_manager';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'role'], 'required'],
            [['status', 'role'], 'integer'],
            [['logintime', 'createtime'], 'safe'],
            [['username', 'mobile', 'realname', 'ip'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 64],
            [['email'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'mobile' => 'Mobile',
            'email' => 'Email',
            'realname' => 'Realname',
            'ip' => 'Ip',
            'status' => 'Status',
            'role' => 'Role',
            'logintime' => 'Logintime',
            'createtime' => 'Createtime',
        ];
    }
    public function getUserByUserName($username)
    {
        if (empty($username) || !is_string($username)) {
            return null;
        }
        return self::find()->where(['username' => $username])->one();
    }

    public function verifyUserPassword($password)
    {
        if (empty($password)) {
            return false;
        }
        if ($this->password != md5($password)) {
            return false;
        }
        return true;
    }

    public function updateLogintime()
    {
        try {
            $this->logintime = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
   
}
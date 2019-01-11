<?php

namespace app\modules\balance\models;

use Yii;
use yii\web\IdentityInterface;
use app\common\Crypt3Des;
/**
 * This is the model class for table "pay_manager".
 *
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string $realname
 * @property string $auth_key
 * @property string $ip
 * @property string $logintime
 * @property integer $status
 * @property string $create_time
 */
class Manager extends \app\modules\balance\models\SystemBase  implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cg_manager';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['logintime', 'create_time'], 'safe'],
            [['status','type'], 'integer'],
            [['username', 'realname', 'ip'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 64],
            [['auth_key','des_key'], 'string', 'max' => 32]
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
            'realname' => 'Realname',
            'auth_key' => 'Auth Key',
            'des_key'  => 'Des Key',
            'ip' => 'Ip',
            'logintime' => 'Logintime',
            'status' => 'Status',
            'type' => 'Types',
            'create_time' => 'Create Time',
        ];
    }
    
    /**
     * 根据给到的ID查询身份。
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * 根据 token 查询身份。
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
	public function beforeSave($insert)
    {
    	if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key   = Yii::$app->security->generateRandomString();
                $this->des_key    = Yii::$app->security->generateRandomString();
                $this ->createPassword();
            }
            return true;
        }
        return false;
    }
    
    public function getUserByUserName($username,$type=0){
        if(empty($username)){
            return null;
        }
        $where = [
            'AND',
            ['username' => $username],
            ['type' => $type]
        ];
        return $data = self::find()->where($where)->one();
    }
    
    public function verifyUserPassword($password){
        if(empty($password)){
            return false;
        }
        if($this->password != Crypt3Des::encrypt($password, $this->des_key)){
            return false;
        }
        return true;
    }
    
    public static function getStatus(){
        return [
            1 => '正常',
            2 => '禁用',
        ];
    }
    
    public static function getTypeStatus(){
        return [
            1 => '支付系统',
            2 => '清结算',
            3 => '保险管理',
        ];
    }
    
    public function getUserInfo($post){
        if(empty($post)){
            return null;
        }
        $where = [
            'AND',
            ['username' => $post['username']],
            ['type' => $post['type']],
            ['!=' , 'id',$post['id']]
        ];
        return $data = self::find()->where($where)->count();
    }

    public function createData($data){
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
    
    public function updateData($data){
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        if(isset($data['password']) && $data['password']!=''){
            $this->password = Crypt3Des::encrypt($data['password'], $this->des_key);
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
    
    private function createPassword(){
        $this -> password = Crypt3Des::encrypt($this->password, $this->des_key);
    }
    
    public function updatePassword($password){
        if(empty($password)){
            return false;
        }
        $this -> password = Crypt3Des::encrypt($password, $this->des_key);
        return $this -> save();
    }

    public function getUserNameById($id)
    {
        if(empty($id)){
            return null;
        }
        return $data = self::find()->where(['id' => $id])->one();
    }
}

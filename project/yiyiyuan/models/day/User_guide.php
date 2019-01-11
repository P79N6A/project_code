<?php

namespace app\models\day;

use app\common\ApiSign;
use app\common\Curl;
use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\BaseModel;
use app\models\xs\XsApi;
use app\models\yyy\XhhApi;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "yi_user_guide".
 *
 * @property string $user_id
 * @property string $mobile
 * @property integer $status
 * @property integer $identity_valid
 * @property string $realname
 * @property string $identity
 * @property string $pic_self
 * @property string $pic_identity
 * @property integer $pic_type
 * @property integer $come_from
 * @property string $create_time
 * @property string $pic_up_time
 * @property string $last_login_time
 */
class User_guide extends BaseModel implements IdentityInterface {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_user';
    }

    public function getUserbank() {
        return $this->hasOne(User_bank::className(), ['user_id' => 'user_id']);
    }

    public function getallloan() {
        return $this->hasMany(User_loan_guide::className(), ['user_id' => 'user_id'])->select("concat('1_',`loan_id`) as loan_id")->where(['in', 'status', [8, 9, 11, 12, 13]])->asArray();
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['status', 'identity_valid', 'pic_type', 'come_from'], 'integer'],
            [['create_time', 'pic_up_time', 'last_login_time'], 'safe'],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['realname'], 'string', 'max' => 32],
            [['pic_self', 'pic_identity'], 'string', 'max' => 128]
        ];
    }

    public function getOldUser() {
        if (!empty($this->identity)) {
            $old = \app\models\news\User::find()->where(['identity' => $this->identity])->one();
            if (!empty($old)) {
                return $old;
            }
        }
        $mobile = $this->mobile;
        $old = \app\models\news\User::find()->where(['mobile' => $mobile])->one();
        return $old;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'user_id' => 'User ID',
            'mobile' => 'Mobile',
            'status' => 'Status',
            'identity_valid' => 'Identity Valid',
            'realname' => 'Realname',
            'identity' => 'Identity',
            'pic_self' => 'Pic Self',
            'pic_identity' => 'Pic Identity',
            'pic_type' => 'Pic Type',
            'come_from' => 'Come From',
            'create_time' => 'Create Time',
            'pic_up_time' => 'Pic Up Time',
            'last_login_time' => 'Last Login Time',
        ];
    }

    /**
     * 更新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/8/2 19:43
     */
    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取记录，根据手机号
     * @param $mobile
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/2 19:04
     */
    public function getByMobile($mobile) {
        if (empty($mobile)) {
            return null;
        }
        return self::find()->where(['mobile' => $mobile])->one();
    }

    /**
     * 获取记录，根据身份证号
     * @param $identity
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/3 15:25
     */
    public function getByIdentity($identity) {
        if (empty($identity)) {
            return null;
        }
        return self::find()->where(['identity' => $identity])->one();
    }

    /**
     * 根据给到的ID查询身份。
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }

    /**
     * 根据 token 查询身份。
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId() {
        return $this->user_id;
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey() {
        return $this->user_id;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 添加用户
     * @param $condition
     * @return bool
     */
    public function addUser($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 修改user信息
     * @param $condition
     * @return bool
     */
    public function update_user($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            Logger::dayLog('mobile', $this->mobile, $error);
            return false;
        }
        return $this->save();
    }

}

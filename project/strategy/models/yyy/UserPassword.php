<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "yi_user_password".
 *
 * @property string $id
 * @property string $user_id
 * @property string $login_password
 * @property string $pay_password
 * @property string $device_tokens
 * @property string $device_type
 * @property string $iden_address
 * @property string $nation
 * @property string $pic_url
 * @property string $iden_url
 * @property double $score
 * @property string $create_time
 * @property string $last_modify_time
 * @property string $version
 */
class UserPassword extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_password';
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
            [['user_id', 'create_time', 'last_modify_time', 'version'], 'required'],
            [['user_id', 'version'], 'integer'],
            [['score'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['login_password', 'pay_password', 'device_tokens', 'iden_address', 'pic_url', 'iden_url'], 'string', 'max' => 64],
            [['device_type'], 'string', 'max' => 10],
            [['nation'], 'string', 'max' => 32]
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
            'iden_address' => '身份证地址',
            'nation' => '民族',
            'pic_url' => '活体图片地址',
            'iden_url' => '身份证照片',
            'score' => '活体验证返回分数',
            'create_time' => '添加时间',
            'last_modify_time' => '最后修改时间',
            'version' => '乐观锁',
        ];
    }

    public function getScoreInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getPwdByUserId($userId)
    {
        return $this->find()->where("user_id = $userId")->orderby('ID DESC')->one();
    }
}

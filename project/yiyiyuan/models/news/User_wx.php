<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_wx".
 *
 * @property string $id
 * @property string $nickname
 * @property integer $is_atten
 * @property string $openid
 * @property string $from_code
 * @property string $head
 * @property string $latitude
 * @property string $longtitude
 * @property string $last_login_time
 * @property string $create_time
 * @property integer $version
 */
class User_wx extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_wx';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_atten', 'version'], 'integer'],
            [['last_login_time', 'create_time'], 'safe'],
            [['nickname', 'openid', 'latitude', 'longtitude'], 'string', 'max' => 64],
            [['from_code'], 'string', 'max' => 20],
            [['head'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nickname' => 'Nickname',
            'is_atten' => 'Is Atten',
            'openid' => 'Openid',
            'from_code' => 'From Code',
            'head' => 'Head',
            'latitude' => 'Latitude',
            'longtitude' => 'Longtitude',
            'last_login_time' => 'Last Login Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 添加用户
     * @param $condition
     * @return array|bool|null
     */
    public function addUser($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_login_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

}

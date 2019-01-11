<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "access_token".
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
class Accesstoken extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_access_token';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['access_token', 'time'], 'required'],
            [['time', 'type'], 'integer'],
            [['access_token'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'access_token' => 'Access Token',
            'time' => 'Time',
            'type' => 'Type',
        ];
    }

    /**
     * 添加一条access_token记录
     * @param type $access_token
     * @return type
     */
    public function add_record($access_token, $type = 1) {
        //update yi_access_token set access_token = '$accessToken',time=$time where type=1
        //insert into " . Accesstoken::tableName() . "(access_token,time) value('$accessToken','$time')
        $data['time'] = time();
        $data['access_token'] = $access_token;
        $data['type'] = $type;
        $error = $this->chkAttributes($data);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    /**
     * 更新access_token记录
     * @param type $access_token
     * @return type
     */
    public function update_record($access_token) {
        $data['access_token'] = $access_token;
        $data['time'] = time();
        $error = $this->chkAttributes($data);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    /**
     * 获取记录，根据type
     * @param $type
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/10/11 15:13
     */
    public function getByType($type) {
        if (empty($type)) {
            return null;
        }
        return self::find()->where(['type' => $type])->one();
    }

    /**
     * 是否失效
     * @param $hour 小时
     * @return bool true未过期 false过期
     * @author 王新龙
     * @date 2018/10/11 15:25
     */
    public function isInvalid($hour = 1) {
        if (empty($this)) {
            return false;
        }
        if ($this->time + 3600 * $hour < time()) {
            return false;
        }
        return true;
    }
}

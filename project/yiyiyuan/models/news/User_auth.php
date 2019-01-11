<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_user_auth".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $user_id
 * @property string $from_user_id
 * @property integer $type
 * @property integer $is_yyy
 * @property integer $is_up
 * @property string $amount
 * @property integer $num
 * @property integer $relation
 * @property string $page_answer
 * @property integer $use_time
 * @property string $create_time
 */
class User_auth extends BaseModel{

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'from_user_id', 'type', 'is_yyy', 'is_up', 'num', 'relation', 'use_time'], 'integer'],
            [['amount'], 'number'],
            [['create_time'], 'safe'],
            [['page_answer'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'from_user_id' => 'From User ID',
            'type' => 'Type',
            'is_yyy' => 'Is Yyy',
            'is_up' => 'Is Up',
            'amount' => 'Amount',
            'num' => 'Num',
            'relation' => 'Relation',
            'page_answer' => 'Page Answer',
            'use_time' => 'Use Time',
            'create_time' => 'Create Time',
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'from_user_id']);
    }

    public function getUsers() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getRed() {
        return $this->hasOne(Red_packets_grant::className(), ['user_id' => 'from_user_id']);
    }

    public function getReds() {
        return $this->hasOne(Red_packets_grant::className(), ['user_id' => 'user_id']);
    }

    /**
     * 查询认证$user_id成功的用户
     */
    public function getAuthByUserId($user_id) {
        $userIds = User_auth::find()->where(['user_id' => $user_id, 'is_up' => 2])->select('from_user_id')->all();
        return $userIds;
    }

}

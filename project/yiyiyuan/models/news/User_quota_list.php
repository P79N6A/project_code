<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_user_quota_list".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $status
 * @property string $create_time
 * @property string $last_modify_time
 */
class User_quota_list extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_quota_list';
    }
    
    
    public function getUserquota() {
        return $this->hasOne(User_quota::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe']
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
            'status' => 'Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
        ];
    }

    public function updateStatus($user_id,$status)
    {
        if(empty($user_id) || empty($status)){
            return false;
        }
        $user_quota_list = self::find()->where(['user_id'=>$user_id])->one();
        if(empty($user_quota_list)){
            return false;
        }
        $user_quota_list->status = $status;
        $user_quota_list->last_modify_time = date('Y-m-d H:i:s');
        return $user_quota_list->save();
    }
}
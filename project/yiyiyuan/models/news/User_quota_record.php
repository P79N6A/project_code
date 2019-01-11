<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_user_quota_record".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property integer $method
 * @property string $old_quota
 * @property string $new_quota
 * @property string $desc
 * @property string $last_modify_time
 * @property string $create_time
 */
class User_quota_record extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_quota_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'method', 'desc', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'type', 'method'], 'integer'],
            [['old_quota', 'new_quota'], 'number'],
            [['desc'], 'string'],
            [['last_modify_time', 'create_time'], 'safe']
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
            'type' => 'Type',
            'method' => 'Method',
            'old_quota' => 'Old Quota',
            'new_quota' => 'New Quota',
            'desc' => 'Desc',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 添加一条纪录
     */
    public static function addRecord($user_id, $old_quota, $new_quota, $desc , $method =false ) {
        $o = new self;

        // 数据
        $create_time = date('Y-m-d H:i:s');
        $data = [
            'user_id' => $user_id,
            'type' => 1,
            'method' => 1,
            'old_quota' => $old_quota,
            'new_quota' => $new_quota,
            'desc' => $desc,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        if($method > 0){
            $data['method'] = $method;
        }

        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }
}

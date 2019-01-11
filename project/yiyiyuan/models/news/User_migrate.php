<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_migrate".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $status
 * @property string $mobile
 * @property string $realname
 * @property string $identity
 * @property string $last_modify_time
 * @property string $create_time
 */
class User_migrate extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_migrate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['realname'], 'string', 'max' => 32]
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
            'mobile' => 'Mobile',
            'realname' => 'Realname',
            'identity' => 'Identity',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    //批量锁定
    public function updateAllLock($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        return self::updateAll(['status' => '3'], ['id' => $ids]);
    }

    //失败
    public function doFail()
    {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status = '2';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //成功
    public function doSuccess()
    {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status = '1';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}

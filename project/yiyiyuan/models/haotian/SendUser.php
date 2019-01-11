<?php

namespace app\models\haotian;

use app\models\news\User;
use Exception;

/**
 * This is the model class for table "send_user".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $project
 * @property integer $channel
 * @property integer $send_type
 * @property string $send_time
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class SendUser extends HaotianBaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'sms_send_user';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'project', 'channel', 'send_type', 'status', 'version'], 'integer'],
            [['send_time', 'last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'user_id'          => 'User ID',
            'project'          => 'Project',
            'channel'          => 'Channel',
            'send_type'        => 'Send Type',
            'send_time'        => 'Send Time',
            'status'           => 'Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time'      => 'Create Time',
            'version'          => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getSendtype() {
        return $this->hasOne(SendType::className(), ['send_type' => 'send_type']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * 锁定
     */
    public function lockAll($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $rows = static::updateAll(['status' => '1'], ['id' => $ids]);
        return $rows;
    }

    /**
     * 保存为锁定: 锁定当前纪录
     * @return  bool
     */
    public function lock() {
        $result = $this->save();
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status           = '1';
            $result                 = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    //处理失败
    public function fail() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status           = '3';
            $result                 = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    //处理成功
    public function success() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status           = '2';
            $result                 = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

}
